<?php
/**
 * Plugin Name: AyurCRM – Ayurvedic & Wellness CRM
 * Plugin URI:  https://github.com/dswithritesh/CRM
 * Description: Enterprise-grade CRM and lead management system for Ayurvedic clinics, wellness centers, and health brands. Supports full lead lifecycle, assignment engine, follow-up tracking, campaign attribution, and reporting.
 * Version:     1.0.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author:      dswithritesh
 * Author URI:  https://github.com/dswithritesh
 * License:     GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ayurcrm
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ---------------------------------------------------------------------------
// Minimum requirement constants (defined before any require so the compat
// check can reference them without loading any other file first).
// ---------------------------------------------------------------------------
define( 'AYURCRM_MIN_PHP', '7.4' );
define( 'AYURCRM_MIN_WP',  '6.0' );

// ---------------------------------------------------------------------------
// Compatibility check — graceful deactivation, zero fatal errors.
// ---------------------------------------------------------------------------
if ( version_compare( PHP_VERSION, AYURCRM_MIN_PHP, '<' ) ) {
	add_action( 'admin_notices', static function () {
		echo '<div class="notice notice-error"><p>'
			. sprintf(
				/* translators: 1: required PHP version, 2: current PHP version */
				esc_html__( 'AyurCRM requires PHP %1$s or higher. Your server is running PHP %2$s. Please upgrade PHP or contact your host.', 'ayurcrm' ),
				esc_html( AYURCRM_MIN_PHP ),
				esc_html( PHP_VERSION )
			)
			. '</p></div>';
	} );
	if ( function_exists( 'deactivate_plugins' ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
	}
	return;
}

global $wp_version;
if ( isset( $wp_version ) && version_compare( $wp_version, AYURCRM_MIN_WP, '<' ) ) {
	add_action( 'admin_notices', static function () use ( $wp_version ) {
		echo '<div class="notice notice-error"><p>'
			. sprintf(
				/* translators: 1: required WP version, 2: current WP version */
				esc_html__( 'AyurCRM requires WordPress %1$s or higher. Your site is running WordPress %2$s. Please upgrade WordPress.', 'ayurcrm' ),
				esc_html( AYURCRM_MIN_WP ),
				esc_html( $wp_version )
			)
			. '</p></div>';
	} );
	if ( function_exists( 'deactivate_plugins' ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
	}
	return;
}

// ---------------------------------------------------------------------------
// Core plugin constants.
// ---------------------------------------------------------------------------
define( 'AYURCRM_VERSION',     '1.0.0' );
define( 'AYURCRM_DB_VERSION',  '1.0.4' );
define( 'AYURCRM_PLUGIN_FILE', __FILE__ );
define( 'AYURCRM_PATH',        plugin_dir_path( __FILE__ ) );
define( 'AYURCRM_URL',         plugin_dir_url( __FILE__ ) );
define( 'AYURCRM_BASENAME',    plugin_basename( __FILE__ ) );

// Processing constants — no WP context required, safe to define here.
define( 'AYURCRM_CHUNK_SIZE',   50 );
define( 'AYURCRM_EXPORT_BATCH', 500 );
define( 'AYURCRM_CACHE_TTL',    900 );

// ---------------------------------------------------------------------------
// Fault-tolerant file loader helper (inline, no dependency).
// ---------------------------------------------------------------------------
/**
 * Require a plugin file only if it exists. On failure, log to error_log and
 * return false — never throw a fatal error from here.
 *
 * @param string $file Absolute path to file.
 * @return bool
 */
function ayurcrm_require( $file ) {
	if ( file_exists( $file ) ) {
		require_once $file;
		return true;
	}
	// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	error_log( 'AyurCRM: required file not found — ' . $file );
	return false;
}

// ---------------------------------------------------------------------------
// Load the two mandatory bootstrap files.
// ---------------------------------------------------------------------------
$ayurcrm_loader_loaded = ayurcrm_require( AYURCRM_PATH . 'includes/class-ayurcrm-loader.php' );
$ayurcrm_plugin_loaded = ayurcrm_require( AYURCRM_PATH . 'includes/class-ayurcrm-plugin.php' );

// If either bootstrap file is missing the plugin cannot function — bail with
// a friendly admin notice rather than a white screen.
if ( ! $ayurcrm_loader_loaded || ! $ayurcrm_plugin_loaded ) {
	add_action( 'admin_notices', static function () {
		echo '<div class="notice notice-error"><p>'
			. esc_html__( 'AyurCRM: Core files are missing. Please re-install the plugin.', 'ayurcrm' )
			. '</p></div>';
	} );
	return;
}

// ---------------------------------------------------------------------------
// Activation / Deactivation hooks.
// register_activation_hook() must be called from the main plugin file.
// The Activator and Deactivator classes are loaded by AyurCRM_Plugin
// internally; the callbacks are referenced as strings so WordPress can
// call them at the right time even before init fires.
// ---------------------------------------------------------------------------
register_activation_hook(
	__FILE__,
	static function () {
		if ( class_exists( 'AyurCRM_Activator' ) ) {
			AyurCRM_Activator::activate();
		}
	}
);

register_deactivation_hook(
	__FILE__,
	static function () {
		if ( class_exists( 'AyurCRM_Deactivator' ) ) {
			AyurCRM_Deactivator::deactivate();
		}
	}
);

// ---------------------------------------------------------------------------
// Boot the plugin — deferred to plugins_loaded so all WP functions and other
// plugins are available. init() registers hooks only; zero heavy work here.
// ---------------------------------------------------------------------------
add_action(
	'plugins_loaded',
	static function () {
		if ( class_exists( 'AyurCRM_Plugin' ) ) {
			AyurCRM_Plugin::get_instance()->init();
		}
	},
	10
);
