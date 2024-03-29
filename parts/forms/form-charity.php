<?php 
/* ***

Template Part:  Forms - Charity

@package	      Sustainable Tools
@author		      Digital Rockpool
@link		        https://www.sustainable.tools/yardstick
@copyright	    Copyright (c) 2022, Digital Rockpool LTD
@license	      GPL-2.0+ 

*** */

$site_url = get_site_url();
$slug = $post->post_name;

$add_url = $_GET['add'];
$edit_url = $_GET['edit'];
$start = $_GET['start'];
$end = $_GET['end'];

$user_id = get_current_user_id();
$master_loc = $_SESSION['master_loc'];
$measure_toggle = $_SESSION['measure_toggle'];
$tag_toggle = $_SESSION['tag_toggle'];

$entry_date = date( 'Y-m-d H:i:s' );

$tag_id = $args['tag_id'];
$edit_charity = $args['edit_charity'];
$edit_id = $args['edit_id'];
$edit_measure = $args['edit_measure'];
$edit_measure_date_formatted = $args['edit_measure_date_formatted'];
$edit_donee_location_id = $args['edit_donee_location_id'];
$edit_value_type_id = $args['edit_value_type_id'];
$edit_amount = $args['edit_amount'];
$edit_duration = $args['edit_duration'];
$edit_note = $args['edit_note'];
$edit_parent_id = $args['edit_parent_id'];

if( empty( $edit_charity ) ) : $update_charity = 'edit_charity'; else : $update_charity = $edit_charity; endif; ?>

