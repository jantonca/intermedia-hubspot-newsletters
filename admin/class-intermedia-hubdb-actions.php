<?php

/**
 * The connection to HubSpot
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
use SevenShores\Hubspot\Resources\HubDB;
use Helpers\HubspotClientHelper;
use SevenShores\Hubspot\Resources\Integration;

class Intermedia_HubDB_Actions {

    public static function verify_hubspot_api(){

        $options = get_option('intermedia_hubspot_newsletters_hubspot_settings');

		if ( !isset($options['hapikey']) || $options['hapikey'] === '' ) {

			return false;

		}

		$hubSpot = HubspotClientHelper::createFactory();
        $response = $hubSpot->Integration()->getAccountDetails();

        if ( isset($response->data->status) ):

			return $response->data->message;

		endif;

        return true;

    }

    public static function get_hubspot_id(){

        $options = get_option('intermedia_hubspot_newsletters_hubspot_settings');

		$hubSpot = HubspotClientHelper::createFactory();

        $response = $hubSpot->Integration()->getAccountDetails();

        return $response->data->portalId;

    }

	public static function get_hubdb_tables() {

		$hubSpot = HubspotClientHelper::createFactory();

		$response = $hubSpot->HubDB()->tables();
	
		$result = [ 'default' => 'Select a HubDB...' ];

		foreach( $response->data->objects as $table ) {

			$result = $result + array( $table->id => $table->name.' (ID: '.$table->id.')' );
				
		}

		return $result;
	
	}

	public static function display_table_specs( $hapikey, $portalId, $tableId ) {

		$hubSpot = HubspotClientHelper::createFactory();

		$params = array(
		  'hapikey' => $hapikey, 
		);
	
		$response = $hubSpot->HubDB()->getTable( $tableId, $portalId, false, $params );
		$epoch = round($response->data->updatedAt/1000);
		$updated_time = new DateTime("@$epoch");  // convert UNIX timestamp to PHP DateTime
		$timezone = new DateTimeZone('Australia/Sydney');
		$updated_time->setTimezone($timezone);

		ob_start();

		?>
	
		<h1>Table name: <b><?php echo  esc_html( $response->data->name ); ?></h1>
		<h2>Table specs</h2>
		<table class="wp-list-table widefat fixed striped table-view-list">
			<thead>
				<tr>
					<th>ID</th>
					<th>Last updated</th>
					<th>Total columns</th>
					<th>Total rows</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><?php echo esc_html( $response->data->id ); ?></td>
					<td><?php echo esc_html( $updated_time->format('r') ); ?></td>
					<td><?php echo esc_html( $response->data->columnCount ); ?></td>
					<td><?php echo esc_html( $response->data->rowCount ); ?></td>
				</tr>
			</tbody>
		</table>
		<h2>Column structure</h2>
		<table class="wp-list-table widefat fixed striped table-view-list">
			<thead>
				<tr>
					<th>Column order</th>
					<th>Column name</th>
					<th>Column ID</th>
				</tr>
			</thead>
			<tbody>
				<?php $i = 1; foreach ( $response->data->columns as $column ): ?>
					<tr>
						<td><?php echo esc_html( $i ); ?></td>
						<td><?php echo esc_html( $column->name ); ?></td>
						<td><?php echo esc_html( $column->id ); ?></td>
					</tr>
				<?php $i++; endforeach; ?>
			</tbody>
		</table>
	
		<?php return ob_get_clean();
	
	}

	public static function display_table_content( $hapikey, $portalId, $tableId ) {

		$hubSpot = HubspotClientHelper::createFactory();
	  
		$params = array(
		  'hapikey' => $hapikey, 
		);
	  
		$response_columns = $hubSpot->HubDB()->getTable( $tableId, $portalId, false, $params );

		$response_rows = $hubSpot->HubDB()->getRows( $tableId, $portalId, false, $params );

		ob_start();

		?>

		<h2>Table content</h2>
		<table class="wp-list-table widefat fixed striped table-view-list">
			<thead>
				<tr>
					<th scope="col" id="hubspot_row_id" class="column-hubspot-row_id">Row ID</th>
					<?php foreach ( $response_columns->data->columns as $column ): ?>
						<th scope="col" id="<?php echo esc_attr( 'hubspot_'.$column->name ); ?>" class="<?php echo esc_attr( 'column-hubspot-'.$column->name ); ?>"><?php echo esc_html( $column->label ); ?> (<?php echo esc_html( $column->name ); ?>) ID: <?php echo esc_html( $column->id ); ?></th>
					<?php endforeach; ?>
					<th scope="col" id="hubspot_row_action" class="column-hubspot-row_action"> </th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $response_rows->data->objects as $rows ): ?>
					<tr>
						<td data-colname="row_id" class="row_id" ><?php echo esc_html( $rows->id ); ?></td>
						<?php foreach ( $rows->values as $row => $value ): ?>
							<?php 
								if ( $row === '1') {
									$row_post_position = $value;
								}
								?>
							<td data-colname="" class="" ><?php echo $value?: '<span><small><i>empty value</i><small></span>'; ?></td>
						<?php endforeach; ?>
						<td data-colname="col-action" class="col-action" >
							<form method="post">
								<input type="hidden" name="<?php echo 'update_table_row['.esc_attr( $rows->id ).']'; ?>" class="button button-secondary" value="<?php echo esc_attr( $row_post_position ); ?>" />
								<p class="submit">
									<input style="display: block;" type="submit" class="button button-secondary" value="Update Row" />
								</p>
							</form>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	
		<?php return ob_get_clean();
	  
		
	}

	public static function update_table_rows( $hapikey, $portalId, $tableId ){

		$hubSpot = HubspotClientHelper::createFactory();
	  
		$params = array(
		  'hapikey' => $hapikey, 
		);

		$response_rows = $hubSpot->HubDB()->getRows( $tableId, $portalId, false, $params );

		$rows_ids = [];

		foreach ( $response_rows->data->objects as  $rows ) {

			foreach ( $rows->values as $key => $value ) {

			  if( $key === '1' ) {

				$rows_ids[ $rows->id ] = $value;

			  }

			}
		}

		$posts_ids = WP_REST_Intermedia_newsletters_Posts::get_posts_ids_entities();

		if( $posts_ids['posts_ids'] ) {

			$options = get_option('intermedia_hubspot_newsletters_newsletters_settings');

			$entities_crops = Intermedia_newsletters_Entities::entities_positions_with_crops();

			foreach ( $posts_ids['positions_ids'] as $position => $id_post_value ) {
	  
					$image_crop = 'full';
					foreach ($entities_crops as $key => $value) {

						if ( strpos( $position, $key ) !== false ) {

							$image_crop = $value;

						}

					}

					/* Starts multisite*/

					if ( 
						isset( $options['subsites_included'] ) && 
						!empty ( $options['subsites_included'] ) 
					) {

						foreach ( $options['subsites_included'] as $subsite_id ) {

							switch_to_blog( (int) $subsite_id );

							if( get_post_status( $id_post_value ) !== 'publish' || !in_array(  $position, get_post_meta( $id_post_value, 'entities_select_positions', true ) )  ) {

								continue;

							}

							$post_type = get_post_type( $id_post_value );

							if ( get_post_meta( $id_post_value, 'intermedia_sponsored_content', true )  && get_post_meta( $id_post_value, 'intermedia_sponsored_content', true ) !=='' ) {
		
								$intermedia_sponsored_content = get_post_meta( $id_post_value, 'intermedia_sponsored_content', true );
								$intermedia_sponsored_name = $intermedia_sponsored_content[0]['name'];
		
							} else {
		
								$intermedia_sponsored_name = 'n/a';
		
							}
		
							/**
							 * Detect tribe events plugin plugin. For use in Admin area only.
							 */
							if ( is_plugin_active( 'the-events-calendar/the-events-calendar.php' ) && $post_type === 'tribe_events' ) {
		
								$events_date_format = !isset( $options['tribe_events_date_format'] ) ? 'd M, Y' : $options['tribe_events_date_format'];
		
								$event_start_date = tribe_get_start_date( $id_post_value, false, $events_date_format );( $id_post_value );
		
							} else {
		
								$event_start_date = 'n/a';
		
							}
		
							if ( in_array( $position, $rows_ids ) ) {
		
								$row_id = array_search ( $position, $rows_ids );
		
								$values = array(
									'1' => $position,
									'2' => $post_type,
									'3' => get_the_title( $id_post_value ),
									'4' => get_permalink( $id_post_value ),
									'5' => get_the_excerpt( $id_post_value ),
									'6' => get_the_post_thumbnail_url( $id_post_value, $image_crop ),
									'7' => $intermedia_sponsored_name,
									'8' => $event_start_date,
								);
								$update_row = $hubSpot->HubDB()->updateRow( $tableId, $row_id, $values );
								$published = $hubSpot->hubDB()->publishDraftTable($tableId);
						
								$epoch = round($published->data->updatedAt/1000);
								$updated_time = new DateTime("@$epoch");  // convert UNIX timestamp to PHP DateTime
								$timezone = new DateTimeZone('Australia/Sydney');
								$updated_time->setTimezone($timezone);
		
								echo '<div class="notice notice-success"><p>Updated row (id: <b>'.esc_html( $row_id ).'</b>) on '.esc_html( $updated_time->format('r') ).' with the position: <b>'.esc_html( $position ).'</b></p></div>';
		
							}
							else {
		
								$values = array(
									'1' => $position,
									'2' => $post_type,
									'3' => get_the_title( $id_post_value ),
									'4' => get_permalink( $id_post_value ),
									'5' => get_the_excerpt( $id_post_value ),
									'6' => get_the_post_thumbnail_url( $id_post_value, $image_crop ),
									'7' => $intermedia_sponsored_name,
									'8' => $event_start_date,
								);
								$create_row = $hubSpot->HubDB()->addRow( $tableId, $values );
								$published = $hubSpot->hubDB()->publishDraftTable($tableId);
								echo '<div class="notice notice-success"><p>New row created with the id: <b>'.esc_html( $create_row->data->id ).'</br> and the position: <b>'.esc_html( $position ).'</b></p></div>';
		
							}

							restore_current_blog();

						}

					/* Ends multisite*/

					} else {

						$post_type = get_post_type( $id_post_value );

						if ( get_post_meta( $id_post_value, 'intermedia_sponsored_content', true )  && get_post_meta( $id_post_value, 'intermedia_sponsored_content', true ) !=='' ) {
	
							$intermedia_sponsored_content = get_post_meta( $id_post_value, 'intermedia_sponsored_content', true );
							$intermedia_sponsored_name = $intermedia_sponsored_content[0]['name'];
	
						} else {
	
							$intermedia_sponsored_name = 'n/a';
	
						}
	
						/**
						 * Detect tribe events plugin plugin. For use in Admin area only.
						 */
						if ( is_plugin_active( 'the-events-calendar/the-events-calendar.php' ) && $post_type === 'tribe_events' ) {
	
							$events_date_format = !isset( $options['tribe_events_date_format'] ) ? 'd M, Y' : $options['tribe_events_date_format'];
	
							$event_start_date = tribe_get_start_date( $id_post_value, false, $events_date_format );( $id_post_value );
	
						} else {
	
							$event_start_date = 'n/a';
	
						}
	
						if ( in_array( $position, $rows_ids ) ) {
	
							$row_id = array_search ( $position, $rows_ids );
	
							$values = array(
								'1' => $position,
								'2' => $post_type,
								'3' => get_the_title( $id_post_value ),
								'4' => get_permalink( $id_post_value ),
								'5' => get_the_excerpt( $id_post_value ),
								'6' => get_the_post_thumbnail_url( $id_post_value, $image_crop ),
								'7' => $intermedia_sponsored_name,
								'8' => $event_start_date,
							);
							$update_row = $hubSpot->HubDB()->updateRow( $tableId, $row_id, $values );
							$published = $hubSpot->hubDB()->publishDraftTable($tableId);
					
							$epoch = round($published->data->updatedAt/1000);
							$updated_time = new DateTime("@$epoch");  // convert UNIX timestamp to PHP DateTime
							$timezone = new DateTimeZone('Australia/Sydney');
							$updated_time->setTimezone($timezone);
	
							echo '<div class="notice notice-success"><p>Updated row (id: <b>'.esc_html( $row_id ).'</b>) on '.esc_html( $updated_time->format('r') ).' with the position: <b>'.esc_html( $position ).'</b></p></div>';
	
						}
						else {
	
							$values = array(
								'1' => $position,
								'2' => $post_type,
								'3' => get_the_title( $id_post_value ),
								'4' => get_permalink( $id_post_value ),
								'5' => get_the_excerpt( $id_post_value ),
								'6' => get_the_post_thumbnail_url( $id_post_value, $image_crop ),
								'7' => $intermedia_sponsored_name,
								'8' => $event_start_date,
							);
							$create_row = $hubSpot->HubDB()->addRow( $tableId, $values );
							$published = $hubSpot->hubDB()->publishDraftTable($tableId);
							echo '<div class="notice notice-success"><p>New row created with the id: <b>'.esc_html( $create_row->data->id ).'</br> and the position: <b>'.esc_html( $position ).'</b></p></div>';
	
						}
	
					}

			}
	  
		} else {
		  
		  echo '<div class="notice notice-warning"><p>There are no posts assigned to any position.</p></div>';
	  
		}
	  
	}

	public static function update_table_row( $hapikey, $portalId, $tableId, $row_id_position ){

		foreach ( $row_id_position as $key => $value ) {

			$row_id = $key;
			$row_position = $value;

		}

		//get the options from the newsletter settings page: wp-admin/plugins.php?page=intermedia_hubspot_newsletters_options&tab=hubspot_table
		$options = get_option('intermedia_hubspot_newsletters_newsletters_settings');

		$hubSpot = HubspotClientHelper::createFactory();
	  
		$params = array(
		  'hapikey' => $hapikey, 
		);

		$posts_ids = WP_REST_Intermedia_newsletters_Posts::get_posts_ids_entities();

		$posts_positions_ids = $posts_ids['positions_ids'];

		if( $posts_ids['posts_ids'] ) {
			
			if ( array_key_exists( $row_position, $posts_positions_ids ) ) {

				$id_post_value = $posts_positions_ids[$row_position];

				$entities_crops = Intermedia_newsletters_Entities::entities_positions_with_crops();
				$image_crop = 'full';
				foreach ($entities_crops as $key => $value) {

					if ( strpos( $row_position, $key ) !== false ) {

						$image_crop = $value;

					}

				}
				
				/* Starts multisite*/

				if ( 
					isset( $options['subsites_included'] ) && 
					!empty ( $options['subsites_included'] ) 
				) {

					foreach ( $options['subsites_included'] as $subsite_id ) {

						switch_to_blog( (int) $subsite_id );

						if( get_post_status( $id_post_value ) !== 'publish'  ) {

							continue;

						}

						$post_type = get_post_type( $id_post_value );

						if ( get_post_meta( $id_post_value, 'intermedia_sponsored_content', true )  && get_post_meta( $id_post_value, 'intermedia_sponsored_content', true ) !=='' ) {
		
							$intermedia_sponsored_content = get_post_meta( $id_post_value, 'intermedia_sponsored_content', true );
							$intermedia_sponsored_name = $intermedia_sponsored_content[0]['name'];
		
						} else {
		
							$intermedia_sponsored_name = 'n/a';
		
						}
		
						/**
						 * Detect tribe events plugin plugin. For use in Admin area only.
						*/
						if ( is_plugin_active( 'the-events-calendar/the-events-calendar.php' ) && $post_type === 'tribe_events' ) {
		
							$events_date_format = !isset( $options['tribe_events_date_format'] ) ? 'd M, Y' : $options['tribe_events_date_format'];
		
							$event_start_date = tribe_get_start_date( $id_post_value, false, $events_date_format );( $id_post_value );
		
						} else {
		
							$event_start_date = 'n/a';
		
						}
		
						$values = array(
							'1' => $row_position,
							'2' => $post_type,
							'3' => get_the_title( $id_post_value ),
							'4' => get_permalink( $id_post_value ),
							'5' => get_the_excerpt( $id_post_value ),
							'6' => get_the_post_thumbnail_url( $id_post_value, $image_crop ),
							'7' => $intermedia_sponsored_name,
							'8' => $event_start_date,
						);
		
						$hubSpot->HubDB()->updateRow( $tableId, $row_id, $values );
						$published = $hubSpot->hubDB()->publishDraftTable($tableId);
				
						$epoch = round($published->data->updatedAt/1000);
						$updated_time = new DateTime("@$epoch");  // convert UNIX timestamp to PHP DateTime
						$timezone = new DateTimeZone('Australia/Sydney');
						$updated_time->setTimezone($timezone);
		
						echo '<div class="notice notice-success"><p>Updated row (id: <b>'.esc_html( $row_id ).'</b>) on '.esc_html( $updated_time->format('r') ).' with the position: <b>'.esc_html( $row_position ).'</b></p></div>';

						restore_current_blog();

					}

				} else {

					$post_type = get_post_type( $id_post_value );

					if ( get_post_meta( $id_post_value, 'intermedia_sponsored_content', true )  && get_post_meta( $id_post_value, 'intermedia_sponsored_content', true ) !=='' ) {
	
						$intermedia_sponsored_content = get_post_meta( $id_post_value, 'intermedia_sponsored_content', true );
						$intermedia_sponsored_name = $intermedia_sponsored_content[0]['name'];
	
					} else {
	
						$intermedia_sponsored_name = 'n/a';
	
					}
	
					/**
					 * Detect tribe events plugin plugin. For use in Admin area only.
					*/
					if ( is_plugin_active( 'the-events-calendar/the-events-calendar.php' ) && $post_type === 'tribe_events' ) {
	
						$events_date_format = !isset( $options['tribe_events_date_format'] ) ? 'd M, Y' : $options['tribe_events_date_format'];
	
						$event_start_date = tribe_get_start_date( $id_post_value, false, $events_date_format );( $id_post_value );
	
					} else {
	
						$event_start_date = 'n/a';
	
					}
	
					$values = array(
						'1' => $row_position,
						'2' => $post_type,
						'3' => get_the_title( $id_post_value ),
						'4' => get_permalink( $id_post_value ),
						'5' => get_the_excerpt( $id_post_value ),
						'6' => get_the_post_thumbnail_url( $id_post_value, $image_crop ),
						'7' => $intermedia_sponsored_name,
						'8' => $event_start_date,
					);
	
					$hubSpot->HubDB()->updateRow( $tableId, $row_id, $values );
					$published = $hubSpot->hubDB()->publishDraftTable($tableId);
			
					$epoch = round($published->data->updatedAt/1000);
					$updated_time = new DateTime("@$epoch");  // convert UNIX timestamp to PHP DateTime
					$timezone = new DateTimeZone('Australia/Sydney');
					$updated_time->setTimezone($timezone);
	
					echo '<div class="notice notice-success"><p>Updated row (id: <b>'.esc_html( $row_id ).'</b>) on '.esc_html( $updated_time->format('r') ).' with the position: <b>'.esc_html( $row_position ).'</b></p></div>';

				}

			} else {

				echo '<div class="notice notice-warning"><p>The row <b>'.esc_html( $row_id ).'</b> has been deleted or does not exists.</p></div>';

			}

			$entities_crops = Intermedia_newsletters_Entities::entities_positions_with_crops();

	  
		} else {
		  
		  echo '<div class="notice notice-warning"><p>There are no posts assigned to any position.</p></div>';
	  
		}
	  
	}

}