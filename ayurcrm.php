<?php
/**
 * AyurCRM — Enterprise CRM for Ayurvedic & Wellness Businesses
 *
 * @package           AyurCRM
 * @author            AyurCRM
 * @copyright         2024 AyurCRM
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       AyurCRM
 * Plugin URI:        https://ayurcrm.com
 * Description:       Enterprise CRM + Leads Management System for Ayurvedic clinics, wellness centers, and health brands.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      8.0
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

/**
 * Plugin-wide constants.
 */
define( 'AYURCRM_VERSION', '1.0.0' );
define( 'AYURCRM_DB_VERSION', '1.0.0' );
define( 'AYURCRM_PLUGIN_FILE', __FILE__ );
define( 'AYURCRM_PATH', plugin_dir_path( __FILE__ ) );
define( 'AYURCRM_URL', plugin_dir_url( __FILE__ ) );
define( 'AYURCRM_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Returns the AyurCRM upload directory path.
 *
 * Defined as a function (not a constant) because wp_upload_dir() must be
 * called at runtime, after WordPress has fully bootstrapped.
 *
 * @return string Absolute path to the AyurCRM upload directory.
 */
function ayurcrm_upload_dir() {
	return wp_upload_dir()['basedir'] . '/ayurcrm';
}

/**
 * Require the plugin orchestrator.
 */
require_once AYURCRM_PATH . 'includes/class-ayurcrm-plugin.php';

/**
 * Bootstrap the plugin.
 *
 * Registers hooks only — zero heavy work at load time.
 */
AyurCRM_Plugin::get_instance()->init();

/**
 * Plugin activation hook.
 *
 * @return void
 */
function ayurcrm_activate() {
	require_once AYURCRM_PATH . 'includes/class-ayurcrm-activator.php';
	AyurCRM_Activator::activate();
}
register_activation_hook( __FILE__, 'ayurcrm_activate' );

/**
 * Plugin deactivation hook.
 *
 * @return void
 */
function ayurcrm_deactivate() {
	require_once AYURCRM_PATH . 'includes/class-ayurcrm-deactivator.php';
	AyurCRM_Deactivator::deactivate();
}
register_deactivation_hook( __FILE__, 'ayurcrm_deactivate' );

/**
 * Uninstall is handled by uninstall.php (registered automatically by WordPress
 * when the file exists at the plugin root).
 */