<form method="post" name="edit" id="<?php echo $update_charity ?>" class="needs-validation" novalidate>
  <div class="row"> <?php

    if( $measure_toggle == 86 ) : // custom measures

      custom_measure_dropdown( $edit_measure );

    elseif( $measure_toggle == 85 ) : // yearly measure

      reporting_year_start_date( $edit_measure_date_formatted );

    else : ?>

      <div class="col-md-4 mb-3">
        <label for="edit-measure-date">Date of Donation<sup class="text-danger">*</sup></label>
        <div class="input-group">
          <span class="input-group-text"><i class="fa-regular fa-calendar-days"></i></span>
          <input type="text" class="form-control date" name="edit-measure-date" id="edit-measure-date" aria-describedby="editMeasureDate" placeholder="dd-mmm-yyyy" value="<?php if( empty( $edit_url ) ) : echo date( 'd-M-Y', strtotime( '-1 day' ) ); else : echo $edit_measure_date_formatted; endif; ?>" data-date-end-date="0d" required>
        </div>
        <div class="invalid-feedback">Please select a date</div>
      </div>

      <div class="col-md-6 mb-3 d-flex align-items-end">  <?php

        if( $measure_toggle == 84 || $measure_toggle == 83 ) : // monthly or weekly measures ?>
          <small>If this entry is for a period of time the amount will be added to the <?php if( $measure_toggle == 84 ) : echo 'month'; elseif( $measure_toggle == 83 ) : echo 'week'; endif; ?> of the selected date.</small> <?php
        endif; ?>

      </div> <?php

    endif; ?>

  </div>

  <div id="repeater-field">
    <div class="entry row g-1 mb-1"> <?php

      $donee_dropdowns = $wpdb->get_results( "SELECT parent_id, location FROM custom_location WHERE loc_id=$master_loc AND active=1 AND id IN (SELECT MAX(id) FROM custom_location GROUP BY parent_id) ORDER BY location ASC" ); ?>

      <div class="<?php if( empty( $add_url ) ) : ?>col-md-4<?php else : ?>col-md-5<?php endif; ?>">
        <?php if( empty( $add_url ) ) : ?><label for="edit-donee-location">Donee Location<sup class="text-danger">*</sup></label><?php endif; ?>
        <select class="form-select" id="edit-donee-location" name="edit-donee-location[]" required>
          <?php if( empty( $edit_url ) ) : ?><option value="">Select Donee Location</option><?php endif; ?>
          <option value="0" <?php if( $edit_source_id == 0 && empty( $add_url ) ) : echo 'selected'; else : echo ''; endif; ?>>Unknown Location</option> <?php

          foreach( $donee_dropdowns as $donee_dropdown ) :

            $donee_location_parent_id = $donee_dropdown->parent_id;
            $donee_location = $donee_dropdown->location;

            if( $edit_donee_location_id == $donee_location_parent_id ) : $selected = 'selected'; else : $selected = ''; endif; ?>

            <option value="<?php echo $donee_location_parent_id ?>" <?php echo $selected ?>><?php echo $donee_location ?></option> <?php

          endforeach; ?>

        </select>
        <div class="invalid-feedback">Please select donee location</div>
      </div> <?php

      if( $tag_id == 66 ) : ?>

        <div class="<?php if( empty( $add_url ) ) : ?>col-md-4<?php else : ?>col-md-3<?php endif; ?>">
          <?php if( empty( $add_url ) ) : ?><label for="edit-duration">Staff Time Donated<sup class="text-danger">*</sup></label><?php endif; ?>
          <input type="number" class="form-control" id="edit-duration" name="edit-duration[]" aria-describedby="editDuration" placeholder="Duration" value="<?php echo $edit_duration ?>" min="1" step="0.01" required>
          <div class="invalid-feedback">Please enter a number greater than 0.01</div>
        </div> <?php

      else :

        if( $edit_value_type_id == 67 ) : $selected = 'selected'; else : $selected = ''; endif;?>

        <div class="<?php if( empty( $add_url ) ) : ?>col-md-4<?php else : ?>col-md-3<?php endif; ?>">
          <?php if( empty( $add_url ) ) : ?><label for="edit-value-type">Value Type<sup class="text-danger">*</sup></label><?php endif; ?>
          <select class="form-select" id="edit-value-type" name="edit-value-type[]" required>
            <option value="68">Cash</option>
            <option value="67" <?php echo $selected ?>>In-Kind</option>
          </select>
          <div class="invalid-feedback">Please select value type</div>
        </div> <?php

      endif; ?>

      <div class="<?php if( empty( $add_url ) ) : ?>col-md-4<?php else : ?>col-md-3<?php endif; ?>">
        <?php if( empty( $add_url ) ) : ?><label for="edit-amount"><?php if( $tag_id == 66 ) : echo 'Cash Equivalent'; else : echo 'Amount / Cash Equivalent'; endif; ?><sup class="text-danger">*</sup></label><?php endif; ?>
        <input type="number" class="form-control" id="edit-amount" name="edit-amount[]" aria-describedby="editAmount" placeholder="<?php if( $tag_id == 66 ) : echo 'Cash Equivalent'; else : echo 'Amount / Cash Equivalent'; endif; ?>" value="<?php echo $edit_amount ?>" min="1" step="0.01" required>
        <div class="invalid-feedback">Please enter a number greater than 0.01</div>
      </div> <?php

      if( empty( $edit_url ) ) : ?><div class="col-1"><button type="button" class="btn btn-success btn-add"><i class="fa-solid fa-plus"></i></button></div> <?php endif; ?>

    </div>
  </div> <?php

  if( $tag_toggle == 1 ) : ?>

    <h5 class="border-top pt-3 mt-3">Tags</h5>

    <div class="row">

      <div class="col-12 mb-3">

        <select class="selectpicker form-control" name="edit-tag[]" multiple title="Select Tags" multiple data-live-search="true"> <?php
          $tag_dropdowns = $wpdb->get_results( "SELECT parent_id, tag FROM custom_tag WHERE loc_id=$master_loc AND cat_id=22 AND tag IS NOT NULL AND active=1 AND id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id) ORDER BY tag ASC" );

          foreach ($tag_dropdowns as $tag_dropdown ) :

            $dropdown_parent_id = $tag_dropdown->parent_id;
            $dropdown_tag = $tag_dropdown->tag;

            $edit_tag_id = $wpdb->get_results( "SELECT tag_id FROM data_tag WHERE data_id=$edit_id AND mod_id=4", ARRAY_N );
            $data_array = array_map( function ($arr) {return $arr[0];}, $edit_tag_id );

            if( in_array($dropdown_parent_id, $data_array ) ) : $selected = 'selected'; else : $selected = ''; endif; ?>

            <option value="<?php echo $dropdown_parent_id ?>" <?php echo $selected ?>><?php echo $dropdown_tag ?></option> <?php

          endforeach; ?>
        </select>
      </div>

    </div> <?php

  endif; ?>

  <h5 class="border-top pt-3 mt-3">Notes</h5>

  <div class="row">
    <div class="col-12 mb-3">
      <label for="edit-note">Please enter any notes for this entry</label>
        <textarea class="form-control" name="edit-note" id="edit-note" aria-describedby="editNote" placeholder="Notes"><?php echo $edit_note ?></textarea>
    </div>
  </div>

  <div class="row">
    <div class="col-12 mb-3"><button class="btn btn-primary" type="submit" name="<?php echo $update_charity ?>"><?php if( empty( $add_url ) ) : echo 'Update'; else : echo 'Add'; endif; echo ' '.str_replace( '-', ' ', $add_url ); ?></button></div>
  </div>
