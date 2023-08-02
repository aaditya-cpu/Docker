<?php
namespace AweBooking\Component\ICalendar;

use DateTimeZone;
use Sabre\VObject\Reader as VReader;
use Sabre\VObject\Component\VCalendar;
use AweBooking\Component\ICalendar\Adapters\Remote_Adapter;
use AweBooking\Component\ICalendar\Exceptions\ReadingException;
use Sabre\VObject\TimeZoneUtil;

class Reader {
	use Adapter_Trait;

	/**
	 * Constructor.
	 *
	 * @param Adapter|null $adapter The adapter implementation.
	 */
	public function __construct( Adapter $adapter = null ) {
		$this->adapter = $adapter ?: new Remote_Adapter;
	}

	/**
	 * Read the calendar.
	 *
	 * @param  string $input Input ical link.
	 * @return \AweBooking\Component\ICalendar\Result
	 *
	 * @throws \AweBooking\Component\ICalendar\Exceptions\ReadingException
	 */
	public function read( $input ) {
		$vcalendar = abrs_rescue( function () use ( $input ) {
			return VReader::read( $this->adapter->get( $input ) );
		});

		if ( ! $vcalendar instanceof VCalendar || ! isset( $vcalendar->prodid ) ) {
			throw new ReadingException( esc_html__( 'Unable reading data from iCalendar.', 'awebooking-icalendar' ) );
		}

		$result        = new Result( (string) $vcalendar->prodid );
		$untrusted_uid = $this->is_untrusted_uid( $vcalendar );

		// Make sure we have a list of event.
		if ( ! isset( $vcalendar->vevent ) ) {
			return $result;
		}

		$timezone = new DateTimeZone( abrs_get_wp_timezone() );
		if ( $timezone_object = $vcalendar->{'X-WR-TIMEZONE'} ) {
			$timezone = TimeZoneUtil::getTimeZone( (string) $timezone_object );
		}

		foreach ( $vcalendar->vevent as $vevent ) {
			// Ignore the PENDING.
			if ( 0 === strpos( (string) $vevent->summary, 'PENDING' ) ) {
				continue;
			}

			// Create the event.
			try {
				$event = new Event(
					(string) $vevent->uid,
					$vevent->dtstart->getDateTime( $timezone ),
					$vevent->dtend->getDateTime( $timezone )
				);
			} catch ( \Exception $e ) {
				continue;
			}

			$event->set_status( isset( $vevent->status ) ? (string) $vevent->status : '' );
			$event->set_summary( isset( $vevent->summary ) ? (string) $vevent->summary : '' );
			$event->set_description( isset( $vevent->description ) ? (string) $vevent->description : '' );

			if ( isset( $vevent->created ) ) {
				$event->set_created( $vevent->created->getDateTime() );
			}

			if ( isset( $vevent->{'LAST-MODIFIED'} ) ) {
				$event->set_last_modified( $vevent->{'LAST-MODIFIED'}->getDateTime( $timezone ) );
			}

			if ( $untrusted_uid ) {
				$dummyuid = $event->get_summary() . '|' . $event->get_start_date()->toDateString() . '|' . $event->get_end_date()->toDateString();
				$event->set_uid( md5( $dummyuid ) );
			}

			$result->push( $event );
		}

		return $result;
	}

	/**
	 * Determines if a calendar provider give us untrusted UID of events.
	 *
	 * @see https://stackoverflow.com/questions/38193837/uid-of-airbnb-ics-will-change-every-time-i-access
	 *
	 * @param  VCalendar $vcalendar VCalendar object.
	 * @return boolean
	 */
	protected function is_untrusted_uid( VCalendar $vcalendar ) {
		$prodid = (string) $vcalendar->prodid;

		// TODO: Check for TripAdvisor.
		if ( 0 === strpos( $prodid, '-//Airbnb' ) || strpos( $prodid, 'TripAdvisor' ) ) {
			return true;
		}

		return true;
	}
}
