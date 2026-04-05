<?php
/**
 * AyurCRM Activation Handler
 *
 * Lightweight handler for plugin activation. Sets up options and schedules
 * cron events. Must NOT perform DB schema operations (migrations run on the
 * first admin_init after activation via the ayurcrm_needs_setup flag).
 *
 * @package AyurCRM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AyurCRM_Activator
 *
 * Called once when the administrator activates the plugin.
 */
class AyurCRM_Activator {

	/**
	 * Run all activation tasks.
	 *
	 * @return void
	 */
	public static function activate() {
		// Signal that the setup wizard / migrator should run on next admin load.
		update_option( 'ayurcrm_needs_setup', 1 );

		// Record activation timestamp for licensing and support purposes.
		if ( ! get_option( 'ayurcrm_activation_date' ) ) {
			update_option( 'ayurcrm_activation_date', time() );
		}

		// Register CRM roles and capabilities.
		require_once AYURCRM_PATH . 'includes/class-ayurcrm-capabilities.php';
		AyurCRM_Capabilities::register_roles();

		// Schedule cron events (skips if already scheduled).
		self::schedule_cron_events();

		// Flush rewrite rules so REST endpoints are immediately available.
		flush_rewrite_rules();
	}

	/**
	 * Schedule all plugin cron events.
	 *
	 * Custom cron intervals must be registered before calling wp_schedule_event()
	 * because WordPress validates the recurrence string against the known schedule
	 * list at the time of scheduling. We register the intervals inline here so
	 * they are available during activation even before the plugin's cron_schedules
	 * filter fires on a normal page load.
	 *
	 * @return void
	 */
	private static function schedule_cron_events() {
		// Register custom intervals inline so they exist during activation.
		add_filter(
			'cron_schedules',
			static function ( $schedules ) {
				if ( ! isset( $schedules['ayurcrm_every_5_minutes'] ) ) {
					$schedules['ayurcrm_every_5_minutes'] = array(
						'interval' => 5 * MINUTE_IN_SECONDS,
						'display'  => __( 'Every 5 Minutes (AyurCRM)', 'ayurcrm' ),
					);
				}
				if ( ! isset( $schedules['ayurcrm_every_10_minutes'] ) ) {
					$schedules['ayurcrm_every_10_minutes'] = array(
						'interval' => 10 * MINUTE_IN_SECONDS,
						'display'  => __( 'Every 10 Minutes (AyurCRM)', 'ayurcrm' ),
					);
				}
				return $schedules;
			}
		);

		$events = array(
			array(
				'hook'  => 'ayurcrm_cron_followup_reminders',
				'recur' => 'hourly',
			),
			array(
				'hook'  => 'ayurcrm_cron_overdue_check',
				'recur' => 'daily',
			),
			array(
				'hook'  => 'ayurcrm_cron_process_notifications',
				'recur' => 'ayurcrm_every_5_minutes',
			),
			array(
				'hook'  => 'ayurcrm_cron_cleanup_exports',
				'recur' => 'daily',
			),
			array(
				'hook'  => 'ayurcrm_cron_process_import_queue',
				'recur' => 'ayurcrm_every_10_minutes',
			),
		);

		foreach ( $events as $event ) {
			if ( ! wp_next_scheduled( $event['hook'] ) ) {
				wp_schedule_event( time(), $event['recur'], $event['hook'] );
			}
		}
	}
}
