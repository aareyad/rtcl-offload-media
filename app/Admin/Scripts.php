<?php

namespace Rtcl\OffloadMedia\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Scripts {

	/**
	 * @return void
	 */
	public static function init(): void {
		add_action( 'admin_enqueue_scripts', [ __CLASS__, 'register_scripts' ] );
		add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_scripts' ], 12 );
	}

	/**
	 * @return void
	 */
	public static function register_scripts(): void {
		wp_register_script(
			'rtcl-offload-media',
			RTCL_OFFLOAD_MEDIA_PLUGIN_URL . 'assets/js/admin.js',
			[ 'jquery', 'rtcl-admin' ],
			RTCL_OFFLOAD_MEDIA_VERSION,
			true
		);
	}

	/**
	 * @return void
	 */
	public static function enqueue_scripts(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $_GET['page'] ) || 'rtcl-settings' !== $_GET['page'] ) {
			return;
		}
		wp_enqueue_script( 'rtcl-offload-media' );
	}
}
