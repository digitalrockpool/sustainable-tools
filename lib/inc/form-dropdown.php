<?php

/* Includes: Form Dropdowns

@package	Yardstick
@author		Digital Rockpool
@link		https://yardstick.co.uk
@copyright	Copyright (c) 2019, Digital Rockpool LTD
@license	GPL-2.0+ */

// GRAVITY DROPDOWNS: COUNTRY / TEAM MEMBER
add_filter( 'gform_pre_render_114', 'populate_edit_location_country' );
add_filter( 'gform_admin_pre_render_114', 'populate_edit_location_country' );
function populate_edit_location_country( $form ) {
	
	global $wpdb;

	$results = $wpdb->get_results( "SELECT country FROM master_country ORDER BY country ASC" );

	foreach( $results as $rows ) :
		$choices[] = array( 'text' => $rows->country, 'value' => $rows->country );
	endforeach;

	foreach( $form['fields'] as &$field ) :
		if( $field['id'] == 51 ) :
			$field['placeholder'] = 'Select Country';
			$field['choices'] = $choices;
		endif;
	endforeach;

	return $form;
}

add_filter( 'gform_pre_render_120', 'populate_add_team_member' );
add_filter( 'gform_admin_pre_render_120', 'populate_add_team_member' );
function populate_add_team_member( $form ) {
	
	global $wpdb;
	
	$user_role = $_SESSION['user_role'];
	
	if( $user_role == 222 ) : /* super admin */

		$results = $wpdb->get_results( "SELECT id, tag FROM master_tag WHERE cat_id=35 ORDER BY id ASC" ); 
	
	else : 
	
		$results = $wpdb->get_results( "SELECT id, tag FROM master_tag WHERE cat_id=35 AND id!=222 ORDER BY id ASC" ); 
	
	endif;

	foreach( $results as $rows ) :
		$choices[] = array( 'text' => $rows->tag, 'value' => $rows->id );
	endforeach;

	foreach( $form['fields'] as &$field ) :
		if( $field['id'] == 17 ) :
			$field['placeholder'] = 'Select User Role';
			$field['choices'] = $choices;
		endif;
	endforeach;

	return $form;
}


// SETTINGS: REPORT SETTINGS SECTOR
function report_settings_sector() {

 if( isset( $_GET['industryID'] ) ) :

		$industry_id = $_GET['industryID'];

		global $wpdb;
		$results = $wpdb->get_results( "SELECT master_tag.id, tag FROM master_tag INNER JOIN relation_tag ON master_tag.id=relation_tag.child_id WHERE parent_id=$industry_id ORDER BY tag ASC");
		
		$option = '<option selected disabled>Select Sector</option>';

		foreach( $results as $rows ) :
			$option .= '<option value="'.$rows->id.'">'.$rows->tag.'</option>';
		endforeach;
	
		echo $option;
	
		die();
	endif;
}
add_action( 'wp_ajax_nopriv_report_settings_sector', 'report_settings_sector' );
add_action( 'wp_ajax_report_settings_sector', 'report_settings_sector' );


// SETTINGS: REPORT SETTINGS SUBSECTOR
function report_settings_subsector() {

 if( isset( $_GET['sectorID'] ) ) :

		$sector_id = $_GET['sectorID'];

		global $wpdb;
		$results = $wpdb->get_results( "SELECT master_tag.id, tag FROM master_tag INNER JOIN relation_tag ON master_tag.id=relation_tag.child_id WHERE parent_id=$sector_id ORDER BY tag ASC" );
		
		$option = '<option selected disabled>Select Subsector</option>';

		foreach( $results as $rows ) :
			$option .= '<option value="'.$rows->id.'">'.$rows->tag.'</option>';
		endforeach;
	
		echo $option;
	
		die();
	
	endif;
}
add_action( 'wp_ajax_nopriv_report_settings_subsector', 'report_settings_subsector' );
add_action( 'wp_ajax_report_settings_subsector', 'report_settings_subsector' );


// DATA: REPORTING YEAR START DATE
function reporting_year_start_date( $edit_measure_date_formatted ) {
	
	$fy_day = $_SESSION['fy_day'];
	$fy_month = $_SESSION['fy_month'];
	
	$dateObj = DateTime::createFromFormat('!m', $fy_month);
	$month_name = $dateObj->format('F'); ?>

	<div class="col-md-4 mb-3">

		<label class="d-block">Reporting Year Start Date<sup class="text-danger">*</sup></label>
		<div class="input-group">
			<div class="input-group-prepend"><label class="input-group-text" for="edit-measure-date"><?php echo $fy_day.' '.$month_name; ?></label></div>

			<select class="custom-select" name="edit-measure-date" id="edit-measure-date"> <?php

				if( empty( $edit_measure_date_formatted ) ) : $selected_year = date( 'Y' ); else : $selected_year = date_format( date_create( $edit_measure_date_formatted ), 'Y' ); endif;
				$earliest_year = date( 'Y',strtotime( '-10 year' ) );
				$latest_year = date( 'Y',strtotime( '-1 year' ) );
	
				foreach ( range( $latest_year, $earliest_year ) as $i ) :
	
					$date_create = $i.'-'.$fy_month.'-'.$fy_day;
					$value = date( 'Y-m-d', strtotime( $date_create ) );
	
					if( $selected_year == $i ) : $selected = 'selected'; else : $selected = ''; endif; 

					echo '<option value="'.$value.'" '.$selected.'>'.$i.'</option>';

				endforeach; ?>

			</select>
		</div>
	</div> <?php

}


