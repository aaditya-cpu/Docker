<?php
namespace AweBooking\ICalendar\Controllers;

use WP_Error;
use Psr\Log\LoggerInterface;
use AweBooking\ICalendar\Sync;

class ICal_Async_Request extends \WP_Async_Request {
	/**
	 * The logger implementation.
	 *
	 * @var \Psr\Log\LoggerInterface
	 */
	protected $logger;

	/**
	 * Make the async request.
	 *
	 * @param LoggerInterface $logger The logger implementation.
	 */
	public function __construct( LoggerInterface $logger ) {
		$this->logger = $logger;

		$this->prefix = 'awebooking';
		$this->action = 'icalendar_synchronized';

		parent::__construct();
	}

	/**
	 * Handle the request.
	 */
	protected function handle() {
		try {
			$this->doing_sync();
			wp_send_json_success( [ 'success' => 'OK' ] );
		} catch ( \Exception $e ) {
			$this->logger->debug( '[iCalendar] ' . $e->getMessage() );
			wp_send_json_error( new WP_Error( 'sync_error', $e->getMessage() ) );
		}
	}

	/**
	 * Handle sync based on the request.
	 *
	 * @throws \InvalidArgumentException
	 */
	protected function doing_sync() {
		// @codingStandardsIgnoreLine
		if ( ! isset( $_REQUEST['room_type'] ) || ! is_numeric( $_REQUEST['room_type'] ) ) {
			throw new \InvalidArgumentException( 'Missing request parameter' );
		}

		// @codingStandardsIgnoreLine
		$room_type = abrs_get_room_type( absint( $_REQUEST['room_type'] ) );

		if ( ! $room_type || ! $room_type->exists() ) {
			throw new \InvalidArgumentException( 'The room type was not found' );
		}

		$this->logger->debug( '[iCalendar] Run synchronization for: ' . $room_type->get( 'title' ) );

		( new Sync( $this->logger ) )->run( $room_type );

		$this->logger->debug( '[iCalendar] Synced for: ' . $room_type->get( 'title' ) );
	}
}
