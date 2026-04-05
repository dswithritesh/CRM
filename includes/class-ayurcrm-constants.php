<?php
/**
 * AyurCRM Runtime Constants
 *
 * Centralises all runtime configuration values that are not already defined
 * in the main plugin file (ayurcrm.php). This file is loaded early in the
 * bootstrap chain and must not perform any DB queries or produce output.
 *
 * Values defined here:
 *  - Upload / storage directory paths
 *  - Default pipeline stages and sub-statuses
 *  - Lead temperature labels
 *  - Lead score thresholds
 *  - Default source list
 *  - Pagination defaults
 *  - Activity type registry
 *  - Communication outcome options
 *  - Table name helpers (via static methods)
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
 * All values are exposed as public class constants or static methods.
 * No instance of this class is ever created.
 */
class AyurCRM_Constants {

	// -----------------------------------------------------------------------
	// Upload / storage paths
	// -----------------------------------------------------------------------

	/**
	 * Sub-directory inside wp-content/uploads for AyurCRM files.
	 * Does NOT include a trailing slash.
	 */
	const UPLOAD_SUBDIR = 'ayurcrm';

	/**
	 * Sub-directory for import staging files.
	 */
	const IMPORT_SUBDIR = 'ayurcrm/imports';

	/**
	 * Sub-directory for export output files.
	 */
	const EXPORT_SUBDIR = 'ayurcrm/exports';

	// -----------------------------------------------------------------------
	// Pagination
	// -----------------------------------------------------------------------

	const LEADS_PER_PAGE       = 25;
	const ACTIVITIES_PER_PAGE  = 50;
	const IMPORTS_PER_PAGE     = 20;
	const EXPORTS_PER_PAGE     = 20;
	const LOGS_PER_PAGE        = 50;

	// -----------------------------------------------------------------------
	// Default pipeline stages
	// -----------------------------------------------------------------------

	/**
	 * Ordered default pipeline stage slugs.
	 * These are the seed values — admins can customise via the pipeline manager.
	 *
	 * @return string[]
	 */
	public static function default_pipeline_stages() {
		return array(
			'new',
			'contacted',
			'interested',
			'qualified',
			'consultation_booked',
			'followup_pending',
			'not_responding',
			'converted',
			'lost',
		);
	}

	/**
	 * Full pipeline stage definitions with labels and colours.
	 *
	 * @return array[]  [ slug => [ label, color, is_terminal ] ]
	 */
	public static function default_pipeline_stage_definitions() {
		return array(
			'new'                  => array( 'label' => 'New',                  'color' => '#6366f1', 'is_terminal' => false ),
			'contacted'            => array( 'label' => 'Contacted',            'color' => '#3b82f6', 'is_terminal' => false ),
			'interested'           => array( 'label' => 'Interested',           'color' => '#f59e0b', 'is_terminal' => false ),
			'qualified'            => array( 'label' => 'Qualified',            'color' => '#10b981', 'is_terminal' => false ),
			'consultation_booked'  => array( 'label' => 'Consultation Booked',  'color' => '#8b5cf6', 'is_terminal' => false ),
			'followup_pending'     => array( 'label' => 'Follow-up Pending',    'color' => '#f97316', 'is_terminal' => false ),
			'not_responding'       => array( 'label' => 'Not Responding',       'color' => '#94a3b8', 'is_terminal' => false ),
			'converted'            => array( 'label' => 'Converted',            'color' => '#22c55e', 'is_terminal' => true  ),
			'lost'                 => array( 'label' => 'Lost',                 'color' => '#ef4444', 'is_terminal' => true  ),
		);
	}

	// -----------------------------------------------------------------------
	// Lead temperature
	// -----------------------------------------------------------------------

	/**
	 * Lead temperature options.
	 *
	 * @return array[]  [ slug => label ]
	 */
	public static function lead_temperature_options() {
		return array(
			'hot'     => 'Hot',
			'warm'    => 'Warm',
			'cold'    => 'Cold',
			'unknown' => 'Unknown',
		);
	}

	// -----------------------------------------------------------------------
	// Lead score thresholds
	// -----------------------------------------------------------------------

	const LEAD_SCORE_HOT_MIN  = 70;
	const LEAD_SCORE_WARM_MIN = 40;
	// Below WARM_MIN = cold.

	/**
	 * Derive lead temperature string from a numeric score.
	 *
	 * @param int|float $score
	 * @return string  'hot'|'warm'|'cold'
	 */
	public static function temperature_from_score( $score ) {
		$score = (int) $score;
		if ( $score >= self::LEAD_SCORE_HOT_MIN ) {
			return 'hot';
		}
		if ( $score >= self::LEAD_SCORE_WARM_MIN ) {
			return 'warm';
		}
		return 'cold';
	}

	// -----------------------------------------------------------------------
	// Lead priority
	// -----------------------------------------------------------------------

	/**
	 * Lead priority options.
	 *
	 * @return array[]  [ slug => label ]
	 */
	public static function lead_priority_options() {
		return array(
			'high'   => 'High',
			'medium' => 'Medium',
			'low'    => 'Low',
		);
	}

