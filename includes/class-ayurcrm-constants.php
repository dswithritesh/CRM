<?php
/**
 * AyurCRM Runtime Constants
 *
 * Defines constants that require WordPress context (e.g. upload dir, site URL)
 * and therefore cannot be set safely in the plugin root file before WordPress
 * loads. Called once by AyurCRM_Plugin::load_foundation_modules() on
 * plugins_loaded.
 *
 * Responsibilities:
 *  - Upload directory paths for imports and exports
 *  - Table name constants for all AyurCRM custom tables
 *  - Status and pipeline constants
 *  - Default option key constants
 *
 * All defines are guarded with defined() checks so this file is safe to
 * include multiple times without redeclaration errors.
 *
 * @package AyurCRM
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AyurCRM_Constants
 *
 * Static initialiser. Call AyurCRM_Constants::init() once on plugins_loaded
 * to define all runtime-context constants.
 */
class AyurCRM_Constants {

	/**
	 * Whether constants have already been initialised.
	 *
	 * @var bool
	 */
	private static $initialised = false;

	/**
	 * Initialise all runtime constants.
	 *
	 * Safe to call multiple times — only runs on the first call.
	 *
	 * @return void
	 */
	public static function init() {
		if ( self::$initialised ) {
			return;
		}

		self::define_upload_paths();
		self::define_table_names();
		self::define_status_constants();
		self::define_option_keys();

		self::$initialised = true;
	}

	// -----------------------------------------------------------------------
	// Upload paths
	// -----------------------------------------------------------------------

	/**
	 * Define upload-directory-based path constants for imports and exports.
	 *
	 * Uses wp_upload_dir() which requires WordPress to be loaded.
	 *
	 * @return void
	 */
	private static function define_upload_paths() {
		$upload = wp_upload_dir();
		$base   = trailingslashit( $upload['basedir'] ) . 'ayurcrm/';

		self::safe_define( 'AYURCRM_UPLOAD_DIR',         $base );
		self::safe_define( 'AYURCRM_IMPORT_DIR',         $base . 'imports/' );
		self::safe_define( 'AYURCRM_EXPORT_DIR',         $base . 'exports/' );
		self::safe_define( 'AYURCRM_UPLOAD_URL',         trailingslashit( $upload['baseurl'] ) . 'ayurcrm/' );
		self::safe_define( 'AYURCRM_IMPORT_URL',         trailingslashit( $upload['baseurl'] ) . 'ayurcrm/imports/' );
		self::safe_define( 'AYURCRM_EXPORT_URL',         trailingslashit( $upload['baseurl'] ) . 'ayurcrm/exports/' );
	}

	// -----------------------------------------------------------------------
	// Table names
	// -----------------------------------------------------------------------

	/**
	 * Define table name constants using the WordPress table prefix.
	 *
	 * @return void
	 */
	private static function define_table_names() {
		global $wpdb;
		$p = $wpdb->prefix;

		// Core tables
		self::safe_define( 'AYURCRM_TABLE_LEADS',              $p . 'ayurcrm_leads' );
		self::safe_define( 'AYURCRM_TABLE_LEAD_META',          $p . 'ayurcrm_lead_meta' );
		self::safe_define( 'AYURCRM_TABLE_ACTIVITIES',         $p . 'ayurcrm_activities' );
		self::safe_define( 'AYURCRM_TABLE_ASSIGNMENTS',        $p . 'ayurcrm_assignments' );
		self::safe_define( 'AYURCRM_TABLE_FOLLOWUPS',          $p . 'ayurcrm_followups' );

		// Import / export
		self::safe_define( 'AYURCRM_TABLE_IMPORTS',            $p . 'ayurcrm_imports' );
		self::safe_define( 'AYURCRM_TABLE_EXPORTS',            $p . 'ayurcrm_exports' );

		// System tables
		self::safe_define( 'AYURCRM_TABLE_LOGS',               $p . 'ayurcrm_logs' );
		self::safe_define( 'AYURCRM_TABLE_NOTIFICATIONS',      $p . 'ayurcrm_notifications' );
		self::safe_define( 'AYURCRM_TABLE_STATUS_REGISTRY',    $p . 'ayurcrm_status_registry' );
		self::safe_define( 'AYURCRM_TABLE_CUSTOM_FIELDS',      $p . 'ayurcrm_custom_fields' );
		self::safe_define( 'AYURCRM_TABLE_INTEGRATION_LOGS',   $p . 'ayurcrm_integration_logs' );
	}

