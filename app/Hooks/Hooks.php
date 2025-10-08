<?php

namespace Rtcl\OffloadMedia\Hooks;

use Rtcl\OffloadMedia\Clients\AbstractClient;
use Rtcl\OffloadMedia\Helper\Functions;
use Rtcl\Helpers\Functions as RtclFunctions;

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
	 * Upload main file and its sizes to R2
	 *
	 * @param array $upload
	 *
	 * @return array
	 */
	public function upload_to_storage( $upload ) {
		if ( empty( $upload['file'] ) ) {
			return $upload;
		}

		$file_path    = wp_normalize_path( $upload['file'] );
		$upload_dir   = wp_get_upload_dir();
		$relative_key = str_replace( wp_normalize_path( $upload_dir['basedir'] . '/' ), '', $file_path );

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
	 * @param array $metadata
	 * @param int   $attachment_id
	 *
	 * @return array
	 */
	public function upload_all_sizes_to_storage( $metadata, $attachment_id ) {
		$file = get_post_meta( $attachment_id, '_wp_attached_file', true );
		if ( ! $file ) {
			return $metadata;
		}

		$upload_dir = wp_get_upload_dir();
		$base_dir   = wp_normalize_path( trailingslashit( $upload_dir['basedir'] ) );

		$client = Functions::get_storage_client();
		if ( ! $client instanceof AbstractClient ) {
			return $metadata;
		}

		$stored_path = [];

		// Upload original file
		$main_file_path = $base_dir . $file;
		if ( file_exists( $main_file_path ) ) {
			$url           = $client->upload( $main_file_path, $file );
			$stored_path[] = $url;
		}

		// Upload all generated sizes
		if ( ! empty( $metadata['sizes'] ) && is_array( $metadata['sizes'] ) ) {
			foreach ( $metadata['sizes'] as $size_data ) {
				if ( ! empty( $size_data['file'] ) ) {
					$size_file_path = wp_normalize_path( path_join( dirname( $main_file_path ), $size_data['file'] ) );
					if ( file_exists( $size_file_path ) ) {
						$relative_key  = str_replace( $base_dir, '', $size_file_path );
						$url           = $client->upload( $size_file_path, $relative_key );
						$stored_path[] = $url;
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
				@unlink( $main_file_path );
			}

			// Delete all resized files
			if ( ! empty( $metadata['sizes'] ) && is_array( $metadata['sizes'] ) ) {
				foreach ( $metadata['sizes'] as $size_data ) {
					if ( ! empty( $size_data['file'] ) ) {
						$size_file_path = wp_normalize_path( path_join( dirname( $main_file_path ), $size_data['file'] ) );
						if ( file_exists( $size_file_path ) ) {
							@unlink( $size_file_path );
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
	 * @param string $url
	 * @param int    $post_id
	 *
	 * @return string
	 */
	public function replace_with_storage_url( $url, $post_id ) {
		// Only replace URL if the file was offloaded
		$offloaded = get_post_meta( $post_id, '_rtcl_offloaded_file', true );
		if ( ! $offloaded ) {
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
	 * @param int $post_id
	 *
	 * @return void
	 */
	public function delete_from_storage( $post_id ): void {
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