	// -----------------------------------------------------------------------
	// Source options
	// -----------------------------------------------------------------------

	/**
	 * Default lead source options.
	 *
	 * @return array[]  [ slug => label ]
	 */
	public static function default_source_options() {
		return array(
			'website'         => 'Website',
			'landing_page'    => 'Landing Page',
			'whatsapp'        => 'WhatsApp',
			'facebook_ad'     => 'Facebook Ad',
			'google_ad'       => 'Google Ad',
			'instagram_ad'    => 'Instagram Ad',
			'organic_search'  => 'Organic Search',
			'referral'        => 'Referral',
			'walkin'          => 'Walk-in',
			'phone_call'      => 'Phone Call',
			'assessment'      => 'Assessment / Quiz',
			'csv_import'      => 'CSV Import',
			'manual_entry'    => 'Manual Entry',
			'webhook'         => 'Webhook / API',
			'other'           => 'Other',
		);
	}

	// -----------------------------------------------------------------------
	// Activity types
	// -----------------------------------------------------------------------

	/**
	 * Activity type constants — used in the lead_activities table `type` column.
	 */
	const ACTIVITY_NOTE              = 'note';
	const ACTIVITY_STATUS_CHANGE     = 'status_change';
	const ACTIVITY_ASSIGNMENT        = 'assignment';
	const ACTIVITY_FOLLOWUP          = 'followup';
	const ACTIVITY_CALL_LOG          = 'call_log';
	const ACTIVITY_EMAIL_LOG         = 'email_log';
	const ACTIVITY_WHATSAPP_LOG      = 'whatsapp_log';
	const ACTIVITY_IMPORT            = 'import';
	const ACTIVITY_EXPORT            = 'export';
	const ACTIVITY_DUPLICATE_FLAG    = 'duplicate_flag';
	const ACTIVITY_MERGE             = 'merge';
	const ACTIVITY_FIELD_UPDATE      = 'field_update';
	const ACTIVITY_WEBHOOK_RECEIVED  = 'webhook_received';
	const ACTIVITY_WEBHOOK_SENT      = 'webhook_sent';
	const ACTIVITY_SLA_BREACH        = 'sla_breach';
	const ACTIVITY_SYSTEM            = 'system';

	/**
	 * All registered activity types as slug => label map.
	 *
	 * @return array[]
	 */
	public static function activity_type_labels() {
		return array(
			self::ACTIVITY_NOTE             => 'Note',
			self::ACTIVITY_STATUS_CHANGE    => 'Status Change',
			self::ACTIVITY_ASSIGNMENT       => 'Assignment',
			self::ACTIVITY_FOLLOWUP         => 'Follow-up',
			self::ACTIVITY_CALL_LOG         => 'Call Log',
			self::ACTIVITY_EMAIL_LOG        => 'Email Log',
			self::ACTIVITY_WHATSAPP_LOG     => 'WhatsApp Log',
			self::ACTIVITY_IMPORT           => 'Import',
			self::ACTIVITY_EXPORT           => 'Export',
			self::ACTIVITY_DUPLICATE_FLAG   => 'Duplicate Flagged',
			self::ACTIVITY_MERGE            => 'Merge',
			self::ACTIVITY_FIELD_UPDATE     => 'Field Update',
			self::ACTIVITY_WEBHOOK_RECEIVED => 'Webhook Received',
			self::ACTIVITY_WEBHOOK_SENT     => 'Webhook Sent',
			self::ACTIVITY_SLA_BREACH       => 'SLA Breach',
			self::ACTIVITY_SYSTEM           => 'System',
		);
	}

	// -----------------------------------------------------------------------
	// Communication / call outcomes
	// -----------------------------------------------------------------------

	/**
	 * Call outcome options for call logging.
	 *
	 * @return array[]  [ slug => label ]
	 */
	public static function call_outcome_options() {
		return array(
			'no_answer'       => 'No Answer',
			'busy'            => 'Busy',
			'switched_off'    => 'Switched Off',
			'not_reachable'   => 'Not Reachable',
			'interested'      => 'Interested',
			'callback_later'  => 'Callback Later',
			'not_interested'  => 'Not Interested',
			'converted'       => 'Converted',
			'wrong_number'    => 'Wrong Number',
			'left_voicemail'  => 'Left Voicemail',
		);
	}

	// -----------------------------------------------------------------------
	// Gender options
	// -----------------------------------------------------------------------

	/**
	 * Gender options for lead capture forms.
	 *
	 * @return array[]  [ slug => label ]
	 */
	public static function gender_options() {
		return array(
			'male'              => 'Male',
			'female'            => 'Female',
			'other'             => 'Other',
			'prefer_not_to_say' => 'Prefer Not to Say',
		);
	}

	// -----------------------------------------------------------------------
	// Device type options
	// -----------------------------------------------------------------------

	/**
	 * Device type options captured at lead submission time.
	 *
	 * @return array[]  [ slug => label ]
	 */
	public static function device_type_options() {
		return array(
			'mobile'  => 'Mobile',
			'tablet'  => 'Tablet',
			'desktop' => 'Desktop',
			'unknown' => 'Unknown',
		);
	}

