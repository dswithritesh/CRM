<?php
/**
 * AyurCRM Hooks Registry
 *
 * Canonical registry of every WordPress action and filter hook used by the
 * plugin. All hook name strings are defined here as class constants so that:
 *
 *  1. There are no magic strings scattered across the codebase.
 *  2. Hook names can be discovered via IDE auto-complete.
 *  3. Renaming a hook requires changing only one constant.
 *
 * Usage:
 *   do_action( AyurCRM_Hooks::LEAD_CREATED, $lead_id, $lead_data );
 *   apply_filters( AyurCRM_Hooks::LEAD_DATA_BEFORE_SAVE, $data );
 *
 * Or use the static helpers:
 *   AyurCRM_Hooks::do_action( AyurCRM_Hooks::LEAD_CREATED, $lead_id );
 *   AyurCRM_Hooks::apply_filters( AyurCRM_Hooks::LEAD_SCORE, $score, $lead );
 *
 * @package AyurCRM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AyurCRM_Hooks
 *
 * Central registry of all plugin hook name constants plus static proxy
 * helpers that delegate to WordPress core functions.
 */
class AyurCRM_Hooks {

	// =========================================================================
	// ACTION HOOK CONSTANTS
	// =========================================================================

	// --- Lead lifecycle ---

	/** Fired immediately before a new lead record is inserted. */
	const LEAD_BEFORE_CREATE = 'ayurcrm_lead_before_create';

	/** Fired after a new lead record has been successfully inserted. */
	const LEAD_CREATED = 'ayurcrm_lead_created';

	/** Fired immediately before an existing lead record is updated. */
	const LEAD_BEFORE_UPDATE = 'ayurcrm_lead_before_update';

	/** Fired after a lead record has been successfully updated. */
	const LEAD_UPDATED = 'ayurcrm_lead_updated';

	/** Fired after a lead record has been permanently deleted. */
	const LEAD_DELETED = 'ayurcrm_lead_deleted';

	/** Fired after a soft-deleted lead has been restored. */
	const LEAD_RESTORED = 'ayurcrm_lead_restored';

	/** Fired after a lead has been archived (non-destructive hide). */
	const LEAD_ARCHIVED = 'ayurcrm_lead_archived';

	/** Fired after a lead's pipeline status changes. */
	const LEAD_STATUS_CHANGED = 'ayurcrm_lead_status_changed';

	/** Fired after a lead is created or updated via import. */
	const LEAD_IMPORTED = 'ayurcrm_lead_imported';

	/** Fired after two lead records are merged into one. */
	const LEADS_MERGED = 'ayurcrm_leads_merged';

	// --- Lead assignment ---

	/** Fired after a lead is assigned to a user for the first time. */
	const LEAD_ASSIGNED = 'ayurcrm_lead_assigned';

	/** Fired after a lead is reassigned from one user to another. */
	const LEAD_REASSIGNED = 'ayurcrm_lead_reassigned';

	// --- Follow-up lifecycle ---

	/** Fired after a follow-up is scheduled for a lead. */
	const FOLLOWUP_SCHEDULED = 'ayurcrm_followup_scheduled';

	/** Fired after a follow-up is marked as completed. */
	const FOLLOWUP_COMPLETED = 'ayurcrm_followup_completed';

	/** Fired when a follow-up is detected as missed/overdue. */
	const FOLLOWUP_MISSED = 'ayurcrm_followup_missed';

	/** Fired after a follow-up reminder notification has been dispatched. */
	const FOLLOWUP_REMINDER_SENT = 'ayurcrm_followup_reminder_sent';

	// --- Notes & activity ---

	/** Fired after a note is added to a lead activity log. */
	const NOTE_ADDED = 'ayurcrm_note_added';

	/** Fired after any activity entry is logged against a lead. */
	const ACTIVITY_LOGGED = 'ayurcrm_activity_logged';

	// --- Import / Export ---

	/** Fired when an import batch starts processing. */
	const IMPORT_STARTED = 'ayurcrm_import_started';

	/** Fired when an import batch finishes successfully. */
	const IMPORT_COMPLETED = 'ayurcrm_import_completed';

	/** Fired when an import batch encounters a fatal error. */
	const IMPORT_FAILED = 'ayurcrm_import_failed';

	/** Fired after an export file has been generated and is ready. */
	const EXPORT_GENERATED = 'ayurcrm_export_generated';

	// --- Duplicate detection ---

	/** Fired when a potential duplicate lead is detected during capture/import. */
	const DUPLICATE_DETECTED = 'ayurcrm_duplicate_detected';

	// --- Notifications ---

	/** Fired when a notification event is triggered (before sending). */
	const NOTIFICATION_TRIGGERED = 'ayurcrm_notification_triggered';

	/** Fired after a notification has been successfully dispatched. */
	const NOTIFICATION_SENT = 'ayurcrm_notification_sent';

	// --- Integrations ---

	/** Fired after an inbound webhook payload has been received and validated. */
	const WEBHOOK_RECEIVED = 'ayurcrm_webhook_received';

	/** Fired after an outbound webhook has been sent to a third-party endpoint. */
	const WEBHOOK_SENT = 'ayurcrm_webhook_sent';

	// --- SLA ---

