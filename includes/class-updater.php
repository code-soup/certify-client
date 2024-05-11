<?php

namespace CodeSoup\CertifyClient;

class Updater {

	public $plugin_id;
	public $plugin_version;
	public $cache_key;
	public $cache_allowed;
	public $certify_server_origin;
	public $license_key;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct( array $args ) {

		$params = wp_parse_args( $args, array(
			'plugin_id'             => '',
			'plugin_version'        => '',
			'cache_key'             => '',
			'cache_allowed'         => false,
			'certify_server_origin' => '',
			'license_key'           => '',
		));

		$this->plugin_id             = $params['plugin_id'];
		$this->plugin_version        = $params['plugin_version'];
		
		$this->cache_key             = sprintf('__cached_%s', sanitize_title($params['plugin_id']) );
		$this->cache_allowed         = $params['cache_allowed'];

		$this->certify_server_origin = $params['certify_server_origin'];
		$this->license_key           = $params['license_key'];
	}


	/**
	 * Get JSON
	 */
	public function request()
	{
		$remote = get_transient( $this->cache_key );

		if( false === $remote || ! $this->cache_allowed ) {

			$remote = wp_remote_get(
				$this->get_update_url(),
				array(
					'timeout' => 30,
					'headers' => array(
						'Accept' => 'application/json'
					),
					'body' => array(
						'license_key' => $this->license_key,
					),
				)
			);

			if(
				is_wp_error( $remote )
				|| 200 !== wp_remote_retrieve_response_code( $remote )
				|| empty( wp_remote_retrieve_body( $remote ) )
			) {
				return false;
			}

			set_transient( $this->cache_key, $remote, DAY_IN_SECONDS );
		}

		$response = json_decode( wp_remote_retrieve_body( $remote ) );
		$response->sections = (array) $response->sections;
		$response->banners  = (array) $response->banners;

		return $response;

	}


	/**
	 * Fetch plugin info screen from repository
	 */
	function fetch_plugin_info( $res, $action, $args ) {

		// do nothing if you're not getting plugin information right now
		if( 'plugin_information' !== $action ) {
			return $res;
		}

		// do nothing if it is not our plugin
		if( $this->plugin_id !== $args->slug ) {
			return $res;
		}

		// get updates
		$response = $this->request();

		if( empty( $response ) ) {
			return $res;
		}

		return $response;
	}



	public function site_transient_update_plugins( $transient )
	{
		if ( empty($transient->checked ) ) {
			return $transient;
		}

		$response = $this->request();

		if (
			$response
			&& version_compare( $this->plugin_version, $response->version, '<' )
			&& version_compare( $response->requires, get_bloginfo( 'version' ), '<=' )
			&& version_compare( $response->requires_php, PHP_VERSION, '<' )
		) {
			
			$res              = new \stdClass();
			$res->slug        = $response->slug;
			$res->plugin      = $response->path;
			$res->new_version = $response->version;
			$res->tested      = $response->tested;
			$res->package     = $response->download_url;

			$transient->response[ $response->path ] = (array) $response;
		}

		return $transient;
	}

	/**
	 * Do a clean up after plugin check is finished
	 *
	 * @link https://developer.wordpress.org/reference/hooks/upgrader_process_complete/
	 */
	public function upgrader_process_complete( $upgrader, $options )
	{
		if (
			$this->cache_allowed
			&& 'update' === $options['action']
			&& 'plugin' === $options[ 'type' ]
		) {
			// just clean the cache when new plugin version is installed
			delete_transient( $this->cache_key );
		}

	}


	public function get_update_url()
	{
		return sprintf(
			'%s/wp-json/certify/v1/plugin-update',
			untrailingslashit( $this->certify_server_origin )
		);
	}
}
