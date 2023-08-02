<?php
namespace AweBooking\Extra\Calendar;

use AweBooking\Hotel\Room_Type;
use AweBooking\Support\Carbonate;
use AweBooking\Support\Service_Hooks;
use Skeleton\Support\Validator;

class Calendar_Hooks extends Service_Hooks {

	/**
	 * Registers services on the given container.
	 *
	 * This method should only be used to configure services and parameters.
	 *
	 * @param Container $container Container instance.
	 */
	public function register( $container ) {
		add_action( 'widgets_init', function() {
			register_widget( Calendar_Widget::class );
		});

		add_shortcode( 'awebooking_room_type_availability', [ $this, 'the_availability_shortcode' ] );
	}

	/**
	 * Init service provider.
	 *
	 * This method will be run after container booted.
	 *
	 * @param AweBooking $awebooking AweBooking instance.
	 */
	public function init( $awebooking ) {
		add_action( 'wp_ajax_awebooking/get_calendar', [ $this, 'ajax_get_calendar' ] );
		add_action( 'wp_ajax_nopriv_awebooking/get_calendar', [ $this, 'ajax_get_calendar' ] );

		add_action( 'awebooking/room_type_tabs', [ $this, 'register_tabs' ] );
	}

	public function register_tabs( $tabs ) {
		$tabs['calendar'] = array(
			'title'    => esc_html__( 'Calendar', 'awebooking-extra' ),
			'priority' => 10,
			'callback' => function() {
				global $room_type;

				if ( $room_type instanceof Room_Type ) {
					(new Availability_Calendar( $room_type ))->display();
				}
			},
		);

		return $tabs;
	}

	public function ajax_get_calendar() {
		$validator = new Validator( $_REQUEST, [
			'date'      => 'required|date',
			'room_type' => 'required|int',
		]);

		if ( $validator->fails() ) {
			return wp_send_json_error( [ 'message' => esc_html__( 'Required parameter missing or invalid.', 'awebooking-extra' ) ] );
		}

		// Get the room-type first.
		$room_type = new Room_Type( absint( $_REQUEST['room_type'] ) );
		if ( ! $room_type->exists() ) {
			return wp_send_json_error( [ 'message' => esc_html__( 'The room-type was not found.', 'awebooking-extra' ) ] );
		}

		// Create calendar Carbon date from request.
		$calendar_date = Carbonate::create_date(
			sanitize_text_field( $_REQUEST['date'] )
		);

		// Trigger next and prev Calendar.
		if ( isset( $_REQUEST['trigger'] ) && 'next' === $_REQUEST['trigger'] ) {
			$calendar_date = $calendar_date->addMonth();
		} elseif ( isset( $_REQUEST['trigger'] ) && 'prev' === $_REQUEST['trigger'] ) {
			$calendar_date = $calendar_date->subMonth();
		}

		$options = [];
		if ( isset( $_REQUEST['options'] ) && is_array( $_REQUEST['options'] ) ) {
			$options = $_REQUEST['options'];
		}

		(new Availability_Calendar( $room_type, $calendar_date, $options ))->display();
		exit;
	}

	/**
	 * Build the "awebooking_room_type_availability" shortcode.
	 *
	 * TODO:
	 * 1. Cache the calendar output.
	 * 2. Add calendar settings.
	 * 3. ...
	 *
	 * @param  array  $atts     The shortcode attributes.
	 * @param  string $contents The shortcode contents.
	 * @return void
	 */
	public function the_availability_shortcode( $atts, $contents = '' ) {
		$pairs = apply_filters( 'awebooking/shortcode/awebooking_room_type_availability/pairs', [
			'id'   => 0,
			'year' => null,
		]);

		// Build shortcode attrs, keep only the pairs.
		$atts = shortcode_atts( $pairs, $atts, 'awebooking_room_type_availability' );

		// Try guest ID from the loop.
		if ( ! $atts['id'] ) {
			$atts['id'] = get_the_ID();
		}

		$room_type = new Room_Type( $atts['id'] );
		if ( ! $room_type->exists() ) {
			printf( '<div class="awebooking-notice awebooking-notice--">%s</div>', esc_html__( 'No room type was not found.', 'awebooking-extra' ) );
			return;
		}

		wp_enqueue_style( 'awebooking-calendar' );
		wp_enqueue_script( 'awebooking-calendar' );

		$calendar = new Availability_Calendar( $room_type );
		$calendar->display();
	}
}
