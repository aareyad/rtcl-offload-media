<?php
/**
 * Offload Media plugin for Classified Listing
 * Plugin Name: Classified Listing - Offload Media
 * Plugin URI: https://wordpress.org/plugins/rtcl-offload-media/
 * Description: Allow users to offload media to Cloudflare R2.
 * Version: 1.0.0
 * Author: RadiusTheme
 * Author URI: https://radiustheme.com
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: rtcl-offload-media
 * Domain Path: /languages
 *
 * @package Rtcl\OffloadMedia
 */

use Rtcl\OffloadMedia\Init;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define constants.
if ( ! defined( 'RTCL_OFFLOAD_MEDIA_VERSION' ) ) {
	define( 'RTCL_OFFLOAD_MEDIA_VERSION', '1.0.0' );
}
if ( ! defined( 'RTCL_OFFLOAD_MEDIA_PLUGIN_FILE' ) ) {
	define( 'RTCL_OFFLOAD_MEDIA_PLUGIN_FILE', __FILE__ );
}
if ( ! defined( 'RTCL_OFFLOAD_MEDIA_PLUGIN_PATH' ) ) {
	define( 'RTCL_OFFLOAD_MEDIA_PLUGIN_PATH', plugin_dir_path( RTCL_OFFLOAD_MEDIA_PLUGIN_FILE ) );
}
if ( ! defined( 'RTCL_OFFLOAD_MEDIA_PLUGIN_URL' ) ) {
	define( 'RTCL_OFFLOAD_MEDIA_PLUGIN_URL', plugin_dir_url( RTCL_OFFLOAD_MEDIA_PLUGIN_FILE ) );
}

// Load composer autoloader.
require_once __DIR__ . '/vendor/autoload.php';

// Initialize plugin.
add_action(
	'plugins_loaded',
	function () {
		Init::init();
	}
);
