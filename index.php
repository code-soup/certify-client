<?php

defined('WPINC') || die;

/**
 * Plugin Name: Certify Client
 * Plugin URI: https://github.com/code-soup/certify-client
 * Description: Client side PHP class for verifying license issued by Certify WordPress plugin.
 * Version: 0.0.1
 * Requires at least: 6.0
 * Requires PHP: 8.2
 * Author: Code Soup
 * Author URI: https://www.codesoup.co
 * License: GPLv3
 * Text Domain: certify-client
 */

register_activation_hook( __FILE__, function() {

    // On activate do this
    \CodeSoup\CertifyClient\Activator::activate();
});

register_deactivation_hook( __FILE__, function () {
    
    // On deactivate do that
    \CodeSoup\CertifyClient\Deactivator::deactivate();
});

include "run.php";