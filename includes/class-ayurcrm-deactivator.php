<?php
/**
 * Deactivation handler.
 *
 * @package AyurCRM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AyurCRM_Deactivator
 *
 * Handles plugin deactivation tasks.
 * Does NOT delete plugin data — that is uninstall.php's responsibility.
 */
class AyurCRM_Deactivator {

	/**
	 * All cron hook names registered by this plugin.
	 *
	 * @var string[]
	 */
	private static array $cron_hooks = array(
		'ayurcrm_process_followup_reminders',
		'ayurcrm_process_notification_queue',
		'ayurcrm_cleanup_temp_files',
		'ayurcrm_admin_summary_email',
		'ayurcrm_cleanup_old_exports',
		'ayurcrm_cleanup_old_logs',
	);

	/**
	 * Run on plugin deactivation.
	 *
	 * @return void
	 */
	public static function deactivate(): void {
		self::clear_cron_events();
		self::clear_transients();
		flush_rewrite_rules( false );
	}

	/**
	 * Unschedule all cron events registered by this plugin.
	 *
	 * @return void
	 */
	private static function clear_cron_events(): void {
		foreach ( self::$cron_hooks as $hook ) {
			$timestamp = wp_next_scheduled( $hook );
			if ( $timestamp ) {
				wp_unschedule_event( $timestamp, $hook );
			}
			// Remove all future occurrences.
			wp_clear_scheduled_hook( $hook );
		}
	}

	/**
	 * Delete transients created by this plugin.
	 *
	 * @return void
	 */
	private static function clear_transients(): void {
		// Migration lock.
		delete_transient( 'ayurcrm_migration_lock' );

		// Import progress transients — pattern: ayurcrm_import_{batch_id}.
		// We iterate through known batch IDs stored in options if present.
		$active_imports = get_option( 'ayurcrm_active_import_batches', array() );

		if ( is_array( $active_imports ) ) {
			foreach ( $active_imports as $batch_id ) {
				if ( is_string( $batch_id ) ) {
					delete_transient( 'ayurcrm_import_' . sanitize_key( $batch_id ) );
					delete_transient( 'ayurcrm_import_progress_' . sanitize_key( $batch_id ) );
				}
			}
		}

		// General purpose transients.
		delete_transient( 'ayurcrm_dashboard_stats' );
		delete_transient( 'ayurcrm_lead_count_cache' );
	}
}
