<?php
namespace AweBooking\ICalendar;

use AweBooking\Constants;
use Psr\Log\LoggerInterface;
use AweBooking\Model\Room_Type;
use AweBooking\Component\ICalendar\Reader;

class Sync {
	/**
	 * The logger implementation.
	 *
	 * @var \Psr\Log\LoggerInterface
	 */
	protected $logger;

	/**
	 * Constructor.
	 *
	 * @param \Psr\Log\LoggerInterface $logger The logger implementation.
	 */
	public function __construct( LoggerInterface $logger = null ) {
		$this->logger = $logger ?: abrs_logger();
	}

	/**
	 * Run sync iCal from remote.
	 *
	 * @param  Room_Type $room_type The room type instance.
	 * @return void
	 */
	public function run( Room_Type $room_type ) {
		$room_units = $room_type->get_rooms();
		if ( 0 === count( $room_units ) ) {
			return;
		}

		$ical_link = abrs_ical_get_sync_link( $room_type );
		if ( ! abrs_valid_url( $ical_link ) ) {
			return;
		}

		/* translators: The calendar link */
		$this->logger->debug( sprintf( esc_html__( 'Reading remote calendar from [%s]', 'awebooking-icalendar' ), esc_url( $ical_link ) ) );

		$result = ( new Reader )->read( $ical_link )
			->only_future();

		/* translators: Number event found */
		$this->logger->debug( sprintf( esc_html__( 'Found %d valid event(s) from the calendar.', 'awebooking-icalendar' ), $result->count() ) );

		// First, delete events that have disappeared from the remote source.
		$synced_events = abrs_ical_get_synced_records( $room_type );

		// Delete the orphan synced records.
		if ( $synced_events && count( $synced_events ) > 0 ) {
			$this->delete_orphan_records( $synced_events, $result->events() );
		}

		$existing_eids = $synced_events->pluck( 'eid' )->all();

		$synced = 0;

		foreach ( $result->events() as $event ) {
			/* @var $event \AweBooking\Component\ICalendar\Event */

			// If this event's ID matches an existing event, skip to the next event.
			if ( in_array( $event->get_uid(), $existing_eids ) ) {
				$this->logger->debug( sprintf( esc_html__( 'Ignore existing event UID: %s.', 'awebooking-icalendar' ), $event->get_uid() ) );
				continue;
			}

			// Find available unit ID to sync.
			$available_rooms = $this->find_available_rooms( $room_units, $event );
			if ( ! $available_rooms || $available_rooms->isEmpty() ) {
				$this->logger->debug( sprintf( esc_html__( 'Ignore event: %s.', 'awebooking-icalendar' ), $event->get_uid() ) );
				continue;
			}

			// Take the first room.
			$sync_unit = $available_rooms->first();

			$this->logger->debug( sprintf( esc_html__( 'Saving: %s.', 'awebooking-icalendar' ), $event->get_uid() ) );

			$entity = ( new ICal_Entity )->fill( [
				'eid'         => $event->get_uid(),
				'unit_id'     => $sync_unit->get_id(),
				'ical_type'   => $result->get_property_name(),
				'entity_id'   => $room_type->get_id(),
				'entity_type' => 'state',
				'start_date'  => $event->get_start_date()->toDateTimeString(),
				'end_date'    => $event->get_end_date()->toDateTimeString(),
			] );

			try {
				$entity->save();

				$synced++;

				$this->logger->debug( sprintf( esc_html__( 'Saved: %s.', 'awebooking-icalendar' ), $event->get_uid() ) );
			} catch ( \Exception $e ) {
				$this->logger->debug( sprintf( esc_html__( 'Prevent saving: %s. Error: %s', 'awebooking-icalendar' ), $event->get_uid(), $e->getMessage() ) );
			}
		}

		$this->logger->debug( esc_html__( 'Sync completed.' . $synced, 'awebooking-icalendar' ) );
	}

	/**
	 * Perform find available rooms in a period of given event.
	 *
	 * @param \AweBooking\Support\Collection        $rooms The rooms.
	 * @param \AweBooking\Component\ICalendar\Event $event The event instance.
	 * @return \AweBooking\Support\Collection|null
	 */
	protected function find_available_rooms( $rooms, $event ) {
		$response = abrs_check_room_states( $rooms, $event->get_timespan(), Constants::STATE_AVAILABLE, [], 'any' );

		if ( is_wp_error( $response ) ) {
			return null;
		}

		$sort_callback = function ( $item ) {
			/* @var $events \AweBooking\Support\Collection */
			$events = $item['data'];

			if ( 1 === count( $events ) && $events[0]->get_value() == 0 ) {
				return -1;
			}

			return count( $events->where( 'value', 0 ) );
		};

		return $response->get_included()
			->sortBy( $sort_callback )
			->pluck( 'resource.reference' );
	}

	/**
	 * Delete the orphan records.
	 *
	 * @param \AweBooking\Support\Collection $synced_events The synced events.
	 * @param \AweBooking\Support\Collection $remote_events The remote events.
	 * @return array
	 */
	protected function delete_orphan_records( &$synced_events, $remote_events ) {
		$remote_uids = $remote_events->pluck( 'uid' );

		$deleted = $synced_events
			->whereNotIn( 'eid', $remote_uids )
			->each( function ( ICal_Entity $record, $index ) use ( $synced_events ) {
				$record->delete();
				$synced_events->forget( $index );
			});

		return $deleted->pluck( 'eid' )->all();
	}
}