	// -----------------------------------------------------------------------
	// Status constants
	// -----------------------------------------------------------------------

	/**
	 * Define lead status slug constants.
	 *
	 * These are the default pipeline stages. Custom stages can be added via
	 * the status registry table; these constants represent the system defaults.
	 *
	 * @return void
	 */
	private static function define_status_constants() {
		// Pipeline stages
		self::safe_define( 'AYURCRM_STATUS_NEW',                  'new' );
		self::safe_define( 'AYURCRM_STATUS_CONTACTED',            'contacted' );
		self::safe_define( 'AYURCRM_STATUS_INTERESTED',           'interested' );
		self::safe_define( 'AYURCRM_STATUS_QUALIFIED',            'qualified' );
		self::safe_define( 'AYURCRM_STATUS_CONSULTATION_BOOKED',  'consultation_booked' );
		self::safe_define( 'AYURCRM_STATUS_FOLLOWUP_PENDING',     'followup_pending' );
		self::safe_define( 'AYURCRM_STATUS_NOT_RESPONDING',       'not_responding' );
		self::safe_define( 'AYURCRM_STATUS_CONVERTED',            'converted' );
		self::safe_define( 'AYURCRM_STATUS_LOST',                 'lost' );

		// Lead temperature
		self::safe_define( 'AYURCRM_TEMP_HOT',   'hot' );
		self::safe_define( 'AYURCRM_TEMP_WARM',  'warm' );
		self::safe_define( 'AYURCRM_TEMP_COLD',  'cold' );

		// Lead priority
		self::safe_define( 'AYURCRM_PRIORITY_HIGH',    'high' );
		self::safe_define( 'AYURCRM_PRIORITY_MEDIUM',  'medium' );
		self::safe_define( 'AYURCRM_PRIORITY_LOW',     'low' );

		// Activity types
		self::safe_define( 'AYURCRM_ACTIVITY_NOTE',           'note' );
		self::safe_define( 'AYURCRM_ACTIVITY_CALL',           'call' );
		self::safe_define( 'AYURCRM_ACTIVITY_EMAIL',          'email' );
		self::safe_define( 'AYURCRM_ACTIVITY_WHATSAPP',       'whatsapp' );
		self::safe_define( 'AYURCRM_ACTIVITY_STATUS_CHANGE',  'status_change' );
		self::safe_define( 'AYURCRM_ACTIVITY_ASSIGNMENT',     'assignment' );
		self::safe_define( 'AYURCRM_ACTIVITY_FOLLOWUP',       'followup' );
		self::safe_define( 'AYURCRM_ACTIVITY_IMPORT',         'import' );
		self::safe_define( 'AYURCRM_ACTIVITY_SYSTEM',         'system' );

		// Call outcomes
		self::safe_define( 'AYURCRM_CALL_NO_ANSWER',     'no_answer' );
		self::safe_define( 'AYURCRM_CALL_BUSY',          'busy' );
		self::safe_define( 'AYURCRM_CALL_SWITCHED_OFF',  'switched_off' );
		self::safe_define( 'AYURCRM_CALL_INTERESTED',    'interested' );
		self::safe_define( 'AYURCRM_CALL_CALLBACK',      'callback' );
		self::safe_define( 'AYURCRM_CALL_CONVERTED',     'converted' );
		self::safe_define( 'AYURCRM_CALL_LOST',          'lost' );
	}

	// -----------------------------------------------------------------------
	// Option key constants
	// -----------------------------------------------------------------------

