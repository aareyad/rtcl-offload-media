<?php

namespace Rtcl\OffloadMedia\Hooks;

use Rtcl\Helpers\Functions;
use Rtcl\OffloadMedia\Clients\R2Client;
use Rtcl\OffloadMedia\Clients\AbstractClient;

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
		add_action( 'wp_ajax_rtcl_r2_check_connection', array( $instance, 'check_r2_connection' ) );
	}

	public function check_r2_connection() {
		if ( ! current_user_can( 'manage_options' ) || ! Functions::verify_nonce() ) {
			wp_send_json_error( 'Unauthorized' );
		}

		$client = $this->get_storage_client();

		if ( $client && $client instanceof \Rtcl\OffloadMedia\Clients\R2Client ) {
			$result = $client->checkConnection();

			error_log( print_r( $result, true ) );

			if ( $result === true ) {
				wp_send_json_success( 'Connection OK' );
			} else {
				wp_send_json_error( 'Connection failed: ' . $result );
			}
		}

		wp_send_json_error( 'R2 Client not initialized' );
	}

	public function upload_to_storage( $upload ) {
		if ( empty( $upload['file'] ) ) {
			return $upload;
		}

		$file_path    = $upload['file'];
		$upload_dir   = wp_get_upload_dir();
		$relative_key = str_replace( $upload_dir['basedir'] . '/', '', $file_path );

		$client = $this->get_storage_client();
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

		$client = $this->get_storage_client();
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

		$client = $this->get_storage_client();
		if ( $client instanceof AbstractClient ) {
			$client->delete( $file );
		}
	}

	private function get_storage_client(): R2Client|bool {
		$options = Functions::get_option( 'rtcl_offload_media_settings' );

		if ( empty( $options ) ) {
			$options = array();
		}

		$provider = $options['provider'] ?? 'r2';

		$config = array(
			'access_key' => $options['access_key'] ?? '',
			'secret_key' => $options['secret_key'] ?? '',
			'bucket'     => $options['bucket'] ?? '',
			'endpoint'   => $options['endpoint'] ?? '',
			'domain'     => $options['domain'] ?? '',
			'region'     => $options['region'] ?? 'auto',
		);

		if ( $provider === 'r2' && class_exists( '\Rtcl\OffloadMedia\Clients\R2Client' ) ) {
			return new R2Client( $config );
		}

		return false;
	}
}