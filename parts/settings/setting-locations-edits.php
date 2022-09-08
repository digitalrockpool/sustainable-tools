<?php 
/* ***

Template Part:  Settings - Locations - Edits

@package	      Sustainable Tools
@author		      Digital Rockpool
@link		        https://www.sustainable.tools/yardstick
@copyright	    Copyright (c) 2022, Digital Rockpool LTD
@license	      GPL-2.0+ 

*** */

$site_url = get_site_url();
$slug = $post->post_name;
$setting_query = $_GET['setting'];

$user_id = get_current_user_id();
$master_loc = $_SESSION['master_loc'];
$entry_date = date( 'Y-m-d H:i:s' );

$title = $args['title'];

$tags  = $wpdb->get_results( "SELECT custom_location.id, location, street, city, county, country, latitude, longitude, parent_id, active FROM custom_location WHERE loc_id=$master_loc AND custom_location.id IN (SELECT MAX(id) FROM custom_location GROUP BY parent_id) ORDER BY active DESC, custom_location.location ASC" );

if( empty( $tags ) ) :

  echo 'Please add the '.strtolower( $title ).' used by your business.';

else : ?>

  <div class="table-responsive-xl">
    <table id="tags" class="table table-borderless">
      <thead>
        <tr>
          <th scope="col" class="no-sort">View | Delete | Edit</th>
          <th scope="col">Sort <?php echo $title ?></th>
        </tr>
      </thead>

      <tbody> <?php

        foreach ( $tags as $tag ) :

          $edit_id = $tag->id;
          $edit_location = $tag->location;
          $edit_street = $tag->street;
          $edit_city = $tag->city;
          $edit_county = $tag->county;
          $edit_country = $tag->country;
          $edit_latitude = $tag->latitude;
          $edit_longitude = $tag->longitude;
          $edit_parent_id = $tag->parent_id;
          $edit_active = $tag->active;
          $edit_update = 'update-'.$edit_id;
          $edit_archive = 'archive-'.$edit_id;

          if( !empty( $edit_street ) ) : $edit_street_row = $edit_street.', '; else:  $edit_street_row = ''; endif;

          $row_item = $edit_location.'<br />'.$edit_street_row.$edit_city.', '.$edit_county.', '.$edit_country; ?>

          <tr<?php if( $edit_active == 0 ) : echo ' class="strikeout"'; endif; ?>>
            <td class="align-top strikeout-buttons">

              <button type="button" class="btn btn-dark d-inline-block" data-toggle="modal" data-target="#modalRevisions-<?php echo $edit_id ?>"><i class="far fa-eye"></i></button>

              <div class="modal fade text-left" id="modalRevisions-<?php echo $edit_id ?>" tabindex="-1" role="dialog" aria-labelledby="modalRevisions-<?php echo $edit_id ?>Title" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                  <div class="modal-content">

                    <div class="modal-header">
                      <h5 class="modal-title" id="modalRevisions-<?php echo $edit_id ?>Title">Revisions for <?php echo $edit_location ?></h5>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="far fa-times-circle"></i></span></button>
                    </div>

                    <div class="modal-body"> <?php

                      $revision_rows = $wpdb->get_results( "SELECT custom_location.id, entry_date, location, street, city, county, country, parent_id, display_name, active FROM custom_location INNER JOIN yard_users ON custom_location.user_id=yard_users.id WHERE parent_id=$edit_parent_id ORDER BY custom_location.id DESC" );

                      foreach( $revision_rows as $revision_row ) :

                        $revision_id = $revision_row->id;
                        $revision_entry_date = date_create( $revision_row->entry_date );
                        $revision_location = $revision_row->location;
                        $revision_street = $revision_row->street;
                        $revision_city = $revision_row->city;
                        $revision_county = $revision_row->county;
                        $revision_country = $revision_row->country;
                        $revision_parent_id = $revision_row->parent_id;
                        $revision_username = $revision_row->display_name;
                        $revision_active = $revision_row->active;

                        if( !empty( $revision_street ) ) : $revision_street_row = $revision_street.', '; endif;

                        echo $revision_location.'<br />'.$revision_street_row.$revision_city.', '.$revision_county.', '.$revision_country.'<br />';

                        if( $revision_id == $revision_parent_id ) : $active_action = 'Added'; elseif( $revision_active == 0 ) : $active_action = 'Deleted'; else : $active_action = 'Edited'; endif;

                        echo '<b>'.$active_action.' on:</b> '.date_format( $revision_entry_date, "d-M-Y H:i" ).' by '.$revision_username.'<br />';

                        if( $revision_id != $revision_parent_id ) : echo '<hr />'; endif;

                      endforeach; ?>

                    </div>

                  </div>
                </div>
              </div> <?php

              if( $edit_active == 1 ) : $edit_active_update = 0; $btn_style = 'btn-danger'; $edit_value = '<i class="fas fa-trash-alt"></i>'; elseif( $edit_active == 0 ) : $edit_active_update = 1; $btn_style = 'btn-success'; $edit_value = '<i class="fas fa-trash-restore-alt"></i>'; endif; ?>

              <form method="post" name="archive" id="<?php echo $edit_archive ?>" class="d-inline-block">
                <button type="submit" class="btn <?php echo $btn_style ?> d-inline-block" name="<?php echo $edit_archive ?>"><?php echo $edit_value ?></button>
              </form> <?php

              if ( isset( $_POST[$edit_archive] ) ) :

                $wpdb->insert( 'custom_location',
                  array(
                    'entry_date' => $entry_date,
                    'record_type' => 'entry_revision',
                    'location' => $edit_location,
                    'street' => $edit_street,
                    'city' => $edit_city,
                    'county' => $edit_county,
                    'country' => $edit_country,
                    'latitude' => $edit_latitude,
                    'longitude' => $edit_longitude,
                    'parent_id' => $edit_parent_id,
                    'user_id' => $user_id,
                    'active' => $edit_active_update,
                    'loc_id' => $master_loc
                  )
                );

                header ('Location:'.$site_url.'/'.$slug.'/?setting='.$setting_query);
                ob_end_flush();

              endif;

              if( $edit_active == 1 ) : ?>

                <button type="button" class="btn btn-light d-inline-block" data-toggle="modal" data-target="#modal-<?php echo $edit_id ?>"><i class="fas fa-pencil"></i></button>

                <div class="modal fade" id="modal-<?php echo $edit_id ?>" tabindex="-1" role="dialog" aria-labelledby="modal-<?php echo $tag_id ?>Title" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                    <div class="modal-content">

                      <div class="modal-header">
                        <h5 class="modal-title" id="modal-<?php echo $edit_id ?>Title"><?php echo $revision_location ?></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="far fa-times-circle"></i></span></button>
                      </div>

                      <div class="modal-body"> <?php echo do_shortcode( '[gravityform id=131 title="false" description=false field_values="tag_parent_id='.$edit_parent_id.'&tag_location='.$edit_location.'&tag_street='.$edit_street.'&tag_city='.$edit_city.'&tag_county='.$edit_county.'&tag_country='.$edit_country.'&tag_coordinates='.$edit_latitude.'|'.$edit_longitude.'"]' ); ?> </div>

                    </div>
                  </div>
                </div> <?php

              endif; ?>

            </td>
            <td><?php echo $row_item; ?></td>
          </tr> <?php

        endforeach; ?>

      </tbody>
    </table>
  </div> <?php

endif;