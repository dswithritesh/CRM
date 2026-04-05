<?php
/**
 * AyurCRM Uninstall Handler
 *
 * Runs only when WordPress calls the plugin uninstall routine.
 * Conditionally removes all plugin data based on the user's setting.
 *
 * @package AyurCRM
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

/**
 * Read the general settings option.
 * Use a direct DB call — class autoloader is not available at uninstall time.
 */
$general_settings = get_option( 'ayurcrm_settings_general', array() );

$remove_data = ! empty( $general_settings['remove_data_on_uninstall'] );

if ( $remove_data ) {
	/**
	 * Drop all plugin tables in FK-safe order (child tables first).
	 *
	 * Uses IF EXISTS so there is no fatal error if a table was never created.
	 */
	$tables = array(
		'ayurcrm_integration_logs',
		'ayurcrm_notification_queue',
		'ayurcrm_logs',
		'ayurcrm_exports',
		'ayurcrm_import_rows',
		'ayurcrm_imports',
		'ayurcrm_lead_meta',
		'ayurcrm_status_registry',
		'ayurcrm_lead_followups',
		'ayurcrm_lead_assignments',
		'ayurcrm_lead_activities',
		'ayurcrm_leads',
	);

	foreach ( $tables as $table ) {
		$full_table = $wpdb->prefix . $table;
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query( "DROP TABLE IF EXISTS `{$full_table}`" );
	}

	// Remove all custom CRM roles from the database.
	$crm_roles = array(
		'ayurcrm_super_admin',
		'ayurcrm_admin',
		'ayurcrm_branch_manager',
		'ayurcrm_doctor',
		'ayurcrm_counselor',
		'ayurcrm_sales',
		'ayurcrm_viewer',
	);

	foreach ( $crm_roles as $role ) {
		remove_role( $role );
	}
}

/**
 * Always remove all plugin options regardless of the remove_data setting.
 *
 * Options are lightweight metadata — leaving them behind would be unexpected
 * for the user who has chosen to remove the plugin.
 */
$options = array(
	'ayurcrm_db_version',
	'ayurcrm_settings_general',
	'ayurcrm_settings_pipeline',
	'ayurcrm_settings_notifications',
	'ayurcrm_settings_integrations',
	'ayurcrm_migration_lock',
	'ayurcrm_needs_setup',
	'ayurcrm_activation_date',
	'ayurcrm_plugin_active',
);

foreach ( $options as $option ) {
	delete_option( $option );
}

/**
 * Log upload directory reference (do NOT delete files without an explicit setting).
 *
 * If the user has enabled data removal, we log the upload path so an
 * administrator can manually clean it up.
 */
if ( $remove_data ) {
	$upload_info = wp_upload_dir();
	if ( empty( $upload_info['error'] ) ) {
		$upload_dir = $upload_info['basedir'] . '/ayurcrm';
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( 'AyurCRM uninstalled. Upload directory was: ' . $upload_dir . ' — please remove manually if no longer needed.' );
	}
}
