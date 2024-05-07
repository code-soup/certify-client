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

	use \CodeSoup\CertifyClient\Traits\HelpersTrait;

	// Main plugin instance.
	protected static $instance = null;

	
	// Assets loader class.
	protected $assets;


	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		// Main plugin instance.
		$instance     = \CodeSoup\CertifyClient\plugin_instance();
		$hooker       = $instance->get_hooker();

		// Admin hooks.
		$hooker->add_actions([
			['admin_menu', $this],
			['admin_init', $this, 'settings_init'],
		]);
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
			'licence_key',
			'Licence Key',
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
		include 'page-license/template.php';
	}

	
	/**
	 * All registered options fields
	 */
	public function render_field()
	{
		$options = get_option('certify_client_options');
		$parsed  = wp_parse_args( $options, [
			'licence_key' => '',
		]);

		printf(
			'<input type="text" name="certify_client_options[licence_key]" class="regular-text" value="%s" />',
			$parsed['licence_key']
		);
	}
}
