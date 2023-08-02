<?php
namespace AweBooking\Component\ICalendar;

use AweBooking\Constants;
use AweBooking\Model\Room_Type;
use AweBooking\Model\Booking;
use AweBooking\Calendar\Scheduler;
use AweBooking\Calendar\Event\Event_Interface;
use AweBooking\Support\Period;
use Sabre\VObject\Component\VCalendar;

class Writer {
	/**
	 * The room-type to export.
	 *
	 * @var Room_Type
	 */
	protected $room_type;

	/**
	 * Create a writer.
	 *
	 * @param Room_Type $room_type The room-type instance.
	 */
	public function __construct( Room_Type $room_type ) {
		$this->room_type = $room_type;
	}

	/**
	 * Create the Calendar.
	 *
	 * @return string
	 */
	public function create() {
		$vcalendar = $this->create_vcalendar();

		$period = Period::createFromDay( abrs_date_time( 'today' ) )
			->moveEndDate( '+6 months' );

		// Export the "unavailable" events.
		$this->get_calendar_events( 'state', $period )
			->where( 'value', Constants::STATE_UNAVAILABLE )
			->each( function ( $event ) use ( $vcalendar ) {
				$this->make_blocked_event( $vcalendar, $event );
			});

		// Export the "booking" events.
		$this->get_calendar_events( 'booking', $period )
			->each( function ( $event ) use ( $vcalendar ) {
				$this->make_booking_event( $vcalendar, $event );
			});

		return $vcalendar->serialize();
	}

	/**
	 * Create the VCalendar object.
	 *
	 * @return VCalendar
	 */
	protected function create_vcalendar() {
		return new VCalendar([
			'PRODID'         => '-//AweThemes//AweBooking ' . awebooking()->version() . '//EN',
			'X-WR-CALNAME'   => $this->sanitize_event_string( get_option( 'blogname' ) ),
			/* translators: The room type name */
			'X-WR-CALDESC'   => $this->sanitize_event_string( sprintf( esc_html__( 'Bookings from %s', 'awebooking-icalendar' ), $this->room_type->get( 'title' ) ) ),
			'X-WR-TIMEZONE'  => abrs_get_wp_timezone(),
			'X-ORIGINAL-URL' => $this->sanitize_event_string( home_url( '/' ) ),
		]);
	}

	/**
	 * Create the booking event in the calendar.
	 *
	 * @param  VCalendar       $vcalendar VCalendar instance.
	 * @param  Event_Interface $event     The event instance.
	 * @return void
	 */
	protected function make_booking_event( VCalendar $vcalendar, Event_Interface $event ) {
		$booking = abrs_get_booking( $event->get_value() );

		// Ignore the non-exists booking.
		if ( ! $booking || ! $booking->exists() || in_array( $booking->get_status(), [ 'trash', 'cancelled' ], true ) ) {
			return;
		}

		// Attach event into vcalendar.
		$vcalendar->add('VEVENT', [
			'UID'           => $this->generate_event_uid( $event ),
			'STATUS'        => 'CONFIRMED',
			'SUMMARY'       => $this->sanitize_event_string( $this->get_booking_summary( $booking, $event ) ),
			'DESCRIPTION'   => $this->sanitize_event_string( $this->get_booking_description( $booking, $event ) ),
			'DTSTART'       => $event->get_start_date(),
			'DTEND'         => $event->get_end_date(),
			'CREATED'       => $booking->get( 'date_created' ),
			'LAST-MODIFIED' => $booking->get( 'date_modified' ),
		]);
	}

	/**
	 * Get the booking summary.
	 *
	 * @param  Booking         $booking The booking instance.
	 * @param  Event_Interface $event   The event instance.
	 *
	 * @return string
	 */
	protected function get_booking_summary( Booking $booking, Event_Interface $event ) {
		return '#' . $booking->get_booking_number() . ' - ' . $booking->get_customer_fullname();
	}

	/**
	 * Get the booking summary.
	 *
	 * @param  Booking         $booking The booking instance.
	 * @param  Event_Interface $event   The event instance.
	 *
	 * @return array
	 */
	protected function get_booking_description( Booking $booking, Event_Interface $event ) {
		return [
			'RESERVATION' => '#' . $booking->get_booking_number(),
			'UNIT'        => '#' . abrs_optional( $event->get_resource() )->get_id(),
			'CHECKIN'     => $event->get_start_date()->format( 'Y-m-d' ),
			'CHECKOUT'    => $event->get_end_date()->format( 'Y-m-d' ),
			'PHONE'       => $booking->get( 'customer_phone' ),
			'EMAIL'       => $booking->get( 'customer_email' ),
		];
	}

	/**
	 * Create blocked event in the calendar.
	 *
	 * @param  VCalendar       $vcalendar VCalendar instance.
	 * @param  Event_Interface $event     The event instance.
	 */
	protected function make_blocked_event( VCalendar $vcalendar, Event_Interface $event ) {
		$vcalendar->add( 'VEVENT', [
			'UID'         => $this->generate_event_uid( $event ),
			'STATUS'      => 'CONFIRMED',
			/* translators: 1: The event start date, 2: The event end date. */
			'DESCRIPTION' => sprintf( esc_html__( 'Blocked from %1$s to %2$s', 'awebooking-icalendar' ), esc_html( $event->get_start_date()->format( 'Y-m-d' ) ), esc_html( $event->get_end_date()->format( 'Y-m-d' ) ) ),
			'SUMMARY'     => esc_html__( 'Blocked', 'awebooking-icalendar' ),
			'DTSTART'     => $event->get_start_date(),
			'DTEND'       => $event->get_end_date(),
		] );
	}

	/**
	 * Generate the event UID.
	 *
	 * @param \AweBooking\Calendar\Event\Event_Interface $event The event instance.
	 * @return string
	 */
	protected function generate_event_uid( Event_Interface $event ) {
		if ( $uid = $event->get_uid() ) {
			return $uid;
		}

		return sha1( $event->get_resource()->get_id() . '/' . $event->get_start_date() . '|' . $event->get_end_date() );
	}

	/**
	 * Returns all events of the room type.
	 *
	 * @param string                     $provider_name The provider name.
	 * @param \AweBooking\Support\Period $period        The period retrieves events.
	 *
	 * @return \AweBooking\Support\Collection
	 */
	protected function get_calendar_events( $provider_name, Period $period ) {
		$rooms = $this->room_type->get_rooms();

		$provider = abrs_calendar_provider(
			$provider_name, array_map( 'abrs_resource_room', $rooms->all() ), true
		);

		$scheduler = new Scheduler(
			$rooms->map( function ( $room ) use ( $provider ) {
				return abrs_calendar( abrs_resource_room( $room ), $provider );
			})
		);

		return $scheduler->get_events( $period )
			->flatten( 1 )
			->where( 'value', '!=', 0 )
			->each( function ( $event ) {
				/* @var \AweBooking\Calendar\Event\Event $event */
				$end_date = $event->get_end_date();

				if ( '23:59:00' === $end_date->format( 'H:i:s' ) ) {
					$event->set_end_date( $end_date->addMinute() );
				}

				return $event;
			});
	}

	/**
	 * Sanitize strings for .ics
	 *
	 * @param  string|array $string String to sanitize.
	 * @return string
	 */
	protected function sanitize_event_string( $string ) {
		if ( is_array( $string ) ) {
			$tmstring = '';

			foreach ( $string as $key => $value ) {
				$tmstring .= "{$key}: {$value}\n";
			}

			return $this->sanitize_event_string( $tmstring );
		}

		return sanitize_textarea_field( $string );
	}
}
