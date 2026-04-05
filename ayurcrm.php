<?php
/**
 * Plugin Name:       AyurCRM — Ayurvedic & Wellness CRM
 * Plugin URI:        https://ayurcrm.com
 * Description:       Enterprise CRM system for Ayurvedic clinics, wellness centers, and health brands.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            AyurCRM
 * Author URI:        https://ayurcrm.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       ayurcrm
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// PHP version check — must happen before anything else.
if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
	add_action( 'admin_notices', function () {
		echo '<div class="notice notice-error"><p><strong>AyurCRM</strong> requires PHP 7.4 or higher. Your server is running PHP ' . esc_html( PHP_VERSION ) . '.</p></div>';
	} );
	// Deactivate the plugin gracefully.
	add_action( 'admin_init', function () {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		if ( isset( $_GET['activate'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			unset( $_GET['activate'] );
		}
	} );
	return;
}

// WordPress version check.
global $wp_version;
if ( isset( $wp_version ) && version_compare( $wp_version, '6.0', '<' ) ) {
	add_action( 'admin_notices', function () {
		global $wp_version;
		echo '<div class="notice notice-error"><p><strong>AyurCRM</strong> requires WordPress 6.0 or higher. Your installation is running WordPress ' . esc_html( $wp_version ) . '.</p></div>';
	} );
	add_action( 'admin_init', function () {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		if ( isset( $_GET['activate'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			unset( $_GET['activate'] );
		}
	} );
	return;
}

// ---------------------------------------------------------------------------
// Constants
// ---------------------------------------------------------------------------

define( 'AYURCRM_VERSION',     '1.0.0' );
define( 'AYURCRM_DB_VERSION',  '1.0.0' );
define( 'AYURCRM_PATH',        plugin_dir_path( __FILE__ ) );
define( 'AYURCRM_URL',         plugin_dir_url( __FILE__ ) );
define( 'AYURCRM_BASENAME',    plugin_basename( __FILE__ ) );
define( 'AYURCRM_MIN_PHP',     '7.4' );
define( 'AYURCRM_MIN_WP',      '6.0' );
define( 'AYURCRM_CHUNK_SIZE',  50 );
define( 'AYURCRM_EXPORT_BATCH', 500 );
define( 'AYURCRM_CACHE_TTL',   900 );

// ---------------------------------------------------------------------------
// Load core files
// ---------------------------------------------------------------------------

require_once AYURCRM_PATH . 'includes/class-ayurcrm-loader.php';
require_once AYURCRM_PATH . 'includes/class-ayurcrm-activator.php';
require_once AYURCRM_PATH . 'includes/class-ayurcrm-deactivator.php';
require_once AYURCRM_PATH . 'includes/class-ayurcrm-plugin.php';

// ---------------------------------------------------------------------------
// Activation / Deactivation hooks
// ---------------------------------------------------------------------------

register_activation_hook( __FILE__, array( 'AyurCRM_Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'AyurCRM_Deactivator', 'deactivate' ) );

// ---------------------------------------------------------------------------
// Bootstrap
// ---------------------------------------------------------------------------

add_action( 'plugins_loaded', function () {
	AyurCRM_Plugin::get_instance()->init();
} );
