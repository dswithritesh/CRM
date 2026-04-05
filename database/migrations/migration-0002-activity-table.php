<?php
/**
 * Migration 0002 — Create lead activities timeline table.
 *
 * @package AyurCRM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AyurCRM_Migration_0002_Activity_Table
 */
class AyurCRM_Migration_0002_Activity_Table extends AyurCRM_Migration_Base {

	/** {@inheritdoc} */
	public function version(): string {
		return '1.0.1';
	}

	/** {@inheritdoc} */
	public function description(): string {
		return 'Create lead activities timeline table';
	}

	/** {@inheritdoc} */
	public function up(): void {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table   = $wpdb->prefix . 'ayurcrm_lead_activities';
		$collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table} (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  lead_id BIGINT UNSIGNED NOT NULL,
  activity_type VARCHAR(100) NOT NULL DEFAULT '',
  activity_subtype VARCHAR(100) NOT NULL DEFAULT '',
  summary VARCHAR(500) NOT NULL DEFAULT '',
  detail LONGTEXT NULL DEFAULT NULL,
  old_value TEXT NULL DEFAULT NULL,
  new_value TEXT NULL DEFAULT NULL,
  channel VARCHAR(50) NOT NULL DEFAULT '',
  direction ENUM('inbound','outbound','internal') NOT NULL DEFAULT 'internal',
  duration_seconds SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  attachments TEXT NULL DEFAULT NULL,
  outcome VARCHAR(100) NOT NULL DEFAULT '',
  performed_by BIGINT UNSIGNED NOT NULL DEFAULT 0,
  performed_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  ip_address VARCHAR(45) NOT NULL DEFAULT '',
  user_agent VARCHAR(500) NOT NULL DEFAULT '',
  meta_data TEXT NULL DEFAULT NULL,
  PRIMARY KEY  (id),
  KEY idx_lead_id (lead_id),
  KEY idx_activity_type (activity_type),
  KEY idx_performed_by (performed_by),
  KEY idx_performed_at (performed_at)
) ENGINE=InnoDB {$collate}";

		dbDelta( $sql );
	}
}
