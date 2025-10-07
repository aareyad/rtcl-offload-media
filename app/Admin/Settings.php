<?php

namespace Rtcl\OffloadMedia\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Settings {
	/**
	 * Init
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'rtcl_option_addon_items', array( __CLASS__, 'register_settings' ) );
	}

	/**
	 * Register settings
	 *
	 * @param $options
	 *
	 * @return array
	 */
	public static function register_settings( $options ): array {
		$options['rtcl_offload_media_settings'] = array(
			'label'  => __( 'Offload Settings', 'rtcl-offload-media' ),
			'fields' => self::options(),
		);

		return $options;
	}

	/**
	 * Options
	 *
	 * @return array
	 */
	public static function options(): array {
		return array(
			'field_title_offload' => array(
				'title'       => esc_html__( 'Offload Settings', 'rtcl-offload-media' ),
				'type'        => 'section',
				'description' => esc_html__( 'Select a cloud storage provider and provide the necessary credentials.',
					'classified-listing' ),
			),
			'provider'            => array(
				'title'   => esc_html__( 'Cloud Provider', 'rtcl-offload-media' ),
				'type'    => 'select',
				'options' => array(
					'r2' => esc_html__( 'Cloudflare R2', 'classified-listing' )
				),
				'default' => 'r2',
			),
			'access_key'          => array(
				'title'       => esc_html__( 'Access Key ID', 'rtcl-offload-media' ),
				'type'        => 'password',
				'default'     => '',
				'placeholder' => 'access-key-***********************',
			),
			'secret_key'          => array(
				'title'       => esc_html__( 'Secret Key', 'rtcl-offload-media' ),
				'type'        => 'password',
				'default'     => '',
				'placeholder' => 'secret-key-***********************',
			),
			'endpoint'            => array(
				'title' => esc_html__( 'Endpoint URL', 'rtcl-offload-media' ),
				'type'  => 'url',
			),
			'bucket'              => array(
				'title' => esc_html__( 'Bucket Name', 'rtcl-offload-media' ),
				'type'  => 'text',
			),
			'domain'              => array(
				'title' => esc_html__( 'Domain', 'rtcl-offload-media' ),
				'type'  => 'text',
			),
			'check_connection'    => [
				'title'       => '',
				'type'        => 'html',
				'description' => __( '<a id="rtcl-offload-connection-check" class="btn" href="#">Check Connection</a>', 'rtcl-offload-media' )
			]
		);
	}
}