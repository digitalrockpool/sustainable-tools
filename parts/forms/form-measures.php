<?php 
/* ***

Template Part:  Forms - Measures

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

$entry_date = date( 'Y-m-d H:i:s' );

$edit_measure = $args['edit_measure'];
$edit_measure_date_formatted = $args['edit_measure_date_formatted'];
$edit_measure_end_formatted = $args['edit_measure_end_formatted'];
$edit_bednight = $args['edit_bednight'];
$edit_roomnight = $args['edit_roomnight'];
$edit_client = $args['edit_client'];
$edit_staff = $args['edit_staff'];
$edit_area = $args['edit_area'];
$edit_note = $args['edit_note'];
$edit_parent_id = $args['edit_parent_id'];

if( empty( $edit_measure ) ) : $update_measure = 'edit_measure'; else : $update_measure = $edit_measure; endif; ?>

<form method="post" name="edit" id="<?php echo $update_measure ?>" class="needs-validation" novalidate>

  <div class="form-row"> <?php

    if( $measure_toggle == 86 ) : // custom ?>

    <div class="col-md-4 mb-3">
      <label for="edit-measure-name">Measure<sup class="text-danger">*</sup></label>
      <select class="form-control" name="edit-measure-name" id="edit-measure-name">
        <option value="">Select Measure</option> <?php

        $measure_dropdowns = $wpdb->get_results( "SELECT parent_id, tag FROM custom_tag WHERE loc_id=$master_loc AND cat_id=32 AND active=1 AND id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id) ORDER BY tag ASC" );

        foreach ($measure_dropdowns as $measure_dropdown ) :

          $dropdown_measure_id = $measure_dropdown->parent_id;
          $dropdown_measure = $measure_dropdown->tag;

          if( $edit_measure_id == $dropdown_measure_id ) : $selected = 'selected'; else : $selected = ''; endif; ?>

          <option value="<?php echo $dropdown_measure_id ?>" <?php echo $selected ?>><?php echo $dropdown_measure ?></option> <?php

        endforeach; ?>

      </select>
    </div> <?php

    endif;

    if( $measure_toggle == 83 ) : // weekly ?>

      <div class="col-md-4 mb-3">
        <label class="control-label" for="edit-measure-week">Measure Week<sup class="text-danger">*</sup></label>
        <div class="input-group mb-2">
          <select class="custom-select" name="edit-measure-week" id="edit-measure-week"> <?php

            if( empty( $edit_measure_date_formatted ) ) : $selected_week = date( 'W', strtotime( '-1 week' ) ); else : $selected_week = date_format( date_create( $edit_measure_date_formatted ), 'W' ); endif;

            foreach ( range( 1, 52 ) as $i ) :

            if( $selected_week == $i ) : $selected = 'selected'; else : $selected = ''; endif;

              echo '<option value="'.$i.'" '.$selected.'>Week '.$i.'</option>';

            endforeach; ?>

          </select>

          <select class="custom-select" name="edit-measure-year" id="edit-measure-year"> <?php

            if( empty( $edit_measure_date_formatted ) ) : $selected_year = date( 'Y' ); else : $selected_year = date_format( date_create( $edit_measure_date_formatted ), 'Y' ); endif;

            $earliest_year = date( 'Y',strtotime( '-10 year' ) );
            $latest_year = date( 'Y' );

            foreach ( range( $latest_year, $earliest_year ) as $i ) :

              if( $selected_year == $i ) : $selected = 'selected'; else : $selected = ''; endif;

              echo '<option value="'.$i.'" '.$selected.'>'.$i.'</option>';

            endforeach; ?>

          </select>
        </div>
      </div> <?php

    elseif( $measure_toggle == 84 ) : // monthly ?>

      <div class="col-md-4 mb-3">
        <label class="control-label" for="edit-measure-month">Measure Month<sup class="text-danger">*</sup></label>
        <div class="input-group mb-2">
          <select class="custom-select" name="edit-measure-month" id="edit-measure-month"> <?php

            if( empty( $edit_measure_date_formatted ) ) : $selected_month = date( 'n', strtotime( '-1 month' ) ); else : $selected_month = date_format( date_create( $edit_measure_date_formatted ), 'n' ); endif;

            foreach ( range( 1, 12 ) as $i ) :

            if( $selected_month == $i ) : $selected = 'selected'; else : $selected = ''; endif;

              echo '<option value="'.$i.'" '.$selected.'>'.date_format(date_create('1900-'.$i.'-1'),"F").'</option>';

            endforeach; ?>

          </select>

          <select class="custom-select" name="edit-measure-year" id="edit-measure-year"> <?php

            if( empty( $edit_measure_date_formatted ) ) :

              $current_month = date( 'n' ); echo $current_month;

              if( $current_month == 1 ) : $selected_year = date( 'Y', strtotime( '-1 year' ) ); else : $selected_year = date( 'Y' ); endif;

            else : $selected_year = date_format( date_create( $edit_measure_date_formatted ), 'Y' ); endif;

            $earliest_year = date( 'Y',strtotime( '-10 year' ) );
            $latest_year = date( 'Y' );

            foreach ( range( $latest_year, $earliest_year ) as $i ) :

              if( $selected_year == $i ) : $selected = 'selected'; else : $selected = ''; endif;

              echo '<option value="'.$i.'" '.$selected.'>'.$i.'</option>';

            endforeach; ?>

          </select>
        </div>
      </div> <?php

    elseif( $measure_toggle == 85 ) : // yearly

      reporting_year_start_date( $edit_measure_date_formatted );

    else : ?>

      <div class="col-md-4 mb-3">
        <label class="control-label" for="edit-measure-date">Measure Date<sup class="text-danger">*</sup></label>
        <div class="input-group mb-2">
          <div class="input-group-prepend"><div class="input-group-text"><i class="far fa-calendar-alt"></i></div></div>
          <input type="text" class="form-control date" name="edit-measure-date" id="edit-measure-date" aria-describedby="editMeasureStart" placeholder="dd-mmm-yyyy" value="<?php if( empty( $edit_url ) && $add_url != 'measures' ) : echo date('d-M-Y'); elseif( $add_url == 'measures' ) : echo ''; else : echo date_format( date_create( $edit_measure_date_formatted ), 'd-M-Y' ); endif; ?>" data-date-end-date="0d" required>
        </div>
      </div> <?php

    endif;

    if( $measure_toggle == 86 ) : // custom

      if( !empty( $edit_measure_end_formatted ) ) : $selected_date = date_format( date_create( $edit_measure_end_formatted ), "d-M-Y" ); endif;  ?>

      <div class="col-md-4 mb-3">
        <label class="control-label" for="edit-measure-end">Measure End Date<sup class="text-danger">*</sup></label>
        <div class="input-group mb-2">
          <div class="input-group-prepend"><div class="input-group-text"><i class="far fa-calendar-alt"></i></div></div>
          <input type="text" class="form-control date" name="edit-measure-end" id="edit-measure-end" aria-describedby="editMeasureEnd" placeholder="dd-mmm-yyyy" value="<?php echo $selected_date ?>" data-date-end-date="0d" required>
        </div>
      </div> <?php

    endif; ?>

  </div>

  <div class="form-row">

    <div class="col-md-6 mb-3">
      <label for="edit-bednight">Bed Nights</label>
      <input type="number" class="form-control" name="edit-bednight" id="edit-bednight" aria-describedby="editBedNight" nameplaceholder="Bed Nights" min="1" step="1" value="<?php echo $edit_bednight ?>">
      <div class="invalid-feedback">Please enter a whole number greater than or equal to 1</div>
    </div>

    <div class="col-md-6 mb-3">
      <label for="edit-roomnight">Room Nights</label>
      <input type="number" class="form-control" name="edit-roomnight" id="edit-roomnight" aria-describedby="editRoomNight" nameplaceholder="Room Nights" min="1" step="1" value="<?php echo $edit_roomnight ?>">
      <div class="invalid-feedback">Please enter a whole number greater than or equal to 1</div>
    </div>

  </div>

  <div class="form-row">

    <div class="col-md-6 mb-3">
      <label for="edit-client">Clients</label>
      <input type="number" class="form-control" name="edit-client" id="edit-client" aria-describedby="editClient" nameplaceholder="Clients" min="1" step="1" value="<?php echo $edit_client ?>">
      <div class="invalid-feedback">Please enter a whole number greater than or equal to 1</div>
    </div>

    <div class="col-md-6 mb-3">
      <label for="edit-staff">Staff</label>
      <input type="number" class="form-control" name="edit-staff" id="edit-staff" aria-describedby="editStaff" nameplaceholder="Staff" min="1" step="1" value="<?php echo $edit_staff ?>">
      <div class="invalid-feedback">Please enter a whole number greater than or equal to 1</div>
    </div>

  </div>

  <div class="form-row">

    <div class="col-md-6 mb-3">
      <label for="edit-area">Area (m2)</label>
      <input type="number" class="form-control" name="edit-area" id="edit-area" aria-describedby="editArea" nameplaceholder="Area" min="1" step="1" value="<?php echo $edit_area ?>">
      <div class="invalid-feedback">Please enter a whole number greater than or equal to 1</div>
    </div>

  </div>

  <div class="form-row">

    <div class="col-12 mb-3">
      <label for="edit-note">Notes</label>
        <textarea class="form-control" name="edit-note" id="edit-note" aria-describedby="editNote" placeholder="Notes"><?php echo $edit_note ?></textarea>
    </div>

  </div>

  <div class="form-row">

    <div class="col-12 mb-3"><button class="btn btn-primary" type="submit" name="<?php echo $update_measure ?>"><?php if( empty( $add_url ) ) : echo 'Update'; else : echo 'Add'; endif; echo ' '.str_replace( '-', ' ', $add_url ); ?></button></div>

  </div>

</form> <?php

$update_measure_name_null = $_POST['edit-measure-name'];
$update_measure_date = $_POST['edit-measure-date'];
$update_measure_week = $_POST['edit-measure-week'];
$update_measure_month = $_POST['edit-measure-month'];
$update_measure_year = $_POST['edit-measure-year'];
$update_measure_end_null = $_POST['edit-measure-end'];
$update_bednight_null = $_POST['edit-bednight'];
$update_roomnight_null = $_POST['edit-roomnight'];
$update_client_null = $_POST['edit-client'];
$update_staff_null = $_POST['edit-staff'];
$update_area_null = $_POST['edit-area'];
$update_note_null = $_POST['edit-note'];

if( empty( $add ) ) : $record_type = 'entry_revision'; else : $record_type = 'entry'; endif;

if( $measure_toggle == 83 ) : // weekly

  $week_start = new DateTime();
  $week_start->setISODate( $update_measure_year, $update_measure_week );
  $update_measure_start = $week_start->format('Y-m-d');

elseif( $measure_toggle == 84 ) : // monthly

  $month_start = $update_measure_year.'-'.$update_measure_month.'-01';
  $update_measure_start = date( 'Y-m-d', strtotime( $month_start ) );

else :

  $update_measure_start = date_format( date_create( $update_measure_date), 'Y-m-d' );

endif;

if( empty( $update_measure_name_null ) ) : $update_measure_name = NULL; else : $update_measure_name = $update_measure_name_null; endif;
if( empty( $update_measure_end_null ) ) : $update_measure_end = NULL; else : $update_measure_end = date_format( date_create( $update_measure_end_null), 'Y-m-d' ); endif;
if( empty( $update_bednight_null ) ) : $update_bednight = NULL; else : $update_bednight = $update_bednight_null; endif;
if( empty( $update_roomnight_null ) ) : $update_roomnight = NULL; else : $update_roomnight = $update_roomnight_null; endif;
if( empty( $update_client_null ) ) : $update_client = NULL; else : $update_client = $update_client_null; endif;
if( empty( $update_staff_null ) ) : $update_staff = NULL; else : $update_staff = $update_staff_null; endif;
if( empty( $update_area_null ) ) : $update_area = NULL; else : $update_area = $update_area_null; endif;
if( empty( $update_note_null ) ) : $update_note = NULL; else : $update_note = $update_note_null; endif;
if( empty( $edit_parent_id ) ) : $update_parent_id = 0; else : $update_parent_id = $edit_parent_id; endif;

if ( isset( $_POST[$update_measure] ) ) :

  $wpdb->insert( 'data_measure',
    array(
      'entry_date' => $entry_date,
      'record_type' => $record_type,
      'measure_type' => $measure_toggle,
      'measure_name' => $update_measure_name,
      'measure_start' => $update_measure_start,
      'measure_end' => $update_measure_end ,
      'bednight' => $update_bednight,
      'roomnight' => $update_roomnight,
      'client' => $update_client,
      'staff' => $update_staff,
      'area' => $update_area,
      'note' => $update_note,
      'active' => 1,
      'parent_id' => $update_parent_id,
      'user_id' => $user_id,
      'loc_id' => $master_loc
    )
  );

  if( empty( $edit_parent_id ) ) :

    $parent_id = $wpdb->insert_id;

    $wpdb->update( 'data_measure',
      array(
        'parent_id' => $parent_id,
      ),
      array(
        'id' => $parent_id
      )
    );

  endif;

  if( empty( $add_url ) ) : $query_string = 'edit='.$edit_url.'&start='.$start.'&end='.$end; else : $query_string = 'add='.$add_url; endif;

  header( 'Location:'.$site_url.'/'.$slug.'/?'.$query_string );
  ob_end_flush();

endif;