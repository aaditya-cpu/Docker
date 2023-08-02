<?php
namespace AweBooking\Component\ICalendar;

trait Adapter_Trait {
	/**
	 * The adapter instance.
	 *
	 * @var \AweBooking\Component\ICalendar\Adapter
	 */
	protected $adapter;

	/**
	 * Set the adapter.
	 *
	 * @param  \AweBooking\Component\ICalendar\Adapter $adapter The adapter implementation.
	 * @return void
	 */
	public function set_adapter( Adapter $adapter ) {
		$this->adapter = $adapter;
	}

	/**
	 * Get the adapter.
	 *
	 * @return \AweBooking\Component\ICalendar\Adapter
	 */
	public function get_adapter() {
		return $this->adapter;
	}
}
