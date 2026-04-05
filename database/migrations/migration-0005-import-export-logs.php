<?php
/**
 * Migration 0005 — Create import, export, log, meta, and queue tables.
 *
 * @package AyurCRM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AyurCRM_Migration_0005_Import_Export_Logs
 */
class AyurCRM_Migration_0005_Import_Export_Logs extends AyurCRM_Migration_Base {

	/** {@inheritdoc} */
	public function version(): string {
		return '1.0.4';
	}

	/** {@inheritdoc} */
	public function description(): string {
		return 'Create import, export, lead meta, status registry, notification queue, logs, and integration logs tables';
	}

	/** {@inheritdoc} */
	public function up(): void {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$collate = $wpdb->get_charset_collate();

		// ------------------------------------------------------------------
		// ayurcrm_imports
		// ------------------------------------------------------------------
		$imports_table = $wpdb->prefix . 'ayurcrm_imports';
		$sql           = "CREATE TABLE {$imports_table} (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  batch_id VARCHAR(64) NOT NULL DEFAULT '',
  file_name VARCHAR(500) NOT NULL DEFAULT '',
  file_path VARCHAR(1000) NOT NULL DEFAULT '',
  file_size BIGINT UNSIGNED NOT NULL DEFAULT 0,
  total_rows BIGINT UNSIGNED NOT NULL DEFAULT 0,
  processed_rows BIGINT UNSIGNED NOT NULL DEFAULT 0,
  success_rows BIGINT UNSIGNED NOT NULL DEFAULT 0,
  failed_rows BIGINT UNSIGNED NOT NULL DEFAULT 0,
  duplicate_rows BIGINT UNSIGNED NOT NULL DEFAULT 0,
  status ENUM('pending','processing','completed','failed','cancelled') NOT NULL DEFAULT 'pending',
  error_message TEXT NULL DEFAULT NULL,
  mapping_config LONGTEXT NULL DEFAULT NULL,
  import_options LONGTEXT NULL DEFAULT NULL,
  started_at DATETIME NULL DEFAULT NULL,
  completed_at DATETIME NULL DEFAULT NULL,
  created_by BIGINT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  updated_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY  (id),
  UNIQUE KEY idx_batch_id (batch_id),
  KEY idx_status (status),
  KEY idx_created_by (created_by),
  KEY idx_created_at (created_at)
) ENGINE=InnoDB {$collate}";
		dbDelta( $sql );