// SETTINGS: OPERATIONS UNIT
function operations_unit_settings() {

 if( isset( $_GET['utilityID'] ) ) :

		$utility_id = $_GET['utilityID'];

		global $wpdb;
	
		$dropdowns = $wpdb->get_results( "SELECT master_tag.id, master_tag.tag FROM master_tag INNER JOIN relation_tag ON master_tag.id=relation_tag.child_id WHERE parent_id=$utility_id AND relation LIKE '%unit'" );
		
		$option = '<option value selected disabled>Select Units *</option>';

		foreach( $dropdowns as $dropdown ) :
			$option .= '<option value="'.$dropdown->id.'">'.$dropdown->tag.'</option>';
		endforeach;
	
		echo $option;
	
		die();
	endif;
}
add_action( 'wp_ajax_nopriv_operations_unit_settings', 'operations_unit_settings' );
add_action( 'wp_ajax_operations_unit_settings', 'operations_unit_settings' );


// DATA: CUSTOM MEASURES
function custom_measure_dropdown( $edit_measure ) {
	
	global $wpdb; 

	$site_url = get_site_url();
	
	$master_loc = $_SESSION['master_loc'];
	$measure_count = $_SESSION['measure_count']; ?>
	
	<div class="<?php if( empty( $measure_count ) ) : echo 'col-md-8'; else : echo 'col-md-12'; endif; ?> mb-3">
		<label for="edit-measure">Measure<sup class="text-danger">*</sup></label>
		<select class="form-control" name="edit-measure" id="edit-measure" required>
			<option value="">Select Measure</option> <?php

			$dropdowns = $wpdb->get_results( "SELECT data_measure.id, custom_tag.id, data_measure.parent_id, tag, measure_start, measure_end FROM data_measure LEFT JOIN custom_tag ON (custom_tag.parent_id=data_measure.measure_name AND custom_tag.id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id)) WHERE data_measure.loc_id=$master_loc AND data_measure.active=1 AND data_measure.id IN (SELECT MAX(id) FROM data_measure GROUP BY parent_id) ORDER BY tag ASC" );

			foreach ($dropdowns as $dropdown ) :

				$measure_parent_id = $dropdown->parent_id;
				$measure_name = $dropdown->tag;
				$measure_start = $dropdown->measure_start;
				$measure_start_formatted = date_format( date_create( $measure_start ), 'd-M-Y' );
				$measure_end = $dropdown->measure_end;
				$measure_end_formatted = date_format( date_create( $measure_end ), 'd-M-Y' );

				if( $edit_measure == $measure_parent_id ) : $selected = 'selected'; else : $selected = ''; endif; ?>

					<option value="<?php echo $measure_parent_id ?>" <?php echo $selected ?>><?php echo $measure_name.': '.$measure_start_formatted.' to '.$measure_end_formatted ?></option> <?php

				endforeach; ?>

		</select>
	</div> <?php
	
	if( empty( $measure_count ) ) : ?>

		<div class="col-md-4 mb-3 d-flex align-items-end">
															
			<small>Measures are set to custom. To add data, you must add a <a href="<?php echo $site_url ?>/data/?add=measures" title="time period">time period</a> first.</small> 
				
		</div><?php
	
	endif;
}


// MEASURE FUNCTIONS ???????????????????????????????? NEED TO BE FIXED WHEN TESTING MEASURES
function populate_data_measure_name() {

	if( isset( $_POST['locationID'] ) ) :

		$loc_name=$_POST['locationID'];

		global $wpdb;

		$results = $wpdb->get_results( "SELECT measure_name, tag FROM custom_tag INNER JOIN data_measure ON custom_tag.id=data_measure.measure_name LEFT JOIN profile_location ON data_measure.loc_id=profile_location.loc_id WHERE profile_location.loc_name='$loc_name' GROUP BY tag ORDER BY tag ASC" );

		foreach( $results as $rows ) :
			$option .= '<option value="'.$rows->measure_name.'">';
			$option .= $rows->tag;
			$option .= '</option>';
			endforeach;

		echo '<option value="0" disabled selected>Select Measure Name</option>'.$option;
		die();
	endif;
}
add_action( 'wp_ajax_nopriv_populate_data_measure_name', 'populate_data_measure_name' );
add_action( 'wp_ajax_populate_data_measure_name', 'populate_data_measure_name' );

