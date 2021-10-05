<?php

/**
 * The settings of the plugin.
 *
 * @link       https://www.intermedia.com.au/
 * @since      1.0.0
 *
 * @package    Intermedia_Hubspot_Newsletters
 * @subpackage Intermedia_Hubspot_Newsletters/admin
 */

/**
 * Class WordPress_Plugin_Template_Settings
 *
 */

class Intermedia_Hubspot_Newsletters_Admin_Settings {

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
	 * The slug of the settings page
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $settings_page_handle = 'intermedia_hubspot_newsletters_options';
	/**
	 * The name for the options names and sections
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $hubspot_settings = 'intermedia_hubspot_newsletters_hubspot_settings';
	private $hubspot_section = 'hubspot_settings';
	private $newsletters_settings = 'intermedia_hubspot_newsletters_newsletters_settings';
	private $newsletters_section = 'newsletters_settings';

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * This function introduces the theme options into the 'Appearance' menu and into a top-level
	 * 'Intermedia HubSpot newsletters' menu.
	 */
	public function setup_plugin_options_menu() {

		//Add the menu to the Plugins set of menu items
		add_plugins_page(
			'Intermedia HubSpot Options', 						// The title to be displayed in the browser window for this page.
			'Intermedia HubSpot Options',						// The text to be displayed for this menu item
			'manage_options',									// Which type of users can see this menu item
			'intermedia_hubspot_newsletters_options',			// The unique ID - that is, the slug - for this menu item
			array( $this, 'render_settings_page_content')		// The name of the function to call when rendering this menu's page
		);

	}

	/**
	 * Provides default values for the Input Options.
	 *
	 * @return array
	 */
	public function default_hubspot_settings() {

		$defaults = array(
			'hapikey'		=>	'',
			'hubdb' => false,
		);

		return $defaults;

	}

	/**
	 * Provides default values for the Input Options.
	 *
	 * @return array
	 */
	public function default_newsletters_settings() {

		$defaults = array(
			'site_prefix'	=>	'intermedia',
			'tribe_events_date_format' => 'd M, Y'
		);

		return $defaults;

	}


