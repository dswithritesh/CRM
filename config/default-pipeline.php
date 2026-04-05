<?php
/**
 * AyurCRM Default Pipeline Stages
 *
 * Returns the default pipeline stage configuration used to seed the
 * status_registry table during initial setup.
 *
 * Each stage is an associative array with the following keys:
 *   - key          (string)  Unique slug used as the DB identifier.
 *   - label        (string)  Human-readable display name.
 *   - type         (string)  Grouping type: 'open', 'terminal', or 'system'.
 *   - color_hex    (string)  Hex colour code for UI badges.
 *   - icon         (string)  Dashicon slug (without the 'dashicons-' prefix).
 *   - sort_order   (int)     Position in the pipeline kanban / list view.
 *   - is_default   (int)     1 if this is the initial status for new leads.
 *   - is_terminal  (int)     1 if the lead cannot move further in the pipeline.
 *   - parent_key   (string|null) Parent stage key for sub-statuses.
 *
 * @package AyurCRM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(

	// -------------------------------------------------------------------------
	// Primary pipeline stages
	// -------------------------------------------------------------------------

	array(
		'key'        => 'new',
		'label'      => __( 'New', 'ayurcrm' ),
		'type'       => 'open',
		'color_hex'  => '#6B7280',
		'icon'       => 'star-filled',
		'sort_order' => 1,
		'is_default' => 1,
		'is_terminal' => 0,
		'parent_key' => null,
	),

	array(
		'key'        => 'contacted',
		'label'      => __( 'Contacted', 'ayurcrm' ),
		'type'       => 'open',
		'color_hex'  => '#3B82F6',
		'icon'       => 'phone',
		'sort_order' => 2,
		'is_default' => 0,
		'is_terminal' => 0,
		'parent_key' => null,
	),

	array(
		'key'        => 'interested',
		'label'      => __( 'Interested', 'ayurcrm' ),
		'type'       => 'open',
		'color_hex'  => '#F59E0B',
		'icon'       => 'heart',
		'sort_order' => 3,
		'is_default' => 0,
		'is_terminal' => 0,
		'parent_key' => null,
	),

	array(
		'key'        => 'qualified',
		'label'      => __( 'Qualified', 'ayurcrm' ),
		'type'       => 'open',
		'color_hex'  => '#8B5CF6',
		'icon'       => 'awards',
		'sort_order' => 4,
		'is_default' => 0,
		'is_terminal' => 0,
		'parent_key' => null,
	),

	array(
		'key'        => 'consultation_booked',
		'label'      => __( 'Consultation Booked', 'ayurcrm' ),
		'type'       => 'open',
		'color_hex'  => '#10B981',
		'icon'       => 'calendar',
		'sort_order' => 5,
		'is_default' => 0,
		'is_terminal' => 0,
		'parent_key' => null,
	),

	array(
		'key'        => 'followup_pending',
		'label'      => __( 'Follow-up Pending', 'ayurcrm' ),
		'type'       => 'open',
		'color_hex'  => '#F97316',
		'icon'       => 'clock',
		'sort_order' => 6,
		'is_default' => 0,
		'is_terminal' => 0,
		'parent_key' => null,
	),

	array(
		'key'        => 'not_responding',
		'label'      => __( 'Not Responding', 'ayurcrm' ),
		'type'       => 'open',
		'color_hex'  => '#EF4444',
		'icon'       => 'dismiss',
		'sort_order' => 7,
		'is_default' => 0,
		'is_terminal' => 0,
		'parent_key' => null,
	),

	array(
		'key'        => 'converted',
		'label'      => __( 'Converted', 'ayurcrm' ),
		'type'       => 'terminal',
		'color_hex'  => '#22C55E',
		'icon'       => 'yes-alt',
		'sort_order' => 8,
		'is_default' => 0,
		'is_terminal' => 1,
		'parent_key' => null,
	),

	array(
		'key'        => 'lost',
		'label'      => __( 'Lost', 'ayurcrm' ),
		'type'       => 'terminal',
		'color_hex'  => '#DC2626',
		'icon'       => 'no-alt',
		'sort_order' => 9,
		'is_default' => 0,
		'is_terminal' => 1,
		'parent_key' => null,
	),

	// -------------------------------------------------------------------------
	// Sub-statuses for "Not Responding"
	// -------------------------------------------------------------------------

	array(
		'key'        => 'no_answer',
		'label'      => __( 'No Answer', 'ayurcrm' ),
		'type'       => 'open',
		'color_hex'  => '#EF4444',
		'icon'       => 'phone',
		'sort_order' => 1,
		'is_default' => 0,
		'is_terminal' => 0,
		'parent_key' => 'not_responding',
	),

	array(
		'key'        => 'busy',
		'label'      => __( 'Busy', 'ayurcrm' ),
		'type'       => 'open',
		'color_hex'  => '#EF4444',
		'icon'       => 'warning',
		'sort_order' => 2,
		'is_default' => 0,
		'is_terminal' => 0,
		'parent_key' => 'not_responding',
	),

	array(
		'key'        => 'switched_off',
		'label'      => __( 'Switched Off', 'ayurcrm' ),
		'type'       => 'open',
		'color_hex'  => '#EF4444',
		'icon'       => 'controls-pause',
		'sort_order' => 3,
		'is_default' => 0,
		'is_terminal' => 0,
		'parent_key' => 'not_responding',
	),

	array(
		'key'        => 'wrong_number',
		'label'      => __( 'Wrong Number', 'ayurcrm' ),
		'type'       => 'open',
		'color_hex'  => '#EF4444',
		'icon'       => 'info',
		'sort_order' => 4,
		'is_default' => 0,
		'is_terminal' => 0,
		'parent_key' => 'not_responding',
	),
);
