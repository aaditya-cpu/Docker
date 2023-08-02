<?php
/**
 * Plugin Name:     AweBooking Fastbook
 * Plugin URI:      http://awethemes.com/plugins/awebooking
 * Description:     Book a room quickly
 * Author:          awethemes
 * Author URI:      http://awethemes.com
 * Text Domain:     awebooking-fastbook
 * Domain Path:     /languages
 * Version:         1.0.1
 *
 * @package         Awebooking/Fastbook
 */


if ( ! defined( 'ABRS_FASTBOOK_VERSION' ) ) {

	/* Define the premium constant */
	if ( ! defined( 'ABRS_PREMIUM' ) ) {
		define( 'ABRS_PREMIUM', true );
	}

	/* Constants */
	define( 'ABRS_FASTBOOK_VERSION', '1.0.1' );
	define( 'ABRS_FASTBOOK_PLUGIN_FILE', __FILE__ );
	define( 'ABRS_FASTBOOK_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
	define( 'ABRS_FASTBOOK_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

	add_action( 'awebooking_init', function( $plugin ) {
		require trailingslashit( __DIR__ ) . 'vendor/autoload.php';

		/* @var \AweBooking\Plugin $plugin */
		$plugin->provider( \AweBooking\FastBook\Service_Provider::class );
	});
}
