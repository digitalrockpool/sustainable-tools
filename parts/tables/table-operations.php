<?php 
/* ***

Template Part:  Edit Table - Operations

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

$cat_id = $args['cat_id'];
$module_strip = $args['module_strip'];
$title = $args['title'];

$edit_url = $_GET['edit'];
$start = $_GET['start'];
$end = $_GET['end'];

$entry_date = date( 'Y-m-d H:i:s' );

$edit_rows = $wpdb->get_results( "SELECT data_operations.id, measure, measure_name.tag as measure_name, measure_date, measure_start, measure_end, utility_id, disposal_id, utility_tag.tag AS utility, custom_tag.tag AS plastic, disposal_tag.tag AS disposal, custom_tag.size, unit_tag.tag AS unit, amount, cost, data_operations.note, data_operations.parent_id, data_operations.active FROM data_operations LEFT JOIN data_measure ON (data_operations.measure=data_measure.parent_id AND data_measure.id IN (SELECT MAX(id) FROM data_measure GROUP BY parent_id)) INNER JOIN custom_tag ON (data_operations.utility_id=custom_tag.parent_id AND custom_tag.id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id)) LEFT JOIN custom_tag measure_name ON (data_measure.measure_name=measure_name.parent_id AND measure_name.id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id)) INNER JOIN master_tag utility_tag ON utility_tag.id=custom_tag.tag_id LEFT JOIN master_tag disposal_tag ON data_operations.disposal_id=disposal_tag.id INNER JOIN master_tag unit_tag ON unit_tag.id=custom_tag.unit_id RIGHT JOIN relation_user ON data_operations.loc_id=relation_user.loc_id WHERE relation_user.user_id=$user_id AND custom_tag.cat_id=$cat_id AND data_operations.id IN (SELECT MAX(id) FROM data_operations GROUP BY parent_id) AND measure_date BETWEEN '$start' AND '$end'" );

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
            <th scope="col" class="filter-column">Measure Name<</th> <?php
          else : ?>
            <th scope="col">Date of Measure</th> <?php
          endif; ?>
          <th scope="col" class="filter-column"><?php echo $title ?></th>
          <th scope="col" class="filter-column">Unit</th> <?php
          if( $title == 'Waste' ) : ?> <th scope="col" class="filter-column">Disposal Methods</th> <?php endif; ?>
          <th scope="col"><?php if( $title == 'Plastic' ) : echo 'Number Purchase'; else : echo 'Amount'; endif; ?></th>
          <th scope="col"><?php if( $title == 'Plastic' ) : echo 'Total Cost'; else : echo 'Cost'; endif; ?></th> <?php
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
          $edit_utility = $edit_row->utility;
          $edit_utility_id = $edit_row->utility_id;
          $edit_plastic = $edit_row->plastic;
          $edit_disposal = $edit_row->disposal;
          $edit_disposal_id = $edit_row->disposal_id;
          $edit_size = $edit_row->size;
          $edit_unit = $edit_row->unit;
          $edit_amount = $edit_row->amount;
          $edit_cost = $edit_row->cost;
          $edit_note = $edit_row->note;
          $edit_parent_id = $edit_row->parent_id;
          $edit_active = $edit_row->active;
          $edit_operations = 'edit-'.$edit_id;
          $archive_operations = 'archive-'.$edit_id;

          $data_tags = $wpdb->get_results( "SELECT data_tag.tag_id, tag FROM custom_tag INNER JOIN data_tag ON custom_tag.parent_id=data_tag.tag_id WHERE data_id=$edit_id AND mod_id=2 AND custom_tag.id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id) ORDER BY tag" ); ?>

          <tr<?php if( $edit_active == 0 ) : echo ' class="strikeout"'; endif; ?>>
            <td class="align-top strikeout-buttons">

              <button type="button" class="btn btn-dark d-inline-block" data-toggle="modal" data-target="#modalRevisions-<?php echo $edit_id ?>"><i class="far fa-eye"></i></button>

              <div class="modal fade text-left" id="modalRevisions-<?php echo $edit_id ?>" tabindex="-1" role="dialog" aria-labelledby="modalRevisions-<?php echo $edit_id ?>Title" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                  <div class="modal-content">

                    <div class="modal-header">
                      <h5 class="modal-title" id="modalRevisions-<?php echo $edit_id ?>Title">Revisions for <?php echo $edit_utility ?></h5>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="far fa-times-circle"></i></span></button>
                    </div>

                    <div class="modal-body"> <?php

                      $revision_rows = $wpdb->get_results( "SELECT data_operations.id, data_operations.entry_date, measure, measure_name.tag as measure_name, measure_date, measure_start, measure_end, utility_tag.tag AS utility, custom_tag.tag AS plastic, disposal_tag.tag AS disposal, custom_tag.size, unit_tag.tag AS unit, amount, cost, data_operations.note, data_operations.parent_id, data_operations.active, display_name FROM data_operations LEFT JOIN data_measure ON (data_operations.measure=data_measure.parent_id AND data_measure.id IN (SELECT MAX(id) FROM data_measure GROUP BY parent_id)) INNER JOIN custom_tag ON (data_operations.utility_id=custom_tag.parent_id AND custom_tag.id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id)) LEFT JOIN custom_tag measure_name ON (data_measure.measure_name=measure_name.parent_id AND custom_tag.id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id)) INNER JOIN master_tag utility_tag ON utility_tag.id=custom_tag.tag_id LEFT JOIN master_tag disposal_tag ON data_operations.disposal_id=disposal_tag.id INNER JOIN master_tag unit_tag ON unit_tag.id=custom_tag.unit_id INNER JOIN wp_users ON data_operations.user_id=wp_users.id RIGHT JOIN relation_user ON data_operations.loc_id=relation_user.loc_id WHERE data_operations.parent_id=$edit_parent_id AND relation_user.user_id=$user_id AND utility_tag.cat_id=$cat_id GROUP BY data_operations.id ORDER BY data_operations.id DESC" );

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
                        $revision_utility = $revision_row->utility;
                        $revision_plastic = $revision_row->plastic;
                        $revision_disposal = $revision_row->disposal;
                        $revision_size = $revision_row->size;
                        $revision_unit = $revision_row->unit;
                        $revision_amount = $revision_row->amount;
                        $revision_cost = $revision_row->cost;
                        $revision_note = $revision_row->note;
                        $revision_parent_id = $revision_row->parent_id;
                        $revision_active = $revision_row->active;
                        $revision_username = $revision_row->display_name;

                        if( $revision_id == $revision_parent_id ) : $active_action = 'Added'; elseif( $revision_active == 0 ) : $active_action = 'Deleted'; else : $active_action = 'Edited'; endif;

                        echo '<b>Date of Measure:</b> ';
                        if( empty( $revision_measure_date ) ) : echo $revision_measure_start_formatted.' to '.$revision_measure_end_formatted; else : echo $revision_measure_date_formatted; endif;
                        echo '<br />';
                        if( $measure_toggle == 86 ) : echo '<b>Measure Name:</b> '.$revision_measure_name.'<br />'; endif;
                        echo '<b>'.$title.' Type:</b> '.$revision_utility;
                        if( !empty ( $revision_plastic ) ) : echo ' - '.$revision_plastic; endif;
                        echo ' (';
                        if( !empty ( $revision_size ) ) : $revision_size_decimal_clean = rtrim( number_format( $revision_size, 2 ) , '0' ); echo rtrim( $revision_size_decimal_clean, '.' ).' '; endif;
                        echo $revision_unit; if( $revision_size > 1 && $revision_unit != 'per pack' && $revision_unit != 'g' && $revision_unit != 'kg' && $revision_unit != 'ml' && $revision_unit != 'oz' ) : echo 's'; endif; echo ')<br />';
                        if( !empty( $revision_disposal ) ) : echo '<b>Disposal Method:</b> '.$revision_disposal.'<br />'; endif;
                        echo '<b>Amount:</b> '.number_format( $revision_amount, 2).'<br />';
                        echo '<b>Cost:</b> '.number_format( $revision_cost, 2).'<br />';

                        if( $tag_toggle == 1 ) :
                          echo '<b>Tags:</b> ';

                          $revision_tags = $wpdb->get_results( "SELECT tag FROM custom_tag INNER JOIN data_tag ON custom_tag.parent_id=data_tag.tag_id WHERE data_id=$revision_id AND mod_id=2 AND custom_tag.id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id) ORDER BY tag" );

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

              <form method="post" name="archive" id="<?php echo $archive_operations ?>" class="d-inline-block align-top">
                <button type="submit" class="btn <?php echo $btn_style ?> d-inline-block" name="<?php echo $archive_operations ?>"><?php echo $edit_value ?></button>
              </form> <?php

              if( isset( $_POST[$archive_operations] ) ) :

                $wpdb->insert( 'data_operations',
                  array(
                    'entry_date' => $entry_date,
                    'record_type' => 'entry_revision',
                    'measure' => $edit_measure,
                    'measure_date' => $edit_measure_date,
                    'utility_id' => $edit_utility_id,
                    'disposal_id' => $edit_disposal_id,
                    'amount' => $edit_amount,
                    'cost' => $edit_cost,
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
                        'mod_id' => 2
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
                      <h5 class="modal-title" id="modalEdit-<?php echo $edit_id ?>Title">Edit <?php echo $title.' Type: '.$edit_utility; if(!empty ( $edit_plastic ) ) : echo ' - '.$edit_plastic;  endif; ?> </h5>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="far fa-times-circle"></i></span></button>
                    </div>

                    <div class="modal-body">
                      <p class="small">Fields marked with an asterisk<span class="text-danger">*</span> are required</p><?php

                      $args = array(
                        'edit_operations' => $edit_operations,
                        'edit_id' => $edit_id,
                        'edit_measure' => $edit_measure, 
                        'edit_measure_date_formatted' => $edit_measure_date_formatted, 
                        'edit_utility_id' => $edit_utility_id, 
                        'edit_amount' => $edit_amount, 
                        'edit_cost' => $edit_cost, 
                        'edit_disposal' => $edit_disposal, 
                        'edit_disposal_id' => $edit_disposal_id, 
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
            if( $measure_toggle == 86 ) : ?><td><?php echo $edit_measure_name; ?></td> <?php endif; ?>
            <td><?php echo $edit_utility; if(!empty ( $edit_plastic ) ) : echo ' - '.$edit_plastic;  endif; ?></td>
            <td><?php if(!empty ( $edit_size ) ) : $edit_size_decimal_clean = rtrim( number_format( $edit_size, 2 ) , '0' ); echo rtrim( $edit_size_decimal_clean, '.' ).' ';  endif; echo $edit_unit; if( $edit_size > 1 && $edit_unit !== 'per pack' && $edit_unit != 'g' && $edit_unit != 'kg' && $edit_unit != 'ml' && $edit_unit != 'oz' ) : echo 's'; endif; ?></td> <?php
            if( $title == 'Waste' ) : ?> <td><?php echo $edit_disposal; ?></td> <?php endif; ?>
            <td class="text-right"><?php echo number_format( $edit_amount, 2 ) ?></td>
            <td class="text-right"><?php if( !empty( $edit_cost ) ) : echo number_format( $edit_cost, 2 ); endif; ?></td> <?php

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
          <th></th><?php
          if( $title == 'Waste' ) : ?> <th></th> <?php endif; ?>
          <th></th>
          <th></th><?php
          if( $tag_toggle == 1 ) : ?> <th></th> <?php endif; ?>
          <th></th>
        </tr>
      </tfoot>
    </table>
  </div> <?php

endif;
