<?php 
/* ***

Template Part:  Forms - Operations

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

$entry_date = date( 'Y-m-d H:i:s' );

if( isset( $_GET['add'] ) && !empty( $_GET['add'] ) ) : $add_url = $_GET['add']; endif;

if( isset( $_GET['edit'] ) && !empty( $_GET['edit'] ) ) : 
  $edit_url = $_GET['edit'];
  $edit_operations = $args['edit_operations'];
  $edit_id = $args['edit_id'];
  $edit_measure = $args['edit_measure'];
  $edit_measure_date_formatted = $args['edit_measure_date_formatted'];
  $edit_utility_id = $args['edit_utility_id'];
  $edit_amount = $args['edit_amount'];
  $edit_cost = $args['edit_cost'];
  $edit_disposal = $args['edit_disposal'];
  $edit_disposal_id = $args['edit_disposal_id'];
  $edit_note = $args['edit_note'];
  $edit_parent_id = $args['edit_parent_id'];
 endif;

if( isset( $_GET['start'] ) && !empty( $_GET['start'] ) ) : $start = $_GET['start']; endif;
if( isset( $_GET['end'] ) && !empty( $_GET['end'] ) ) : $end = $_GET['end']; endif;

$cat_id = $args['cat_id'];

if( empty( $edit_operations ) ) : $update_operations = 'edit_operations'; else : $update_operations = $edit_operations; endif; ?>

<form method="post" name="edit" id="<?php echo $update_operations ?>" class="needs-validation" novalidate>
  <div class="row"> <?php

    if( $measure_toggle == 86 ) : // custom measures

      custom_measure_dropdown( $edit_measure );

    elseif( $measure_toggle == 85 ) : // yearly measure

      reporting_year_start_date( $edit_measure_date_formatted );

    else : ?>

      <div class="col-md-4 mb-3">
        <label for="edit-measure-date">Date<sup class="text-danger">*</sup></label>
        <div class="input-group has-validation">
          <span class="input-group-text"><i class="fa-regular fa-calendar-days"></i></span>
          <input type="text" class="form-control date" name="edit-measure-date" id="edit-measure-date" aria-describedby="editMeasureDate" placeholder="dd-mmm-yyyy" value="<?php if( empty( $edit_url ) ) : echo date( 'd-M-Y', strtotime( '-1 day' ) ); else : echo $edit_measure_date_formatted; endif; ?>" data-date-end-date="0d" required>
          <div class="invalid-feedback">Please select a date</div>
        </div>
      </div>

      <div class="col-md-6 mb-3 d-flex align-items-end"> <?php

        if( $measure_toggle == 84 || $measure_toggle == 83 ) : // monthly or weekly measures ?>
          <small>If this entry is for a period of time the amount will be added to the <?php if( $measure_toggle == 84 ) : echo 'month'; elseif( $measure_toggle == 83 ) : echo 'week'; endif; ?> of the selected date.</small> <?php
        endif; ?>

      </div><?php

    endif; ?>

  </div> <?php

  if( empty( $edit_url ) ) : ?>

    <div id="repeater-field">
      <div class="entry row g-1 mb-1">
        <div class="col-4">
          <select class="form-select edit-utility" id="edit-utility" name="edit-utility[]" required>
            <option value="" selected disabled>Select <?php echo ucfirst( str_replace( '-', ' ', $add_url ) ); ?> *</option> <?php

            if( $add_url == 'plastic' ) :

              $utility_dropdowns = $wpdb->get_results( "SELECT custom_tag.tag_id, system_tag.tag AS system_tag, custom_tag.tag AS custom_tag, size, unit_tag.tag AS unit_tag, parent_id FROM custom_tag INNER JOIN master_tag system_tag ON system_tag.id=custom_tag.tag_id INNER JOIN master_tag unit_tag ON unit_tag.id=custom_tag.unit_id WHERE custom_tag.cat_id=40 AND loc_id=$master_loc AND active=1 AND custom_tag.id IN (SELECT MAX(custom_tag.id) FROM custom_tag GROUP BY parent_id)" );

              foreach( $utility_dropdowns as $utility_dropdown ) :

                $system_tag = $utility_dropdown->system_tag;
                $custom_tag = $utility_dropdown->custom_tag;
                $size = $utility_dropdown->size;
                $unit_tag = $utility_dropdown->unit_tag;

                if( !empty( $custom_tag ) && empty( $size ) ) :
                  $plastic_dropdown = $system_tag.' - '.$custom_tag;
                elseif( empty( $custom_tag ) && !empty( $size ) ) :
                  $plastic_dropdown = $system_tag.' ('.$size.' '.$unit_tag.')';
                elseif( !empty( $custom_tag ) && !empty( $size ) ) :
                  $plastic_dropdown = $system_tag.' - '.$custom_tag.' ('.$size.' '.$unit_tag.')';
                else :
                  $plastic_dropdown = $system_tag;
                endif; ?>

                <option value="<?php echo $utility_dropdown->parent_id ?>"><?php echo $plastic_dropdown ?></option> <?php

              endforeach;

            else :

              $utility_dropdowns = $wpdb->get_results( "SELECT custom_tag.parent_id, master_tag.tag FROM master_tag INNER JOIN custom_tag ON master_tag.id=custom_tag.tag_id WHERE custom_tag.cat_id=$cat_id AND active=1 AND custom_tag.id IN (SELECT MAX(custom_tag.id) FROM custom_tag GROUP BY parent_id) AND loc_id=$master_loc ORDER BY master_tag.tag ASC" );

              foreach( $utility_dropdowns as $utility_dropdown ) : ?>
                <option value="<?php echo $utility_dropdown->parent_id ?>"><?php echo $utility_dropdown->tag ?></option> <?php
              endforeach;

            endif; ?>

          </select>
          <div class="invalid-feedback">Please select <?php echo str_replace( '-', ' ', $add_url ) ?></div>
        </div>

        <div class="<?php if( $add_url == 'waste' ) : echo 'col-md-2'; else : echo 'col-md-3'; endif; ?> mb-1">
        <input type="number" class="form-control" name="edit-amount[]" id="edit-amount" aria-describedby="editAmount" placeholder="<?php if( $add_url == 'plastic' ) : echo 'Number Purchase'; else : echo 'Amount'; endif; ?> *" value="" min="1" step="0.01" required>
        <div class="invalid-feedback">Please enter a number greater than or equal to 0.01</div>
      </div>

      <div class="<?php if( $add_url == 'waste' ) : echo 'col-md-2'; else : echo 'col-md-3'; endif; ?> mb-1">
        <input type="number" class="form-control" name="edit-cost[]" id="edit-cost" aria-describedby="editCost" placeholder="<?php if( $add_url == 'plastic' ) : echo 'Total Cost'; else : echo 'Cost'; endif; ?>" value="" min="1" step="0.01">
        <div class="invalid-feedback">Please enter a number greater than or equal to 0.01</div>
      </div> <?php

      if( $add_url == 'waste' ) : ?>

        <div class="col-3 mb-1">
            <select class="form-select edit-disposal" name="edit-disposal[]" id="edit-disposal" required>
            <option value="" selected disabled>Select Waste Disposal *</option> <?php

            $disposal_dropdowns = $wpdb->get_results( "SELECT custom_tag.tag_id, master_tag.tag FROM master_tag INNER JOIN custom_tag ON master_tag.id=custom_tag.tag_id WHERE custom_tag.cat_id=16 AND loc_id=$master_loc AND active=1 GROUP BY parent_id ORDER BY master_tag.tag ASC" );

            foreach( $disposal_dropdowns as $disposal_dropdown ) : ?>
              <option value="<?php echo $disposal_dropdown->tag_id ?>"><?php echo $disposal_dropdown->tag ?></option> <?php
            endforeach; ?>

          </select>
          <div class="invalid-feedback">Please select disposal method</div>
        </div>  <?php

      endif; ?>

        <div class="col-1">
          <button type="button" class="btn btn-success btn-add"><i class="fa-solid fa-plus"></i></button>
        </div>
      </div>
    </div> <?php

  else : ?>

    <div class="form-row">

      <div class="<?php if( $edit_url == 'waste' ) : echo 'col-md-4'; else : echo 'col-md-6'; endif; ?> mb-3">
        <label for="edit-amount"><?php if( $edit_url == 'plastic' ) : echo 'Number Purchase'; else : echo 'Amount'; endif; ?><sup class="text-danger">*</sup></label>
        <input type="number" class="form-control" name="edit-amount" id="edit-amount" aria-describedby="editAmount" nameplaceholder="Amount" value="<?php echo $edit_amount ?>" min="1" step="0.01" required>
        <div class="invalid-feedback">Please enter a number greater than or equal to 0.01</div>
      </div>

      <div class="<?php if( $edit_url == 'waste' ) : echo 'col-md-4'; else : echo 'col-md-6'; endif; ?> mb-3">
        <label for="edit-cost"><?php if( $edit_url == 'plastic' ) : echo 'Total Cost'; else : echo 'Cost'; endif; ?></label>
        <input type="number" class="form-control" name="edit-cost" id="edit-cost" aria-describedby="editCost" placeholder="Cost" value="<?php echo $edit_cost ?>" min="1" step="0.01">
        <div class="invalid-feedback">Please enter a number greater than or equal to 0.01</div>
      </div> <?php

      if( $edit_url == 'waste' ) : ?>

        <div class="col-4 mb-3">
          <label for="edit-disposal">Waste Disposal Method<sup class="text-danger">*</sup></label>
            <select class="form-control" name="edit-disposal" id="edit-disposal" required> <?php

            $disposal_dropdowns = $wpdb->get_results( "SELECT relation_tag.child_id, child_tag.tag AS child FROM master_tag INNER JOIN relation_tag ON master_tag.id=relation_tag.parent_id INNER JOIN master_tag child_tag ON child_tag.id=relation_tag.child_id INNER JOIN custom_tag ON master_tag.id=custom_tag.tag_id WHERE custom_tag.parent_id=$edit_utility_id AND relation='waste-disposal' AND custom_tag.active=1 AND custom_tag.loc_id=$master_loc GROUP BY child" );

            foreach( $disposal_dropdowns as $disposal_dropdown ) :

              $edit_waste_id = $disposal_dropdown->child_id;
              $edit_waste_tag = $disposal_dropdown->child;

              if( $edit_waste_id == $edit_disposal_id ) : $selected = 'selected'; else : $selected = ''; endif; ?>

              <option value="<?php echo $edit_waste_id ?>" <?php echo $selected ?>><?php echo $edit_waste_tag ?></option> <?php
            endforeach; ?>

          </select>
          <div class="invalid-feedback">Please select disposal method</div>
        </div>  <?php

      endif; ?>

    </div> <?php

  endif;

  if( $tag_toggle == 1 ) : ?>

    <h5 class="border-top pt-3 mt-3">Tags</h5>

    <div class="form-row">

      <div class="col-12 mb-1">

        <select class="selectpicker form-control" name="edit-tag[]" multiple title="Select Tags" data-live-search="true"> <?php
          $tag_dropdowns = $wpdb->get_results( "SELECT parent_id, tag FROM custom_tag WHERE loc_id=$master_loc AND cat_id=22 AND tag IS NOT NULL AND active=1 AND id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id) ORDER BY tag ASC" );

          foreach ($tag_dropdowns as $tag_dropdown ) :

            $dropdown_parent_id = $tag_dropdown->parent_id;
            $dropdown_tag = $tag_dropdown->tag;

            $edit_tag_id = $wpdb->get_results( "SELECT tag_id FROM data_tag WHERE data_id=$edit_id AND mod_id=2", ARRAY_N );
            $data_array = array_map( function ($arr) {return $arr[0];}, $edit_tag_id );

            if( in_array($dropdown_parent_id, $data_array ) ) : $selected = 'selected'; else : $selected = ''; endif; ?>

            <option value="<?php echo $dropdown_parent_id ?>" <?php echo $selected ?>><?php echo $dropdown_tag ?></option> <?php

          endforeach; ?>
        </select>
      </div>

    </div> <?php

  endif; ?>

  <h5 class="border-top pt-3 mt-3">Notes</h5>

  <div class="form-row">

    <div class="col-12 mb-3">
      <label for="edit-note">Please enter any notes for this entry</label>
        <textarea class="form-control" name="edit-note" id="edit-note" aria-describedby="editNote" placeholder="Notes"><?php if( isset($edit_note) ) : echo $edit_note; endif; ?></textarea>
    </div>

  </div>

  <div class="form-row">

    <div class="col-12 mb-3"><button class="btn btn-primary" type="submit" name="<?php echo $update_operations ?>"><?php if( empty( $add_url ) ) : echo 'Update'; else : echo 'Add'; endif; echo ' '.str_replace( '-', ' ', $add_url ); ?></button></div>

  </div>
</form> <?php

if ( isset( $_POST[$update_operations] ) && empty( $edit_url ) ) :

  $update_utility_array = $_POST['edit-utility'];
  $update_amount_array = $_POST['edit-amount'];
  $update_cost_array = $_POST['edit-cost'];
  $update_disposal_array = $_POST['edit-disposal'];

  foreach( $update_utility_array as $index => $update_utility_array ) :

    $update_measure_null = $_POST['edit-measure'];
    $update_measure_date_null = $_POST['edit-measure-date'];
    $update_utility_id = $update_utility_array;
    $update_amount = $update_amount_array[$index];
    $update_cost_null = $update_cost_array[$index];
    $update_disposal_null = $update_disposal_array[$index];
    $update_tags = $_POST['edit-tag'];
    $update_note_null = $_POST['edit-note'];

    if( empty( $update_measure_null ) ) : $update_measure = NULL; else : $update_measure = $update_measure_null; endif;
    if( empty( $update_measure_date_null ) ) : $update_measure_date = NULL; else : $update_measure_date = date_format( date_create( $update_measure_date_null ), 'Y-m-d' ); endif;
    if( empty( $update_cost_null ) ) : $update_cost = NULL; else : $update_cost = $update_cost_null; endif;
    if( empty( $update_disposal_null ) ) : $update_disposal = NULL; else : $update_disposal = $update_disposal_null; endif;
    if( empty( $update_note_null ) ) : $update_note = NULL; else : $update_note = $update_note_null; endif;

    $wpdb->insert( 'data_operations',
      array(
        'entry_date' => $entry_date,
        'record_type' => 'entry',
        'measure' => $update_measure,
        'measure_date' => $update_measure_date,
        'utility_id' => $update_utility_id,
        'disposal_id' => $update_disposal,
        'amount' => $update_amount,
        'cost' => $update_cost,
        'note' => $update_note,
        'active' => 1,
        'parent_id' => 0,
        'user_id' => $user_id,
        'loc_id' => $master_loc
      )
    );

    $last_id = $wpdb->insert_id;

    if( empty( $edit_parent_id ) ) :

      $wpdb->update( 'data_operations',
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
            'mod_id' => 2
          )
        );

      endforeach;

    endif;

  endforeach;

  header( 'Location:'.$site_url.'/'.$slug.'/?add='.$add_url );
  ob_end_flush();

endif;

if ( isset( $_POST[$update_operations] ) && empty( $add_url ) ) :

  $update_measure_null = $_POST['edit-measure'];
  $update_measure_date_null = $_POST['edit-measure-date'];
  $update_amount = $_POST['edit-amount'];
  $update_cost_null = $_POST['edit-cost'];
  $update_disposal_null = $_POST['edit-disposal'];
  $update_tags = $_POST['edit-tag'];
  $update_note_null = $_POST['edit-note'];

  if( empty( $update_measure_null ) ) : $update_measure = NULL; else : $update_measure = $update_measure_null; endif;
  if( empty( $update_measure_date_null ) ) : $update_measure_date = NULL; else : $update_measure_date = date_format( date_create( $update_measure_date_null ), 'Y-m-d' ); endif;
  if( empty( $update_cost_null ) ) : $update_cost = NULL; else : $update_cost = $update_cost_null; endif;
  if( empty( $update_disposal_null ) ) : $update_disposal = NULL; else : $update_disposal = $update_disposal_null; endif;
  if( empty( $update_note_null ) ) : $update_note = NULL; else : $update_note = $update_note_null; endif;

  $wpdb->insert( 'data_operations',
    array(
      'entry_date' => $entry_date,
      'record_type' => 'entry_revision',
      'measure' => $update_measure,
      'measure_date' => $update_measure_date,
      'utility_id' => $edit_utility_id,
      'disposal_id' => $update_disposal,
      'amount' => $update_amount,
      'cost' => $update_cost,
      'note' => $update_note,
      'active' => 1,
      'parent_id' => $edit_parent_id,
      'user_id' => $user_id,
      'loc_id' => $master_loc
    )
  );

  $last_id = $wpdb->insert_id;

  if( !empty( $update_tags ) ) :

    foreach( $update_tags as $update_tag ) :

      $wpdb->insert( 'data_tag',
        array(
          'data_id' => $last_id,
          'tag_id' => $update_tag,
          'mod_id' => 2
        )
      );

    endforeach;

  endif;

  header( 'Location:'.$site_url.'/'.$slug.'/?edit='.$edit_url.'&start='.$start.'&end='.$end );
  ob_end_flush();

endif; ?>

<script>
  $(document).on('change', '.edit-utility', function(){
    var disposalPOP = $(this).val();
    var dropDown = $(this).parent().parent().find(".edit-disposal");
    dropDown.empty();
    $.ajax({
      url:"<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php",
      type:'GET',
      data:'action=operations_disposal_edit&utilityID=' + disposalPOP,
      success:function(results) {
        dropDown.append(results);
      }
    });
  });
</script> <?php
