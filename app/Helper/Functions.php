<?php

namespace Rtcl\OffloadMedia\Helper;

use Rtcl\Helpers\Functions as RtclFunctions;
use Rtcl\OffloadMedia\Clients\R2Client;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Functions {

	public static function get_storage_client(): R2Client|bool {
		$options = RtclFunctions::get_option( 'rtcl_offload_media_settings' );

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