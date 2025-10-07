<?php

namespace Rtcl\OffloadMedia;

use Rtcl\OffloadMedia\Admin\Settings;
use Rtcl\OffloadMedia\Hooks\Hooks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Initializes the Offload Media plugin.
 *
 * Handles setup for admin settings and core hooks.
 *
 * @package Rtcl\OffloadMedia
 */
class Init {
	/**
	 * Initialize the plugin
	 *
	 * @return void
	 */
	public static function init(): void {
		if ( is_admin() ) {
			self::load_admin();
		}
		self::load_hooks();
	}

	/**
	 * Load admin
	 *
	 * @return void
	 */
	private static function load_admin(): void {
		Settings::init();
	}

	/**
	 * Load hooks
	 *
	 * @return void
	 */
	private static function load_hooks(): void {
		Hooks::init();
	}
}