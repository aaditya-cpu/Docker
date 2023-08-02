<?php
namespace AweBooking\Extra;

use AweBooking\AweBooking;
use AweBooking\Support\Addon;
use AweBooking\Admin\Admin_Settings;

class Extra_Addon extends Addon {
	/* Constants */
	const VERSION = '0.2.0';

	/**
	 * Requires minimum AweBooking version.
	 *
	 * @return string
	 */
	public function requires() {
		return '3.0.0-beta8';
	}

	/**
	 * Registers services on the awebooking.
	 *
	 * @return void
	 */
	public function register() {
		$this->awebooking->trigger( new Calendar\Calendar_Hooks );
		$this->awebooking->trigger( new Extra_Pricing\Extra_Pricing_Hooks );

		load_plugin_textdomain( 'awebooking-extra', false, dirname( $this->get_basename() ) . '/languages' );
	}

	/**
	 * Init the addon.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'awebooking/register_admin_scripts', [ $this, 'enqueue_admin_scripts' ] );
		add_action( 'awebooking/admin_settings/register', [ $this, '_admin_settings' ] );
	}

	/**
	 * Enqueue scripts
	 */
	public function enqueue_scripts() {
		wp_register_style( 'awebooking-calendar', plugin_dir_url( __DIR__ ) . 'assets/css/awebooking-calendar.css' );
		wp_register_script( 'awebooking-calendar', plugin_dir_url( __DIR__ ) . 'assets/js/awebooking-calendar.js', [ 'jquery' ] );

		if ( is_room_type() ) {
			wp_enqueue_style( 'awebooking-calendar' );
			wp_enqueue_script( 'awebooking-calendar' );
		}
	}

	/**
	 * Register admin scripts.
	 */
	public function enqueue_admin_scripts() {
		$screen = get_current_screen();

		wp_register_script( 'awebooking-extra-price', plugin_dir_url( __DIR__ ) . 'assets/js/admin/awebooking-extra-price.js', array( 'awebooking-admin' ), static::VERSION, true );

		if ( AweBooking::ROOM_TYPE === $screen->id ) {
			wp_enqueue_script( 'awebooking-extra-price' );

			/**
			 * Allow plugins/themes to filter the settings' JS data
			 *
			 * @param array $js_data JS Data.
			 */
			$js_data = apply_filters( 'awebooking/extra/extra_price', [
				'chargeType' => [
					''         => esc_html__( 'No extra charge', 'awebooking-extra' ),
					'fixed'    => esc_html__( 'Fixed surcharge fees', 'awebooking-extra' ),
					'foreach'  => esc_html__( 'For each', 'awebooking-extra' ),
					'upto'     => esc_html__( 'Up to', 'awebooking-extra' ),
				],
				'text' => [
					'descTypeNone'      => esc_html__( 'Default: Not additional fee for the extended capacity.', 'awebooking-extra' ),
					'descTypeFixed'     => esc_html__( 'Fixed surcharge fees for the extended capacity.', 'awebooking-extra' ),
					'descTypeMandatory' => esc_html__( 'Requirement to enter the amount for each extended person.', 'awebooking-extra' ),
					'descTypeOptional'  => esc_html__( 'Required amount of amount for each expand.', 'awebooking-extra' ),
					'add'               => esc_html__( 'Add', 'awebooking-extra' ),
				],
			]);

			wp_localize_script( 'awebooking-extra-price', 'awebookingExtraPrice', $js_data );
		}
	}

	/**
	 * Add admin settings.
	 *
	 * @param  Admin_Settings $admin_settings Admin_Settings instance.
	 */
	public function _admin_settings( Admin_Settings $admin_settings ) {
		$admin_settings->add_field( array(
			'id'       => '__breakdown_pricing__',
			'section'  => 'display',
			'type' => 'title',
			'name' => esc_html__( 'Breakdown Pricing', 'awebooking-extra' ),
			'priority' => 45,
		) );

		$admin_settings->add_field( array(
			'id'       => 'enable_breakdown_pricing',
			'section'  => 'display',
			'name' => esc_html__( 'Breakdown pricing?', 'awebooking-extra' ),
			'type'     => 'toggle',
			'default'  => awebooking( 'setting' )->get( 'enable_breakdown_pricing' ),
			'priority' => 50,
		) );
	}
}
