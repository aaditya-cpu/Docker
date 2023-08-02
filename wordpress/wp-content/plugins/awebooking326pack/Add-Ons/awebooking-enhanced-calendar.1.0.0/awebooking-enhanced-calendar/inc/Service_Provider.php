<?php
namespace AweBooking\Enhanced_Calendar;

use AweBooking\Support\Service_Provider as Core_Service_Provider;

class Service_Provider extends Core_Service_Provider {
	/**
	 * Registers services on the plugin.
	 *
	 * @access private
	 */
	public function register() {
		add_action( 'widgets_init', function() {
			register_widget( Widgets\Room_Availability_Widget::class );
		});
	}

	/**
	 * Init service provider.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

		add_action( 'abrs_register_routes', function ( $routes ) {
			/* @var $routes \FastRoute\RouteCollector */
			$routes->get( '/calendar/availability/{room_type}', Controllers\Availability_Calendar_Controller::class . '@show' );
		});

		add_filter( 'abrs_shortcodes', function ( $shortcodes ) {
			$shortcodes['awebooking_room_availability']      = Shortcodes\Room_Availability_Shortcode::class;
			$shortcodes['awebooking_room_type_availability'] = Shortcodes\Room_Availability_Shortcode::class;

			return $shortcodes;
		});
	}

	/**
	 * Enqueue scripts.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		$version    = AWEBOOKING_ENHANCED_CALENDAR_VERSION;
		$suffix     = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$plugin_url = trailingslashit( AWEBOOKING_ENHANCED_CALENDAR_PLUGIN_URL );

		wp_register_style( 'awebooking-enhanced-calendar', $plugin_url . 'assets/css/enhanced-calendar' . $suffix . '.css', [], $version );
		wp_register_script( 'awebooking-enhanced-calendar', $plugin_url . 'assets/js/enhanced-calendar' . $suffix . '.js', [ 'awebooking', 'flatpickr' ], $version, true );
	}
}
