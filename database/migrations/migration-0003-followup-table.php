<?php
/**
 * Migration 0003 — Create lead follow-ups table.
 *
 * @package AyurCRM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AyurCRM_Migration_0003_Followup_Table
 */
class AyurCRM_Migration_0003_Followup_Table extends AyurCRM_Migration_Base {

	/** {@inheritdoc} */
	public function version(): string {
		return '1.0.2';
	}

	/** {@inheritdoc} */
	public function description(): string {
		return 'Create lead follow-ups table';
	}

	/** {@inheritdoc} */
	public function up(): void {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table   = $wpdb->prefix . 'ayurcrm_lead_followups';
		$collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table} (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  lead_id BIGINT UNSIGNED NOT NULL,
  scheduled_by BIGINT UNSIGNED NOT NULL DEFAULT 0,
  assigned_to BIGINT UNSIGNED NOT NULL DEFAULT 0,
  followup_type VARCHAR(100) NOT NULL DEFAULT '',
  followup_date DATE NOT NULL,
  followup_time TIME NULL DEFAULT NULL,
  notes TEXT NULL DEFAULT NULL,
  status ENUM('pending','completed','cancelled','overdue','rescheduled') NOT NULL DEFAULT 'pending',
  outcome VARCHAR(200) NOT NULL DEFAULT '',
  outcome_notes TEXT NULL DEFAULT NULL,
  completed_at DATETIME NULL DEFAULT NULL,
  completed_by BIGINT UNSIGNED NOT NULL DEFAULT 0,
  reminder_sent TINYINT(1) NOT NULL DEFAULT 0,
  reminder_sent_at DATETIME NULL DEFAULT NULL,
  parent_followup_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  is_auto_generated TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  updated_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY  (id),
  KEY idx_lead_id (lead_id),
  KEY idx_assigned_to (assigned_to),
  KEY idx_followup_date (followup_date),
  KEY idx_status (status)
) ENGINE=InnoDB {$collate}";

		dbDelta( $sql );
	}
}