	/**
	 * Define WP options key constants used by AyurCRM.
	 *
	 * Centralising option keys here prevents typo-driven bugs and makes
	 * searching for option usage trivial.
	 *
	 * @return void
	 */
	private static function define_option_keys() {
		self::safe_define( 'AYURCRM_OPT_DB_VERSION',          'ayurcrm_db_version' );
		self::safe_define( 'AYURCRM_OPT_ACTIVATION_DATE',     'ayurcrm_activation_date' );
		self::safe_define( 'AYURCRM_OPT_PLUGIN_ACTIVE',       'ayurcrm_plugin_active' );
		self::safe_define( 'AYURCRM_OPT_SETTINGS',            'ayurcrm_settings' );
		self::safe_define( 'AYURCRM_OPT_NOTIFICATION_PREFS',  'ayurcrm_notification_prefs' );
		self::safe_define( 'AYURCRM_OPT_PIPELINE_CONFIG',     'ayurcrm_pipeline_config' );
		self::safe_define( 'AYURCRM_OPT_BRANCH_CONFIG',       'ayurcrm_branch_config' );
		self::safe_define( 'AYURCRM_OPT_ASSIGNMENT_RULES',    'ayurcrm_assignment_rules' );
		self::safe_define( 'AYURCRM_OPT_SLA_CONFIG',          'ayurcrm_sla_config' );
		self::safe_define( 'AYURCRM_OPT_INTEGRATION_CONFIG',  'ayurcrm_integration_config' );
		self::safe_define( 'AYURCRM_OPT_IMPORT_PRESETS',      'ayurcrm_import_presets' );
		self::safe_define( 'AYURCRM_OPT_ROUND_ROBIN_INDEX',   'ayurcrm_round_robin_index' );

		// Transient keys (not wp_options, but referenced as constants)
		self::safe_define( 'AYURCRM_TRANSIENT_NEEDS_MIGRATION',  'ayurcrm_needs_migration' );
		self::safe_define( 'AYURCRM_TRANSIENT_MIGRATION_LOCK',   'ayurcrm_migration_lock' );
		self::safe_define( 'AYURCRM_TRANSIENT_DASHBOARD_TODAY',  'ayurcrm_cache_dashboard_today' );
		self::safe_define( 'AYURCRM_TRANSIENT_DASHBOARD_STATS',  'ayurcrm_cache_dashboard_stats' );
	}

	// -----------------------------------------------------------------------
	// Utility
	// -----------------------------------------------------------------------

	/**
	 * Define a constant only if it has not already been defined.
	 *
	 * @param string $name  Constant name.
	 * @param mixed  $value Constant value.
	 * @return void
	 */
	private static function safe_define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * Return all default pipeline statuses as an ordered array.
	 *
	 * Each entry: [ 'slug' => string, 'label' => string, 'color' => string ]
	 *
	 * @return array[]
	 */
	public static function get_default_statuses() {
		return array(
			array( 'slug' => AYURCRM_STATUS_NEW,                 'label' => 'New',                  'color' => '#6B7280' ),
			array( 'slug' => AYURCRM_STATUS_CONTACTED,           'label' => 'Contacted',             'color' => '#3B82F6' ),
			array( 'slug' => AYURCRM_STATUS_INTERESTED,          'label' => 'Interested',            'color' => '#8B5CF6' ),
			array( 'slug' => AYURCRM_STATUS_QUALIFIED,           'label' => 'Qualified',             'color' => '#F59E0B' ),
			array( 'slug' => AYURCRM_STATUS_CONSULTATION_BOOKED, 'label' => 'Consultation Booked',   'color' => '#10B981' ),
			array( 'slug' => AYURCRM_STATUS_FOLLOWUP_PENDING,    'label' => 'Follow-up Pending',     'color' => '#F97316' ),
			array( 'slug' => AYURCRM_STATUS_NOT_RESPONDING,      'label' => 'Not Responding',        'color' => '#EF4444' ),
			array( 'slug' => AYURCRM_STATUS_CONVERTED,           'label' => 'Converted',             'color' => '#059669' ),
			array( 'slug' => AYURCRM_STATUS_LOST,                'label' => 'Lost',                  'color' => '#DC2626' ),
		);
	}

	/**
	 * Return all call outcome options as slug => label map.
	 *
	 * @return string[]
	 */
	public static function get_call_outcomes() {
		return array(
			AYURCRM_CALL_NO_ANSWER    => 'No Answer',
			AYURCRM_CALL_BUSY         => 'Busy',
			AYURCRM_CALL_SWITCHED_OFF => 'Switched Off',
			AYURCRM_CALL_INTERESTED   => 'Interested',
			AYURCRM_CALL_CALLBACK     => 'Callback Requested',
			AYURCRM_CALL_CONVERTED    => 'Converted',
			AYURCRM_CALL_LOST         => 'Lost',
		);
	}

	/**
	 * Return all activity type slugs as a flat array.
	 *
	 * @return string[]
	 */
	public static function get_activity_types() {
		return array(
			AYURCRM_ACTIVITY_NOTE,
			AYURCRM_ACTIVITY_CALL,
			AYURCRM_ACTIVITY_EMAIL,
			AYURCRM_ACTIVITY_WHATSAPP,
			AYURCRM_ACTIVITY_STATUS_CHANGE,
			AYURCRM_ACTIVITY_ASSIGNMENT,
			AYURCRM_ACTIVITY_FOLLOWUP,
			AYURCRM_ACTIVITY_IMPORT,
			AYURCRM_ACTIVITY_SYSTEM,
		);
	}
}
