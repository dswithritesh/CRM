<?php
/**
 * AyurCRM Deactivation Handler
 *
 * Handles plugin deactivation. Cleans up scheduled cron events and flushes
 * rewrite rules. Does NOT delete any user data — that is the responsibility
 * of the uninstall handler.
 *
 * @package AyurCRM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AyurCRM_Deactivator
 *
 * Called once when the administrator deactivates the plugin.
 */
class AyurCRM_Deactivator {

	/**
	 * Run all deactivation tasks.
	 *
	 * @return void
	 */
	public static function deactivate() {
		// Remove all scheduled cron events created by this plugin.
		self::unschedule_cron_events();

		// Mark the plugin as inactive (does NOT delete data).
		update_option( 'ayurcrm_plugin_active', 0 );

		// Flush rewrite rules so our REST endpoints are removed cleanly.
		flush_rewrite_rules();
	}

	/**
	 * Unschedule all plugin cron events.
	 *
	 * Iterates through every registered hook and clears all scheduled
	 * occurrences from the WP-Cron queue.
	 *
	 * @return void
	 */
	private static function unschedule_cron_events() {
		$hooks = array(
			'ayurcrm_cron_followup_reminders',
			'ayurcrm_cron_overdue_check',
			'ayurcrm_cron_process_notifications',
			'ayurcrm_cron_cleanup_exports',
			'ayurcrm_cron_process_import_queue',
		);

		foreach ( $hooks as $hook ) {
			$timestamp = wp_next_scheduled( $hook );

			while ( $timestamp ) {
				wp_unschedule_event( $timestamp, $hook );
				$timestamp = wp_next_scheduled( $hook );
			}
		}
	}
}
