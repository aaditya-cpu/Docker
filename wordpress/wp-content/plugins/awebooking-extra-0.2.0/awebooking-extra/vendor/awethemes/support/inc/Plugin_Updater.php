<?php
namespace Awethemes\Support;

/**
 * Creates the AweThemes API connection.
 *
 * @link https://code.tutsplus.com/tutorials/a-guide-to-the-wordpress-http-api-automatic-plugin-updates--wp-25181
 */
class Plugin_Updater {
	/* Constants */
	const LATEST_URI   = 'https://update.awethemes.com/latest/{plugin}.json';
	const DOWNLOAD_URI = 'https://update.awethemes.com/download/{plugin}.zip';

	/**
	 * The plugin name (slug name, eg: awebooking).
	 *
	 * @var string
	 */
	protected $plugin;

	/**
	 * The plugin base name (eg: awebooking/awebooking.php).
	 *
	 * @var string
	 */
	protected $basename;

	/**
	 * Current plugin version.
	 *
	 * @var string
	 */
	protected $current_version;

	/**
	 * Constructor.
	 *
	 * @param string $plugin          Plugin name/slug.
	 * @param string $basename        Plugin basename, @see plugin_basename().
	 * @param string $current_version Current plugin version.
	 */
	public function __construct( $plugin, $basename, $current_version ) {
		$this->plugin   = $plugin;
		$this->basename = $basename;
		$this->current_version = $current_version;
	}

	/**
	 * Hooks in to WP.
	 *
	 * @return void
	 */
	public function hooks() {
		add_filter( 'plugins_api', array( $this, 'get_infomation' ), 10, 3 );
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_update' ) );
	}

	/**
	 * Check for Updates at the defined API endpoint and modify the update array.
	 *
	 * This function dives into the update API just when WordPress creates its update array,
	 * then adds a custom API call and injects the custom plugin data retrieved from the API.
	 * It is reassembled from parts of the native WordPress plugin update code.
	 * See wp-includes/update.php line 272 for the original wp_update_plugins() function.
	 *
	 * @param  array $transient Update array build by WordPress.
	 * @return array
	 */
	public function check_update( $transient ) {
		// Prevent if transient checked is empty.
		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		// Get latest version of awebooking.
		$remote_version = $this->request(
			str_replace( '{plugin}', $this->plugin, static::LATEST_URI )
		);

		if ( isset( $remote_version['latest'] ) && version_compare( $this->current_version, $remote_version['latest'], '<' ) ) {
			$transient->response[ $this->basename ] = (object) array(
				'slug'        => $this->plugin,
				'plugin'      => $this->basename,
				'new_version' => $remote_version['latest'],
				'package'     => str_replace( '{plugin}', $this->plugin, static::DOWNLOAD_URI ),
			);
		}

		return $transient;
	}

	/**
	 * Add awebooking response for infomation request.
	 *
	 * @param false|object $result The result data.
	 * @param string       $action The type of information being requested from the Plugin Install API.
	 * @param object       $args   Plugin API arguments.
	 * @return string
	 */
	public function get_infomation( $result, $action, $args ) {
		if ( 'plugin_information' !== $action ) {
			return $result;
		}

		if ( $args->slug !== $this->basename ) {
			return $result;
		}

		// TODO: ...
		return $result;
	}

	/**
	 * Request to remote and retrieve json response.
	 *
	 * @param  string $url URL to retrieve.
	 * @return array|false
	 */
	protected function request( $url ) {
		$response = wp_remote_get( $url );

		if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			return false;
		}

		return json_decode( wp_remote_retrieve_body( $response ), true );
	}
}
