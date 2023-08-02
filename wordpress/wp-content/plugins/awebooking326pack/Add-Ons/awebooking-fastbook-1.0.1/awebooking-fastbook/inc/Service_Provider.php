<?php

namespace AweBooking\FastBook;

use AweBooking\Support\Service_Provider as Base_Service_Provider;

/**
 * Fastbook.
 */
class Service_Provider extends Base_Service_Provider {

	/**
	 * Registers services on the plugin.
	 *
	 * @access private
	 */
	public function register() {

	}

	/**
	 * Init service provider.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'abrs_search_complete', [ $this, 'add_reservation_fastbook' ] );
	}

	/**
	 * Has item navigation checkout
	 *
	 * @param \AweBooking\Frontend\Search\Search_Query $search The search instance.
	 */
	public function add_reservation_fastbook( $search ) {
		$request = abrs_http_request();

		if ( ! $request->get( 'only' ) ) {
			return;
		}

		if ( ! $search->results->has_items() ) {
			return;
		}

		$items = $search->results->get_items();

		$res = abrs_reservation();
		$res->flush();

		try {
			$res->add_room_stay( $search->results->get_request(), $items[0]['room_type'] );

			wp_redirect( abrs_get_page_permalink( 'checkout' ) );
			exit();
		} catch ( \Exception $e ) {}
	}
}
