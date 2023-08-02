<?php
namespace AweBooking\ICalendar;

use AweBooking\Constants;
use AweBooking\Model\Model;

class ICal_Entity extends Model {
	/**
	 * Name of object type.
	 *
	 * @var string
	 */
	protected $object_type = 'awebooking_ical_entity';

	/**
	 * WordPress type for object.
	 *
	 * @var string
	 */
	protected $wp_type = 'awebooking_ical_entity';

	/**
	 * This object does not support metadata.
	 *
	 * @var false
	 */
	protected $meta_type = false;

	/**
	 * The attributes for this object.
	 *
	 * @var array
	 */
	protected $attributes = [
		'eid'         => '',
		'unit_id'     => 0,
		'entity_id'   => 0,
		'entity_type' => '',
		'ical_type'   => '',
		'start_date'  => 0,
		'end_date'    => 0,
	];

	/**
	 * WP Object constructor.
	 *
	 * @param array|object $instance Object instance.
	 */
	public function __construct( $instance = null ) {
		if ( $instance && isset( $instance->eid ) ) {
			$this->id     = $instance->eid;
			$this->exists = true;

			$this->set_instance( $instance );
			$this->setup();
		}

		$this->sync_original();
	}

	/**
	 * {@inheritdoc}
	 */
	protected function finish_save() {
		$timespan = abrs_timespan( $this['start_date'], $this['end_date'] );

		if ( ! is_wp_error( $timespan ) ) {
			abrs_apply_room_state( $this['unit_id'], $timespan, Constants::STATE_SYNC );
		}
	}

	/**
	 * {@inheritdoc}
	 */
	protected function setup() {
		foreach ( get_object_vars( $this->instance ) as $key => $value ) {
			$this[ $key ] = $value;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	protected function perform_insert() {
		global $wpdb;

		// We need a eid present.
		if ( ! $this['eid'] ) {
			return 0;
		}

		$inserted = $wpdb->insert(
			"{$wpdb->prefix}awebooking_ical_entities",
			[
				'eid'         => $this['eid'],
				'unit_id'     => $this['unit_id'],
				'ical_type'   => $this['ical_type'],
				'entity_id'   => $this['entity_id'],
				'entity_type' => $this['entity_type'],
				'start_date'  => $this['start_date'],
				'end_date'    => $this['end_date'],
			],
			[
				'%s',
				'%d',
				'%s',
				'%d',
				'%s',
				'%s',
				'%s',
			]
		);

		return $inserted ? 1 : 0;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function perform_delete( $force ) {
		global $wpdb;

		$deleted = $wpdb->delete(
			$wpdb->prefix . 'awebooking_ical_entities',
			[ 'eid' => $this['eid'] ],
			'%s'
		);

		$timespan = abrs_timespan( $this['start_date'], $this['end_date'] );
		if ( ! is_wp_error( $timespan ) ) {
			abrs_unblock_room( $this['unit_id'], $timespan );
		}

		return true;
	}
}
