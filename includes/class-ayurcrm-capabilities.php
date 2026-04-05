<?php
/**
 * AyurCRM Roles and Capabilities
 *
 * Defines all custom WordPress roles used by AyurCRM and the full
 * capability map for each role. Also provides helpers for capability
 * checks used throughout the plugin.
 *
 * Roles defined:
 *  - ayurcrm_super_admin   — Full access to everything
 *  - ayurcrm_admin         — Full CRM access except system settings
 *  - ayurcrm_branch_manager — Branch-scoped admin access
 *  - ayurcrm_doctor        — View/update assigned leads; add notes
 *  - ayurcrm_counselor     — View/update assigned leads; manage follow-ups
 *  - ayurcrm_sales         — View assigned leads; update status/follow-up
 *  - ayurcrm_viewer        — Read-only access to assigned leads
 *
 * Capability naming convention:
 *  ayurcrm_{action}_{object}
 *  Examples: ayurcrm_view_all_leads, ayurcrm_edit_leads, ayurcrm_export_leads
 *
 * @package AyurCRM
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AyurCRM_Capabilities
 */
class AyurCRM_Capabilities {

	// -----------------------------------------------------------------------
	// Capability constants
	// -----------------------------------------------------------------------

	// Lead access
	const CAP_VIEW_ALL_LEADS        = 'ayurcrm_view_all_leads';
	const CAP_VIEW_ASSIGNED_LEADS   = 'ayurcrm_view_assigned_leads';
	const CAP_CREATE_LEADS          = 'ayurcrm_create_leads';
	const CAP_EDIT_LEADS            = 'ayurcrm_edit_leads';
	const CAP_DELETE_LEADS          = 'ayurcrm_delete_leads';
	const CAP_RESTORE_LEADS         = 'ayurcrm_restore_leads';

	// Lead operations
	const CAP_ASSIGN_LEADS          = 'ayurcrm_assign_leads';
	const CAP_REASSIGN_LEADS        = 'ayurcrm_reassign_leads';
	const CAP_EXPORT_LEADS          = 'ayurcrm_export_leads';
	const CAP_IMPORT_LEADS          = 'ayurcrm_import_leads';
	const CAP_MERGE_LEADS           = 'ayurcrm_merge_leads';

	// Lead interaction
	const CAP_ADD_NOTES             = 'ayurcrm_add_notes';
	const CAP_UPDATE_STATUS         = 'ayurcrm_update_status';
	const CAP_UPDATE_FOLLOWUP       = 'ayurcrm_update_followup';
	const CAP_LOG_COMMUNICATION     = 'ayurcrm_log_communication';

	// Reporting
	const CAP_VIEW_REPORTS          = 'ayurcrm_view_reports';
	const CAP_VIEW_BRANCH_REPORTS   = 'ayurcrm_view_branch_reports';
	const CAP_VIEW_ALL_REPORTS      = 'ayurcrm_view_all_reports';
	const CAP_EXPORT_REPORTS        = 'ayurcrm_export_reports';

	// Users / staff
	const CAP_MANAGE_USERS          = 'ayurcrm_manage_users';
	const CAP_VIEW_STAFF_METRICS    = 'ayurcrm_view_staff_metrics';

	// Settings / system
	const CAP_MANAGE_SETTINGS       = 'ayurcrm_manage_settings';
	const CAP_MANAGE_NOTIFICATIONS  = 'ayurcrm_manage_notifications';
	const CAP_MANAGE_INTEGRATIONS   = 'ayurcrm_manage_integrations';
	const CAP_MANAGE_PIPELINE       = 'ayurcrm_manage_pipeline';
	const CAP_MANAGE_BRANCHES       = 'ayurcrm_manage_branches';
	const CAP_VIEW_LOGS             = 'ayurcrm_view_logs';

	// -----------------------------------------------------------------------
	// Role slug constants
	// -----------------------------------------------------------------------

	const ROLE_SUPER_ADMIN     = 'ayurcrm_super_admin';
	const ROLE_ADMIN           = 'ayurcrm_admin';
	const ROLE_BRANCH_MANAGER  = 'ayurcrm_branch_manager';
	const ROLE_DOCTOR          = 'ayurcrm_doctor';
	const ROLE_COUNSELOR       = 'ayurcrm_counselor';
	const ROLE_SALES           = 'ayurcrm_sales';
	const ROLE_VIEWER          = 'ayurcrm_viewer';

	// -----------------------------------------------------------------------
	// Role definitions
	// -----------------------------------------------------------------------

