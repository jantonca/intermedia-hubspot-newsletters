<?php
/**
 * The WordPress posts API REST Controller for newsletters
 *
 * @link       https://www.intermedia.com.au/
 * @since      1.0.0
 *
 * @package    Intermedia_Hubspot_Newsletters
 * @subpackage Intermedia_Hubspot_Newsletters/admin
 */

/**
 * Class API REST posts controller
 *
 */
class WP_REST_Intermedia_newsletters_Posts {

	/**
	 * Posts in the positions endpoint
	 *
	 * 
	 * @return WP_REST_Response.
	 */
	public static function posts_endpoint() {

		$posts_ids = WP_REST_Intermedia_newsletters_Posts::get_posts_ids_entities();

		if( $posts_ids ){
			
			$entities_crops = [];

			$entities = Intermedia_newsletters_Entities::create_entities_positions();
			$image_crop = 'full';
			foreach ( $entities as $key => $value ) {
				
				$entities_crops[$key]= $value['crop'];
	
			}

			$posts = [];

			foreach ( $posts_ids['positions_ids'] as $position => $post_id ) {
	
				$data = [
					'id'             		=> $post_id,
					'newsletter_position' => $position,
					'type' => get_post_type( $post_id ),
					'title' => get_the_title( $post_id ),
					'permalink' => get_permalink( $post_id ),
					'excerpt' => get_the_excerpt( $post_id ),
					'image_source' => get_the_post_thumbnail_url( $post_id, $image_crop ),
				];
				$add_ons = [];
				$posts[] = array_merge( $data, $add_ons );
			}
	
		} else {
			$posts = 'No posts assigned to any position.';
		}

		return new \WP_REST_Response( $posts );

	}

	/**
	 * Positions endpoint
	 *
	 * 
	 * @return WP_REST_Response.
	 */
	public static function positions_endpoint() {

		$entities_options = get_option('intermedia_hubspot_newsletters_newsletters_settings');
        $cpt_included = $entities_options['cpt_included'];

		$output = array(
			'amount_positions' => count( self::get_newsletter_positions() ),
			'positions_name' => self::get_newsletter_positions(),
			'post_types' => $cpt_included,
			'posts_ids' => self::get_posts_ids()[0],
			'positions_ids' => self::get_posts_ids()[1],
		);

		return new \WP_REST_Response( $output );

	}

	/**
	 * Positions endpoint
	 *
	 * 
	 * @return WP_REST_Response.
	 */
	public static function import_csv_config_endpoint() {

		$output = json_decode(
			file_get_contents( __DIR__ . '/../json_config.json' ),
			true
		);

		return new \WP_REST_Response( $output );

	}

	/**
    * Get all newsletter positions
    *
    * @since 1.11.0
    * @access public
    */
	public static function get_newsletter_positions() {

		$all_positions = [];
	
		for ( $x = 1; $x <= get_option('amount_newsletters'); $x++ ):
	
		$name_newsletter_raw = get_option('name_newsletter_'.$x);
		$name_newsletter = str_replace(' ', '_', $name_newsletter_raw);
		$name_newsletter = strtolower($name_newsletter);
		$name_newsletter = preg_replace( '/[^A-Za-z0-9\-]/', '', $name_newsletter );
	
			for ( $y = 1; $y <= get_option('positions_newsletter_'.$x); $y++ ):
	
				array_push( $all_positions, $name_newsletter.'_position_'.$y );
	
			endfor;
	
		endfor;
	
		return $all_positions;
	}

	public static function select_get_post_types() {

        $post_types = get_post_types(

            array(
                'public'       => true,
                'show_in_rest' => true,
            ),
            'objects'
			
        );

		$posts = array();

		foreach ($post_types as $post_type) {

			$posts[$post_type->name] = $post_type->labels->singular_name;

		}

		return $posts;

	}

