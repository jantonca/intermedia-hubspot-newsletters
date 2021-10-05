<?php
/**
 * The entities and positions in the newsletters
 *
 * @link       https://www.intermedia.com.au/
 * @since      1.0.0
 *
 * @package    Intermedia_Hubspot_Newsletters
 * @subpackage Intermedia_Hubspot_Newsletters/admin
 */

/**
 * Class for managing the entities and positions in the newsletters
 *
 */
class Intermedia_newsletters_Entities {

    public static function create_entities_positions(){
        
        $entities_options = get_option('intermedia_hubspot_newsletters_newsletters_settings');

        if ( isset( $entities_options['site_prefix'] ) && !empty( $entities_options['site_prefix'] ) && isset( $entities_options['newsletters_entities_number'] ) && !empty( $entities_options['newsletters_entities_number'] ) ) {
            
            $site_prefix = $entities_options['site_prefix'];
            $newsletters_entities_number = (int) $entities_options['newsletters_entities_number'];

            $entities = $entities_options['newsletters_entities'];

            for ( $i=0; $i < $newsletters_entities_number; $i++ ) {
    
                for ($n=1; $n <= $entities[$i]['amount']; $n++) { 
    
                    $positions[] = $site_prefix.'_'.$entities[$i]['name'].'_'.$n;
                    
                }   
    
            }
    
            return $positions;

        }

    }

    public static function create_entities_positions_multisite(){

        $current_site_entities_options = get_option('intermedia_hubspot_newsletters_newsletters_settings');
        
        if ( 
            isset( $current_site_entities_options['subsites_included'] ) && 
            !empty ( $current_site_entities_options['subsites_included'] ) &&
            isset( $current_site_entities_options['site_prefix'] ) && 
            !empty( $current_site_entities_options['site_prefix'] ) && 
            isset( $current_site_entities_options['newsletters_entities_number'] ) && 
            !empty( $current_site_entities_options['newsletters_entities_number'] ) &&
            isset( $current_site_entities_options['newsletters_entities'] ) && 
            !empty( $current_site_entities_options['newsletters_entities'] ) 
        ) {
            foreach ( $current_site_entities_options['subsites_included'] as $subsite_id ) {

                switch_to_blog( (int) $subsite_id );

                $entities_options = get_option('intermedia_hubspot_newsletters_newsletters_settings');

                if( 
                    isset( $entities_options['newsletters_entities_number'] ) && 
                    !empty( $entities_options['newsletters_entities_number'] )  
                ) {

                    $site_prefix = $entities_options['site_prefix'];
                    $newsletters_entities_number = (int) $entities_options['newsletters_entities_number'];
        
                    $entities = $entities_options['newsletters_entities'];
        
                    for ( $i=0; $i < $newsletters_entities_number; $i++ ) {
                        if( 
                            isset( $entities[$i]['amount'] ) && 
                            !empty( $entities[$i]['amount'] ) &&
                            isset( $entities[$i]['name'] ) && 
                            !empty( $entities[$i]['name'] )
                        ) {
                            for ($n=1; $n <= $entities[$i]['amount']; $n++) { 
            
                                $positions[] = $site_prefix.'_'.$entities[$i]['name'].'_'.$n;
                                
                            } 
                        }
  
                    }

                }

                restore_current_blog();

            }
    
            return $positions;

        }

    }

    public static function entities_positions_with_crops(){

        $entities_options = get_option('intermedia_hubspot_newsletters_newsletters_settings');

        if ( isset( $entities_options['site_prefix'] ) && !empty( $entities_options['site_prefix'] ) && isset( $entities_options['newsletters_entities_number'] ) && !empty( $entities_options['newsletters_entities_number'] ) ) {
            
            $site_prefix = $entities_options['site_prefix'];
            $newsletters_entities_number = (int) $entities_options['newsletters_entities_number'];

            $entities = $entities_options['newsletters_entities'];

            for ( $i=0; $i < $newsletters_entities_number; $i++ ) {
    
                for ($n=0; $n < $entities[$i]['amount']; $n++) { 
    
                    $positions[$site_prefix.'_'.$entities[$i]['name'].'_'.$n] = $entities[$i]['crop'];
                    
                }   
    
            }
    
            return $positions;

        }

    }

