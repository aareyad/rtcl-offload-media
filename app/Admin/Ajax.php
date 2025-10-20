<?php

namespace Rtcl\OffloadMedia\Admin;

use Rtcl\Helpers\Functions as RtclFunctions;
use Rtcl\OffloadMedia\Clients\AbstractClient;
use Rtcl\OffloadMedia\Helper\Functions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Ajax {

	public static function init(): void {
		add_action( 'wp_ajax_rtcl_check_offload_connection', [ __CLASS__, 'check_offload_connection' ] );
	}

	/**
	 * Check R2 connection
	 *
	 * @return void
	 */
	public static function check_offload_connection(): void {
		if ( ! current_user_can( 'manage_options' ) || ! RtclFunctions::verify_nonce() ) {
			wp_send_json_error( 'Unauthorized' );
		}

		$client = Functions::get_storage_client();

		if ( $client instanceof AbstractClient ) {
			$result = $client->checkConnection();

			if ( $result === true ) {
				wp_send_json_success( 'Connection OK' );
			} else {
				wp_send_json_error( 'Connection failed: ' . $result );
			}
		}

		wp_send_json_error( 'Client not initialized' );
	}

}