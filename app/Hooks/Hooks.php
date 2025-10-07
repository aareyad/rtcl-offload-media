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
		add_filter( 'wp_handle_upload', array( $instance, 'upload_to_storage' ) );
		add_filter( 'wp_get_attachment_url', array( $instance, 'replace_with_storage_url' ), 20, 2 );
		add_action( 'delete_attachment', array( $instance, 'delete_from_storage' ) );
		add_filter( 'wp_generate_attachment_metadata', [ $instance, 'upload_all_sizes_to_storage' ], 99, 2 );
	}

	public function upload_to_storage( $upload ) {
		if ( empty( $upload['file'] ) ) {
			return $upload;
		}

		//$file_path    = $upload['file'];
		$file_path  = wp_normalize_path( $upload['file'] );
		$upload_dir = wp_get_upload_dir();
		//$relative_key = str_replace( $upload_dir['basedir'] . '/', '', $file_path );
		$relative_key = str_replace( wp_normalize_path( $upload_dir['basedir'] . '/' ), '', $file_path );

		$client = Functions::get_storage_client();

		if ( ! ( $client instanceof AbstractClient ) ) {
			return $upload;
		}

		$attachment_id = $upload['id'] ?? 0;

		// 1. Generate attachment metadata (resizes)
		if ( $attachment_id ) {
			$metadata = wp_generate_attachment_metadata( $attachment_id, $file_path );
			wp_update_attachment_metadata( $attachment_id, $metadata );
		} else {
			$metadata = [];
		}

		// 2. Prepare all files to upload: main file + sizes
		$files_to_upload = [ $relative_key ];

		if ( ! empty( $metadata['sizes'] ) && is_array( $metadata['sizes'] ) ) {
			foreach ( $metadata['sizes'] as $size ) {
				$files_to_upload[] = dirname( $relative_key ) . '/' . $size['file'];
			}
		}

		// 3. Upload all files to R2
		foreach ( $files_to_upload as $file ) {
			$local_file = $upload_dir['basedir'] . '/' . $file;
			if ( file_exists( $local_file ) ) {
				$client->upload( $local_file, $file );
			}
		}

		// 4. Save offloaded files meta
		/*if ( $attachment_id ) {
			update_post_meta( $attachment_id, '_rtcl_offloaded_files', $files_to_upload );
		}*/

		// 5. If offload-only mode, delete local files
		$offload_only = true;
		if ( $offload_only ) {
			foreach ( $files_to_upload as $file ) {
				$local_file = $upload_dir['basedir'] . '/' . $file;
				if ( file_exists( $local_file ) ) {
					@unlink( $local_file );
				}
			}
		}

		// 6. Set URL to R2 main file
		$upload['url'] = $client->getUrl( $relative_key );

		return $upload;


		/*if ( $client instanceof AbstractClient ) {
			$url = $client->upload( $file_path, $relative_key );
			if ( $url ) {
				$upload['url'] = $url;

				$remove_local_file = true;
				if ( $remove_local_file ) {
					@unlink( $upload['file'] );
				}
			}
		}*/

		return $upload;
	}

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

		// Upload the main/original file
		$main_file_path = $base_dir . $file;
		if ( file_exists( $main_file_path ) ) {
			$client->upload( $main_file_path, $file );
		}

		// Upload each generated image size
		if ( ! empty( $metadata['sizes'] ) ) {
			foreach ( $metadata['sizes'] as $size_data ) {
				if ( ! empty( $size_data['file'] ) ) {
					$size_file_path = wp_normalize_path( path_join( dirname( $main_file_path ), $size_data['file'] ) );
					if ( file_exists( $size_file_path ) ) {
						$relative_key = str_replace( $base_dir, '', $size_file_path );
						$client->upload( $size_file_path, $relative_key );
					}
				}
			}
		}

		// replace URLs in metadata for consistency
		if ( isset( $metadata['file'] ) ) {
			$metadata['file'] = $file;
		}

		return $metadata;
	}

	public function replace_with_storage_url( $url, $post_id ) {
		$file = get_post_meta( $post_id, '_wp_attached_file', true );
		if ( ! $file ) {
			return $url;
		}

		$client = Functions::get_storage_client();
		if ( $client instanceof AbstractClient ) {
			// Only replace URL if a file exists in storage
			if ( $client->head( $file ) ) {
				return $client->getUrl( $file );
			}
		}

		return $url;
	}

	/**
	 * Delete a file from storage
	 *
	 * @param $post_id
	 *
	 * @return void
	 */
	public function delete_from_storage( $post_id ): void {
		$file = get_post_meta( $post_id, '_wp_attached_file', true );
		if ( ! $file ) {
			return;
		}

		$client = Functions::get_storage_client();
		if ( $client instanceof AbstractClient ) {
			$client->delete( $file );
		}
	}
}