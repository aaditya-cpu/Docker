<?php
namespace AweBooking\Gallery;

class Service_Provider extends \AweBooking\Support\Service_Provider {
	/**
	 * Registers services on the plugin.
	 *
	 * @access private
	 */
	public function register() {
		$this->plugin->singleton( Admin_Setting::class );
		$this->plugin->tag( Admin_Setting::class, 'setting.appearance' );
	}

	/**
	 * Init service provider.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_filter( 'abrs_get_template', [ $this, 'overwrite_template' ], 10, 2 );

		if ( 'on' === abrs_get_option( 'display_gallery_on_single', 'on' ) ) {
			remove_action( 'abrs_single_room_sections', 'abrs_single_room_gallery', 20 );
			add_action( 'abrs_single_room_sections', function() {
				include __DIR__ . '/views/template-gallery.php';
			}, 20 );
		}
	}

	/**
	 * Custom overwrite template.
	 *
	 * @param  string $located       Located template.
	 * @param  string $template_name The template name.
	 * @return string
	 */
	public function overwrite_template( $located, $template_name ) {
		if ( 'search/result/room-type.php' === $template_name ) {
			return __DIR__ . '/views/template-room-type.php';
		}

		return $located;
	}

	/**
	 * Enqueue scripts.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$plugin_url = trailingslashit( AWEBOOKING_GALLERY_PLUGIN_URL );

		wp_register_style( 'photoswipe', $plugin_url . 'assets/vendor/photoswipe/photoswipe.css', [], '4.1.2' );
		wp_register_style( 'photoswipe-default-skin', $plugin_url . 'assets/vendor/photoswipe/default-skin/default-skin.css', [ 'photoswipe' ], '4.1.2' );
		wp_register_script( 'photoswipe', $plugin_url . 'assets/vendor/photoswipe/photoswipe' . $suffix . '.js', [], '4.1.2', true );
		wp_register_script( 'photoswipe-ui-default', $plugin_url . 'assets/vendor/photoswipe/photoswipe-ui-default' . $suffix . '.js', [ 'photoswipe' ], '4.1.2', true );

		wp_register_style( 'awebooking-photo-gallery', $plugin_url . 'assets/css/photo-gallery' . $suffix . '.css', [ 'photoswipe', 'photoswipe-default-skin' ], AWEBOOKING_GALLERY_VERSION );
		wp_register_script( 'awebooking-photo-gallery', $plugin_url . 'assets/js/photo-gallery' . $suffix . '.js', [ 'awebooking', 'photoswipe-ui-default' ], AWEBOOKING_GALLERY_VERSION, true );

		if ( abrs_is_search_page() || abrs_is_room_type() ) {
			// Enqueue the photo gallery.
			wp_enqueue_style( 'awebooking-photo-gallery' );
			wp_enqueue_script( 'awebooking-photo-gallery' );

			wp_localize_script( 'awebooking-photo-gallery', '_awebookingGallery', [
				'displayOnSearch' => 'on' === abrs_get_option( 'display_gallery_on_search', 'on' ),
				'displayOnSingle' => 'on' === abrs_get_option( 'display_gallery_on_single', 'on' ),
			]);

			// Print the photoswipe layout in the footer.
			add_action( 'wp_footer', function() {
				include __DIR__ . '/views/photoswipe.php';
			});
		}
	}
}
