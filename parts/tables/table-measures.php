<?php 
/* ***

Template Part:  Edit Table - Measures

@package	      Sustainable Tools
@author		      Digital Rockpool
@link		        https://www.sustainable.tools/yardstick
@copyright	    Copyright (c) 2022, Digital Rockpool LTD
@license	      GPL-2.0+ 

*** */

$site_url = get_site_url();
$slug = $post->post_name;

$user_id = get_current_user_id();
$master_loc = $_SESSION['master_loc'];
$measure_toggle = $_SESSION['measure_toggle'];

$module_strip = $args['module_strip'];
$title = $args['title'];

$edit_url = $_GET['edit'];
$start = $_GET['start'];
$end = $_GET['end'];

$entry_date = date( 'Y-m-d H:i:s' );

$edit_rows = $wpdb->get_results( "SELECT data_measure.id, tag, custom_tag.parent_id AS measure_name_id, measure_start, measure_end, bednight, roomnight, client, staff, area, note, data_measure.parent_id, data_measure.active, loc_name FROM data_measure LEFT JOIN custom_tag ON (data_measure.measure_name=custom_tag.parent_id AND custom_tag.id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id)) INNER JOIN profile_location ON (data_measure.loc_id=profile_location.parent_id AND profile_location.id IN (SELECT MAX(id) FROM profile_location GROUP BY parent_id)) INNER JOIN relation_user ON data_measure.loc_id=relation_user.loc_id WHERE relation_user.user_id=$user_id AND data_measure.id IN (SELECT MAX(id) FROM data_measure GROUP BY parent_id)" );

if( empty( $edit_rows) ) :

  echo 'No '.strtolower( $title ).' data has been added.';

