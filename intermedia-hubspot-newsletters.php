<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.intermedia.com.au/
 * @since             1.0.1
 * @package           Intermedia_Hubspot_Newsletters
 *
 * @wordpress-plugin
 * Plugin Name:       Intermedia HubSpot Newsletters
 * Plugin URI:        https://www.intermedia.com.au/
 * Description:       This is a plugin to connect the WordPress site to Hubspot plattform and populate Databases for newsletters purposes.
 * Version:           1.0.1
 * Author:            Jose Anton
 * Author URI:        https://www.intermedia.com.au/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       intermedia-hubspot-newsletters
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'INTERMEDIA_HUBSPOT_NEWSLETTERS_VERSION', '1.0.0' );

// =============================================
// Define Constants
// =============================================
if ( ! defined( 'INTERMEDIA_BASE_PATH' ) ) {
	define( 'INTERMEDIA_BASE_PATH', __FILE__ );
}
if ( ! defined( 'INTERMEDIA_PLUGIN_DIR' ) ) {
	define( 'INTERMEDIA_PLUGIN_DIR', untrailingslashit( dirname( INTERMEDIA_BASE_PATH ) ) );
}
// =============================================
// Set autoload
// =============================================
require_once INTERMEDIA_PLUGIN_DIR . '/vendor/autoload.php';

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-intermedia-hubspot-newsletters-activator.php
 */
function activate_intermedia_hubspot_newsletters() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-intermedia-hubspot-newsletters-activator.php';
	Intermedia_Hubspot_Newsletters_Activator::activate();
}
/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-intermedia-hubspot-newsletters-deactivator.php
 */
function deactivate_intermedia_hubspot_newsletters() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-intermedia-hubspot-newsletters-deactivator.php';
	Intermedia_Hubspot_Newsletters_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_intermedia_hubspot_newsletters' );
register_deactivation_hook( __FILE__, 'deactivate_intermedia_hubspot_newsletters' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-intermedia-hubspot-newsletters.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_intermedia_hubspot_newsletters() {

	$plugin = new Intermedia_Hubspot_Newsletters();
	$plugin->run();

}
run_intermedia_hubspot_newsletters();