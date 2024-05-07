<?php

namespace CodeSoup\CertifyClient\Admin;

// Exit if accessed directly
defined( 'WPINC' ) || die;


/**
 * @file
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 */
class Updater {

	use \CodeSoup\CertifyClient\Traits\HelpersTrait;

	// Main plugin instance.
	protected static $instance = null;

	
	// Assets loader class.
	protected $assets;

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
	public function __construct() {

		// Main plugin instance.
		$instance     = \CodeSoup\CertifyClient\plugin_instance();
		$hooker       = $instance->get_hooker();
		$this->assets = $instance->get_assets();

		$this->plugin_slug        = $this->get_plugin_id();
		$this->plugin_path        = $this->get_plugin_id('/index.php');
		$this->version            = '1.0';
		$this->cache_key          = 'certify_client_update';
		$this->cache_allowed      = false;
		$this->certify_server_url = 'http://cs.zz/certify';

		$hooker->add_filters([
			['plugins_api', $this, 'fetch_plugin_info', 20, 3],
			['site_transient_update_plugins', $this],
		]);

		// Admin hooks.
		$hooker->add_actions([
			['upgrader_process_complete', $this, '', 10, 2]
		]);

		$this->request();
		$this->log('tu');
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

		$this->log( $this->get_update_url() );

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
