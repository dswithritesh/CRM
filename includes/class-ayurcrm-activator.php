<?php
/**
 * Lightweight activation handler.
 *
 * @package AyurCRM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AyurCRM_Activator
 *
 * Handles plugin activation tasks. Must remain extremely lightweight —
 * no DB migrations, no schema creation, no heavy loops.
 */
class AyurCRM_Activator {

	/**
	 * Run on plugin activation.
	 *
	 * @return void
	 */
	public static function activate(): void {
		if ( ! self::check_requirements() ) {
			return;
		}

		// Store lightweight flags (not autoloaded).
		add_option( 'ayurcrm_needs_setup',       '1',             '', 'no' );
		add_option( 'ayurcrm_activation_time',   time(),          '', 'no' );
		add_option( 'ayurcrm_plugin_version',    AYURCRM_VERSION, '', 'no' );

		// Register custom roles and capabilities.
		if ( class_exists( 'AyurCRM_Capabilities' ) ) {
			AyurCRM_Capabilities::register_roles_and_caps();
		}

		// Schedule cron events if cron class is available.
		if ( class_exists( 'AyurCRM_Cron' ) ) {
			AyurCRM_Cron::schedule_events();
		}

		// Flag that rewrite rules should be flushed on next admin_init
		// (avoids calling flush_rewrite_rules() directly during activation).
		add_option( 'ayurcrm_flush_rewrite_rules', '1', '', 'no' );
	}

	/**
	 * Verify PHP and WordPress version requirements.
	 *
	 * Deactivates the plugin and terminates execution with a user-friendly
	 * message if requirements are not met.
	 *
	 * @return bool True when requirements pass, false otherwise.
	 */
	private static function check_requirements(): bool {
		if ( version_compare( PHP_VERSION, AYURCRM_MIN_PHP, '<' ) ) {
			deactivate_plugins( AYURCRM_BASENAME );
			wp_die(
				sprintf(
					/* translators: 1: minimum PHP version, 2: current PHP version */
					esc_html__(
						'AyurCRM requires PHP %1$s or higher. Your server is running PHP %2$s. Please upgrade PHP and try again.',
						'ayurcrm'
					),
					esc_html( AYURCRM_MIN_PHP ),
					esc_html( PHP_VERSION )
				),
				esc_html__( 'Plugin Activation Error', 'ayurcrm' ),
				array( 'back_link' => true )
			);
			return false;
		}

		global $wp_version;
		if ( version_compare( $wp_version, AYURCRM_MIN_WP, '<' ) ) {
			deactivate_plugins( AYURCRM_BASENAME );
			wp_die(
				sprintf(
					/* translators: 1: minimum WP version, 2: current WP version */
					esc_html__(
						'AyurCRM requires WordPress %1$s or higher. Your installation is running WordPress %2$s. Please upgrade WordPress and try again.',
						'ayurcrm'
					),
					esc_html( AYURCRM_MIN_WP ),
					esc_html( $wp_version )
				),
				esc_html__( 'Plugin Activation Error', 'ayurcrm' ),
				array( 'back_link' => true )
			);
			return false;
		}

		return true;
	}
}
