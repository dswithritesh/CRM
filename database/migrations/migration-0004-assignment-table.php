<?php
/**
 * Migration 0004 — Create lead assignments history table.
 *
 * @package AyurCRM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AyurCRM_Migration_0004_Assignment_Table
 */
class AyurCRM_Migration_0004_Assignment_Table extends AyurCRM_Migration_Base {

	/** {@inheritdoc} */
	public function version(): string {
		return '1.0.3';
	}

	/** {@inheritdoc} */
	public function description(): string {
		return 'Create lead assignments history table';
	}

	/** {@inheritdoc} */
	public function up(): void {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table   = $wpdb->prefix . 'ayurcrm_lead_assignments';
		$collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table} (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  lead_id BIGINT UNSIGNED NOT NULL,
  assigned_from BIGINT UNSIGNED NOT NULL DEFAULT 0,
  assigned_to BIGINT UNSIGNED NOT NULL DEFAULT 0,
  assigned_by BIGINT UNSIGNED NOT NULL DEFAULT 0,
  assignment_type ENUM('manual','auto','round_robin','escalation') NOT NULL DEFAULT 'manual',
  branch_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  notes VARCHAR(500) NOT NULL DEFAULT '',
  assigned_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  unassigned_at DATETIME NULL DEFAULT NULL,
  is_current TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY  (id),
  KEY idx_lead_id (lead_id),
  KEY idx_assigned_to (assigned_to),
  KEY idx_assigned_at (assigned_at),
  KEY idx_is_current (is_current)
) ENGINE=InnoDB {$collate}";

		dbDelta( $sql );
	}
}
