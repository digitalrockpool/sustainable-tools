<?php 
/* ***

Template Part:  Forms - Labour

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
$edit_labour = $args['edit_labour'];
$edit_id = $args['edit_id'];
$edit_measure = $args['edit_measure'];
$edit_measure_date_formatted = $args['edit_measure_date_formatted'];
$edit_hometown_id = $args['edit_hometown_id'];
$edit_gender_id = $args['edit_gender_id'];
$edit_ethnicity_id = $args['edit_ethnicity_id'];
$edit_disability_id = $args['edit_disability_id'];
$edit_level_id = $args['edit_level_id'];
$edit_role_id = $args['edit_role_id'];
$edit_part_time_id = $args['edit_part_time_id'];
$edit_promoted_id = $args['edit_promoted_id'];
$edit_under16_id = $args['edit_under16_id'];
$edit_start_date = $args['edit_start_date'];
$edit_start_date_formatted = $args['edit_start_date_formatted'];
$edit_leave_date = $args['edit_leave_date'];
$edit_leave_date_formatted = $args['edit_leave_date_formatted'];
$edit_days_worked = $args['edit_days_worked'];
$edit_time_mentored = $args['edit_time_mentored'];
$edit_contract_dpw = $args['edit_contract_dpw'];
$edit_contract_wpy = $args['edit_contract_wpy'];
$edit_annual_leave = $args['edit_annual_leave'];
$edit_salary = $args['edit_salary'];
$edit_overtime = $args['edit_overtime'];
$edit_bonuses = $args['edit_bonuses'];
$edit_gratuities = $args['edit_gratuities'];
$edit_benefits = $args['edit_benefits'];
$edit_cost_training = $args['edit_cost_training'];
$edit_training_days = $args['edit_training_days'];
$edit_note = $args['edit_note'];
$edit_parent_id = $args['edit_parent_id'];

$master_contract_dpw = $wpdb->get_row( "SELECT tag FROM custom_tag WHERE tag_id=281 AND loc_id=$master_loc AND active=1 AND id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id )" );
$master_contract_wpy = $wpdb->get_row( "SELECT tag FROM custom_tag WHERE tag_id=282 AND loc_id=$master_loc AND active=1 AND id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id )" );
$master_annual_leave = $wpdb->get_row( "SELECT tag FROM custom_tag WHERE tag_id=283 AND loc_id=$master_loc AND active=1 AND id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id )" );

if( empty( $edit_contract_dpw ) ) : $contract_dpw = $master_contract_dpw->tag; else : $contract_dpw = $edit_contract_dpw; endif;
if( empty( $edit_contract_wpy ) ) : $contract_wpy = $master_contract_wpy->tag; else : $contract_wpy = $edit_contract_wpy; endif;
if( empty( $edit_annual_leave ) ) : $annual_leave = $master_annual_leave->tag; else : $annual_leave = $edit_annual_leave; endif;

if( empty( $edit_labour ) ) : $update_labour = 'edit_labour'; else : $update_labour = $edit_labour; endif; ?>

<form method="post" name="edit" id="<?php echo $update_labour ?>" class="needs-validation" novalidate>
  <div class="form-group row"> <?php

    if( $measure_toggle == 86 && $tag_id != 69 && $tag_id != 70 && $tag_id != 71 ) : // custom measures || permanent || seasonal || fixed term

        custom_measure_dropdown( $edit_measure );

    elseif( $measure_toggle == 85 || $tag_id == 69 || $tag_id == 70 || $tag_id == 71 ) : // yearly measure || permanent || seasonal || fixed term

        reporting_year_start_date( $edit_measure_date_formatted );

    else : ?>

      <div class="col-md-4 mb-3">
        <label for="edit-measure-date">Date of <?php if( $tag_id == 73 ) : echo 'Internship'; else : echo 'Work'; endif; ?><sup class="text-danger">*</sup></label>
        <div class="input-group mb-2">
          <span class="input-group-text"><i class="fa-regular fa-calendar-days"></i></span>
          <input type="text" class="form-control date" name="edit-measure-date" id="edit-measure-date" aria-describedby="editMeasureDate" placeholder="dd-mmm-yyyy" value="<?php if( empty( $edit_url ) ) : echo date('d-M-Y'); else : echo $edit_measure_date_formatted; endif; ?>" data-date-end-date="0d" required>
        </div>
        <div class="invalid-feedback">Please select a date</div>
      </div>

      <div class="col-md-4 mb-2 d-flex align-items-end"> <?php

        if( $measure_toggle == 84 || $measure_toggle == 83 ) : // monthly || weekly measures ?>
          <small>If this entry is for a period of time the amount will be added to the <?php if( $measure_toggle == 84 ) : echo 'month'; elseif( $measure_toggle == 83 ) : echo 'week'; endif; ?> of the selected date.</small> <?php
        endif; ?>

      </div> <?php

    endif; ?>

  </div>

  <h5 class="border-top pt-3">Employee</h5>

  <div class="row g-1">
    <div class="col-md-4 mb-3">
      <label for="edit-hometown">Hometown<sup class="text-danger">*</sup></label>
      <select class="form-select" name="edit-hometown" id="edit-hometown" required> <?php
        if( empty( $edit_url ) ) : ?> <option value="">Select Hometown</option> <?php endif; ?>
        <option value="0">Unknown Location</option> <?php

        $hometown_dropdowns = $wpdb->get_results( "SELECT parent_id, location FROM custom_location WHERE loc_id=$master_loc AND active=1 AND id IN (SELECT MAX(id) FROM custom_location GROUP BY parent_id) ORDER BY location DESC" );

        foreach ($hometown_dropdowns as $hometown_dropdown ) :

          $dropdown_hometown_id = $hometown_dropdown->parent_id;
          $dropdown_hometown = $hometown_dropdown->location;

          if( $edit_hometown_id == $dropdown_hometown_id ) : $selected = 'selected'; else : $selected = ''; endif; ?>

          <option value="<?php echo $dropdown_hometown_id ?>" <?php echo $selected ?>><?php echo $dropdown_hometown ?></option> <?php

        endforeach; ?>

      </select>
      <div class="invalid-feedback">Please select hometown</div>
    </div>

    <div class="col-md-4 mb-3">
      <label for="edit-gender">Gender<sup class="text-danger">*</sup></label>
      <select class="form-select" name="edit-gender" id="edit-gender" required> <?php
        if( empty( $edit_url ) ) : ?> <option value="">Select Gender</option> <?php endif;

        $gender_dropdowns = $wpdb->get_results( "SELECT id, tag FROM master_tag WHERE cat_id=7 ORDER BY tag ASC" );

        foreach ($gender_dropdowns as $gender_dropdown ) :

          $dropdown_gender_id = $gender_dropdown->id;
          $dropdown_gender = $gender_dropdown->tag;

          if( $edit_gender_id == $dropdown_gender_id ) : $selected = 'selected'; else : $selected = ''; endif; ?>

          <option value="<?php echo $dropdown_gender_id ?>" <?php echo $selected ?>><?php echo $dropdown_gender ?></option> <?php

        endforeach; ?>

      </select>
      <div class="invalid-feedback">Please select gender</div>
    </div>

    <div class="col-md-4 mb-3">
      <label for="edit-ethnicity">Ethnicity</label>
      <select class="form-select" name="edit-ethnicity" id="edit-ethnicity">
        <option value="">Select Ethnicity</option> <?php

        $ethnicity_dropdowns = $wpdb->get_results( "SELECT parent_id, tag FROM custom_tag WHERE loc_id=$master_loc AND cat_id=20 AND active=1 AND id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id) ORDER BY tag ASC" );

        foreach ($ethnicity_dropdowns as $ethnicity_dropdown ) :

          $dropdown_ethnicity_id = $ethnicity_dropdown->parent_id;
          $dropdown_ethnicity = $ethnicity_dropdown->tag;

          if( $edit_ethnicity_id == $dropdown_ethnicity_id ) : $selected = 'selected'; else : $selected = ''; endif; ?>

          <option value="<?php echo $dropdown_ethnicity_id ?>" <?php echo $selected ?>><?php echo $dropdown_ethnicity ?></option> <?php

        endforeach; ?>

      </select>
    </div>
  </div>

  <div class="row g-1">
    <div class="col-md-4 mb-3">
      <label for="edit-role">Role</label>
      <select class="form-select" name="edit-role" id="edit-role">
        <option value="">Select Role</option> <?php

        $role_dropdowns = $wpdb->get_results( "SELECT parent_id, tag FROM custom_tag WHERE loc_id=$master_loc AND cat_id=21 AND active=1 AND id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id) ORDER BY tag ASC" );

        foreach ($role_dropdowns as $role_dropdown ) :

          $dropdown_role_id = $role_dropdown->parent_id;
          $dropdown_role = $role_dropdown->tag;

          if( $edit_role_id == $dropdown_role_id ) : $selected = 'selected'; else : $selected = ''; endif; ?>

          <option value="<?php echo $dropdown_role_id ?>" <?php echo $selected ?>><?php echo $dropdown_role ?></option> <?php

        endforeach; ?>

      </select>
    </div>

    <div class="col-md-4 mb-3"> <?php

      if( $edit_disability_id == 1 ) : $checked_yes = 'checked'; $checked_no = '';  else : $checked_yes = ''; $checked_no = 'checked'; endif; ?>

      <label class="d-block">Does employee have a disability?<sup class="text-danger">*</sup></label>
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="edit-disability" id="edit-disability-yes" value="1" <?php echo $checked_yes ?>>
        <label class="form-check-label" for="edit-disability-yes">Yes</label>
      </div>
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="edit-disability" id="edit-disability-no" value="0" <?php echo $checked_no ?>>
        <label class="form-check-label" for="edit-disability-no">No</label>
      </div>
    </div>

    <div class="col-md-4 mb-3"> <?php

      if( $edit_under16_id == 1 ) : $checked_yes = 'checked'; $checked_no = '';  else : $checked_yes = ''; $checked_no = 'checked'; endif; ?>

      <label class="d-block">Is employee under 16?<sup class="text-danger">*</sup></label>
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="edit-under16" id="edit-under16-yes" value="1" <?php echo $checked_yes ?>>
        <label class="form-check-label" for="edit-under16-yes">Yes</label>
      </div>
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="edit-under16" id="edit-under16-no" value="0" <?php echo $checked_no ?>>
        <label class="form-check-label" for="edit-under16-no">No</label>
      </div>
    </div>
  </div> <?php

  if( $tag_id == 69 || $tag_id == 70 || $tag_id == 71 ) : // permanent || seasonal || fixed term ?>

    <div class="row g-1">

      <div class="col-md-4 mb-3">
        <label for="edit-level">Level</label>
        <select class="form-select" name="edit-level" id="edit-level">
        <option value="">Select Level</option> <?php

          $level_dropdowns = $wpdb->get_results( "SELECT id, tag FROM master_tag WHERE cat_id=9 ORDER BY id ASC" );

          foreach ($level_dropdowns as $level_dropdown ) :

            $dropdown_level_id = $level_dropdown->id;
            $dropdown_level = $level_dropdown->tag;

            if( $edit_level_id == $dropdown_level_id ) : $selected = 'selected'; else : $selected = ''; endif; ?>

            <option value="<?php echo $dropdown_level_id ?>" <?php echo $selected ?>><?php echo $dropdown_level ?></option> <?php

          endforeach; ?>

        </select>
      </div>

      <div class="col-md-4 mb-3"> <?php

        if( $edit_part_time_id == 1 ) : $checked_yes = 'checked'; $checked_no = '';  else : $checked_yes = ''; $checked_no = 'checked'; endif; ?>

        <label class="d-block">Is employee part-time?<sup class="text-danger">*</sup></label>
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="radio" name="edit-part-time" id="edit-part-time-yes" value="1" <?php echo $checked_yes ?>>
          <label class="form-check-label" for="edit-part-time-yes">Yes</label>
        </div>
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="radio" name="edit-part-time" id="edit-part-time-no" value="0" <?php echo $checked_no ?>>
          <label class="form-check-label" for="edit-part-time-no">No</label>
        </div>
      </div>

      <div class="col-md-4 mb-3"> <?php

        if( $edit_promoted_id == 1 ) : $checked_yes = 'checked'; $checked_no = '';  else : $checked_yes = ''; $checked_no = 'checked'; endif; ?>

        <label class="d-block">Has employee been promoted?<sup class="text-danger">*</sup></label>
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="radio" name="edit-promoted" id="edit-promoted-yes" value="1" <?php echo $checked_yes ?>>
          <label class="form-check-label" for="edit-promoted-no">Yes</label>
        </div>
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="radio" name="edit-promoted" id="edit-promoted-no" value="0" <?php echo $checked_no ?>>
          <label class="form-check-label" for="edit-promoted-no">No</label>
        </div>
      </div>
    </div> <?php

  endif; ?>

  <h5 class="border-top pt-3">Days</h5>

  <div class="row g-1"> <?php

    if( $tag_id == 72 ) : // casual ?>

      <div class="col-md-4 mb-3">
        <label for="edit-days-worked">Days Worked<sup class="text-danger">*</sup></label>
        <input type="number" class="form-control" name="edit-days-worked" id="edit-days-worked" aria-describedby="editDaysWorked" value="<?php echo $edit_days_worked ?>" min="1" max="365" step="0.1" required>
        <div class="invalid-feedback">Please enter a number between 1 and 365</div>
      </div> <?php

    elseif( $tag_id == 73 ) : //intern ?>

      <div class="col-md-4 mb-3">
        <label for="edit-days-worked">Days Worked<sup class="text-danger">*</sup></label>
        <input type="number" class="form-control" name="edit-days-worked" id="edit-days-worked" aria-describedby="editDaysWorked" value="<?php echo $edit_days_worked ?>" min="1" max="365" step="0.1" required>
        <div class="invalid-feedback">Please enter a number between 1 and 365</div>
      </div>

      <div class="col-md-4 mb-3">
        <label for="edit-time-mentored">Time Mentored in Days<sup class="text-danger">*</sup></label>
        <input type="number" class="form-control" name="edit-time-mentored" id="edit-time-mentored" aria-describedby="editTimeMentored" value="<?php echo $edit_time_mentored ?>" min="1" max="365" step="0.1" required>
        <div class="invalid-feedback">Please enter a number between 1 and 365</div>
      </div> <?php

    else : ?>

      <div class="col-md-4 mb-3">
        <label for="edit-start-date">Start Date</label>
        <div class="input-group mb-2">
          <span class="input-group-text"><i class="fa-regular fa-calendar-days"></i></span>
          <input type="text" class="form-control date" name="edit-start-date" id="edit-start-date" aria-describedby="editStartDate" placeholder="dd-mmm-yyyy" value="<?php if( !empty( $edit_start_date ) ) : echo $edit_start_date_formatted; endif; ?>" data-date-end-date="0d">
        </div>
      </div>

      <div class="col-md-4 mb-3">
        <label for="edit-leave-date">Leave Date</label>
        <div class="input-group mb-2">
          <span class="input-group-text"><i class="fa-regular fa-calendar-days"></i></span>
          <input type="text" class="form-control date" name="edit-leave-date" id="edit-leave-date" aria-describedby="editLeaveDate" placeholder="dd-mmm-yyyy" value="<?php if( !empty( $edit_leave_date ) ) : echo $edit_leave_date_formatted; endif; ?>" data-date-end-date="0d">
        </div>
      </div>

      <div class="col-md-4 mb-3 d-flex align-items-end">
        <small class="pb-2">Dates are only required if the employee has worked a partial year</small>
      </div>

      </div><div class="row g-1">

      <div class="col-md-4 mb-3">
        <label for="edit-contract-dpw">Contracted Days per Week<sup class="text-danger">*</sup></label>
        <input type="number" class="form-control" name="edit-contract-dpw" id="edit-contract-dpw" aria-describedby="editContractDPW" value="<?php echo $contract_dpw ?>" min="1" max="7" step="0.1" required>
        <div class="invalid-feedback">Please enter a number between 1 and 7</div>
      </div>

      <div class="col-md-4 mb-3">
        <label for="edit-contract-wpy">Contracted Weeks per Year<sup class="text-danger">*</sup></label>
        <input type="number" class="form-control" name="edit-contract-wpy" id="edit-contract-wpy" aria-describedby="editContractWPY" value="<?php echo $contract_wpy ?>" min="1" max="52" step="0.1" required>
        <div class="invalid-feedback">Please enter a number between 1 and 52</div>
      </div>

      <div class="col-md-4 mb-3">
        <label for="edit-annual-leave">Annual Leave in Days<sup class="text-danger">*</sup></label>
        <input type="number" class="form-control" name="edit-annual-leave" id="edit-annual-leave" aria-describedby="editAnnualLeave" value="<?php echo $annual_leave ?>" min="1" max="365" step="0.1" required>
        <div class="invalid-feedback">Please enter a number between 1 and 365</div>
      </div> <?php

    endif; ?>
  </div>

  <h5 class="border-top pt-3">Pay</h5>

  <div class="row g-1">
    <div class="col-md-4 mb-3">
      <label for="edit-salary"><?php if( $tag_id == 72 ) /* casual */ : echo 'Pay'; elseif( $tag_id == 73 ) /* intern */ : echo 'Financial Compensation'; else : echo 'Salary'; endif; ?><sup class="text-danger">*</sup></label>
      <input type="number" class="form-control" name="edit-salary" id="edit-salary" aria-describedby="editSalary" value="<?php echo $edit_salary ?>" min="1" step="0.01" required>
      <div class="invalid-feedback">Please enter a number greater than 0.01</div>
    </div> <?php

    if( $tag_id == 69 || $tag_id == 70 || $tag_id == 71 || $tag_id == 86 ) : // permanent || seasonal || fixed term || contract ?>
      <div class="col-md-4 mb-3">
        <label for="edit-overtime">Overtime</label>
        <input type="number" class="form-control" name="edit-overtime" id="edit-overtime" aria-describedby="editOvertime" value="<?php echo $edit_overtime ?>" min="0" step="0.01">
        <div class="invalid-feedback">Please enter a number greater than 0.01</div>
      </div>

      <div class="col-md-4 mb-3">
        <label for="edit-bonuses">Bonuses</label>
        <input type="number" class="form-control" name="edit-bonuses" id="edit-bonuses" aria-describedby="editBonuses" value="<?php echo $edit_bonuses ?>" min="0" step="0.01">
        <div class="invalid-feedback">Please enter a number greater than 0.01</div>
      </div>

      </div><div class="row g-1"> <?php

    endif; ?>

    <div class="col-md-4 mb-3">
      <label for="edit-gratuities">Gratuities</label>
      <input type="number" class="form-control" name="edit-gratuities" id="edit-gratuities" aria-describedby="editGratuities" value="<?php echo $edit_gratuities ?>" min="0" step="0.01">
      <div class="invalid-feedback">Please enter a number greater than 0.01</div>
    </div>

    <div class="col-md-4 mb-3">
      <label for="edit-benefits">Benefits</label>
      <input type="number" class="form-control" name="edit-benefits" id="edit-benefits" aria-describedby="editBenefits" value="<?php echo $edit_benefits ?>" min="0" step="0.01">
      <div class="invalid-feedback">Please enter a number greater than 0.01</div>
    </div><?php

    if( $tag_id == 69 || $tag_id == 70 || $tag_id == 71 || $tag_id == 86 ) : // permanent || seasonal || fixed term || contract ?>

      <div class="col-md-4 mb-3 d-flex align-items-end">
        <small>Training should not be included in benefits but added separately below</small>
      </div> <?php

    endif; ?>
  </div>

  <h5 class="border-top pt-3">Training</h5>

  <div class="row g-1">

    <div class="col-md-4 mb-3">
      <label for="edit-cost-training">Cost of Training</label>
      <input type="number" class="form-control" name="edit-cost-training" id="edit-cost-training" aria-describedby="editCostTraining" value="<?php echo $edit_cost_training ?>" min="1" step="0.01">
      <div class="invalid-feedback">Please enter a number greater than 0.01</div>
    </div>

    <div class="col-md-4 mb-3">
      <label for="edit-training-days">Number of Training Days</label>
      <input type="number" class="form-control" name="edit-training-days" id="edit-training-days" aria-describedby="editTrainingDays" value="<?php echo $edit_training_days ?>" min="1" max="365" step="0.01">
      <div class="invalid-feedback">Please enter a number between 1 and 365</div>
    </div>

    <div class="col-md-4 mb-3 d-flex align-items-end">
      <small>Please ensure training costs have not been entered in benefits above</small>
    </div>

  </div> <?php

  if( $tag_toggle == 1 ) : ?>

    <h5 class="border-top pt-3">Tags</h5>

    <div class="row g-1">

      <div class="col-12 mb-3">

        <select class="selectpicker form-control" name="edit-tag[]" multiple title="Select Tags" multiple data-live-search="true"> <?php
          $tag_dropdowns = $wpdb->get_results( "SELECT parent_id, tag FROM custom_tag WHERE loc_id=$master_loc AND cat_id=22 AND tag IS NOT NULL AND active=1 AND id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id) ORDER BY tag ASC" );

          foreach( $tag_dropdowns as $tag_dropdown ) :

            $dropdown_parent_id = $tag_dropdown->parent_id;
            $dropdown_tag = $tag_dropdown->tag;

            $edit_tag_id = $wpdb->get_results( "SELECT tag_id FROM data_tag WHERE data_id=$edit_id AND mod_id=3", ARRAY_N );
            $data_array = array_map( function ($arr) {return $arr[0];}, $edit_tag_id );

            if( in_array($dropdown_parent_id, $data_array ) ) : $selected = 'selected'; else : $selected = ''; endif; ?>

            <option value="<?php echo $dropdown_parent_id ?>" <?php echo $selected ?>><?php echo $dropdown_tag ?></option> <?php

          endforeach; ?>
        </select>
      </div>

    </div> <?php

  endif; ?>

  <h5 class="border-top pt-3">Notes</h5>

  <div class="row g-1">
    <div class="col-12 mb-3">
      <label for="edit-note">Please enter any notes for this <?php if( $tag_id == 73 ) : echo 'intern'; else : echo 'employee'; endif; ?></label>
        <textarea class="form-control" name="edit-note" id="edit-note" aria-describedby="editNote" placeholder="Notes"><?php echo $edit_note ?></textarea>
    </div>
  </div>

  <div class="row g-1">
    <div class="col-12 mb-3"><button class="btn btn-primary" type="submit" name="<?php echo $update_labour ?>"><?php if( empty( $add_url ) ) : echo 'Update'; else : echo 'Add'; endif; echo ' '.str_replace( '-', ' ', $add_url ); ?></button></div>
  </div>

