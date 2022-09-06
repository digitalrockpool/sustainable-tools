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