</form> <?php

if( isset( $_POST[$update_charity] ) ) :

  $update_donee_location_array = $_POST['edit-donee-location'];
  $update_value_type_array = $_POST['edit-value-type'];
  $update_amount_array = $_POST['edit-amount'];
  $update_duration_array = $_POST['edit-duration'];

  foreach( $update_donee_location_array as $index => $update_donee_location_array ) :

    $update_measure_null = $_POST['edit-measure'];
    $update_measure_date_null = $_POST['edit-measure-date'];
    $update_donee_location = $update_donee_location_array;
    $update_value_type_null = $update_value_type_array[$index];
    $update_amount = $update_amount_array[$index];
    $update_duration_null = $update_duration_array[$index];

    $update_tags = $_POST['edit-tag'];
    $update_note_null = $_POST['edit-note'];

    if( empty( $add_url ) ) : $record_type = 'entry_revision'; else : $record_type = 'entry'; endif;
    if( empty( $update_measure_null ) ) : $update_measure = NULL; else : $update_measure = $update_measure_null; endif;
    if( empty( $update_measure_date_null ) ) : $update_measure_date = NULL; else : $update_measure_date = date_format( date_create( $update_measure_date_null ), 'Y-m-d' ); endif;
    if( empty( $update_value_type_null ) ) : $update_value_type = 67; else : $update_value_type = $update_value_type_null; endif;
    if( empty( $update_duration_null ) ) : $update_duration = NULL; else : $update_duration = $update_duration_null; endif;
    if( empty( $update_note_null ) ) : $update_note = NULL; else : $update_note = $update_note_null; endif;
    if( empty( $edit_parent_id ) ) : $update_parent_id = 0; else : $update_parent_id = $edit_parent_id; endif;

    $wpdb->insert( 'data_charity',
    array(
      'entry_date' => $entry_date,
      'record_type' => $record_type,
      'measure' => $update_measure,
      'measure_date' => $update_measure_date,
      'donation_type' => $tag_id,
      'value_type' => $update_value_type,
      'amount' => $update_amount,
      'duration' => $update_duration,
      'location' => $update_donee_location,
      'note' => $update_note,
      'active' => 1,
      'parent_id' => $update_parent_id,
      'user_id' => $user_id,
      'loc_id' => $master_loc
      )
    );

  $last_id = $wpdb->insert_id;

  if( empty( $edit_parent_id ) ) :

    $wpdb->update( 'data_charity',
      array(
        'parent_id' => $last_id,
      ),
      array(
        'id' => $last_id
      )
    );

  endif;

  if( !empty( $update_tags ) ) :

    foreach( $update_tags as $update_tag ) :

      $wpdb->insert( 'data_tag',
        array(
          'data_id' => $last_id,
          'tag_id' => $update_tag,
          'mod_id' => 4
        )
      );

    endforeach;

  endif;

  endforeach;

  if( empty( $add_url ) ) : $query_string = 'edit='.$edit_url.'&start='.$start.'&end='.$end; else : $query_string = 'add='.$add_url; endif;

  header( 'Location:'.$site_url.'/'.$slug.'/?'.$query_string );
  ob_end_flush();

endif;