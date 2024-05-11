<?php

namespace CodeSoup\CertifyClient;

/**
 * @file
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 */
final class Init {

    /**
     * Main plugin instance
     */
    private static $instance;


    /**
     * Plugin Settings
     */
    private static $params;

    /**
     * Make constructor protected, to prevent direct instantiation
     *
     * @since    1.0.0
     */
    protected function __construct() {}


    /**
     * Main Instance.
     *
     * Ensures only one instance is loaded or can be loaded.
     *
     * @since 1.0
     * @static
     * @return Main instance.
     */
    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    
    /**
     * Singletons should not be cloneable.
     */
    private function __clone()
    {
        throw new \Exception('Cannot clone ' . __CLASS__);
    }

    
    /**
     * Singletons should not be restorable from strings.
     */
    public function __wakeup()
    {
        throw new \Exception('Cannot unserialize ' . __CLASS__);
    }



    /**
     * Run everything on init
     * @return void
     */
    public function init( array $args )
    {
        self::$params = wp_parse_args( $args, array(
            'plugin_id'             => '',
            'plugin_version'        => '',
            'cache_allowed'         => false,
            'certify_server_origin' => '',
            'license_key'           => '',
        ));

        /**
         * Plugin Update class
         */
        $updater = new Updater(self::$params);

        add_filter('site_transient_update_plugins', [$updater, 'site_transient_update_plugins'] );
        add_filter('plugins_api', [$updater, 'fetch_plugin_info'], 20, 3 );

        add_action('upgrader_process_complete', [$updater, 'upgrader_process_complete'], 10, 2);
    }



    /**
     * Method to validate License Key with Certify Server
     */
    public function validate()
    {
        $response = wp_remote_get(
            sprintf('%s/wp-json/certify/v1/validate', $this->get_param('certify_server_origin')),
            array(
                'timeout' => 30,
                'headers' => array(
                    'Accept' => 'application/json'
                ),
                'body' => array(
                    'license_key' => sanitize_text_field( self::$params['license_key'] )
                ),
            )
        );

        if (
            is_wp_error( $response )
            || 200 !== wp_remote_retrieve_response_code( $response )
            || empty( wp_remote_retrieve_body( $response ) )
        ) {
            // Log to error_log
            if ( defined('WP_DEBUG') )
            {
                error_log( print_r($response, true) );
            }
            return new \WP_Error('verification-failed', 'Licesnse verification failed');
        }

        // Return response from Certify Server
        return wp_remote_retrieve_body( $response );
    }


    /**
     * Get single param
     */
    private function get_param( string $key )
    {
        return self::$params[ $key ] ?? '';
    }
}