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
		add_filter( 'wp_get_attachment_url', array( $instance, 'replace_with_storage_url' ), 10, 2 );
		add_action( 'delete_attachment', array( $instance, 'delete_from_storage' ) );
	}

	public function upload_to_storage( $upload ) {
		if ( empty( $upload['file'] ) ) {
			return $upload;
		}

		$file_path    = $upload['file'];
		$upload_dir   = wp_get_upload_dir();
		$relative_key = str_replace( $upload_dir['basedir'] . '/', '', $file_path );

		$client = Functions::get_storage_client();
		if ( $client instanceof AbstractClient ) {
			$url = $client->upload( $file_path, $relative_key );
			if ( $url ) {
				$upload['url'] = $url;
			}
		}

		return $upload;
	}

	public function replace_with_storage_url( $url, $post_id ) {
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