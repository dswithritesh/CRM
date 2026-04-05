<?php
/**
 * AyurCRM Capabilities & Role Definitions
 *
 * Defines all seven CRM roles and their associated capability sets.
 * This class is the single source of truth for access control across the
 * entire plugin. All permission checks should call user_has_cap() rather
 * than hard-coding capability strings.
 *
 * @package AyurCRM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AyurCRM_Capabilities
 *
 * Role definitions, capability maps, and helper methods for capability checks.
 */
class AyurCRM_Capabilities {

	// -------------------------------------------------------------------------
	// Role slug constants
	// -------------------------------------------------------------------------

	/** @var string Super-admin role slug. */
	const SUPER_ADMIN = 'ayurcrm_super_admin';

	/** @var string Admin role slug. */
	const ADMIN = 'ayurcrm_admin';

	/** @var string Branch manager role slug. */
	const BRANCH_MANAGER = 'ayurcrm_branch_manager';

	/** @var string Doctor role slug. */
	const DOCTOR = 'ayurcrm_doctor';

	/** @var string Counselor role slug. */
	const COUNSELOR = 'ayurcrm_counselor';

	/** @var string Sales role slug. */
	const SALES = 'ayurcrm_sales';

	/** @var string Viewer role slug. */
	const VIEWER = 'ayurcrm_viewer';

	// -------------------------------------------------------------------------
	// Capability string constants
	// -------------------------------------------------------------------------

	/** @var string Capability: view all leads (not just assigned). */
	const CAP_VIEW_ALL_LEADS = 'ayurcrm_view_all_leads';

	/** @var string Capability: view leads assigned to the current user. */
	const CAP_VIEW_ASSIGNED_LEADS = 'ayurcrm_view_assigned_leads';

	/** @var string Capability: create and edit lead records. */
	const CAP_EDIT_LEADS = 'ayurcrm_edit_leads';

	/** @var string Capability: delete lead records. */
	const CAP_DELETE_LEADS = 'ayurcrm_delete_leads';

	/** @var string Capability: assign leads to users. */
	const CAP_ASSIGN_LEADS = 'ayurcrm_assign_leads';

	/** @var string Capability: export lead data. */
	const CAP_EXPORT_LEADS = 'ayurcrm_export_leads';

	/** @var string Capability: import leads from CSV/spreadsheet. */
	const CAP_IMPORT_LEADS = 'ayurcrm_import_leads';

	/** @var string Capability: add notes to a lead. */
	const CAP_ADD_NOTES = 'ayurcrm_add_notes';

	/** @var string Capability: update a lead's pipeline status. */
	const CAP_UPDATE_STATUS = 'ayurcrm_update_status';

	/** @var string Capability: update or create follow-up entries. */
	const CAP_UPDATE_FOLLOWUP = 'ayurcrm_update_followup';

	/** @var string Capability: view all-clinic reports. */
	const CAP_VIEW_REPORTS = 'ayurcrm_view_reports';

	/** @var string Capability: view branch-scoped reports. */
	const CAP_VIEW_BRANCH_REPORTS = 'ayurcrm_view_branch_reports';

	/** @var string Capability: manage plugin settings. */
	const CAP_MANAGE_SETTINGS = 'ayurcrm_manage_settings';

	/** @var string Capability: manage WordPress users within CRM context. */
	const CAP_MANAGE_USERS = 'ayurcrm_manage_users';

	/** @var string Capability: manage pipeline stage configuration. */
	const CAP_MANAGE_PIPELINE = 'ayurcrm_manage_pipeline';

	/** @var string Capability: manage notification templates and rules. */
	const CAP_MANAGE_NOTIFICATIONS = 'ayurcrm_manage_notifications';

	/** @var string Capability: manage third-party integration settings. */
	const CAP_MANAGE_INTEGRATIONS = 'ayurcrm_manage_integrations';

	/** @var string Capability: view system activity and error logs. */
	const CAP_VIEW_LOGS = 'ayurcrm_view_logs';

	/** @var string Capability: access the CRM dashboard. */
	const CAP_VIEW_DASHBOARD = 'ayurcrm_view_dashboard';

	/** @var string Capability: perform bulk edits on leads. */
	const CAP_BULK_EDIT_LEADS = 'ayurcrm_bulk_edit_leads';

	// -------------------------------------------------------------------------
	// Public API
	// -------------------------------------------------------------------------

