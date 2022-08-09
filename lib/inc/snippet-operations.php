<?php ob_start();

/* Includes: OPERATIONS SNIPPETS

@package	Yardstick
@author		Digital Rockpool
@link		https://yardstick.co.uk
@copyright	Copyright (c) 2021, Digital Rockpool LTD
@license	GPL-2.0+ */


// ADD SETTINGS
function operations_add_setting( $set_id, $cat_id, $title, $title_singular ) {

	global $wpdb;
	global $post;

	$site_url = get_site_url();
	$slug = $post->post_name;
	$setting_query = $_GET['setting'];

	$user_id = get_current_user_id();
	$master_loc = $_SESSION['master_loc'];
	$entry_date = date( 'Y-m-d H:i:s' );

	if( $set_id == 7 ) : // plastics

		$utility_dropdowns = $wpdb->get_results( "SELECT master_tag.id, master_tag.tag FROM master_tag WHERE cat_id=$cat_id ORDER BY master_tag.tag ASC" );

	else :

		$utility_dropdowns = $wpdb->get_results( "SELECT master_tag.id AS id, master_tag.tag AS tag FROM master_tag WHERE cat_id=$cat_id AND NOT EXISTS (SELECT custom_tag.tag_id FROM custom_tag WHERE master_tag.id=custom_tag.tag_id AND loc_id=$master_loc) GROUP BY tag ORDER BY tag ASC" );

	endif;

	if( empty( $utility_dropdowns ) ) : ?>

		<p>All <?php echo strtolower( $title ); ?> have been added. If you require a new <?php echo strtolower( $title_singular ); ?> please email <a href="mailto:support@yardstick.co.uk" title="support@yardstick.co.uk">support@yardstick.co.uk</a></p> <?php

	else : ?>

		<form method="post" name="add-operation-settings" id="add-operation-settings" class="needs-validation" novalidate> <?php

			if( $set_id == 14 ) : // operation setting ?>

				<div class="form-row">

					<div class="col-md-4 mb-3">
						<label for="set-capacity">Capacity<sup class="text-danger">*</sup></label>
						<input type="number" class="form-control" id="set-capacity" name="set-operation-tag[]" min="1" step="1" required>
						<input type="hidden" name="set-operation-type[]" value="289"> <!-- value = master_tag.id -->
						<div class="invalid-feedback">Please enter a whole number greater than or equal to 1</div>

					</div>

					<div class="col-md-4 mb-3">
						<label for="set-days-open">Days open<sup class="text-danger">*</sup></label>
						<input type="number" class="form-control" id="set-days-open" name="set-operation-tag[]" min="1" max="365" step="1" required>
						<input type="hidden" name="set-operation-type[]" value="280"> <!-- value = master_tag.id -->
						<div class="invalid-feedback">Please enter a whole number between 1 and 365</div>
					</div>

					<div class="col-md-4 mb-3">
						<label for="set-total-area">Total area in m2<sup class="text-danger">*</sup></label>
						<input type="number" class="form-control" id="set-total-area" name="set-operation-tag[]" min="1" step="0.01" required>
						<input type="hidden" name="set-operation-type[]" value="288"> <!-- value = master_tag.id -->
						<div class="invalid-feedback">Please enter a number greater than or equal to 0.01</div>
					</div>

				</div> <?php

			else : ?>
				<div id="repeater-field">
					<div class="entry form-row mb-1">
						<div class="<?php if( $set_id == 6 ) : echo 'col-10';  elseif( $set_id == 7 ) : echo 'col-3';  else : echo 'col-5'; endif; ?>">
							<select id="set-operation-type" name="set-operation-type[]" class="form-control set-utility-type" required>
								<option value="" selected disabled>Select <?php echo $title_singular ?> *</option> <?php

								foreach( $utility_dropdowns as $utility_dropdown ) : ?>
									<option value="<?php echo $utility_dropdown->id ?>"><?php echo $utility_dropdown->tag ?></option> <?php
								endforeach; ?>

							</select>
							<div class="invalid-feedback">Please select <?php echo strtolower( $title_singular ); ?> </div>
						</div> <?php

						if( $set_id == 7 ) : // plastics ?>

							<div class="col-3">
								<input type="text" class="form-control" id="set-operation-tag" name="set-operation-tag[]" placeholder="Description">
							</div> <?php

						endif;

						if( $set_id != 6 ) : // disposal methods ?>

							<div class="<?php if( $set_id == 7 ) : echo 'col-2';  else : echo 'col-5'; endif; ?>">
								<select id="set-operation-unit" name="set-operation-unit[]" class="form-control set-utility-unit" required>
									<option value="" selected disabled>Select Units *</option> <?php

									$unit_dropdowns = $wpdb->get_results( "SELECT master_tag.id, master_tag.tag FROM master_tag INNER JOIN relation_tag ON master_tag.id=relation_tag.child_id WHERE relation='$title_singular-unit' GROUP BY master_tag.tag" );

									foreach( $unit_dropdowns as $unit_dropdown ) : ?>
										<option value="<?php echo $unit_dropdown->id ?>"><?php echo $unit_dropdown->tag ?></option> <?php
									endforeach; ?>

								</select>
								<div class="invalid-feedback">Please select units</div>
							</div> <?php

						endif;

						if( $set_id == 7 ) : // plastics ?>

							<div class="col-2">
								<input type="number" class="form-control" id="set-operation-size" name="set-operation-size[]" min="0" step="0.01" placeholder="Quantity *" required>
								<div class="invalid-feedback">Please enter a number greater than or equal to 0.01</div>
							</div> <?php

						endif; ?>

						<div class="col-2">
							<span class="input-group-btn"><button type="button" class="btn btn-success btn-add"><i class="fas fa-plus"></i></button></span>
						</div>
					</div>
				</div> <?php

			endif; ?>

			<div class="form-row">
				<div class="col-2 offset-10 mb-3"><button class="btn btn-primary <?php if( $set_id !=14 ) : echo 'float-none'; endif; ?>" type="submit" name="add-operation-settings">Add</button></div>
			</div>
		</form> <?php

		$set_operation_type_array = $_POST['set-operation-type'];
		$set_operation_tag_array = $_POST['set-operation-tag'];
		$set_operation_unit_array = $_POST['set-operation-unit'];
		$set_operation_size_array = $_POST['set-operation-size'];

		if ( isset( $_POST['add-operation-settings'] ) ) :

			foreach( $set_operation_type_array as $index => $set_operation_type_array ) :

				$tag_id = $set_operation_type_array;
				if( empty( $set_operation_tag_array ) ) : $tag = NULL; else : $tag = $set_operation_tag_array[$index]; endif;
				if( empty( $set_operation_unit_array ) ) : $unit_id = NULL; else : $unit_id = $set_operation_unit_array[$index]; endif;
				if( empty( $set_operation_size_array ) ) : $size = NULL; else : $size = $set_operation_size_array[$index]; endif;

				if( $set_id == 7 ) : // plastic

					$tag_check = $wpdb->get_row( "SELECT tag_id FROM custom_tag WHERE tag='$tag' AND size=$size AND unit_id=$unit_id AND loc_id=$master_loc" );

				else :

					$tag_check = $wpdb->get_row( "SELECT tag_id FROM custom_tag WHERE tag_id=$tag_id AND loc_id=$master_loc" );

				endif;

				if( empty( $tag_check ) ) :

					$wpdb->insert( 'custom_tag',
						array(
							'entry_date' => $entry_date,
							'record_type' => 'entry',
							'tag' => $tag,
							'tag_id' => $tag_id,
							'size' => $size,
							'unit_id' => $unit_id,
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
	endif; ?>

	<script>
		$(document).on('change', '.set-utility-type', function(){
			var unitPOP = $(this).val();
			var dropDown = $(this).parent().parent().find(".set-utility-unit");
			dropDown.empty();
			$.ajax({
				url:"<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php",
				type:'GET',
				data:'action=operations_unit_settings&utilityID=' + unitPOP,
				success:function(results) {
					dropDown.append(results);
				}
			});
		});
	</script> <?php
}


// LATEST ENTRIES
function operations_latest_entries( $add, $title, $extra_value ) {

	global $wpdb;

	$cat_id = $extra_value;
	$user_id = get_current_user_id();

	$add_rows = $wpdb->get_results( "SELECT measure_date, measure_start, master_tag.tag AS master_tag, custom_tag.tag AS custom_tag, amount FROM data_operations LEFT JOIN data_measure ON (data_operations.measure=data_measure.parent_id AND data_measure.id IN (SELECT MAX(id) FROM data_measure GROUP BY parent_id)) INNER JOIN custom_tag ON (data_operations.utility_id=custom_tag.parent_id AND custom_tag.id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id)) INNER JOIN master_tag ON master_tag.id=custom_tag.tag_id INNER JOIN relation_user ON data_operations.loc_id=relation_user.loc_id WHERE relation_user.user_id=$user_id AND master_tag.cat_id=$cat_id AND data_operations.id IN (SELECT MAX(id) FROM data_operations GROUP BY parent_id) GROUP BY data_operations.id ORDER BY data_operations.id DESC LIMIT 5" );

	if( empty( $add_rows) ) :

		echo '<p>No '.strtolower( $title ).' data has been added.</p>';

	else : ?>

		<div class="table-responsive-xl mb-4">
			<table id="latest" class="table table-borderless">
				<thead>
					<tr>
						<th scope="col">Date</th>
						<th scope="col"><?php echo $title ?></th>
						<th scope="col">Amount</th>
					</tr>
				</thead>

				<tbody> <?php

					foreach ( $add_rows as $add_row ) :

						$latest_date = $add_row->measure_date;
						$latest_date_formatted = date_format( date_create( $latest_date ), 'd-M-Y' );
						$latest_measure_start = $add_row->measure_start;
						$latest_measure_start_formatted = date_format( date_create( $latest_measure_start ), 'd-M-Y' );
						$latest_master_tag = $add_row->master_tag;
						$latest_custom_tag_entry = $add_row->custom_tag;
						$latest_amount = $add_row->amount;

						if( !empty( $latest_custom_tag_entry ) ) : $latest_custom_tag = ' - '.$latest_custom_tag_entry; endif; ?>

						<tr>
						<td nowrap><?php if( empty( $latest_date ) ) : echo $latest_measure_start_formatted; else : echo $latest_date_formatted; endif; ?></td> <?php
						if( $loc_number > 1 ) : ?> <td scope="col"><?php echo substr( $latest_loc_name, 10 ) ?></td> <?php endif; ?>
						<td><?php echo $latest_master_tag.$latest_custom_tag ?></td>
						<td class="text-right" nowrap><?php echo number_format($latest_amount, 2) ?></td>
						</tr> <?php

					endforeach; ?>

				</tbody>
			</table>
		</div> <?php

	endif;

}


// OPERATIONS FORM
function operations_form( $cat_id, $latest_start, $latest_end, $edit_operations, $edit_id, $employee_id, $edit_measure, $edit_measure_name, $edit_measure_date_formatted, $edit_utility_id, $edit_amount, $edit_cost, $edit_disposal, $edit_disposal_id, $edit_note, $edit_parent_id ) {

	global $wpdb;
	global $post;

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

	if( empty( $edit_operations ) ) : $update_operations = 'edit_operations'; else : $update_operations = $edit_operations; endif; ?>

	<form method="post" name="edit" id="<?php echo $update_operations ?>" class="needs-validation" novalidate>
		<div class="form-row"> <?php

			if( $measure_toggle == 86 ) : // custom measures

				custom_measure_dropdown( $edit_measure );

			elseif( $measure_toggle == 85 ) : // yearly measure

				reporting_year_start_date( $edit_measure_date_formatted );

			else : ?>

				<div class="col-md-4 mb-3">
					<label class="control-label" for="edit-measure-date">Date<sup class="text-danger">*</sup></label>
					<div class="input-group mb-2">
						<div class="input-group-prepend"><div class="input-group-text"><i class="far fa-calendar-alt"></i></div></div>
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
				<div class="entry form-row mb-1">
					<div class="col-4">
						<select class="form-control edit-utility" id="edit-utility" name="edit-utility[]" required>
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
							<select class="form-control edit-disposal" name="edit-disposal[]" id="edit-disposal" required>
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
						<button type="button" class="btn btn-success btn-add"><i class="fas fa-plus"></i></button>
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

					<select class="selectpicker form-control" name="edit-tag[]" multiple title="Select Tags" multiple data-live-search="true"> <?php
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
    			<textarea class="form-control" name="edit-note" id="edit-note" aria-describedby="editNote" placeholder="Notes"><?php echo $edit_note ?></textarea>
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

}


// OPERATIONS EDIT
function operations_edit( $edit, $latest_start, $latest_end, $title, $extra_value ) {

	global $wpdb;
	global $post;

	$site_url = get_site_url();
	$slug = $post->post_name;

	$cat_id = $extra_value;

	$user_id = get_current_user_id();
	$master_loc = $_SESSION['master_loc'];
	$measure_toggle = $_SESSION['measure_toggle'];
	$tag_toggle = $_SESSION['tag_toggle'];
	$fy_day = $_SESSION['fy_day'];
	$fy_month = $_SESSION['fy_month'];

	$edit_url = $_GET['edit'];
	$start = $_GET['start'];
	$end = $_GET['end'];

	$dateObj   = DateTime::createFromFormat('!m', $fy_month);
	$month_name = $dateObj->format('F');

	$entry_date = date( 'Y-m-d H:i:s' );

	$latest_measure_date = $wpdb->get_row( "SELECT measure_date FROM data_operations INNER JOIN relation_user ON data_operations.loc_id=relation_user.loc_id INNER JOIN custom_tag ON data_operations.utility_id=custom_tag.id WHERE relation_user.user_id=$user_id AND cat_id=$cat_id AND data_operations.id IN (SELECT MAX(id) FROM data_operations GROUP BY parent_id) ORDER BY measure_date DESC" );

	$latest_end = $latest_measure_date->measure_date;
	$latest_start = date( 'Y-m-d', strtotime( "$end -364 days" ) );

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
						if( $edit == 'waste' ) : ?> <th scope="col" class="filter-column">Disposal Methods</th> <?php endif; ?>
						<th scope="col"><?php if( $edit == 'plastic' ) : echo 'Number Purchase'; else : echo 'Amount'; endif; ?></th>
						<th scope="col"><?php if( $edit == 'plastic' ) : echo 'Total Cost'; else : echo 'Cost'; endif; ?></th> <?php
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

												$revision_rows = $wpdb->get_results( "SELECT data_operations.id, data_operations.entry_date, measure, measure_name.tag as measure_name, measure_date, measure_start, measure_end, utility_tag.tag AS utility, custom_tag.tag AS plastic, disposal_tag.tag AS disposal, custom_tag.size, unit_tag.tag AS unit, amount, cost, data_operations.note, data_operations.parent_id, data_operations.active, display_name FROM data_operations LEFT JOIN data_measure ON (data_operations.measure=data_measure.parent_id AND data_measure.id IN (SELECT MAX(id) FROM data_measure GROUP BY parent_id)) INNER JOIN custom_tag ON (data_operations.utility_id=custom_tag.parent_id AND custom_tag.id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id)) LEFT JOIN custom_tag measure_name ON (data_measure.measure_name=measure_name.parent_id AND custom_tag.id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id)) INNER JOIN master_tag utility_tag ON utility_tag.id=custom_tag.tag_id LEFT JOIN master_tag disposal_tag ON data_operations.disposal_id=disposal_tag.id INNER JOIN master_tag unit_tag ON unit_tag.id=custom_tag.unit_id INNER JOIN yard_users ON data_operations.user_id=yard_users.id RIGHT JOIN relation_user ON data_operations.loc_id=relation_user.loc_id WHERE data_operations.parent_id=$edit_parent_id AND relation_user.user_id=$user_id AND utility_tag.cat_id=$cat_id GROUP BY data_operations.id ORDER BY data_operations.id DESC" );

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

								<form method="post" name="archive" id="<?php echo $archive_operations ?>" class="d-inline-block">
									<button type="submit" class="btn <?php echo $btn_style ?> d-inline-block" name="<?php echo $archive_operations ?>"><?php echo $edit_value ?></button>
								</form> <?php

								if ( isset( $_POST[$archive_operations] ) ) :

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

											<div class="modal-body"> <?php

												operations_form( $cat_id, $latest_start, $latest_end, $edit_operations, $edit_id, $employee_id, $edit_measure, $edit_measure_name, $edit_measure_date_formatted, $edit_utility_id, $edit_amount, $edit_cost, $edit_disposal, $edit_disposal_id, $edit_note, $edit_parent_id ); ?>

										</div>

										</div>
									</div>
								</div>

							</td>
							<td><span class="d-none"><?php echo $edit_measure_date.$edit_measure_start ?></span><?php if( empty( $edit_measure_date ) ) : echo $edit_measure_start_formatted.' to '.$edit_measure_end_formatted; else : echo $edit_measure_date_formatted; endif; ?></td> <?php
							if( $measure_toggle == 86 ) : ?><td><?php echo $edit_measure_name; ?></td> <?php endif; ?>
							<td><?php echo $edit_utility; if(!empty ( $edit_plastic ) ) : echo ' - '.$edit_plastic;  endif; ?></td>
							<td><?php if(!empty ( $edit_size ) ) : $edit_size_decimal_clean = rtrim( number_format( $edit_size, 2 ) , '0' ); echo rtrim( $edit_size_decimal_clean, '.' ).' ';  endif; echo $edit_unit; if( $edit_size > 1 && $edit_unit !== 'per pack' && $edit_unit != 'g' && $edit_unit != 'kg' && $edit_unit != 'ml' && $edit_unit != 'oz' ) : echo 's'; endif; ?></td> <?php
							if( $edit == 'waste' ) : ?> <td><?php echo $edit_disposal; ?></td> <?php endif; ?>
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
						if( $edit == 'waste' ) : ?> <th></th> <?php endif; ?>
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


// CHART FILTER
function chart_dropdown_master_tag() {

	global $wpdb;

	$master_loc = $_SESSION['master_loc'];
	$chart = str_replace( '-', ' ', $_GET['chart'] );
	$filter = str_replace( '   ', ' - ', str_replace( '-', ' ', $_GET['filter'] ) );

	$cat_id_lookup = $wpdb->get_row( "SELECT id FROM master_category WHERE category='$chart'");
	$cat_id = $cat_id_lookup->id;

	$dropdowns = $wpdb->get_results( "SELECT master_tag.tag FROM master_tag INNER JOIN custom_tag ON master_tag.id=custom_tag.tag_id WHERE custom_tag.cat_id=$cat_id AND loc_id=$master_loc AND active=1 GROUP BY parent_id ORDER BY master_tag.tag ASC" );

	foreach( $dropdowns as $dropdown ) :
		$value_tag = $dropdown->tag;
		if( $filter == strtolower( $value_tag ) ) : $selected = 'selected'; else : $selected = ''; endif; ?>
		<option value="<?php echo $value_tag ?>" <?php echo $selected ?>><?php echo $value_tag ?></option> <?php
	endforeach;

}

function chart_dropdown_plastic_tag( ) {

	global $wpdb;

	$master_loc = $_SESSION['master_loc'];
	$chart = str_replace( '-', ' ', $_GET['chart'] );

	$cat_id_lookup = $wpdb->get_row( "SELECT id FROM master_category WHERE category='$chart'");
	$cat_id = $cat_id_lookup->id;

	$dropdowns = $wpdb->get_results( "SELECT master_tag.tag AS system_tag, custom_tag.tag AS custom_tag FROM master_tag INNER JOIN custom_tag ON master_tag.id=custom_tag.tag_id INNER JOIN data_operations ON custom_tag.parent_id=data_operations.utility_id WHERE custom_tag.cat_id=$cat_id AND custom_tag.loc_id=$master_loc AND custom_tag.active=1 AND custom_tag.id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id) GROUP BY custom_tag.parent_id ORDER BY master_tag.tag ASC" );

	foreach( $dropdowns as $dropdown ) : ?>
		<option value="<?php echo $dropdown->system_tag.'|'.$dropdown->custom_tag; ?>"><?php echo $dropdown->system_tag.' - '.$dropdown->custom_tag ?></option> <?php
	endforeach;

}





// CHART WATER MENU ITEM
function shortcode_chart_water_menu() {

	global $wpdb;

	$site_url = get_site_url();
	$master_loc = $_SESSION['master_loc'];

	$filter_row = $wpdb->get_row( "SELECT master_tag.tag as tag FROM master_tag INNER JOIN custom_tag ON master_tag.id=custom_tag.tag_id INNER JOIN data_operations ON custom_tag.parent_id=data_operations.utility_id WHERE custom_tag.cat_id=18 AND custom_tag.loc_id=$master_loc AND custom_tag.active=1 ORDER BY master_tag.tag ASC" );
	$filter = $filter = str_replace( ' ', '-', strtolower( $filter_row->tag ) );

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

	$string = '<a class="mega-menu-link" href="'.$site_url.'/charts/?chart=water&series=usage&filter='.$filter.'&frequency='.$measure.'&start='.$start.'&end='.$end.'">Water</a>';

	return $string;
}
add_shortcode('shortcode-chart-water-menu', 'shortcode_chart_water_menu');


// CHART WASTE MENU ITEM
function shortcode_chart_waste_menu() {

	global $wpdb;

	$site_url = get_site_url();
	$master_loc = $_SESSION['master_loc'];

	$filter_row = $wpdb->get_row( "SELECT master_tag.tag as tag FROM master_tag INNER JOIN custom_tag ON master_tag.id=custom_tag.tag_id INNER JOIN data_operations ON custom_tag.parent_id=data_operations.utility_id WHERE custom_tag.cat_id=19 AND custom_tag.loc_id=$master_loc AND custom_tag.active=1 ORDER BY master_tag.tag ASC" );
	$filter = $filter = str_replace( ' ', '-', strtolower( $filter_row->tag ) );

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

	$string = '<a class="mega-menu-link" href="'.$site_url.'/charts/?chart=water&series=usage&filter='.$filter.'&frequency='.$measure.'&start='.$start.'&end='.$end.'">Waste</a>';

	return $string;
}
add_shortcode('shortcode-chart-waste-menu', 'shortcode_chart_waste_menu');


// CHART PLASTIC MENU ITEM
function shortcode_chart_plastic_menu() {

	global $wpdb;

	$site_url = get_site_url();
	$master_loc = $_SESSION['master_loc'];

	$filter_row = $wpdb->get_row( "SELECT master_tag.tag AS system_tag, custom_tag.tag AS custom_tag FROM master_tag INNER JOIN custom_tag ON master_tag.id=custom_tag.tag_id INNER JOIN data_operations ON custom_tag.parent_id=data_operations.utility_id WHERE custom_tag.cat_id=40 AND custom_tag.loc_id=$master_loc AND custom_tag.active=1 AND custom_tag.id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id) ORDER BY master_tag.tag ASC" );
	$filter = str_replace( ' ', '-', strtolower( $filter_row->system_tag ) );
	$filter2 = str_replace( ' ', '-', strtolower( $filter_row->custom_tag ) );

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

	$string = '<a class="mega-menu-link" href="'.$site_url.'/charts/?chart=plastic&series=usage&filter='.$filter.'|'.$filter2.'&frequency='.$measure.'&start='.$start.'&end='.$end.'">Plastic</a>';

	return $string;
}
add_shortcode('shortcode-chart-plastic-menu', 'shortcode_chart_plastic_menu');?>
