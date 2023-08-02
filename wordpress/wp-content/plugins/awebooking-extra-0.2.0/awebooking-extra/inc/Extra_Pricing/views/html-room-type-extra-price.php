<?php
use AweBooking\Hotel\Room_Type;

global $post;
$room_type = new Room_Type( $post->ID );
$max_adults = $room_type->get_max_adults();
$max_children = $room_type->get_max_children();
$adults_extra_price_type = $room_type->get_meta( 'adults_extra_price_type' ) ?: '';

switch ( $adults_extra_price_type ) {
	case 'fixed':
		$adults_extra_price_amount = $room_type->get_meta( 'adults_extra_fixed_amount' ) ? $room_type->get_meta( 'adults_extra_fixed_amount' ) : '';
		break;

	case 'foreach':
		$adults_extra_price_amount = $room_type->get_meta( 'adults_extra_foreach_amount' ) ? json_encode( $room_type->get_meta( 'adults_extra_foreach_amount' ) ) : '';
		break;

	case 'upto':
		$adults_extra_price_amount = $room_type->get_meta( 'adults_extra_upto_amount' ) ? json_encode( $room_type->get_meta( 'adults_extra_upto_amount' ) ) : '';
		break;

	default:
		$adults_extra_price_amount = '';
		break;
}

$children_extra_price_type = $room_type->get_meta( 'children_extra_price_type' ) ?: '';

switch ( $children_extra_price_type ) {
	case 'fixed':
		$children_extra_price_amount = $room_type->get_meta( 'children_extra_fixed_amount' ) ? $room_type->get_meta( 'children_extra_fixed_amount' ) : '';
		break;

	case 'foreach':
		$children_extra_price_amount = $room_type->get_meta( 'children_extra_foreach_amount' ) ? json_encode( $room_type->get_meta( 'children_extra_foreach_amount' ) ) : '';
		break;

	case 'upto':
		$children_extra_price_amount = $room_type->get_meta( 'children_extra_upto_amount' ) ? json_encode( $room_type->get_meta( 'children_extra_upto_amount' ) ) : '';
		break;

	default:
		$children_extra_price_amount = '';
		break;
}
?>

<div id="awebooking-extra-price">
	<?php if ( $max_adults ) : ?>
		<div class="cmb-row">
			<label><b><?php esc_html_e( 'For Adults:', 'awebooking-extra' ); ?></b></label>
			<extra-price :capacity_number="<?php echo esc_attr( $max_adults ); ?>" :prefix="'adults'" :type="'<?php echo esc_attr( $adults_extra_price_type ); ?>'" :price="'<?php echo esc_attr( $adults_extra_price_amount ); ?>'"></extra-price>
		</div>
	<?php endif; ?>

	<?php if ( $max_children ) : ?>
		<div class="cmb-row">
			<label><b><?php esc_html_e( 'For Children:', 'awebooking-extra' ); ?></b></label>
			<extra-price :capacity_number="<?php echo esc_attr( $max_children ); ?>" :prefix="'children'" :type="'<?php echo esc_attr( $children_extra_price_type ); ?>'" :price="'<?php echo esc_attr( $children_extra_price_amount ); ?>'"></extra-price>
		</div>
	<?php endif;?>
</div>