	/**
	 * Renders a simple page to display for the theme menu defined above.
	 */
	public function render_settings_page_content( $active_tab = '' ) {

		$hubspot_settings = get_option( $this->hubspot_settings );

		?>
		<!-- Create a header in the default WordPress 'wrap' container -->
		<div class="wrap">

			<h2><?php _e( 'Intermedia HubSpot Newsletters', esc_html( $this->plugin_name ) ); ?></h2>
			<?php settings_errors(); ?>

			<?php 
				if( isset( $_GET[ 'tab' ] ) ) {

					$active_tab = sanitize_text_field( $_GET[ 'tab' ] );
				
				} else if( $active_tab == 'hubspot_table' ) {
					$active_tab = 'hubspot_table';
				} else if( $active_tab == 'newsletters_settings' ) {
					$active_tab = 'newsletters_settings';
				} else if( $active_tab == 'newsletters_entities' ) {
					$active_tab = 'newsletters_entities';
				} else {
					$active_tab = 'hubspot_settings';
				}
			?>

			<?php echo wp_kses( $this->api_admin_notices_action(), wp_kses_allowed_html( 'post' ) ); ?>

			<h2 class="nav-tab-wrapper">
				<a href="<?php echo '?page='.esc_attr( $this->settings_page_handle ).'&tab=hubspot_settings' ?>" class="nav-tab <?php echo $active_tab == 'hubspot_settings' ? 'nav-tab-active' : ''; ?>"><?php _e( 'HubSpot Settings', esc_html( $this->plugin_name ) ); ?></a>
				<?php if ( Intermedia_HubDB_Actions::verify_hubspot_api() === true && isset( $hubspot_settings[ 'hubdb'] ) && $hubspot_settings[ 'hubdb'] !== 'default'  ): ?>
					<a href="<?php echo '?page='.esc_attr( $this->settings_page_handle ).'&tab=hubspot_table' ?>" class="nav-tab <?php echo $active_tab == 'hubspot_table' ? 'nav-tab-active' : ''; ?>"><?php _e( 'HubSpot Table: '.esc_html( $hubspot_settings[ 'hubdb'] ) , esc_html( $this->plugin_name )); ?></a>
				<?php endif; ?>
				<a href="<?php echo '?page='.esc_attr( $this->settings_page_handle ).'&tab=newsletters_settings' ?>" class="nav-tab <?php echo $active_tab == 'newsletters_settings' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Newsletters Settings', esc_html( $this->plugin_name ) ); ?></a>
				<a href="<?php echo '?page='.esc_attr( $this->settings_page_handle ).'&tab=newsletters_entities' ?>" class="nav-tab <?php echo $active_tab == 'newsletters_entities' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Newsletters Entities', esc_html( $this->plugin_name ) ); ?></a>
			</h2>
			<?php if( $active_tab == 'hubspot_settings' ): ?>	
				<form method="post" action="options.php">
					<?php

					if( $active_tab == 'hubspot_settings' ) {

						settings_fields( 'intermedia_hubspot_newsletters_hubspot_settings' );
						do_settings_sections( 'intermedia_hubspot_newsletters_hubspot_settings' );

					}

					submit_button();

					?>
				</form>
			<?php endif; ?>

			<?php if( $active_tab == 'hubspot_table' ): ?>

				<?php echo wp_kses( Intermedia_HubDB_Actions::display_table_specs( $hubspot_settings[ 'hapikey'], Intermedia_HubDB_Actions::get_hubspot_id(), $hubspot_settings[ 'hubdb'] ), wp_kses_allowed_html( 'post' ) ); ?>

				<?php echo  Intermedia_HubDB_Actions::display_table_content( $hubspot_settings[ 'hapikey'], Intermedia_HubDB_Actions::get_hubspot_id(), $hubspot_settings[ 'hubdb'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped  ?>

				<form method="post">
					<?php wp_nonce_field( 'update_table_rows', 'update_table_rows_nonce' ); ?>
					<p class="submit"><input type="submit" name="update_table_rows" class="button button-primary" value="Update rows" /></p>
				</form>
				<?php

					if( array_key_exists( 'update_table_rows', $_POST ) && isset( $_POST['update_table_rows_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['update_table_rows_nonce'] ), 'update_table_rows' ) ) {

                        Intermedia_HubDB_Actions::update_table_rows( $hubspot_settings[ 'hapikey'], Intermedia_HubDB_Actions::get_hubspot_id(), $hubspot_settings[ 'hubdb'] );

                    }

					if( array_key_exists( 'update_table_row', $_POST ) && isset( $_POST['update_table_row'] ) ) {

						$row_id_position = array_map( 'sanitize_text_field', $_POST['update_table_row'] );
	
                        Intermedia_HubDB_Actions::update_table_row( $hubspot_settings[ 'hapikey'], Intermedia_HubDB_Actions::get_hubspot_id(), $hubspot_settings[ 'hubdb'], $row_id_position );

                    }

				?>

			<?php endif; ?>

			<?php if( $active_tab == 'newsletters_settings' ): ?>
				
				<form method="post" action="options.php">
					<?php

					if( $active_tab == 'newsletters_settings' ) {

						settings_fields( 'intermedia_hubspot_newsletters_newsletters_settings' );
						do_settings_sections( 'intermedia_hubspot_newsletters_newsletters_settings' );

					}

					submit_button();

					?>
				</form>

			<?php endif; ?>

			<?php if( $active_tab == 'newsletters_entities' ): ?>

				<?php echo wp_kses( self::display_table_entities(), wp_kses_allowed_html( 'post' ) ); ?>
				<?php echo wp_kses( self::display_table_entities_positions( WP_REST_Intermedia_newsletters_Posts::get_posts_ids_entities() ), wp_kses_allowed_html( 'post' ) ); ?>
				<?php echo self::display_table_entities_positions_sites( WP_REST_Intermedia_newsletters_Posts::get_posts_ids_entities() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

				<?php if ( current_user_can( 'manage_options' ) ): ?>

				<form method="post">
					<p class="submit"><input type="submit" name="delete_all_entities_metadata" class="button button-primary" value="Remove positions" /></p>
				</form>
				<?php
				
					if( array_key_exists( 'delete_all_entities_metadata', $_POST ) ) {

						echo wp_kses( $this->delete_all_entities_metadata(), wp_kses_allowed_html( 'post' ) );

                    }

				?>
				<?php 
				
					if( array_key_exists( 'remove_position_post', $_POST ) ) {

						echo self::remove_position_from_post( $_POST['remove_position_post'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

					}
				
				?>

				<?php endif; ?>

			<?php endif; ?>

		</div><!-- /.wrap -->

	<?php
	}

	/**
	 * This function provides a simple description for the Input Examples page.
	 *
	 * It's called from the 'initialize_intermedia_hubspot_newsletters_hubspot_settings' function by being passed as a parameter
	 * in the add_settings_section function.
	 */
	public function intermedia_hubspot_newsletters_hubspot_settings_callback() {

		echo '<p>' . esc_html( __( 'Settings for connecting to HubSpot throught an API KEY and select the hubspot_table to work with.', $this->plugin_name ) ) . '</p>';

	} // end general_options_callback

	/**
	 * This function provides a simple description for the Input Examples page.
	 *
	 * It's called from the 'initialize_intermedia_hubspot_newsletters_hubspot_settings' function by being passed as a parameter
	 * in the add_settings_section function.
	 */
	public function intermedia_hubspot_newsletters_newsletters_settings_callback() {

		echo '<p>' . esc_html( __( 'Settings for managing newsletters content.', $this->plugin_name ) ) . '</p>';

	} // end general_options_callback

	/**
	 * Initializes the HubSpot options by registering the Sections,
	 * Fields, and Settings.
	 *
	 * This function is registered with the 'admin_init' hook.
	 */
	public function initialize_intermedia_hubspot_newsletters_hubspot_settings() {

		if( false == get_option( $this->hubspot_settings ) ) {
			$default_array = $this->default_hubspot_settings();
			update_option( $this->hubspot_settings, $default_array );
		} // end if

		$values = get_option( $this->hubspot_settings );

		add_settings_section(
			$this->hubspot_section,
			__( 'HubSpot settings', $this->plugin_name ),
			array( $this, 'intermedia_hubspot_newsletters_hubspot_settings_callback'),
			$this->hubspot_settings
		);
		add_settings_field(
			'Hubspot API Key',
			__( 'Hubspot API Key', $this->plugin_name ),
			array( $this, 'input_render_callback'),
			$this->hubspot_settings,  										// menu slug, see t5_sae_add_options_page()
			$this->hubspot_section,
			array (
				'label_for'   => 'hapikey', 						// makes the field name clickable,
				'name'        => 'hapikey', 				// value for 'name' attribute
				'value'       => esc_attr( $values['hapikey'] ),
				'option_name' => $this->hubspot_settings,
				'type' 		  => 'password',
				'attribute'   => '',
				'classes'	  => 'regular-text'
			)
		);
		if ( Intermedia_HubDB_Actions::verify_hubspot_api() === true ) {

			add_settings_field(
				'Portal ID (Read only)',
				__( 'Portal ID (Read only)', $this->plugin_name ),
				array( $this, 'input_render_callback'),
				$this->hubspot_settings,  		// menu slug, see t5_sae_add_options_page()
				$this->hubspot_section,
				array (
					'label_for'   => 'portalId', 	// makes the field name clickable,
					'name'        => 'portalId', 	// value for 'name' attribute
					'value'       => esc_attr( Intermedia_HubDB_Actions::get_hubspot_id() ),
					'option_name' => $this->hubspot_settings,
					'type' 		  => 'text',
					'attribute'   => 'disabled',
					'classes'	  => 'regular-text'
				)
			);
			$hubdb_tables = Intermedia_HubDB_Actions::get_hubdb_tables();
			add_settings_field(
				'Select the HubDB',
				__( 'Select the database from HubSpot.', $this->plugin_name ),
				array( $this, 'select_render_callback'),
				$this->hubspot_settings,		// menu slug, see t5_sae_add_options_page()
				$this->hubspot_section,
				array (
					'label_for'   => 'hubdb', 	// makes the field name clickable,
					'name'        => 'hubdb', 	// value for 'name' attribute
					'value'       => !isset( $values['hubdb'] ) ? '' : $values['hubdb'],
					'options'     => $hubdb_tables,
					'option_name' => $this->hubspot_settings
				)
			);

		}
		register_setting(
			$this->hubspot_settings,
			$this->hubspot_settings,
			array( $this, 'validate_input_examples')
		);

	}

	/**
	 * Initializes the HubSpot options by registering the Sections,
	 * Fields, and Settings.
	 *
	 * This function is registered with the 'admin_init' hook.
	 */
	public function initialize_intermedia_hubspot_newsletters_newsletters_settings() {

		if( false == get_option( $this->newsletters_settings ) ) {

			$default_array = $this->default_newsletters_settings();

			update_option( $this->newsletters_settings, $default_array );

		} // end if

		$values = get_option( $this->newsletters_settings );

		$cpt_registered = WP_REST_Intermedia_newsletters_Posts::select_get_post_types();
		$subsites = self::select_get_blog_sites();

		add_settings_section(
			$this->newsletters_section,
			__( 'Newsletters settings', $this->plugin_name ),
			array( $this, 'intermedia_hubspot_newsletters_newsletters_settings_callback'),
			$this->newsletters_settings
		);

		add_settings_field(
			'Site prefix',
			__( 'Site prefix', $this->plugin_name ),
			array( $this, 'input_render_callback'),
			$this->newsletters_settings,  						// menu slug, see t5_sae_add_options_page()
			$this->newsletters_section,
			array (
				'label_for'   => 'site_prefix', 				// makes the field name clickable,
				'name'        => 'site_prefix', 				// value for 'name' attribute
				'value'       => esc_attr( $values['site_prefix'] ),
				'option_name' => $this->newsletters_settings,
				'type' 		  => 'text',
				'attribute'   => '',
				'classes'	  => 'regular-text'
			)
		);

		add_settings_field(
			'CPT included',
			__( 'Select the CPT that are going to be included.', $this->plugin_name ),
			array( $this, 'multi_select_render_callback'),
			$this->newsletters_settings,  			// menu slug, see t5_sae_add_options_page()
			$this->newsletters_section,
			array (
				'label_for'   => 'cpt_included', 	// makes the field name clickable,
				'name'        => 'cpt_included', 	// value for 'name' attribute
				'value'       => !isset( $values['cpt_included'] ) ? '' : $values['cpt_included'],
				'options'     => $cpt_registered,
				'option_name' => $this->newsletters_settings,
			)
		);

		add_settings_field(
			'Subsites included in entities query.',
			__( 'Select the subsites (including the current) that are going to be included in the entities query.', $this->plugin_name ),
			array( $this, 'multi_select_render_callback'),
			$this->newsletters_settings,  			// menu slug, see t5_sae_add_options_page()
			$this->newsletters_section,
			array (
				'label_for'   => 'subsites_included', 	// makes the field name clickable,
				'name'        => 'subsites_included', 	// value for 'name' attribute
				'value'       => !isset( $values['subsites_included'] ) ? '' : $values['subsites_included'],
				'options'     => $subsites,
				'option_name' => $this->newsletters_settings,
			)
		);

		if( isset( $values['cpt_included'] ) && in_array( 'tribe_events', $values['cpt_included'] ) ){
			add_settings_field(
				'Tribe Events date format',
				__( 'Tribe Events date format', $this->plugin_name ),
				array( $this, 'input_render_callback'),
				$this->newsletters_settings,  						// menu slug, see t5_sae_add_options_page()
				$this->newsletters_section,
				array (
					'label_for'   => 'tribe_events_date_format', 				// makes the field name clickable,
					'name'        => 'tribe_events_date_format', 				// value for 'name' attribute
					'value'       => !isset( $values['tribe_events_date_format'] ) ? 'd M, Y' : $values['tribe_events_date_format'],
					'option_name' => $this->newsletters_settings,
					'type' 		  => 'text',
					'attribute'   => '',
					'classes'	  => 'regular-text'
				)
			);
		}

		add_settings_field(
			'Number of newsletters entities',
			__( 'Number of newsletters entities', $this->plugin_name ),
			array( $this, 'input_render_callback'),
			$this->newsletters_settings,  						// menu slug, see t5_sae_add_options_page()
			$this->newsletters_section,
			array (
				'label_for'   => 'newsletters_entities_number', 				// makes the field name clickable,
				'name'        => 'newsletters_entities_number', 				// value for 'name' attribute
				'value'       => esc_attr( !isset( $values['newsletters_entities_number'] ) ? '' : $values['newsletters_entities_number'] ),
				'option_name' => $this->newsletters_settings,
				'type' 		  => 'number',
				'attribute'   => 'step="1" min="1"',
				'classes'	  => 'small-text'

			)
		);

		$registered_crops = WP_REST_Intermedia_newsletters_Posts::get_registered_crops_attachments();

		if( isset( $values['newsletters_entities_number'] ) && !empty( $values['newsletters_entities_number'] ) ){

			$newsletters_entities_number = (int) $values['newsletters_entities_number'];
			
			for ( $i=0; $i < $newsletters_entities_number; $i++ ) {
		
				add_settings_field(
					'Newsletter entity name '.$i,
					__( 'Newsletter entity name '.$i, $this->plugin_name ),
					array( $this, 'newsletters_entities_render_callback'),
					$this->newsletters_settings,  						// menu slug, see t5_sae_add_options_page()
					$this->newsletters_section,
					array (
						'label_for'   			=> 'newsletter_entity_name_'.$i, 				// makes the field name clickable,
						'name'        			=> 'newsletters_entities', 				// value for 'name' attribute
						'value'       			=> !isset( $values['newsletters_entities'][$i]['name'] ) ? '' : $values['newsletters_entities'][$i]['name'],
						'option_name' 			=> $this->newsletters_settings,
						'type' 		  			=> 'text',
						'attribute'  	 		=> '',
						'classes'	  			=> 'regular-text',
						'amount_label'			=> 'newsletter_entity_amount_'.$i, 				// makes the field name clickable,
						'amount_name'        	=> 'newsletters_entities', 				// value for 'name' attribute
						'amount_value'      	=> !isset( $values['newsletters_entities'][$i]['amount'] ) ? '' : $values['newsletters_entities'][$i]['amount'],
						'amount_option_name' 	=> $this->newsletters_settings,
						'amount_type' 			=> 'number',
						'amount_attribute'   	=> 'step="1" min="1"',
						'amount_classes'	  	=> 'small-text',
						'crop_label'			=> 'newsletter_entity_crop_'.$i, 				// makes the field name clickable, 15
						'crop_name'       		=> 'newsletters_entities', 				// value for 'name' attribute 16
						'crop_value'       		=> !isset( $values['newsletters_entities'][$i]['crop'] ) ? '' : $values['newsletters_entities'][$i]['crop'], // 17
						'crop_option_name' 		=> $this->newsletters_settings, //18
						'crop_classes'	  		=> 'regular-text', //19
						'crop_options' 			=> $registered_crops, //20
						'increment'				=> $i
					)
				);
	
			}

		} 

		register_setting(
			$this->newsletters_settings,
			$this->newsletters_settings,
			array( $this, 'validate_entities_settings')
		);
	}

	public function input_render_callback ( $args ) {

		printf(
			'<input type="%5$s" name="%1$s[%2$s]" id="%3$s" value="%4$s" class="%7$s" %6$s >',
			esc_html( $args['option_name'] ),
			esc_html( $args['name'] ),
			esc_html( $args['label_for'] ),
			esc_html( $args['value'] ),
			esc_html( $args['type'] ),
			esc_html( $args['attribute'] ),
			esc_html( $args['classes'] ),
		);

	}// end input_element_callback

	public function newsletters_entities_render_callback ( $args ) {

		printf(
			'<input type="%5$s" name="%1$s[%2$s][%20$s][name]" id="%3$s" value="%4$s" class="%7$s" %6$s>
			<label style="margin-left: 1rem;margin-right: 1rem;" for="%10$s">Entities number</label>
			<input type="%12$s" name="%8$s[%9$s][%20$s][amount]" id="%10$s" value="%11$s" class="%14$s" %13$s>
			<label style="margin-left: 1rem;margin-right: 1rem;" for="%17$s">Entities crop</label>
			<select class="%19$s" name="%15$s[%16$s][%20$s][crop]" id="%17$s">',
			esc_html( $args['option_name']),
			esc_html( $args['name'] ),
			esc_html( $args['label_for'] ),
			esc_html( $args['value'] ),
			esc_html( $args['type']),
			esc_html( $args['attribute'] ),
			esc_html( $args['classes'] ),
			esc_html( $args['amount_option_name'] ),
			esc_html( $args['amount_name'] ),
			esc_html( $args['amount_label'] ),
			esc_html( $args['amount_value'] ),
			esc_html( $args['amount_type'] ),
			esc_html( $args['amount_attribute'] ),
			esc_html( $args['amount_classes'] ),
			esc_html( $args['crop_option_name'] ),
			esc_html( $args['crop_name'] ),
			esc_html( $args['crop_label'] ),
			esc_html( $args['crop_value']),
			esc_html( $args['crop_classes'] ),
			esc_html( $args['increment'] ),
		);
		foreach ( $args['crop_options'] as $val => $title ) {
			printf(
				'<option value="%3$s" %2$s>%3$s</option>',
				esc_html( $val ),
				selected( $title, $args['crop_value'], FALSE ),
				esc_html( $title )
			);
		}

		print '</select>';

	}// end newsletters_entities_render_callback

	public function select_render_callback( $args ) {

		printf(
			'<select %3$s class="regular-text" name="%1$s[%2$s]" id="%3$s">',
			esc_html( $args['option_name'] ),
			esc_html( $args['name'] ),
			esc_html( $args['label_for'] ),
		);

		foreach ( $args['options'] as $val => $title ) {
			printf(
				'<option value="%1$s" %2$s>%3$s</option>',
				esc_html( $val ),
				selected( $val, $args['value'], FALSE ),
				esc_html( $title )
			);
		}

		print '</select>';

	}// end select_render_callback

	public function multi_select_render_callback( $args ) {

		printf(
			'<select multiple="multiple" class="custom-select-posts-entities regular-text" name="%1$s[%2$s][]" id="%3$s">',
			esc_html( $args['option_name'] ),
			esc_html( $args['name'] ),
			esc_html( $args['label_for'] ),
		);

		foreach ( $args['options'] as $val => $title ) {

			printf(
				'<option value="%1$s" %2$s>%3$s</option>',
				esc_html( $val ),
				selected( is_array( $args['value'] ) && in_array( $val, $args['value'] ), TRUE ),
				esc_html( $title )
			);
		}

		print '</select>';

	}// end select_render_callback

	public function validate_input_examples( $input ) {

		// Create our array for storing the validated options
		$output = array();
		// Loop through each of the incoming options
		foreach( $input as $key => $value ) {

			// Check to see if the current option has a value. If so, process it.
			if( isset( $input[$key] ) && $input[$key] ) {

				// Strip all HTML and PHP tags and properly handle quoted strings
				$output[$key] = wp_strip_all_tags( stripslashes( $input[ $key ] ) );

			} // end if

		} // end foreach

		// Return the array processing any additional functions filtered by this action
		return apply_filters( 'validate_input_examples', $output, $input );

	} // end validate_input_examples

	public function validate_entities_settings( $input ) {

		// Create our array for storing the validated options
		$output = array();
		// Loop through each of the incoming options
		foreach( $input as $key => $value ) {

			// Check to see if the current option has a value. If so, process it.
			if( isset( $input[$key] ) && !is_array( $value ) ) {

				// Strip all HTML and PHP tags and properly handle quoted strings
				$output[$key] = wp_strip_all_tags( stripslashes( $input[ $key ] ) );

			} else {

				$output[$key] = $input[ $key ];

			} // end if

		} // end foreach

		// Return the array processing any additional functions filtered by this action
		return apply_filters( 'validate_input_examples', $output, $input );

	} // end validate_input_examples

	/**
	 * Displays all messages registered in the API
	*/
	protected function api_admin_notices_action() {

		$api_verification = Intermedia_HubDB_Actions::verify_hubspot_api();

		ob_start(); ?>

		<?php if ( $api_verification === false ): ?>

			<div class="notice notice-warning"><p><strong>You must provide a Hubspot api key or token.</strong></p></div>
		
		<?php elseif( $api_verification === true ): ?>

			<div class="notice notice-success"><p><strong>The API key has been verified!!</strong></p></div>

		<?php else: ?>

			<div class="notice notice-error"><p><strong><?php echo esc_html( $api_verification ); ?></strong></p></div>

		<?php endif; ?>

		<?php if ( is_multisite() ): ?>

			<div class="notice notice-info"><p>This is a multisite configuration. The current Blog (subsite) ID: <strong><?php echo esc_html( get_current_blog_id() ); ?></strong></p></div>
			
		<?php endif; ?>

		<?php return ob_get_clean();
	}
	/**
	 * Displays the table for the entities configuration
	*/
	public static function display_table_entities() {

		$entities_options = get_option('intermedia_hubspot_newsletters_newsletters_settings');

		ob_start();

		?>

		<h2>Newsletters Entities<?php if( is_multisite()): echo ' (current blog)'; endif ?></h2>

		<?php if ( isset( $entities_options['newsletters_entities'] ) && !empty( $entities_options['newsletters_entities'] ) ): ?>

			<table class="wp-list-table widefat fixed striped table-view-list">
				<thead>
					<tr>
						<th>Entity name</th>
						<th>Amount of entities</th>
						<th>Entity crop</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $entities_options['newsletters_entities'] as $key => $value ): ?>
						<tr>
							<td><?php echo esc_html( $value['name']); ?></td>
							<td><?php echo esc_html( $value['amount'] ); ?></td>
							<td><?php echo esc_html( $value['crop'] ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

		<?php else: ?>

			<p>No entities have been created yet.</p>

		<?php endif; ?>

		<?php return ob_get_clean();
	
	}
	/**
	 * Displays all positions with posts assigned
	*/
	public static function display_table_entities_positions( $entities ) {

		$options = get_option('intermedia_hubspot_newsletters_newsletters_settings');

		ob_start();

		?>

		<h2>Current positions and posts<?php if( is_multisite()): echo ' (including subsites)'; endif ?></h2>

		<?php if ( isset( $entities ) && !empty( $entities['positions_ids'] ) ): ?>

			<table class="wp-list-table widefat fixed striped table-view-list">
				<thead>
					<tr>
						<th>Entity name</th>
						<th>Post ID</th>
						<?php if( is_multisite()): ?><th>Site ID</th><?php endif ?>
						<th>Post title</th>
					</tr>
				</thead>
				<tbody>
					<?php $i = 1; foreach ( $entities['positions_ids'] as $key => $value ): ?>
						<tr>
						<?php if ( isset( $options['subsites_included'] ) && !empty ( $options['subsites_included'] ) ): ?>
							<?php foreach ( $options['subsites_included'] as $subsite_id ): ?>
								<?php switch_to_blog( (int) $subsite_id ); ?>

								<?php $entities_select_positions = get_post_meta( $value, 'entities_select_positions', true ); ?>
								<?php if(  in_array( (int) $value, $entities['positions_ids']) &&  get_post_status( (int) $value ) !== 'publish'  ) :  restore_current_blog(); continue; endif ?>
								<?php if(  !is_array( $entities_select_positions ) || !in_array( $key, $entities_select_positions )  ) : restore_current_blog(); continue; endif ?>
								<td><?php echo esc_html( $key ); ?></td>
								<td><?php echo (int) $value; ?></td>
								<td><?php echo (int) $subsite_id; ?></td>
								<td><?php echo esc_html( get_the_title( (int) $value ) ); ?></td>
								<?php restore_current_blog(); ?>

							<?php endforeach ?>

						<?php else: ?>
							<td><?php echo esc_html( $key ); ?></td>
							<td><?php echo esc_html( $value ); ?></td>
							<td><?php echo esc_html( get_the_title( (int) $value ) ); ?></td>
						<?php endif ?>
						</tr>
					<?php $i++; endforeach; ?>
				</tbody>
			</table>
		<?php else: ?>

			<p>No entities positions assigned yet to any posts or CPT.</p>

		<?php endif; ?>

		<?php return ob_get_clean();
	
	}

	/**
	 * Displays all positions with all posts assigned to any subsite
	*/
	public static function display_table_entities_positions_sites ( $entities ) {

		$options = get_option('intermedia_hubspot_newsletters_newsletters_settings');

		ob_start();

		?>

		<h2>Total positions and posts</h2>

		<?php if ( isset( $entities ) && !empty( $entities['posts_ids'] ) && isset( $options['cpt_included'] ) && !empty( $options['cpt_included'] )   ): ?>

			<table class="wp-list-table widefat fixed striped table-view-list">

				<thead>

					<tr>
						<th>Entity name</th>
						<th>Post ID</th>
						<th>Site ID</th>
						<th>Post title</th>
						<th></th>
					</tr>
					
				</thead>

					<?php foreach( $entities['posts_ids'] as $position => $positions ): ?>
						
						<?php foreach( $positions as $subsite => $subsite_posts ): ?>
						
							<?php foreach( $subsite_posts as $post_id ): ?>
								<tr>
									<td><?php echo esc_html( $position ); ?></td>
									<td><?php echo esc_html( $post_id ); ?></td>
									<td><?php echo esc_html( $subsite ); ?></td>
									<?php switch_to_blog( (int) $subsite ); ?>
									<td><?php echo esc_html( get_the_title( (int) $post_id ) ); ?></td>
									<?php restore_current_blog(); ?>
									<td data-colname="col-action" class="col-action" >
										<form method="post">
											<input type="hidden" name="<?php echo esc_attr( 'remove_position_post['.$subsite.']['.$position.']' ); ?>"; class="button button-secondary" value="<?php echo esc_attr( $post_id ); ?>" />
											<p class="submit">
												<input style="display: block;" type="submit" class="button button-secondary" value="Remove position from post" />
											</p>
										</form>
									</td>
								</tr>
							<?php endforeach; ?>		

						<?php endforeach; ?>
						
					<?php endforeach; ?>

				<tbody>

				</tbody>

			</table>

		<?php else: ?>

			<p>No entities positions assigned yet to any posts or CPT.</p>

		<?php endif; ?>

		<?php return ob_get_clean();
	
	}
	/**
	 * Delete all the entities metadata in posts.
	*/
	public function delete_all_entities_metadata() {

		$current_site_entities_options = get_option('intermedia_hubspot_newsletters_newsletters_settings');
		
		if ( isset( $current_site_entities_options['subsites_included'] ) && !empty ( $current_site_entities_options['subsites_included'] ) ) {

			$entities = Intermedia_newsletters_Entities::create_entities_positions();

			foreach ( $entities as $entity ) {

				foreach ($current_site_entities_options['subsites_included'] as $subsite_id ){

					switch_to_blog( (int) $subsite_id );

					$args = array(
						'posts_per_page' 		=> -1, // this gets all posts, you may only want to get a few at a time
						'post_type' 			=> $current_site_entities_options['cpt_included'],
						'meta_query' => array(
							array(
									'key'     	=> 'entities_select_positions',
									'value'  	=> serialize( $entity ),
									'compare' 	=> 'LIKE'
								)
							),
						'fields' 			  	=> 'ids'
					);

					$post_with_positions = get_posts( $args );

					if ( $post_with_positions ) {

						$total_entities[$entity] = array(
							'blog_id_'.$subsite_id => $post_with_positions
						);

						foreach ( $post_with_positions as $post_id ) {

							$post_positions = get_post_meta( $post_id, 'entities_select_positions', true );

							if ( ( $post_position_key = array_search( $entity, $post_positions ) ) !== false ) {

								unset( $post_positions[ $post_position_key ] );
								update_post_meta( $post_id, 'entities_select_positions', $post_positions );

							}
			
							echo '<div class="notice notice-success"><p>Removed entity position: <strong>'.esc_html( $entity ).'</strong> from post ID: <strong>'.esc_html( $post_id ).'</strong> in the Blog ID: <strong>'.esc_html( $subsite_id ).'</strong></p></div>';
			
						}

					}

					restore_current_blog();

				}
				
			}

		} else {

			$cpt_included = $current_site_entities_options['cpt_included'];
	
			$args = array(
				'posts_per_page' 	=> -1, // this gets all posts, you may only want to get a few at a time
				'post_type' 		=> $cpt_included,
				'meta_key' 			=> 'entities_select_positions',
				'fields' 			=> 'ids'
			);
	
			$post_with_positions = get_posts( $args );
	
			if ( count( $post_with_positions ) > 0 && !empty( $post_with_positions ) ) {
	
				foreach ($post_with_positions as $post_id) {
	
					delete_post_meta($post_id, 'entities_select_positions');
	
					echo '<div class="notice notice-success"><p>Removed entities positions from post ID: <strong>'.esc_html( $post_id ).'</strong></p></div>';
	
				}
	
			} else {
	
				echo '<div class="notice notice-warning"><p>There are no posts assigned to any entity.</p></div>';
	
			}

		}
        
	}

	public static function select_get_blog_sites() {

		$subsites = get_sites();

		foreach ( $subsites as $subsite ) {

			$subsite_id = get_object_vars($subsite)["blog_id"];
			$subsite_name = get_blog_details($subsite_id)->blogname;
			$select_sites[ $subsite_id ] = $subsite_name;

		}

		return $select_sites;

	}

	public static function get_all_posts_positions() {

		$current_site_entities_options = get_option('intermedia_hubspot_newsletters_newsletters_settings');
		
		if ( isset( $current_site_entities_options['subsites_included'] ) && !empty ( $current_site_entities_options['subsites_included'] ) ) {

			$entities = Intermedia_newsletters_Entities::create_entities_positions();

			foreach ( $entities as $entity ) {

				foreach ($current_site_entities_options['subsites_included'] as $subsite_id ){

					switch_to_blog( (int) $subsite_id );

					$args = array(
						'posts_per_page' 		=> -1, // this gets all posts, you may only want to get a few at a time
						'post_type' 			=> $current_site_entities_options['cpt_included'],
						'meta_query' => array(
							array(
									'key'     	=> 'entities_select_positions',
									'value'  	=> serialize( $entity ),
									'compare' 	=> 'LIKE'
								)
							),
						'fields' 			  	=> 'ids'
					);

					$post_with_positions = get_posts( $args );

					if ( $post_with_positions ) {

						$total_entities[$entity] = array(
							'blog_id_'.$subsite_id => $post_with_positions
						);

						foreach ( $post_with_positions as $post_id ) {
							
							$xxx = get_post_meta( $post_id, 'entities_select_positions', true );
			
						}

					}

					restore_current_blog();

				}
				

			}

			return $xxx;

		} else {

			$cpt_included = $current_site_entities_options['cpt_included'];
	
			$args = array(
				'posts_per_page' 	=> -1, // this gets all posts, you may only want to get a few at a time
				'post_type' 		=> $cpt_included,
				'meta_key' 			=> 'entities_select_positions',
				'fields' 			=> 'ids'
			);
	
			$post_with_positions = get_posts( $args );
	
			if ( count( $post_with_positions ) > 0 && !empty( $post_with_positions ) ) {
	
				return $post_with_positions;
	
			}

		}
	}

	public static function remove_position_from_post( $post_data ) {

		foreach ( $post_data as $subsite => $post) {

			switch_to_blog( (int) $subsite );

			foreach ( $post as $position => $post_id ) {

				$post_positions = get_post_meta( $post_id, 'entities_select_positions', true );

				if ( ( $post_position_key = array_search( $position, $post_positions ) ) !== false ) {

					unset( $post_positions[ $post_position_key ] );
					update_post_meta( $post_id, 'entities_select_positions', $post_positions );

				}

				echo '<div class="notice notice-success"><p>Removed position (id: <b>'.esc_html( $position ).'</b>) from post id: <b>'.esc_html( $post_id ).'</b> in the subsite id: <b>'.esc_html( $subsite ).'</b></p></div>';


			}

			restore_current_blog();

		}

	}

}