function populate_data_measure_date() {

	if( isset( $_POST['measure_nameID'] ) ) :

		$measure_name=$_POST['measure_nameID'];

		global $wpdb;

		$results = $wpdb->get_results( "SELECT id, measure_start, measure_end FROM data_measure WHERE measure_name=$measure_name" );

		foreach( $results as $rows ) :
			$option .= '<option value="'.$rows->id.'">';
			$option .= date( "d-M-Y", strtotime( $rows->measure_start ) ).' to '.date( "d-M-Y", strtotime( $rows->measure_end ) );
			$option .= '</option>';
		endforeach;

		echo '<option value="0" disabled selected>Select Date Range</option>'.$option;
		die();
		
	endif;
}
add_action( 'wp_ajax_nopriv_populate_data_measure_date', 'populate_data_measure_date' );
add_action( 'wp_ajax_populate_data_measure_date', 'populate_data_measure_date' );



// SETTINGS: LOCATION - COUNTRY
add_filter( 'gform_pre_render_83', 'populate_setting_location' );
add_filter( 'gform_pre_render_131', 'populate_setting_location' );
function populate_setting_location( $form ) {
	
	global $wpdb;

	$results = $wpdb->get_results( "SELECT country FROM master_country ORDER BY country ASC" );

	foreach( $results as $rows ) :
		$choices[] = array( 'text' => $rows->country, 'value' => $rows->country );
	endforeach;

	foreach( $form['fields'] as &$field ) :
		if( $field['id'] == 18 ) :
			$field['choices'] = $choices;
		endif;
	endforeach;

	return $form;
}



// DATA: MEASURE - LOCATION - NEED TO UPDATE FOR ENTERPRISE
add_filter( 'gform_pre_render_15', 'populate_data_measure_location' );
function populate_data_measure_location( $form ) {

	global $wpdb;
	$user_id = get_current_user_id();
	
	$results = $wpdb->get_results( "SELECT loc_name FROM profile_location INNER JOIN relation_user ON relation_user.loc_id=profile_location.loc_id WHERE relation_user.user_id=$user_id AND relation_user.user_role!=225 AND active=1 ORDER BY loc_name ASC" );

	foreach( $results as $result ) :
		$choices[] = array( 'text' => $result->loc_name, 'value' => $result->loc_name );
	endforeach;

	foreach( $form['fields'] as &$field ) :
		if( $field['id'] == 53 ) :
			$field['placeholder'] = 'Select Location';
			$field['choices'] = $choices;
		endif;
	endforeach;

	return $form;
}


// DATA: MEASURE - NAME
add_filter( 'gform_pre_render_15', 'populate_data_measure_measure_name' );
function populate_data_measure_measure_name( $form ) {

	global $wpdb;
	$master_loc = $_SESSION['master_loc'];
	
	$dropdowns = $wpdb->get_results( "SELECT parent_id, tag FROM custom_tag WHERE loc_id=$master_loc AND cat_id=32 AND active=1 AND id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id) ORDER BY tag ASC" );

	foreach( $dropdowns as $dropdown ) :
		$choices[] = array( 'text' => $dropdown->tag, 'value' => $dropdown->tag );
	endforeach;

	foreach( $form['fields'] as &$field ) :
		if( $field['id'] == 42 ) :
			$field['placeholder'] = 'Select Measure Name';
			$field['choices'] = $choices;
		endif;
	endforeach;

	return $form;
}


// DATA: MEASURE - NEPALI MONTH
add_filter( 'gform_pre_render_15', 'populate_edit_location_nepali_month' );
add_filter( 'gform_admin_pre_render_15', 'populate_edit_location_nepali_month' );
function populate_edit_location_nepali_month( $form ) {
	
	global $wpdb;

	$results = $wpdb->get_results( "SELECT tag FROM master_tag WHERE cat_id=38 ORDER BY id ASC" );

	foreach( $results as $rows ) :
		$choices[] = array( 'text' => $rows->tag, 'value' => $rows->tag );
	endforeach;

	foreach( $form['fields'] as &$field ) :
		if( $field['id'] == 57 ) :
			$field['placeholder'] = 'Select Month';
			$field['choices'] = $choices;
		endif;
	endforeach;

	return $form;
}


