<?php
/**
 * Versioned migration runner with locking and abstract base class.
 *
 * @package AyurCRM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ---------------------------------------------------------------------------
// Abstract base class — every migration must extend this
// ---------------------------------------------------------------------------

/**
 * Abstract Class AyurCRM_Migration_Base
 *
 * All concrete migration classes must extend this and implement the three
 * abstract methods.
 */
abstract class AyurCRM_Migration_Base {

	/**
	 * Return the semantic version string this migration brings the DB to.
	 *
	 * @return string  e.g. '1.0.0'
	 */
	abstract public function version(): string;

	/**
	 * Execute the migration (forward direction only for Phase 1).
	 *
	 * @return void
	 */
	abstract public function up(): void;

	/**
	 * Short human-readable description of what the migration does.
	 *
	 * @return string
	 */
	abstract public function description(): string;
}

// ---------------------------------------------------------------------------
// Migration runner
// ---------------------------------------------------------------------------

/**
 * Class AyurCRM_Migrator
 *
 * Discovers pending migrations, acquires a distributed lock, and runs each
 * migration in ascending version order. Logs results via error_log() and,
 * if the logs table exists, to {prefix}ayurcrm_logs.
 */
class AyurCRM_Migrator {

	/**
	 * Transient key used as distributed migration lock.
	 */
	const LOCK_KEY = 'ayurcrm_migration_lock';

	/**
	 * Lock TTL in seconds (5 minutes).
	 */
	const LOCK_TTL = 300;

	// -----------------------------------------------------------------------
	// Public API
	// -----------------------------------------------------------------------

	/**
	 * Run any pending migrations if the DB schema is behind the plugin version.
	 *
	 * @return void
	 */
	public function maybe_run_migrations(): void {
		$current_version = (string) get_option( 'ayurcrm_db_version', '0.0.0' );

		if ( version_compare( $current_version, AYURCRM_DB_VERSION, '>=' ) ) {
			return;
		}

		// Acquire lock — use a unique token so we can verify we own the lock
		// after setting it, which reduces (though does not fully eliminate) the
		// race window inherent in transient-based locking.
		$lock_token = wp_generate_uuid4();
		set_transient( self::LOCK_KEY, $lock_token, self::LOCK_TTL );

		// Verify we still hold the lock; bail if another process won the race.
		if ( get_transient( self::LOCK_KEY ) !== $lock_token ) {
			return;
		}

		$pending = $this->get_pending_migrations( $current_version );

		if ( empty( $pending ) ) {
			delete_transient( self::LOCK_KEY );
			return;
		}

		foreach ( $pending as $migration ) {
			$success = $this->run_migration( $migration );

			if ( $success ) {
				// Advance the stored version after each successful migration.
				update_option( 'ayurcrm_db_version', $migration->version(), 'no' );
			} else {
				// Stop the chain on first failure to avoid cascade issues.
				break;
			}
		}

		delete_transient( self::LOCK_KEY );
	}

	// -----------------------------------------------------------------------
	// Private helpers
	// -----------------------------------------------------------------------

	/**
	 * Collect all registered migrations that are newer than $current_version.
	 *
	 * @param string $current_version The currently stored DB version.
	 * @return AyurCRM_Migration_Base[]
	 */
	private function get_pending_migrations( string $current_version ): array {
		$all_classes = array(
			'AyurCRM_Migration_0001_Base_Schema',
			'AyurCRM_Migration_0002_Activity_Table',
			'AyurCRM_Migration_0003_Followup_Table',
			'AyurCRM_Migration_0004_Assignment_Table',
			'AyurCRM_Migration_0005_Import_Export_Logs',
		);

		$pending = array();

		foreach ( $all_classes as $class ) {
			if ( ! class_exists( $class ) ) {
				continue;
			}

			/** @var AyurCRM_Migration_Base $migration */
			$migration = new $class();

			if ( version_compare( $migration->version(), $current_version, '>' ) ) {
				$pending[] = $migration;
			}
		}

		// Sort ascending by version.
		usort( $pending, function ( AyurCRM_Migration_Base $a, AyurCRM_Migration_Base $b ) {
			return version_compare( $a->version(), $b->version() );
		} );

		return $pending;
	}

	/**
	 * Execute a single migration and handle errors.
	 *
	 * @param AyurCRM_Migration_Base $migration The migration to run.
	 * @return bool True on success, false on failure.
	 */
	private function run_migration( AyurCRM_Migration_Base $migration ): bool {
		$label = sprintf(
			'AyurCRM Migration %s — %s',
			$migration->version(),
			$migration->description()
		);

		try {
			$migration->up();

			error_log( '[AyurCRM] SUCCESS: ' . $label ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions

			$this->write_log( 'info', 'migration', $label . ' completed successfully.' );

			return true;

		} catch ( \Throwable $e ) {
			$message = $label . ' FAILED: ' . $e->getMessage();

			error_log( '[AyurCRM] ERROR: ' . $message ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions

			$this->write_log( 'error', 'migration', $message );

			return false;
		}
	}

	/**
	 * Write a log entry to the DB logs table if it exists.
	 *
	 * @param string $level   Log level (info, error, warning).
	 * @param string $context Context/channel string.
	 * @param string $message Human-readable message.
	 * @return void
	 */
	private function write_log( string $level, string $context, string $message ): void {
		if ( ! class_exists( 'AyurCRM_DB' ) ) {
			return;
		}

		$db    = AyurCRM_DB::get_instance();
		$table = $db->logs();

		if ( ! $db->table_exists( $table ) ) {
			return;
		}

		$db->insert(
			$table,
			array(
				'level'      => $level,
				'context'    => $context,
				'message'    => $message,
				'created_at' => current_time( 'mysql', true ),
			),
			array( '%s', '%s', '%s', '%s' )
		);
	}
}
