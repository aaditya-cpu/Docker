<?php
namespace AweBooking\Component\ICalendar;

use AweBooking\Model\Common\Timespan;
use AweBooking\Calendar\Resource\Resource;
use AweBooking\Calendar\Event\Event as Base_Event;

class Event extends Base_Event {
	/**
	 * Create an event.
	 *
	 * @param string           $uid        The resource implementation.
	 * @param \DateTime|string $start_date The start date of the event.
	 * @param \DateTime|string $end_date   The end date of the event.
	 */
	public function __construct( $uid, $start_date, $end_date ) {
		$this->uid = $uid;

		parent::__construct( new Resource( 0 ), $start_date, $end_date );
	}

	/**
	 * Gets the timespan of the event.
	 *
	 * @return Timespan
	 */
	public function get_timespan() {
		return new Timespan( $this->get_start_date(), $this->get_end_date() );
	}
}
