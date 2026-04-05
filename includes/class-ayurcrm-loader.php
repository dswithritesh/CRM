<?php
/**
 * Fault-tolerant module loader.
 *
 * @package AyurCRM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AyurCRM_Loader
 *
 * Queues action and filter registrations, and provides safe file loading
 * that catches Throwable errors without crashing the entire plugin.
 */
class AyurCRM_Loader {

	/**
	 * Queued action registrations.
	 *
	 * @var array
	 */
	private array $actions = [];

	/**
	 * Queued filter registrations.
	 *
	 * @var array
	 */
	private array $filters = [];

	/**
	 * Errors encountered during module loading.
	 *
	 * @var array
	 */
	private array $load_errors = [];

	// -----------------------------------------------------------------------
	// Public API
	// -----------------------------------------------------------------------

	/**
	 * Queue a WordPress action hook registration.
	 *
	 * @param string $hook          The action hook name.
	 * @param object $component     The object that contains the callback.
	 * @param string $callback      Method name on $component to call.
	 * @param int    $priority      Hook priority.
	 * @param int    $accepted_args Number of accepted arguments.
	 * @return void
	 */
	public function add_action(
		string $hook,
		$component,
		string $callback,
		int $priority = 10,
		int $accepted_args = 1
	): void {
		$this->add_to_collection(
			$this->actions,
			$hook,
			$component,
			$callback,
			$priority,
			$accepted_args
		);
	}

	/**
	 * Queue a WordPress filter hook registration.
	 *
	 * @param string $hook          The filter hook name.
	 * @param object $component     The object that contains the callback.
	 * @param string $callback      Method name on $component to call.
	 * @param int    $priority      Hook priority.
	 * @param int    $accepted_args Number of accepted arguments.
	 * @return void
	 */
	public function add_filter(
		string $hook,
		$component,
		string $callback,
		int $priority = 10,
		int $accepted_args = 1
	): void {
		$this->add_to_collection(
			$this->filters,
			$hook,
			$component,
			$callback,
			$priority,
			$accepted_args
		);
	}

	/**
	 * Register all queued hooks with WordPress.
	 *
	 * @return void
	 */
	public function run(): void {
		foreach ( $this->actions as $hook ) {
			add_action(
				$hook['hook'],
				array( $hook['component'], $hook['callback'] ),
				$hook['priority'],
				$hook['accepted_args']
			);
		}

		foreach ( $this->filters as $hook ) {
			add_filter(
				$hook['hook'],
				array( $hook['component'], $hook['callback'] ),
				$hook['priority'],
				$hook['accepted_args']
			);
		}
	}

	/**
	 * Safely require a PHP file, catching any Throwable.
	 *
	 * @param string $file_path Absolute path to the file to load.
	 * @return bool True on success, false on failure.
	 */
	public function load_module( string $file_path ): bool {
		if ( ! file_exists( $file_path ) ) {
			$this->load_errors[] = array(
				'file'    => $file_path,
				'message' => sprintf( 'Module file does not exist: %s. The related functionality will be disabled.', $file_path ),
			);
			return false;
		}

		try {
			require_once $file_path;
			return true;
		} catch ( \Throwable $e ) {
			$this->load_errors[] = array(
				'file'    => $file_path,
				'message' => $e->getMessage(),
				'trace'   => $e->getTraceAsString(),
			);

			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( // phpcs:ignore WordPress.PHP.DevelopmentFunctions
					sprintf(
						'AyurCRM: Failed to load module %s — %s',
						$file_path,
						$e->getMessage()
					)
				);
			}

			return false;
		}
	}

	/**
	 * Return all errors encountered during module loading.
	 *
	 * @return array
	 */
	public function get_load_errors(): array {
		return $this->load_errors;
	}

	// -----------------------------------------------------------------------
	// Private helpers
	// -----------------------------------------------------------------------

	/**
	 * Add a hook definition to the given collection array.
	 *
	 * @param array  $collection    Reference to the target collection.
	 * @param string $hook          Hook name.
	 * @param object $component     Component object.
	 * @param string $callback      Callback method name.
	 * @param int    $priority      Hook priority.
	 * @param int    $accepted_args Number of accepted arguments.
	 * @return void
	 */
	private function add_to_collection(
		array &$collection,
		string $hook,
		$component,
		string $callback,
		int $priority,
		int $accepted_args
	): void {
		$collection[] = array(
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args,
		);
	}
}