</form> <?php

$update_measure_null = $_POST['edit-measure'];
$update_measure_date_null = $_POST['edit-measure-date'];
$update_hometown = $_POST['edit-hometown'];
$update_gender = $_POST['edit-gender'];
$update_ethnicity_null = $_POST['edit-ethnicity'];
$update_disability = $_POST['edit-disability'];
$update_level_null = $_POST['edit-level'];
$update_role_null = $_POST['edit-role'];
$update_part_time_null = $_POST['edit-part-time'];
$update_promoted_null = $_POST['edit-promoted'];
$update_under16 = $_POST['edit-under16'];
$update_start_date_null = $_POST['edit-start-date'];
$update_leave_date_null = $_POST['edit-leave-date'];
$update_days_worked_null = $_POST['edit-days-worked'];
$update_time_mentored_null = $_POST['edit-time-mentored'];
$update_contract_dpw_null = $_POST['edit-contract-dpw'];
$update_contract_wpy_null = $_POST['edit-contract-wpy'];
$update_annual_leave_null = $_POST['edit-annual-leave'];
$update_salary = $_POST['edit-salary'];
$update_overtime_null = $_POST['edit-overtime'];
$update_bonuses_null = $_POST['edit-bonuses'];
$update_gratuities_null = $_POST['edit-gratuities'];
$update_benefits_null = $_POST['edit-benefits'];
$update_cost_training_null = $_POST['edit-cost-training'];
$update_training_days_null = $_POST['edit-training-days'];
$update_tags = $_POST['edit-tag'];
$update_note_null = $_POST['edit-note'];

