<?php

/* Includes: MEASURES SNIPPETS

@package	Yardstick
@author		Digital Rockpool
@link		https://yardstick.co.uk
@copyright	Copyright (c) 2019, Digital Rockpool LTD
@license	GPL-2.0+ */


// ADD SETTINGS
function measures_add_setting( $set_id, $cat_id, $title, $title_singular ) {

	global $wpdb;
	global $post;

	$site_url = get_site_url();
	$slug = $post->post_name;
	$setting_query = $_GET['setting'];

	$user_id = get_current_user_id();
	$master_loc = $_SESSION['master_loc'];
	$measure_toggle = $_SESSION['measure_toggle'];
	$measure_toggle_name = $_SESSION['measure_toggle_name'];

	$entry_date = date( 'Y-m-d H:i:s' ); ?>

	<form method="post" name="edit-measures" id="edit-measures">

		<div class="form-row">
			<div class="col-md-12 mb-3">
				<p>Measures are time periods where you can record the occupancy of both staff and clients and/or the proportion of the building that was in use.</p>
				<label class="font-weight-normal align-top pt-1 pr-4">Change Measures:<sup class="text-danger">*</sup></label> <?php

				$measure_types = $wpdb->get_results( "SELECT id, tag FROM master_tag WHERE cat_id=13 AND id!=86 ORDER BY id ASC" ); // Hiding custom tags

				$measure_type_selected = $wpdb->get_row( "SELECT parent_id, tag_id  FROM custom_tag WHERE loc_id=$master_loc AND active=1 AND id IN (SELECT MAX(id) FROM custom_tag WHERE cat_id=13 GROUP BY parent_id)" );
				$measure_type_selected_parent_id = $measure_type_selected->parent_id;
				$measure_type_selected = $measure_type_selected->tag_id;

				foreach( $measure_types as $measure_type ) :

					$measure_type_id = $measure_type->id;
					$measure_type_tag = $measure_type->tag;

					if( $measure_type_selected == $measure_type_id ) : $checked = 'checked'; else : $checked = ''; endif; ?>

					<div class="form-check form-check-inline">
						<input class="form-check-input" type="radio" name="edit-measure-type" id="edit-measures<?php echo $measure_type_tag ?>" value="<?php echo $measure_type_id ?>" <?php echo $checked ?>>
						<label class="form-check-label" for="edit-measures<?php echo $measure_type_tag ?>"><?php echo $measure_type_tag ?></label>
					</div> <?php

				endforeach; ?>

				<small class="d-block pt-2">Your measure are set to: <strong><?php echo $measure_toggle_name ?></strong><br />Changing the time periods you report in will only affect future data entered.</small>
			</div>
		</div>

		<div class="form-row">
			<div class="col-12 mb-3"><button class="btn btn-primary" type="submit" name="edit-measures">Update</button></div>
		</div>

	</form><?php

	$update_measure_type = $_POST['edit-measure-type'];

	if ( isset( $_POST['edit-measures'] ) ) :

		$wpdb->insert( 'custom_tag',
			array(
				'entry_date' => $entry_date,
				'record_type' => 'entry_revision',
				'tag' => NULL,
				'tag_id' => $update_measure_type,
				'size' => NULL,
				'unit_id' => NULL,
				'cat_id' => 13,
				'active' => 1,
				'parent_id' => $measure_type_selected_parent_id,
				'user_id' => $user_id,
				'loc_id' => $master_loc
			)
		);

		header ('Location:'.$site_url.'/'.$slug.'/?setting='.$setting_query);
		ob_end_flush();

	endif;

	if( $measure_toggle == 86 ) : ?>

		<h4 class="border-top pt-3 mt-3">Add Custom Measures</h4>

		<form method="post" name="add-measure-settings" id="add-measure-settings">

			<div id="repeater-field">
				<div class="entry form-row mb-1">
					<div class="col-10">
						<input type="text" class="form-control" id="set-measure-name" name="set-measure-name[]" placeholder="Measure Name *" required>
					</div>

					<div class="col-2">
						<span class="input-group-btn"><button type="button" class="btn btn-success btn-add"><i class="fas fa-plus"></i></button></span>
					</div>
				</div>
			</div>

			<div class="form-row">
				<div class="col-2 offset-10 mb-3"><button class="btn btn-primary float-none" type="submit" name="add-measure-settings">Add</button></div>
			</div>
		</form> <?php

		$set_measure_name_array = $_POST['set-measure-name'];

		if ( isset( $_POST['add-measure-settings'] ) ) :

			foreach( $set_measure_name_array as $index => $set_measure_name_array ) :

				$tag = $set_measure_name_array;

				$wpdb->insert( 'custom_tag',
					array(
						'entry_date' => $entry_date,
						'record_type' => 'entry',
						'tag' => $tag,
						'tag_id' => NULL,
						'size' => NULL,
						'unit_id' => NULL,
						'custom_cat' => NULL,
						'cat_id' => $cat_id,
						'parent_id' => 0,
						'user_id' => $user_id,
						'active' => 1,
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

			endforeach;

			header ('Location:'.$site_url.'/'.$slug.'/?setting='.$setting_query);
			ob_end_flush();

		endif;

	endif;
}


// LATEST ENTRIES
function measures_latest_entries( $add, $title ) {

	global $wpdb;

	$user_id = get_current_user_id();
	$measure_toggle = $_SESSION['measure_toggle'];

	$add_rows = $wpdb->get_results( "SELECT measure_start, bednight, roomnight, client, loc_name FROM data_measure INNER JOIN profile_location ON (data_measure.loc_id=profile_location.parent_id AND profile_location.id IN (SELECT MAX(id) FROM profile_location GROUP BY parent_id)) INNER JOIN relation_user ON data_measure.loc_id=relation_user.loc_id WHERE relation_user.user_id=$user_id AND data_measure.active=1 AND data_measure.id IN (SELECT MAX(id) FROM data_measure GROUP BY parent_id) ORDER BY data_measure.id DESC LIMIT 5" );

	if( empty( $add_rows) ) :

		echo '<p>No '.strtolower( $title ).' data has been added.</p>';

	else : ?>

		<div class="table-responsive-xl mb-4">
			<table id="latest" class="table table-borderless">
				<thead>
					<tr>
						<th scope="col">Start Date</th>
						<th scope="col">B/N</th>
						<th scope="col">R/N</th>
						<th scope="col">Client</th>
					</tr>
				</thead>

				<tbody> <?php

					foreach ( $add_rows as $add_row ) :

						if( $measure_toggle == 83 ) : /* weekly */
							$latest_date = 'Week '.date_format( date_create( $add_row->measure_start ), 'W Y' );

						elseif( $measure_toggle == 84 ) : /* monthly */
							$latest_date = date_format( date_create( $add_row->measure_start ), 'M-Y' );

						else :
							$latest_date = date_format( date_create( $add_row->measure_start ), 'd-M-Y' );

						endif;

						$latest_bednight = $add_row->bednight;
						$latest_roomnight = $add_row->roomnight;
						$latest_client = $add_row->client; ?>

						<tr>
							<td><?php echo $latest_date ?></td>
							<td class="text-right" nowrap><?php echo number_format( $latest_bednight ) ?></td>
							<td class="text-right" nowrap><?php echo number_format( $latest_roomnight ) ?></td>
							<td class="text-right" nowrap><?php echo number_format( $latest_client ) ?></td>
						</tr> <?php

					endforeach; ?>

				</tbody>
			</table>
		</div> <?php

	endif;

}


// MEASURES FORM
function measures_data_form( $latest_start, $latest_end, $edit_measure_name, $edit_measure_date_formatted, $edit_measure_end_formatted, $edit_bednight, $edit_roomnight, $edit_client, $edit_staff, $edit_area, $edit_note, $edit_parent_id, $edit_measure ) {

	global $wpdb;
	global $post;

	$site_url = get_site_url();
	$slug = $post->post_name;

	$add_url = $_GET['add'];
	$edit_url = $_GET['edit'];

	$user_id = get_current_user_id();
	$master_loc = $_SESSION['master_loc'];
	$measure_toggle = $_SESSION['measure_toggle'];

	$entry_date = date( 'Y-m-d H:i:s' );

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

		if( empty( $add_url ) ) : $query_string = 'edit='.$edit_url.'&start='.$latest_start.'&end='.$latest_end; else : $query_string = 'add='.$add_url; endif;

		header( 'Location:'.$site_url.'/'.$slug.'/?'.$query_string );
		ob_end_flush();

	endif;

}


// MEASURES EDIT
function measures_edit( $edit, $latest_start, $latest_end, $title ) {

	global $wpdb;
	global $post;

	$site_url = get_site_url();
	$slug = $post->post_name;

	$user_id = get_current_user_id();
	$master_loc = $_SESSION['master_loc'];
	$measure_toggle = $_SESSION['measure_toggle'];

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

												measures_data_form( $latest_start, $latest_end, $edit_measure_name, $edit_measure_date, $edit_measure_end, $edit_bednight, $edit_roomnight, $edit_client, $edit_staff, $edit_area, $edit_note, $edit_parent_id, $edit_measure ); ?>

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
}