// DATA: OPERATIONS - MEASURE WRONG FORM NUMBER SHOULD BE 130
/* $loc_number = $_SESSION['loc_number'];

if( $loc_number > 1 ) :

	add_filter( 'gform_pre_render_17', 'populate_data_operations_location' );
	add_filter( 'gform_admin_pre_render_17', 'populate_data_operations_location' );
	function populate_data_operations_location( $form ) {

		global $wpdb;
		$user_id = get_current_user_id();

		$results = $wpdb->get_results( "SELECT loc_name FROM profile_location INNER JOIN relation_user ON relation_user.loc_id=profile_location.loc_id WHERE relation_user.user_id=$user_id AND relation_user.user_role!=225 AND active=1 ORDER BY loc_name ASC" );

		foreach( $results as $rows ) :
			$choices[] = array( 'text' => $rows->loc_name, 'value' => $rows->loc_name );
		endforeach;

		foreach( $form['fields'] as &$field )
			if( $field['id'] == 85) :
				$field['placeholder'] = 'Select Location';
				$field['choices'] = $choices;
			endif; ?>

			<script type="text/javascript">
				jQuery(document).ready(function(){

					jQuery('#input_17_85').change(function(){
						var measurePOP=jQuery('#input_17_85').val();

					jQuery('#input_17_45').empty();
						jQuery.ajax({
							url:"<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php",
							type:'POST',
							data:'action=populate_data_measure&locationID=' + measurePOP,

							success:function(results) {
								jQuery('#input_17_45').append(results);
								}
							});
					});

					jQuery('#input_17_45').change(function(){
						var datePOP=jQuery('#input_17_45').val();

					jQuery('#input_17_54').empty();
						jQuery.ajax({
							url:"<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php",
							type:'POST',
							data:'action=populate_data_measure_date&measure_nameID=' + datePOP,

							success:function(results) {
								jQuery('#input_17_54').append(results);
							}
						});
					});
				});
			</script> <?php

		return $form;
	}

	populate_data_measure_name();
	populate_data_measure_date(); 

else :

	add_filter( 'gform_pre_render_130', 'populate_data_operations_measure' );
	function populate_data_operations_measure( $form ) {

		global $wpdb;
		$master_loc = $_SESSION['master_loc'];

		$dropdowns = $wpdb->get_results( "SELECT measure_name, tag FROM custom_tag INNER JOIN data_measure ON custom_tag.id=data_measure.measure_name WHERE custom_tag.loc_id=$master_loc AND data_measure.loc_id=$master_loc GROUP BY tag ORDER BY tag ASC" );
		
		// $dropdowns = $wpdb->get_results( "SELECT parent_id, tag FROM custom_tag WHERE loc_id=$master_loc AND cat_id=20 AND active=1 AND id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id) ORDER BY tag ASC" );

		foreach( $dropdowns as $dropdown ) :
			$choices[] = array( 'text' => $dropdown->tag, 'value' => $dropdown->measure_name );
		endforeach;

		foreach( $form['fields'] as &$field )
			if( $field['id'] == 45) :
				$field['placeholder'] = 'Select Measure Name';
				$field['choices'] = $choices;
			endif; ?>

			<script type="text/javascript">
				jQuery(document).ready(function(){

					jQuery('#input_130_45').change(function(){
						var datePOP = jQuery('#input_130_45').val();

					jQuery('#input_130_54').empty();
						jQuery.ajax({
							url:"<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php",
							type:'POST',
							data:'action=populate_data_measure_date&measure_nameID=' + datePOP,

							success:function(results) {
								jQuery('#input_130_54').append(results);
							}
						});
					});
				});
			</script> <?php

		return $form;
	}

	populate_data_measure_date();

endif; */


// DATA: OPERATIONS - WASTE DISPOSAL
function operations_disposal_edit() {

 if( isset( $_GET['utilityID'] ) ) :

		$utility_id = $_GET['utilityID'];
		$master_loc = $_SESSION['master_loc'];

		global $wpdb;
	
		$disposal_dropdowns = $wpdb->get_results( "SELECT relation_tag.child_id, child_tag.tag AS child FROM master_tag INNER JOIN relation_tag ON master_tag.id=relation_tag.parent_id INNER JOIN master_tag child_tag ON child_tag.id=relation_tag.child_id INNER JOIN custom_tag ON master_tag.id=custom_tag.tag_id WHERE custom_tag.parent_id=$utility_id AND relation='waste-disposal' AND custom_tag.active=1 AND custom_tag.loc_id=$master_loc GROUP BY child" );
		
		$option = '<option value selected disabled>Select Disposal Method *</option>';

		foreach( $disposal_dropdowns as $disposal_dropdown ) :
			$option .= '<option value="'.$disposal_dropdown->child_id.'">'.$disposal_dropdown->child.'</option>';
		endforeach;
	
		echo $option;
	
		die();
	endif;
}
add_action( 'wp_ajax_nopriv_operations_disposal_edit', 'operations_disposal_edit' );
add_action( 'wp_ajax_operations_disposal_edit', 'operations_disposal_edit' );


add_filter( 'gform_column_input_130_92_3', 'populate_data_disposal', 10, 5 );
function populate_data_disposal( $input_info, $field, $column, $value, $form_id ) {
	
	global $wpdb;
	$master_loc = $_SESSION['master_loc'];
	
	$dropdowns = $wpdb->get_results( "SELECT master_tag.id, master_tag.tag FROM master_tag INNER JOIN custom_tag ON master_tag.id=custom_tag.tag_id WHERE custom_tag.cat_id=16 AND active=1 AND custom_tag.id IN (SELECT MAX(custom_tag.id) FROM custom_tag GROUP BY parent_id) AND loc_id=$master_loc ORDER BY master_tag.tag ASC" );
	
	$choices[] = array( 'text' => 'Select Disposal Method', 'value' => '' );

	foreach( $dropdowns as $dropdown ) :
		$choices[] = array( 'text' => $dropdown->tag, 'value' => $dropdown->id );
	endforeach;
	
    return array( 'type' => 'select', 'choices' => $choices );
}


