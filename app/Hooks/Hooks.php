<?php

namespace Rtcl\OffloadMedia\Hooks;

use Rtcl\OffloadMedia\Clients\AbstractClient;
use Rtcl\OffloadMedia\Helper\Functions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Hooks {

	/**
	 * Init
	 *
	 * @return void
	 */
	public static function init(): void {
		$instance = new self();
		// Upload hook
		add_filter( 'wp_handle_upload', [ $instance, 'upload_to_storage' ] );
		// Replace URLs for attachments
		add_filter( 'wp_get_attachment_url', [ $instance, 'replace_with_storage_url' ], 20, 2 );
		// Delete attachments from storage
		add_action( 'delete_attachment', [ $instance, 'delete_from_storage' ] );
		// Upload all sizes after metadata generation
		add_filter( 'wp_generate_attachment_metadata', [ $instance, 'upload_all_sizes_to_storage' ], 99, 2 );
	}

	/**
	 * Upload the main file and its sizes to R2
	 *
	 * @param array $upload Upload data.
	 *
	 * @return array
	 */
	public function upload_to_storage( array $upload ): array {
		if ( empty( $upload['file'] ) ) {
			return $upload;
		}

		$file_path    = wp_normalize_path( $upload['file'] );
		$upload_dir   = wp_get_upload_dir();
		$relative_key = str_replace( wp_normalize_path( $upload_dir['basedir'] . '/' ), '', $file_path );

		// Skip non-classified uploads
		$only_classified = Functions::offload_only_rtcl();
		if ( $only_classified && ! str_contains( $relative_key, 'classified-listing/' ) ) {
			return $upload;
		}

		$client = Functions::get_storage_client();
		if ( ! ( $client instanceof AbstractClient ) ) {
			return $upload;
		}

		if ( file_exists( $file_path ) ) {
			$url = $client->upload( $file_path, $relative_key );

			if ( $url ) {
				$upload['url'] = $url;
			}
		}

		return $upload;
	}

	/**
	 * Upload all generated sizes to storage after metadata generation
	 *
	 * @param array $metadata Attachment metadata.
	 * @param int   $attachment_id Attachment ID.
	 *
	 * @return array
	 */
	public function upload_all_sizes_to_storage( array $metadata, int $attachment_id ): array {
		$file = get_post_meta( $attachment_id, '_wp_attached_file', true );
		if ( ! $file ) {
			return $metadata;
		}

		$upload_dir = wp_get_upload_dir();
		$base_dir   = wp_normalize_path( trailingslashit( $upload_dir['basedir'] ) );

		// Skip non-classified uploads
		$only_classified = Functions::offload_only_rtcl();
		if ( $only_classified && ! str_contains( $file, 'classified-listing/' ) ) {
			return $metadata;
		}

		$client = Functions::get_storage_client();
		if ( ! $client instanceof AbstractClient ) {
			return $metadata;
		}

		$stored_path = [];

		// Upload original file
		$main_file_path = $base_dir . $file;
		if ( file_exists( $main_file_path ) ) {
			$client->upload( $main_file_path, $file );
			$stored_path['main'] = $file;
		}

		// Upload all generated sizes
		if ( ! empty( $metadata['sizes'] ) && is_array( $metadata['sizes'] ) ) {
			foreach ( $metadata['sizes'] as $size_name => $size_data ) {
				if ( ! empty( $size_data['file'] ) ) {
					$size_file_path = wp_normalize_path( path_join( dirname( $main_file_path ), $size_data['file'] ) );
					if ( file_exists( $size_file_path ) ) {
						$relative_key = str_replace( $base_dir, '', $size_file_path );
						$client->upload( $size_file_path, $relative_key );
						$stored_path[ $size_name ] = $relative_key;
					}
				}
			}
		}

		if ( $attachment_id && ! empty( $stored_path ) ) {
			update_post_meta( $attachment_id, '_rtcl_offloaded_file', $stored_path );
		}

		if ( Functions::remove_local_file() ) {
			// Delete the main file
			if ( file_exists( $main_file_path ) ) {
				wp_delete_file( $main_file_path );
			}

			// Delete all resized files
			if ( ! empty( $metadata['sizes'] ) && is_array( $metadata['sizes'] ) ) {
				foreach ( $metadata['sizes'] as $size_data ) {
					if ( ! empty( $size_data['file'] ) ) {
						$size_file_path = wp_normalize_path( path_join( dirname( $main_file_path ), $size_data['file'] ) );
						if ( file_exists( $size_file_path ) ) {
							wp_delete_file( $size_file_path );
						}
					}
				}
			}
		}

		return $metadata;
	}

	/**
	 * Replace attachment URL with storage URL
	 *
	 * @param string $url Attachment URL.
	 * @param int    $post_id The attachment post ID.
	 *
	 * @return string
	 */
	public function replace_with_storage_url( string $url, int $post_id ): string {
		// Only replace URL if the file was offloaded
		$offloaded = get_post_meta( $post_id, '_rtcl_offloaded_file', true );
		if ( ! $offloaded || empty( $offloaded['main'] ) ) {
			return $url;
		}

		$file = get_post_meta( $post_id, '_wp_attached_file', true );
		if ( ! $file ) {
			return $url;
		}

		$client = Functions::get_storage_client();
		if ( $client instanceof AbstractClient ) {
			return $client->getUrl( $file );
		}

		return $url;
	}

	/**
	 * Delete attachment files from storage
	 *
	 * @param int $post_id The attachment post ID.
	 *
	 * @return void
	 */
	public function delete_from_storage( int $post_id ): void {
		$file = get_post_meta( $post_id, '_wp_attached_file', true );
		if ( ! $file ) {
			return;
		}

		$offloaded = get_post_meta( $post_id, '_rtcl_offloaded_file', true );
		if ( ! $offloaded ) {
			return;
		}

		$client = Functions::get_storage_client();
		if ( $client instanceof AbstractClient ) {
			$client->delete( $file );

			// Delete all sizes if metadata exists
			$metadata = wp_get_attachment_metadata( $post_id );
			if ( ! empty( $metadata['sizes'] ) && is_array( $metadata['sizes'] ) ) {
				foreach ( $metadata['sizes'] as $size ) {
					$size_file = dirname( $file ) . '/' . $size['file'];
					$client->delete( $size_file );
				}
				delete_post_meta( $post_id, '_rtcl_offloaded_file' );
			}
		}
	}
}
