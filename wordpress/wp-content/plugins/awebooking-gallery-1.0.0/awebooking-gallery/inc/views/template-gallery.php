<?php
/**
 * The template for displaying room gallery in the template-parts/single/content.php template
 *
 * This template can be overridden by copying it to {yourtheme}/awebooking/template-parts/single/gallery.php.
 *
 * @see      http://docs.awethemes.com/awebooking/developers/theme-developers/
 * @author   awethemes
 * @package  AweBooking
 * @version  3.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

global $room_type;

$attachment_ids = $room_type->get_gallery_ids();

if ( ! $attachment_ids ) {
	return;
}

$gallery = array_map( function( $id ) {
	$metadata = wp_get_attachment_image_src( $id, 'full' );

	if ( ! $metadata ) {
		return null;
	}

	return [
		'src' => $metadata[0],
		'w'   => $metadata[1],
		'h'   => $metadata[2],
		'thumb_src' => wp_get_attachment_image_url( $id ),
	];
}, $room_type->get_gallery_ids() );

$gallery = array_filter( $gallery );

?>

<div class="room__section room-gallery-section">
	<h4 class="room__section-title"><?php esc_html_e( 'Gallery', 'awebooking' ); ?></h4>

	<ul class="room-gallery" data-gallery='<?php echo json_encode( $gallery, JSON_HEX_APOS ); ?>'>
		<?php
		foreach ( $attachment_ids as $attachment_id ) {
			$image = '<li class="room-gallery__item">';
			$image .= wp_get_attachment_image( $attachment_id, 'awebooking_thumbnail' );
			$image .= '</li>';
			echo apply_filters( 'abrs_single_room_thumbnail_html', $image ); // WPCS: xss ok.
		}
		?>
	</ul>
</div>