    public function entities_add_metabox() {

        $entities_options = get_option('intermedia_hubspot_newsletters_newsletters_settings');

        if ( isset( $entities_options['cpt_included'] ) && $entities_options['cpt_included'] !== '' ) {

            add_meta_box(

                'entities_metabox', // metabox ID
                'Newsletters positions', // title
                array( $this, 'entities_metabox_callback' ),
                $entities_options['cpt_included'], // post type or post types in array
                'normal',
                'default'
    
            );

        }
    
    }

    public function register_post_positions_api () {

        $entities_options = get_option('intermedia_hubspot_newsletters_newsletters_settings');

        if ( isset( $entities_options['cpt_included'] ) && $entities_options['cpt_included'] !== '' ) {

            foreach ( $entities_options['cpt_included'] as $cpt ) {

                register_post_meta(
                    $cpt,
                    'entities_select_positions',
                    array(
                        'single'       => true,
                        'type'         => 'array',
                        'show_in_rest' => array(
                            'schema' => array(
                                'type'  => 'array',
                                'items' => array(
                                    'type' => 'string',
                                ),
                            ),
                        ),
                    )
                );

            }
            
        }

    }
    
    public function entities_metabox_callback( $post_object ) {
    
        $appended_positions = get_post_meta( $post_object->ID, 'entities_select_positions',true );

        if( is_multisite() ) {

            $positions = self::create_entities_positions_multisite();

        } else {

            $positions = self::create_entities_positions();
            
        }

        if( $positions ) {

            ob_start(); ?>

            <p class="entities-box">

                <label for="entities_select_positions">Newsletters Positions:</label>

                <select id="entities_select_positions" class="custom-select-posts-entities" name="entities_select_positions[]" multiple="multiple">

                <?php foreach( $positions as $position ): ?>

                    <?php $selected = ( is_array( $appended_positions ) && in_array( $position, $appended_positions ) ) ? ' selected="selected"' : ''; ?>

                    <option value="<?php echo esc_attr( $position ); ?>" <?php echo esc_attr( $selected ); ?> ><?php echo esc_html( $position ); ?></option>

                <?php endforeach; ?>

                <select>
            </p>

            <?php $output =  ob_get_clean(); ?>

            <?php

                $allowed_html = array(
                    'p' => array(
                        'class'  => array(),
                    ),
                    'label' => array(
                        'for'  => array(),
                    ),
                    'select' => array(
                        'class'  => array(),
                        'id'    => array(),
                        'name'  => array(),
                        'multiple' => array()
                    ),
                    'option' => array(
                        'position'  => array(),
                        'selected'    => array(),
                    ),
                );

            ?>

            <?php echo wp_kses( $output, $allowed_html );

        }
        
    }

    public function entities_save_metaboxdata( $post_id, $post ) {

        if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return $post_id;

        $entities_options = get_option('intermedia_hubspot_newsletters_newsletters_settings');
        $cpt_included = $entities_options['cpt_included'];

        // if post type is different from our selected one, do nothing
        if ( isset( $cpt_included ) && in_array( $post->post_type, $cpt_included ) ) {

            if( isset( $_POST['entities_select_positions'] ) && isset( $_POST[ '_inline_edit' ] ) && wp_verify_nonce( sanitize_text_field( $_POST[ '_inline_edit' ] ), 'inlineeditnonce' ) )

                update_post_meta( $post_id, 'entities_select_positions', sanitize_text_field( $_POST['entities_select_positions'] ) );

            else

                delete_post_meta( $post_id, 'entities_select_positions' );
                
        }
        return $post_id;
    }

    /*******************************add a column in the wp_list_table of the admin area***********************************************/

    /**
    * Add new columns to the post table
    *
    * @param Array $columns - Current columns on the list post and events CPT
    */
    public static function add_entites_columns( $columns ) {

        $column_meta = array( 'entities_select_positions' => 'Entities Newsletters' );

        $columns = array_slice( $columns, 0, 6, true ) + $column_meta + array_slice( $columns, 6, NULL, true );

        return $columns;

    }

    // Register the columns as sortable
    public static function register_sortable_entities_column( $columns ) {

        $columns['entities_select_positions'] = 'entities_select_positions';

        return $columns;
        
    }

    //Add filter to the request to make the hits sorting process numeric, not string
    public function hits_column_orderby_entities( $vars ) {

        if ( isset( $vars['orderby'] ) && 'entities_select_positions' == $vars['orderby'] ) {

            $vars = array_merge( $vars, array(
                'meta_key' => 'entities_select_positions',
                'orderby' => 'meta_value'
                ) 
            );

        }

        return $vars;

    }

