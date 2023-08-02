<?php
namespace AweBooking\Enhanced_Calendar\Widgets;

use AweBooking\Core\Widget\Widget;
use AweBooking\Support\WP_Data;

class Room_Availability_Widget extends Widget {
	/**
	 * Contructor.
	 */
	public function __construct() {
		parent::__construct(
			'awebooking_availability_calendar',
			esc_html__( 'AweBooking: Availability Calendar', 'awebooking-enhanced-calendar' ),
			[
				'classname'   => 'awebooking-availability-calendar-widget',
				'description' => esc_html__( 'Display the availability calendar of single room.', 'awebooking-enhanced-calendar' ),
			]
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function widget( $args, $instance ) {
		$instance = $this->parse( $instance );

		/** This filter is documented in wp-includes/widgets/class-wp-widget-pages.php */
		$title = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );

		// Output the widget.
		echo $args['before_widget']; // @WPCS: XSS OK.

		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title']; // @WPCS: XSS OK.
		}

		echo @do_shortcode( '[awebooking_room_availability ' . abrs_html_attributes( $instance ) . ']' ); // @WPCS: XSS OK.

		echo $args['after_widget']; // WPCS: XSS OK.
	}

	/**
	 * {@inheritdoc}
	 */
	public function fields() {
		return [
			[
				'id'      => 'title',
				'type'    => 'text',
				'name'    => esc_html__( 'Title', 'awebooking-enhanced-calendar' ),
				'default' => '',
			],
			[
				'id'       => 'room',
				'type'     => 'select',
				'name'     => esc_html__( 'Room?', 'awebooking-enhanced-calendar' ),
				'required' => true,
				'options_cb' => WP_Data::cb( 'posts', [ 'post_type' => 'room_type', 'posts_per_page' => - 1 ] ),
			],
			[
				'id'      => 'show_months',
				'type'    => 'select',
				'name'    => esc_html__( 'Show months', 'awebooking-enhanced-calendar' ),
				'default' => '1',
				'options' => [
					'1' => 1,
					'2' => 2,
				],
			],
		];
	}
}