	// -----------------------------------------------------------------------
	// Database table name helpers
	// -----------------------------------------------------------------------

	/**
	 * Return the full (prefixed) name of the leads table.
	 *
	 * @return string
	 */
	public static function table_leads() {
		global $wpdb;
		return $wpdb->prefix . 'ayurcrm_leads';
	}

	/**
	 * Return the full (prefixed) name of the lead activities table.
	 *
	 * @return string
	 */
	public static function table_activities() {
		global $wpdb;
		return $wpdb->prefix . 'ayurcrm_lead_activities';
	}

	/**
	 * Return the full (prefixed) name of the lead assignments table.
	 *
	 * @return string
	 */
	public static function table_assignments() {
		global $wpdb;
		return $wpdb->prefix . 'ayurcrm_lead_assignments';
	}

	/**
	 * Return the full (prefixed) name of the follow-ups table.
	 *
	 * @return string
	 */
	public static function table_followups() {
		global $wpdb;
		return $wpdb->prefix . 'ayurcrm_followups';
	}

	/**
	 * Return the full (prefixed) name of the imports table.
	 *
	 * @return string
	 */
	public static function table_imports() {
		global $wpdb;
		return $wpdb->prefix . 'ayurcrm_imports';
	}

	/**
	 * Return the full (prefixed) name of the exports table.
	 *
	 * @return string
	 */
	public static function table_exports() {
		global $wpdb;
		return $wpdb->prefix . 'ayurcrm_exports';
	}

	/**
	 * Return the full (prefixed) name of the logs table.
	 *
	 * @return string
	 */
	public static function table_logs() {
		global $wpdb;
		return $wpdb->prefix . 'ayurcrm_logs';
	}

	/**
	 * Return the full (prefixed) name of the lead meta table.
	 *
	 * @return string
	 */
	public static function table_lead_meta() {
		global $wpdb;
		return $wpdb->prefix . 'ayurcrm_lead_meta';
	}

	/**
	 * Return an associative map of all AyurCRM table names.
	 *
	 * Used by the migrator and DB class to iterate all tables.
	 *
	 * @return string[]  [ key => full_table_name ]
	 */
	public static function all_tables() {
		return array(
			'leads'       => self::table_leads(),
			'activities'  => self::table_activities(),
			'assignments' => self::table_assignments(),
			'followups'   => self::table_followups(),
			'imports'     => self::table_imports(),
			'exports'     => self::table_exports(),
			'logs'        => self::table_logs(),
			'lead_meta'   => self::table_lead_meta(),
		);
	}

	// -----------------------------------------------------------------------
	// Upload directory helpers
	// -----------------------------------------------------------------------

	/**
	 * Return the absolute filesystem path to the AyurCRM uploads directory.
	 * Creates the directory if it does not exist.
	 *
	 * @param string $subdir  One of the SUBDIR constants or a custom relative path.
	 * @return string  Absolute path with trailing slash.
	 */
	public static function get_upload_dir( $subdir = self::UPLOAD_SUBDIR ) {
		$upload_dir = wp_upload_dir();
		$path       = trailingslashit( $upload_dir['basedir'] ) . ltrim( $subdir, '/' );

		if ( ! file_exists( $path ) ) {
			wp_mkdir_p( $path );

			// Drop an .htaccess to block direct HTTP access to uploads.
			$htaccess = $path . '/.htaccess';
			if ( ! file_exists( $htaccess ) ) {
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
				file_put_contents( $htaccess, "Options -Indexes\ndeny from all\n" );
			}
		}

		return trailingslashit( $path );
	}

	/**
	 * Return the public URL for the AyurCRM uploads directory.
	 *
	 * Note: files in this directory are blocked by .htaccess on Apache.
	 * Do NOT expose raw import/export files via this URL in production.
	 *
	 * @param string $subdir
	 * @return string  URL with trailing slash.
	 */
	public static function get_upload_url( $subdir = self::UPLOAD_SUBDIR ) {
		$upload_dir = wp_upload_dir();
		return trailingslashit( $upload_dir['baseurl'] ) . ltrim( $subdir, '/' ) . '/';
	}

	// -----------------------------------------------------------------------
	// Option key constants
	// -----------------------------------------------------------------------

	const OPTION_DB_VERSION         = 'ayurcrm_db_version';
	const OPTION_PLUGIN_ACTIVE      = 'ayurcrm_plugin_active';
	const OPTION_ACTIVATION_DATE    = 'ayurcrm_activation_date';
	const OPTION_SETTINGS           = 'ayurcrm_settings';
	const OPTION_PIPELINE_STAGES    = 'ayurcrm_pipeline_stages';
	const OPTION_NOTIFICATION_PREFS = 'ayurcrm_notification_prefs';
	const TRANSIENT_NEEDS_MIGRATION = 'ayurcrm_needs_migration';
	const TRANSIENT_MIGRATION_LOCK  = 'ayurcrm_migration_lock';
}
