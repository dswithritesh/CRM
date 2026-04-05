<?php
/**
 * AyurCRM Plugin Orchestrator
 *
 * Singleton class that owns the loader instance, requires all module files
 * in dependency order, and registers every WordPress hook. This is the only
 * class that wires the plugin together.
 *
 * Rules enforced here:
 *  - Zero DB queries at init time
 *  - Zero output at any point
 *  - All admin-only services loaded behind is_admin() gates
 *  - All heavy work deferred to specific WP hooks (admin_init, wp_loaded, etc.)
 *  - Missing module files are logged and skipped — never fatal
 *
 * @package AyurCRM
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AyurCRM_Plugin
 *
 * Central orchestrator. One instance per request, created via get_instance().
 */
class AyurCRM_Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var AyurCRM_Plugin|null
	 */
	private static $instance = null;

	/**
	 * The hook loader / queue manager.
	 *
	 * @var AyurCRM_Loader
	 */
	private $loader;

	/**
	 * Plugin version snapshot (from constant).
	 *
	 * @var string
	 */
	private $version;

	/**
	 * Private constructor — use get_instance().
	 */
	private function __construct() {
		$this->version = defined( 'AYURCRM_VERSION' ) ? AYURCRM_VERSION : '1.0.0';
		$this->loader  = new AyurCRM_Loader();
	}

	/**
	 * Return (and lazily create) the singleton instance.
	 *
	 * @return AyurCRM_Plugin
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	// -----------------------------------------------------------------------
	// Boot entry point
	// -----------------------------------------------------------------------

	/**
	 * Initialise the plugin.
	 *
	 * Called once from ayurcrm.php via the plugins_loaded hook. Loads all
	 * foundation files, registers WordPress hooks through the loader queue,
	 * then flushes the queue with loader->run(). No heavy work here.
	 *
	 * @return void
	 */
	public function init() {
		$this->load_foundation_modules();
		$this->define_global_hooks();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_api_hooks();
		$this->define_cron_hooks();
		$this->loader->run();
	}

	// -----------------------------------------------------------------------
	// Module loading
	// -----------------------------------------------------------------------

	/**
	 * Load foundation-layer files that must be available everywhere.
	 *
	 * Loads in strict dependency order. Each file is guarded — a missing file
	 * is logged and skipped rather than causing a fatal error.
	 *
	 * @return void
	 */
	private function load_foundation_modules() {
		$path = defined( 'AYURCRM_PATH' ) ? AYURCRM_PATH : trailingslashit( plugin_dir_path( __FILE__ ) . '..' );

		// Core helpers and constants wrapper — no dependencies.
		$this->loader->load_module( $path . 'includes/class-ayurcrm-constants.php' );
		$this->loader->load_module( $path . 'includes/class-ayurcrm-helpers.php' );
		$this->loader->load_module( $path . 'includes/class-ayurcrm-hooks.php' );
		$this->loader->load_module( $path . 'includes/class-ayurcrm-capabilities.php' );

		// Activation / deactivation — required here so the hooks registered
		// in ayurcrm.php can resolve the class names at activation time.
		$this->loader->load_module( $path . 'includes/class-ayurcrm-activator.php' );
		$this->loader->load_module( $path . 'includes/class-ayurcrm-deactivator.php' );

		// Database layer.
		$this->loader->load_module( $path . 'database/class-ayurcrm-db.php' );
		$this->loader->load_module( $path . 'database/class-ayurcrm-migrator.php' );

		// Domain modules — loaded only if the file exists (Phase 2+).
		$domain_modules = array(
			// Leads domain.
			'modules/leads/class-ayurcrm-lead-model.php',
			'modules/leads/class-ayurcrm-lead-sanitizer.php',
			'modules/leads/class-ayurcrm-lead-validator.php',
			'modules/leads/class-ayurcrm-lead-duplicate.php',
			'modules/leads/class-ayurcrm-lead-repository.php',
			'modules/leads/class-ayurcrm-lead-service.php',
			// Activities domain.
			'modules/activities/class-ayurcrm-activity-model.php',
			'modules/activities/class-ayurcrm-activity-repository.php',
			'modules/activities/class-ayurcrm-activity-service.php',
			// Pipeline domain.
			'modules/pipeline/class-ayurcrm-pipeline-registry.php',
			'modules/pipeline/class-ayurcrm-pipeline-service.php',
			'modules/pipeline/class-ayurcrm-status-transition.php',
			// Assignments domain.
			'modules/assignments/class-ayurcrm-assignment-model.php',
			'modules/assignments/class-ayurcrm-assignment-repository.php',
			'modules/assignments/class-ayurcrm-assignment-service.php',
			'modules/assignments/class-ayurcrm-assignment-router.php',
			// Follow-ups domain.
			'modules/followups/class-ayurcrm-followup-model.php',
			'modules/followups/class-ayurcrm-followup-repository.php',
			'modules/followups/class-ayurcrm-followup-service.php',
			'modules/followups/class-ayurcrm-followup-sla.php',
			// Users domain.
			'modules/users/class-ayurcrm-user-repository.php',
			'modules/users/class-ayurcrm-user-service.php',
			'modules/users/class-ayurcrm-user-capability-guard.php',
			// Notifications domain.
			'modules/notifications/class-ayurcrm-notification-service.php',
			'modules/notifications/class-ayurcrm-notification-email.php',
			'modules/notifications/class-ayurcrm-notification-dispatcher.php',
			// Reporting domain.
			'modules/reporting/class-ayurcrm-report-cache.php',
			'modules/reporting/class-ayurcrm-report-engine.php',
			'modules/reporting/class-ayurcrm-report-leads.php',
			'modules/reporting/class-ayurcrm-report-pipeline.php',
			'modules/reporting/class-ayurcrm-report-campaigns.php',
			'modules/reporting/class-ayurcrm-report-staff.php',
			// Capture layer.
			'modules/capture/class-ayurcrm-capture-form.php',
			'modules/capture/class-ayurcrm-capture-ajax.php',
			// Import / Export.
			'modules/import/class-ayurcrm-import-parser.php',
			'modules/import/class-ayurcrm-import-mapper.php',
			'modules/import/class-ayurcrm-import-validator.php',
			'modules/import/class-ayurcrm-import-processor.php',
			'modules/import/class-ayurcrm-importer.php',
			'modules/export/class-ayurcrm-export-builder.php',
			'modules/export/class-ayurcrm-export-batcher.php',
			'modules/export/class-ayurcrm-exporter.php',
		);

		foreach ( $domain_modules as $relative_path ) {
			$full_path = $path . $relative_path;
			if ( file_exists( $full_path ) ) {
				$this->loader->load_module( $full_path );
			}
			// Silently skip files that don't exist yet — they belong to
			// future phases and will be added incrementally.
		}
	}

	// -----------------------------------------------------------------------
	// Hook registration methods — queue only, no execution
	// -----------------------------------------------------------------------

	/**
	 * Register hooks that must fire on every request (front and admin).
	 *
	 * @return void
	 */
	private function define_global_hooks() {
		// Runtime upload constants need wp_upload_dir() — defer to init.
		if ( class_exists( 'AyurCRM_Constants' ) ) {
			$this->loader->add_action( 'init', AyurCRM_Constants::get_instance(), 'define_upload_constants', 1, 0 );
		}

		// Load text domain for i18n.
		$this->loader->add_action( 'init', $this, 'load_textdomain', 5, 0 );

		// Pipeline registry initialisation (populates in-memory stage list).
		if ( class_exists( 'AyurCRM_Pipeline_Registry' ) ) {
			$this->loader->add_action( 'init', AyurCRM_Pipeline_Registry::get_instance(), 'load', 10, 0 );
		}

		// REST API capture endpoint registration.
		if ( class_exists( 'AyurCRM_Capture_REST' ) ) {
			$this->loader->add_action( 'rest_api_init', new AyurCRM_Capture_REST(), 'register_routes', 10, 0 );
		}
	}

	/**
	 * Register hooks that fire only in the WordPress admin context.
	 *
	 * @return void
	 */
	private function define_admin_hooks() {
		if ( ! is_admin() ) {
			return;
		}

		// Deferred migration runner — only if class is available.
		if ( class_exists( 'AyurCRM_Migrator' ) ) {
			$this->loader->add_action( 'admin_init', AyurCRM_Migrator::get_instance(), 'maybe_run', 5, 0 );
		}

		// Admin menu + pages — loaded on demand.
		$path = defined( 'AYURCRM_PATH' ) ? AYURCRM_PATH : trailingslashit( plugin_dir_path( __FILE__ ) . '..' );
		$this->loader->load_module( $path . 'admin/class-ayurcrm-admin.php' );
		$this->loader->load_module( $path . 'admin/class-ayurcrm-admin-assets.php' );

		if ( class_exists( 'AyurCRM_Admin' ) ) {
			$admin = new AyurCRM_Admin();
			$this->loader->add_action( 'admin_menu', $admin, 'register_menus', 10, 0 );
			$this->loader->add_action( 'admin_init', $admin, 'handle_admin_post', 20, 0 );
		}

		if ( class_exists( 'AyurCRM_Admin_Assets' ) ) {
			$assets = new AyurCRM_Admin_Assets();
			$this->loader->add_action( 'admin_enqueue_scripts', $assets, 'enqueue', 10, 1 );
		}

		// AJAX handlers — admin-only, loaded on demand.
		$ajax_modules = array(
			'admin/ajax/class-ayurcrm-ajax-leads.php',
			'admin/ajax/class-ayurcrm-ajax-assignments.php',
			'admin/ajax/class-ayurcrm-ajax-followups.php',
			'admin/ajax/class-ayurcrm-ajax-notes.php',
			'admin/ajax/class-ayurcrm-ajax-import.php',
			'admin/ajax/class-ayurcrm-ajax-export.php',
			'admin/ajax/class-ayurcrm-ajax-dashboard.php',
		);

		$ajax_class_map = array(
			'AyurCRM_Ajax_Leads',
			'AyurCRM_Ajax_Assignments',
			'AyurCRM_Ajax_Followups',
			'AyurCRM_Ajax_Notes',
			'AyurCRM_Ajax_Import',
			'AyurCRM_Ajax_Export',
			'AyurCRM_Ajax_Dashboard',
		);

		foreach ( $ajax_modules as $index => $relative ) {
			$full = $path . $relative;
			if ( file_exists( $full ) ) {
				$this->loader->load_module( $full );
				$class = $ajax_class_map[ $index ];
				if ( class_exists( $class ) ) {
					$instance = new $class();
					$this->loader->add_action( 'wp_loaded', $instance, 'register_ajax_hooks', 10, 0 );
				}
			}
		}

		// Notification queue cron flusher registration in admin context.
		if ( class_exists( 'AyurCRM_Notification_Dispatcher' ) ) {
			$this->loader->add_action( 'admin_init', new AyurCRM_Notification_Dispatcher(), 'register', 15, 0 );
		}
	}

	/**
	 * Register hooks that fire only on public (non-admin) requests.
	 *
	 * @return void
	 */
	private function define_public_hooks() {
		if ( is_admin() ) {
			return;
		}

		// Public shortcode capture form.
		if ( class_exists( 'AyurCRM_Capture_Form' ) ) {
			$capture = new AyurCRM_Capture_Form();
			$this->loader->add_action( 'init', $capture, 'register_shortcodes', 10, 0 );
		}

		// Public AJAX capture (nopriv).
		if ( class_exists( 'AyurCRM_Capture_Ajax' ) ) {
			$ajax_capture = new AyurCRM_Capture_Ajax();
			$this->loader->add_action( 'wp_loaded', $ajax_capture, 'register_ajax_hooks', 10, 0 );
		}
	}

	/**
	 * Register REST API route hooks.
	 *
	 * Routes for internal CRUD endpoints — separate from capture endpoint.
	 *
	 * @return void
	 */
	private function define_api_hooks() {
		$path = defined( 'AYURCRM_PATH' ) ? AYURCRM_PATH : trailingslashit( plugin_dir_path( __FILE__ ) . '..' );

		$rest_modules = array(
			'api/class-ayurcrm-rest-controller.php',
			'api/class-ayurcrm-rest-leads.php',
			'api/class-ayurcrm-rest-capture.php',
			'api/class-ayurcrm-rest-webhook.php',
			'api/class-ayurcrm-rest-reports.php',
			'api/class-ayurcrm-rest-users.php',
		);

		$rest_classes = array(
			null, // base controller — no registration needed
			'AyurCRM_REST_Leads',
			'AyurCRM_REST_Capture',
			'AyurCRM_REST_Webhook',
			'AyurCRM_REST_Reports',
			'AyurCRM_REST_Users',
		);

		foreach ( $rest_modules as $index => $relative ) {
			$full = $path . $relative;
			if ( file_exists( $full ) ) {
				$this->loader->load_module( $full );
				$class = $rest_classes[ $index ];
				if ( $class && class_exists( $class ) ) {
					$this->loader->add_action( 'rest_api_init', new $class(), 'register_routes', 10, 0 );
				}
			}
		}
	}

	/**
	 * Register WP-Cron job hooks.
	 *
	 * Cron classes loaded only if files exist — Phase 1 stubs them out,
	 * Phase 3+ provides real implementations.
	 *
	 * @return void
	 */
	private function define_cron_hooks() {
		$path = defined( 'AYURCRM_PATH' ) ? AYURCRM_PATH : trailingslashit( plugin_dir_path( __FILE__ ) . '..' );

		$cron_modules = array(
			'cron/class-ayurcrm-cron-manager.php',
			'cron/class-ayurcrm-cron-followup.php',
			'cron/class-ayurcrm-cron-sla.php',
			'cron/class-ayurcrm-cron-notifications.php',
			'cron/class-ayurcrm-cron-export.php',
			'cron/class-ayurcrm-cron-import.php',
		);

		$cron_hook_map = array(
			null,
			array( 'class' => 'AyurCRM_Cron_Followup',      'hook' => 'ayurcrm_cron_followup_check' ),
			array( 'class' => 'AyurCRM_Cron_SLA',            'hook' => 'ayurcrm_cron_sla_check' ),
			array( 'class' => 'AyurCRM_Cron_Notifications',  'hook' => 'ayurcrm_cron_flush_notifications' ),
			array( 'class' => 'AyurCRM_Cron_Export',         'hook' => 'ayurcrm_cron_export_process' ),
			array( 'class' => 'AyurCRM_Cron_Import',         'hook' => 'ayurcrm_cron_import_process' ),
		);

		foreach ( $cron_modules as $index => $relative ) {
			$full = $path . $relative;
			if ( file_exists( $full ) ) {
				$this->loader->load_module( $full );
				$map = $cron_hook_map[ $index ];
				if ( $map && class_exists( $map['class'] ) ) {
					$instance = new $map['class']();
					$this->loader->add_action( $map['hook'], $instance, 'run', 10, 0 );
				}
			}
		}

		// Cron schedule filter — register custom intervals.
		$this->loader->add_filter( 'cron_schedules', $this, 'register_cron_schedules', 10, 1 );
	}

	// -----------------------------------------------------------------------
	// Callback implementations
	// -----------------------------------------------------------------------

	/**
	 * Load the plugin text domain for translations.
	 *
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'ayurcrm',
			false,
			dirname( defined( 'AYURCRM_BASENAME' ) ? AYURCRM_BASENAME : plugin_basename( __FILE__ ) ) . '/languages'
		);
	}

	/**
	 * Register custom WP-Cron intervals.
	 *
	 * @param array $schedules Existing schedules.
	 * @return array Modified schedules.
	 */
	public function register_cron_schedules( $schedules ) {
		if ( ! isset( $schedules['every_5_minutes'] ) ) {
			$schedules['every_5_minutes'] = array(
				'interval' => 5 * MINUTE_IN_SECONDS,
				'display'  => esc_html__( 'Every 5 Minutes', 'ayurcrm' ),
			);
		}

		if ( ! isset( $schedules['every_10_minutes'] ) ) {
			$schedules['every_10_minutes'] = array(
				'interval' => 10 * MINUTE_IN_SECONDS,
				'display'  => esc_html__( 'Every 10 Minutes', 'ayurcrm' ),
			);
		}

		if ( ! isset( $schedules['every_15_minutes'] ) ) {
			$schedules['every_15_minutes'] = array(
				'interval' => 15 * MINUTE_IN_SECONDS,
				'display'  => esc_html__( 'Every 15 Minutes', 'ayurcrm' ),
			);
		}

		return $schedules;
	}

	/**
	 * Return the loader instance (for testing / introspection).
	 *
	 * @return AyurCRM_Loader
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Return the plugin version.
	 *
	 * @return string
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Prevent cloning of the singleton.
	 *
	 * @return void
	 */
	private function __clone() {}

	/**
	 * Prevent unserialization of the singleton.
	 *
	 * @return void
	 * @throws \RuntimeException Always.
	 */
	public function __wakeup() {
		throw new \RuntimeException( 'Cannot unserialize singleton AyurCRM_Plugin.' );
	}
}
