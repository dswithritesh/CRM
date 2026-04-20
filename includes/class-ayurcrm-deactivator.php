<?php
/**
 * AyurCRM Plugin Deactivator
 *
 * Handles everything that must happen when the plugin is deactivated via
 * WordPress. Intentionally conservative — data is preserved, DB tables are
 * left intact, and roles/capabilities are retained so that re-activation
 * restores full functionality without data loss.
 *
 * What this file IS allowed to do:
 *  - Unschedule WP-Cron events registered by AyurCRM
 *  - Delete runtime transients
 *  - Mark the plugin as inactive in options
 *  - Flush rewrite rules
 *
 * What this file MUST NOT do:
 *  - Drop any database tables
 *  - Delete leads, activities, or any user data
 *  - Remove roles or capabilities
 *  - Produce any output
 *
 * Data removal belongs exclusively in uninstall.php and only executes when
 * the user explicitly deletes the plugin from WordPress admin.
 *
 * @package AyurCRM
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AyurCRM_Deactivator
 */
class AyurCRM_Deactivator {

	/**
	 * Cron hooks registered by AyurCRM that must be unscheduled on deactivation.
	 *
	 * Must mirror the hooks in AyurCRM_Activator::$cron_events exactly.
	 *
	 * @var string[]
	 */
	private static $cron_hooks = array(
		'ayurcrm_cron_followup_check',
		'ayurcrm_cron_sla_check',
		'ayurcrm_cron_flush_notifications',
		'ayurcrm_cron_export_process',
		'ayurcrm_cron_import_process',
	);

	/**
	 * Runtime transient keys to clean up on deactivation.
	 *
	 * These are ephemeral flags and caches — safe to delete.
	 * Persistent options (settings, db_version, lead data) are NOT deleted here.
	 *
	 * @var string[]
	 */
	private static $transients_to_clear = array(
		'ayurcrm_needs_migration',
		'ayurcrm_migration_lock',
		'ayurcrm_cache_dashboard_today',
		'ayurcrm_cache_dashboard_stats',
	);

	/**
	 * Plugin deactivation handler.
	 *
	 * Called by register_deactivation_hook() in ayurcrm.php.
	 *
	 * @return void
	 */
	public static function deactivate() {
		self::unschedule_cron_events();
		self::clear_transients();
		self::mark_inactive();

		// Flush rewrite rules so any AyurCRM public endpoints are removed.
		flush_rewrite_rules( false );
	}

	// -----------------------------------------------------------------------
	// Individual deactivation steps
	// -----------------------------------------------------------------------

	/**
	 * Unschedule all WP-Cron events registered by AyurCRM.
	 *
	 * Uses wp_next_scheduled() + wp_unschedule_event() pattern which is
	 * safe for single-event hooks. Also calls wp_clear_scheduled_hook()
	 * as a belt-and-suspenders fallback to remove any orphaned instances.
	 *
	 * @return void
	 */
	private static function unschedule_cron_events() {
		foreach ( self::$cron_hooks as $hook ) {
			// Remove the next scheduled occurrence.
			$timestamp = wp_next_scheduled( $hook );
			if ( $timestamp ) {
				wp_unschedule_event( $timestamp, $hook );
			}

			// Belt-and-suspenders: clear all instances of this hook.
			wp_clear_scheduled_hook( $hook );
		}
	}

	/**
	 * Delete runtime transients created by AyurCRM.
	 *
	 * Only ephemeral runtime flags and caches are removed.
	 * Settings transients and user-data transients are preserved.
	 *
	 * @return void
	 */
	private static function clear_transients() {
		foreach ( self::$transients_to_clear as $transient ) {
			delete_transient( $transient );
		}

		// Also clear any per-date dashboard cache transients using a wildcard
		// approach via direct DB query (safe, low-risk, targets only our prefix).
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
				$wpdb->esc_like( '_transient_ayurcrm_cache_' ) . '%',
				$wpdb->esc_like( '_transient_timeout_ayurcrm_cache_' ) . '%'
			)
		);
	}

	/**
	 * Mark the plugin as inactive in options.
	 *
	 * This allows other plugins or admin tooling to detect AyurCRM state.
	 * The option is set back to '1' on re-activation.
	 *
	 * @return void
	 */
	private static function mark_inactive() {
		update_option( 'ayurcrm_plugin_active', '0', false );
	}
}
