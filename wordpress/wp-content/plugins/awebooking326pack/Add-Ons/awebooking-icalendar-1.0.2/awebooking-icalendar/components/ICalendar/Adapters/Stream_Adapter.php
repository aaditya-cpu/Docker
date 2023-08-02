<?php
namespace AweBooking\Component\ICalendar\Adapters;

use AweBooking\Component\ICalendar\Adapter;

class Stream_Adapter implements Adapter {
	/**
	 * Get the data resource from a file.
	 *
	 * @param  string $input The input file path.
	 * @return resource
	 *
	 * @throws \InvalidArgumentException
	 */
	public function get( $input ) {
		$resource = @fopen( $input, 'r' );

		if ( ! is_resource( $resource ) ) {
			throw new \InvalidArgumentException( 'Resource not found or unreadable.' );
		}

		return $resource;
	}
}
