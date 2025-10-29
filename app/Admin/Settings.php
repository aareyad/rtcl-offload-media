<?php

namespace Rtcl\OffloadMedia\Admin;

use Rtcl\OffloadMedia\Helper\Functions;

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
		add_action( 'rtcl_option_addon_items', [ __CLASS__, 'register_settings' ] );
	}

	/**
	 * Register settings
	 *
	 * @param array $options Settings to configure an object.
	 *
	 * @return array
	 */
	public static function register_settings( $options ): array {
		$options['rtcl_offload_media_settings'] = [
			'label'  => __( 'Offload Settings', 'rtcl-offload-media' ),
			'fields' => self::options(),
		];

		return $options;
	}

	/**
	 * Options
	 *
	 * @return array
	 */
	public static function options(): array {
		return [
			'field_title_offload' => [
				'title'       => esc_html__( 'Offload Settings', 'rtcl-offload-media' ),
				'type'        => 'section',
				'description' => esc_html__(
					'Select a cloud storage provider and provide the necessary credentials.',
					'rtcl-offload-media'
				),
			],
			'check_connection'    => [
				'title'       => '',
				'type'        => 'html',
				'description' => empty( Functions::get_options() )
					? ''
					: __( '<a id="rtcl-offload-connection-check" class="btn" href="#">Check Connection</a>', 'rtcl-offload-media' ),
			],
			'provider'            => [
				'title'   => esc_html__( 'Cloud Provider', 'rtcl-offload-media' ),
				'type'    => 'select',
				'options' => [
					'r2'     => esc_html__( 'Cloudflare R2', 'rtcl-offload-media' ),
					'wasabi' => esc_html__( 'Wasabi', 'rtcl-offload-media' ),
				],
				'default' => 'r2',
			],
			'access_key'          => [
				'title'       => esc_html__( 'Access Key ID', 'rtcl-offload-media' ),
				'type'        => 'password',
				'default'     => '',
				'placeholder' => 'access-key-***********************',
			],
			'secret_key'          => [
				'title'       => esc_html__( 'Secret Key', 'rtcl-offload-media' ),
				'type'        => 'password',
				'default'     => '',
				'placeholder' => 'secret-key-***********************',
			],
			'endpoint'            => [
				'title'   => esc_html__( 'Endpoint URL', 'rtcl-offload-media' ),
				'type'    => 'url',
				'depends' => [
					'on' => [
						[
							'field'     => 'rtcl_offload_media_settings.provider',
							'value'     => 'r2',
							'condition' => '=',
						],
					],
				],
			],
			'region'              => [
				'title'   => esc_html__( 'Region', 'rtcl-offload-media' ),
				'type'    => 'text',
				'depends' => [
					'on' => [
						[
							'field'     => 'rtcl_offload_media_settings.provider',
							'value'     => 'wasabi',
							'condition' => '=',
						],
					],
				],
			],
			'bucket'              => [
				'title' => esc_html__( 'Bucket Name', 'rtcl-offload-media' ),
				'type'  => 'text',
			],
			'domain'              => [
				'title' => esc_html__( 'Domain', 'rtcl-offload-media' ),
				'type'  => 'text',
			],
			'skip_local_storage'  => [
				'title'       => esc_html__( 'Skip Local Storage', 'rtcl-offload-media' ),
				'type'        => 'switch',
				'default'     => 'yes',
				'description' => esc_html__( 'Allow upload only to CDN storage.', 'rtcl-offload-media' ),
			],
			'rtcl_offload_only'   => [
				'title'       => esc_html__( 'Offload Listing Image Only', 'rtcl-offload-media' ),
				'type'        => 'switch',
				'default'     => 'yes',
				'description' => esc_html__( 'Allow upload only for listing image and use local storage for others.', 'rtcl-offload-media' ),
			],
		];
	}
}
