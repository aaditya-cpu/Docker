<?php
namespace AweBooking\Gallery;

use AweBooking\Admin\Settings\Appearance_Setting;

class Admin_Setting {
	/**
	 * The settings instance.
	 *
	 * @var \AweBooking\Admin\Settings\Appearance_Setting
	 */
	protected $settings;

	/**
	 * Constructor.
	 *
	 * @param \AweBooking\Admin\Settings\Appearance_Setting $settings The settings instance.
	 */
	public function __construct( Appearance_Setting $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Register settings.
	 *
	 * @return void
	 */
	public function register() {
		$gallery = $this->settings->add_section( 'gallery', [
			'title' => esc_html__( 'Gallery', 'awebooking-gallery' )
		]);

		$gallery->add_field([
			'id'       => 'display_gallery_on_search',
			'type'     => 'abrs_toggle',
			'name'     => esc_html__( 'Display room gallery on search?', 'awebooking-gallery' ),
			'default'  => 'on',
		]);

		$gallery->add_field([
			'id'       => 'display_gallery_on_single',
			'type'     => 'abrs_toggle',
			'name'     => esc_html__( 'Display gallery on single room?', 'awebooking-gallery' ),
			'default'  => 'on',
		]);
	}
}
