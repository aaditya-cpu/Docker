<?php
namespace AweBooking\Enhanced_Calendar\Shortcodes;

use Illuminate\Support\Arr;
use AweBooking\Core\Shortcode\Shortcode;

class Room_Availability_Shortcode extends Shortcode {
	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->defaults = [
			'room'        => 0,
			'show_months' => 1,
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function output( $request ) {
		if ( empty( $this->atts['room'] ) && abrs_is_room_type() ) {
			$this->atts['room'] = get_the_ID();
		}

		if ( ! $this->atts['room'] ) {
			$this->print_error( esc_html__( 'The room type ID is required!', 'awebooking-enhanced-calendar' ) );
			return;
		}

		// Can't resolve the room type, leaving.
		if ( ! $room_type = abrs_get_room_type( $this->atts['room'] ) ) {
			return;
		}

		// Ensure the flatpickr enqueued.
		if ( ! wp_script_is( 'awebooking-enhanced-calendar', 'enqueued' ) ) {
			wp_enqueue_script( 'awebooking-enhanced-calendar' );
		}

		if ( ! wp_style_is( 'awebooking-enhanced-calendar', 'enqueued' ) ) {
			wp_enqueue_style( 'awebooking-enhanced-calendar' );
		}

		$settings = Arr::except( $this->atts, 'id' );
		echo '<div data-init="availability-calendar" data-room="' . esc_attr( $room_type->get_id() ) . '" data-settings=\'' . json_encode( $settings ) . '\'"></div>';
	}
}