	/**
	 * Full role definitions map.
	 *
	 * Structure: role_slug => [ 'display_name' => string, 'capabilities' => string[] ]
	 *
	 * @return array[]
	 */
	public static function get_role_definitions() {
		return array(

			self::ROLE_SUPER_ADMIN => array(
				'display_name' => 'CRM Super Admin',
				'capabilities' => array(
					self::CAP_VIEW_ALL_LEADS,
					self::CAP_VIEW_ASSIGNED_LEADS,
					self::CAP_CREATE_LEADS,
					self::CAP_EDIT_LEADS,
					self::CAP_DELETE_LEADS,
					self::CAP_RESTORE_LEADS,
					self::CAP_ASSIGN_LEADS,
					self::CAP_REASSIGN_LEADS,
					self::CAP_EXPORT_LEADS,
					self::CAP_IMPORT_LEADS,
					self::CAP_MERGE_LEADS,
					self::CAP_ADD_NOTES,
					self::CAP_UPDATE_STATUS,
					self::CAP_UPDATE_FOLLOWUP,
					self::CAP_LOG_COMMUNICATION,
					self::CAP_VIEW_REPORTS,
					self::CAP_VIEW_BRANCH_REPORTS,
					self::CAP_VIEW_ALL_REPORTS,
					self::CAP_EXPORT_REPORTS,
					self::CAP_MANAGE_USERS,
					self::CAP_VIEW_STAFF_METRICS,
					self::CAP_MANAGE_SETTINGS,
					self::CAP_MANAGE_NOTIFICATIONS,
					self::CAP_MANAGE_INTEGRATIONS,
					self::CAP_MANAGE_PIPELINE,
					self::CAP_MANAGE_BRANCHES,
					self::CAP_VIEW_LOGS,
				),
			),

			self::ROLE_ADMIN => array(
				'display_name' => 'CRM Admin',
				'capabilities' => array(
					self::CAP_VIEW_ALL_LEADS,
					self::CAP_VIEW_ASSIGNED_LEADS,
					self::CAP_CREATE_LEADS,
					self::CAP_EDIT_LEADS,
					self::CAP_DELETE_LEADS,
					self::CAP_RESTORE_LEADS,
					self::CAP_ASSIGN_LEADS,
					self::CAP_REASSIGN_LEADS,
					self::CAP_EXPORT_LEADS,
					self::CAP_IMPORT_LEADS,
					self::CAP_MERGE_LEADS,
					self::CAP_ADD_NOTES,
					self::CAP_UPDATE_STATUS,
					self::CAP_UPDATE_FOLLOWUP,
					self::CAP_LOG_COMMUNICATION,
					self::CAP_VIEW_REPORTS,
					self::CAP_VIEW_BRANCH_REPORTS,
					self::CAP_VIEW_ALL_REPORTS,
					self::CAP_EXPORT_REPORTS,
					self::CAP_MANAGE_USERS,
					self::CAP_VIEW_STAFF_METRICS,
					self::CAP_MANAGE_NOTIFICATIONS,
					self::CAP_MANAGE_PIPELINE,
					self::CAP_VIEW_LOGS,
					// NOTE: CAP_MANAGE_SETTINGS and CAP_MANAGE_INTEGRATIONS intentionally
					// omitted — those belong to Super Admin only.
				),
			),

			self::ROLE_BRANCH_MANAGER => array(
				'display_name' => 'Branch Manager',
				'capabilities' => array(
					self::CAP_VIEW_ALL_LEADS,
					self::CAP_VIEW_ASSIGNED_LEADS,
					self::CAP_CREATE_LEADS,
					self::CAP_EDIT_LEADS,
					self::CAP_ASSIGN_LEADS,
					self::CAP_REASSIGN_LEADS,
					self::CAP_EXPORT_LEADS,
					self::CAP_ADD_NOTES,
					self::CAP_UPDATE_STATUS,
					self::CAP_UPDATE_FOLLOWUP,
					self::CAP_LOG_COMMUNICATION,
					self::CAP_VIEW_REPORTS,
					self::CAP_VIEW_BRANCH_REPORTS,
					self::CAP_VIEW_STAFF_METRICS,
					// Branch-scoped; view_all_reports intentionally omitted.
				),
			),

			self::ROLE_DOCTOR => array(
				'display_name' => 'Doctor',
				'capabilities' => array(
					self::CAP_VIEW_ASSIGNED_LEADS,
					self::CAP_EDIT_LEADS,
					self::CAP_ADD_NOTES,
					self::CAP_UPDATE_STATUS,
					self::CAP_LOG_COMMUNICATION,
					// Doctors see only assigned leads; no assign/export/import.
				),
			),

			self::ROLE_COUNSELOR => array(
				'display_name' => 'Counselor',
				'capabilities' => array(
					self::CAP_VIEW_ASSIGNED_LEADS,
					self::CAP_EDIT_LEADS,
					self::CAP_ADD_NOTES,
					self::CAP_UPDATE_STATUS,
					self::CAP_UPDATE_FOLLOWUP,
					self::CAP_LOG_COMMUNICATION,
					// Counselors manage follow-ups; no assign/export/import.
				),
			),

			self::ROLE_SALES => array(
				'display_name' => 'Sales Executive',
				'capabilities' => array(
					self::CAP_VIEW_ASSIGNED_LEADS,
					self::CAP_ADD_NOTES,
					self::CAP_UPDATE_STATUS,
					self::CAP_UPDATE_FOLLOWUP,
					self::CAP_LOG_COMMUNICATION,
					// Sales can update status and follow-up; cannot edit lead fields.
				),
			),

			self::ROLE_VIEWER => array(
				'display_name' => 'Staff Viewer',
				'capabilities' => array(
					self::CAP_VIEW_ASSIGNED_LEADS,
					// Read-only; no write operations at all.
				),
			),

		);
	}

