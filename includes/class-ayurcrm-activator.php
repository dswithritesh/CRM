<?php
/**
 * AyurCRM Plugin Activator
 *
 * Handles everything that must happen when the plugin is activated via
 * WordPress. Intentionally lightweight — no DB schema work, no migrations,
 * no imports, no heavy loops.
 *
 * What this file IS allowed to do:
 *  - Write a small number of WP options (fast, autoloaded)
 *  - Call AyurCRM_Capabilities::register_roles() (pure WP user API)
 *  - Schedule WP-Cron events
 *  - Set a transient flag so the deferred migration runner knows to run
 *
 * What this file MUST NOT do:
 *  - Run dbDelta() or any CREATE TABLE / ALTER TABLE
 *  - Query the database for lead records
 *  - Scan directories or parse files
 *  - Produce any output
 *
 * @package AyurCRM
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AyurCRM_Activator
 */
class AyurCRM_Activator {

	/**
	 * WP-Cron event definitions.
	 *
	 * Each entry: [ hook, recurrence, offset_seconds ]
	 * offset_seconds = how far in the future the first run is scheduled.
	 *
	 * @var array[]
	 */
	private static $cron_events = array(
		array(
			'hook'       => 'ayurcrm_cron_followup_check',
			'recurrence' => 'every_15_minutes',
			'offset'     => 5 * MINUTE_IN_SECONDS,
		),
		array(
			'hook'       => 'ayurcrm_cron_sla_check',
			'recurrence' => 'every_15_minutes',
			'offset'     => 7 * MINUTE_IN_SECONDS,
		),
		array(
			'hook'       => 'ayurcrm_cron_flush_notifications',
			'recurrence' => 'every_5_minutes',
			'offset'     => 2 * MINUTE_IN_SECONDS,
		),
		array(
			'hook'       => 'ayurcrm_cron_export_process',
			'recurrence' => 'every_10_minutes',
			'offset'     => 3 * MINUTE_IN_SECONDS,
		),
		array(
			'hook'       => 'ayurcrm_cron_import_process',
			'recurrence' => 'every_10_minutes',
			'offset'     => 4 * MINUTE_IN_SECONDS,
		),
	);

	/**
	 * Plugin activation handler.
	 *
	 * Called by register_activation_hook() in ayurcrm.php.
	 * Must complete in under 30 seconds on any standard hosting environment.
	 *
	 * @return void
	 */
	public static function activate() {
		self::register_custom_cron_intervals();
		self::register_roles();
		self::schedule_cron_events();
		self::set_activation_options();
		self::flag_needs_migration();

		// Flush rewrite rules last so any future public endpoints resolve.
		flush_rewrite_rules( false );
	}

	// -----------------------------------------------------------------------
	// Individual activation steps
	// -----------------------------------------------------------------------

	/**
	 * Register custom cron intervals inline during activation.
	 *
	 * wp_schedule_event() silently fails if the interval does not yet exist
	 * in the cron_schedules filter. Because filters may not be registered at
	 * activation time (plugins_loaded has not fired), we add the intervals
	 * directly here as a one-time guard so the schedule calls below succeed.
	 *
	 * @return void
	 */
	private static function register_custom_cron_intervals() {
		add_filter(
			'cron_schedules',
			static function ( $schedules ) {
				if ( ! isset( $schedules['every_5_minutes'] ) ) {
					$schedules['every_5_minutes'] = array(
						'interval' => 5 * MINUTE_IN_SECONDS,
						'display'  => 'Every 5 Minutes',
					);
				}
				if ( ! isset( $schedules['every_10_minutes'] ) ) {
					$schedules['every_10_minutes'] = array(
						'interval' => 10 * MINUTE_IN_SECONDS,
						'display'  => 'Every 10 Minutes',
					);
				}
				if ( ! isset( $schedules['every_15_minutes'] ) ) {
					$schedules['every_15_minutes'] = array(
						'interval' => 15 * MINUTE_IN_SECONDS,
						'display'  => 'Every 15 Minutes',
					);
				}
				return $schedules;
			},
			1
		);
	}

	/**
	 * Register CRM roles and capabilities.
	 *
	 * Delegates to AyurCRM_Capabilities if available. Safe to call on every
	 * activation — add_role() is idempotent (WordPress skips existing roles).
	 *
	 * @return void
	 */
	private static function register_roles() {
		if ( class_exists( 'AyurCRM_Capabilities' ) ) {
			AyurCRM_Capabilities::register_roles();
		}
	}

	/**
	 * Schedule all WP-Cron recurring events.
	 *
	 * Each event is only scheduled if it is not already in the cron queue,
	 * preventing duplicate events on re-activation.
	 *
	 * @return void
	 */
	private static function schedule_cron_events() {
		foreach ( self::$cron_events as $event ) {
			if ( ! wp_next_scheduled( $event['hook'] ) ) {
				wp_schedule_event(
					time() + $event['offset'],
					$event['recurrence'],
					$event['hook']
				);
			}
		}
	}

	/**
	 * Write activation-time options.
	 *
	 * Only sets options that do not already exist (add_option is a no-op if
	 * the option key already has a value — safe on re-activation).
	 *
	 * @return void
	 */
	private static function set_activation_options() {
		// Record first-ever activation timestamp (never overwritten).
		add_option( 'ayurcrm_activation_date', current_time( 'mysql' ), '', false );

		// Mark plugin as active.
		update_option( 'ayurcrm_plugin_active', '1', false );

		// Seed default general settings if not already present.
		if ( ! get_option( 'ayurcrm_settings_general' ) ) {
			$defaults_file = defined( 'AYURCRM_PATH' )
				? AYURCRM_PATH . 'config/default-settings.php'
				: trailingslashit( plugin_dir_path( __FILE__ ) . '..' ) . 'config/default-settings.php';

			if ( file_exists( $defaults_file ) ) {
				$defaults = include $defaults_file;
				if ( is_array( $defaults ) && isset( $defaults['general'] ) ) {
					update_option( 'ayurcrm_settings_general', $defaults['general'], false );
				}
				if ( is_array( $defaults ) && isset( $defaults['notifications'] ) ) {
					update_option( 'ayurcrm_settings_notifications', $defaults['notifications'], false );
				}
				if ( is_array( $defaults ) && isset( $defaults['integrations'] ) ) {
					update_option( 'ayurcrm_settings_integrations', $defaults['integrations'], false );
				}
			}
		}
	}

	/**
	 * Set the transient flag that tells the deferred migration runner to fire.
	 *
	 * The runner checks this on admin_init (lightweight transient read).
	 * TTL is generous — if the admin never loads within 1 hour, the version
	 * comparison fallback in the migrator will still catch it.
	 *
	 * @return void
	 */
	private static function flag_needs_migration() {
		set_transient( 'ayurcrm_needs_migration', '1', HOUR_IN_SECONDS );
	}
}
