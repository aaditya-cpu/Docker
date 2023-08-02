<?php
/**
 * Plugin Name:     Awebooking Gallery
 * Plugin URI:      https://awethemes.com/plugins/awebooking
 * Description:     Display the gallery.
 * Author:          awethemes
 * Author URI:      http://awethemes.com
 * Text Domain:     awebooking-gallery
 * Domain Path:     /languages
 * Version:         1.0.0
 *
 * @package         Awebooking/Gallery
 */

if ( ! defined( 'AWEBOOKING_GALLERY_VERSION' ) ) {
	require __DIR__ . '/vendor/autoload.php';

	/* Define the premium constant */
	if ( ! defined( 'ABRS_PREMIUM' ) ) {
		define( 'ABRS_PREMIUM', true );
	}

	/* Constants */
	define( 'AWEBOOKING_GALLERY_VERSION', '1.0.0' );
	define( 'AWEBOOKING_GALLERY_PLUGIN_FILE', __FILE__ );
	define( 'AWEBOOKING_GALLERY_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
	define( 'AWEBOOKING_GALLERY_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

	add_action( 'awebooking_init', function( $plugin ) {
		/* @var \AweBooking\Plugin $plugin */
		$plugin->provider( \AweBooking\Gallery\Service_Provider::class );
	});
}
