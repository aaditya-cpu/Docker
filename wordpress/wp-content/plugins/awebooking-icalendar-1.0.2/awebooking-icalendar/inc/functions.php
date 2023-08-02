<?php

use AweBooking\Constants;
use AweBooking\Support\Collection;
use AweBooking\ICalendar\ICal_Entity;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

function abrs_ical_logger() {
	if ( ! awebooking()->bound( 'icalendar_logger' ) ) {
		awebooking()->singleton( 'icalendar_logger', function () {
			$handler = ( new StreamHandler( WP_CONTENT_DIR . '/awebooking-icalendar.log', Logger::DEBUG ) )
				->setFormatter( new LineFormatter( null, null, true, true ) );

			return new Logger( 'awebooking', [ $handler ] );
		});
	}

	return awebooking()->make( 'icalendar_logger' );
}

/**
 * Gets the iCalendar sync link.
 *
 * @param \AweBooking\Model\Room_Type|int $room_type The room type ID.
 * @return string|null
 */
function abrs_ical_get_sync_link( $room_type ) {
	$room_type = abrs_get_room_type( $room_type );

	if ( ! $room_type ) {
		return null;
	}

	return trim( $room_type->get_meta( '_ical_sync_link' ) );
}

/**
 * Gets all synced records by room-type.
 *
 * @param  \AweBooking\Model\Room_Type|int $room_type The room type ID.
 * @return \AweBooking\Support\Collection|null
 */
function abrs_ical_get_synced_records( $room_type ) {
	global $wpdb;

	$room_units = $room_type->get_rooms();

	if ( empty( $room_units ) ) {
		return null;
	}

	$ids = wp_list_pluck( $room_units->all(), 'id' );
	$ids = implode( "', '", array_map( 'esc_sql', $ids ) );

	// @codingStandardsIgnoreLine
	$results = $wpdb->get_results( "SELECT * FROM `{$wpdb->prefix}awebooking_ical_entities` WHERE `unit_id` IN ('{$ids}')" );

	return Collection::make( (array) $results )
		->map_into( ICal_Entity::class );
}

/**
 * Get the export calendar link.
 *
 * @param  \AweBooking\Model\Room_Type|int $room_type The room type instance.
 * @return string|null
 */
function abrs_ical_get_calendar_link( $room_type ) {
	$room_type = abrs_get_room_type( $room_type );

	if ( ! $room_type ) {
		return null;
	}

	$secret = $room_type->get_meta( '_ical_secret' );

	// See missing or invalid secret key, create new one.
	if ( ! $secret || 40 !== strlen( $secret ) ) {
		$room_type->update_meta( '_ical_secret', $secret = abrs_random_string( 40 )  );
	}

	$calendar_link = abrs_route( "ical/{$room_type->get_id()}/calendar.ics", [ 's' => $secret ] );

	return apply_filters( 'abrs_ical_get_calendar_link', $calendar_link, $room_type );
}

/**
 * Run sync calendars.
 *
 * @return void
 */
function abrs_ical_async_calendars() {
	$logger = abrs_ical_logger();
	$logger->info( '[iCalendar] Cron-job: Start the synchronization via cron-job at ' . abrs_date_time( 'now' )->toDateTimeString() );

	// Get room types to sync.
	$sync_room_types = get_posts([
		'post_type'   => Constants::ROOM_TYPE,
		'numberposts' => - 1,
		'meta_query'  => [
			[ 'key' => '_ical_sync_link', 'compare' => 'EXISTS' ], // @codingStandardsIgnoreLine
		],
	]);

	if ( empty( $sync_room_types ) ) {
		$logger->info( '[iCalendar] Cron-job: Nothing to synchronization' );
		return;
	}

	$logger->debug( '[iCalendar] Cron-job: Found ' . count( $sync_room_types ) . ' room-type(s) valid to synchronization', wp_list_pluck( $sync_room_types, 'post_title' ) );
	$request = awebooking( 'ical_async_request' );

	// Walk througth sync room-types and dispatch async actions.
	array_walk( $sync_room_types, function( $post ) use ( $request, $logger ) {
		$dispatched = $request->data( [ 'room_type' => $post->ID ] )->dispatch();

		if ( is_wp_error( $dispatched ) ) {
			$logger->debug( '[iCalendar] Error dispatching: ' . $post->post_title );
		}
	});
}
add_action( 'awebooking/cron_icalendar_synchronized', 'abrs_ical_async_calendars' );
