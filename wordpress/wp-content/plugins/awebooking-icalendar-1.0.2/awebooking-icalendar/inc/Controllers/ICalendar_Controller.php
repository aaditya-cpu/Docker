<?php
namespace AweBooking\ICalendar\Controllers;

use Awethemes\Http\Request;
use Awethemes\Http\Exception\HttpException;
use AweBooking\ICalendar\Sync;
use AweBooking\ICalendar\Logger\SSEHandler;
use AweBooking\Component\ICalendar\Writer;
use AweBooking\Model\Room_Type;
use Awethemes\Http\Response;
use Monolog\Logger;

class ICalendar_Controller {
	/**
	 * Export the ICS calendar.
	 *
	 * @param \Awethemes\Http\Request     $request   The http request instance.
	 * @param \AweBooking\Model\Room_Type $room_type The room type instance.
	 *
	 * @return \Awethemes\Http\Response
	 */
	public function export( Request $request, Room_Type $room_type ) {
		$this->prepare_export_request( $request, $room_type );

		try {
			$calendar = ( new Writer( $room_type ) )->create();
		} catch ( \Exception $e ) {
			$calendar = '';
		}

		return ( new Response( $calendar ) )
			->header( 'Content-Type', 'text/calendar; charset=' . get_option( 'blog_charset' ) )
			->header( 'Content-Disposition', 'attachment; filename=calendar.ics' );
	}

	/**
	 * Check if a given request has access to get items.
	 *
	 * @param \Awethemes\Http\Request     $request   The http request instance.
	 * @param \AweBooking\Model\Room_Type $room_type The room type instance.
	 *
	 * @return void
	 *
	 * @throws \RuntimeException
	 */
	protected function prepare_export_request( Request $request, Room_Type $room_type ) {
		$secret = $request->get( 's' );

		if ( strlen( $secret ) !== 40 ) {
			throw new \RuntimeException( esc_html__( 'Invalid secret key.', 'awebooking-icalendar' ) );
		}

		if ( ! hash_equals( $secret, $room_type->get_meta( '_ical_secret' ) ) ) {
			throw new \RuntimeException( esc_html__( '"Invalid secret pair.', 'awebooking-icalendar' ) );
		}
	}

	/**
	 * Ping to "ical" remote of a room type.
	 *
	 * @param \Awethemes\Http\Request     $request   The request instance.
	 * @param \AweBooking\Model\Room_Type $room_type The room type instance.
	 *
	 * @return mixed
	 */
	public function ping( Request $request, Room_Type $room_type ) {
		return 'ping';
	}

	/**
	 * Fetch the events from "ical" in a room type.
	 *
	 * @param \AweBooking\Model\Room_Type $room_type The room type instance.
	 *
	 * @throws mixed
	 */
	public function pull( Room_Type $room_type ) {
		if ( ! abrs_ical_get_sync_link( $room_type ) ) {
			throw new HttpException( 500, esc_html__( 'Unable to process this action. The room type is not connected to any iCalendar.', 'awebooking-icalendar' ) );
		}

		// Time to run the import!
		set_time_limit( 0 );

		// Turn off PHP output compression.
		// @codingStandardsIgnoreStart
		$previous = error_reporting( error_reporting() ^ E_WARNING );
		ini_set( 'output_buffering', 'off' );
		ini_set( 'zlib.output_compression', false );
		error_reporting( $previous );
		// @codingStandardsIgnoreEnd

		header( 'Content-Type: text/event-stream' );

		// Setting this header instructs Nginx to disable fastcgi_buffering
		// and disable gzip for this request.
		if ( $GLOBALS['is_nginx'] ) {
			header( 'X-Accel-Buffering: no' );
			header( 'Content-Encoding: none' );
		}

		// 2KB padding for IE
		echo ':' . str_repeat( ' ', 2048 ) . "\n\n"; // WPCS: XSS OK.

		// Ensure we're not buffered.
		wp_ob_end_flush_all();
		flush();

		$logger = new Logger( 'awebooking-icalendar', [ new SSEHandler ] );

		( new Sync( $logger ) )->run( $room_type );

		$this->emit_sse_message( [ 'action' => 'complete' ] );
		exit;
	}

	/**
	 * Emit a Server-Sent Events message.
	 *
	 * @param mixed $data Data to be JSON-encoded and sent in the message.
	 */
	protected function emit_sse_message( $data ) {
		echo "event: message\n";
		echo 'data: ' . wp_json_encode( $data ) . "\n\n";

		// Extra padding.
		echo ':' . str_repeat( ' ', 2048 ) . "\n\n"; // @WPCS: XSS OK.
		flush();
	}
}
