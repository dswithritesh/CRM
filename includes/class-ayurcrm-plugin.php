<?php
/**
 * AyurCRM Plugin Orchestrator
 *
 * Singleton class that wires together all plugin components.
 * The only method called from the main plugin file is init(), which registers
 * hooks — no heavy work is performed at load time.
 *
 * @package AyurCRM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// The loader is a hard dependency — load it before anything else.
require_once AYURCRM_PATH . 'includes/class-ayurcrm-loader.php';
require_once AYURCRM_PATH . 'includes/class-ayurcrm-constants.php';
require_once AYURCRM_PATH . 'includes/class-ayurcrm-hooks.php';

/**
 * Class AyurCRM_Plugin
 *
 * Central orchestrator. Instantiated once via get_instance(). All WordPress
 * hook registrations originate here.
 */
class AyurCRM_Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var AyurCRM_Plugin|null
	 */
	private static $instance = null;

	/**
	 * Module loader instance.
	 *
	 * @var AyurCRM_Loader
	 */
	private $loader;

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	private $version;

	/**
	 * Private constructor — use get_instance().
	 */
	private function __construct() {
		$this->version = AYURCRM_VERSION;
		$this->loader  = new AyurCRM_Loader();
	}

	/**
	 * Returns the singleton plugin instance.
	 *
	 * @return AyurCRM_Plugin
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Initialise the plugin — registers all WordPress hooks.
	 *
	 * This is the ONLY method called from ayurcrm.php. It does zero heavy
	 * work; it only queues hook callbacks.
	 *
	 * @return void
	 */
	public function init() {
		// i18n.
		add_action( 'init', array( $this, 'load_textdomain' ) );

		// DB migrations (admin only — lightweight guard).
		add_action( 'admin_init', array( $this, 'run_migrations' ) );

		// Admin UI.
		add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

		// Public assets.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_assets' ) );

		// REST API.
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );

		// Capture layer (web-to-lead forms, webhook receiver, etc.).
		add_action( 'wp_loaded', array( $this, 'init_capture_layer' ) );

		// Cron intervals (registered directly on the filter, no intermediate hook).
		add_filter( 'cron_schedules', array( $this, 'add_cron_intervals' ) );

		$this->loader->run();
	}

	/**
	 * Load the plugin text domain for translations.
	 *
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'ayurcrm',
			false,
			dirname( AYURCRM_BASENAME ) . '/languages'
		);
	}

	/**
	 * Run database migrations if needed.
	 *
	 * The migrator class is part of Phase 1 DB files (not yet built).
	 * Guarded with class_exists() so the plugin never fatals with a missing class.
	 *
	 * @return void
	 */
	public function run_migrations() {
		if ( ! class_exists( 'AyurCRM_Migrator' ) ) {
			$migrator_file = AYURCRM_PATH . 'database/class-ayurcrm-migrator.php';
			if ( file_exists( $migrator_file ) ) {
				require_once $migrator_file;
			}
		}

		if ( class_exists( 'AyurCRM_Migrator' ) ) {
			( new AyurCRM_Migrator() )->maybe_run_migrations();
		}
	}

	/**
	 * Register the plugin admin menu pages.
	 *
	 * Delegates to AyurCRM_Admin (built in a later phase).
	 * Guarded with class_exists() to prevent fatals.
	 *
	 * @return void
	 */
	public function register_admin_menu() {
		if ( ! class_exists( 'AyurCRM_Admin' ) ) {
			$admin_file = AYURCRM_PATH . 'admin/class-ayurcrm-admin.php';
			if ( file_exists( $admin_file ) ) {
				require_once $admin_file;
			}
		}

		if ( class_exists( 'AyurCRM_Admin' ) ) {
			( new AyurCRM_Admin() )->register_menus();
		}
	}

	/**
	 * Enqueue admin CSS and JavaScript assets.
	 *
	 * Only loads on AyurCRM admin pages (hook suffix contains 'ayurcrm').
	 *
	 * @param string $hook Current admin page hook suffix.
	 * @return void
	 */
	public function enqueue_admin_assets( $hook ) {
		// Only load on plugin pages.
		if ( false === strpos( $hook, 'ayurcrm' ) ) {
			return;
		}

		wp_enqueue_style(
			'ayurcrm-admin',
			AYURCRM_URL . 'assets/css/admin.css',
			array(),
			$this->version
		);

		wp_enqueue_script(
			'ayurcrm-admin',
			AYURCRM_URL . 'assets/js/admin.js',
			array( 'jquery' ),
			$this->version,
			true
		);

		wp_localize_script(
			'ayurcrm-admin',
			'ayurcrmAdmin',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'ayurcrm_nonce_admin' ),
				'version' => $this->version,
			)
		);
	}

	/**
	 * Enqueue public-facing assets (capture form styles & scripts).
	 *
	 * @return void
	 */
	public function enqueue_public_assets() {
		wp_enqueue_style(
			'ayurcrm-public',
			AYURCRM_URL . 'assets/css/public.css',
			array(),
			$this->version
		);

		wp_enqueue_script(
			'ayurcrm-public',
			AYURCRM_URL . 'assets/js/public.js',
			array( 'jquery' ),
			$this->version,
			true
		);

		wp_localize_script(
			'ayurcrm-public',
			'ayurcrmPublic',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'ayurcrm_nonce_public' ),
			)
		);
	}

	/**
	 * Register REST API route controllers.
	 *
	 * REST controller classes are loaded lazily from the api/ directory.
	 * Guarded so missing classes do not cause fatals.
	 *
	 * @return void
	 */
	public function register_rest_routes() {
		$controllers = array(
			'AyurCRM_REST_Leads_Controller'    => AYURCRM_PATH . 'api/class-ayurcrm-rest-leads-controller.php',
			'AyurCRM_REST_Capture_Controller'  => AYURCRM_PATH . 'api/class-ayurcrm-rest-capture-controller.php',
			'AyurCRM_REST_Settings_Controller' => AYURCRM_PATH . 'api/class-ayurcrm-rest-settings-controller.php',
		);

		foreach ( $controllers as $class => $file ) {
			if ( ! class_exists( $class ) && file_exists( $file ) ) {
				require_once $file;
			}

			if ( class_exists( $class ) ) {
				( new $class() )->register_routes();
			}
		}
	}

	/**
	 * Initialise the capture layer (web-to-lead forms, webhook receiver).
	 *
	 * @return void
	 */
	public function init_capture_layer() {
		$capture_file = AYURCRM_PATH . 'includes/class-ayurcrm-capture.php';

		if ( ! class_exists( 'AyurCRM_Capture' ) && file_exists( $capture_file ) ) {
			require_once $capture_file;
		}

		if ( class_exists( 'AyurCRM_Capture' ) ) {
			( new AyurCRM_Capture() )->init();
		}
	}

	/**
	 * Add custom cron intervals to WordPress.
	 *
	 * @param array $schedules Existing cron schedules.
	 * @return array Modified schedules including plugin-specific intervals.
	 */
	public function add_cron_intervals( $schedules ) {
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
	 */
	public function __wakeup() {
		// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
		throw new \Exception( 'Cannot unserialize a singleton.' );
	}
}
