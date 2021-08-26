<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.intermedia.com.au/
 * @since      1.0.0
 *
 * @package    Intermedia_Hubspot_Newsletters
 * @subpackage Intermedia_Hubspot_Newsletters/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Intermedia_Hubspot_Newsletters
 * @subpackage Intermedia_Hubspot_Newsletters/includes
 * @author     Jose Anton <Janton@intermedia.com.au>
 */
class Intermedia_Hubspot_Newsletters {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Intermedia_Hubspot_Newsletters_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'INTERMEDIA_HUBSPOT_NEWSLETTERS_VERSION' ) ) {
			$this->version = INTERMEDIA_HUBSPOT_NEWSLETTERS_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'intermedia-hubspot-newsletters';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Intermedia_Hubspot_Newsletters_Loader. Orchestrates the hooks of the plugin.
	 * - Intermedia_Hubspot_Newsletters_i18n. Defines internationalization functionality.
	 * - Intermedia_Hubspot_Newsletters_Admin. Defines all hooks for the admin area.
	 * - Intermedia_Hubspot_Newsletters_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-intermedia-hubspot-newsletters-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-intermedia-hubspot-newsletters-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-intermedia-hubspot-newsletters-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-intermedia-hubspot-newsletters-public.php';

		$this->loader = new Intermedia_Hubspot_Newsletters_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Intermedia_Hubspot_Newsletters_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Intermedia_Hubspot_Newsletters_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Intermedia_Hubspot_Newsletters_Admin( $this->get_plugin_name(), $this->get_version() );
		$plugin_settings = new Intermedia_Hubspot_Newsletters_Admin_Settings( $this->get_plugin_name(), $this->get_version() );
		$articles_controller = new WP_REST_Intermedia_newsletters( $this->get_plugin_name(), $this->get_version() );
		$newsletters_entities = new Intermedia_newsletters_Entities( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->loader->add_action( 'admin_menu', $plugin_settings, 'setup_plugin_options_menu' );
		$this->loader->add_action( 'admin_init', $plugin_settings, 'initialize_intermedia_hubspot_newsletters_hubspot_settings' );
		$this->loader->add_action( 'admin_init', $plugin_settings, 'initialize_intermedia_hubspot_newsletters_newsletters_settings' );
		$this->loader->add_action( 'rest_api_init', $articles_controller, 'register_routes' );
		$this->loader->add_action( 'wp_ajax_reset_all_entities_metadata', $plugin_settings, 'delete_all_entities_metadata' );
		$this->loader->add_action( 'init', $newsletters_entities, 'setup_cpt_custom_column_entities', 20 );
		$this->loader->add_filter( 'request', $newsletters_entities, 'hits_column_orderby_entities' );
		// Add our text to the quick edit box
		$this->loader->add_action('quick_edit_custom_box', $newsletters_entities, 'entities_quick_edit_custom_box', 10, 2);
		$this->loader->add_action( 'admin_menu', $newsletters_entities, 'entities_add_metabox' );
		$this->loader->add_action( 'save_post', $newsletters_entities, 'entities_save_metaboxdata', 10, 2 );
		$this->loader->add_action( 'save_post', $newsletters_entities, 'entities_quick_edit_save' );
		$this->loader->add_action( 'rest_api_init', $newsletters_entities, 'register_post_positions_api' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Intermedia_Hubspot_Newsletters_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Intermedia_Hubspot_Newsletters_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