if( empty( $add_url ) ) : $record_type = 'entry_revision'; else : $record_type = 'entry'; endif;
if( empty( $update_measure_null ) ) : $update_measure = NULL; else : $update_measure = $update_measure_null; endif;
if( empty( $update_measure_date_null ) ) : $update_measure_date = NULL; else : $update_measure_date = date_format( date_create( $update_measure_date_null ), 'Y-m-d' );; endif;
if( empty( $update_ethnicity_null ) ) : $update_ethnicity = NULL; else : $update_ethnicity = $update_ethnicity_null; endif;
if( empty( $update_level_null ) ) : $update_level = NULL; else : $update_level = $update_level_null; endif;
if( empty( $update_role_null ) ) : $update_role = NULL; else : $update_role = $update_role_null; endif;
if( empty( $update_part_time_null ) ) : $update_part_time = NULL; else : $update_part_time = $update_part_time_null; endif;
if( empty( $update_promoted_null ) ) : $update_promoted = NULL; else : $update_promoted = $update_promoted_null; endif;
if( empty( $update_start_date_null ) ) : $update_start_date = NULL; else : $update_start_date = date_format( date_create( $update_start_date_null ), 'Y-m-d' ); endif;
if( empty( $update_leave_date_null ) ) : $update_leave_date = NULL; else : $update_leave_date = date_format( date_create( $update_st_date_null ), 'Y-m-d' ); endif;
if( empty( $update_days_worked_null ) ) : $update_days_worked = NULL; else : $update_days_worked = $update_days_worked_null; endif;
if( empty( $update_time_mentored_null ) ) : $update_time_mentored = NULL; else : $update_time_mentored = $update_time_mentored_null; endif;
if( empty( $update_contract_dpw_null ) ) : $update_contract_dpw = NULL; else : $update_contract_dpw = $update_contract_dpw_null; endif;
if( empty( $update_contract_wpy_null ) ) : $update_contract_wpy = NULL; else : $update_contract_wpy = $update_contract_wpy_null; endif;
if( empty( $update_annual_leave_null ) ) : $update_annual_leave = NULL; else : $update_annual_leave = $update_annual_leave_null; endif;
if( empty( $update_overtime_null ) ) : $update_overtime = 0; else : $update_overtime = $update_overtime_null; endif;
if( empty( $update_bonuses_null ) ) : $update_bonuses = 0; else : $update_bonuses = $update_bonuses_null; endif;
if( empty( $update_gratuities_null ) ) : $update_gratuities = 0; else : $update_gratuities = $update_gratuities_null; endif;
if( empty( $update_benefits_null ) ) : $update_benefits = 0; else : $update_benefits = $update_benefits_null; endif;
if( empty( $update_cost_training_null ) ) : $update_cost_training = NULL; else : $update_cost_training = $update_cost_training_null; endif;
if( empty( $update_training_days_null ) ) : $update_training_days = NULL; else : $update_training_days = $update_training_days_null; endif;
if( empty( $update_note_null ) ) : $update_note = NULL; else : $update_note = $update_note_null; endif;
if( empty( $edit_parent_id ) ) : $update_parent_id = 0; else : $update_parent_id = $edit_parent_id; endif;