	/** Fired when a lead's SLA response window has been breached. */
	const SLA_BREACHED = 'ayurcrm_sla_breached';

	// --- Admin ---

	/** Fired after any plugin settings section is saved. */
	const SETTINGS_SAVED = 'ayurcrm_settings_saved';

	/** Fired after a database migration batch completes. */
	const MIGRATION_COMPLETED = 'ayurcrm_migration_completed';

	// =========================================================================
	// FILTER HOOK CONSTANTS
	// =========================================================================

	// --- Lead data ---

	/** Filter lead data array before it is written to the database. */
	const LEAD_DATA_BEFORE_SAVE = 'ayurcrm_lead_data_before_save';

	/** Filter lead data array after it is loaded from the database. */
	const LEAD_DATA_AFTER_LOAD = 'ayurcrm_lead_data_after_load';

	/** Filter the hash string used for duplicate-detection. */
	const DUPLICATE_HASH = 'ayurcrm_duplicate_hash';

	/** Filter a lead's calculated score. */
	const LEAD_SCORE = 'ayurcrm_lead_score';

	/** Filter a lead's temperature value (hot/warm/cold/unknown). */
	const LEAD_TEMPERATURE = 'ayurcrm_lead_temperature';

	// --- Assignment ---

	/** Filter the user ID that a lead will be auto-assigned to. */
	const ASSIGNMENT_TARGET_USER = 'ayurcrm_assignment_target_user';

	/** Filter the list of user IDs eligible for auto-assignment. */
	const ASSIGNMENT_ELIGIBLE_USERS = 'ayurcrm_assignment_eligible_users';

	// --- Pipeline ---

	/** Filter the array of pipeline stage definitions. */
	const PIPELINE_STAGES = 'ayurcrm_pipeline_stages';

	/** Filter the map of allowed status transitions for a lead. */
	const ALLOWED_STATUS_TRANSITIONS = 'ayurcrm_allowed_status_transitions';

	// --- Import / Export ---

	/** Filter the column map used when importing a CSV file. */
	const IMPORT_COLUMN_MAP = 'ayurcrm_import_column_map';

	/** Filter a single row of data during import processing. */
	const IMPORT_ROW_DATA = 'ayurcrm_import_row_data';

	/** Filter the list of columns included in an export file. */
	const EXPORT_COLUMNS = 'ayurcrm_export_columns';

	/** Filter a single row of data during export generation. */
	const EXPORT_ROW = 'ayurcrm_export_row';

	// --- Notifications ---

	/** Filter the list of recipient email addresses for a notification. */
	const NOTIFICATION_RECIPIENTS = 'ayurcrm_notification_recipients';

	/** Filter the notification template body. */
	const NOTIFICATION_TEMPLATE = 'ayurcrm_notification_template';

	/** Filter the notification email subject line. */
	const NOTIFICATION_SUBJECT = 'ayurcrm_notification_subject';

	// --- Access control ---

	/** Filter whether the current user can view a specific lead. */
	const USER_CAN_VIEW_LEAD = 'ayurcrm_user_can_view_lead';

	// --- Queries ---

	/** Filter the WP_Query / direct DB query args for the leads list. */
	const LEADS_QUERY_ARGS = 'ayurcrm_leads_query_args';

	// --- Dashboard ---

	/** Filter the statistics array displayed on the dashboard. */
	const DASHBOARD_STATS = 'ayurcrm_dashboard_stats';

	/** Filter the array of dashboard card component definitions. */
	const DASHBOARD_CARDS = 'ayurcrm_dashboard_cards';

	// --- REST API ---

	/** Filter a lead object before it is returned by the REST API. */
	const REST_LEAD_RESPONSE = 'ayurcrm_rest_lead_response';

	/** Filter capture form data submitted via the REST capture endpoint. */
	const REST_CAPTURE_DATA = 'ayurcrm_rest_capture_data';

	// --- Architecture ---

	/** Filter the list of registered plugin module files to load. */
	const REGISTERED_MODULES = 'ayurcrm_registered_modules';

	/** Filter the role → capabilities map before roles are registered. */
	const CAPABILITIES_MAP = 'ayurcrm_capabilities_map';

	// =========================================================================
	// STATIC PROXY HELPERS
	// =========================================================================

	/**
	 * Fire a plugin action hook.
	 *
	 * Proxy for WordPress core do_action() that accepts a hook constant value.
	 *
	 * @param string $hook_constant Value of one of the ACTION hook constants.
	 * @param mixed  ...$args       Additional arguments passed to the hook.
	 * @return void
	 */
	public static function do_action( $hook_constant, ...$args ) {
		do_action( $hook_constant, ...$args );
	}

	/**
	 * Apply a plugin filter hook.
	 *
	 * Proxy for WordPress core apply_filters() that accepts a hook constant value.
	 *
	 * @param string $hook_constant Value of one of the FILTER hook constants.
	 * @param mixed  $value         The value to filter.
	 * @param mixed  ...$args       Additional arguments passed to the hook.
	 * @return mixed The (possibly modified) value.
	 */
	public static function apply_filters( $hook_constant, $value, ...$args ) {
		return apply_filters( $hook_constant, $value, ...$args );
	}
}
