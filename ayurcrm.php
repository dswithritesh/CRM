<?php
/**
 * Plugin Name: AyurCRM – Ayurvedic & Wellness CRM
 * Plugin URI: https://github.com/dswithritesh/CRM
 * Description: Enterprise-grade CRM and lead management system for Ayurvedic clinics, wellness centers, and health brands. Supports full lead lifecycle, assignment engine, follow-up tracking, campaign attribution, and reporting.
 * Version: 1.0.0
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Author: dswithritesh
 * Author URI: https://github.com/dswithritesh
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ayurcrm
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AYURCRM_VERSION', '1.0.0' );
define( 'AYURCRM_DB_VERSION', '1.0.0' );
define( 'AYURCRM_PATH', plugin_dir_path( __FILE__ ) );
define( 'AYURCRM_URL', plugin_dir_url( __FILE__ ) );
define( 'AYURCRM_BASENAME', plugin_basename( __FILE__ ) );
define( 'AYURCRM_PLUGIN_FILE', __FILE__ );
define( 'AYURCRM_MINIMUM_PHP', '7.4' );
define( 'AYURCRM_MINIMUM_WP', '5.8' );

/**
 * Check minimum PHP and WordPress version requirements.
 * If requirements are not met, show an admin notice and deactivate the plugin.
 *
 * @return bool True if requirements are met, false otherwise.
 */
function ayurcrm_meets_requirements() {
	global $wp_version;

	if ( version_compare( PHP_VERSION, AYURCRM_MINIMUM_PHP, '<' ) ) {
		return false;
	}

	if ( version_compare( $wp_version, AYURCRM_MINIMUM_WP, '<' ) ) {
		return false;
	}

	return true;
}

if ( ! ayurcrm_meets_requirements() ) {
	/**
	 * Display an admin notice when minimum requirements are not met.
	 * Uses a transient so the notice persists across the redirect that follows
	 * deactivate_plugins(), ensuring the user always sees why AyurCRM was deactivated.
	 */
	function ayurcrm_requirements_notice() {
		$message = get_transient( 'ayurcrm_requirements_error' );
		if ( ! $message ) {
			return;
		}
		delete_transient( 'ayurcrm_requirements_error' );
		echo '<div class="notice notice-error is-dismissible"><p>' . nl2br( esc_html( $message ) ) . '</p></div>';
	}

	add_action( 'admin_notices', 'ayurcrm_requirements_notice' );

	$messages = array();

	if ( version_compare( PHP_VERSION, AYURCRM_MINIMUM_PHP, '<' ) ) {
		$messages[] = sprintf(
			/* translators: 1: Required PHP version, 2: Current PHP version */
			esc_html__( 'AyurCRM requires PHP %1$s or higher. Your current PHP version is %2$s. Please upgrade PHP to activate this plugin.', 'ayurcrm' ),
			esc_html( AYURCRM_MINIMUM_PHP ),
			esc_html( PHP_VERSION )
		);
	}

	if ( version_compare( $wp_version, AYURCRM_MINIMUM_WP, '<' ) ) {
		$messages[] = sprintf(
			/* translators: 1: Required WordPress version, 2: Current WordPress version */
			esc_html__( 'AyurCRM requires WordPress %1$s or higher. Your current WordPress version is %2$s. Please upgrade WordPress to activate this plugin.', 'ayurcrm' ),
			esc_html( AYURCRM_MINIMUM_WP ),
			esc_html( $wp_version )
		);
	}

	set_transient( 'ayurcrm_requirements_error', implode( "\n", $messages ), 60 );

	if ( function_exists( 'deactivate_plugins' ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
	}

	return;
}

if ( file_exists( AYURCRM_PATH . 'includes/class-ayurcrm-loader.php' ) ) {
	require_once AYURCRM_PATH . 'includes/class-ayurcrm-loader.php';
}

if ( file_exists( AYURCRM_PATH . 'includes/class-ayurcrm-plugin.php' ) ) {
	require_once AYURCRM_PATH . 'includes/class-ayurcrm-plugin.php';
}

/*
 * Register activation and deactivation hooks from the main plugin file, as
 * required by WordPress. The AyurCRM_Activator and AyurCRM_Deactivator classes
 * are loaded internally by AyurCRM_Plugin (via class-ayurcrm-plugin.php above),
 * so they are guaranteed to be defined by the time these hooks fire.
 */
register_activation_hook( __FILE__, array( 'AyurCRM_Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'AyurCRM_Deactivator', 'deactivate' ) );

if ( class_exists( 'AyurCRM_Plugin' ) ) {
	AyurCRM_Plugin::get_instance()->init();
}