	/**
	 * Returns the capability matrix: role slug → array of capability strings.
	 *
	 * This is the canonical access-control map for the entire plugin.
	 *
	 * @return array<string, string[]>
	 */
	public static function get_capabilities_map() {
		$all = self::get_all_capabilities();

		return array(
			self::SUPER_ADMIN    => $all,

			self::ADMIN          => $all,

			self::BRANCH_MANAGER => array(
				self::CAP_VIEW_ASSIGNED_LEADS,
				self::CAP_EDIT_LEADS,
				self::CAP_ASSIGN_LEADS,
				self::CAP_EXPORT_LEADS,
				self::CAP_ADD_NOTES,
				self::CAP_UPDATE_STATUS,
				self::CAP_UPDATE_FOLLOWUP,
				self::CAP_VIEW_BRANCH_REPORTS,
				self::CAP_VIEW_DASHBOARD,
				self::CAP_BULK_EDIT_LEADS,
			),

			self::DOCTOR         => array(
				self::CAP_VIEW_ASSIGNED_LEADS,
				self::CAP_ADD_NOTES,
				self::CAP_UPDATE_STATUS,
				self::CAP_VIEW_DASHBOARD,
			),

			self::COUNSELOR      => array(
				self::CAP_VIEW_ASSIGNED_LEADS,
				self::CAP_EDIT_LEADS,
				self::CAP_ADD_NOTES,
				self::CAP_UPDATE_STATUS,
				self::CAP_UPDATE_FOLLOWUP,
				self::CAP_VIEW_DASHBOARD,
			),

			self::SALES          => array(
				self::CAP_VIEW_ASSIGNED_LEADS,
				self::CAP_EDIT_LEADS,
				self::CAP_ADD_NOTES,
				self::CAP_UPDATE_STATUS,
				self::CAP_UPDATE_FOLLOWUP,
				self::CAP_VIEW_DASHBOARD,
			),

			self::VIEWER         => array(
				self::CAP_VIEW_ASSIGNED_LEADS,
				self::CAP_VIEW_DASHBOARD,
			),
		);
	}

	/**
	 * Register all seven CRM roles with their capabilities in WordPress.
	 *
	 * Safe to call multiple times — uses add_role() / get_role()->add_cap()
	 * which are idempotent.
	 *
	 * @return void
	 */
	public static function register_roles() {
		$map = self::get_capabilities_map();

		foreach ( $map as $role_slug => $capabilities ) {
			// Build WP-style capability array: [ 'cap_name' => true, ... ].
			$caps_array = array_fill_keys( $capabilities, true );

			$role = get_role( $role_slug );

			if ( null === $role ) {
				// Role does not exist yet — create it.
				add_role( $role_slug, self::get_role_label( $role_slug ), $caps_array );
			} else {
				// Role already exists — ensure all current caps are present.
				foreach ( $caps_array as $cap => $grant ) {
					$role->add_cap( $cap, $grant );
				}
			}
		}
	}

	/**
	 * Remove all seven CRM roles from WordPress.
	 *
	 * Called by the uninstall handler when removing all data.
	 *
	 * @return void
	 */
	public static function remove_roles() {
		$roles = array(
			self::SUPER_ADMIN,
			self::ADMIN,
			self::BRANCH_MANAGER,
			self::DOCTOR,
			self::COUNSELOR,
			self::SALES,
			self::VIEWER,
		);

		foreach ( $roles as $role ) {
			remove_role( $role );
		}
	}

	/**
	 * Returns a flat array of every capability string defined by the plugin.
	 *
	 * @return string[]
	 */
	public static function get_all_capabilities() {
		return array(
			self::CAP_VIEW_ALL_LEADS,
			self::CAP_VIEW_ASSIGNED_LEADS,
			self::CAP_EDIT_LEADS,
			self::CAP_DELETE_LEADS,
			self::CAP_ASSIGN_LEADS,
			self::CAP_EXPORT_LEADS,
			self::CAP_IMPORT_LEADS,
			self::CAP_ADD_NOTES,
			self::CAP_UPDATE_STATUS,
			self::CAP_UPDATE_FOLLOWUP,
			self::CAP_VIEW_REPORTS,
			self::CAP_VIEW_BRANCH_REPORTS,
			self::CAP_MANAGE_SETTINGS,
			self::CAP_MANAGE_USERS,
			self::CAP_MANAGE_PIPELINE,
			self::CAP_MANAGE_NOTIFICATIONS,
			self::CAP_MANAGE_INTEGRATIONS,
			self::CAP_VIEW_LOGS,
			self::CAP_VIEW_DASHBOARD,
			self::CAP_BULK_EDIT_LEADS,
		);
	}

	/**
	 * Check whether a user has a specific CRM capability.
	 *
	 * @param string   $capability The capability string to check (without prefix).
	 * @param int|null $user_id    WordPress user ID. Defaults to current user.
	 * @return bool True if the user has the capability.
	 */
	public static function user_has_cap( $capability, $user_id = null ) {
		if ( null === $user_id ) {
			$user_id = get_current_user_id();
		}

		if ( ! $user_id ) {
			return false;
		}

		return user_can( $user_id, $capability );
	}

	// -------------------------------------------------------------------------
	// Internal helpers
	// -------------------------------------------------------------------------

	/**
	 * Returns the human-readable label for a CRM role slug.
	 *
	 * @param string $role_slug One of the ROLE_* constants.
	 * @return string Translated display label.
	 */
	private static function get_role_label( $role_slug ) {
		$labels = array(
			self::SUPER_ADMIN    => __( 'AyurCRM Super Admin', 'ayurcrm' ),
			self::ADMIN          => __( 'AyurCRM Admin', 'ayurcrm' ),
			self::BRANCH_MANAGER => __( 'AyurCRM Branch Manager', 'ayurcrm' ),
			self::DOCTOR         => __( 'AyurCRM Doctor', 'ayurcrm' ),
			self::COUNSELOR      => __( 'AyurCRM Counselor', 'ayurcrm' ),
			self::SALES          => __( 'AyurCRM Sales', 'ayurcrm' ),
			self::VIEWER         => __( 'AyurCRM Viewer', 'ayurcrm' ),
		);

		return isset( $labels[ $role_slug ] ) ? $labels[ $role_slug ] : $role_slug;
	}
}
