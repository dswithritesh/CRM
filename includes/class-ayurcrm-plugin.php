<?php
/**
 * Plugin orchestrator — singleton pattern.
 *
 * @package AyurCRM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AyurCRM_Plugin
 *
 * Central orchestrator that wires together all plugin modules via the loader.
 */
class AyurCRM_Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var AyurCRM_Plugin|null
	 */
	private static ?AyurCRM_Plugin $instance = null;

	/**
	 * Hook/filter loader.
	 *
	 * @var AyurCRM_Loader
	 */
	private AyurCRM_Loader $loader;

	/**
	 * Whether init() has already run.
	 *
	 * @var bool
	 */
	private bool $initialized = false;

	// -----------------------------------------------------------------------
	// Singleton
	// -----------------------------------------------------------------------

	/**
	 * Private constructor — use get_instance().
	 */
	private function __construct() {
		$this->loader = new AyurCRM_Loader();
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
	// Public API
	// -----------------------------------------------------------------------

	/**
	 * Initialise the plugin — the ONLY public entry point.
	 *
	 * Called via `plugins_loaded` action from the main plugin file.
	 *
	 * @return void
	 */
	public function init(): void {
		if ( $this->initialized ) {
			return;
		}
		$this->initialized = true;

		$this->load_dependencies();

		// Register upload constants on init (needs WP upload dir).
		add_action( 'init', array( AyurCRM_Constants::class, 'define_upload_constants' ), 1 );

		// Load plugin text domain.
		add_action( 'init', array( $this, 'load_textdomain' ) );

		// Run DB migrations on admin_init.
		add_action( 'admin_init', array( $this, 'maybe_run_migrations' ) );

		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_ajax_hooks();
		$this->define_rest_hooks();
		$this->define_cron_hooks();

		$this->loader->run();
	}

	/**
	 * Load plugin text domain for translations.
	 *
	 * @return void
	 */
	public function load_textdomain(): void {
		load_plugin_textdomain(
			'ayurcrm',
			false,
			dirname( AYURCRM_BASENAME ) . '/languages/'
		);
	}

	/**
	 * Trigger the DB migrator (guarded — migrator class may not be loaded if
	 * the database module failed).
	 *
	 * @return void
	 */
	public function maybe_run_migrations(): void {
		if ( class_exists( 'AyurCRM_Migrator' ) ) {
			( new AyurCRM_Migrator() )->maybe_run_migrations();
		}
	}

	// -----------------------------------------------------------------------
	// Dependency loading
	// -----------------------------------------------------------------------

	/**
	 * Load all required module files via the fault-tolerant loader.
	 *
	 * Each load is individually guarded; a failed optional module does NOT
	 * crash the plugin.
	 *
	 * @return void
	 */
	private function load_dependencies(): void {
		$loader = $this->loader;

		// Runtime constants (upload paths).
		$loader->load_module( AYURCRM_PATH . 'includes/class-ayurcrm-constants.php' );

		// Capabilities / roles.
		$loader->load_module( AYURCRM_PATH . 'includes/class-ayurcrm-capabilities.php' );

		// Database layer.
		$loader->load_module( AYURCRM_PATH . 'database/class-ayurcrm-db.php' );

		// Migrator + abstract base.
		$loader->load_module( AYURCRM_PATH . 'database/class-ayurcrm-migrator.php' );

		// Migration files (Phase 1).
		$migration_files = array(
			'database/migrations/migration-0001-base-schema.php',
			'database/migrations/migration-0002-activity-table.php',
			'database/migrations/migration-0003-followup-table.php',
			'database/migrations/migration-0004-assignment-table.php',
			'database/migrations/migration-0005-import-export-logs.php',
		);

		foreach ( $migration_files as $file ) {
			$loader->load_module( AYURCRM_PATH . $file );
		}

		// Configuration.
		$loader->load_module( AYURCRM_PATH . 'config/default-pipeline.php' );
		$loader->load_module( AYURCRM_PATH . 'config/default-settings.php' );

		// Future phases — guard every load so missing files are silently skipped.
		// Admin modules (Phase 2+).
		$admin_files = array(
			// e.g. 'admin/class-ayurcrm-admin.php',
		);

		if ( is_admin() ) {
			foreach ( $admin_files as $file ) {
				$loader->load_module( AYURCRM_PATH . $file );
			}
		}

		// AJAX handlers (Phase 3+).
		$ajax_files = array(
			// e.g. 'includes/ajax/class-ayurcrm-ajax-leads.php',
		);

		if ( wp_doing_ajax() ) {
			foreach ( $ajax_files as $file ) {
				$loader->load_module( AYURCRM_PATH . $file );
			}
		}

		// REST API (Phase 4+).
		$rest_files = array(
			// e.g. 'includes/rest/class-ayurcrm-rest-leads.php',
		);

		foreach ( $rest_files as $file ) {
			$loader->load_module( AYURCRM_PATH . $file );
		}
	}

	// -----------------------------------------------------------------------
	// Hook definitions
	// -----------------------------------------------------------------------

	/**
	 * Register admin-only hooks.
	 *
	 * @return void
	 */
	private function define_admin_hooks(): void {
		if ( ! is_admin() ) {
			return;
		}
		// Phase 2+ admin hooks registered here.
	}

	/**
	 * Register public-facing hooks.
	 *
	 * @return void
	 */
	private function define_public_hooks(): void {
		// Phase 5+ public hooks registered here.
	}

	/**
	 * Register AJAX hooks.
	 *
	 * @return void
	 */
	private function define_ajax_hooks(): void {
		if ( ! wp_doing_ajax() ) {
			return;
		}
		// Phase 3+ AJAX hooks registered here.
	}

	/**
	 * Register REST API hooks.
	 *
	 * @return void
	 */
	private function define_rest_hooks(): void {
		// Phase 4+ REST hooks registered here.
	}

	/**
	 * Register cron hooks.
	 *
	 * @return void
	 */
	private function define_cron_hooks(): void {
		// Phase 6+ cron hooks registered here.
	}
}