// DATA: OPERATIONS - PLASTIC
add_filter( 'gform_column_input_130_93_1', 'populate_data_plastic', 10, 5 );
function populate_data_plastic( $input_info, $field, $column, $value, $form_id ) {
	
	global $wpdb;
	$master_loc = $_SESSION['master_loc'];
	
	$dropdowns = $wpdb->get_results( "SELECT custom_tag.tag_id, system_tag.tag AS system_tag, custom_tag.tag AS custom_tag, size, unit_tag.tag AS unit_tag, parent_id FROM custom_tag INNER JOIN master_tag system_tag ON system_tag.id=custom_tag.tag_id INNER JOIN master_tag unit_tag ON unit_tag.id=custom_tag.unit_id WHERE custom_tag.cat_id=40 AND loc_id=$master_loc AND active=1 AND custom_tag.id IN (SELECT MAX(custom_tag.id) FROM custom_tag GROUP BY parent_id)" );
	
	$choices[] = array( 'text' => 'Select Plastic', 'value' => '' );

	foreach( $dropdowns as $dropdown ) :
	
		$system_tag = $dropdown->system_tag;
		$custom_tag = $dropdown->custom_tag;
		$size = $dropdown->size;
		$unit_tag = $dropdown->unit_tag;

		if( !empty( $custom_tag ) && empty( $size ) ) : 
			$plastic_dropdown = $system_tag.' - '.$custom_tag;
		elseif( empty( $custom_tag ) && !empty( $size ) ) :
			$plastic_dropdown = $system_tag.' ('.$size.' '.$unit_tag.')';
		elseif( !empty( $custom_tag ) && !empty( $size ) ) :
			$plastic_dropdown = $system_tag.' - '.$custom_tag.' ('.$size.' '.$unit_tag.')';
		else : 
			$plastic_dropdown = $system_tag;
		endif;
	
		$choices[] = array( 'text' => $plastic_dropdown, 'value' => $dropdown->parent_id );
	
	endforeach;
	
    return array( 'type' => 'select', 'choices' => $choices );
}


// DATA: OPERATIONS - TAGS
add_filter( 'gform_pre_render_130', 'populate_data_operation_tags' );
function populate_data_operation_tags( $form ) {
	
	global $wpdb;
	$master_loc = $_SESSION['master_loc'];

	$dropdowns = $wpdb->get_results( "SELECT tag, parent_id FROM custom_tag WHERE cat_id=22 AND active=1 AND loc_id=$master_loc AND id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id) ORDER BY tag ASC" );

	foreach( $dropdowns as $dropdown ) :
		$choices[] = array( 'text' => $dropdown->tag, 'value' => $dropdown->parent_id );
	endforeach;

	foreach( $form['fields'] as &$field ) :
		if( $field['id'] == 94 ) :
			$field['choices'] = $choices;
		endif;
	endforeach;

	return $form;
}


/* DATA: SUPPLY CHAIN - MEASURE
$loc_number = $_SESSION['loc_number'];

if( $loc_number > 1 ) :

	add_filter( 'gform_pre_render_24', 'populate_data_supply_location' );
	add_filter( 'gform_admin_pre_render_24', 'populate_data_supply_location' );
	function populate_data_supply_location( $form ) {

		global $wpdb;
		$user_id = get_current_user_id();

		$results = $wpdb->get_results( "SELECT loc_name FROM profile_location INNER JOIN relation_user ON relation_user.loc_id=profile_location.loc_id WHERE relation_user.user_id=$user_id AND relation_user.user_role!=225 AND active=1 ORDER BY loc_name ASC" );

		foreach( $results as $rows ) :
			$choices[] = array( 'text' => $rows->loc_name, 'value' => $rows->loc_name );
		endforeach;

		foreach( $form['fields'] as &$field )
			if( $field['id'] == 50) :
				$field['placeholder'] = 'Select Location';
				$field['choices'] = $choices;
			endif; ?>

			<script type="text/javascript">
				jQuery(document).ready(function(){

					jQuery('#input_24_50').change(function(){
						var measurePOP=jQuery('#input_24_50').val();

					jQuery('#input_24_24').empty();
						jQuery.ajax({
							url:"<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php",
							type:'POST',
							data:'action=populate_data_supply_measure&locationID=' + measurePOP,

							success:function(results) {
								jQuery('#input_24_24').append(results);
							}
						});
					});

					jQuery('#input_24_24').change(function(){
						var datePOP=jQuery('#input_24_24').val();

					jQuery('#input_24_25').empty();
						jQuery.ajax({
							url:"<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php",
							type:'POST',
							data:'action=populate_data_supply_measure_date&measure_nameID=' + datePOP,

							success:function(results) {
								jQuery('#input_24_25').append(results);
							}
						});
					});
				});
			</script> <?php

		return $form;
	}

	

	populate_data_measure_name();
	populate_data_measure_date();

else :

	add_filter( 'gform_pre_render_24', 'populate_data_supply_measure' );
	function populate_data_supply_measure( $form ) {

		global $wpdb;
		$master_loc = $_SESSION['master_loc'];

		$results = $wpdb->get_results( "SELECT measure_name, tag FROM custom_tag INNER JOIN data_measure ON custom_tag.id=data_measure.measure_name WHERE custom_tag.loc_id=$master_loc AND data_measure.loc_id=$master_loc GROUP BY tag ORDER BY tag ASC" );

		foreach( $results as $rows ) :
			$choices[] = array( 'text' => $rows->tag, 'value' => $rows->measure_name );
		endforeach;

		foreach( $form['fields'] as &$field )
			if( $field['id'] == 24) :
				$field['placeholder'] = 'Select Measure Name';
				$field['choices'] = $choices;
			endif; ?>

			<script type="text/javascript">
				jQuery(document).ready(function(){

					jQuery('#input_24_24').change(function(){
						var datePOP = jQuery('#input_24_24').val();

					jQuery("#input_24_25").empty();
						jQuery.ajax({
							url:"<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php",
							type:'POST',
							data:'action=populate_data_supply_measure_date&measure_nameID=' + datePOP,

							success:function(results) {
								jQuery("#input_24_25").append(results);
							}
						});
					});
				});
			</script> <?php

		return $form;
	}

	populate_data_measure_date();

endif; */


