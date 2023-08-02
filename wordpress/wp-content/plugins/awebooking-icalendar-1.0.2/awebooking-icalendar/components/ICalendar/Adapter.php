<?php
namespace AweBooking\Component\ICalendar;

interface Adapter {
	/**
	 * Get the data from input.
	 *
	 * @param  mixed $input Input file name or url.
	 * @return mixed
	 */
	public function get( $input );
}
