<?php
/**
 * Default pipeline stages configuration.
 *
 * @package AyurCRM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AyurCRM_Default_Pipeline
 *
 * Provides the canonical list of pipeline stage definitions.
 */
class AyurCRM_Default_Pipeline {

	/**
	 * Return all default pipeline stages.
	 *
	 * @return array[]
	 */
	public static function get_stages(): array {
		return array(
			array(
				'key'         => 'new',
				'label'       => 'New',
				'color'       => '#6366F1',
				'sort_order'  => 1,
				'is_default'  => true,
				'is_terminal' => false,
			),
			array(
				'key'         => 'contacted',
				'label'       => 'Contacted',
				'color'       => '#3B82F6',
				'sort_order'  => 2,
				'is_default'  => false,
				'is_terminal' => false,
			),
			array(
				'key'         => 'interested',
				'label'       => 'Interested',
				'color'       => '#0EA5E9',
				'sort_order'  => 3,
				'is_default'  => false,
				'is_terminal' => false,
			),
			array(
				'key'         => 'qualified',
				'label'       => 'Qualified',
				'color'       => '#8B5CF6',
				'sort_order'  => 4,
				'is_default'  => false,
				'is_terminal' => false,
			),
			array(
				'key'         => 'consultation_booked',
				'label'       => 'Consultation Booked',
				'color'       => '#10B981',
				'sort_order'  => 5,
				'is_default'  => false,
				'is_terminal' => false,
			),
			array(
				'key'         => 'followup_pending',
				'label'       => 'Follow-up Pending',
				'color'       => '#F59E0B',
				'sort_order'  => 6,
				'is_default'  => false,
				'is_terminal' => false,
			),
			array(
				'key'         => 'not_responding',
				'label'       => 'Not Responding',
				'color'       => '#EF4444',
				'sort_order'  => 7,
				'is_default'  => false,
				'is_terminal' => false,
			),
			array(
				'key'         => 'converted',
				'label'       => 'Converted',
				'color'       => '#22C55E',
				'sort_order'  => 8,
				'is_default'  => false,
				'is_terminal' => true,
			),
			array(
				'key'         => 'lost',
				'label'       => 'Lost',
				'color'       => '#DC2626',
				'sort_order'  => 9,
				'is_default'  => false,
				'is_terminal' => true,
			),
		);
	}
}