/* DATA: SUPPLY CHAIN - SOURCE LOCATION
add_filter( 'gform_column_input_24_53_1', 'populate_data_supply_source', 10, 5 );
function populate_data_supply_source( $input_info, $field, $column, $value, $form_id ) {

	global $wpdb;
	$master_loc = $_SESSION['master_loc'];
	
	$dropdowns = $wpdb->get_results( "SELECT parent_id, location FROM custom_location WHERE loc_id=$master_loc AND active=1 AND id IN (SELECT MAX(id) FROM custom_location GROUP BY parent_id) ORDER BY location DESC" );

	$choices[] = array( 'text' => 'Select Supply Source', 'value' => '' );

	foreach( $dropdowns as $dropdown ) :
		$choices[] = array( 'text' => $dropdown->location, 'value' => $dropdown->parent_id );
	endforeach;
	
	$choices[] = array( 'text' => 'Unknown Location', 'value' => 0 );

	return array( 'type' => 'select', 'choices' => $choices );

	return $form;
} */

/* DATA: SUPPLY CHAIN - TAGS
add_filter( 'gform_pre_render_24', 'populate_data_supply_tags' );
function populate_data_supply_tags( $form ) {
	
	global $wpdb;
	$master_loc = $_SESSION['master_loc'];

	$dropdowns = $wpdb->get_results( "SELECT tag, parent_id FROM custom_tag WHERE cat_id=22 AND active=1 AND loc_id=$master_loc AND id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id) ORDER BY tag ASC" );

	foreach( $dropdowns as $dropdown ) :
		$choices[] = array( 'text' => $dropdown->tag, 'value' => $dropdown->parent_id );
	endforeach;

	foreach( $form['fields'] as &$field ) :
		if( $field['id'] == 58 ) :
			$field['choices'] = $choices;
		endif;
	endforeach;

	return $form;
} */


/* DATA: CHARITY - MEASURE
$loc_number = $_SESSION['loc_number'];

if( $loc_number > 1 ) :

	add_filter( 'gform_pre_render_5', 'populate_data_charity_location' );
	add_filter( 'gform_admin_pre_render_5', 'populate_data_charity_location' );
	function populate_data_charity_location( $form ) {

		global $wpdb;
		$user_id = get_current_user_id();

		$results = $wpdb->get_results( "SELECT loc_name FROM profile_location INNER JOIN relation_user ON relation_user.loc_id=profile_location.loc_id WHERE relation_user.user_id=$user_id AND relation_user.user_role!=225 AND active=1 ORDER BY loc_name ASC" );

		foreach( $results as $rows ) :
			$choices[] = array( 'text' => $rows->loc_name, 'value' => $rows->loc_name );
		endforeach;

		foreach( $form['fields'] as &$field )
			if( $field['id'] == 57) :
				$field['placeholder'] = 'Select Location';
				$field['choices'] = $choices;
			endif; ?>

			<script type="text/javascript">
				jQuery(document).ready(function(){

					jQuery('#input_5_57').change(function(){
						var measurePOP=jQuery('#input_5_57').val();

					jQuery('#input_5_29').empty();
						jQuery.ajax({
							url:"<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php",
							type:'POST',
							data:'action=populate_data_charity_measure&locationID=' + measurePOP,

							success:function(results) {
								jQuery('#input_5_29').append(results);
							}
						});
					});

					jQuery('#input_5_29').change(function(){
						var datePOP=jQuery('#input_5_29').val();

					jQuery('#input_5_28').empty();
						jQuery.ajax({
							url:"<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php",
							type:'POST',
							data:'action=populate_data_charity_measure_date&measure_nameID=' + datePOP,

							success:function(results) {
								jQuery('#input_5_28').append(results);
							}
						});
					});
				});
			</script> <?php

		return $form;
	}

	populate_data_measure_name();
	populate_data_measure_date();

else :

	add_filter( 'gform_pre_render_5', 'populate_data_charity_measure' );
	function populate_data_charity_measure( $form ) {

		global $wpdb;
		$master_loc = $_SESSION['master_loc'];

		$results = $wpdb->get_results( "SELECT measure_name, tag FROM custom_tag INNER JOIN data_measure ON custom_tag.id=data_measure.measure_name WHERE data_measure.loc_id=$master_loc GROUP BY tag ORDER BY tag ASC" );

		foreach( $results as $rows ) :
			$choices[] = array( 'text' => $rows->tag, 'value' => $rows->measure_name );
		endforeach;

		foreach( $form['fields'] as &$field )
			if( $field['id'] == 29) :
				$field['placeholder'] = 'Select Measure Name';
				$field['choices'] = $choices;
			endif; ?>

			<script type="text/javascript">
				jQuery(document).ready(function(){

					jQuery('#input_5_29').change(function(){
						var datePOP = jQuery('#input_5_29').val();

					jQuery("#input_5_28").empty();
						jQuery.ajax({
							url:"<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php",
							type:'POST',
							data:'action=populate_data_charity_measure_date&measure_nameID=' + datePOP,

							success:function(results) {
								jQuery("#input_5_28").append(results);
							}
						});
					});
				});
			</script> <?php

		return $form;
	}

	populate_data_measure_date();

endif; */

