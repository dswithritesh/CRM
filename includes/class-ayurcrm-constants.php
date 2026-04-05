<?php
/**
 * AyurCRM Constants & Path Helpers
 *
 * Central class for path resolution, URL generation, table name resolution,
 * and option key wrapping. All other classes should use these helpers instead
 * of constructing paths or option keys inline.
 *
 * @package AyurCRM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AyurCRM_Constants
 *
 * Provides static helper methods for plugin-wide path, URL, table name,
 * and option resolution.
 */
class AyurCRM_Constants {

	/**
	 * Canonical list of all AyurCRM table name slugs (without prefix).
	 *
	 * Used by the migrator, uninstaller, and any code that needs to enumerate
	 * all plugin tables.
	 *
	 * @var string[]
	 */
	const TABLE_SLUGS = array(
		'leads',
		'lead_activities',
		'lead_assignments',
		'lead_followups',
		'status_registry',
		'lead_meta',
		'imports',
		'import_rows',
		'exports',
		'logs',
		'notification_queue',
		'integration_logs',
	);

	/**
	 * Returns the absolute path to the AyurCRM upload directory.
	 *
	 * @return string Absolute filesystem path (no trailing slash).
	 */
	public static function get_upload_dir() {
		return wp_upload_dir()['basedir'] . '/ayurcrm';
	}

	/**
	 * Returns the public URL to the AyurCRM upload directory.
	 *
	 * @return string Public URL (no trailing slash).
	 */
	public static function get_upload_url() {
		return wp_upload_dir()['baseurl'] . '/ayurcrm';
	}

	/**
	 * Returns the absolute filesystem path for a specific import batch.
	 *
	 * @param string $import_uid Unique identifier for the import batch (UUID or slug).
	 * @return string Absolute filesystem path (no trailing slash).
	 */
	public static function get_import_dir( $import_uid ) {
		return self::get_upload_dir() . '/imports/' . sanitize_file_name( $import_uid );
	}

	/**
	 * Returns the absolute filesystem path for a specific export batch.
	 *
	 * @param string $export_uid Unique identifier for the export batch (UUID or slug).
	 * @return string Absolute filesystem path (no trailing slash).
	 */
	public static function get_export_dir( $export_uid ) {
		return self::get_upload_dir() . '/exports/' . sanitize_file_name( $export_uid );
	}

	/**
	 * Returns the fully-qualified (prefixed) database table name.
	 *
	 * Example: AyurCRM_Constants::get_table('leads') → 'wp_ayurcrm_leads'
	 *
	 * @param string $name Table slug (e.g. 'leads', 'lead_activities').
	 * @return string Full table name including WordPress DB prefix.
	 */
	public static function get_table( $name ) {
		global $wpdb;
		return $wpdb->prefix . 'ayurcrm_' . $name;
	}

	/**
	 * Returns the value of a plugin option.
	 *
	 * Automatically prepends the 'ayurcrm_' prefix so callers never need to
	 * hard-code the prefix.
	 *
	 * @param string $key     Option key without the 'ayurcrm_' prefix.
	 * @param mixed  $default Default value to return if the option does not exist.
	 * @return mixed Option value or $default.
	 */
	public static function get_option( $key, $default = null ) {
		return get_option( 'ayurcrm_' . $key, $default );
	}

	/**
	 * Updates (or creates) a plugin option.
	 *
	 * Automatically prepends the 'ayurcrm_' prefix.
	 *
	 * @param string $key   Option key without the 'ayurcrm_' prefix.
	 * @param mixed  $value Value to store.
	 * @return bool True on success, false on failure.
	 */
	public static function update_option( $key, $value ) {
		return update_option( 'ayurcrm_' . $key, $value );
	}
}
