<?php
/**
 * WordPress database wrapper and query helper.
 *
 * @package AyurCRM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AyurCRM_DB
 *
 * Provides a thin, type-safe wrapper around wpdb and centralises
 * all table-name resolution for the AyurCRM plugin.
 */
class AyurCRM_DB {

	/**
	 * Singleton instance.
	 *
	 * @var AyurCRM_DB|null
	 */
	private static ?AyurCRM_DB $instance = null;

	/**
	 * WordPress database object.
	 *
	 * @var wpdb
	 */
	private \wpdb $wpdb;

	// -----------------------------------------------------------------------
	// Singleton
	// -----------------------------------------------------------------------

	/**
	 * Private constructor.
	 */
	private function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
	}

	/**
	 * Return (and lazily create) the singleton instance.
	 *
	 * @return self
	 */
	public static function get_instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	// -----------------------------------------------------------------------
	// Table name getters
	// -----------------------------------------------------------------------

	/** @return string */
	public function leads(): string {
		return $this->wpdb->prefix . 'ayurcrm_leads';
	}

	/** @return string */
	public function lead_activities(): string {
		return $this->wpdb->prefix . 'ayurcrm_lead_activities';
	}

	/** @return string */
	public function lead_assignments(): string {
		return $this->wpdb->prefix . 'ayurcrm_lead_assignments';
	}

	/** @return string */
	public function lead_followups(): string {
		return $this->wpdb->prefix . 'ayurcrm_lead_followups';
	}

	/** @return string */
	public function lead_meta(): string {
		return $this->wpdb->prefix . 'ayurcrm_lead_meta';
	}

	/** @return string */
	public function imports(): string {
		return $this->wpdb->prefix . 'ayurcrm_imports';
	}

	/** @return string */
	public function import_rows(): string {
		return $this->wpdb->prefix . 'ayurcrm_import_rows';
	}

	/** @return string */
	public function exports(): string {
		return $this->wpdb->prefix . 'ayurcrm_exports';
	}

	/** @return string */
	public function notification_queue(): string {
		return $this->wpdb->prefix . 'ayurcrm_notification_queue';
	}

	/** @return string */
	public function status_registry(): string {
		return $this->wpdb->prefix . 'ayurcrm_status_registry';
	}

	/** @return string */
	public function logs(): string {
		return $this->wpdb->prefix . 'ayurcrm_logs';
	}

	/** @return string */
	public function integration_logs(): string {
		return $this->wpdb->prefix . 'ayurcrm_integration_logs';
	}

	// -----------------------------------------------------------------------
	// CRUD wrappers
	// -----------------------------------------------------------------------

	/**
	 * Insert a row into the given table.
	 *
	 * @param string  $table  Table name (use table getter methods).
	 * @param array   $data   Column => value pairs.
	 * @param array   $format Optional sprintf format strings for $data values.
	 * @return int|false Inserted row ID or false on failure.
	 */
	public function insert( string $table, array $data, array $format = [] ) {
		$result = $this->wpdb->insert( $table, $data, $format ?: null );
		if ( false === $result ) {
			return false;
		}
		return (int) $this->wpdb->insert_id;
	}

	/**
	 * Update rows in the given table.
	 *
	 * @param string $table        Table name.
	 * @param array  $data         Column => value pairs to update.
	 * @param array  $where        Column => value pairs for WHERE clause.
	 * @param array  $data_format  Optional format strings for $data.
	 * @param array  $where_format Optional format strings for $where.
	 * @return int|false Number of rows updated or false on failure.
	 */
	public function update(
		string $table,
		array $data,
		array $where,
		array $data_format = [],
		array $where_format = []
	) {
		return $this->wpdb->update(
			$table,
			$data,
			$where,
			$data_format ?: null,
			$where_format ?: null
		);
	}

	/**
	 * Delete rows from the given table.
	 *
	 * @param string $table        Table name.
	 * @param array  $where        Column => value pairs for WHERE clause.
	 * @param array  $where_format Optional format strings for $where.
	 * @return int|false Number of rows deleted or false on failure.
	 */
	public function delete( string $table, array $where, array $where_format = [] ) {
		return $this->wpdb->delete( $table, $where, $where_format ?: null );
	}

	// -----------------------------------------------------------------------
	// Query helpers
	// -----------------------------------------------------------------------

	/**
	 * Return a single row as an object.
	 *
	 * The SQL must be pre-prepared via {@see prepare()}.
	 *
	 * @param string $sql Pre-prepared SQL query.
	 * @return object|null
	 */
	public function get_row( string $sql ): ?object {
		$result = $this->wpdb->get_row( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL
		return $result ?: null;
	}

	/**
	 * Return an array of row objects.
	 *
	 * The SQL must be pre-prepared via {@see prepare()}.
	 *
	 * @param string $sql Pre-prepared SQL query.
	 * @return array
	 */
	public function get_results( string $sql ): array {
		$results = $this->wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL
		return is_array( $results ) ? $results : array();
	}

	/**
	 * Return a single value from a query.
	 *
	 * The SQL must be pre-prepared via {@see prepare()}.
	 *
	 * @param string $sql Pre-prepared SQL query.
	 * @return mixed
	 */
	public function get_var( string $sql ) {
		return $this->wpdb->get_var( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL
	}

	/**
	 * Prepare a SQL query safely.
	 *
	 * @param string $sql  SQL with placeholders.
	 * @param mixed  ...$args Values to substitute.
	 * @return string
	 */
	public function prepare( string $sql, ...$args ): string {
		return $this->wpdb->prepare( $sql, ...$args ); // phpcs:ignore WordPress.DB.PreparedSQL
	}

	/**
	 * Execute a SQL query.
	 *
	 * The SQL must be pre-prepared via {@see prepare()}.
	 *
	 * @param string $sql Pre-prepared SQL query.
	 * @return int|bool Number of rows affected or false on error.
	 */
	public function query( string $sql ) {
		return $this->wpdb->query( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL
	}

	// -----------------------------------------------------------------------
	// Utility
	// -----------------------------------------------------------------------

	/**
	 * Return the last wpdb error message.
	 *
	 * @return string
	 */
	public function get_last_error(): string {
		return (string) $this->wpdb->last_error;
	}

	/**
	 * Return the last query executed by wpdb.
	 *
	 * @return string
	 */
	public function get_last_query(): string {
		return (string) $this->wpdb->last_query;
	}

	/**
	 * Check whether the given table exists in the database.
	 *
	 * @param string $table Full table name (including prefix).
	 * @return bool
	 */
	public function table_exists( string $table ): bool {
		$sql    = $this->wpdb->prepare( 'SHOW TABLES LIKE %s', $table );
		$result = $this->wpdb->get_var( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL
		return $table === $result;
	}

	/**
	 * Return the database charset + collation string for use in CREATE TABLE.
	 *
	 * @return string  e.g. "DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
	 */
	public function get_charset_collate(): string {
		return $this->wpdb->get_charset_collate();
	}
}