if ( isset( $_POST[$update_labour] ) ) :

  $wpdb->insert( 'data_labour',
    array(
      'entry_date' => $entry_date,
      'record_type' => $record_type,
      'measure' => $update_measure,
      'measure_date' => $update_measure_date,
      'employee_type' => $tag_id,
      'location' => $update_hometown,
      'gender' => $update_gender,
      'ethnicity' => $update_ethnicity,
      'disability' => $update_disability,
      'level' => $update_level,
      'role' => $update_role,
      'part_time' => $update_part_time,
      'promoted' => $update_promoted,
      'under16' => $update_under16,
      'start_date' => $update_start_date,
      'leave_date' => $update_leave_date,
      'days_worked' => $update_days_worked,
      'time_mentored' => $update_time_mentored,
      'contract_dpw' => $update_contract_dpw,
      'contract_wpy' => $update_contract_wpy,
      'annual_leave' => $update_annual_leave,
      'salary' => $update_salary,
      'overtime' => $update_overtime,
      'bonuses' => $update_bonuses,
      'gratuities' => $update_gratuities,
      'benefits' => $update_benefits,
      'household' => NULL,
      'cost_training' => $update_cost_training,
      'training_days' => $update_training_days,
      'note' => $update_note,
      'active' => 1,
      'parent_id' => $update_parent_id,
      'user_id' => $user_id,
      'loc_id' => $master_loc
    )
  );

  $last_id = $wpdb->insert_id;

  if( empty( $edit_parent_id ) ) :

    $wpdb->update( 'data_labour',
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
          'mod_id' => 3
        )
      );

    endforeach;

  endif;

  if( empty( $add_url ) ) : $query_string = 'edit='.$edit_url.'&start='.$start.'&end='.$end; else : $query_string = 'add='.$add_url; endif;

  header( 'Location:'.$site_url.'/'.$slug.'/?'.$query_string );
  ob_end_flush();

endif;