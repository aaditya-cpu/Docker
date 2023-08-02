<?php
namespace AweBooking\ICalendar\Logger;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

class SSEHandler extends StreamHandler {
	/**
	 * {@inheritdoc}
	 */
	public function __construct( $level = Logger::DEBUG ) {
		parent::__construct( fopen( 'php://output', 'w' ), $level );
	}

	/**
	 * {@inheritdoc}
	 */
	protected function write( array $record ) {
		parent::write( $record );
		flush();
		usleep( 50000 );
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getDefaultFormatter() {
		return new LineFormatter( "event: log\ndata: {\"level\": \"%level_name%\",\"message\": \"%message%\"}\n\n" );
	}
}