	/**
    * Get Posts ids.
    *
    * @since 1.11.0
    * @access public
    */
	public static function get_posts_ids() {

		$entities_options = get_option('intermedia_hubspot_newsletters_newsletters_settings');
        $cpt_included = $entities_options['cpt_included'];

		$positions = self::get_newsletter_positions();
		$posts_ids = [];
		$positions_ids = [];
		foreach ( $positions as $position ) {
			$args = array(
				'posts_per_page' => 1, // this gets all posts, you may only want to get a few at a time
				'post_type' => $cpt_included,
				'meta_key' => 'intermedia_newsletter_position',
				'meta_value' => $position,
				'fields' => 'ids'
			);
			$post_id = get_posts( $args );
			if( $post_id ) {
				array_push($posts_ids, $post_id[0]);
				array_push( $positions_ids, array( $position => $post_id[0] ) );
			}
		}
		$output = array( $posts_ids, $positions_ids );

		return $output;

	}

	/**
    * Get Posts data.
    *
    * @since 1.11.0
    * @access public
    */
	public static function get_posts_data( $post_type, $per_page, $posts_ids ) {

		$args     = [
			'post_type'           => $post_type,
			'post_status'         => 'publish',
			'posts_per_page'      => $per_page,
			'suppress_filters'    => false,
			'ignore_sticky_posts' => true,
			'has_password'        => false,
			'post__in'			  => $posts_ids,
			'orderby' 			  => 'post__in',
		];
		$query        = new WP_Query();
		$query_result = $query->query( $args );
		return $query_result;

	}

	/**
    * Get Posts ids with entities.
    *
    * @since 1.11.0
    * @access public
    */
	public static function get_posts_ids_entities() {

		$positions = Intermedia_newsletters_Entities::create_entities_positions();
		
		$posts_ids = [];
		$positions_ids = [];

		$current_site_entities_options = get_option('intermedia_hubspot_newsletters_newsletters_settings');
        
        if ( 
            isset( $current_site_entities_options['subsites_included'] ) && 
            !empty ( $current_site_entities_options['subsites_included'] ) &&
			isset( $current_site_entities_options['cpt_included']  ) && 
            !empty ( $current_site_entities_options['cpt_included']  )
        ) {
			
			if ( isset( $positions ) && !empty( $positions ) ) {

				foreach ( $positions as $position ) {

					foreach ( $current_site_entities_options['subsites_included'] as $subsite_id ) {
						switch_to_blog( (int) $subsite_id );
						$cpt_included = $current_site_entities_options['cpt_included'];
			
						$args = array(
							'posts_per_page' => -1, // this gets all posts, you may only want to get a few at a time
							'post_type' => $cpt_included,
							'meta_query' => array(
								array(
									'key' => 'entities_select_positions',
									'value' => $position,
									'compare' => 'LIKE'
								)
							),
							'fields' => 'ids'
						);
						$post_id = get_posts( $args );
						if( $post_id ) {

							$posts_ids[ $position ][$subsite_id] = $post_id;
							$positions_ids[ $position ] = $post_id[ 0 ];
			
						}
		
						restore_current_blog();
		
					}
		
				}
		
				$output = array( 
					'posts_ids' => $posts_ids, 
					'positions_ids' => $positions_ids 
				);

				return $output;
	
			}

		}
		
		if ( isset( $positions ) && !empty( $positions ) && isset( $current_site_entities_options['cpt_included'] ) && !empty ( $current_site_entities_options['cpt_included'] ) ) {

			foreach ( $positions as $position ) {

				$cpt_included = $current_site_entities_options['cpt_included'];
	
				$args = array(
					'posts_per_page' => 1, // this gets all posts, you may only want to get a few at a time
					'post_type' => $cpt_included,
					'meta_query' => array(
						array(
							'key' => 'entities_select_positions',
							'value' => $position,
							'compare' => 'LIKE'
						)
					),
					'fields' => 'ids'
				);
	
				$post_id = get_posts( $args );
	
				if( $post_id ) {
	
					array_push($posts_ids, $post_id[0]);
	
					$positions_ids[$position] = $post_id[0];
	
				}
	
			}
	
			$output = array( 
				'posts_ids' => $posts_ids, 
				'positions_ids' => $positions_ids 
			);

			return $output;

		}

	}

	/**
    * Get all crops creted in Posts.
    *
    * @since 1.11.0
    * @access public
    */
	public static function get_registered_crops_attachments() {
		
		$image_subsizes = array_keys( wp_get_registered_image_subsizes() );
		
		return $image_subsizes;
		
	}
	
}