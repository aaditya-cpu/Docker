<?php
namespace AweBooking\Enhanced_Calendar\Controllers;

use AweBooking\Constants;
use AweBooking\Hotel\Room_Type;
use AweBooking\Support\Period;
use AweBooking\Calendar\Scheduler;
use Awethemes\Http\Request;
use Awethemes\Http\Json_Response;

class Availability_Calendar_Controller {
	/**
	 * Show the availability dates.
	 *
	 * @param \Awethemes\Http\Request     $request   The request instance.
	 * @param \AweBooking\Hotel\Room_Type $room_type The room type instance.
	 *
	 * @return mixed
	 */
	public function show( Request $request, Room_Type $room_type ) {
		try {
			$period = $this->get_month_period( $request );
		} catch ( \Exception $e ) {
			return new Json_Response( [ 'error' => $e->getMessage() ], 500 );
		}

		// Create the scheduler.
		$scheduler = $this->create_rooms_scheduler( $room_type );

		// Fetch all events as itemized.
		$itemized = $scheduler->get_events( $period )
			->map( function ( $events ) {
				/* @var $events \AweBooking\Calendar\Event\Events */
				return $events->itemize();
			});

		// Preapre response availability.
		$availability = [];

		foreach ( $period->getDatePeriod( '1 day' ) as $date ) {
			/* @var $date \DateTimeInterface */
			$index = $date->format( 'Y-m-d' );

			$availability[ $index ] = $this->check_availability( $itemized, $index );
		}

		return new Json_Response( $availability, 200 );
	}

	/**
	 * Check the availability of given date.
	 *
	 * @param \AweBooking\Support\Collection $itemized The itemized.
	 * @param string                         $index    The date index.
	 *
	 * @return bool
	 */
	protected function check_availability( $itemized, $index ) {
		$available = 0;

		foreach ( $itemized->all() as $k => $v ) {
			/* @var $v \AweBooking\Calendar\Event\Itemized */
			if ( Constants::STATE_AVAILABLE === $v->get( $index ) ) {
				$available++;
			}
		}

		return $available;
	}

	/**
	 * Returns the month period from the request.
	 *
	 * @param  \Awethemes\Http\Request $request The request instance.
	 * @return \AweBooking\Support\Period
	 */
	protected function get_month_period( Request $request ) {
		$original_timezone = date_default_timezone_get();
		date_default_timezone_set( abrs_get_wp_timezone() ); // @codingStandardsIgnoreLine

		if ( $request->has( 'start_date', 'end_date' ) ) {
			$period = Period::create( $request['start_date'], $request['end_date'] )
							->moveEndDate( '1 day' );
		} elseif ( $request->has( 'month', 'year' ) ) {
			$period = Period::createFromMonth( $request['year'], $request['month'] );
		} else {
			$period = Period::createFromDuration( 'today', '1 month' );
		}

		if ( $period->get_start_date() < abrs_date( 'today' ) ) {
			$period = $period->startingOn( 'today' );
		}

		date_default_timezone_set( $original_timezone ); // @codingStandardsIgnoreLine

		return $period;
	}

	/**
	 * Create the room scheduler.
	 *
	 * @param \AweBooking\Hotel\Room_Type $room_type The room type instance.
	 * @return \AweBooking\Calendar\Scheduler
	 */
	protected function create_rooms_scheduler( Room_Type $room_type ) {
		$rooms = $room_type->get_rooms();

		$provider  = abrs_calendar_provider( 'state', $rooms, true );
		$scheduler = new Scheduler;

		abrs_collect( $rooms )->each( function ( $room ) use ( $scheduler, $provider ) {
			$scheduler->push( abrs_calendar( abrs_resource_room( $room ), $provider ) );
		} );

		return $scheduler;
	}
}
