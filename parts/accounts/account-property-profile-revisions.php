<?php 
/* ***

Template Part:  Forms - Property Profile - Revisions

@package	      Sustainable Tools
@author		      Digital Rockpool
@link		        https://www.sustainable.tools/yardstick
@copyright	    Copyright (c) 2022, Digital Rockpool LTD
@license	      GPL-2.0+ 

*** */

$master_loc = $_SESSION['master_loc']; ?>
	
<section class="secondary-box p-3 mb-4 bg-white shadow-sm">

  <h2 class="h4-style">Revisions</h2> <?php

  $profiles  = $wpdb->get_results( "SELECT profile_location.id, entry_date, display_name, loc_name, parent_id FROM profile_location INNER JOIN wp_users ON profile_location.user_id=wp_users.id WHERE parent_id=$master_loc AND country<>'' ORDER BY profile_location.id DESC" );
  
  if( empty( $profiles ) ) : ?> 
  
    <p>Your property profile have not been edited.</p> <?php
  
  else : ?>

    <div class="table-responsive-xl">
      <table id="tags" class="table table-borderless">
        <tbody> <?php

          foreach( $profiles as $profile ) :

            $view_id = $profile->id;
            $view_entry_date = date_create( $profile->entry_date );
            $view_display_name = $profile->display_name;
            $view_loc_name = $profile->loc_name;
            $view_parent_id = $profile->parent_id; ?>

            <tr>
              <td> 
                <button type="button" class="btn btn-dark d-inline-block" data-toggle="modal" data-target="#modalRevisions-<?php echo $view_id ?>"><i class="far fa-eye"></i></button>

                <div class="modal fade text-left" id="modalRevisions-<?php echo $view_id ?>" tabindex="-1" role="dialog" aria-labelledby="modalRevisions-<?php echo $view_id ?>Title" aria-hidden="true">
                  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                    <div class="modal-content">

                      <div class="modal-header">
                        <h5 class="modal-title" id="modalRevisions-<?php echo $view_id ?>Title">Revisions for <?php echo $view_loc_name ?></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="far fa-times-circle"></i></span></button>
                      </div>

                      <div class="modal-body"> <?php

                        $revision_rows = $wpdb->get_results( "SELECT profile_location.id, entry_date, loc_name, street, city, county, country, latitude, longitude, display_name, parent_id FROM profile_location INNER JOIN wp_users ON profile_location.user_id=wp_users.id WHERE profile_location.id=$view_id" );

                        foreach( $revision_rows as $revision_row ) :

                          $revision_id = $revision_row->id;
                          $revision_entry_date = date_create( $revision_row->entry_date );
                          $revision_location = $revision_row->loc_name;
                          $revision_street = $revision_row->street;
                          $revision_city = $revision_row->city;
                          $revision_county = $revision_row->county;
                          $revision_country = $revision_row->country;
                          $revision_latitude = $revision_row->latitude;
                          $revision_longitude = $revision_row->longitude;
                          $revision_username = $revision_row->display_name;
                          $revision_parent_id = $revision_row->parent_id;

                          echo '<p><b>Property Name: </b>'.$revision_location.'<br />';

                          if( !empty( $revision_street ) ) : echo '<b>Street Name: </b>'.$revision_street.'<br />'; endif;

                          echo '<b>Village / Town / City: </b>'.$revision_city.'<br />';
                          echo '<b>County / State / Province / Region: </b>'.$revision_county.'<br />';
                          echo '<b>Country: </b>'.$revision_country.'<br />';
                          echo '<b>Latitude: </b>'.$revision_latitude.'<br />';
                          echo '<b>Longitude: </b>'.$revision_longitude.'</p>';

                          if( $revision_id == $revision_parent_id ) : $active_action = 'Added'; else : $active_action = 'Edited'; endif;

                          echo '<p><b>'.$active_action.' on:</b> '.date_format( $revision_entry_date, "d-M-Y H:i" ).' by '.$revision_username.'</p>';

                        endforeach; ?>

                      </div>

                    </div>
                  </div>
                </div> 

              </td>
              <td><?php

                if( $view_id == $view_parent_id ) : $active_action = 'Added'; else : $active_action = 'Edited'; endif;

                echo '<b>'.$view_loc_name.'</b><br />'.$active_action.' on '.date_format( $view_entry_date, "d-M-Y" ).' by '.$view_display_name ?>

              </td>
            </tr> <?php

          endforeach; ?>

        </tbody>
      </table>
    </div> <?php

  endif; ?>
  
</section> <?php