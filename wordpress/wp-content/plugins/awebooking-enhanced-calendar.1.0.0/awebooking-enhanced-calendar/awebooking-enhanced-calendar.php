<?php
/**
 * Plugin Name:     Awebooking Enhanced Calendar
 * Plugin URI:      https://awethemes.com/plugins/awebooking
 * Description:     Show room available status in calendar
 * Author:          awethemes
 * Author URI:      http://awethemes.com
 * Text Domain:     awebooking-enhanced-calendar
 * Domain Path:     /languages
 * Version:         1.0.0
 *
 * @package         Awebooking/Enhanced_Calendar
 */

if ( ! defined( 'AWEBOOKING_ENHANCED_CALENDAR_VERSION' ) ) {
	require __DIR__ . '/vendor/autoload.php';

	/* Define the premium constant */
	if ( ! defined( 'ABRS_PREMIUM' ) ) {
		define( 'ABRS_PREMIUM', true );
	}

	/* Constants */
	define( 'AWEBOOKING_ENHANCED_CALENDAR_VERSION', '1.0.0' );
	define( 'AWEBOOKING_ENHANCED_CALENDAR_PLUGIN_FILE', __FILE__ );
	define( 'AWEBOOKING_ENHANCED_CALENDAR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
	define( 'AWEBOOKING_ENHANCED_CALENDAR_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

	add_action( 'awebooking_init', function( $plugin ) {
		/* @var \AweBooking\Plugin $plugin */
		$plugin->provider( \AweBooking\Enhanced_Calendar\Service_Provider::class );
	});
}
