<?php
/**
 * Role and capability registration.
 *
 * @package AyurCRM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AyurCRM_Capabilities
 *
 * Defines all custom capabilities and manages custom role registration.
 */
class AyurCRM_Capabilities {

	// -----------------------------------------------------------------------
	// Capability constants
	// -----------------------------------------------------------------------

	const CAP_VIEW_ALL_LEADS        = 'ayurcrm_view_all_leads';
	const CAP_VIEW_ASSIGNED_LEADS   = 'ayurcrm_view_assigned_leads';
	const CAP_VIEW_BRANCH_LEADS     = 'ayurcrm_view_branch_leads';
	const CAP_EDIT_LEADS            = 'ayurcrm_edit_leads';
	const CAP_DELETE_LEADS          = 'ayurcrm_delete_leads';
	const CAP_ASSIGN_LEADS          = 'ayurcrm_assign_leads';
	const CAP_EXPORT_LEADS          = 'ayurcrm_export_leads';
	const CAP_IMPORT_LEADS          = 'ayurcrm_import_leads';
	const CAP_ADD_NOTES             = 'ayurcrm_add_notes';
	const CAP_UPDATE_STATUS         = 'ayurcrm_update_status';
	const CAP_UPDATE_FOLLOWUP       = 'ayurcrm_update_followup';
	const CAP_VIEW_REPORTS          = 'ayurcrm_view_reports';
	const CAP_VIEW_BRANCH_REPORTS   = 'ayurcrm_view_branch_reports';
	const CAP_MANAGE_SETTINGS       = 'ayurcrm_manage_settings';
	const CAP_MANAGE_USERS          = 'ayurcrm_manage_users';
	const CAP_MANAGE_PIPELINE       = 'ayurcrm_manage_pipeline';
	const CAP_MANAGE_NOTIFICATIONS  = 'ayurcrm_manage_notifications';
	const CAP_MANAGE_INTEGRATIONS   = 'ayurcrm_manage_integrations';
	const CAP_VIEW_LOGS             = 'ayurcrm_view_logs';
	const CAP_ACCESS_CRM            = 'ayurcrm_access_crm';

	// -----------------------------------------------------------------------
	// Role definitions
	// -----------------------------------------------------------------------

