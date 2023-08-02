<?php
namespace AweBooking\Extra\Calendar;

use Skeleton\Widget;
use AweBooking\AweBooking;

class Calendar_Widget extends Widget {
	/**
	 * Array of default values for widget settings.
	 *
	 * @var array
	 */
	public $defaults = [
		'title'     => '',
		'room_type' => '',
	];

	/**
	 * Contructor the widget.
	 */
	public function __construct() {
		parent::__construct( 'awebooking_calendar', esc_html__( 'AweBooking: Calendar', 'awebooking-extra' ), [
			'classname'   => 'awebooking-calendar-widget',
			'description' => esc_html__( 'Display availability calendar of your room-type.', 'awebooking-extra' ),
		]);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @param  array $args      The widget arguments set up when a sidebar is registered.
	 * @param  array $instance  The widget settings as set by user.
	 */
	public function widget( $args, $instance ) {
		$instance = wp_parse_args( (array) $instance, $this->defaults );

		/** This filter is documented in wp-includes/widgets/class-wp-widget-pages.php */
		$title = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );

		// Build shortcode attributes.
		$attributes = apply_filters( 'awebooking/widget/calendar_attributes', [
			'id' => $instance['room_type'] ? $instance['room_type'] : '',
		]);

		// Output the widget.
		echo $args['before_widget'];
		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		$html_attributes = '';
		foreach ( $attributes as $key => $value ) {
			$html_attributes .= $key . '="' . esc_attr( $value ) . '" ';
		}

		do_shortcode( '[awebooking_room_type_availability ' . $html_attributes . ']' );
		echo $args['after_widget']; // WPCS: XSS OK.
	}

	/**
	 * Array of widget fields args.
	 *
	 * @var array
	 */
	public function fields() {
		$fields[] = [
			'id'   => 'title',
			'type' => 'text',
			'name' => esc_html__( 'Title:', 'awebooking-extra' ),
		];

		$fields[] = [
			'id'   => 'room_type',
			'type' => 'select',
			'name' => esc_html__( 'Room type:', 'awebooking-extra' ),
			'options_cb' => wp_data_callback( 'posts',  array(
				'post_type'   => AweBooking::ROOM_TYPE,
				'posts_per_page' => -1,
			)),
		];

		return $fields;
	}
}
