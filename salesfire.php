<?php

/**
 * Plugin Name: Salesfire
 * Plugin URI: https://www.salesfire.co.uk
 * Description: Boost conversions with Salesfire CRO. Integrates with WooCommerce.
 * Version: 1.0.6
 * Developer: Salesfire
 * Developer URI: https://www.salesfire.co.uk
 * Text Domain: salesfire
 * Domain Path: /languages
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

include(__DIR__ . '/src/Settings.php');
include(__DIR__ . '/src/Tracking.php');

$settings = new Salesfire_Settings;
$tracking = new Salesfire_Tracking;

add_action( 'init', array( $tracking, 'init' ) );
add_action( 'admin_init', array ( $settings, 'init') );
add_action( 'admin_menu', array ($settings, 'init_page') );
