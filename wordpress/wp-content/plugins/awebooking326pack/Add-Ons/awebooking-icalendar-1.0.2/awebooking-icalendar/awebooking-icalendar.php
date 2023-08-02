<?php
/**
 * Plugin Name:     Awebooking iCalendar
 * Plugin URI:      http://awethemes.com/plugins/awebooking
 * Description:     Sync and import bookings uses iCAL format.
 * Author:          awethemes
 * Author URI:      http://awethemes.com
 * Text Domain:     awebooking-icalendar
 * Domain Path:     /languages
 * Version:         1.0.1
 *
 * @package         Awebooking/ICalendar
 */

use AweBooking\ICalendar\Installer;
use AweBooking\ICalendar\Providers\ICalendar_Service_Provider;

if ( ! defined( 'ABRS_ICAL_VERSION' ) ) {
	require trailingslashit( __DIR__ ) . 'vendor/autoload.php';

	/* Constants */
	define( 'ABRS_ICAL_VERSION', '1.0.1' );
	define( 'ABRS_ICAL_PLUGIN_FILE', __FILE__ );
	define( 'ABRS_ICAL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
	define( 'ABRS_ICAL_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

	/* Define the premium constant */
	if ( ! defined( 'ABRS_PREMIUM' ) ) {
		define( 'ABRS_PREMIUM', true );
	}

	/* Init the addon */
	add_action( 'awebooking_init', function( $plugin ) {
		/* @var \AweBooking\Plugin $plugin */
		$plugin->provider( ICalendar_Service_Provider::class );
	});

	/* Activate hooks */
	register_activation_hook( __FILE__, [ Installer::class, 'activate' ] );
	register_deactivation_hook( __FILE__, [ Installer::class, 'deactivate' ] );
}
