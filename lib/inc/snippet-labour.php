<?php ob_start();

/* Includes: LABOUR SNIPPETS

@package	Yardstick
@author		Digital Rockpool
@link		https://yardstick.co.uk
@copyright	Copyright (c) 2019, Digital Rockpool LTD
@license	GPL-2.0+ */


// SETTINGS
function labour_add_setting( $set_id, $cat_id, $title, $title_singular ) {

	global $wpdb;
	global $post;

	$site_url = get_site_url();
	$slug = $post->post_name;
	$setting_query = $_GET['setting'];

	$user_id = get_current_user_id();
	$master_loc = $_SESSION['master_loc'];
	$entry_date = date( 'Y-m-d H:i:s' );

	$employee_dropdowns = $wpdb->get_results( "SELECT id, tag FROM master_tag WHERE cat_id=$cat_id AND NOT EXISTS (SELECT tag FROM custom_tag WHERE master_tag.id=custom_tag.tag_id AND cat_id=$cat_id AND loc_id=$master_loc) ORDER BY tag ASC" );

	$labour_settings = $wpdb->get_results( "SELECT id FROM custom_tag WHERE cat_id=$cat_id AND loc_id=$master_loc" );

	if( ( empty( $employee_dropdowns ) && $set_id == 8 ) || ( !empty( $labour_settings ) && $set_id == 13 ) ) : ?>

		<p>All <?php echo strtolower( $title ); ?> have been added. If you require a new <?php echo strtolower( $title_singular ); ?> please email <a href="mailto:support@yardstick.co.uk" title="support@yardstick.co.uk">support@yardstick.co.uk</a>.</p> <?php

	else : ?>

		<form method="post" id="add-employee-settings" name="add-employee-settings" class="needs-validation" novalidate> <?php

			if( $set_id == 13 ) : ?>

				<div class="form-row">

					<div class="col-md-4 mb-3">
						<label for="set-dpw">Contracted days per week<sup class="text-danger">*</sup></label>
						<input type="number" class="form-control" id="set-dpw" name="set-employee-setting[]" min="1" max="7" step="0.1" value="5" required>
						<input type="hidden" name="set-tag-id[]" value="281">
						<div class="invalid-feedback">Please enter a number between 1 and 5</div>
					</div>

					<div class="col-md-4 mb-3">
						<label for="set-wpy">Contracted weeks per year<sup class="text-danger">*</sup></label>
						<input type="number" class="form-control" id="set-wpy" name="set-employee-setting[]" min="1" max="52" step="0.1" value="52" required>
						<input type="hidden" name="set-tag-id[]" value="282">
						<div class="invalid-feedback">Please enter a number between 1 and 52</div>
					</div>

					<div class="col-md-4 mb-3">
						<label for="set-al">Annual leave in days<sup class="text-danger">*</sup></label>
						<input type="number" class="form-control" id="set-al" name="set-employee-setting[]" min="1" max="365" step="0.1" value="25" required>
						<input type="hidden" name="set-tag-id[]" value="283">
						<div class="invalid-feedback">Please enter a number between 1 and 365</div>
					</div>

				</div> <?php

			else : ?>

				<div id="repeater-field">
					<div class="entry form-row mb-1">
						<div class="col-10"> <?php

							if( $set_id == 8 ) : /* employee type */ ?>
								<select class="form-control" id="set-employee-setting" name="set-employee-setting[]" required>
									<option value="" selected disabled>Select <?php echo $title_singular ?> *</option> <?php

									foreach( $employee_dropdowns as $employee_dropdown ) : ?>
										<option value="<?php echo $employee_dropdown->id ?>"><?php echo $employee_dropdown->tag ?></option> <?php
									endforeach; ?>

								</select> <?php

							else : ?>

								<input type="text" class="form-control" id="set-employee-setting" name="set-employee-setting[]" placeholder="Add <?php echo strtolower( $title_singular ); ?>" required> <?php

							endif; ?>

						</div>

						<div class="col-2">
							<span class="input-group-btn"><button type="button" class="btn btn-success btn-add"><i class="fas fa-plus"></i></button></span>
						</div>
					</div>
				</div> <?php

			endif; ?>

			<div class="form-row">
				<div class="col-2 offset-10 mb-3"><button class="btn btn-primary <?php if( $set_id !=13 ) : echo 'float-none'; endif; ?>" type="submit" name="add-employee-settings">Add</button></div>
			</div>
		</form> <?php

		$set_employee_setting_array = $_POST['set-employee-setting'];
		$set_tag_id_array = $_POST['set-tag-id'];

		if ( isset( $_POST['add-employee-settings'] ) ) :

			foreach( $set_employee_setting_array as $index => $set_employee_setting_array ) :

				if( $set_id == 13 ) : // labour settings

					$tag = $set_employee_setting_array;
					$tag_id = $set_tag_id_array[$index];
					$tag_check = NULL;

				elseif( $set_id == 8 ) : // employee types

					$tag = NULL;
					$tag_id = $set_employee_setting_array;
					$tag_check = $wpdb->get_row( "SELECT tag_id FROM custom_tag WHERE tag_id=$tag_id AND loc_id=$master_loc" );

				else :

					$tag = $set_employee_setting_array;
					$tag_id = NULL;
					$tag_check = $wpdb->get_row( "SELECT tag FROM custom_tag WHERE tag='$tag' AND cat_id=$cat_id AND loc_id=$master_loc" );

				endif;


				if( empty( $tag_check ) ) :

					$wpdb->insert( 'custom_tag',
						array(
							'entry_date' => $entry_date,
							'record_type' => 'entry',
							'tag' => $tag,
							'tag_id' => $tag_id,
							'size' => NULL,
							'unit_id' => NULL,
							'custom_cat' => NULL,
							'cat_id' => $cat_id,
							'parent_id' => 0,
							'user_id' => $user_id,
							'loc_id' => $master_loc
						)
					);

					$parent_id = $wpdb->insert_id;

					$wpdb->update( 'custom_tag',
						array(
							'parent_id' => $parent_id,
						),
						array(
							'id' => $parent_id
						)
					);

				endif;

			endforeach;

			header ('Location:'.$site_url.'/'.$slug.'/?setting='.$setting_query);
			ob_end_flush();

		endif;
	endif;
}


