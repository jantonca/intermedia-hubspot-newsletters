<?php
/**
 * The WordPress API REST Controller
 *
 * @link       https://www.intermedia.com.au/
 * @since      1.0.0
 *
 * @package    Intermedia_Hubspot_Newsletters
 * @subpackage Intermedia_Hubspot_Newsletters/admin
 */

/**
 * Class Hubspot_Connection
 *
 */
class WP_REST_Intermedia_newsletters extends WP_REST_Controller {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Attribute schema.
	 *
	 * @var array
	 */
	public $attribute_schema;

	/**
	 * Constructs the controller.
	 *
	 * @access public
	 */
	public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->namespace = $plugin_name.'/v1';
		$this->rest_base = 'newsletters';
	}
    /**
	 * Registers the necessary REST API routes.
	 *
	 * @access public
	 */
	public function register_routes() {

		// Endpoint to get articles on the front-end.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_items' ],
					'args'                => [],
					'permission_callback' => '__return_true',
				],
			]
		);

		// Endpoint to get articles in the editor, in regular/query mode.
		register_rest_route(
			$this->namespace,
			'/posts-positions',
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ 'WP_REST_Intermedia_newsletters_Posts', 'posts_endpoint' ],
				'args'                => [
					'author'         => [
						'type'    => 'array',
						'items'   => array(
							'type' => 'integer',
						),
						'default' => array(),
					],
					'categories'     => [
						'type'    => 'array',
						'items'   => array(
							'type' => 'integer',
						),
						'default' => array(),
					],
					'excerpt_length' => [
						'type'    => 'integer',
						'default' => 55,
					],
					'include'        => [
						'type'    => 'array',
						'items'   => array(
							'type' => 'integer',
						),
						'default' => array(),
					],
					'orderby'        => [
						'sanitize_callback' => 'sanitize_text_field',
					],
					'per_page'       => [
						'sanitize_callback' => 'absint',
					],
					'show_excerpt'   => [
						'type' => 'boolean',
					],
					'tags'           => [
						'type'    => 'array',
						'items'   => array(
							'type' => 'integer',
						),
						'default' => array(),
					],
					'tags_exclude'   => [
						'type'    => 'array',
						'items'   => array(
							'type' => 'integer',
						),
						'default' => array(),
					],
					'post_type'      => [
						'type'    => 'array',
						'items'   => array(
							'type' => 'string',
						),
						'default' => array(),
					],
				],
				'permission_callback' => '__return_true',
			]
		);

		// Endpoint to get positions created for newsletters
		register_rest_route(
			$this->namespace,
			'/entities-posts-positions',
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ 'Intermedia_newsletters_Entities', 'create_entities_positions' ],
				'args'                => [],
				'permission_callback' => '__return_true',
			]
		);

		// Endpoint to get positions created for newsletters
		register_rest_route(
			$this->namespace,
			'/newsletters-settings',
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_newsletters_settings' ],
				'args'                => [],
				'permission_callback' => '__return_true',
			]
		);

    }
	/**
	 * Sets up and returns attribute schema.
	 *
	 * @return array
	 */
	public function get_attribute_schema() {
		if ( empty( $this->attribute_schema ) ) {
			$block_json = json_decode(
				file_get_contents( __DIR__ . '/../block/block.json' ), // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
				true
			);

			$this->attribute_schema = array_merge(
				$block_json['attributes'],
				[
					'exclude_ids' => [
						'type'    => 'array',
						'default' => [],
						'items'   => [
							'type' => 'integer',
						],
					],
				]
			);
		}
		return $this->attribute_schema;
	}

	public function get_newsletters_settings(){

		return get_option('intermedia_hubspot_newsletters_newsletters_settings');
	}

}