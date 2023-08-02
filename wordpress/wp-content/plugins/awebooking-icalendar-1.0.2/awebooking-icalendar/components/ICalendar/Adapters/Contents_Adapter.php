<?php
namespace AweBooking\Component\ICalendar\Adapters;

use AweBooking\Component\ICalendar\Adapter;

class Contents_Adapter implements Adapter {
	/**
	 * Get the data from input.
	 *
	 * @param  string $input The input string.
	 * @return string
	 */
	public function get( $input ) {
		return $input;
	}
}