//LATEST ENTRIES
function labour_latest_entries( $add, $title, $extra_value) {

	global $wpdb;

	$employee_id = $extra_value;
	$user_id = get_current_user_id();

	$add_rows = $wpdb->get_results( "SELECT custom_location.location, tag, salary FROM data_labour INNER JOIN custom_location ON (data_labour.location=custom_location.parent_id AND custom_location.id IN (SELECT MAX(id) FROM custom_location GROUP BY parent_id)) INNER JOIN master_tag ON data_labour.gender=master_tag.id INNER JOIN relation_user ON data_labour.loc_id=relation_user.loc_id WHERE employee_type=$employee_id AND relation_user.user_id=$user_id AND data_labour.active=1 ORDER BY data_labour.id DESC LIMIT 5" );

	if( empty( $add_rows) ) :

		echo '<p>No '.strtolower( $title ).' data has been added.</p>';

	else : ?>

		<div class="table-responsive-xl mb-4">
			<table id="latest" class="table table-borderless">
				<thead>
					<tr>
						<th scope="col">Hometown</th>
						<th scope="col">Gender</th> <?php
						if( $employee_id == 72 || $employee_id == 73 ) : // casual || intern ?> <th scope="col">Pay</th> <?php else : ?> <th scope="col">Salary</th> <?php endif; ?>
					</tr>
				</thead>

				<tbody> <?php

					foreach ( $add_rows as $add_row ) :

						$latest_hometown = $add_row->location;
						$latest_gender = $add_row->tag;
						$latest_salary = $add_row->salary; ?>

						<tr>
							<td><?php echo $latest_hometown ?></td>
							<td><?php echo $latest_gender ?></td>
							<td class="text-right" nowrap><?php echo number_format( $latest_salary, 2) ?></td>
						</tr> <?php

					endforeach; ?>

				</tbody>
			</table>
		</div> <?php

	endif;

}


