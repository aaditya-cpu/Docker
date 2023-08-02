<?php
namespace AweBooking\Component\ICalendar;

use AweBooking\Support\Collection;

class Result {
	/**
	 * The property name.
	 *
	 * @var string
	 */
	protected $property;

	/**
	 * The list events.
	 *
	 * @var \AweBooking\Support\Collection
	 */
	protected $events;

	/**
	 * Create the reader result.
	 *
	 * @param string $property The property name.
	 * @param array  $events   The result events.
	 */
	public function __construct( $property, $events = [] ) {
		$this->property = $property;
		$this->events   = new Collection( $events );
	}

	/**
	 * Gets all events.
	 *
	 * @return \AweBooking\Support\Collection
	 */
	public function events() {
		return $this->events;
	}

	/**
	 * Get the property.
	 *
	 * @return string
	 */
	public function get_property() {
		return $this->property;
	}

	/**
	 * Get the property name.
	 *
	 * @return string
	 */
	public function get_property_name() {
		$pieces = explode( '//', $this->property );

		return isset( $pieces[1] ) ? trim( $pieces[1] ) : $this->property;
	}

	/**
	 * Filter only future events
	 *
	 * @return static
	 */
	public function only_future() {
		return $this->filter( function( $event ) {
			/* @var $event \AweBooking\Component\ICalendar\Event */
			$period = $event->get_period();

			return $period->contains( 'today' ) || $period->isAfter( 'today' );
		});
	}

	/**
	 * Call the virtual methods.
	 *
	 * @param string $method    The method name.
	 * @param array  $arguments The method arguments.
	 *
	 * @return mixed
	 */
	public function __call( $method, $arguments ) {
		$response = call_user_func_array( [ $this->events, $method ], $arguments );

		if ( $response instanceof Collection ) {
			if ( spl_object_hash( $response ) === spl_object_hash( $this->events ) ) {
				return $this;
			}

			return new static( $this->property, $response );
		}

		return $response;
	}
}
