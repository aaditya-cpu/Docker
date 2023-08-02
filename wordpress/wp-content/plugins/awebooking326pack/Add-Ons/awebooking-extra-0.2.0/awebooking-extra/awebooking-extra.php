<?php
/**
 * Plugin Name:     AweBooking Extra
 * Plugin URI:      http://awethemes.com/plugins/awebooking
 * Description:     Additional awesomeness is included in just one addon.
 * Author:          awethemes
 * Author URI:      http://awethemes.com
 * Text Domain:     awebooking-extra
 * Domain Path:     /languages
 * Version:         0.2.0
 *
 * @package         AweBooking/Extra
 */

use AweBooking\Support\Template;
use AweBooking\Extra\Extra_Addon;

/**
 * Fire the extra into AweBooking.
 *
 * @param  AweBooking $awebooking AweBooking instance.
 * @return void
 */
function awebooking_register_extra_addon( AweBooking $awebooking ) {
	require_once trailingslashit( __DIR__ ) . 'vendor/autoload.php';

	Template::$template_dirs[] = trailingslashit( __DIR__ ) . 'templates/';

	$awebooking->register_addon( new Extra_Addon( 'awebooking-extra', __FILE__ ) );
}
add_action( 'awebooking/init', 'awebooking_register_extra_addon' );
