<?php
namespace AweBooking\Extra\Extra_Pricing;

use AweBooking\Pricing\Price;
use AweBooking\Hotel\Room_Type;
use AweBooking\Booking\Request;
use AweBooking\Pricing\Calculator_Handle;

class Extra_Price_Calculator implements Calculator_Handle {

	/**
	 * Room_Type instance.
	 *
	 * @var Room_Type
	 */
	protected $room_type;

	/**
	 * Booking request instance.
	 *
	 * @var Request
	 */
	protected $request;

	/**
	 * Extra_Price_Calculator constructor.
	 *
	 * @param Availability $availability Booking availability instance.
	 */
	public function __construct( Room_Type $room_type, Request $request ) {
		$this->room_type = $room_type;
		$this->request = $request;
	}

	/**
	 * Handle calculator the price in pipeline.
	 *
	 * @param  Price $price Current price in pipe.
	 * @return Price
	 */
	public function handle( Price $price ) {
		$max_adults = $this->room_type->get_max_adults();
		$max_children = $this->room_type->get_max_children();

		if ( ! $max_adults && ! $max_children ) {
			return $price;
		}

		// For Adults.
		$adults_request = $this->request->get_adults();
		$overloaded_adults = intval( $adults_request - $this->room_type->get_number_adults() );
		if ( $overloaded_adults > 0 ) {
			$price = $this->handle_extra_price_people( $price, $max_adults, $overloaded_adults, $this->request->get_nights(), 'adults' );
		}

		// For Children.
		$children_request = $this->request->get_children();
		$overloaded_children = intval( $children_request - $this->room_type->get_number_children() );
		if ( $overloaded_children > 0 ) {
			$price = $this->handle_extra_price_people( $price, $max_children, $overloaded_children, $this->request->get_nights(), 'children' );
		}

		return $price;
	}

	/**
	 * Handle extra price for people.
	 *
	 * @param  Price  $price Current price in pipe.
	 * @param  int    $max_people max people
	 * @param  int    $overloaded overloaded capacity
	 * @param  string $prefix  prefix meta key.
	 * @return Price
	 */
	public function handle_extra_price_people( Price $price, $max_people, $overloaded, $nights, $prefix = 'adults' ) {
		$type = $this->room_type->get_meta( $prefix . '_extra_price_type' );
		switch ( $type ) {
			case 'fixed':
				$fixed_value = $this->room_type->get_meta( $prefix . '_extra_fixed_amount' );
				$add_price = (new Price( $fixed_value ))->multiply( $overloaded )->multiply( $nights );
				$price = $price->add( $add_price );
				break;

			case 'foreach':
				$foreach_value = $this->room_type->get_meta( $prefix . '_extra_foreach_amount' );
				$validate = collect( $foreach_value )->where( 'value', '' )->first();

				// Ensure qty foreach_value fields = qty max adults and not emty.
				if ( ! $validate && count( $foreach_value ) === $max_people ) {
					$extra_price = collect( $foreach_value )->where( 'size', $overloaded )->first();
					if ( $extra_price && $extra_price['value'] ) {
						$add_price = (new Price( $extra_price['value'] ))->multiply( $overloaded )->multiply( $nights );
						$price = $price->add( $add_price );
					}
				}
				break;

			case 'upto':
				$upto_value = $this->room_type->get_meta( $prefix . '_extra_upto_amount' );
				$extra_price = collect( $upto_value )->where( 'size', '<=', $overloaded )->sortByDesc( 'size' )->first();
				if ( $extra_price ) {
					$add_price = (new Price( $extra_price['value'] ))->multiply( $overloaded )->multiply( $nights );
					$price = $price->add( $add_price );
				}
				break;

		} // End switch().

		return $price;
	}
}
