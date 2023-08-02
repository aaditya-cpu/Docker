<?php

use AweBooking\Constants;
use AweBooking\Model\Room_Type;

$room_types = abrs_collect( get_posts( [
	'posts_per_page' => - 1,
	'post_type'      => Constants::ROOM_TYPE,
] ) )->map_into( Room_Type::class );

?>

<?php foreach ( $room_types as $room_type ) : ?>
	<?php
	/* @var $room_type \AweBooking\Hotel\Room_Type */

	$sync_link = $room_type->get_meta( '_ical_sync_link' );
	?>

	<div class="icalendar-widget">
		<div class="icalendar-widget__label">
			<a href="<?php echo esc_url( get_edit_post_link( $room_type->get_id() ) ); ?>">
				<?php echo esc_html( $room_type->get( 'title' ) ); ?>
			</a>

			<nav>
				<?php if ( $sync_link ) : ?>
					<a href="#" class="js-icalendar-sync" data-room="<?php echo esc_attr( $room_type->get_id() ); ?>"><i class="dashicons dashicons-update"></i></a>
				<?php endif; ?>

				<a href="#" class="js-icalendar-export" data-link="<?php echo esc_url( abrs_ical_get_calendar_link( $room_type ) ); ?>"><i class="dashicons dashicons-upload"></i></a>
			</nav>
		</div>

		<label class="icalendar-widget__input">
			<i class="aficon aficon-link"></i>
			<input type="text" name="icalendar_links[<?php echo esc_attr( $room_type->get_id() ); ?>]" value="<?php echo esc_attr( $sync_link ); ?>">
		</label>
	</div>
<?php endforeach; ?>
