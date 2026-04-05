<?php
/**
 * AyurCRM Default Settings
 *
 * Returns the default settings array used when no configuration has been
 * saved yet. Values are organised by section to match the settings page tabs.
 *
 * Usage:
 *   $defaults = require AYURCRM_PATH . 'config/default-settings.php';
 *
 * @package AyurCRM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(

	// -------------------------------------------------------------------------
	// General settings
	// -------------------------------------------------------------------------
	'general' => array(
		'crm_name'                 => 'AyurCRM',
		'company_name'             => '',
		'admin_email'              => get_option( 'admin_email' ),
		'timezone'                 => get_option( 'timezone_string', 'Asia/Kolkata' ),
		'date_format'              => 'd/m/Y',
		'time_format'              => 'H:i',
		'leads_per_page'           => 25,
		'default_lead_status'      => 'new',
		'default_lead_temperature' => 'unknown',
		'default_country'          => 'India',

		/**
		 * Duplicate-detection strategy.
		 * block  — reject the incoming lead entirely.
		 * flag   — create the lead but mark it as a suspected duplicate.
		 * append — merge duplicate fields into the existing record.
		 */
		'duplicate_strategy'       => 'flag',

		'auto_assign_enabled'      => false,

		/**
		 * When true, uninstall.php will drop all plugin tables and remove all
		 * data. Defaults to false so accidental deactivation cannot lose data.
		 */
		'remove_data_on_uninstall' => false,

		'enable_rest_api'          => true,
		'rest_api_key_required'    => false,
		'enable_webhook_receiver'  => true,
	),

	// -------------------------------------------------------------------------
	// Pipeline settings
	// Populated from the status_registry table at runtime; defaults defined in
	// config/default-pipeline.php and seeded during initial migration.
	// -------------------------------------------------------------------------
	'pipeline' => array(),

	// -------------------------------------------------------------------------
	// Notification settings
	// -------------------------------------------------------------------------
	'notifications' => array(
		'new_lead_notify_admin'      => true,
		'new_lead_notify_assigned'   => true,
		'assignment_notify_user'     => true,
		'followup_reminder_enabled'  => true,
		'followup_overdue_notify'    => true,
		'sla_breach_notify'          => true,
		'import_complete_notify'     => true,
		'export_ready_notify'        => true,

		/**
		 * Sender identity for outgoing email notifications.
		 * Leave empty to fall back to WordPress site name / admin email.
		 */
		'notification_from_name'     => '',
		'notification_from_email'    => '',
	),

	// -------------------------------------------------------------------------
	// Integration settings
	// -------------------------------------------------------------------------
	'integrations' => array(
		'webhook_secret'         => '',

		/**
		 * Array of REST API key objects: [ ['key' => '...', 'label' => '...', 'created' => timestamp], ... ]
		 */
		'rest_api_keys'          => array(),

		'google_sheets_enabled'  => false,
		'meta_leads_enabled'     => false,
		'whatsapp_enabled'       => false,
		'telephony_enabled'      => false,
	),
);
