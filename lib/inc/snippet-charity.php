<?php ob_start();

/* Includes: CHARITY SNIPPETS

@package	Yardstick
@author		Digital Rockpool
@link		https://yardstick.co.uk
@copyright	Copyright (c) 2019, Digital Rockpool LTD
@license	GPL-2.0+ */


// SETTINGS
function charity_add_setting(  $set_id, $cat_id, $title, $title_singular ) {

	global $wpdb;
	global $post;

	$site_url = get_site_url();
	$slug = $post->post_name;
	$setting_query = $_GET['setting'];

	$user_id = get_current_user_id();
	$master_loc = $_SESSION['master_loc'];
	$entry_date = date( 'Y-m-d H:i:s' );

	$charity_dropdowns = $wpdb->get_results( "SELECT id, tag FROM master_tag WHERE cat_id=$cat_id AND NOT EXISTS (SELECT tag FROM custom_tag WHERE master_tag.id=custom_tag.tag_id AND cat_id=$cat_id AND loc_id=$master_loc) ORDER BY tag ASC" );

	if( empty( $charity_dropdowns ) ) : ?>

		<p>All <?php echo strtolower( $title ); ?> have been added. If you require a new <?php echo strtolower( $title_singular ); ?> please email <a href="mailto:support@yardstick.co.uk" title="support@yardstick.co.uk">support@yardstick.co.uk</a>.</p> <?php

	else : ?>

		<form method="post" id="add-charity-settings" name="add-charity-settings" class="needs-validation" novalidate>
			<div id="repeater-field">
				<div class="entry form-row mb-1">
					<div class="col-10">
						<select class="form-control" id="set-donation-type" name="set-donation-type[]" required>
							<option value="" selected disabled>Select <?php echo $title_singular ?> *</option> <?php

							foreach( $charity_dropdowns as $charity_dropdown ) : ?>
								<option value="<?php echo $charity_dropdown->id ?>"><?php echo $charity_dropdown->tag ?></option> <?php
							endforeach; ?>

						</select>
						<div class="invalid-feedback">Please select charity type</div>
					</div>

					<div class="col-2">
						<span class="input-group-btn"><button type="button" class="btn btn-success btn-add"><i class="fas fa-plus"></i></button></span>
					</div>
				</div>
			</div>

			<div class="form-row">
				<div class="col-2 offset-10 mb-3"><button class="btn btn-primary float-none" type="submit" name="add-charity-settings">Add</button></div>
			</div>
		</form> <?php

		$set_donation_type_array = $_POST['set-donation-type'];

		if ( isset( $_POST['add-charity-settings'] ) ) :

			foreach( $set_donation_type_array as $index => $set_donation_type_array ) :

				$tag_id = $set_donation_type_array;

				$tag_check = $wpdb->get_row( "SELECT tag_id FROM custom_tag WHERE tag_id=$tag_id AND loc_id=$master_loc" );
				if( empty( $tag_check ) ) :

					$wpdb->insert( 'custom_tag',
						array(
							'entry_date' => $entry_date,
							'record_type' => 'entry',
							'tag' => NULL,
							'tag_id' => $tag_id,
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

				endif;

			endforeach;

			header ('Location:'.$site_url.'/'.$slug.'/?setting='.$setting_query);
			ob_end_flush();

		endif;
	endif;
}


// LATEST ENTRIES
function charity_latest_entries( $add, $title ) {

	global $wpdb;

	$user_id = get_current_user_id();
	$donation_type = $wpdb->get_row( "SELECT id FROM master_tag WHERE tag='$add'" );
	$donation_id = $donation_type->id;

	$add_rows = $wpdb->get_results( "SELECT measure_date, measure_start, custom_location.location, amount FROM data_charity LEFT JOIN data_measure ON (data_charity.measure=data_measure.parent_id AND data_measure.id IN (SELECT MAX(id) FROM data_measure GROUP BY parent_id)) INNER JOIN custom_location ON (data_charity.location=custom_location.parent_id AND custom_location.id IN (SELECT MAX(id) FROM custom_location GROUP BY parent_id)) INNER JOIN relation_user ON data_charity.loc_id=relation_user.loc_id WHERE donation_type=$donation_id AND relation_user.user_id=$user_id AND data_charity.active=1 AND data_charity.id IN (SELECT MAX(id) FROM data_charity GROUP BY parent_id) ORDER BY data_charity.id DESC LIMIT 5" );

	if( empty( $add_rows) ) :

		echo '<p>No '.strtolower( $title ).' data has been added.</p>';

	else : ?>

		<div class="table-responsive-xl mb-4">
			<table id="latest" class="table table-borderless">
				<thead>
					<tr>
						<th scope="col">Date</th>
						<th scope="col">Location</th>
						<th scope="col">Amount</th>
					</tr>
				</thead>

				<tbody> <?php

					foreach ( $add_rows as $add_row ) :

						$latest_date = $add_row->measure_date;
						$latest_date_formatted = date_format( date_create( $latest_date ), 'd-M-Y' );
						$latest_measure_start = $add_row->measure_start;
						$latest_measure_start_formatted = date_format( date_create( $latest_measure_start ), 'd-M-Y' );
						$latest_donee_location = $add_row->location;
						$latest_amount = $add_row->amount;

						if( !empty( $latest_custom_tag_entry ) ) : $latest_custom_tag = ' - '.$latest_custom_tag_entry; endif; ?>

						<tr>
							<td><?php if( empty( $latest_date ) ) : echo $latest_measure_start_formatted; else : echo $latest_date_formatted; endif; ?></td>
							<td><?php echo $latest_donee_location ?></td>
							<td class="text-right" nowrap><?php echo number_format( $latest_amount, 2) ?></td>
						</tr> <?php

					endforeach; ?>

				</tbody>
			</table>
		</div> <?php

	endif;

}


// DATA ENTRY FORM
function charity_form( $edit_charity, $latest_start, $latest_end, $edit_id, $donation_id, $edit_measure, $edit_measure_name, $edit_measure_date_formatted, $edit_donee_location_id, $edit_value_type_id, $edit_amount, $edit_duration, $edit_note, $edit_parent_id ) {

	global $wpdb;
	global $post;

	$site_url = get_site_url();
	$slug = $post->post_name;

	$add_url = $_GET['add'];
	$edit_url = $_GET['edit'];

	$user_id = get_current_user_id();
	$master_loc = $_SESSION['master_loc'];
	$calendar = $_SESSION['calendar'];
	$measure_toggle = $_SESSION['measure_toggle'];
	$tag_toggle = $_SESSION['tag_toggle'];

	$entry_date = date( 'Y-m-d H:i:s' );

	if( empty( $edit_charity ) ) : $update_charity = 'edit_charity'; else : $update_charity = $edit_charity; endif; ?>

	<form method="post" name="edit" id="<?php echo $update_charity ?>" class="needs-validation" novalidate>
		<div class="form-row"> <?php

			if( $measure_toggle == 86 ) : // custom measures

				custom_measure_dropdown( $edit_measure );

			elseif( $measure_toggle == 85 ) : // yearly measure

				reporting_year_start_date( $edit_measure_date_formatted );

			else : ?>

				<div class="col-md-4 mb-3">
					<label class="control-label" for="edit-measure-date">Date of Donation<sup class="text-danger">*</sup></label>
					<div class="input-group mb-2">
						<div class="input-group-prepend"><div class="input-group-text"><i class="far fa-calendar-alt"></i></div></div>
						<input type="text" class="form-control date" name="edit-measure-date" id="edit-measure-date" aria-describedby="editMeasureDate" placeholder="dd-mmm-yyyy" value="<?php if( empty( $edit_url ) ) : echo date( 'd-M-Y', strtotime( '-1 day' ) ); else : echo $edit_measure_date_formatted; endif; ?>" data-date-end-date="0d" required>
						<div class="invalid-feedback">Please select a date</div>
					</div>
				</div>

				<div class="col-md-6 mb-3 d-flex align-items-end">  <?php

					if( $measure_toggle == 84 || $measure_toggle == 83 ) : // monthly or weekly measures ?>
						<small>If this entry is for a period of time the amount will be added to the <?php if( $measure_toggle == 84 ) : echo 'month'; elseif( $measure_toggle == 83 ) : echo 'week'; endif; ?> of the selected date.</small> <?php
					endif; ?>

				</div> <?php

			endif; ?>

		</div>

		<div id="repeater-field">
			<div class="entry form-row mb-1"> <?php

				$donee_dropdowns = $wpdb->get_results( "SELECT parent_id, location FROM custom_location WHERE loc_id=$master_loc AND active=1 AND id IN (SELECT MAX(id) FROM custom_location GROUP BY parent_id) ORDER BY location ASC" ); ?>

				<div class="<?php if( empty( $add_url ) ) : ?>col-md-4<?php else : ?>col-md-5<?php endif; ?>">
					<?php if( empty( $add_url ) ) : ?><label for="edit-donee-location">Donee Location<sup class="text-danger">*</sup></label><?php endif; ?>
					<select class="form-control" id="edit-donee-location" name="edit-donee-location[]" required>
						<?php if( empty( $edit_url ) ) : ?><option value="">Select Donee Location</option><?php endif; ?>
						<option value="0" <?php if( $edit_source_id == 0 && empty( $add_url ) ) : echo 'selected'; else : echo ''; endif; ?>>Unknown Location</option> <?php

						foreach ($donee_dropdowns as $donee_dropdown ) :

							$donee_location_parent_id = $donee_dropdown->parent_id;
							$donee_location = $donee_dropdown->location;

							if( $edit_donee_location_id == $donee_location_parent_id ) : $selected = 'selected'; else : $selected = ''; endif; ?>

							<option value="<?php echo $donee_location_parent_id ?>" <?php echo $selected ?>><?php echo $donee_location ?></option> <?php

						endforeach; ?>

					</select>
					<div class="invalid-feedback">Please select donee location</div>
				</div> <?php

				if( $donation_id == 66 ) : ?>

					<div class="<?php if( empty( $add_url ) ) : ?>col-md-4<?php else : ?>col-md-3<?php endif; ?>">
						<?php if( empty( $add_url ) ) : ?><label for="edit-duration">Staff Time Donated<sup class="text-danger">*</sup></label><?php endif; ?>
						<input type="number" class="form-control" id="edit-duration" name="edit-duration[]" aria-describedby="editDuration" placeholder="Duration" value="<?php echo $edit_duration ?>" min="1" step="0.01" required>
						<div class="invalid-feedback">Please enter a number greater than 0.01</div>
					</div> <?php

				else :

					if( $edit_value_type_id == 67 ) : $selected = 'selected'; else : $selected = ''; endif;?>

					<div class="<?php if( empty( $add_url ) ) : ?>col-md-4<?php else : ?>col-md-3<?php endif; ?>">
						<?php if( empty( $add_url ) ) : ?><label for="edit-value-type">Value Type<sup class="text-danger">*</sup></label><?php endif; ?>
						<select class="form-control" id="edit-value-type" name="edit-value-type[]" required>
							<option value="68">Cash</option>
							<option value="67" <?php echo $selected ?>>In-Kind</option>
						</select>
						<div class="invalid-feedback">Please select value type</div>
					</div> <?php

				endif; ?>

				<div class="<?php if( empty( $add_url ) ) : ?>col-md-4<?php else : ?>col-md-3<?php endif; ?>">
					<?php if( empty( $add_url ) ) : ?><label for="edit-amount"><?php if( $donation_id == 66 ) : echo 'Cash Equivalent'; else : echo 'Amount / Cash Equivalent'; endif; ?><sup class="text-danger">*</sup></label><?php endif; ?>
					<input type="number" class="form-control" id="edit-amount" name="edit-amount[]" aria-describedby="editAmount" placeholder="<?php if( $donation_id == 66 ) : echo 'Cash Equivalent'; else : echo 'Amount / Cash Equivalent'; endif; ?>" value="<?php echo $edit_amount ?>" min="1" step="0.01" required>
					<div class="invalid-feedback">Please enter a number greater than 0.01</div>
				</div> <?php

				if( empty( $edit_url ) ) : ?><div class="col-1"><button type="button" class="btn btn-success btn-add"><i class="fas fa-plus"></i></button></div> <?php endif; ?>

			</div>
		</div> <?php

		if( $tag_toggle == 1 ) : ?>

			<h5 class="border-top pt-3 mt-3">Tags</h5>

			<div class="form-row">

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

		<div class="form-row">

			<div class="col-12 mb-3">
				<label for="edit-note">Please enter any notes for this entry</label>
    			<textarea class="form-control" name="edit-note" id="edit-note" aria-describedby="editNote" placeholder="Notes"><?php echo $edit_note ?></textarea>
			</div>

		</div>

		<div class="form-row">
			<div class="col-12 mb-3"><button class="btn btn-primary" type="submit" name="<?php echo $update_charity ?>"><?php if( empty( $add_url ) ) : echo 'Update'; else : echo 'Add'; endif; echo ' '.str_replace( '-', ' ', $add_url ); ?></button></div>
		</div>
	</form> <?php

	if ( isset( $_POST[$update_charity] ) ) :

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
				'donation_type' => $donation_id,
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

		if( empty( $add_url ) ) : $query_string = 'edit='.$edit_url.'&start='.$latest_start.'&end='.$latest_end; else : $query_string = 'add='.$add_url; endif;

		header( 'Location:'.$site_url.'/'.$slug.'/?'.$query_string );
		ob_end_flush();

	endif;
}


// DATA EDIT
function charity_edit( $edit, $latest_start, $latest_end, $title, $extra_value ) {

	global $wpdb;
	global $post;

	$site_url = get_site_url();
	$slug = $post->post_name;

	$donation_id = $extra_value;

	$user_id = get_current_user_id();
	$master_loc = $_SESSION['master_loc'];
	$plan_id = $_SESSION['plan_id'];

	$measure_toggle = $_SESSION['measure_toggle'];
	$tag_toggle = $_SESSION['tag_toggle'];

	$fy_day = $_SESSION['fy_day'];
	$fy_month  = $_SESSION['fy_month'];

	$edit_url = $_GET['edit'];
	$start = $_GET['start'];
	$end = $_GET['end'];

	$dateObj   = DateTime::createFromFormat('!m', $fy_month);
	$month_name = $dateObj->format('F');

	$entry_date = date( 'Y-m-d H:i:s' );

	$latest_measure_date = $wpdb->get_row( "SELECT measure_date FROM data_charity INNER JOIN relation_user ON data_charity.loc_id=relation_user.loc_id WHERE relation_user.user_id=$user_id AND donation_type=$donation_id AND data_charity.id IN (SELECT MAX(id) FROM data_charity GROUP BY parent_id) ORDER BY measure_date DESC" );

	$latest_end = $latest_measure_date->measure_date;
	$latest_start = date( 'Y-m-d', strtotime( "$end -364 days" ) );

	$edit_rows = $wpdb->get_results( "SELECT data_charity.id, measure, custom_tag.tag AS measure_name, measure_date, measure_start, measure_end, master_tag.tag as value_type, value_type as value_type_id, amount, duration, data_charity.location AS location_id, custom_location.location, data_charity.note, data_charity.parent_id, data_charity.active, loc_name FROM data_charity LEFT JOIN data_measure ON (data_charity.measure=data_measure.parent_id AND data_measure.id IN (SELECT MAX(id) FROM data_measure GROUP BY parent_id)) LEFT JOIN custom_tag ON (data_measure.measure_name=custom_tag.parent_id AND custom_tag.id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id)) INNER JOIN master_tag ON data_charity.value_type=master_tag.id INNER JOIN profile_location ON (data_charity.loc_id=profile_location.parent_id AND profile_location.id IN (SELECT MAX(id) FROM profile_location GROUP BY parent_id)) INNER JOIN custom_location ON (data_charity.location=custom_location.parent_id AND custom_location.id IN (SELECT MAX(id) FROM custom_location GROUP BY parent_id)) INNER JOIN relation_user ON data_charity.loc_id=relation_user.loc_id WHERE donation_type=$donation_id AND relation_user.user_id=$user_id AND data_charity.id IN (SELECT MAX(id) FROM data_charity GROUP BY parent_id) AND measure_date BETWEEN '$start' AND '$end'" );

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
						if( $donation_id == 66 ) : ?> <th scope="col">Staff Time Donated</th> <?php else : ?> <th scope="col" class="filter-column">Value Type</th> <?php endif; ?>
						<th scope="col">Amount (or cash equivalent)</th> <?php
						if( $tag_toggle == 1 ) : ?> <th scope="col">Tags</th> <?php endif; ?>
						<th scope="col">Notes</th>
					</tr>
				</thead>

				<tbody> <?php

					foreach ( $edit_rows as $edit_row ) :

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

												$revision_rows = $wpdb->get_results( "SELECT data_charity.id, data_charity.entry_date, measure, custom_tag.tag AS measure_name, measure_date, measure_start, measure_end, master_tag.tag AS value_type, amount, duration, custom_location.location, data_charity.note, data_charity.parent_id, data_charity.active, loc_name, display_name, data_charity.user_id FROM data_charity LEFT JOIN data_measure ON (data_charity.measure=data_measure.parent_id AND data_measure.id IN (SELECT MAX(id) FROM data_measure GROUP BY parent_id)) LEFT JOIN custom_tag ON (data_measure.measure_name=custom_tag.parent_id AND custom_tag.id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id)) INNER JOIN master_tag ON data_charity.value_type=master_tag.id INNER JOIN profile_location ON (data_charity.loc_id=profile_location.parent_id AND profile_location.id IN (SELECT MAX(id) FROM profile_location GROUP BY parent_id)) INNER JOIN custom_location ON (data_charity.location=custom_location.parent_id AND custom_location.id IN (SELECT MAX(id) FROM custom_location GROUP BY parent_id)) LEFT JOIN yard_users ON data_charity.user_id=yard_users.id INNER JOIN relation_user ON data_charity.loc_id=relation_user.loc_id WHERE data_charity.parent_id=$edit_parent_id AND relation_user.user_id=$user_id ORDER BY data_charity.id DESC" );

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
													if( $donation_id == 66 ) : echo '<b>Staff Time Donated:</b> '.number_format( $revision_duration, 2 ).'<br />'; endif;
													if( $donation_id != 66 ) : echo '<b>Value Type:</b> '.$revision_value_type.'<br />'; endif;
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

								if ( isset( $_POST[$archive_charity] ) ) :

									$wpdb->insert( 'data_charity',
										array(
											'entry_date' => $entry_date,
											'record_type' => 'entry_revision',
											'measure' => $edit_measure,
											'measure_date' => $edit_measure_date,
											'donation_type' => $donation_id,
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

											<div class="modal-body"> <?php

												charity_form( $edit_charity, $latest_start, $latest_end, $edit_id, $donation_id, $edit_measure, $edit_measure_name, $edit_measure_date_formatted, $edit_donee_location_id, $edit_value_type_id, $edit_amount, $edit_duration, $edit_note, $edit_parent_id ); ?>

											</div>

										</div>
									</div>
								</div>

							</td>
							<td><span class="d-none"><?php echo $edit_measure_date.$edit_measure_start ?></span><?php if( empty( $edit_measure_date ) ) : echo $edit_measure_start_formatted.' to '.$edit_measure_end_formatted; else : echo $edit_measure_date_formatted; endif; ?></td> <?php
							if( $measure_toggle == 86 ) : ?><td><?php echo $edit_measure_name ?></td> <?php endif; ?>
							<td><?php echo $edit_donee_location ?></td> <?php
							if( $donation_id != 66 ) : ?> <td><?php echo $edit_value_type ?></td> <?php endif;
							if( $donation_id == 66 ) : ?> <td class="text-right"><?php echo number_format( $edit_duration, 2 ) ?></td> <?php endif; ?>
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
}


// CHART FILTER
function chart_dropdown_charity() {

	global $wpdb;

	$master_loc = $_SESSION['master_loc'];
	$filter = str_replace( '   ', ' - ', str_replace( '-', ' ', $_GET['filter'] ) ); // NEED TO GET THIS WORKING

	$dropdowns = $wpdb->get_results( "SELECT master_tag.tag FROM master_tag INNER JOIN custom_tag ON master_tag.id=custom_tag.tag_id WHERE custom_tag.cat_id=4 AND loc_id=$master_loc AND active=1 AND custom_tag.id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id) ORDER BY master_tag.tag ASC" );

	foreach( $dropdowns as $dropdown ) :
		$value_tag = $dropdown->tag;
		if( $filter == strtolower( $value_tag ) ) : $selected = 'selected'; else : $selected = ''; endif; ?>
		<option value="<?php echo $value_tag ?>" <?php echo $selected ?>><?php echo $value_tag ?></option> <?php
	endforeach;

}


// CHART DONATION DISTANCE MENU ITEM
function shortcode_chart_donation_distance_menu() {

	global $wpdb;

	$site_url = get_site_url();
	$master_loc = $_SESSION['master_loc'];

	$filter_row = $wpdb->get_row( "SELECT master_tag.tag as tag FROM master_tag INNER JOIN custom_tag ON master_tag.id=custom_tag.tag_id INNER JOIN data_operations ON custom_tag.parent_id=data_operations.utility_id WHERE custom_tag.cat_id=15 AND custom_tag.loc_id=$master_loc AND custom_tag.active=1 ORDER BY master_tag.tag ASC" );
	$filter = str_replace( ' ', '-', strtolower( $filter_row->tag ) );

	$measure_toggle = $_SESSION['measure_toggle'];

	$today = date( 'Y-m-d' );
	$fy_day = $_SESSION['fy_day'];
	$fy_month = $_SESSION['fy_month'];
	$fy_year_current = date( 'Y' );
	$fy_date_convert = date_create( $fy_month.'/'.$fy_day.'/'.$fy_year_current );
	$fy_date = date_format( $fy_date_convert, 'Y-m-d' );

	if( $fy_date < $today  ) : $fy_year = date( 'Y', strtotime( '-1 year' ) ); else : $fy_year = date( 'Y', strtotime( '-2 year' ) ); endif;

	$year_fy_start_date = $fy_month.'/'.$fy_day.'/'.$fy_year;
	$year_fy_start = date( 'Y-m-d', strtotime( $year_fy_start_date ) );
	$year_fy_start_10 = date( 'Y-m-d', strtotime( '-9 years', strtotime( $year_fy_start_date ) ) );
	$year_fy_end = date( 'Y-m-d', strtotime( '1 year -1 day', strtotime( $year_fy_start_date ) ) );
	$month_end = date( 'Y-m-d', strtotime('last day of previous month'));
	$month_start = date( 'Y-m-01', strtotime('-12 months'));
	$day_number = date( 'N' );
	$week_start = date( 'Y-m-d', strtotime( '-'.( 90+$day_number ).' days') );
	$week_end = date( 'Y-m-d', strtotime( '-'.$day_number.' days' ) );
	$day_start = date( 'Y-m-d', strtotime( '-14 day') );
	$day_end = date( 'Y-m-d', strtotime( '-1 day' ) );

	if( $measure_toggle == 82 ) :

		$measure = 'day';
		$start = $day_start;
		$end = $day_end;

	elseif( $measure_toggle == 83 ) :

		$measure = 'week';
		$start = $week_start;
		$end = $week_end;

	elseif( $measure_toggle == 84 ) :

		$measure = 'month';
		$start = $month_start;
		$end = $month_end;

	else :

		$measure = 'year';
		$start = $year_fy_start_10;
		$end = $year_fy_end;

	endif;

	$string = '<a class="mega-menu-link" href="'.$site_url.'/charts/?chart=fuel&series=usage&filter='.$filter.'&frequency='.$measure.'&start='.$start.'&end='.$end.'">Fuel</a>';

	return $string;
}
add_shortcode('shortcode-chart-donation-distance-menu', 'shortcode_chart_donation_distance_menu');