	/**
	 * Return all role definitions with their associated capability maps.
	 *
	 * @return array
	 */
	public static function get_roles(): array {
		return array(
			'ayurcrm_super_admin' => array(
				'display_name' => __( 'AyurCRM Super Admin', 'ayurcrm' ),
				'capabilities' => array_fill_keys( self::get_all_capabilities(), true ),
			),

			'ayurcrm_admin' => array(
				'display_name' => __( 'AyurCRM Admin', 'ayurcrm' ),
				'capabilities' => array_fill_keys(
					array(
						self::CAP_VIEW_ALL_LEADS,
						self::CAP_VIEW_ASSIGNED_LEADS,
						self::CAP_VIEW_BRANCH_LEADS,
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
						self::CAP_MANAGE_PIPELINE,
						self::CAP_MANAGE_NOTIFICATIONS,
						self::CAP_VIEW_LOGS,
						self::CAP_ACCESS_CRM,
					),
					true
				),
			),

			'ayurcrm_branch_manager' => array(
				'display_name' => __( 'AyurCRM Branch Manager', 'ayurcrm' ),
				'capabilities' => array_fill_keys(
					array(
						self::CAP_VIEW_BRANCH_LEADS,
						self::CAP_VIEW_ASSIGNED_LEADS,
						self::CAP_EDIT_LEADS,
						self::CAP_ASSIGN_LEADS,
						self::CAP_EXPORT_LEADS,
						self::CAP_ADD_NOTES,
						self::CAP_UPDATE_STATUS,
						self::CAP_UPDATE_FOLLOWUP,
						self::CAP_VIEW_BRANCH_REPORTS,
						self::CAP_MANAGE_NOTIFICATIONS,
						self::CAP_ACCESS_CRM,
					),
					true
				),
			),

			'ayurcrm_doctor' => array(
				'display_name' => __( 'AyurCRM Doctor', 'ayurcrm' ),
				'capabilities' => array_fill_keys(
					array(
						self::CAP_VIEW_ASSIGNED_LEADS,
						self::CAP_ADD_NOTES,
						self::CAP_UPDATE_STATUS,
						self::CAP_ACCESS_CRM,
					),
					true
				),
			),

			'ayurcrm_counselor' => array(
				'display_name' => __( 'AyurCRM Counselor', 'ayurcrm' ),
				'capabilities' => array_fill_keys(
					array(
						self::CAP_VIEW_ASSIGNED_LEADS,
						self::CAP_EDIT_LEADS,
						self::CAP_ADD_NOTES,
						self::CAP_UPDATE_STATUS,
						self::CAP_UPDATE_FOLLOWUP,
						self::CAP_ACCESS_CRM,
					),
					true
				),
			),

			'ayurcrm_sales' => array(
				'display_name' => __( 'AyurCRM Sales', 'ayurcrm' ),
				'capabilities' => array_fill_keys(
					array(
						self::CAP_VIEW_ASSIGNED_LEADS,
						self::CAP_EDIT_LEADS,
						self::CAP_ADD_NOTES,
						self::CAP_UPDATE_STATUS,
						self::CAP_UPDATE_FOLLOWUP,
						self::CAP_ACCESS_CRM,
					),
					true
				),
			),

			'ayurcrm_viewer' => array(
				'display_name' => __( 'AyurCRM Viewer', 'ayurcrm' ),
				'capabilities' => array_fill_keys(
					array(
						self::CAP_VIEW_ASSIGNED_LEADS,
						self::CAP_ACCESS_CRM,
					),
					true
				),
			),
		);
	}

	// -----------------------------------------------------------------------
	// Registration
	// -----------------------------------------------------------------------

	/**
	 * Register all custom roles and add capabilities to the administrator role.
	 *
	 * @return void
	 */
	public static function register_roles_and_caps(): void {
		$roles = self::get_roles();

		foreach ( $roles as $role_slug => $role_data ) {
			if ( null === get_role( $role_slug ) ) {
				add_role( $role_slug, $role_data['display_name'], $role_data['capabilities'] );
			}
		}

		// Grant all CRM capabilities to the WP administrator role.
		$administrator = get_role( 'administrator' );
		if ( $administrator instanceof WP_Role ) {
			foreach ( self::get_all_capabilities() as $cap ) {
				$administrator->add_cap( $cap );
			}
		}
	}

	/**
	 * Remove all custom roles and strip CRM caps from the administrator role.
	 *
	 * Called from uninstall.php only.
	 *
	 * @return void
	 */
	public static function remove_roles_and_caps(): void {
		foreach ( array_keys( self::get_roles() ) as $role_slug ) {
			remove_role( $role_slug );
		}

		$administrator = get_role( 'administrator' );
		if ( $administrator instanceof WP_Role ) {
			foreach ( self::get_all_capabilities() as $cap ) {
				$administrator->remove_cap( $cap );
			}
		}
	}

	// -----------------------------------------------------------------------
	// Helpers
	// -----------------------------------------------------------------------

	/**
	 * Return a flat array of all capability strings.
	 *
	 * @return string[]
	 */
	public static function get_all_capabilities(): array {
		return array(
			self::CAP_VIEW_ALL_LEADS,
			self::CAP_VIEW_ASSIGNED_LEADS,
			self::CAP_VIEW_BRANCH_LEADS,
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
			self::CAP_ACCESS_CRM,
		);
	}

	/**
	 * Check whether the current user has a given CRM capability.
	 *
	 * Also grants access to WP administrators regardless of explicit cap.
	 *
	 * @param string $cap Capability to check.
	 * @return bool
	 */
	public static function current_user_can( string $cap ): bool {
		return current_user_can( 'administrator' ) || current_user_can( $cap );
	}
}