	// -----------------------------------------------------------------------
	// Role registration (called from activator)
	// -----------------------------------------------------------------------

	/**
	 * Register all AyurCRM roles with WordPress.
	 *
	 * Safe to call on every activation — add_role() is a no-op if the role
	 * already exists. Uses the capability map from get_role_definitions().
	 *
	 * @return void
	 */
	public static function register_roles() {
		foreach ( self::get_role_definitions() as $slug => $definition ) {
			// Build the WP capabilities array: [ cap_name => true ]
			$caps = array();
			foreach ( $definition['capabilities'] as $cap ) {
				$caps[ $cap ] = true;
			}

			// add_role() is idempotent — returns null if role exists, WP_Role if new.
			add_role( $slug, $definition['display_name'], $caps );
		}
	}

	/**
	 * Remove all AyurCRM roles from WordPress.
	 *
	 * Called only from uninstall.php — never from the deactivator.
	 * remove_role() is a no-op if the role does not exist.
	 *
	 * @return void
	 */
	public static function remove_roles() {
		foreach ( array_keys( self::get_role_definitions() ) as $slug ) {
			remove_role( $slug );
		}
	}

	// -----------------------------------------------------------------------
	// Capability check helpers
	// -----------------------------------------------------------------------

	/**
	 * Check whether the current user has a given AyurCRM capability.
	 *
	 * Wraps current_user_can() with a consistent prefix check. WordPress
	 * administrators are implicitly granted all capabilities via their
	 * built-in 'administrator' role (which has all_caps = true in WP core).
	 *
	 * @param string $capability Capability name (with or without ayurcrm_ prefix).
	 * @return bool
	 */
	public static function current_user_can( $capability ) {
		// Normalise: strip accidental double prefix.
		if ( strpos( $capability, 'ayurcrm_' ) !== 0 ) {
			$capability = 'ayurcrm_' . $capability;
		}
		return current_user_can( $capability );
	}

	/**
	 * Return all capability slugs defined by AyurCRM.
	 *
	 * @return string[]
	 */
	public static function get_all_capabilities() {
		$caps = array();
		foreach ( self::get_role_definitions() as $definition ) {
			foreach ( $definition['capabilities'] as $cap ) {
				$caps[ $cap ] = $cap;
			}
		}
		return array_values( $caps );
	}

	/**
	 * Return the role slug(s) for the current user that are AyurCRM roles.
	 *
	 * Returns empty array if the user has no AyurCRM role.
	 *
	 * @return string[]
	 */
	public static function get_current_user_crm_roles() {
		$user = wp_get_current_user();
		if ( ! $user || ! $user->ID ) {
			return array();
		}
		$crm_role_slugs = array_keys( self::get_role_definitions() );
		return array_values( array_intersect( (array) $user->roles, $crm_role_slugs ) );
	}

	/**
	 * Return TRUE if the current user has any AyurCRM role.
	 *
	 * WordPress administrators are not considered AyurCRM roles — they
	 * access CRM via capability inheritance, not role assignment.
	 *
	 * @return bool
	 */
	public static function current_user_has_crm_role() {
		return ! empty( self::get_current_user_crm_roles() );
	}

	/**
	 * Return TRUE if the current user's visibility is limited to assigned leads only.
	 *
	 * Used by the repository layer to automatically scope lead queries.
	 *
	 * @return bool
	 */
	public static function current_user_is_restricted_to_assigned() {
		// Admins and branch managers see all leads (branch-scoped for the latter
		// is enforced separately in the repository, not here).
		if ( current_user_can( 'administrator' )
			|| self::current_user_can( self::CAP_VIEW_ALL_LEADS )
		) {
			return false;
		}

		// Everyone else (doctor, counselor, sales, viewer) sees only assigned leads.
		return self::current_user_can( self::CAP_VIEW_ASSIGNED_LEADS );
	}
}