    /**
    * Display data in new columns
    *
    * @param  $column Current column
    *
    * @return Data for the column
    */
    public static function entities_custom_column ($column) {

        global $post;

        switch ( $column ) {

            case 'entities_select_positions':

                $entities_select_positions = get_post_meta( $post->ID, 'entities_select_positions', true );

                if ( isset( $entities_select_positions ) && $entities_select_positions !== '' ) {

                    echo esc_html( implode( ', ', $entities_select_positions ) );

                }

            break;
        }

    }

    public function setup_cpt_custom_column_entities() {

        $entities_options = get_option('intermedia_hubspot_newsletters_newsletters_settings');

        if ( isset( $entities_options['cpt_included'] ) && $entities_options['cpt_included'] !== '' ) {

            //Filter to show all kind of CPT in custom column newsletter_position
            add_action( 'manage_posts_custom_column' , array( 'Intermedia_newsletters_Entities', 'entities_custom_column' ) );

            foreach ( $entities_options['cpt_included']  as $type ) {

                add_filter( 'manage_edit-'.$type.'_columns',  array( 'Intermedia_newsletters_Entities', 'add_entites_columns' ) );

                add_filter( 'manage_edit-'.$type.'_sortable_columns', array( 'Intermedia_newsletters_Entities', 'register_sortable_entities_column' ) );

                if( $type === 'page' ) {

                    add_action( 'manage_'.$type.'_posts_custom_column' , array( 'Intermedia_newsletters_Entities', 'entities_custom_column' ) );
                    
                }
            }

        }

    }

    public function entities_quick_edit_custom_box( $column_name, $post_type ) {

        if ( 'entities_select_positions' == $column_name ) {

            if( is_multisite() ) {

                $positions = self::create_entities_positions_multisite();
    
            } else {
    
                $positions = self::create_entities_positions();
                
            }

            if( $positions ) {

                ob_start(); ?>
            
                <fieldset class="inline-edit-col-right">
                    <div class="inline-edit-col entities-box">
                        <label for="entities_select_positions">Newsletters Positions:</label>

                        <select id="entities_select_positionsx" class="custom-select-posts-entitiesx" name="entities_select_positions[]" multiple="multiple">

                        <?php foreach( $positions as $position ): ?>

                            <option id="<?php echo esc_attr( $position ); ?>" value="<?php echo esc_attr( $position ); ?>" ><?php echo esc_html( $position ); ?></option>

                        <?php endforeach; ?>

                        <select>
                    </div>
                </fieldset>

                <?php $output =  ob_get_clean(); ?>

                <?php

                    $allowed_html = array(
                        'fieldset' => array(
                            'class'  => array(),
                        ),
                        'div' => array(
                            'class'  => array(),
                        ),
                        'label' => array(
                            'for'  => array(),
                        ),
                        'select' => array(
                            'class'  => array(),
                            'id'    => array(),
                            'name'  => array(),
                            'multiple' => array()
                        ),
                        'option' => array(
                            'id'    => array(),
                            'value'  => array(),
                        ),
                    );

                ?>

                <?php echo wp_kses( $output, $allowed_html );

            }
            
        }

    }
    
    /*
    * Quick Edit Save
    */

    public function entities_quick_edit_save( $post_id ){

        // check user capabilities
        if ( !current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
        
        // verify quick edit nonce
        if ( isset( $_POST[ '_inline_edit' ] ) && ! wp_verify_nonce( sanitize_text_field( $_POST[ '_inline_edit' ] ), 'inlineeditnonce' ) )
        return $post_id;

        $entities_options = get_option('intermedia_hubspot_newsletters_newsletters_settings');
        $cpt_included = $entities_options['cpt_included'];

        // if post type is different from our selected one, do nothing
        if ( isset( $cpt_included ) && in_array( get_post_type( $post_id ), $cpt_included ) ) {
            
            if( isset( $_POST['entities_select_positions'] ) ) {

                $entities_select_positions = array_map( 'sanitize_text_field', $_POST['entities_select_positions'] );

                update_post_meta( $post_id, 'entities_select_positions', $entities_select_positions );

            } else {

                delete_post_meta( $post_id, 'entities_select_positions' );

            }
                
        }

    }

}