// DATA ENTRY FORM
function labour_form( $edit_labour, $latest_start, $latest_end, $edit_id, $employee_id, $edit_measure, $edit_measure_name, $edit_measure_date_formatted, $edit_hometown_id, $edit_gender_id, $edit_ethnicity_id, $edit_disability_id, $edit_level_id, $edit_role_id, $edit_part_time_id, $edit_promoted_id, $edit_under16_id, $edit_start_date, $edit_start_date_formatted, $edit_leave_date, $edit_leave_date_formatted, $edit_days_worked, $edit_time_mentored, $edit_contract_dpw, $edit_contract_wpy, $edit_annual_leave, $edit_salary, $edit_overtime, $edit_bonuses, $edit_gratuities, $edit_benefits, $edit_cost_training, $edit_training_days, $edit_note, $edit_parent_id ) {

	global $wpdb;
	global $post;

	$site_url = get_site_url();
	$slug = $post->post_name;

	$add_url = $_GET['add'];
	$edit_url = $_GET['edit'];

	$user_id = get_current_user_id();
	$master_loc = $_SESSION['master_loc'];
	$measure_toggle = $_SESSION['measure_toggle'];
	$tag_toggle = $_SESSION['tag_toggle'];

	$entry_date = date( 'Y-m-d H:i:s' );

	$master_contract_dpw = $wpdb->get_row( "SELECT tag FROM custom_tag WHERE tag_id=281 AND loc_id=$master_loc AND active=1 AND id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id )" );
	$master_contract_wpy = $wpdb->get_row( "SELECT tag FROM custom_tag WHERE tag_id=282 AND loc_id=$master_loc AND active=1 AND id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id )" );
	$master_annual_leave = $wpdb->get_row( "SELECT tag FROM custom_tag WHERE tag_id=283 AND loc_id=$master_loc AND active=1 AND id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id )" );

	if( empty( $edit_contract_dpw ) ) : $contract_dpw = $master_contract_dpw->tag; else : $contract_dpw = $edit_contract_dpw; endif;
	if( empty( $edit_contract_wpy ) ) : $contract_wpy = $master_contract_wpy->tag; else : $contract_wpy = $edit_contract_wpy; endif;
	if( empty( $edit_annual_leave ) ) : $annual_leave = $master_annual_leave->tag; else : $annual_leave = $edit_annual_leave; endif;

	if( empty( $edit_labour ) ) : $update_labour = 'edit_labour'; else : $update_labour = $edit_labour; endif; ?>

	<form method="post" name="edit" id="<?php echo $update_labour ?>" class="needs-validation" novalidate>
		<div class="form-row"> <?php

			if( $measure_toggle == 86 && $employee_id != 69 && $employee_id != 70 && $employee_id != 71 ) : // custom measures || permanent || seasonal || fixed term

					custom_measure_dropdown( $edit_measure );

			elseif( $measure_toggle == 85 || $employee_id == 69 || $employee_id == 70 || $employee_id == 71 ) : // yearly measure || permanent || seasonal || fixed term

					reporting_year_start_date( $edit_measure_date_formatted );

			else : ?>

				<div class="col-md-4 mb-3">
					<label class="control-label" for="edit-measure-date">Date of <?php if( $employee_id == 73 ) : echo 'Internship'; else : echo 'Work'; endif; ?><sup class="text-danger">*</sup></label>
					<div class="input-group mb-2">
						<div class="input-group-prepend"><div class="input-group-text"><i class="far fa-calendar-alt"></i></div></div>
						<input type="text" class="form-control date" name="edit-measure-date" id="edit-measure-date" aria-describedby="editMeasureDate" placeholder="dd-mmm-yyyy" value="<?php if( empty( $edit_url ) ) : echo date('d-M-Y'); else : echo $edit_measure_date_formatted; endif; ?>" data-date-end-date="0d" required>
						<div class="invalid-feedback">Please select a date</div>
					</div>
				</div>

				<div class="col-md-4 mb-2 d-flex align-items-end"> <?php

					if( $measure_toggle == 84 || $measure_toggle == 83 ) : // monthly || weekly measures ?>
						<small>If this entry is for a period of time the amount will be added to the <?php if( $measure_toggle == 84 ) : echo 'month'; elseif( $measure_toggle == 83 ) : echo 'week'; endif; ?> of the selected date.</small> <?php
					endif; ?>

				</div> <?php

			endif; ?>

		</div>

		<h5 class="border-top pt-3">Employee</h5>

		<div class="form-row">

			<div class="col-md-4 mb-3">
				<label for="edit-hometown">Hometown<sup class="text-danger">*</sup></label>
				<select class="form-control" name="edit-hometown" id="edit-hometown" required> <?php
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
				<select class="form-control" name="edit-gender" id="edit-gender" required> <?php
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
				<select class="form-control" name="edit-ethnicity" id="edit-ethnicity">
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

		<div class="form-row">

			<div class="col-md-4 mb-3">
				<label for="edit-role">Role</label>
				<select class="form-control" name="edit-role" id="edit-role">
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

		if( $employee_id == 69 || $employee_id == 70 || $employee_id == 71 ) : // permanent || seasonal || fixed term ?>

			<div class="form-row">

				<div class="col-md-4 mb-3">
					<label for="edit-level">Level</label>
					<select class="form-control" name="edit-level" id="edit-level">
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

		<div class="form-row"> <?php

			if( $employee_id == 72 ) : // casual ?>

				<div class="col-md-4 mb-3">
					<label for="edit-days-worked">Days Worked<sup class="text-danger">*</sup></label>
					<input type="number" class="form-control" name="edit-days-worked" id="edit-days-worked" aria-describedby="editDaysWorked" value="<?php echo $edit_days_worked ?>" min="1" max="365" step="0.1" required>
					<div class="invalid-feedback">Please enter a number between 1 and 365</div>
				</div> <?php

			elseif( $employee_id == 73 ) : //intern ?>

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
					<label class="control-label" for="edit-start-date">Start Date</label>
					<div class="input-group mb-2">
						<div class="input-group-prepend"><div class="input-group-text"><i class="far fa-calendar-alt"></i></div></div>
						<input type="text" class="form-control date" name="edit-start-date" id="edit-start-date" aria-describedby="editStartDate" placeholder="dd-mmm-yyyy" value="<?php if( !empty( $edit_start_date ) ) : echo $edit_start_date_formatted; endif; ?>" data-date-end-date="0d">
					</div>
				</div>

				<div class="col-md-4 mb-3">
					<label class="control-label" for="edit-leave-date">Leave Date</label>
					<div class="input-group mb-2">
						<div class="input-group-prepend"><div class="input-group-text"><i class="far fa-calendar-alt"></i></div></div>
						<input type="text" class="form-control date" name="edit-leave-date" id="edit-leave-date" aria-describedby="editLeaveDate" placeholder="dd-mmm-yyyy" value="<?php if( !empty( $edit_leave_date ) ) : echo $edit_leave_date_formatted; endif; ?>" data-date-end-date="0d">
					</div>
				</div>

				<div class="col-md-4 mb-3 d-flex align-items-end">
					<small class="pb-2">Dates are only required if the employee has worked a partial year</small>
				</div>

				</div><div class="form-row">

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

		<div class="form-row">

			<div class="col-md-4 mb-3">
				<label for="edit-salary"><?php if( $employee_id == 72 ) /* casual */ : echo 'Pay'; elseif( $employee_id == 73 ) /* intern */ : echo 'Financial Compensation'; else : echo 'Salary'; endif; ?><sup class="text-danger">*</sup></label>
				<input type="number" class="form-control" name="edit-salary" id="edit-salary" aria-describedby="editSalary" value="<?php echo $edit_salary ?>" min="1" step="0.01" required>
				<div class="invalid-feedback">Please enter a number greater than 0.01</div>
			</div> <?php

			if( $employee_id == 69 || $employee_id == 70 || $employee_id == 71 || $employee_id == 86 ) : // permanent || seasonal || fixed term || contract ?>
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

				</div><div class="form-row"> <?php

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

			if( $employee_id == 69 || $employee_id == 70 || $employee_id == 71 || $employee_id == 86 ) : // permanent || seasonal || fixed term || contract ?>

				<div class="col-md-4 mb-3 d-flex align-items-end">
					<small>Training should not be included in benefits but added separately below</small>
				</div> <?php

			endif; ?>

		</div>

		<h5 class="border-top pt-3">Training</h5>

		<div class="form-row">

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

			<div class="form-row">

				<div class="col-12 mb-3">

					<select class="selectpicker form-control" name="edit-tag[]" multiple title="Select Tags" multiple data-live-search="true"> <?php
						$tag_dropdowns = $wpdb->get_results( "SELECT parent_id, tag FROM custom_tag WHERE loc_id=$master_loc AND cat_id=22 AND tag IS NOT NULL AND active=1 AND id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id) ORDER BY tag ASC" );

						foreach ($tag_dropdowns as $tag_dropdown ) :

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

		<div class="form-row">

			<div class="col-12 mb-3">
				<label for="edit-note">Please enter any notes for this <?php if( $employee_id == 73 ) : echo 'intern'; else : echo 'employee'; endif; ?></label>
    			<textarea class="form-control" name="edit-note" id="edit-note" aria-describedby="editNote" placeholder="Notes"><?php echo $edit_note ?></textarea>
			</div>

		</div>

		<div class="form-row">

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
				'employee_type' => $employee_id,
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

		if( empty( $add_url ) ) : $query_string = 'edit='.$edit_url.'&start='.$latest_start.'&end='.$latest_end; else : $query_string = 'add='.$add_url; endif;

		header( 'Location:'.$site_url.'/'.$slug.'/?'.$query_string );
		ob_end_flush();

	endif;
}


