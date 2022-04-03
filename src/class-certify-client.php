<?php

namespace CodeSoup\CertifyClient;

// Exit if accessed directly
defined( 'WPINC' ) || die;


/**
 * @file
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 *
 * TODO:
 * - check for licence validity transient
 * -
 * 
 */
class CertifyClient {

    /**
     * IMPORTANT
     * 
     * This is the only thing you need to change in order to make this work.
     * Based on prefix all other required data is loaded from database
     * 
     * @var string
     */
    protected $prefix = '_certify_';

    
    /**
     * Version
     * 
     * Currently installed plugin version
     * This will be retrieved from wp_options table
     * 
     * @var string
     */
    protected $version;


    /**
     * Plugin slug
     * This is plugins' folder name 
     * 
     * @var string
     */
    protected $plugin_slug;

    
    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    public function __construct() {
        // Do something if required
    }

    /**
     * Validate License Key
     * 
     * @param   $request [description]
     * @return [type]                    [description]
     */
    public static function is_license_key_valid( array $args = array() ) : bool {

        $response = true;

        return $response;
    }
}


// add_filter( 'plugins_api', 'get_plugin_info', 20, 3);