/* DATA: CHARITY - DONEE LOCATION
add_filter( 'gform_column_input_5_62_1', 'populate_data_charity_donee_location', 10, 5 );
function populate_data_charity_donee_location( $input_info, $field, $column, $value, $form_id ) {

	global $wpdb;
	$master_loc = $_SESSION['master_loc'];
	
	$dropdowns = $wpdb->get_results( "SELECT parent_id, location FROM custom_location WHERE loc_id=$master_loc AND active=1 AND id IN (SELECT MAX(id) FROM custom_location GROUP BY parent_id) ORDER BY location DESC" );

	$choices[] = array( 'text' => 'Select Donee Location', 'value' => '' );

	foreach( $dropdowns as $dropdown ) :
		$choices[] = array( 'text' => $dropdown->location, 'value' => $dropdown->parent_id );
	endforeach;
	
	$choices[] = array( 'text' => 'Unknown Location', 'value' => 0 );

	return array( 'type' => 'select', 'choices' => $choices );

	return $form;
} */

/* DATA: CHARITY - DONEE LOCATION
add_filter( 'gform_column_input_5_63_1', 'populate_data_charity_donee_location_staff', 10, 5 );
function populate_data_charity_donee_location_staff( $input_info, $field, $column, $value, $form_id ) {

	global $wpdb;
	$master_loc = $_SESSION['master_loc'];
	
	$dropdowns = $wpdb->get_results( "SELECT parent_id, location FROM custom_location WHERE loc_id=$master_loc AND active=1 AND id IN (SELECT MAX(id) FROM custom_location GROUP BY parent_id) ORDER BY location DESC" );

	$choices[] = array( 'text' => 'Select Donee Location', 'value' => '' );

	foreach( $dropdowns as $dropdown ) :
		$choices[] = array( 'text' => $dropdown->location, 'value' => $dropdown->parent_id );
	endforeach;
	
	$choices[] = array( 'text' => 'Unknown Location', 'value' => 0 );

	return array( 'type' => 'select', 'choices' => $choices );

	return $form;
} */

/* DATA: CHARITY - VALUE TYPE
add_filter( 'gform_column_input_5_62_2', 'populate_data_charity_value_type', 10, 5 );
function populate_data_charity_value_type( $input_info, $field, $column, $value, $form_id ) {

	global $wpdb;
	
	$dropdowns = $wpdb->get_results( "SELECT id, tag FROM master_tag WHERE cat_id=5 ORDER BY tag ASC" );

	$choices[] = array( 'text' => 'Select Value Type', 'value' => '' );

	foreach( $dropdowns as $dropdown ) :
		$choices[] = array( 'text' => $dropdown->tag, 'value' => $dropdown->id );
	endforeach;
	
    return array( 'type' => 'select', 'choices' => $choices );
} */


/* DATA: CHARITY - TAGS
add_filter( 'gform_pre_render_5', 'populate_data_charity_tags' );
function populate_data_charity_tags( $form ) {
	
	global $wpdb;
	$master_loc = $_SESSION['master_loc'];

	$dropdowns = $wpdb->get_results( "SELECT tag, parent_id FROM custom_tag WHERE cat_id=22 AND active=1 AND loc_id=$master_loc AND id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id) ORDER BY tag ASC" );

	foreach( $dropdowns as $dropdown ) :
		$choices[] = array( 'text' => $dropdown->tag, 'value' => $dropdown->parent_id );
	endforeach;

	foreach( $form['fields'] as &$field ) :
		if( $field['id'] == 68 ) :
			$field['choices'] = $choices;
		endif;
	endforeach;

	return $form;
} */


