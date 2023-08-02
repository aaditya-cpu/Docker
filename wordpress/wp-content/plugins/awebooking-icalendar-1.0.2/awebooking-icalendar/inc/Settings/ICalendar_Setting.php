<?php
namespace AweBooking\ICalendar\Settings;

use AweBooking\Constants;
use AweBooking\Model\Room_Type;
use AweBooking\Admin\Settings\Abstract_Setting;
use Awethemes\Http\Request;

class ICalendar_Setting extends Abstract_Setting {
	/**
	 * {@inheritdoc}
	 */
	public function get_id() {
		return 'icalendar';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_label() {
		return esc_html__( 'ICalendar', 'awebooking-icalendar' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function setup_fields() {
		$this->add_field([
			'id'   => '__icalendar_links_title',
			'type' => 'title',
			'name' => esc_html__( 'Import Calendar', 'awebooking-icalendar' ),
			'desc' => esc_html__( 'Calendar importing allows you to automatically keep your AweBooking calendar up to date with an external calendar that supports the iCalendar format, including Google Calendar or the calendar on Booking.com, HomeAway, Airbnb etc.', 'awebooking-icalendar' ),
		]);

		$this->add_field( [
			'id'          => '__icalendar_links',
			'type'        => 'include',
			'show_names'  => false,
			'save_fields' => false,
			'include'     => trailingslashit( __DIR__ ) . 'views/html-setting-icalendar.php',
		]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function save( Request $request ) {
		parent::save( $request );

		$links = $request->get( 'icalendar_links', null );
		if ( ! is_array( $links ) || is_null( $links ) ) {
			return;
		}

		foreach ( $links as $room_type => $link ) {
			$room_type = abrs_get_room_type( $room_type );
			$room_type->update_meta( '_ical_sync_link', rawurldecode( esc_url_raw( $link ) ) );
		}
	}
}