		// ------------------------------------------------------------------
		// ayurcrm_import_rows
		// ------------------------------------------------------------------
		$import_rows_table = $wpdb->prefix . 'ayurcrm_import_rows';
		$sql               = "CREATE TABLE {$import_rows_table} (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  import_id BIGINT UNSIGNED NOT NULL,
  row_number BIGINT UNSIGNED NOT NULL DEFAULT 0,
  raw_data LONGTEXT NULL DEFAULT NULL,
  mapped_data LONGTEXT NULL DEFAULT NULL,
  status ENUM('pending','success','failed','duplicate','skipped') NOT NULL DEFAULT 'pending',
  lead_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  error_message TEXT NULL DEFAULT NULL,
  processed_at DATETIME NULL DEFAULT NULL,
  PRIMARY KEY  (id),
  KEY idx_import_id (import_id),
  KEY idx_status (status),
  KEY idx_lead_id (lead_id)
) ENGINE=InnoDB {$collate}";
		dbDelta( $sql );

		// ------------------------------------------------------------------
		// ayurcrm_exports
		// ------------------------------------------------------------------
		$exports_table = $wpdb->prefix . 'ayurcrm_exports';
		$sql           = "CREATE TABLE {$exports_table} (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  export_key VARCHAR(64) NOT NULL DEFAULT '',
  file_name VARCHAR(500) NOT NULL DEFAULT '',
  file_path VARCHAR(1000) NOT NULL DEFAULT '',
  file_url VARCHAR(2083) NOT NULL DEFAULT '',
  file_size BIGINT UNSIGNED NOT NULL DEFAULT 0,
  total_rows BIGINT UNSIGNED NOT NULL DEFAULT 0,
  filters LONGTEXT NULL DEFAULT NULL,
  columns LONGTEXT NULL DEFAULT NULL,
  format VARCHAR(10) NOT NULL DEFAULT 'csv',
  status ENUM('pending','processing','completed','failed','expired') NOT NULL DEFAULT 'pending',
  error_message TEXT NULL DEFAULT NULL,
  started_at DATETIME NULL DEFAULT NULL,
  completed_at DATETIME NULL DEFAULT NULL,
  expires_at DATETIME NULL DEFAULT NULL,
  created_by BIGINT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY  (id),
  UNIQUE KEY idx_export_key (export_key),
  KEY idx_status (status),
  KEY idx_created_by (created_by),
  KEY idx_expires_at (expires_at)
) ENGINE=InnoDB {$collate}";
		dbDelta( $sql );

		// ------------------------------------------------------------------
		// ayurcrm_lead_meta
		// ------------------------------------------------------------------
		$lead_meta_table = $wpdb->prefix . 'ayurcrm_lead_meta';
		$sql             = "CREATE TABLE {$lead_meta_table} (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  lead_id BIGINT UNSIGNED NOT NULL,
  meta_key VARCHAR(255) NOT NULL DEFAULT '',
  meta_value LONGTEXT NULL DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY  (id),
  KEY idx_lead_id (lead_id),
  KEY idx_meta_key (meta_key)
) ENGINE=InnoDB {$collate}";
		dbDelta( $sql );

		// ------------------------------------------------------------------
		// ayurcrm_status_registry
		// ------------------------------------------------------------------
		$status_table = $wpdb->prefix . 'ayurcrm_status_registry';
		$sql          = "CREATE TABLE {$status_table} (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  status_key VARCHAR(100) NOT NULL DEFAULT '',
  label VARCHAR(200) NOT NULL DEFAULT '',
  color_hex VARCHAR(7) NOT NULL DEFAULT '#000000',
  sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  is_default TINYINT(1) NOT NULL DEFAULT 0,
  is_terminal TINYINT(1) NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  updated_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY  (id),
  UNIQUE KEY idx_status_key (status_key),
  KEY idx_sort_order (sort_order)
) ENGINE=InnoDB {$collate}";
		dbDelta( $sql );

		// Seed default pipeline stages.
		$this->seed_status_registry( $status_table );

		// ------------------------------------------------------------------
		// ayurcrm_notification_queue
		// ------------------------------------------------------------------
		$notif_table = $wpdb->prefix . 'ayurcrm_notification_queue';
		$sql         = "CREATE TABLE {$notif_table} (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  notification_type VARCHAR(100) NOT NULL DEFAULT '',
  recipient_user_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  recipient_email VARCHAR(200) NOT NULL DEFAULT '',
  subject VARCHAR(500) NOT NULL DEFAULT '',
  body LONGTEXT NULL DEFAULT NULL,
  template VARCHAR(100) NOT NULL DEFAULT '',
  template_data LONGTEXT NULL DEFAULT NULL,
  priority TINYINT UNSIGNED NOT NULL DEFAULT 5,
  status ENUM('pending','sent','failed','cancelled') NOT NULL DEFAULT 'pending',
  attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
  last_attempted_at DATETIME NULL DEFAULT NULL,
  sent_at DATETIME NULL DEFAULT NULL,
  error_message TEXT NULL DEFAULT NULL,
  lead_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  reference_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  reference_type VARCHAR(50) NOT NULL DEFAULT '',
  created_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY  (id),
  KEY idx_status (status),
  KEY idx_lead_id (lead_id),
  KEY idx_recipient_user_id (recipient_user_id),
  KEY idx_created_at (created_at)
) ENGINE=InnoDB {$collate}";
		dbDelta( $sql );

		// ------------------------------------------------------------------
		// ayurcrm_logs
		// ------------------------------------------------------------------
		$logs_table = $wpdb->prefix . 'ayurcrm_logs';
		$sql        = "CREATE TABLE {$logs_table} (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  level VARCHAR(20) NOT NULL DEFAULT 'info',
  context VARCHAR(100) NOT NULL DEFAULT '',
  message TEXT NOT NULL,
  data LONGTEXT NULL DEFAULT NULL,
  user_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  lead_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  ip_address VARCHAR(45) NOT NULL DEFAULT '',
  created_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY  (id),
  KEY idx_level (level),
  KEY idx_context (context),
  KEY idx_lead_id (lead_id),
  KEY idx_created_at (created_at)
) ENGINE=InnoDB {$collate}";
		dbDelta( $sql );

		// ------------------------------------------------------------------
		// ayurcrm_integration_logs
		// ------------------------------------------------------------------
		$integ_logs_table = $wpdb->prefix . 'ayurcrm_integration_logs';
		$sql              = "CREATE TABLE {$integ_logs_table} (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  integration VARCHAR(100) NOT NULL DEFAULT '',
  direction ENUM('inbound','outbound') NOT NULL DEFAULT 'outbound',
  event_type VARCHAR(100) NOT NULL DEFAULT '',
  request_data LONGTEXT NULL DEFAULT NULL,
  response_data LONGTEXT NULL DEFAULT NULL,
  status ENUM('success','failed','pending','timeout') NOT NULL DEFAULT 'pending',
  http_status SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  duration_ms SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  error_message TEXT NULL DEFAULT NULL,
  lead_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY  (id),
  KEY idx_integration (integration),
  KEY idx_status (status),
  KEY idx_lead_id (lead_id),
  KEY idx_created_at (created_at)
) ENGINE=InnoDB {$collate}";
		dbDelta( $sql );
	}

	/**
	 * Seed the status_registry table with default pipeline stages.
	 *
	 * @param string $table Full table name.
	 * @return void
	 */
	private function seed_status_registry( string $table ): void {
		global $wpdb;

		// Skip seeding if data already exists.
		// The table name is safe to interpolate — it comes from $wpdb->prefix (a
		// trusted WordPress value) concatenated with a static string literal.
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery
		$count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
		if ( $count > 0 ) {
			return;
		}

		$now    = current_time( 'mysql', true );
		$stages = array(
			array( 'new',                  'New',                   '#6366F1', 1,  1, 0 ),
			array( 'contacted',            'Contacted',             '#3B82F6', 2,  0, 0 ),
			array( 'interested',           'Interested',            '#0EA5E9', 3,  0, 0 ),
			array( 'qualified',            'Qualified',             '#8B5CF6', 4,  0, 0 ),
			array( 'consultation_booked',  'Consultation Booked',   '#10B981', 5,  0, 0 ),
			array( 'followup_pending',     'Follow-up Pending',     '#F59E0B', 6,  0, 0 ),
			array( 'not_responding',       'Not Responding',        '#EF4444', 7,  0, 0 ),
			array( 'converted',            'Converted',             '#22C55E', 8,  0, 1 ),
			array( 'lost',                 'Lost',                  '#DC2626', 9,  0, 1 ),
		);

		foreach ( $stages as $stage ) {
			$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$table,
				array(
					'status_key'  => $stage[0],
					'label'       => $stage[1],
					'color_hex'   => $stage[2],
					'sort_order'  => $stage[3],
					'is_default'  => $stage[4],
					'is_terminal' => $stage[5],
					'is_active'   => 1,
					'created_at'  => $now,
					'updated_at'  => $now,
				),
				array( '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%s', '%s' )
			);
		}
	}
}
