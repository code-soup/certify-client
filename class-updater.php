<?php

namespace CodeSoup\CertifyClient;

class Updater {

	public $plugin_slug;
	public $plugin_path;
	public $version;
	public $cache_key;
	public $cache_allowed;
	public $certify_server_url;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $args = []) {

		$params = wp_parse_args( $args, array(
			'plugin_slug'        => '',
			'plugin_path'        => '',
			'version'            => '',
			'cache_key'          => '',
			'cache_allowed'      => false,
			'certify_server_url' => '',
		));

		$this->plugin_slug        = $params['plugin_slug'];
		$this->plugin_path        = $params['plugin_slug'];
		$this->version            = $params['plugin_version'];
		$this->cache_key          = $params['cache_key'];
		$this->cache_allowed      = $params['cache_allowed'];
		$this->certify_server_url = $params['certify_server_url'];

		add_filter('site_transient_update_plugins', [$this, 'site_transient_update_plugins'] );
		add_filter('plugins_api', [$this, 'fetch_plugin_info'], 20, 3 );

		// Admin hooks.
		add_action('upgrader_process_complete', [$this, 'upgrader_process_complete'], 10, 2);

		$this->request();
	}


	/**
	 * Get JSON
	 */
	public function request()
	{
		$remote = get_transient( $this->cache_key );

		if( false === $remote || ! $this->cache_allowed ) {

			$remote = wp_remote_get(
				$this->certify_server_url,
				array(
					'timeout' => 30,
					'headers' => array(
						'Accept' => 'application/json'
					),
					'body' => array(
						'licence_key' => $this->get_license_key()
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

		$request = wp_remote_get(
			$this->get_update_url(),
			array(
				'timeout' => 30,
				'headers' => array(
					'Accept' => 'application/json'
				),
				'body' => array(
					'licence_key' => $this->get_license_key()
				),
			)
		);

		$remote = json_decode( wp_remote_retrieve_body( $remote ) );

		return $remote;

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
		if( $this->plugin_slug !== $args->slug ) {
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
			&& version_compare( $this->version, $response->version, '<' )
			&& version_compare( $response->requires, get_bloginfo( 'version' ), '<=' )
			&& version_compare( $response->requires_php, PHP_VERSION, '<' )
		) {
			$transient->response[ $response->plugin ] = $response;
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


	public function get_license_key()
	{
		$options = get_option('certify_client_options');
		$parsed  = wp_parse_args( $options, [
			'licence_key' => '',
		]);

		return $parsed['licence_key'];
	}



	public function get_update_url()
	{
		return path_join( $this->certify_server_url, 'wp-json/certify/v1/validate' );
	}
}