// DATA EDIT
function labour_edit( $edit, $latest_start, $latest_end, $title, $extra_value ) {

	global $wpdb;
	global $post;

	$site_url = get_site_url();
	$slug = $post->post_name;

	$employee_id = $extra_value;

	$user_id = get_current_user_id();
	$master_loc = $_SESSION['master_loc'];
	$measure_toggle = $_SESSION['measure_toggle'];
	$tag_toggle = $_SESSION['tag_toggle'];

	$edit_url = $_GET['edit'];
	$start = $_GET['start'];
	$end = $_GET['end'];

	$latest_measure_date = $wpdb->get_row( "SELECT measure_date FROM data_labour INNER JOIN relation_user ON data_labour.loc_id=relation_user.loc_id WHERE relation_user.user_id=$user_id AND employee_type=$employee_id AND data_labour.id IN (SELECT MAX(id) FROM data_labour GROUP BY parent_id) ORDER BY measure_date DESC" );

	$latest_end = $latest_measure_date->measure_date;
	$latest_start = date( 'Y-m-d', strtotime( "$end -364 days" ) );

	$entry_date = date( 'Y-m-d H:i:s' );

	$edit_rows = $wpdb->get_results( "SELECT data_labour.id, measure, custom_tag.tag AS measure_name, measure_date, measure_start, measure_end, custom_location.location, data_labour.location AS location_id, gender_tag.tag AS gender, gender AS gender_id, ethnicity_tag.tag AS ethnicity, ethnicity AS ethnicity_id, disability_tag.tag AS disability, disability AS disability_id, level_tag.tag AS level, level AS level_id, role_tag.tag AS role, role AS role_id, part_time_tag.tag AS part_time, part_time AS part_time_id, promoted_tag.tag AS promoted, promoted AS promoted_id, under16_tag.tag AS under16, under16 AS under16_id, start_date, leave_date, days_worked, time_mentored, contract_dpw, contract_wpy, annual_leave, salary, overtime, bonuses, gratuities, benefits, cost_training, training_days, data_labour.note, data_labour.parent_id, data_labour.active FROM data_labour LEFT JOIN data_measure ON (data_labour.measure=data_measure.parent_id AND data_measure.id IN (SELECT MAX(id) FROM data_measure GROUP BY parent_id)) LEFT JOIN custom_tag ON (data_measure.measure_name=custom_tag.parent_id AND custom_tag.id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id)) LEFT JOIN master_tag gender_tag ON data_labour.gender=gender_tag.id LEFT JOIN custom_location ON (data_labour.location=custom_location.parent_id AND custom_location.id IN (SELECT MAX(id) FROM custom_location GROUP BY parent_id)) LEFT JOIN custom_tag ethnicity_tag ON (data_labour.ethnicity=ethnicity_tag.parent_id AND ethnicity_tag.id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id)) LEFT JOIN master_tag disability_tag ON data_labour.disability=disability_tag.id LEFT JOIN master_tag level_tag ON data_labour.level=level_tag.id LEFT JOIN custom_tag role_tag ON (data_labour.role=role_tag.parent_id AND role_tag.id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id)) LEFT JOIN master_tag part_time_tag ON data_labour.part_time=part_time_tag.id LEFT JOIN master_tag promoted_tag ON data_labour.promoted=promoted_tag.id LEFT JOIN master_tag under16_tag ON data_labour.under16=under16_tag.id INNER JOIN relation_user ON data_labour.loc_id=relation_user.loc_id WHERE employee_type=$employee_id AND relation_user.user_id=$user_id AND data_labour.id IN (SELECT MAX(id) FROM data_labour GROUP BY parent_id) AND measure_date BETWEEN '$start' AND '$end'" );

	if( empty( $edit_rows) ) :

		echo 'No '.strtolower( $title ).' data has been added.';

	else : ?>

		<div class="table-responsive-xl mb-3">
			<table id="edit" class="table table-borderless nowrap" style="width:100%;">
				<thead>
					<tr>
						<th scope="col" class="no-sort">View | Delete | Edit</th> <?php
						if( $measure_toggle == 86 && ( $employee_id == 72 || $employee_id == 73 ) ) : // custom measures || casual || intern ?>
							<th scope="col">Date Range</th>
							<th scope="col" class="filter-column">Measure Name</th> <?php
						else : ?>
							<th scope="col">Date</th> <?php
						endif; ?>
						<th scope="col" class="filter-column">Hometown</th>
						<th scope="col" class="filter-column">Gender</th>
						<th scope="col" class="filter-column">Ethnicity</th>
						<th scope="col" class="filter-column">Disabilities</th>
						<th scope="col" class="filter-column">Under 16</th>
						<th scope="col" class="filter-column">Role</th> <?php
						if( $employee_id == 72 || $employee_id == 73 ) : // casual || intern ?>
							<th scope="col">Days Worked</th> <?php
						elseif( $employee_id == 73 ) : ?>
							<th scope="col">Time Mentored in Days</th> <?php
						elseif( $employee_id == 228 ) : // contract ?>
							<th scope="col">Start Date</th>
							<th scope="col">Leave Date</th>
							<th scope="col">Contracted Days per Week</th>
							<th scope="col">Contracted Week per year</th>
							<th scope="col">Annual Leave in Days</th> <?php
						else : ?>
							<th scope="col" class="filter-column">Level</th>
							<th scope="col" class="filter-column">Part-Time</th>
							<th scope="col" class="filter-column">Promoted</th>
							<th scope="col">Start Date</th>
							<th scope="col">Leave Date</th>
							<th scope="col">Contracted Days per Week</th>
							<th scope="col">Contracted Week per year</th>
							<th scope="col">Annual Leave in Days</th> <?php
						endif;
						if( $employee_id == 72 ) : // casual ?>
							<th scope="col">Total Pay</th> <?php
						elseif( $employee_id == 73 ) : // intern ?>
							<th scope="col">Financial Compensation</th> <?php
						else : ?>
							<th scope="col">Salary</th>
							<th scope="col">Overtime</th>
							<th scope="col">Bonuses</th> <?php
						endif; ?>
						<th scope="col">Gratuities</th>
						<th scope="col">Benefits</th>
						<th scope="col">Cost of Training</th>
						<th scope="col">Number of Training Days</th> <?php
						if( $tag_toggle == 1 ) : ?> <th scope="col">Tags</th> <?php endif; ?>
						<th scope="col">Notes</th>
					</tr>
				</thead>

				<tbody> <?php

					foreach ( $edit_rows as $edit_row ) :

						$edit_id = $edit_row->id;
						$edit_measure_name = $edit_row->measure;
						$edit_measure_name = $edit_row->measure_name;
						$edit_measure_date = $edit_row->measure_date;
						$edit_measure_date_formatted = date_format( date_create( $edit_measure_date ), 'd-M-Y' );
						$edit_measure_start = $edit_row->measure_start;
						$edit_measure_start_formatted = date_format( date_create( $edit_measure_start ), 'd-M-Y' );
						$edit_measure_end = $edit_row->measure_end;
						$edit_measure_end_formatted = date_format( date_create( $edit_measure_end ), 'd-M-Y' );
						$edit_hometown = $edit_row->location;
						$edit_hometown_id = $edit_row->location_id;
						$edit_gender = $edit_row->gender;
						$edit_gender_id = $edit_row->gender_id;
						$edit_ethnicity = $edit_row->ethnicity;
						$edit_ethnicity_id = $edit_row->ethnicity_id;
						$edit_disability = $edit_row->disability;
						$edit_disability_id = $edit_row->disability_id;
						$edit_level = $edit_row->level;
						$edit_level_id = $edit_row->level_id;
						$edit_role = $edit_row->role;
						$edit_role_id = $edit_row->role_id;
						$edit_part_time = $edit_row->part_time;
						$edit_part_time_id = $edit_row->part_time_id;
						$edit_promoted = $edit_row->promoted;
						$edit_promoted_id = $edit_row->promoted_id;
						$edit_under16 = $edit_row->under16;
						$edit_under16_id = $edit_row->under16_id;
						$edit_start_date = $edit_row->start_date;
						$edit_start_date_formatted = date_format( date_create( $edit_start_date ), 'd-M-Y' );
						$edit_leave_date = $edit_row->leave_date;
						$edit_leave_date_formatted = date_format( date_create( $edit_leave_date ), 'd-M-Y' );
						$edit_days_worked = $edit_row->days_worked;
						$edit_time_mentored = $edit_row->time_mentored;
						$edit_contract_dpw = $edit_row->contract_dpw;
						$edit_contract_wpy = $edit_row->contract_wpy;
						$edit_annual_leave = $edit_row->annual_leave;
						$edit_salary = $edit_row->salary;
						$edit_overtime = $edit_row->overtime;
						$edit_bonuses = $edit_row->bonuses;
						$edit_gratuities = $edit_row->gratuities;
						$edit_benefits = $edit_row->benefits;
						$edit_cost_training = $edit_row->cost_training;
						$edit_training_days = $edit_row->training_days;
						$edit_note = $edit_row->note;
						$edit_parent_id = $edit_row->parent_id;
						$edit_active = $edit_row->active;
						$edit_labour = 'edit-'.$edit_id;
						$archive_labour = 'archive-'.$edit_id;

						$data_tags = $wpdb->get_results( "SELECT data_tag.tag_id, tag FROM custom_tag INNER JOIN data_tag ON custom_tag.parent_id=data_tag.tag_id WHERE data_id=$edit_id AND mod_id=3 AND custom_tag.id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id) ORDER BY tag" ); ?>

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

												$revision_rows = $wpdb->get_results( "SELECT data_labour.id, data_labour.entry_date, measure, custom_tag.tag AS measure_name, measure_date, measure_start, measure_end, custom_location.location,  gender_tag.tag AS gender, ethnicity_tag.tag AS ethnicity, disability_tag.tag AS disability, level_tag.tag AS level, role_tag.tag AS role, part_time_tag.tag AS part_time, promoted_tag.tag AS promoted, under16_tag.tag AS under16, start_date, leave_date, days_worked, time_mentored, contract_dpw, contract_wpy, annual_leave, salary, overtime, bonuses, gratuities, benefits, cost_training, training_days, data_labour.note, data_labour.parent_id, data_labour.active, loc_name, display_name FROM data_labour LEFT JOIN data_measure ON (data_labour.measure=data_measure.parent_id AND data_measure.id IN (SELECT MAX(id) FROM data_measure GROUP BY parent_id)) LEFT JOIN custom_tag ON (data_measure.measure_name=custom_tag.parent_id AND custom_tag.id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id)) LEFT JOIN master_tag gender_tag ON data_labour.gender=gender_tag.id LEFT JOIN custom_location ON (data_labour.location=custom_location.parent_id AND custom_location.id IN (SELECT MAX(id) FROM custom_location GROUP BY parent_id)) LEFT JOIN custom_tag ethnicity_tag ON (data_labour.ethnicity=ethnicity_tag.parent_id AND ethnicity_tag.id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id)) LEFT JOIN master_tag disability_tag ON data_labour.disability=disability_tag.id LEFT JOIN master_tag level_tag ON data_labour.level=level_tag.id LEFT JOIN custom_tag role_tag ON (data_labour.role=role_tag.parent_id AND custom_tag.id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id)) LEFT JOIN master_tag part_time_tag ON data_labour.part_time=part_time_tag.id LEFT JOIN master_tag promoted_tag ON data_labour.promoted=promoted_tag.id LEFT JOIN master_tag under16_tag ON data_labour.under16=under16_tag.id INNER JOIN profile_location ON (data_labour.loc_id=profile_location.parent_id AND profile_location.id IN (SELECT MAX(id) FROM profile_location GROUP BY parent_id)) INNER JOIN yard_users ON data_labour.user_id=yard_users.id INNER JOIN relation_user ON data_labour.loc_id=relation_user.loc_id WHERE employee_type=$employee_id AND data_labour.parent_id=$edit_parent_id AND relation_user.user_id=$user_id ORDER BY data_labour.id DESC" );

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
													$revision_hometown = $revision_row->location;
													$revision_gender = $revision_row->gender;
													$revision_ethnicity = $revision_row->ethnicity;
													$revision_disability = $revision_row->disability;
													$revision_level = $revision_row->level;
													$revision_role = $revision_row->role;
													$revision_part_time = $revision_row->part_time;
													$revision_promoted = $revision_row->promoted;
													$revision_under16 = $revision_row->under16;
													$revision_start_date = $revision_row->start_date;
													$revision_start_date_formatted = date_format( date_create( $revision_row->start_date ), 'd-M-Y' );
													$revision_leave_date = $revision_row->leave_date;
													$revision_leave_date_formatted = date_format( date_create( $revision_row->leave_date ), 'd-M-Y' );
													$revision_days_worked = $revision_row->days_worked;
													$revision_time_mentored = $revision_row->time_mentored;
													$revision_contract_dpw = $revision_row->contract_dpw;
													$revision_contract_wpy = $revision_row->contract_wpy;
													$revision_annual_leave = $revision_row->annual_leave;
													$revision_salary = $revision_row->salary;
													$revision_overtime = $revision_row->overtime;
													$revision_bonuses = $revision_row->bonuses;
													$revision_gratuities = $revision_row->gratuities;
													$revision_benefits = $revision_row->benefits;
													$revision_household_contrib = $revision_row->household_contrib;
													$revision_cost_training = $revision_row->cost_training;
													$revision_training_days = $revision_row->training_days;
													$revision_note = $revision_row->note;
													$revision_parent_id = $revision_row->parent_id;
													$revision_active = $revision_row->active;
													$revision_username = $revision_row->display_name;

													if( $revision_id == $revision_parent_id ) : $active_action = 'Added'; elseif( $revision_active == 0 ) : $active_action = 'Deleted'; else : $active_action = 'Edited'; endif;
													echo '<b>Date:</b> ';
													if( empty( $revision_measure_date ) ) : echo $revision_measure_start_formatted.' to '.$revision_measure_end_formatted; else : echo $revision_measure_date_formatted; endif;
													echo '<br />';
													if( $measure_toggle == 86 && ( $employee_id == 72 || $employee_id == 73 ) ) : echo '<b>Measure Name:</b> '.$revision_measure_name.'<br />'; endif; // custom measures || casual || intern
													echo '<b>Hometown:</b> '.$revision_hometown.'<br />';
													echo '<b>Gender:</b> '.$revision_gender.'<br />';
													echo '<b>Ethnicity:</b> '.$revision_ethnicity.'<br />';
													echo '<b>Disabilities:</b> '.$revision_disability.'<br />';
													echo '<b>Under 16:</b> '.$revision_under16.'<br />';
													echo '<b>Role:</b> '.$revision_role.'<br />';

													if( $employee_id == 72 || $employee_id == 73 ) : // casual || intern
														echo '<b>Days Worked:</b> ';
														$revision_days_worked_decimal_clean = rtrim( number_format( $revision_days_worked, 2 ) , '0' ); echo rtrim( $revision_days_worked_decimal_clean, '.' );
														echo '<br />';
													elseif( $employee_id == 73 ) : // intern
														echo '<b>Time Mentored in Days:</b> ';
														$revision_time_mentored_decimal_clean = rtrim( number_format( $revision_time_mentored, 2 ) , '0' ); echo rtrim( $revision_time_mentored_decimal_clean, '.' );
														echo '<br />';
													else :
														echo '<b>Level:</b> '.$revision_level.'<br />';
														echo '<b>Part-Time:</b> '.$revision_part_time.'<br />';
														echo '<b>Promoted:</b> '.$revision_promoted.'<br />';


														echo '<b>Start Date:</b> ';
														if( !empty( $revision_start_date ) ) : echo $revision_start_date_formatted; endif;
														echo '<br />';

														echo '<b>Leave Date:</b> ';
														if( !empty( $revision_leave_date ) ) : echo $revision_leave_date_formatted; endif;
														echo '<br />';

														echo '<b>Contracted Days per Week:</b> ';
														$revision_contract_dpw_decimal_clean = rtrim( number_format( $revision_contract_dpw, 2 ) , '0' ); echo rtrim( $revision_contract_dpw_decimal_clean, '.' );
														echo '<br />';

														echo '<b>Contracted Week per year:</b> ';
														$revision_contract_wpy_decimal_clean = rtrim( number_format( $revision_contract_wpy, 2 ) , '0' ); echo rtrim( $revision_contract_wpy_decimal_clean, '.' );
														echo '<br />';

														echo '<b>Annual Leave in Days:</b> ';
														$revision_annual_leave_decimal_clean = rtrim( number_format( $revision_annual_leave, 2 ) , '0' ); echo rtrim( $revision_annual_leave_decimal_clean, '.' );
														echo '<br />';

													endif;

													if( $employee_id == 72 ) : // casual
														echo '<b>Total Pay:</b> '.number_format( $revision_salary, 2).'<br />';
													elseif( $employee_id == 73 ) : // intern
														echo '<b>Financial Compensation:</b> '.number_format( $revision_salary, 2).'<br />';
													else :
														echo '<b>Salary:</b> '.number_format( $revision_salary, 2).'<br />';
														echo '<b>Overtime:</b> '.number_format( $revision_overtime, 2).'<br />';
														echo '<b>Bonuses:</b> '.number_format( $revision_bonuses, 2).'<br />';
													endif;

													echo '<b>Gratuities:</b> '.number_format( $revision_gratuities, 2).'<br />';
													echo '<b>Benefits:</b> '.number_format( $revision_benefits, 2).'<br />';
													echo '<b>Cost of Training:</b> '.number_format( $revision_cost_training, 2).'<br />';
													echo '<b>Number of Training Days:</b> ';
													$revision_training_days_decimal_clean = rtrim( number_format( $revision_training_days, 2 ) , '0' ); echo rtrim( $revision_training_days_decimal_clean, '.' );
													echo '<br />';

													if( $tag_toggle == 1 ) :
														echo '<b>Tags:</b> ';

														$revision_tags = $wpdb->get_results( "SELECT tag FROM custom_tag INNER JOIN data_tag ON custom_tag.parent_id=data_tag.tag_id WHERE data_id=$revision_id AND mod_id=3 AND custom_tag.id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id) ORDER BY tag" );

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

								<form method="post" name="archive" id="<?php echo $archive_labour ?>" class="d-inline-block">
									<button type="submit" class="btn <?php echo $btn_style ?> d-inline-block" name="<?php echo $archive_labour ?>"><?php echo $edit_value ?></button>
								</form> <?php

								if ( isset( $_POST[$archive_labour] ) ) :

									$wpdb->insert( 'data_labour',
										array(
											'entry_date' => $entry_date,
											'record_type' => 'entry_revision',
											'measure' => $edit_measure_name,
											'measure_date' => $edit_measure_date,
											'employee_type' => $employee_id,
											'location' => $edit_hometown_id,
											'gender' => $edit_gender_id,
											'ethnicity' => $edit_ethnicity_id,
											'disability' => $edit_disability_id,
											'level' => $edit_level_id,
											'role' => $edit_role_id,
											'part_time' => $edit_part_time_id,
											'promoted' => $edit_promoted_id,
											'under16' => $edit_under16_id,
											'start_date' => $edit_start_date,
											'leave_date' => $edit_leave_date,
											'days_worked' => $edit_days_worked,
											'time_mentored' => $edit_time_mentored,
											'contract_dpw' => $edit_contract_dpw,
											'contract_wpy' => $edit_contract_wpy,
											'annual_leave' => $edit_annual_leave,
											'salary' => $edit_salary,
											'overtime' => $edit_overtime,
											'bonuses' => $edit_bonuses,
											'gratuities' => $edit_gratuities,
											'benefits' => $edit_benefits,
											'household' => NULL,
											'cost_training' => $edit_cost_training,
											'training_days' => $edit_training_days,
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
													'mod_id' => 3
												)
											);

										endforeach;

									endif;

									header( 'Location:'.$site_url.'/'.$slug.'/?edit='.$edit_url.'&start='.$latest_start.'&end='.$latest_end );
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

												<p class="small">Fields marked with an asterisk<span class="text-danger">*</span> are required</p> <?php

												labour_form( $edit_labour, $latest_start, $latest_end, $edit_id,$employee_id, $edit_measure, $edit_measure_name, $edit_measure_date_formatted, $edit_hometown_id, $edit_gender_id, $edit_ethnicity_id, $edit_disability_id, $edit_level_id, $edit_role_id, $edit_part_time_id, $edit_promoted_id, $edit_under16_id, $edit_start_date, $edit_start_date_formatted, $edit_leave_date, $edit_leave_date_formatted, $edit_days_worked, $edit_time_mentored, $edit_contract_dpw, $edit_contract_wpy, $edit_annual_leave, $edit_salary, $edit_overtime, $edit_bonuses, $edit_gratuities, $edit_benefits, $edit_cost_training, $edit_training_days, $edit_note, $edit_parent_id );
												 ?>

											</div>

										</div>
									</div>
								</div>

							</td>
							<td><span class="d-none"><?php echo $edit_measure_date.$edit_measure_start ?></span><?php if( empty( $edit_measure_date ) ) : echo $edit_measure_start_formatted.' to '.$edit_measure_end_formatted; else : echo $edit_measure_date_formatted; endif; ?></td> <?php
							if( $measure_toggle == 86 && ( $employee_id == 72 || $employee_id == 73 ) ) : // custom measure && casual || intern ?><td><?php echo $edit_measure_name; ?></td> <?php endif; ?>
							<td><?php echo $edit_hometown ?></td>
							<td><?php echo $edit_gender ?></td>
							<td><?php echo $edit_ethnicity ?></td>
							<td><?php echo $edit_disability ?></td>
							<td><?php echo $edit_under16 ?></td>
							<td><?php echo $edit_role; ?></td> <?php
							if( $employee_id == 72 || $employee_id == 73 ) : // casual || intern ?>
								<td class="text-right"><?php $edit_days_worked_decimal_clean = rtrim( number_format( $edit_days_worked, 2 ) , '0' ); echo rtrim( $edit_days_worked_decimal_clean, '.' ); ?></td> <?php
							elseif( $employee_id == 73 ) : ?>
								<td class="text-right"><?php $edit_time_mentored_decimal_clean = rtrim( number_format( $edit_time_mentored, 2 ) , '0' ); echo rtrim( $edit_days_worked_decimal_clean, '.' ); ?></td> <?php
							elseif( $employee_id == 228 ) : // contract ?>
								<td><?php if( !empty( $edit_start_date ) ) : echo $edit_start_date_formatted; endif; ?></td>
								<td><?php if( !empty( $edit_leave_date ) ) : echo $edit_leave_date_formatted; endif; ?></td>
								<td class="text-right"><?php $edit_contract_dpw_decimal_clean = rtrim( number_format( $edit_contract_dpw, 2 ) , '0' ); echo rtrim( $edit_contract_dpw_decimal_clean, '.' ); ?></td>
								<td class="text-right"><?php $edit_contract_wpy_decimal_clean = rtrim( number_format( $edit_contract_wpy, 2 ) , '0' ); echo rtrim( $edit_contract_wpy_decimal_clean, '.' ); ?></td>
								<td class="text-right"><?php $edit_annual_leave_decimal_clean = rtrim( number_format( $edit_annual_leave, 2 ) , '0' ); echo rtrim( $edit_annual_leave_decimal_clean, '.' ); ?></td> <?php
							else : ?>
								<td><?php echo $edit_level; ?></td>
								<td><?php echo $edit_part_time; ?></td>
								<td><?php echo $edit_promoted; ?></td>
								<td><?php if( !empty( $edit_start_date ) ) : echo $edit_start_date_formatted; endif; ?></td>
								<td><?php if( !empty( $edit_leave_date ) ) : echo $edit_leave_date_formatted; endif; ?></td>
								<td class="text-right"><?php $edit_contract_dpw_decimal_clean = rtrim( number_format( $edit_contract_dpw, 2 ) , '0' ); echo rtrim( $edit_contract_dpw_decimal_clean, '.' ); ?></td>
								<td class="text-right"><?php $edit_contract_wpy_decimal_clean = rtrim( number_format( $edit_contract_wpy, 2 ) , '0' ); echo rtrim( $edit_contract_wpy_decimal_clean, '.' ); ?></td>
								<td class="text-right"><?php $edit_annual_leave_decimal_clean = rtrim( number_format( $edit_annual_leave, 2 ) , '0' ); echo rtrim( $edit_annual_leave_decimal_clean, '.' ); ?></td> <?php
							endif; ?>
							<td class="text-right"><?php echo number_format( $edit_salary, 2) ?></td> <?php
							if( $employee_id == 69 || $employee_id == 70 || $employee_id == 71 || $employee_id == 228 ) : // permanent || seasonal || fixed term || contract ?>
								<td class="text-right"><?php echo number_format( $edit_overtime, 2) ?></td>
								<td class="text-right"><?php echo number_format( $edit_bonuses, 2) ?></td> <?php
							endif; ?>
							<td class="text-right"><?php echo number_format( $edit_gratuities, 2) ?></td>
							<td class="text-right"><?php echo number_format( $edit_benefits, 2) ?></td>
							<td class="text-right"><?php echo number_format( $edit_cost_training, 2) ?></td>
							<td class="text-right"><?php $edit_training_days_decimal_clean = rtrim( number_format( $edit_training_days, 2 ) , '0' ); echo rtrim( $edit_training_days_decimal_clean, '.' ); ?></td> <?php

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
						if( $measure_toggle == 86 && ( $employee_id == 72 || $employee_id == 73 ) ) : // custom measures || casual || intern ?>
							<th></th>
							<th></th><?php
						else : ?>
							<th></th><?php
						endif; ?>
						<th></th>
						<th></th>
						<th></th>
						<th></th>
						<th></th>
						<th></th><?php
						if( $employee_id == 72 || $employee_id == 73 ) : // casual || intern ?>
							<th></th><?php
						elseif( $employee_id == 73 ) : ?>
							<th></th><?php
						elseif( $employee_id == 228 ) : // contract ?>
							<th></th>
							<th></th>
							<th></th>
							<th></th>
							<th></th><?php
						else : ?>
							<th></th>
							<th></th>
							<th></th>
							<th></th>
							<th></th>
							<th></th>
							<th></th>
							<th></th><?php
						endif;
						if( $employee_id == 72 ) : // casual ?>
							<th></th><?php
						elseif( $employee_id == 73 ) : // intern ?>
							<th></th><?php
						else : ?>
							<th></th>
							<th></th>
							<th></th><?php
						endif; ?>
						<th></th>
						<th></th>
						<th></th>
						<th></th><?php
						if( $tag_toggle == 1 ) : ?> <th></th> <?php endif; ?>
						<th></th>
					</tr>
				</tfoot>

			</table>
		</div> <?php

	 endif;
}
