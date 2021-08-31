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

			<h2><?php _e( 'Intermedia HubSpot Newsletters', $this->plugin_name ); ?></h2>
			<?php settings_errors(); ?>

			<?php if( isset( $_GET[ 'tab' ] ) ) {
				$active_tab = $_GET[ 'tab' ];
			} else if( $active_tab == 'hubspot_table' ) {
				$active_tab = 'hubspot_table';
			} else if( $active_tab == 'newsletters_settings' ) {
				$active_tab = 'newsletters_settings';
			} else if( $active_tab == 'newsletters_entities' ) {
				$active_tab = 'newsletters_entities';
			} else {
				$active_tab = 'hubspot_settings';
			} // end if/else ?>

			<?php echo $this->api_admin_notices_action(); ?>

			<h2 class="nav-tab-wrapper">
				<a href="?page=<?php echo $this->settings_page_handle; ?>&tab=hubspot_settings" class="nav-tab <?php echo $active_tab == 'hubspot_settings' ? 'nav-tab-active' : ''; ?>"><?php _e( 'HubSpot Settings', $this->plugin_name ); ?></a>
				<?php if ( Intermedia_HubDB_Actions::verify_hubspot_api() === true && isset( $hubspot_settings[ 'hubdb'] ) && $hubspot_settings[ 'hubdb'] !== 'default'  ): ?>
					<a href="?page=<?php echo $this->settings_page_handle; ?>&tab=hubspot_table" class="nav-tab <?php echo $active_tab == 'hubspot_table' ? 'nav-tab-active' : ''; ?>"><?php _e( 'HubSpot Table: '.$hubspot_settings[ 'hubdb'] , $this->plugin_name ); ?></a>
				<?php endif; ?>
				<a href="?page=<?php echo $this->settings_page_handle; ?>&tab=newsletters_settings" class="nav-tab <?php echo $active_tab == 'newsletters_settings' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Newsletters Settings', $this->plugin_name ); ?></a>
				<a href="?page=<?php echo $this->settings_page_handle; ?>&tab=newsletters_entities" class="nav-tab <?php echo $active_tab == 'newsletters_entities' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Newsletters Entities', $this->plugin_name ); ?></a>
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

				<?php echo Intermedia_HubDB_Actions::display_table_specs( $hubspot_settings[ 'hapikey'], Intermedia_HubDB_Actions::get_hubspot_id(), $hubspot_settings[ 'hubdb'] ) ?>

				<?php echo Intermedia_HubDB_Actions::display_table_content( $hubspot_settings[ 'hapikey'], Intermedia_HubDB_Actions::get_hubspot_id(), $hubspot_settings[ 'hubdb'] ) ?>
				<form method="post">
					<p class="submit"><input type="submit" name="update_table_rows" class="button button-primary" value="Update rows" /></p>
				</form>
				<?php
				
					if( array_key_exists( 'update_table_rows', $_POST ) ) {

                        Intermedia_HubDB_Actions::update_table_rows( $hubspot_settings[ 'hapikey'], Intermedia_HubDB_Actions::get_hubspot_id(), $hubspot_settings[ 'hubdb'] );

                    }
					if( array_key_exists( 'update_table_row', $_POST ) ) {

                        Intermedia_HubDB_Actions::update_table_row( $hubspot_settings[ 'hapikey'], Intermedia_HubDB_Actions::get_hubspot_id(), $hubspot_settings[ 'hubdb'], $_POST['update_table_row'] );

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

				<?php echo self::display_table_entities();?>
				<?php echo self::display_table_entities_positions( WP_REST_Intermedia_newsletters_Posts::get_posts_ids_entities() );?>

				<?php if ( current_user_can( 'manage_options' ) ): ?>

				<form method="post">
					<p class="submit"><input type="submit" name="delete_all_entities_metadata" class="button button-primary" value="Remove positions" /></p>
				</form>
				<?php
				
					if( array_key_exists( 'delete_all_entities_metadata', $_POST ) ) {

                        echo $this->delete_all_entities_metadata();

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

		/* highlight_string("<?php \n\$hubspot_settings =\n" . var_export( get_option( $this->hubspot_settings ), true ) . ";\n ?>"); */

		echo '<p>' . __( 'Settings for connecting to HubSpot throught an API KEY and select the hubspot_table to work with.', $this->plugin_name ) . '</p>';

	} // end general_options_callback

	/**
	 * This function provides a simple description for the Input Examples page.
	 *
	 * It's called from the 'initialize_intermedia_hubspot_newsletters_hubspot_settings' function by being passed as a parameter
	 * in the add_settings_section function.
	 */
	public function intermedia_hubspot_newsletters_newsletters_settings_callback() {

/* 		highlight_string("<?php \n\$newsletters_settings =\n" . var_export( get_option( $this->newsletters_settings ), true ) . ";\n ?>");

		echo '<p>' . __( 'Settings for managing newsletters content.', $this->plugin_name ) . '</p>'; */

	} // end general_options_callback

	/**
	 * Initializes the HubSpot options by registering the Sections,
	 * Fields, and Settings.
	 *
	 * This function is registered with the 'admin_init' hook.
	 */
	public function initialize_intermedia_hubspot_newsletters_hubspot_settings() {

		//delete_option($this->hubspot_settings);

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
					'value'       => !isset( $values['hubdb'] ) ? '' : $values['hubdb'],//esc_attr( $values['hubdb'] )
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
				'value'       => esc_attr( $values['newsletters_entities_number'] ),
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
			$args['option_name'],
			$args['name'],
			$args['label_for'],
			$args['value'],
			$args['type'],
			$args['attribute'],
			$args['classes'],
		);

	}// end input_element_callback

	public function newsletters_entities_render_callback ( $args ) {

		printf(
			'<input type="%5$s" name="%1$s[%2$s][%20$s][name]" id="%3$s" value="%4$s" class="%7$s" %6$s>
			<label style="margin-left: 1rem;margin-right: 1rem;" for="%10$s">Entities number</label>
			<input type="%12$s" name="%8$s[%9$s][%20$s][amount]" id="%10$s" value="%11$s" class="%14$s" %13$s>
			<label style="margin-left: 1rem;margin-right: 1rem;" for="%17$s">Entities crop</label>
			<select class="%19$s" name="%15$s[%16$s][%20$s][crop]" id="%17$s">',
			$args['option_name'],
			$args['name'],
			$args['label_for'],
			$args['value'],
			$args['type'],
			$args['attribute'],
			$args['classes'],
			$args['amount_option_name'],
			$args['amount_name'],
			$args['amount_label'],
			$args['amount_value'],
			$args['amount_type'],
			$args['amount_attribute'],
			$args['amount_classes'],
			$args['crop_option_name'],
			$args['crop_name'],
			$args['crop_label'],
			$args['crop_value'],
			$args['crop_classes'],
			$args['increment'],
		);
		foreach ( $args['crop_options'] as $val => $title ) {
			printf(
				'<option value="%3$s" %2$s>%3$s</option>',
				$val,
				selected( $title, $args['crop_value'], FALSE ),
				$title
			);
		}

		print '</select>';

	}// end newsletters_entities_render_callback

	public function select_render_callback( $args ) {

		printf(
			'<select %3$s class="regular-text" name="%1$s[%2$s]" id="%3$s">',
			$args['option_name'],
			$args['name'],
			$args['label_for'],
		);

		foreach ( $args['options'] as $val => $title ) {
			printf(
				'<option value="%1$s" %2$s>%3$s</option>',
				$val,
				selected( $val, $args['value'], FALSE ),
				$title
			);
		}

		print '</select>';

	}// end select_render_callback

	public function multi_select_render_callback( $args ) {

		printf(
			'<select multiple="multiple" class="custom-select-posts-entities regular-text" name="%1$s[%2$s][]" id="%3$s">',
			$args['option_name'],
			$args['name'],
			$args['label_for'],
		);

		foreach ( $args['options'] as $val => $title ) {

			printf(
				'<option value="%1$s" %2$s>%3$s</option>',
				$val,
				selected( is_array( $args['value'] ) && in_array( $val, $args['value'] ), TRUE ),
				$title
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
				$output[$key] = strip_tags( stripslashes( $input[ $key ] ) );

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
				$output[$key] = strip_tags( stripslashes( $input[ $key ] ) );

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

			<div class="notice notice-error"><p><strong><?php echo $api_verification; ?></strong></p></div>

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

		<h2>Newsletters Entities</h2>

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
							<td><?php echo $value['name']; ?></td>
							<td><?php echo $value['amount']; ?></td>
							<td><?php echo $value['crop']; ?></td>
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

		ob_start();

		?>

		<h2>Entities with positions</h2>

		<?php if ( isset( $entities ) && !empty( $entities['positions_ids'] ) ): ?>

			<table class="wp-list-table widefat fixed striped table-view-list">
				<thead>
					<tr>
						<th>Entity name</th>
						<th>Post ID</th>
						<th>Post title</th>
					</tr>
				</thead>
				<tbody>
					<?php $i = 1; foreach ( $entities['positions_ids'] as $key => $value ): ?>
						<tr>
							<td><?php echo $key; ?></td>
							<td><?php echo $value; ?></td>
							<td><?php echo get_the_title( (int) $value ) ?></td>
						</tr>
					<?php $i++; endforeach; ?>
				</tbody>
			</table>
		<?php else: ?>

			<p>No entities positions assigned yet to any posts.</p>

		<?php endif; ?>

		<?php return ob_get_clean();
	
	}
	/**
	 * Delete all the entities metadata in posts.
	*/
	public function delete_all_entities_metadata() {

        $entities_options = get_option('intermedia_hubspot_newsletters_newsletters_settings');

        $cpt_included = $entities_options['cpt_included'];

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

				echo '<div class="notice notice-success"><p>Removed entities positions from post ID: <strong>'.$post_id.'</strong></p></div>';

			}

		} else {

			echo '<div class="notice notice-warning"><p>There are no posts assigned to any entity.</p></div>';

		}
        
	}

}