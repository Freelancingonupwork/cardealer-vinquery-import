<?php
/**
 * Plugin Name: Car Dealer - VIN Vehicle Import
 * Plugin URI:  http://www.potenzaglobalsolutions.com/
 * Description: This addon provide option to import vehicle using VIN.
 * Version:     2.0.0
 * Author:      Potenza Global Solutions
 * Author URI:  https://themeforest.net/item/car-dealer-automotive-responsive-wordpress-theme/20213334
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: cdvqi-addon
 *
 * @package VIN Vehicle Import
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Define PLUGIN_FILE.
if ( ! defined( 'CDVQI_PLUGIN_FILE' ) ) {
	define( 'CDVQI_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'CDVQI_VERSION' ) ) {
	define( 'CDVQI_VERSION', '2.0.0' );
}

define( 'CDVQI_PATH', plugin_dir_path( __FILE__ ) );
define( 'CDVQI_URL', plugin_dir_url( __FILE__ ) );

if ( is_admin() ) {
	require plugin_dir_path( __FILE__ ) . 'init.php';
	require plugin_dir_path( __FILE__ ) . 'admin-ajax.php';

	include_once dirname( __FILE__ ) . '/classes/class-cdvqi.php';
	include_once dirname( __FILE__ ) . '/classes/class-cardealer-vinquery-import.php';
}
