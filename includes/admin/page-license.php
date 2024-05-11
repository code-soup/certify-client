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
class PageLicense {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		add_action( 'admin_menu', array($this, 'admin_menu') );
		add_action( 'admin_init', array($this, 'settings_init') );
	}

	/**
	 * Register Options page
	 */
	public function admin_menu() {
		add_menu_page(
			'Certify Client',
			'Certify Client',
			'manage_options',
			'certify_client',
			array($this, 'render_page')
		);
	}


	/**
	 * Register Custom Options using Settings API
	 */
	public function settings_init()
	{
		// Add Settings
		register_setting('certify_client', 'certify_client_options');
		
		// Add Section
		add_settings_section(
			'certify_client_section',
			'Certify Client Settings',
			NULL,
			'certify_client'
		);

		// Add Field
		add_settings_field(
			'license_key',
			'License Key',
			array($this, 'render_field'),
			'certify_client',
			'certify_client_section'
		);
	}

	
	/**
	 * HTML Markup for options page
	 */
	public function render_page()
	{
		include 'options/page-license.php';
	}

	
	/**
	 * All registered options fields
	 */
	public function render_field()
	{
		$options = get_option('certify_client_options');
		$parsed  = wp_parse_args( $options, [
			'license_key' => '',
		]);

		printf(
			'<input type="text" name="certify_client_options[license_key]" class="regular-text" value="%s" />',
			$parsed['license_key']
		);
	}
}
