<?php
/**
 * AyurCRM Hook Name Constants
 *
 * Centralizes all custom action and filter hook names used throughout
 * the AyurCRM plugin. Using constants instead of bare strings prevents
 * typos, enables IDE auto-completion, and makes hook refactoring safe.
 *
 * Naming convention:
 *  Actions: AYURCRM_ACTION_{VERB}_{NOUN}
 *  Filters: AYURCRM_FILTER_{NOUN}_{ADJECTIVE/CONTEXT}
 *
 * @package AyurCRM
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AyurCRM_Hooks
 *
 * All hook name constants for AyurCRM.
 */
class AyurCRM_Hooks {

	// -----------------------------------------------------------------------
	// Lead lifecycle actions
	// -----------------------------------------------------------------------

	/** Fired after a lead is successfully created. Passes lead ID (int). */
	const ACTION_LEAD_CREATED        = 'ayurcrm_lead_created';

	/** Fired after a lead is updated. Passes lead ID (int) and changed fields (array). */
	const ACTION_LEAD_UPDATED        = 'ayurcrm_lead_updated';

	/** Fired after a lead is soft-deleted (moved to trash). Passes lead ID (int). */
	const ACTION_LEAD_DELETED        = 'ayurcrm_lead_deleted';

	/** Fired after a lead is restored from trash. Passes lead ID (int). */
	const ACTION_LEAD_RESTORED       = 'ayurcrm_lead_restored';

	/** Fired after a lead status changes. Passes lead ID, old status, new status. */
	const ACTION_LEAD_STATUS_CHANGED = 'ayurcrm_lead_status_changed';

	/** Fired after a lead is assigned to a user. Passes lead ID, old assignee ID, new assignee ID. */
	const ACTION_LEAD_ASSIGNED       = 'ayurcrm_lead_assigned';

	/** Fired after a lead is reassigned. Passes lead ID, old assignee ID, new assignee ID. */
	const ACTION_LEAD_REASSIGNED     = 'ayurcrm_lead_reassigned';

	/** Fired after a duplicate lead is merged. Passes surviving lead ID, merged lead ID. */
	const ACTION_LEAD_MERGED         = 'ayurcrm_lead_merged';

	// -----------------------------------------------------------------------
	// Activity actions
	// -----------------------------------------------------------------------

	/** Fired after an activity (note/call/email) is logged. Passes activity ID (int). */
	const ACTION_ACTIVITY_LOGGED     = 'ayurcrm_activity_logged';

	/** Fired after an activity is updated. Passes activity ID (int). */
	const ACTION_ACTIVITY_UPDATED    = 'ayurcrm_activity_updated';

	/** Fired after an activity is deleted. Passes activity ID (int). */
	const ACTION_ACTIVITY_DELETED    = 'ayurcrm_activity_deleted';

	// -----------------------------------------------------------------------
	// Follow-up actions
	// -----------------------------------------------------------------------

	/** Fired when a follow-up is scheduled. Passes lead ID (int), follow-up datetime (string). */
	const ACTION_FOLLOWUP_SCHEDULED  = 'ayurcrm_followup_scheduled';

	/** Fired when a follow-up is marked complete. Passes lead ID (int). */
	const ACTION_FOLLOWUP_COMPLETED  = 'ayurcrm_followup_completed';

	/** Fired when a follow-up is overdue (detected by cron). Passes lead ID (int). */
	const ACTION_FOLLOWUP_OVERDUE    = 'ayurcrm_followup_overdue';

	// -----------------------------------------------------------------------
	// Import / export actions
	// -----------------------------------------------------------------------

	/** Fired after a lead import batch is processed. Passes import ID (int), row count (int). */
	const ACTION_IMPORT_BATCH_DONE   = 'ayurcrm_import_batch_done';

	/** Fired after an export file is generated. Passes export ID (int), file path (string). */
	const ACTION_EXPORT_READY        = 'ayurcrm_export_ready';

	// -----------------------------------------------------------------------
	// Cron actions (bound in AyurCRM_Plugin::define_cron_hooks)
	// -----------------------------------------------------------------------

	/** WP-Cron hook: check for overdue follow-ups. */
	const CRON_FOLLOWUP_CHECK        = 'ayurcrm_cron_followup_check';

	/** WP-Cron hook: check SLA breaches. */
	const CRON_SLA_CHECK             = 'ayurcrm_cron_sla_check';

	/** WP-Cron hook: flush notification queue. */
	const CRON_FLUSH_NOTIFICATIONS   = 'ayurcrm_cron_flush_notifications';

	/** WP-Cron hook: process pending export jobs. */
	const CRON_EXPORT_PROCESS        = 'ayurcrm_cron_export_process';

	/** WP-Cron hook: process pending import jobs. */
	const CRON_IMPORT_PROCESS        = 'ayurcrm_cron_import_process';

	// -----------------------------------------------------------------------
	// Filter hooks
	// -----------------------------------------------------------------------

	/**
	 * Filter the lead data array before it is inserted into the DB.
	 * Passes data array. Must return array.
	 */
	const FILTER_LEAD_DATA_BEFORE_INSERT  = 'ayurcrm_lead_data_before_insert';

	/**
	 * Filter the lead data array before it is updated in the DB.
	 * Passes data array, lead ID (int). Must return array.
	 */
	const FILTER_LEAD_DATA_BEFORE_UPDATE  = 'ayurcrm_lead_data_before_update';

	/**
	 * Filter the lead object/array after it is retrieved from the DB.
	 * Passes lead data (array). Must return array.
	 */
	const FILTER_LEAD_DATA_AFTER_FETCH    = 'ayurcrm_lead_data_after_fetch';

	/**
	 * Filter the array of allowed lead statuses.
	 * Passes statuses array (slug => label). Must return array.
	 */
	const FILTER_LEAD_STATUSES            = 'ayurcrm_lead_statuses';

	/**
	 * Filter the lead query args before the repository executes a search.
	 * Passes args array. Must return array.
	 */
	const FILTER_LEAD_QUERY_ARGS          = 'ayurcrm_lead_query_args';

	/**
	 * Filter the columns included in a lead export.
	 * Passes columns array (slug => label). Must return array.
	 */
	const FILTER_EXPORT_COLUMNS           = 'ayurcrm_export_columns';

	/**
	 * Filter the REST API response data for a single lead.
	 * Passes response data (array), lead ID (int). Must return array.
	 */
	const FILTER_REST_LEAD_RESPONSE       = 'ayurcrm_rest_lead_response';

	/**
	 * Filter the capability required for a given CRM action.
	 * Passes capability string, action string. Must return string.
	 */
	const FILTER_REQUIRED_CAPABILITY      = 'ayurcrm_required_capability';
}
