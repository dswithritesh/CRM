<?php
/**
 * Migration 0001 — Create core leads table.
 *
 * @package AyurCRM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AyurCRM_Migration_0001_Base_Schema
 */
class AyurCRM_Migration_0001_Base_Schema extends AyurCRM_Migration_Base {

	/** {@inheritdoc} */
	public function version(): string {
		return '1.0.0';
	}

	/** {@inheritdoc} */
	public function description(): string {
		return 'Create core leads table';
	}

	/** {@inheritdoc} */
	public function up(): void {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table   = $wpdb->prefix . 'ayurcrm_leads';
		$collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table} (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  full_name VARCHAR(200) NOT NULL DEFAULT '',
  phone VARCHAR(30) NOT NULL DEFAULT '',
  email VARCHAR(200) NOT NULL DEFAULT '',
  city VARCHAR(100) NOT NULL DEFAULT '',
  state VARCHAR(100) NOT NULL DEFAULT '',
  country VARCHAR(100) NOT NULL DEFAULT '',
  age TINYINT UNSIGNED NULL DEFAULT NULL,
  gender ENUM('male','female','other','prefer_not_to_say') NOT NULL DEFAULT 'prefer_not_to_say',
  concern VARCHAR(200) NOT NULL DEFAULT '',
  concern_detail TEXT NULL DEFAULT NULL,
  source VARCHAR(100) NOT NULL DEFAULT '',
  source_detail VARCHAR(200) NOT NULL DEFAULT '',
  utm_source VARCHAR(200) NOT NULL DEFAULT '',
  utm_medium VARCHAR(200) NOT NULL DEFAULT '',
  utm_campaign VARCHAR(200) NOT NULL DEFAULT '',
  utm_term VARCHAR(200) NOT NULL DEFAULT '',
  utm_content VARCHAR(200) NOT NULL DEFAULT '',
  landing_page VARCHAR(2083) NOT NULL DEFAULT '',
  referrer VARCHAR(2083) NOT NULL DEFAULT '',
  device_type VARCHAR(50) NOT NULL DEFAULT '',
  gclid VARCHAR(200) NOT NULL DEFAULT '',
  fbclid VARCHAR(200) NOT NULL DEFAULT '',
  branch_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  branch_code VARCHAR(50) NOT NULL DEFAULT '',
  answers_payload LONGTEXT NULL DEFAULT NULL,
  assessment_score SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  result_category VARCHAR(100) NOT NULL DEFAULT '',
  source_form_id VARCHAR(100) NOT NULL DEFAULT '',
  source_form_name VARCHAR(200) NOT NULL DEFAULT '',
  lead_score SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  lead_temperature ENUM('cold','warm','hot') NOT NULL DEFAULT 'cold',
  lead_quality ENUM('low','medium','high','premium') NOT NULL DEFAULT 'medium',
  assigned_to BIGINT UNSIGNED NOT NULL DEFAULT 0,
  assigned_doctor BIGINT UNSIGNED NOT NULL DEFAULT 0,
  owner_user_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  assigned_at DATETIME NULL DEFAULT NULL,
  status VARCHAR(100) NOT NULL DEFAULT 'new',
  sub_status VARCHAR(100) NOT NULL DEFAULT '',
  priority ENUM('low','normal','high','urgent') NOT NULL DEFAULT 'normal',
  followup_date DATE NULL DEFAULT NULL,
  followup_time TIME NULL DEFAULT NULL,
  last_contacted_at DATETIME NULL DEFAULT NULL,
  next_action VARCHAR(200) NOT NULL DEFAULT '',
  contact_attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
  notes_summary TEXT NULL DEFAULT NULL,
  internal_comments TEXT NULL DEFAULT NULL,
  tags TEXT NULL DEFAULT NULL,
  duplicate_hash VARCHAR(64) NOT NULL DEFAULT '',
  duplicate_of BIGINT UNSIGNED NOT NULL DEFAULT 0,
  is_duplicate TINYINT(1) NOT NULL DEFAULT 0,
  import_batch_id VARCHAR(64) NOT NULL DEFAULT '',
  is_archived TINYINT(1) NOT NULL DEFAULT 0,
  is_deleted TINYINT(1) NOT NULL DEFAULT 0,
  created_by BIGINT UNSIGNED NOT NULL DEFAULT 0,
  updated_by BIGINT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  updated_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY  (id),
  KEY idx_phone (phone),
  KEY idx_email (email),
  KEY idx_status (status),
  KEY idx_assigned_to (assigned_to),
  KEY idx_branch_id (branch_id),
  KEY idx_source (source),
  KEY idx_followup_date (followup_date),
  KEY idx_created_at (created_at),
  KEY idx_duplicate_hash (duplicate_hash),
  KEY idx_is_deleted (is_deleted),
  KEY idx_is_archived (is_archived),
  KEY idx_import_batch_id (import_batch_id)
) ENGINE=InnoDB {$collate}";

		dbDelta( $sql );
	}
}
