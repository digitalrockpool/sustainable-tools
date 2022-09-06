<?php 
/* ***

Template Part:  Edit Table - Charity

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
$tag_toggle = $_SESSION['tag_toggle'];

$tag_id = $args['tag_id'];
$module_strip = $args['module_strip'];
$title = $args['title'];

$edit_url = $_GET['edit'];
$start = $_GET['start'];
$end = $_GET['end'];

$entry_date = date( 'Y-m-d H:i:s' );

$edit_rows = $wpdb->get_results( "SELECT data_charity.id, measure, custom_tag.tag AS measure_name, measure_date, measure_start, measure_end, master_tag.tag as value_type, value_type as value_type_id, amount, duration, data_charity.location AS location_id, custom_location.location, data_charity.note, data_charity.parent_id, data_charity.active, loc_name FROM data_charity LEFT JOIN data_measure ON (data_charity.measure=data_measure.parent_id AND data_measure.id IN (SELECT MAX(id) FROM data_measure GROUP BY parent_id)) LEFT JOIN custom_tag ON (data_measure.measure_name=custom_tag.parent_id AND custom_tag.id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id)) INNER JOIN master_tag ON data_charity.value_type=master_tag.id INNER JOIN profile_location ON (data_charity.loc_id=profile_location.parent_id AND profile_location.id IN (SELECT MAX(id) FROM profile_location GROUP BY parent_id)) INNER JOIN custom_location ON (data_charity.location=custom_location.parent_id AND custom_location.id IN (SELECT MAX(id) FROM custom_location GROUP BY parent_id)) INNER JOIN relation_user ON data_charity.loc_id=relation_user.loc_id WHERE donation_type=$tag_id AND relation_user.user_id=$user_id AND data_charity.id IN (SELECT MAX(id) FROM data_charity GROUP BY parent_id) AND measure_date BETWEEN '$start' AND '$end'" );

if( empty( $edit_rows) ) :

  echo 'No '.strtolower( $title ).' data has been added.';

else : ?>

  <div class="table-responsive-xl mb-3">
    <table id="edit" class="table table-borderless nowrap" style="width:100%;">
      <thead>
        <tr>
          <th scope="col" class="no-sort">View | Delete | Edit</th> <?php
          if( $measure_toggle == 86 ) : ?>
            <th scope="col">Date Range</th>
            <th scope="col" class="filter-column">Measure Name</th> <?php
          else : ?>
            <th scope="col">Date of Donation</th> <?php
          endif; ?>
          <th scope="col" class="filter-column">Donee Location</th> <?php
          if( $tag_id == 66 ) : ?> <th scope="col">Staff Time Donated</th> <?php else : ?> <th scope="col" class="filter-column">Value Type</th> <?php endif; ?>
          <th scope="col">Amount (or cash equivalent)</th> <?php
          if( $tag_toggle == 1 ) : ?> <th scope="col">Tags</th> <?php endif; ?>
          <th scope="col">Notes</th>
        </tr>
      </thead>

      <tbody> <?php

        foreach( $edit_rows as $edit_row ) :

          $edit_id = $edit_row->id;
          $edit_measure = $edit_row->measure;
          $edit_measure_name = $edit_row->measure_name;
          $edit_measure_date = $edit_row->measure_date;
          $edit_measure_date_formatted = date_format( date_create( $edit_measure_date ), 'd-M-Y' );
          $edit_measure_start = $edit_row->measure_start;
          $edit_measure_start_formatted = date_format( date_create( $edit_measure_start ), 'd-M-Y' );
          $edit_measure_end = $edit_row->measure_end;
          $edit_measure_end_formatted = date_format( date_create( $edit_measure_end ), 'd-M-Y' );
          $edit_value_type = $edit_row->value_type;
          $edit_value_type_id = $edit_row->value_type_id;
          $edit_amount = $edit_row->amount;
          $edit_duration = $edit_row->duration;
          $edit_donee_location_id = $edit_row->location_id;
          $edit_donee_location = $edit_row->location;
          $edit_note = $edit_row->note;
          $edit_parent_id = $edit_row->parent_id;
          $edit_active = $edit_row->active;
          $edit_charity = 'edit-'.$edit_id;
          $archive_charity = 'archive-'.$edit_id;

          $data_tags = $wpdb->get_results( "SELECT data_tag.tag_id, tag FROM custom_tag INNER JOIN data_tag ON custom_tag.parent_id=data_tag.tag_id WHERE data_id=$edit_id AND mod_id=4 AND custom_tag.id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id)" );  ?>

          <tr <?php if( $edit_active == 0 ) : echo ' class="strikeout"'; endif; ?>>
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

                      $revision_rows = $wpdb->get_results( "SELECT data_charity.id, data_charity.entry_date, measure, custom_tag.tag AS measure_name, measure_date, measure_start, measure_end, master_tag.tag AS value_type, amount, duration, custom_location.location, data_charity.note, data_charity.parent_id, data_charity.active, loc_name, display_name, data_charity.user_id FROM data_charity LEFT JOIN data_measure ON (data_charity.measure=data_measure.parent_id AND data_measure.id IN (SELECT MAX(id) FROM data_measure GROUP BY parent_id)) LEFT JOIN custom_tag ON (data_measure.measure_name=custom_tag.parent_id AND custom_tag.id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id)) INNER JOIN master_tag ON data_charity.value_type=master_tag.id INNER JOIN profile_location ON (data_charity.loc_id=profile_location.parent_id AND profile_location.id IN (SELECT MAX(id) FROM profile_location GROUP BY parent_id)) INNER JOIN custom_location ON (data_charity.location=custom_location.parent_id AND custom_location.id IN (SELECT MAX(id) FROM custom_location GROUP BY parent_id)) LEFT JOIN wp_users ON data_charity.user_id=wp_users.id INNER JOIN relation_user ON data_charity.loc_id=relation_user.loc_id WHERE data_charity.parent_id=$edit_parent_id AND relation_user.user_id=$user_id ORDER BY data_charity.id DESC" );

                      foreach( $revision_rows as $revision_row ) :

                        $revision_id = $revision_row->id;
                        $revision_entry_date = date_create( $revision_row->entry_date );
                        $revision_measure = $revision_row->measure;
                        $revision_measure_name = $revision_row->measure_name;
                        $revision_measure_date = $revision_row->measure_date;
                        $revision_measure_date_formatted = date_format( date_create( $revision_measure_date ), 'd-M-Y' );
                        $revision_measure_start = $revision_row->measure_start;
                        $revision_measure_start_formatted = date_format( date_create( $revision_measure_start ), 'd-M-Y' );
                        $revision_measure_end = $revision_row->measure_end;
                        $revision_measure_end_formatted = date_format( date_create( $revision_measure_end ), 'd-M-Y' );
                        $revision_value_type = $revision_row->value_type;
                        $revision_amount = $revision_row->amount;
                        $revision_duration = $revision_row->duration;
                        $revision_donee_location = $revision_row->location;
                        $revision_note = $revision_row->note;
                        $revision_parent_id = $revision_row->parent_id;
                        $revision_active = $revision_row->active;
                        $revision_username_null = $revision_row->display_name;
                        $revision_user_id = $revision_row->user_id;

                        if( $revision_id == $revision_parent_id ) : $active_action = 'Added'; elseif( $revision_active == 0 ) : $active_action = 'Deleted'; else : $active_action = 'Edited'; endif;
                        if( empty( $revision_username_null ) ) :

                          $deleted_user = $wpdb->get_row( "SELECT deleted_user FROM relation_user WHERE active=0 AND user_id=$revision_user_id" );
                          $revision_username = $deleted_user->deleted_user.' (deleted user)';

                        else :

                          $revision_username = $revision_username_null;

                        endif;

                        echo '<b>Date of Donation:</b> ';
                        if( empty( $revision_measure_date ) ) : echo $revision_measure_start_formatted.' to '.$revision_measure_end_formatted; else : echo $revision_measure_date_formatted; endif;
                        echo '<br />';
                        if( $measure_toggle == 86 ) : echo '<b>Measure Name:</b> '.$revision_measure_name.'<br />'; endif;
                        echo '<b>Donee Location:</b> '.$revision_donee_location.'<br />';
                        if( $tag_id == 66 ) : echo '<b>Staff Time Donated:</b> '.number_format( $revision_duration, 2 ).'<br />'; endif;
                        if( $tag_id != 66 ) : echo '<b>Value Type:</b> '.$revision_value_type.'<br />'; endif;
                        echo '<b>Amount:</b> '.number_format( $revision_amount, 2 ).'<br />';

                        if( $tag_toggle == 1 ) :
                          echo '<b>Tags:</b> ';

                          $revision_tags = $wpdb->get_results( "SELECT tag FROM custom_tag INNER JOIN data_tag ON custom_tag.parent_id=data_tag.tag_id WHERE data_id=$revision_id AND mod_id=4 AND custom_tag.id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id) ORDER BY tag" );

                          $trim = '';
                          foreach( $revision_tags as $revision_tag ) :
                            $trim .= $revision_tag->tag.', ';
                          endforeach;

                          echo rtrim($trim, ', ').'<br />';

                        endif;

                        echo '<b>Notes:</b> '.$revision_note.'<br />';
                        echo '<b>'.$active_action.' on:</b> '.date_format( $revision_entry_date, "d-M-Y H:i" ).' by '.$revision_username.'<br />';
                        echo '<b>Entry ID:</b> '.$revision_id.'<br />';

                        if( $revision_id != $revision_parent_id ) : echo '<hr />'; endif;

                      endforeach; ?>

                    </div>

                  </div>
                </div>
              </div> <?php

              if( $edit_active == 1 ) : $edit_active_update = 0; $btn_style = 'btn-danger'; $edit_value = '<i class="far fa-trash-alt"></i>'; elseif( $edit_active == 0 ) : $edit_active_update = 1;  $btn_style = 'btn-success'; $edit_value = '<i class="far fa-trash-restore-alt"></i>'; endif; ?>

              <form method="post" name="archive" id="<?php echo $archive_charity ?>" class="d-inline-block">
                <button type="submit" class="btn <?php echo $btn_style ?> d-inline-block" name="<?php echo $archive_charity ?>"><?php echo $edit_value ?></button>
              </form> <?php

              if( isset( $_POST[$archive_charity] ) ) :

                $wpdb->insert( 'data_charity',
                  array(
                    'entry_date' => $entry_date,
                    'record_type' => 'entry_revision',
                    'measure' => $edit_measure,
                    'measure_date' => $edit_measure_date,
                    'donation_type' => $tag_id,
                    'value_type' => $edit_value_type_id,
                    'amount' => $edit_amount,
                    'duration' => $edit_duration,
                    'location' => $edit_donee_location_id,
                    'note' => $edit_note,
                    'active' => $edit_active_update,
                    'parent_id' => $edit_parent_id,
                    'user_id' => $user_id,
                    'loc_id' => $master_loc
                  )
                );

                $last_id = $wpdb->insert_id;

                if( !empty( $data_tags ) ) :

                  foreach( $data_tags as $data_tag ) :

                    $data_tag_id = $data_tag->tag_id;

                    $wpdb->insert( 'data_tag',
                      array(
                        'data_id' => $last_id,
                        'tag_id' => $data_tag_id,
                        'mod_id' => 4
                      )
                    );

                  endforeach;

                endif;

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

                    <div class="modal-body"><?php

                      $args = array(
                        'edit_charity' => $edit_charity,
                        'edit_id' => $edit_id,
                        'edit_measure' => $edit_measure, 
                        'edit_measure_date_formatted' => $edit_measure_date_formatted, 
                        'edit_donee_location_id' => $edit_donee_location_id, 
                        'edit_value_type_id' => $edit_value_type_id, 
                        'edit_amount' => $edit_amount, 
                        'edit_duration' => $edit_duration, 
                        'edit_note' => $edit_note, 
                        'edit_parent_id' => $edit_parent_id
                      );

                      get_template_part('/parts/forms/form', $module_strip, $args ); ?>

                    </div>

                  </div>
                </div>
              </div>

            </td>
            <td><span class="d-none"><?php echo $edit_measure_date.$edit_measure_start ?></span><?php if( empty( $edit_measure_date ) ) : echo $edit_measure_start_formatted.' to '.$edit_measure_end_formatted; else : echo $edit_measure_date_formatted; endif; ?></td> <?php
            if( $measure_toggle == 86 ) : ?><td><?php echo $edit_measure_name ?></td> <?php endif; ?>
            <td><?php echo $edit_donee_location ?></td> <?php
            if( $tag_id != 66 ) : ?> <td><?php echo $edit_value_type ?></td> <?php endif;
            if( $tag_id == 66 ) : ?> <td class="text-right"><?php echo number_format( $edit_duration, 2 ) ?></td> <?php endif; ?>
            <td class="text-right"><?php echo number_format( $edit_amount, 2 ) ?></td> <?php

            if( $tag_toggle == 1 ) : ?>
              <td><?php
                foreach( $data_tags as $data_tag ) : ?>
                  <div class="btn btn-info d-inline-block mr-1 float-none"><?php echo $data_tag->tag ?></div> <?php
                endforeach; ?>
              </td> <?php
            endif; ?>

            <td><?php echo $edit_note ?></td>
          </tr> <?php

        endforeach; ?>

      </tbody>

      <tfoot>
        <tr>
          <th class="text-right">Filter Data</th><?php
          if( $measure_toggle == 86 ) : ?>
            <th></th>
            <th></th><?php
          else : ?>
            <th></th> <?php
          endif; ?>
          <th></th>
          <th></th>
          <th></th> <?php
          if( $tag_toggle == 1 ) : ?> <th></th> <?php endif; ?>
          <th></th>
        </tr>
      </tfoot>

    </table>
  </div> <?php

endif;