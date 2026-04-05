<?php
/**
 * Default plugin settings.
 *
 * @package AyurCRM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AyurCRM_Default_Settings
 *
 * Provides typed defaults for every settings section and helpers to read
 * and write settings from/to wp_options.
 */
class AyurCRM_Default_Settings {

	/**
	 * Return the full defaults array organised by section.
	 *
	 * Note: This method intentionally uses WordPress functions such as
	 * wp_timezone_string() and get_bloginfo() — call it only after
	 * WordPress is fully loaded.
	 *
	 * @return array
	 */
	public static function get_defaults(): array {
		return array(
			'general'       => array(
				'timezone'       => function_exists( 'wp_timezone_string' ) ? wp_timezone_string() : 'UTC',
				'date_format'    => 'd/m/Y',
				'time_format'    => 'H:i',
				'leads_per_page' => 25,
				'default_country'=> 'India',
				'currency'       => 'INR',
				'clinic_name'    => get_bloginfo( 'name' ),
				'clinic_email'   => get_bloginfo( 'admin_email' ),
			),

			'duplicate'     => array(
				'strategy'            => 'flag',   // block | flag | append
				'hash_method'         => 'phone_primary',
				'time_window_seconds' => 60,
			),

			'assignment'    => array(
				'default_method'      => 'manual',
				'auto_assign_enabled' => false,
				'round_robin_enabled' => false,
			),

			'notifications' => array(
				'new_lead_email'    => true,
				'assignment_email'  => true,
				'followup_reminder' => true,
				'overdue_alert'     => true,
				'admin_summary'     => false,
				'from_name'         => get_bloginfo( 'name' ),
				'from_email'        => get_bloginfo( 'admin_email' ),
			),

			'import'        => array(
				'chunk_size'        => defined( 'AYURCRM_CHUNK_SIZE' ) ? AYURCRM_CHUNK_SIZE : 50,
				'duplicate_strategy'=> 'flag',
				'required_fields'   => array( 'full_name', 'phone' ),
			),

			'export'        => array(
				'batch_size'      => defined( 'AYURCRM_EXPORT_BATCH' ) ? AYURCRM_EXPORT_BATCH : 500,
				'expiry_hours'    => 24,
				'default_columns' => array( 'full_name', 'phone', 'email', 'status', 'source', 'created_at' ),
			),

			'sla'           => array(
				'first_response_minutes' => 30,
				'followup_hours'         => 24,
				'enabled'                => false,
			),
		);
	}

	/**
	 * Read a single setting value, falling back to the built-in default.
	 *
	 * @param string $section Settings section key (e.g. 'general').
	 * @param string $key     Setting key within the section.
	 * @param mixed  $default Override the built-in default. Pass null to use built-in.
	 * @return mixed
	 */
	public static function get_option( string $section, string $key, $default = null ) {
		$option_key = 'ayurcrm_settings_' . $section;
		$saved      = get_option( $option_key );

		if ( is_array( $saved ) && array_key_exists( $key, $saved ) ) {
			return $saved[ $key ];
		}

		// Fall back to provided default, then to built-in defaults.
		if ( null !== $default ) {
			return $default;
		}

		$defaults = static::get_defaults();
		return $defaults[ $section ][ $key ] ?? null;
	}

	/**
	 * Persist an entire settings section to wp_options.
	 *
	 * The option is NOT autoloaded to avoid adding weight to every page load.
	 *
	 * @param string $section Settings section key.
	 * @param array  $data    Associative array of key => value pairs.
	 * @return bool True if the option was successfully saved.
	 */
	public static function save_section( string $section, array $data ): bool {
		$option_key = 'ayurcrm_settings_' . $section;

		if ( get_option( $option_key ) === false ) {
			// Option doesn't exist yet — use add_option so we can set autoload.
			return add_option( $option_key, $data, '', 'no' );
		}

		return update_option( $option_key, $data, 'no' );
	}
}
