<?php
namespace AweBooking\Extra\Extra_Pricing;

use AweBooking\Hotel\Room_Type;
use AweBooking\Booking\Request;
use AweBooking\Booking\Availability;
use AweBooking\Support\Service_Hooks;

class Extra_Pricing_Hooks extends Service_Hooks {
	/**
	 * Init service provider.
	 */
	public function register( $container ) {
		add_action( 'awebooking/register_metabox/room_type', [ $this, 'add_room_type_metabox' ] );
		add_filter( 'awebooking/availability/room_price_pipes', [ $this, 'add_extra_price_pipe' ], 10, 3 );
		add_action( 'save_post_room_type', [ $this, 'save_room_type_metabox' ], 10, 2 );
	}

	/**
	 * Add room type metabox.
	 *
	 * @param void $metabox metabox.
	 */
	public function add_room_type_metabox( $metabox ) {
		$extra_price = $metabox->add_section(
			'extra_price', array(
				'title' => esc_html__( 'Extra Price', 'awebooking-extra' ),
			)
		);

		$extra_price->add_field(
			array(
				'id'         => '__extra_price__',
				'type'       => 'title',
				'name'       => esc_html__( 'Extra Price', 'awebooking-extra' ),
				'show_on_cb' => [ $this, '_render_extra_price_callback' ],
			)
		);
	}

	/**
	 * Render extra price callback.
	 *
	 * @return void
	 */
	public function _render_extra_price_callback( $field ) {
		global $post;
		$room_type = new Room_Type( $post->ID );

		if ( ! $room_type->get_max_adults() && ! $room_type->get_max_children() ) : ?>
			<p style="padding: 0 15px;"><?php esc_html_e( 'Allow extra capacity fields is not set for Adults or Children.', 'awebooking-extra' ); ?></p>
			<div id="awebooking-extra-price"></div>
		<?php
		else :
			include trailingslashit( __DIR__ ) . 'views/html-room-type-extra-price.php';
		endif;
	}

	/**
	 * Save room type metabox.
	 *
	 * @param  Room_Type $room_type AweBooking\Hotel\Room_Type
	 * @param  boolean   $update    true/false
	 */
	public function save_room_type_metabox( $post_id, $post ) {
		// If this is just a revision, don't do anything.
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		$room_type = new Room_Type( $post_id );

		// For Adults.
		if ( isset( $_POST['max_adults'] ) ) {
			$max_adults = $room_type->get_max_adults();
			$this->handle_extra_price_fields_input( $room_type, $max_adults, 'adults' );
		}

		// For Children.
		if ( isset( $_POST['max_children'] ) ) {
			$max_children = $room_type->get_max_children();
			$this->handle_extra_price_fields_input( $room_type, $max_children, 'children' );
		}
	}

	/**
	 * Handle extra price fields input.
	 *
	 * @param  Room_Type $room_type  AweBooking\Hotel\Room_Type
	 * @param  int       $max_people max people
	 * @param  string    $prefix     prefix meta key.
	 */
	public function handle_extra_price_fields_input( Room_Type $room_type, $max_people, $prefix = 'adults' ) {
		$new_max_people = intval( $_POST[ 'max_' . $prefix ] );

		if ( isset( $_POST[ $prefix . '_extra_price_type' ] ) ) {
			$room_type->update_meta( $prefix . '_extra_price_type', $_POST[ $prefix . '_extra_price_type' ] );
		}

		if ( isset( $_POST[ $prefix . '_extra_fixed_amount' ] ) ) {
			$amount = awebooking_sanitize_price( $_POST[ $prefix . '_extra_fixed_amount' ] );
			$room_type->update_meta( $prefix . '_extra_fixed_amount', $amount );
		}

		if ( isset( $_POST[ $prefix . '_extra_foreach_amount' ] ) && is_array( $_POST[ $prefix . '_extra_foreach_amount' ] ) ) {
			$amount = $_POST[ $prefix . '_extra_foreach_amount' ];
			// Validate data.
			$amount = array_map(
				function( $value ) {
						$value['size']  = (int) $value['size'];
						$value['value'] = awebooking_sanitize_price( $value['value'] );
						return $value;
				}, $amount
			);

			// Ensure qty of foreach amount input = qty of new max people.
			if ( $new_max_people < $max_people ) {
				array_splice( $amount, $new_max_people );
			}
			$room_type->update_meta( $prefix . '_extra_foreach_amount', $amount );
		}

		if ( isset( $_POST[ $prefix . '_extra_upto_amount' ] ) && is_array( $_POST[ $prefix . '_extra_upto_amount' ] ) ) {
			$upto_value = $_POST[ $prefix . '_extra_upto_amount' ];
			// Validate data.
			$upto_value = array_map(
				function( $value ) {
						$value['size']  = (int) $value['size'];
						$value['value'] = awebooking_sanitize_price( $value['value'] );
						return $value;
				}, $upto_value
			);
			// Ensure qty of upto amount input = qty of new max people.
			if ( $new_max_people < $max_people ) {
				array_splice( $upto_value, $new_max_people );
			}
			// Validate discrete data.
			if ( is_array( $upto_value ) ) {
				$amount = [];
				foreach ( $upto_value as $key => $value ) {
					if ( ! $value['value'] ) {
						break;
					}
					$amount[] = [
						'size'  => $value['size'],
						'value' => $value['value'],
					];
				}
			}

			$room_type->update_meta( $prefix . '_extra_upto_amount', $amount );
		}
	}

	/**
	 * Add extra price pipe.
	 *
	 * @param Request      $request      Request
	 * @param Availability $availability Availability
	 *
	 * @return  array
	 */
	public function add_extra_price_pipe( $pipes, Request $request, Availability $availability ) {
		$pipes[] = new Extra_Price_Calculator( $availability->get_room_type(), $request );

		return $pipes;
	}
}
