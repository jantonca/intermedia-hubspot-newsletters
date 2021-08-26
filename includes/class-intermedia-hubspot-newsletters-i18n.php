<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://www.intermedia.com.au/
 * @since      1.0.0
 *
 * @package    Intermedia_Hubspot_Newsletters
 * @subpackage Intermedia_Hubspot_Newsletters/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Intermedia_Hubspot_Newsletters
 * @subpackage Intermedia_Hubspot_Newsletters/includes
 * @author     Jose Anton <Janton@intermedia.com.au>
 */
class Intermedia_Hubspot_Newsletters_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'intermedia-hubspot-newsletters',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