else : ?>

  <div class="table-responsive-xl mb-3">
    <table id="edit" class="table table-borderless nowrap" style="width:100%;">
      <thead>
        <tr>
          <th scope="col" class="no-sort">View | Delete | Edit</th> <?php
          if( $measure_toggle == 86 ) : ?> <th scope="col">Name</th> <?php endif; // custom ?>
          <th scope="col">Start Date</th> <?php
          if( $measure_toggle == 86 ) : ?> <th scope="col">End Date</th> <?php endif; // custom ?>
          <th scope="col">B/N</th>
          <th scope="col">R/N</th>
          <th scope="col">Clients</th>
          <th scope="col">Staff</th>
          <th scope="col">Area <small class="d-inline" style="font-weight: 300;">(m2)</small></th>
          <th scope="col">Notes</th>
        </tr>
      </thead>

      <tbody> <?php

        foreach ( $edit_rows as $edit_row ) :

          $edit_id = $edit_row->id;
          $edit_measure_name = $edit_row->tag;
          $edit_measure_name_id = $edit_row->measure_name_id;
          $edit_measure_date = $edit_row->measure_start;
          $edit_measure_date_formatted = date_format( date_create( $edit_measure_date ), 'd-M-Y' );
          $edit_measure_end = $edit_row->measure_end;
          $edit_measure_end_formatted = date_format( date_create( $edit_measure_end ), 'd-M-Y' );
          $edit_bednight = $edit_row->bednight;
          $edit_roomnight = $edit_row->roomnight;
          $edit_client = $edit_row->client;
          $edit_staff = $edit_row->staff;
          $edit_area = $edit_row->area;
          $edit_note = $edit_row->note;
          $edit_parent_id = $edit_row->parent_id;
          $edit_active = $edit_row->active;
          $edit_measure = 'edit-'.$edit_id;
          $archive_measure = 'archive-'.$edit_id; ?>

          <tr<?php if( $edit_active == 0 ) : echo ' class="strikeout"'; endif; ?>>
            <td class="align-top strikeout-buttons">

              <button type="button" class="btn btn-dark d-inline-block" data-toggle="modal" data-target="#modalRevisions-<?php echo $edit_id ?>"><i class="far fa-eye"></i></button>

              <div class="modal fade text-left" id="modalRevisions-<?php echo $edit_id ?>" tabindex="-1" role="dialog" aria-labelledby="modalRevisions-<?php echo $edit_id ?>Title" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                  <div class="modal-content">

                    <div class="modal-header">
                      <h5 class="modal-title" id="modalRevisions-<?php echo $edit_id ?>Title">Revisions for <?php echo $title ?></h5>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="far fa-times-circle"></i></span></button>
                    </div>

                    <div class="modal-body"> <?php

                      $revision_rows = $wpdb->get_results( "SELECT data_measure.id, data_measure.entry_date, tag, measure_start, measure_end, bednight, roomnight, client, staff, area, note, data_measure.parent_id, data_measure.active, loc_name, display_name FROM data_measure LEFT JOIN custom_tag ON (data_measure.measure_name=custom_tag.parent_id AND custom_tag.id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id)) INNER JOIN yard_users ON data_measure.user_id=yard_users.id INNER JOIN profile_location ON (data_measure.loc_id=profile_location.parent_id AND profile_location.id IN (SELECT MAX(id) FROM profile_location GROUP BY parent_id)) INNER JOIN relation_user ON data_measure.loc_id=relation_user.loc_id WHERE data_measure.parent_id=$edit_parent_id AND relation_user.user_id=$user_id ORDER BY data_measure.id DESC" );

                      foreach( $revision_rows as $revision_row ) :

                        $revision_id = $revision_row->id;
                        $revision_entry_date = date_create( $revision_row->entry_date );
                        $revision_measure_name = $revision_row->tag;
                        $revision_measure_start_formatted = date_format( date_create( $revision_row->measure_start ), 'd-M-Y' );
                        $revision_measure_end_formatted = date_format( date_create( $revision_row->measure_end ), 'd-M-Y' );
                        $revision_bednight = $revision_row->bednight;
                        $revision_roomnight = $revision_row->roomnight;
                        $revision_client = $revision_row->client;
                        $revision_staff = $revision_row->staff;
                        $revision_area = $revision_row->area;
                        $revision_note = $revision_row->note;
                        $revision_parent_id = $revision_row->parent_id;
                        $revision_active = $revision_row->active;
                        $revision_username = $revision_row->display_name;

                        if( $revision_id == $revision_parent_id ) : $active_action = 'Added'; elseif( $revision_active == 0 ) : $active_action = 'Deleted'; else : $active_action = 'Edited'; endif;

                        if( $measure_toggle == 86 ) : echo '<b>Measure Name:</b> '.$revision_measure_name.'<br />'; endif; // custom
                        echo '<b>Start Date:</b> '.$revision_measure_start_formatted.'<br />';
                        if( $measure_toggle == 86 ) : echo '<b>End Date:</b> '.$revision_measure_end_formatted.'<br />'; endif; // custom
                        echo '<b>Bed Nights:</b> '.number_format( $revision_bednight ).'<br />';
                        echo '<b>Room Nights:</b> '.number_format( $revision_roomnight ).'<br />';
                        echo '<b>Clients:</b> '.number_format( $revision_client ).'<br />';
                        echo '<b>Staff:</b> '.number_format( $revision_staff ).'<br />';
                        echo '<b>Area (m2):</b> '.number_format( $revision_area ).'<br />';
                        echo '<b>Notes:</b> '.$revision_note.'<br />';
                        echo '<b>'.$active_action.' on:</b> '.date_format( $revision_entry_date, "d-M-Y H:i" ).' by '.$revision_username.'<br />';

                        if( $revision_id != $revision_parent_id ) : echo '<hr />'; endif;

                      endforeach; ?>

                    </div>

                  </div>
                </div>
              </div> <?php

              if( $edit_active == 1 ) : $edit_active_update = 0; $btn_style = 'btn-danger'; $edit_value = '<i class="far fa-trash-alt"></i>'; elseif( $edit_active == 0 ) : $edit_active_update = 1;  $btn_style = 'btn-success'; $edit_value = '<i class="far fa-trash-restore-alt"></i>'; endif; ?>

              <form method="post" name="archive" id="<?php echo $archive_measure ?>" class="d-inline-block">
                <button type="submit" class="btn <?php echo $btn_style ?> d-inline-block" name="<?php echo $archive_measure ?>"><?php echo $edit_value ?></button>
              </form> <?php

              if ( isset( $_POST[$archive_measure] ) ) :

                $wpdb->insert( 'data_measure',
                  array(
                    'entry_date' => $entry_date,
                    'record_type' => 'entry_revision',
                    'measure_type' => $measure_toggle,
                    'measure_name' => $edit_measure_name_id,
                    'measure_start' => $edit_measure_date,
                    'measure_end' => $edit_measure_end,
                    'bednight' => $edit_bednight,
                    'roomnight' => $edit_roomnight,
                    'client' => $edit_client,
                    'staff' => $edit_staff,
                    'area' => $edit_area,
                    'note' => $edit_note,
                    'active' => $edit_active_update,
                    'parent_id' => $edit_parent_id,
                    'user_id' => $user_id,
                    'loc_id' => $master_loc
                  )
                );

                header( 'Location:'.$site_url.'/'.$slug.'/?edit='.$edit_url.'&start='.$start.'&end='.$end );
                ob_end_flush();

              endif;

              if( $edit_active == 1 ) : ?>

                <button type="button" class="btn btn-light d-inline-block" data-toggle="modal" data-target="#modalEdit-<?php echo $edit_id ?>"><i class="fas fa-pencil"></i></button><?php

              endif; ?>

              <div class="modal fade" id="modalEdit-<?php echo $edit_id ?>" tabindex="-1" role="dialog" aria-labelledby="modalEdit-<?php echo $edit_id ?>Title" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                  <div class="modal-content text-left">

                    <div class="modal-header">
                      <h5 class="modal-title" id="modalEdit-<?php echo $edit_id ?>Title">Edit <?php echo $title ?></h5>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="far fa-times-circle"></i></span></button>
                    </div>

                    <div class="modal-body">
                      <p class="small">Fields marked with an asterisk<span class="text-danger">*</span> are required</p><?php

                      $args = array(
                        'edit_measure' => $edit_measure,
                        'edit_measure_name' => $edit_measure_name,
                        'edit_measure_date' => $edit_measure_date,
                        'edit_measure_end' => $edit_measure_end,
                        'edit_bednight' => $edit_bednight,
                        'edit_roomnight' => $edit_roomnight,
                        'edit_client' => $edit_client,
                        'edit_staff' => $edit_staff,
                        'edit_area' => $edit_area,
                        'edit_note' => $edit_note,
                        'edit_parent_id' => $edit_parent_id
                      );

                      get_template_part('/parts/forms/form', $module_strip, $args ); ?>

                    </div>

                  </div>
                </div>
              </div>

            </td> <?php
            if( $measure_toggle == 86 ) : // custom ?> <td><?php echo $edit_measure_name; ?></td> <?php endif; ?>
            <td><span class="d-none"><?php echo $edit_measure_date.$edit_measure_date ?></span><?php echo $edit_measure_date_formatted; ?></td> <?php
            if( $measure_toggle == 86 ) : // custom ?> <td><?php if( empty( $edit_measure_end ) ) : echo '&nbsp'; else : echo $edit_measure_end_formatted; endif; ?></td> <?php endif; ?>
            <td><?php echo number_format( $edit_bednight ) ?></td>
            <td><?php echo number_format( $edit_roomnight ) ?></td>
            <td><?php echo number_format( $edit_client ) ?></td>
            <td><?php echo number_format( $edit_staff ) ?></td>
            <td><?php echo number_format( $edit_area ) ?></td>
            <td><?php echo $edit_note ?></td>
          </tr> <?php

        endforeach; ?>

      </tbody>

      <tfoot>
        <tr>
          <th class="text-right"><?php if( $measure_toggle == 86 ) : ?> Filter Data<?php endif; // custom ?></th><?php
          if( $measure_toggle == 86 ) : ?> <th></th> <?php endif; // custom ?>
          <th></th> <?php
          if( $measure_toggle == 86 ) : ?> <th></th> <?php endif; // custom ?>
          <th></th>
          <th></th>
          <th></th>
          <th></th>
          <th></th>
          <th></th>
        </tr>
      </tfoot>

    </table>
  </div> <?php

endif;