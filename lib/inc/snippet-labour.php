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

