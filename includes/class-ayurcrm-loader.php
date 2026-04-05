<?php
/**
 * AyurCRM Module Loader
 *
 * Fault-tolerant hook registration and module loading.
 * All WordPress action/filter registrations flow through this class so that
 * the full hook map is in one place and every load is guarded against missing
 * files or fatal errors.
 *
 * @package AyurCRM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AyurCRM_Loader
 *
 * Manages a queue of WordPress actions and filters and provides safe
 * file-require helpers for loading plugin modules.
 */
class AyurCRM_Loader {

	/**
	 * Queued WordPress action hooks.
	 *
	 * Each entry is an associative array with keys:
	 *   hook, component, callback, priority, accepted_args
	 *
	 * @var array[]
	 */
	private $actions = array();

	/**
	 * Queued WordPress filter hooks.
	 *
	 * Each entry is an associative array with keys:
	 *   hook, component, callback, priority, accepted_args
	 *
	 * @var array[]
	 */
	private $filters = array();

	/**
	 * Queue an action hook for later registration.
	 *
	 * @param string   $hook          WordPress action hook name.
	 * @param object   $component     Object instance that owns the callback.
	 * @param string   $callback      Name of the method to call.
	 * @param int      $priority      Hook priority (default 10).
	 * @param int      $accepted_args Number of arguments the callback accepts (default 1).
	 * @return void
	 */
	public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Queue a filter hook for later registration.
	 *
	 * @param string   $hook          WordPress filter hook name.
	 * @param object   $component     Object instance that owns the callback.
	 * @param string   $callback      Name of the method to call.
	 * @param int      $priority      Hook priority (default 10).
	 * @param int      $accepted_args Number of arguments the callback accepts (default 1).
	 * @return void
	 */
	public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Append a hook definition to the given queue array.
	 *
	 * @param array    $hooks         Existing queue array.
	 * @param string   $hook          WordPress hook name.
	 * @param object   $component     Object instance that owns the callback.
	 * @param string   $callback      Method name.
	 * @param int      $priority      Hook priority.
	 * @param int      $accepted_args Number of accepted arguments.
	 * @return array Updated queue array.
	 */
	private function add( $hooks, $hook, $component, $callback, $priority, $accepted_args ) {
		$hooks[] = array(
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args,
		);

		return $hooks;
	}

	/**
	 * Register all queued actions and filters with WordPress.
	 *
	 * Call this once after all hooks have been queued (typically at the end of
	 * AyurCRM_Plugin::init()).
	 *
	 * @return void
	 */
	public function run() {
		foreach ( $this->filters as $hook ) {
			add_filter(
				$hook['hook'],
				array( $hook['component'], $hook['callback'] ),
				$hook['priority'],
				$hook['accepted_args']
			);
		}

		foreach ( $this->actions as $hook ) {
			add_action(
				$hook['hook'],
				array( $hook['component'], $hook['callback'] ),
				$hook['priority'],
				$hook['accepted_args']
			);
		}
	}

	/**
	 * Safely require a single module file.
	 *
	 * Logs an error if the file does not exist instead of throwing a fatal.
	 *
	 * @param string $file_path Absolute path to the PHP file.
	 * @return bool True if the file was loaded, false on failure.
	 */
	public function load_module( $file_path ) {
		if ( ! file_exists( $file_path ) ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( 'AyurCRM: module not found — ' . $file_path );
			return false;
		}

		require_once $file_path;
		return true;
	}

	/**
	 * Load multiple module files, continuing past any that fail.
	 *
	 * @param string[] $files Array of absolute file paths.
	 * @return void
	 */
	public function load_modules( array $files ) {
		foreach ( $files as $file ) {
			$this->load_module( $file );
		}
	}
}
