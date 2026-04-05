<?php
/**
 * AyurCRM Fault-Tolerant Module Loader
 *
 * Maintains two queues — actions and filters — and registers them with
 * WordPress when run() is called. Also provides a guarded require layer so
 * that a missing or broken module file is logged and skipped rather than
 * crashing the entire plugin.
 *
 * @package AyurCRM
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AyurCRM_Loader
 *
 * Hook queue manager + fault-tolerant file loader.
 */
class AyurCRM_Loader {

	/**
	 * Registered action hooks.
	 *
	 * @var array[]
	 */
	private $actions = array();

	/**
	 * Registered filter hooks.
	 *
	 * @var array[]
	 */
	private $filters = array();

	/**
	 * Files that failed to load, keyed by absolute path.
	 *
	 * @var string[]
	 */
	private $load_errors = array();

	// -----------------------------------------------------------------------
	// Hook queue methods
	// -----------------------------------------------------------------------

	/**
	 * Queue an action hook for registration.
	 *
	 * @param string   $hook          The WordPress action hook name.
	 * @param object   $component     The object that owns the callback.
	 * @param string   $callback      Method name on $component.
	 * @param int      $priority      Hook priority. Default 10.
	 * @param int      $accepted_args Number of arguments. Default 1.
	 * @return void
	 */
	public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->actions = $this->add_to_collection(
			$this->actions,
			$hook,
			$component,
			$callback,
			$priority,
			$accepted_args
		);
	}

	/**
	 * Queue a filter hook for registration.
	 *
	 * @param string   $hook          The WordPress filter hook name.
	 * @param object   $component     The object that owns the callback.
	 * @param string   $callback      Method name on $component.
	 * @param int      $priority      Hook priority. Default 10.
	 * @param int      $accepted_args Number of arguments. Default 1.
	 * @return void
	 */
	public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->filters = $this->add_to_collection(
			$this->filters,
			$hook,
			$component,
			$callback,
			$priority,
			$accepted_args
		);
	}

	/**
	 * Register all queued actions and filters with WordPress.
	 *
	 * Called once by AyurCRM_Plugin::init() after all modules are wired.
	 *
	 * @return void
	 */
	public function run() {
		foreach ( $this->filters as $entry ) {
			add_filter(
				$entry['hook'],
				array( $entry['component'], $entry['callback'] ),
				$entry['priority'],
				$entry['accepted_args']
			);
		}

		foreach ( $this->actions as $entry ) {
			add_action(
				$entry['hook'],
				array( $entry['component'], $entry['callback'] ),
				$entry['priority'],
				$entry['accepted_args']
			);
		}
	}

	// -----------------------------------------------------------------------
	// File loading methods
	// -----------------------------------------------------------------------

	/**
	 * Safely require a single module file.
	 *
	 * If the file does not exist or triggers a Throwable/Exception during
	 * inclusion, the error is logged and FALSE is returned. The rest of the
	 * plugin continues to load normally.
	 *
	 * @param string $file_path Absolute path to the file.
	 * @return bool TRUE on success, FALSE on failure.
	 */
	public function load_module( $file_path ) {
		if ( ! file_exists( $file_path ) ) {
			$this->load_errors[ $file_path ] = 'File not found';
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( 'AyurCRM Loader: module not found — ' . $file_path );
			return false;
		}

		try {
			require_once $file_path;
			return true;
		} catch ( \Throwable $e ) {
			$message = $e->getMessage();
			$this->load_errors[ $file_path ] = $message;
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( 'AyurCRM Loader: failed to load ' . $file_path . ' — ' . $message );
			return false;
		} catch ( \Exception $e ) {
			// PHP 5 compat catch kept for safety; minimum is PHP 7.4 but belt-and-suspenders.
			$message = $e->getMessage();
			$this->load_errors[ $file_path ] = $message;
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( 'AyurCRM Loader: failed to load ' . $file_path . ' — ' . $message );
			return false;
		}
	}

	/**
	 * Safely require multiple module files in order.
	 *
	 * Continues loading remaining files even if one fails.
	 *
	 * @param string[] $files Array of absolute file paths.
	 * @return array{loaded: string[], failed: string[]} Summary of results.
	 */
	public function load_modules( array $files ) {
		$result = array(
			'loaded' => array(),
			'failed' => array(),
		);

		foreach ( $files as $file ) {
			if ( $this->load_module( $file ) ) {
				$result['loaded'][] = $file;
			} else {
				$result['failed'][] = $file;
			}
		}

		return $result;
	}

	/**
	 * Return all file paths that failed to load, with their error messages.
	 *
	 * @return string[] Associative array of path => error message.
	 */
	public function get_load_errors() {
		return $this->load_errors;
	}

	/**
	 * Return TRUE if any module failed to load.
	 *
	 * @return bool
	 */
	public function has_load_errors() {
		return ! empty( $this->load_errors );
	}

	// -----------------------------------------------------------------------
	// Internal helpers
	// -----------------------------------------------------------------------

	/**
	 * Append a hook entry to a collection array.
	 *
	 * @param array  $collection    Existing collection (actions or filters).
	 * @param string $hook          Hook name.
	 * @param object $component     Owning object.
	 * @param string $callback      Method name.
	 * @param int    $priority      Priority.
	 * @param int    $accepted_args Argument count.
	 * @return array Updated collection.
	 */
	private function add_to_collection( $collection, $hook, $component, $callback, $priority, $accepted_args ) {
		$collection[] = array(
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => (int) $priority,
			'accepted_args' => (int) $accepted_args,
		);
		return $collection;
	}
}
