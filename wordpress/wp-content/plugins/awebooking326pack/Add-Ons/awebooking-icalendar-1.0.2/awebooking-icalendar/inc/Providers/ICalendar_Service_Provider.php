<?php
namespace AweBooking\ICalendar\Providers;

use AweBooking\Support\Service_Provider;
use AweBooking\ICalendar\Settings\ICalendar_Setting;
use AweBooking\ICalendar\Controllers\ICal_Async_Request;
use AweBooking\ICalendar\Controllers\ICalendar_Controller;
use Illuminate\Support\Arr;

class ICalendar_Service_Provider extends Service_Provider {
	/**
	 * Registers services on the plugin.
	 *
	 * @return void
	 */
	public function register() {
		// Load plugin text domain.
		load_plugin_textdomain( 'awebooking-icalendar', false, basename( dirname( ABRS_ICAL_PLUGIN_FILE ) ) . '/languages' );

		// Load core functions.
		require __DIR__ . '/../functions.php';

		$this->plugin->bind( 'ical_async_request', function () {
			return new ICal_Async_Request( abrs_ical_logger() );
		} );
	}

	/**
	 * Init service provider.
	 *
	 * @return void
	 */
	public function init() {
		// Init the async request controller.
		$this->plugin->make( 'ical_async_request' );

		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );

		add_action( 'abrs_register_admin_settings', function ( $settings ) {
			/* @var $settings \AweBooking\Admin\Admin_Settings */
			$settings->register( new ICalendar_Setting( $this->plugin ) );
		});

		add_action( 'abrs_register_admin_routes', function ( $routes ) {
			/* @var $routes \FastRoute\RouteCollector */
			$routes->get( '/ical/{room_type}/ping', ICalendar_Controller::class . '@ping' );
			$routes->get( '/ical/{room_type}/pull', ICalendar_Controller::class . '@pull' );
		});

		add_action( 'abrs_register_routes', function ( $routes ) {
			/* @var $routes \FastRoute\RouteCollector */
			$routes->get( '/ical/{room_type}/calendar.ics', ICalendar_Controller::class . '@export' );
		});
	}

	/**
	 * Enqueue the admin scripts.
	 */
	public function admin_enqueue_scripts() {
		// @codingStandardsIgnoreLine
		if ( ! abrs_admin_route_is( '/settings' ) || 'icalendar' !== Arr::get( $_GET, 'setting' ) ) {
			return;
		}

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_enqueue_style( 'awebooking-icalendar', trailingslashit( ABRS_ICAL_PLUGIN_URL ) . 'assets/css/icalendar' . $suffix . '.css', [ 'awebooking-admin' ], ABRS_ICAL_VERSION );
		wp_enqueue_script( 'awebooking-icalendar', trailingslashit( ABRS_ICAL_PLUGIN_URL ) . 'assets/js/icalendar' . $suffix . '.js', [ 'awebooking-admin' ], ABRS_ICAL_VERSION, true );
	}
}