// CHART: CHART TYPE AND FREQUENCY - THIS HAS MOVED TO BOOTSTRAP SO NEED TO CHANGE CODE
/* add_filter( 'gform_pre_render_128', 'populate_chart_selector' );
add_filter( 'gform_admin_pre_render_128', 'populate_chart_selector' );
function populate_chart_selector( $form ) {
	
	global $wpdb;
	$mod_id = $_GET['module'];
	$geo_type = $_SESSION['geo_type'];
	
	$results = $wpdb->get_results( "SELECT page_id, title FROM master_chart WHERE mod_id=$mod_id AND (geo_type=$geo_type OR geo_type IS NULL) ORDER BY sequence" );

	foreach( $results as $rows ) :
		$choices[] = array( 'text' => $rows->title, 'value' => $rows->page_id );
	endforeach;

	foreach( $form['fields'] as &$field )
		if( $field['id'] == 1) :
			$field['placeholder'] = 'Select Chart Type';
			$field['choices'] = $choices;
		endif; ?>

		<script type="text/javascript">
			jQuery(document).ready(function(){

				jQuery('#input_128_1').change(function(){
					var frequency_idPOP=jQuery('#input_128_1').val();

				jQuery('#input_128_14').empty();
					jQuery.ajax({
						url:"<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php",
						type:'POST',
						data:'action=populate_chart_selector_frequency_id&pageID=' + frequency_idPOP,

						success:function(results) {
							jQuery('#input_128_14').append(results);
						}
					});
				});

				jQuery('#input_128_14').change(function(){
					var frequencyPOP=jQuery('#input_128_14').val();

				jQuery('#input_128_13').empty();
					jQuery.ajax({
						url:"<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php",
						type:'POST',
						data:'action=populate_chart_selector_frequency_value&frequencyID=' + frequencyPOP,

						success:function(results) {
							jQuery('#input_128_13').append(results);
						}
					});
				});
			});
		</script> <?php

	return $form;
}

function populate_chart_selector_frequency_id() {

	if( isset( $_POST['pageID'] ) ) :

		global $wpdb;
		$page_id = $_POST['pageID'];
		$measure_toggle = $_SESSION['measure_toggle'];
	
		if( $measure_toggle == 86 ) : 
		
			$results = $wpdb->get_results( "SELECT chart_id, frequency FROM master_chartmeta WHERE measure BETWEEN 85 and $measure_toggle AND page_id=$page_id GROUP BY chart_id" );
	
		else : 
		
			$results = $wpdb->get_results( "SELECT chart_id, frequency FROM master_chartmeta WHERE measure BETWEEN $measure_toggle AND 85 AND page_id=$page_id GROUP BY chart_id" );
	
		endif;
	
		foreach( $results as $rows ) :
			$option .= '<option value="'.$rows->chart_id.'">';
			$option .= $rows->frequency;
			$option .= '</option>';
		endforeach;

		echo '<option value="0" disabled selected>Select Frequency</option>'.$option;
	
		die();
	endif;
}

add_action( 'wp_ajax_nopriv_populate_chart_selector_frequency_id', 'populate_chart_selector_frequency_id' );
add_action( 'wp_ajax_populate_chart_selector_frequency_id', 'populate_chart_selector_frequency_id' );

function populate_chart_selector_frequency_value() {

	if( isset( $_POST['frequencyID'] ) ) :

		global $wpdb;
		$frequency_id = $_POST['frequencyID'];
	
		$results = $wpdb->get_results( "SELECT frequency FROM master_chartmeta WHERE chart_id=$frequency_id GROUP BY frequency" );
	
		foreach( $results as $rows ) :
			$option .= '<option value="'.$rows->frequency.'">';
			$option .= $rows->frequency;
			$option .= '</option>';
		endforeach;
	
		echo $option;
	
		die();
	endif;
}

add_action( 'wp_ajax_nopriv_populate_chart_selector_frequency_value', 'populate_chart_selector_frequency_value' );
add_action( 'wp_ajax_populate_chart_selector_frequency_value', 'populate_chart_selector_frequency_value' );

// CHART: FREQUENCY - ADMIN ONLY
add_filter( 'gform_admin_pre_render_128', 'populate_chart_selector_frequency_admin' );
function populate_chart_selector_frequency_admin( $form ) {

	global $wpdb;

	$results = $wpdb->get_results( "SELECT frequency FROM master_chartmeta GROUP BY frequency" );

	foreach( $results as $rows ) :
		$choices[] = array( 'text' => $rows->frequency, 'value' => $rows->frequency );
	endforeach;

	foreach( $form['fields'] as &$field ) :
		if( $field['id'] == 13 ) :
			$field['choices'] = $choices;
		endif;
	endforeach;

	return $form;
	
}

// CHART: LOCATION
add_filter( 'gform_pre_render_128', 'populate_chart_selector_location' );
function populate_chart_selector_location( $form ) {

	global $wpdb;
	
	$user_id = get_current_user_id();

	$results = $wpdb->get_results( "SELECT loc_name FROM profile_location INNER JOIN relation_user ON relation_user.loc_id=profile_location.loc_id WHERE relation_user.user_id=$user_id AND relation_user.user_role!=225 AND active=1 ORDER BY loc_name ASC" );
	
	$choices[] = array( 'text' => 'All', 'value' => '' );

	foreach( $results as $rows ) :
		$choices[] = array( 'text' => $rows->loc_name, 'value' => $rows->loc_name );
	endforeach;

	foreach( $form['fields'] as &$field ) :
		if( $field['id'] == 4 ) :
			$field['placeholder'] = 'Select Location';
			$field['choices'] = $choices;
		endif;
	endforeach;

	return $form;
	
} */
