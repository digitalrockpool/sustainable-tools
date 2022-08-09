<?php

/* Includes: CSV UPLOADS

@package	Yardstick
@author		Digital Rockpool
@link		https://yardstick.co.uk
@copyright	Copyright (c) 2018, Digital Rockpool LTD
@license	GPL-2.0+ */

function clean_values( $values ) {
	return filter_var( $values, FILTER_SANITIZE_STRING );
}

function csv_upload( $cell, $mod_query, $utility_type, $employee_type, $donation_type, $loc_name ) {
	
	$user_id = get_current_user_id();
	
	$loc_number = $_SESSION['loc_number'];
	$master_loc = $_SESSION['master_loc'];
	$calendar = $_SESSION['calendar'];
	$measure_toggle = $_SESSION['measure_toggle'];
	$tag_toggle = $_SESSION['tag_toggle'];
	
	global $wpdb;
	
	if( $loc_number > 1 ) :
	
		$loc_names = $wpdb->get_row( "SELECT profile_location.loc_id FROM profile_location INNER JOIN relation_user ON relation_user.loc_id=profile_location.loc_id WHERE relation_user.user_id=$user_id AND profile_location.loc_name='$loc_name'" );
		$loc_id = $loc_names->loc_id;
	
	else :
	
		$loc_id = $master_loc;
	
	endif;
	
	if( $mod_query != 1 && $measure_toggle == 86 && $employee_type != 69 && $employee_type != 70 && $employee_type != 71 && $employee_type != 228 ) :
	
	echo $mod_query;
	
		$measure_name_value = clean_values( $cell[0] );
		$measure_start = date_format( date_create( $cell[1] ), 'Y-m-d' );
		$measure_end = date_format( date_create( $cell[2] ), 'Y-m-d' );
	
		$measure_names = $wpdb->get_row( "SELECT id FROM custom_tag WHERE tag='$measure_name_value' AND cat_id=32 AND loc_id=$master_loc" );
	
		$measures = $wpdb->get_row( "SELECT measure_name, measure_start, measure_end FROM data_measure INNER JOIN custom_tag ON data_measure.measure_name=custom_tag.id WHERE measure_name='$measure_name_value' AND measure_start='$measure_start' AND measure_end='$measure_end' AND data_measure.loc_id=$loc_id" );
	
	
		if( empty( $measure_names ) ) :
	
			$wpdb->insert( 'custom_tag',
				array(
					'tag' => $measure_name_value,
					'tag_id' => NULL,
					'unit_id' => NULL,
					'cat_id' => 32,
					'active' => 1,
					'loc_id' => $master_loc
				)
			);

			$measure_name = $wpdb->insert_id;
			$wpdb->insert( 'data_measure',
				array(
					'measure_type' => 86,
					'measure_name' => $measure_name,
					'measure_start' => $measure_start,
					'measure_end' => $measure_end,
					'bednight' => NULL,
					'roomnight' => NULL,
					'client' => NULL,
					'staff' => NULL,
					'area' => NULL,
					'note' => NULL,
					'loc_id' => $master_loc
				)
			);
	
			$measure = $wpdb->insert_id;
		
		elseif( !empty( $measure_names ) && empty( $measures ) ) :
	
			$measure_name = $measure_names->id;
			$wpdb->insert( 'data_measure',
				array(
					'measure_type' => 86,
					'measure_name' => $measure_name,
					'measure_start' => $measure_start,
					'measure_end' => $measure_end,
					'bednight' => NULL,
					'roomnight' => NULL,
					'client' => NULL,
					'staff' => NULL,
					'area' => NULL,
					'note' => NULL,
					'loc_id' => $master_loc
				)
			);
	
			$measure = $wpdb->insert_id;
				
		else :		
	
			$measure = $measures->id;
	
		endif;
	
	elseif ( $mod_query != 1) :
	
		$measure = NULL;
		$measure_date = date_format( date_create( $cell[0] ), 'Y-m-d' );
	
	endif;
	
	if( $mod_query == 1 ) :
	
		if( $measure_toggle == 86 || ( $measure_toggle == 84 && $calendar == 231 ) ) :
			
			$measure_name_value = clean_values( $cell[0] );
			$measure_start_value = $cell[1];
			$measure_end_value = $cell[2];
			$bednight_value = (int)$cell[3];
			$roomnight_value = (int)$cell[4];
			$client_value = (int)$cell[5];
			$staff_value = (int)$cell[6];
			$area_value = (int)$cell[7];
			$note_value = clean_values( $cell[8] );

		else : 
	
			$measure_start_value = $cell[0];
			$bednight_value = (int)$cell[1];
			$roomnight_value = (int)$cell[2];
			$client_value = (int)$cell[3];
			$staff_value = (int)$cell[4];
			$area_value = (int)$cell[5];
			$note_value = clean_values( $cell[6] );

		endif;
	
		if( $measure_toggle == 84 && $calendar == 231 ) :

			$measure_names = $wpdb->get_row( "SELECT id FROM master_tag WHERE tag='$measure_name_value'" );
	
			if( empty( $measure_names ) ) : $measure_name = -1; else : $measure_name = $measure_names->id; endif;
	
		elseif( $measure_toggle == 86 ) :
	
			$measure_names = $wpdb->get_row( "SELECT id FROM custom_tag WHERE tag='$measure_name_value' AND cat_id=32 AND loc_id=$master_loc" );

			if( empty( $measure_names ) ) :

				$wpdb->insert( 'custom_tag',
					array(
						'tag' => $measure_name_value,
						'tag_id' => NULL,
						'unit_id' => NULL,
						'cat_id' => 32,
						'active' => 1,
						'loc_id' => $master_loc
					)
				);
				$measure_name = $wpdb->insert_id;
	
			else :
	
				$measure_name = $measure_names->id;
	
			endif;
		
		else :
	
			$measure_name = NULL;
	
		endif;
	
		if( $measure_toggle == 84 && $calendar == 237 ) : 
	
			$measure_start_create = date_create( $measure_start_value );
			$measure_start = date_format( $measure_start_create, 'Y-m-1' );
			$measure_end = NULL;
	
		elseif( $measure_toggle == 83 ) :
	
			$measure_start_create = date_create( $measure_start_value );
	
			$week = date_format( $measure_start_create, 'W' );;
			$year = date_format( $measure_start_create, 'Y' );;

			$timestamp = mktime( 0, 0, 0, 1, 1,  $year ) + ( $week * 7 * 24 * 60 * 60 );
			$week_start = $timestamp - 86400 * ( date( 'N', $timestamp ) - 1 );
			$measure_start = date( 'Y-m-d', $week_start );
	
			$measure_end = NULL;
	
		elseif( $measure_toggle == 82 ) :
	
			$measure_start_create = date_create( $measure_start_value );
			$measure_start = date_format( $measure_start_create, 'Y-m-d' );
			$measure_end = NULL;
	
		else :

			$measure_start_create = date_create( $measure_start_value );
			$measure_start = date_format( $measure_start_create, 'Y-m-d' );
			$measure_end_create = date_create( $measure_end_value );
			$measure_end = date_format( $measure_end_create, 'Y-m-d' );
	
		endif;

		if( empty( $bednight_value ) ) : $bednight = NULL; else : $bednight = $bednight_value; endif;
		if( empty( $roomnight_value ) ) : $roomnight = NULL; else : $roomnight = $roomnight_value; endif;
		if( empty( $client_value ) ) : $client = NULL; else : $client = $client_value; endif;
		if( empty( $staff_value ) ) : $staff = NULL; else : $staff = $staff_value; endif;
		if( empty( $area_value ) ) : $area = NULL; else : $area = $area_value; endif;
		if( empty( $note_value ) ) : $note = NULL; else : $note = $note_value; endif;
	
		$wpdb->insert(
			'data_measure',
			array(
				'measure_type' => $measure_toggle,
				'measure_name' => $measure_name,
				'measure_start' => $measure_start,
				'measure_end' => $measure_end,
				'bednight' => $bednight,
				'roomnight' => $roomnight,
				'client' => $client,
				'staff' => $staff,
				'area' => $area,
				'note' => $note,
				'loc_id' => $loc_id
			)
		);
	
	elseif( $mod_query == 2 ) :
	
		if( $measure_toggle == 86 && $tag_toggle == 1 && $utility_type != 89 ) :

			$utility_value = clean_values( $cell[3] );
			$amount = (float)$cell[4];
			$cost_value = (float)$cell[5];
			$tag1_value = clean_values( $cell[6] );
			$tag2_value = clean_values( $cell[7] );
			$tag3_value = clean_values( $cell[8] );
			$tag4_value = clean_values( $cell[9] );
			$note_value = clean_values( $cell[10] );

		elseif( $measure_toggle == 86 && $tag_toggle == 0 && $utility_type != 89 ) :

			$utility_value = clean_values( $cell[3] );
			$amount = (float)$cell[4];
			$cost_value = (float)$cell[5];
			$note_value = clean_values( $cell[6] );

		elseif( $measure_toggle != 86 && $tag_toggle == 1 && $utility_type != 89 ) :

			$utility_value = clean_values( $cell[1] );
			$amount = (float)$cell[2];
			$cost_value = (float)$cell[3];
			$tag1_value = clean_values( $cell[4] );
			$tag2_value = clean_values( $cell[5] );
			$tag3_value = clean_values( $cell[6] );
			$tag4_value = clean_values( $cell[7] );
			$note_value = clean_values( $cell[8] );

		elseif( $measure_toggle != 86 && $tag_toggle == 0 && $utility_type != 89 ) :

			$utility_value = clean_values( $cell[1] );
			$amount = (float)$cell[2];
			$cost_value = (float)$cell[3];
			$note_value = clean_values( $cell[4] );

		elseif( $measure_toggle == 86 && $tag_toggle == 1 && $utility_type == 89 ) :

			$utility_value = clean_values( $cell[3] );
			$disposal_value = clean_values( $cell[4] );
			$amount = (float)$cell[5];
			$cost_value = (float)$cell[6];
			$tag1_value = clean_values( $cell[7] );
			$tag2_value = clean_values( $cell[8] );
			$tag3_value = clean_values( $cell[9] );
			$tag4_value = clean_values( $cell[10] );
			$note_value = clean_values( $cell[11] );

		elseif( $measure_toggle == 86 && $tag_toggle == 0 && $utility_type == 89 ) :

			$utility_value = clean_values( $cell[3] );
			$disposal_value = clean_values( $cell[4] );
			$amount = (float)$cell[5];
			$cost_value = (float)$cell[6];
			$note_value = clean_values( $cell[7] );

		elseif( $measure_toggle != 86 && $tag_toggle == 1 && $utility_type == 89 ) :

			$utility_value = clean_values( $cell[1] );
			$disposal_value = clean_values( $cell[2] );
			$amount = (float)$cell[3];
			$cost_value = (float)$cell[4];
			$tag1_value = clean_values( $cell[5] );
			$tag2_value = clean_values( $cell[6] );
			$tag3_value = clean_values( $cell[7] );
			$tag4_value = clean_values( $cell[8] );
			$note_value = clean_values( $cell[9] );

		elseif( $measure_toggle != 86 && $tag_toggle == 0 && $utility_type == 89 ) :

			$utility_value = clean_values( $cell[1] );
			$disposal_value = clean_values( $cell[2] );
			$amount = (float)$cell[3];
			$cost_value = (float)$cell[4];
			$note_value = clean_values( $cell[5] );

		endif;

		$master_utility = $wpdb->get_row( "SELECT id, cat_id FROM master_tag WHERE tag='$utility_value' AND (cat_id=15 OR cat_id=18 OR cat_id=19)" );
		$utility_id = $wpdb->get_row( "SELECT master_tag.id FROM master_tag INNER JOIN custom_tag ON master_tag.id=custom_tag.tag_id WHERE master_tag.tag='$utility_value' AND loc_id=$master_loc AND (master_tag.cat_id=15 OR master_tag.cat_id=18 OR master_tag.cat_id=19)" );
	
		$master_utility_id = $master_utility->id;

		$unit_id = $wpdb->get_row( "SELECT master_tag.id FROM master_tag INNER JOIN relation_tag ON master_tag.id=relation_tag.child_id WHERE tag='$unit_value' AND cat_id=17 AND parent_id=$master_utility_id" );
	
		if( empty( $unit_id ) ) : $unit = -1; else : $unit = $unit_id->id; endif;
	
		if( empty( $master_utility ) ) : 

			$utility = -1;

		elseif( !empty( $master_utility ) && empty( $utility_id ) ) :
	
			if( $unit_id == -1 ) : $active = -1; else : $active = 1; endif;
	
			$utility = $master_utility_id;
			$cat_id = $master_utility->cat_id;

			$wpdb->insert( 'custom_tag',
				array(
					'tag' => NULL,
					'tag_id' => $master_utility_id,
					'unit_id' => $unit,
					'cat_id' => $cat_id,
					'active' => $active,
					'loc_id' => $master_loc
				)
			);

		 else :
	
			$utility = $master_utility_id;
	
		endif;

		$master_disposal_id = $wpdb->get_row( "SELECT id FROM master_tag WHERE tag='$disposal_value' AND cat_id=16" );
		$disposal_id = $wpdb->get_row( "SELECT master_tag.id FROM master_tag INNER JOIN custom_tag ON master_tag.id=custom_tag.tag_id WHERE master_tag.tag='$disposal_value' AND loc_id=$master_loc AND master_tag.cat_id=16" );
		
		if( empty( $master_disposal_id ) && $utility_type == 89 ) :

			$disposal = -1;

		elseif( !empty( $master_disposal_id ) && empty( $disposal_id ) && $utility_type == 89 ) :

			$disposal = $master_disposal_id->id;

			$wpdb->insert( 'custom_tag',
				array(
					'tag' => NULL,
					'tag_id' => $disposal,
					'unit_id' => NULL,
					'cat_id' => 16,
					'active' => 1,
					'loc_id' => $master_loc
				)
			);
	
		elseif( !empty( $disposal_id ) && $utility_type == 89 ) :
	
			$disposal = $master_disposal_id->id;
	
		else :

			$disposal = NULL;

		endif;

		if( empty( $cost_value ) ) : $cost = NULL; else : $cost = $cost_value; endif;

		$tag1_id = $wpdb->get_row( "SELECT id FROM custom_tag WHERE tag='$tag1_value' AND cat_id=22 AND loc_id=$master_loc" );
		if( empty( $tag1_id ) && !empty( $tag1_value ) ) :

			$wpdb->insert( 'custom_tag',
				array(
					'tag' => $tag1_value,
					'tag_id' => NULL,
					'unit_id' => NULL,
					'cat_id' => 22,
					'active' => 1,
					'loc_id' => $master_loc
				)
			);

			$tag1 = $wpdb->insert_id;

		elseif( !empty( $tag1_id ) ) :

			$tag1 = $tag1_id->id;

		else :

			$tag1 = NULL;

		endif;

		$tag2_id = $wpdb->get_row( "SELECT id FROM custom_tag WHERE tag='$tag2_value' AND cat_id=23 AND loc_id=$master_loc" );
		if( empty( $tag2_id ) && !empty( $tag2_value ) ) :

			$wpdb->insert( 'custom_tag',
				array(
					'tag' => $tag2_value,
					'tag_id' => NULL,
					'unit_id' => NULL,
					'cat_id' => 23,
					'active' => 1,
					'loc_id' => $master_loc
				)
			);

			$tag2 = $wpdb->insert_id;

		elseif( !empty( $tag2_id ) ) :

			$tag2 = $tag2_id->id;

		else :

			$tag2 = NULL;

		endif;

		$tag3_id = $wpdb->get_row( "SELECT id FROM custom_tag WHERE tag='$tag3_value' AND cat_id=24 AND loc_id=$master_loc" );
		if( empty( $tag3_id ) && !empty( $tag3_value ) ) :

			$wpdb->insert( 'custom_tag',
				array(
					'tag' => $tag3_value,
					'tag_id' => NULL,
					'unit_id' => NULL,
					'cat_id' => 24,
					'active' => 1,
					'loc_id' => $master_loc
				)
			);

			$tag3 = $wpdb->insert_id;

		elseif( !empty( $tag3_id ) ) :

			$tag3 = $tag3_id->id;

		else :

			$tag3 = NULL;

		endif;

		$tag4_id = $wpdb->get_row( "SELECT id FROM custom_tag WHERE tag='$tag4_value' AND cat_id=25 AND loc_id=$master_loc" );
		if( empty( $tag4_id ) && !empty( $tag4_value ) ) :

			$wpdb->insert( 'custom_tag',
				array(
					'tag' => $tag4_value,
					'tag_id' => NULL,
					'unit_id' => NULL,
					'cat_id' => 25,
					'active' => 1,
					'loc_id' => $master_loc
				)
			);

			$tag4 = $wpdb->insert_id;

		elseif( !empty( $tag4_id ) ) :

			$tag4 = $tag4_id->id;

		else :

			$tag4 = NULL;

		endif;

		if( empty( $note_value ) ) : $note = NULL; else : $note = $note_value; endif;

		$wpdb->insert( 'data_operations',
			array(
				'measure' => $measure,
				'measure_date' => $measure_date,
				'utility' => $utility,
				'disposal' => $disposal,
				'amount' => $amount,
				'cost' => $cost,
				'tag1' => $tag1,
				'tag2' => $tag2,
				'tag3' => $tag3,
				'tag4' => $tag4,
				'note' => $note,
				'loc_id' => $loc_id
			)
		);
	
	elseif( $mod_query == 3 ) :
	
		if( ( $employee_type == 69 || $employee_type == 70 || $employee_type == 71 || $employee_type == 228 ) && $tag_toggle == 1 ) :

			$location_value = clean_values( $cell[1] );
			$gender_value = clean_values( $cell[2] );
			$ethnicity_value = clean_values( $cell[3] );
			$role_value = clean_values( $cell[4] );
			$disability_value = clean_values( strtolower( $cell[5] ) );
			$under16_value = clean_values( strtolower( $cell[6] ) );
			$level_value = clean_values( $cell[7] );
			$part_time_value = clean_values( strtolower( $cell[8] ) );
			$promoted_value = clean_values( strtolower( $cell[9] ) );
			$start_date_value = $cell[10];
			$leave_date_value = $cell[11];
			$contract_dpw_value = (int)$cell[12];
			$contract_wpy_value = (int)$cell[13];
			$annual_leave_value = (int)$cell[14];
			$salary = (float)$cell[15];
			$overtime_value = (float)$cell[16];
			$bonuses_value = (float)$cell[17];
			$gratuities = (float)$cell[18];
			$benefits = (float)$cell[19];
			$cost_training_value = (float)$cell[20];
			$training_days_value = (float)$cell[21];
			$tag1_value = clean_values( $cell[22] );
			$tag2_value = clean_values( $cell[23] );
			$tag3_value = clean_values( $cell[24] );
			$tag4_value = clean_values( $cell[25] );
			$note_value = clean_values( $cell[26] );
	
		elseif( ( $employee_type == 69 || $employee_type == 70 || $employee_type == 71 || $employee_type == 228 ) && $tag_toggle == 0 ) :

			$location_value = clean_values( $cell[1] );
			$gender_value = clean_values( $cell[2] );
			$ethnicity_value = clean_values( $cell[3] );
			$role_value = clean_values( $cell[4] );
			$disability_value = clean_values( strtolower( $cell[5] ) );
			$under16_value = clean_values( strtolower( $cell[6] ) );
			$level_value = clean_values( $cell[7] );
			$part_time_value = clean_values( strtolower( $cell[8] ) );
			$promoted_value = clean_values( strtolower( $cell[9] ) );
			$start_date_value = $cell[10];
			$leave_date_value = $cell[11];
			$contract_dpw_value = (int)$cell[12];
			$contract_wpy_value = (int)$cell[13];
			$annual_leave_value = (int)$cell[14];
			$salary = (float)$cell[15];
			$overtime_value = (float)$cell[16];
			$bonuses_value = (float)$cell[17];
			$gratuities = (float)$cell[18];
			$benefits = (float)$cell[19];
			$cost_training_value = (float)$cell[20];
			$training_days_value = (float)$cell[21];
			$note_value = clean_values( $cell[22] );

		elseif( $employee_type == 72 && $measure_toggle == 86 && $tag_toggle == 1 ) :

			$location_value = clean_values( $cell[3] );
			$gender_value = clean_values( $cell[4] );
			$ethnicity_value = clean_values( $cell[5] );
			$role_value = clean_values( $cell[6] );
			$disability_value = clean_values( strtolower( $cell[7] ) );
			$under16_value = clean_values( strtolower( $cell[8] ) );
			$days_worked_value = (float)$cell[9];
			$salary = (float)$cell[10];
			$gratuities = (float)$cell[11];
			$benefits = (float)$cell[12];
			$cost_training_value = (float)$cell[13];
			$training_days_value = (float)$cell[14];
			$tag1_value = clean_values( $cell[15] );
			$tag2_value = clean_values( $cell[16] );
			$tag3_value = clean_values( $cell[17] );
			$tag4_value = clean_values( $cell[18] );
			$note_value = clean_values( $cell[19] );

		elseif( $employee_type == 72 && $measure_toggle == 86 && $tag_toggle == 0 ) :

			$location_value = clean_values( $cell[3] );
			$gender_value = clean_values( $cell[4] );
			$ethnicity_value = clean_values( $cell[5] );
			$role_value = clean_values( $cell[6] );
			$disability_value = clean_values( strtolower( $cell[7] ) );
			$under16_value = clean_values( strtolower( $cell[8] ) );
			$days_worked_value = (float)$cell[9];
			$salary = (float)$cell[10];
			$gratuities = (float)$cell[11];
			$benefits = (float)$cell[12];
			$cost_training_value = (float)$cell[13];
			$training_days_value = (float)$cell[14];
			$note_value = clean_values( $cell[15] );

		elseif( $employee_type == 72 && $measure_toggle != 86 && $tag_toggle == 1 ) :

			$location_value = clean_values( $cell[1] );
			$gender_value = clean_values( $cell[2] );
			$ethnicity_value = clean_values( $cell[3] );
			$role_value = clean_values( $cell[4] );
			$disability_value = clean_values( strtolower( $cell[5] ) );
			$under16_value = clean_values( strtolower( $cell[6] ) );
			$days_worked_value = (float)$cell[7];
			$salary = (float)$cell[8];
			$gratuities = (float)$cell[9];
			$benefits = (float)$cell[10];
			$cost_training_value = (float)$cell[11];
			$training_days_value = (float)$cell[12];
			$tag1_value = clean_values( $cell[13] );
			$tag2_value = clean_values( $cell[14] );
			$tag3_value = clean_values( $cell[15] );
			$tag4_value = clean_values( $cell[16] );
			$note_value = clean_values( $cell[17] );

		elseif( $employee_type == 72 && $measure_toggle != 86 && $tag_toggle == 0 ) :

			$location_value = clean_values( $cell[1] );
			$gender_value = clean_values( $cell[2] );
			$ethnicity_value = clean_values( $cell[3] );
			$role_value = clean_values( $cell[4] );
			$disability_value = clean_values( strtolower( $cell[5] ) );
			$under16_value = clean_values( strtolower( $cell[6] ) );
			$days_worked_value = (float)$cell[7];
			$salary = (float)$cell[8];
			$gratuities = (float)$cell[9];
			$benefits = (float)$cell[10];
			$cost_training_value = (float)$cell[11];
			$training_days_value = (float)$cell[12];
			$note_value = clean_values( $cell[13] );

		elseif( $employee_type == 73 && $measure_toggle == 86 && $tag_toggle == 1 ) :

			$location_value = clean_values( $cell[3] );
			$gender_value = clean_values( $cell[4] );
			$ethnicity_value = clean_values( $cell[5] );
			$role_value = clean_values( $cell[6] );
			$disability_value = clean_values( strtolower( $cell[7] ) );
			$under16_value = clean_values( strtolower( $cell[8] ) );
			$days_worked_value = (float)$cell[9];
			$time_mentored_value = (float)$cell[10];
			$salary = (float)$cell[11];
			$gratuities = (float)$cell[12];
			$benefits = (float)$cell[13];
			$cost_training_value = (float)$cell[14];
			$training_days_value = (float)$cell[15];
			$tag1_value = clean_values( $cell[16] );
			$tag2_value = clean_values( $cell[17] );
			$tag3_value = clean_values( $cell[18] );
			$tag4_value = clean_values( $cell[19] );
			$note_value = clean_values( $cell[20] );

		elseif( $employee_type == 73 && $measure_toggle == 86 && $tag_toggle == 0 ) :

			$location_value = clean_values( $cell[3] );
			$gender_value = clean_values( $cell[4] );
			$ethnicity_value = clean_values( $cell[5] );
			$role_value = clean_values( $cell[6] );
			$disability_value = clean_values( strtolower( $cell[7] ) );
			$under16_value = clean_values( strtolower( $cell[8] ) );
			$days_worked_value = (float)$cell[9];
			$time_mentored_value = (float)$cell[10];
			$salary = (float)$cell[11];
			$gratuities = (float)$cell[12];
			$benefits = (float)$cell[13];
			$cost_training_value = (float)$cell[14];
			$training_days_value = (float)$cell[15];
			$note_value = clean_values( $cell[16] );

		elseif( $employee_type == 73 && $measure_toggle != 86 && $tag_toggle == 1 ) :

			$location_value = clean_values( $cell[1] );
			$gender_value = clean_values( $cell[2] );
			$ethnicity_value = clean_values( $cell[3] );
			$role_value = clean_values( $cell[4] );
			$disability_value = clean_values( strtolower( $cell[5] ) );
			$under16_value = clean_values( strtolower( $cell[6] ) );
			$days_worked_value = (float)$cell[7];
			$time_mentored_value = (float)$cell[8];
			$salary = (float)$cell[9];
			$gratuities = (float)$cell[10];
			$benefits = (float)$cell[11];
			$cost_training_value = (float)$cell[12];
			$training_days_value = (float)$cell[13];
			$tag1_value = clean_values( $cell[14] );
			$tag2_value = clean_values( $cell[15] );
			$tag3_value = clean_values( $cell[16] );
			$tag4_value = clean_values( $cell[17] );
			$note_value = clean_values( $cell[18] );

		elseif( $employee_type == 73 && $measure_toggle != 86 && $tag_toggle == 0 ) :

			$location_value = clean_values( $cell[1] );
			$gender_value = clean_values( $cell[2] );
			$ethnicity_value = clean_values( $cell[3] );
			$role_value = clean_values( $cell[4] );
			$disability_value = clean_values( strtolower( $cell[5] ) );
			$under16_value = clean_values( strtolower( $cell[6] ) );
			$days_worked_value = (float)$cell[7];
			$time_mentored_value = (float)$cell[8];
			$salary = (float)$cell[9];
			$gratuities = (float)$cell[10];
			$benefits = (float)$cell[11];
			$cost_training_value = (float)$cell[12];
			$training_days_value = (float)$cell[13];
			$note_value = clean_values( $cell[14] );

		endif;

		$location_id = $wpdb->get_row( "SELECT id FROM custom_location WHERE location='$location_value' AND loc_id=$master_loc" );
		if( empty( $location_id ) && !empty( $location_value ) ) :

			$wpdb->insert( 'custom_location',
				array(
					'location' => $location_value,
					'active' => -1,
					'loc_id' => $master_loc
				)
			);

			$location = $wpdb->insert_id;

		elseif( !empty( $location_id ) ) :

			$location = $location_id->id;

		else :

			$location = 0;

		endif;

		$gender_id = $wpdb->get_row( "SELECT id FROM master_tag WHERE tag='$gender_value' AND cat_id=7");
		if( empty( $gender_id ) ) : $gender = -1; else : $gender = $gender_id->id; endif;

		$ethnicity_id = $wpdb->get_row( "SELECT id FROM custom_tag WHERE tag='$ethnicity_value' AND cat_id=20 AND loc_id=$master_loc" );
		if( empty( $ethnicity_id ) && !empty( $ethnicity_value ) ) :

			$wpdb->insert( 'custom_tag',
				array(
					'tag' => $ethnicity_value,
					'tag_id' => NULL,
					'unit_id' => NULL,
					'cat_id' => 20,
					'active' => 1,
					'loc_id' => $master_loc
				)
			);

			$ethnicity = $wpdb->insert_id;

		elseif( !empty( $ethnicity_id ) ) :

			$ethnicity = $ethnicity_id->id;

		else :

			$ethnicity = NULL;

		endif;

		if( $disability_value == 'yes' ) : $disability = 1; else : $disability = 0; endif;

		$level_id = $wpdb->get_row( "SELECT id FROM master_tag WHERE tag='$level_value' AND cat_id=9");
		if( empty( $level_id ) ) : $level = NULL; else : $level = $level_id->id; endif;

		$role_id = $wpdb->get_row( "SELECT id FROM custom_tag WHERE tag='$role_value' AND cat_id=21 AND loc_id=$master_loc" );
		if( empty( $role_id ) && !empty( $role_value ) ) :

			$wpdb->insert( 'custom_tag',
				array(
					'tag' => $role_value,
					'tag_id' => NULL,
					'unit_id' => NULL,
					'cat_id' => 21,
					'active' => 1,
					'loc_id' => $master_loc
				)
			);

			$role = $wpdb->insert_id;

		elseif( !empty( $role_id ) ) :

			$role = $role_id->id;

		else :

			$role = NULL;

		endif;

		if( $part_time_value == 'yes' ) : $part_time = 1; elseif( $part_time_value == 'no' ) : $part_time = 0; elseif( empty( $part_time_value ) && ( $employee_type == 69 || $employee_type == 70 || $employee_type == 71 || $employee_type == 228 ) ) : $part_time = 0; else : $part_time = NULL; endif;

		if( $promoted_value == 'yes' ) : $promoted = 1; elseif( $promoted_value == 'no' ) : $promoted = 0; else : $promoted = NULL; endif;

		if( $under16_value == 'yes' ) : $under16 = 1; else : $under16 = 0; endif;

		if( empty( $start_date_value ) ) : $start_date = NULL; else : $start_date_create = date_create( $start_date_value ); $start_date = date_format( $start_date_create, 'Y-m-d' ); endif;

		if( empty( $leave_date_value ) ) : $leave_date = NULL; else : $leave_date_create = date_create( $leave_date_value ); $leave_date = date_format( $leave_date_create, 'Y-m-d' ); endif;

		if( $employee_type == 72 || $employee_type == 73 ) : $days_worked = $days_worked_value; else : $days_worked = NULL; endif;

		if( $employee_type == 73 ) : $time_mentored = $time_mentored_value; else : $time_mentored = NULL; endif;

		$contract = $wpdb->get_row( "SELECT labour_dpw, labour_wpy, labour_al FROM profile_locationmeta WHERE loc_id=$loc_id" );
		$labour_dpw = $contract->labour_dpw;
		$labour_wpy = $contract->labour_wpy;
		$labour_al = $contract->labour_al;

		if( !empty( $contract_dpw_value ) && ( $employee_type == 69 || $employee_type == 70 || $employee_type == 71 || $employee_type == 228 ) ) : $contract_dpw = $contract_dpw_value; elseif( empty( $contract_dpw_value ) && ( $employee_type == 69 || $employee_type == 70 || $employee_type == 71 || $employee_type == 228 ) ) : $contract_dpw = $labour_dpw ; else : $contract_dpw = NULL; endif;

		if( !empty( $contract_wpy_value ) && ( $employee_type == 69 || $employee_type == 70 || $employee_type == 71 || $employee_type == 228 ) ) : $contract_wpy = $contract_wpy_value; elseif( empty( $contract_wpy_value ) && ( $employee_type == 69 || $employee_type == 70 || $employee_type == 71 || $employee_type == 228 ) ) : $contract_wpy = $labour_wpy ; else : $contract_wpy = NULL; endif;

		if( !empty( $annual_leave_value ) && ( $employee_type == 69 || $employee_type == 70 || $employee_type == 71 || $employee_type == 228 ) ) : $annual_leave = $annual_leave_value; elseif( empty( $annual_leave_value ) && ( $employee_type == 69 || $employee_type == 70 || $employee_type == 71 || $employee_type == 228 ) ) : $annual_leave = $labour_al ; else : $annual_leave = NULL; endif;
	
		if( empty( $overtime_value ) ) : $overtime = 0; else : $overtime = $overtime_value; endif;
	
		if( empty( $bonuses_value ) ) : $bonuses = 0; else : $bonuses = $bonuses_value; endif;

		if( empty( $cost_training_value ) ) : $cost_training = NULL; else : $cost_training = $cost_training_value; endif;

		if( empty( $training_days_value ) ) : $training_days = NULL; else : $training_days = $training_days_value; endif;

		$tag1_id = $wpdb->get_row( "SELECT id FROM custom_tag WHERE tag='$tag1_value' AND cat_id=22 AND loc_id=$master_loc" );
		if( empty( $tag1_id ) && !empty( $tag1_value ) ) :

			$wpdb->insert( 'custom_tag',
				array(
					'tag' => $tag1_value,
					'tag_id' => NULL,
					'unit_id' => NULL,
					'cat_id' => 22,
					'active' => 1,
					'loc_id' => $master_loc
				)
			);

			$tag1 = $wpdb->insert_id;

		elseif( !empty( $tag1_id ) ) :

			$tag1 = $tag1_id->id;

		else :

			$tag1 = NULL;

		endif;

		$tag2_id = $wpdb->get_row( "SELECT id FROM custom_tag WHERE tag='$tag2_value' AND cat_id=23 AND loc_id=$master_loc" );
		if( empty( $tag2_id ) && !empty( $tag2_value ) ) :

			$wpdb->insert( 'custom_tag',
				array(
					'tag' => $tag2_value,
					'tag_id' => NULL,
					'unit_id' => NULL,
					'cat_id' => 23,
					'active' => 1,
					'loc_id' => $master_loc
				)
			);

			$tag2 = $wpdb->insert_id;

		elseif( !empty( $tag2_id ) ) :

			$tag2 = $tag2_id->id;

		else :

			$tag2 = NULL;

		endif;

		$tag3_id = $wpdb->get_row( "SELECT id FROM custom_tag WHERE tag='$tag3_value' AND cat_id=26 AND loc_id=$master_loc" );
		if( empty( $tag3_id ) && !empty( $tag3_value ) ) :

			$wpdb->insert( 'custom_tag',
				array(
					'tag' => $tag3_value,
					'tag_id' => NULL,
					'unit_id' => NULL,
					'cat_id' => 26,
					'active' => 1,
					'loc_id' => $master_loc
				)
			);

			$tag3 = $wpdb->insert_id;

		elseif( !empty( $tag3_id ) ) :

			$tag3 = $tag3_id->id;

		else :

			$tag3 = NULL;

		endif;

		$tag4_id = $wpdb->get_row( "SELECT id FROM custom_tag WHERE tag='$tag4_value' AND cat_id=27 AND loc_id=$master_loc" );
		if( empty( $tag4_id ) && !empty( $tag4_value ) ) :

			$wpdb->insert( 'custom_tag',
				array(
					'tag' => $tag4_value,
					'tag_id' => NULL,
					'unit_id' => NULL,
					'cat_id' => 27,
					'active' => 1,
					'loc_id' => $master_loc
				)
			);

			$tag4 = $wpdb->insert_id;

		elseif( !empty( $tag4_id ) ) :

			$tag4 = $tag4_id->id;

		else :

			$tag4 = NULL;

		endif;

		if( empty( $note_value ) ) : $note = NULL; else : $note = $note_value; endif;

		$wpdb->insert(
			'data_labour',
			array(
				'measure' => $measure,
				'measure_date' => $measure_date,
				'employee_type' => $employee_type,
				'location' => $location,
				'gender' => $gender,
				'ethnicity' => $ethnicity,
				'disability' => $disability,
				'level' => $level,
				'role' => $role,
				'part_time' => $part_time,
				'promoted' => $promoted,
				'under16' => $under16,
				'start_date' => $start_date,
				'leave_date' => $leave_date,
				'days_worked' => $days_worked,
				'time_mentored' => $time_mentored,
				'contract_dpw' => $contract_dpw,
				'contract_wpy' => $contract_wpy,
				'annual_leave' => $annual_leave,
				'salary' => $salary,
				'overtime' => $overtime,
				'bonuses' => $bonuses,
				'gratuities' => $gratuities,
				'benefits' => $benefits,
				'cost_training' => $cost_training,
				'training_days' => $training_days,
				'tag1' => $tag1,
				'tag2' => $tag2,
				'tag3' => $tag3,
				'tag4' => $tag4,
				'note' => $note,
				'loc_id' => $loc_id
			)
		);
	
	elseif( $mod_query == 5 ) :
	
		if( $measure_toggle == 86 && $tag_toggle == 1 ) :

			$amount = (float)$cell[3];
			$tax = (float)$cell[4];
			$location_value = clean_values( $cell[5] );
			$tag1_value = clean_values( $cell[6] );
			$tag2_value = clean_values( $cell[7] );
			$tag3_value = clean_values( $cell[8] );
			$tag4_value = clean_values( $cell[9] );
			$note_value = clean_values( $cell[10] );

		elseif( $measure_toggle == 86 && $tag_toggle == 0 ) :

			$amount = (float)$cell[3];
			$tax = (float)$cell[4];
			$location_value = clean_values( $cell[5] );
			$note_value = clean_values( $cell[6] );

		elseif( $measure_toggle != 86 && $tag_toggle == 1 ) :

			$amount = (float)$cell[1];
			$tax = (float)$cell[2];
			$location_value = clean_values( $cell[3] );
			$tag1_value = clean_values( $cell[4] );
			$tag2_value = clean_values( $cell[5] );
			$tag3_value = clean_values( $cell[6] );
			$tag4_value = clean_values( $cell[7] );
			$note_value = clean_values( $cell[8] );

		elseif( $measure_toggle != 86 && $tag_toggle == 0 ) :

			$amount = (float)$cell[1];
			$tax = (float)$cell[2];
			$location_value = clean_values( $cell[3] );
			$note_value = clean_values( $cell[4] );

		endif;

		$location_id = $wpdb->get_row( "SELECT id FROM custom_location WHERE location='$location_value' AND loc_id=$master_loc" );
		if( empty( $location_id ) && !empty( $location_value ) ) :

			$wpdb->insert( 'custom_location',
				array(
					'location' => $location_value,
					'active' => -1,
					'loc_id' => $master_loc
				)
			);

			$location = $wpdb->insert_id;

		elseif( !empty( $location_id ) ) :

			$location = $location_id->id;

		else :

			$location = 0;

		endif;

		$tag1_id = $wpdb->get_row( "SELECT id FROM custom_tag WHERE tag='$tag1_value' AND cat_id=22 AND loc_id=$master_loc" );
		if( empty( $tag1_id ) && !empty( $tag1_value ) ) :

			$wpdb->insert( 'custom_tag',
				array(
					'tag' => $tag1_value,
					'tag_id' => NULL,
					'unit_id' => NULL,
					'cat_id' => 22,
					'active' => 1,
					'loc_id' => $master_loc
				)
			);

			$tag1 = $wpdb->insert_id;

		elseif( !empty( $tag1_id ) ) :

			$tag1 = $tag1_id->id;

		else :

			$tag1 = NULL;

		endif;

		$tag2_id = $wpdb->get_row( "SELECT id FROM custom_tag WHERE tag='$tag2_value' AND cat_id=23 AND loc_id=$master_loc" );
		if( empty( $tag2_id ) && !empty( $tag2_value ) ) :

			$wpdb->insert( 'custom_tag',
				array(
					'tag' => $tag2_value,
					'tag_id' => NULL,
					'unit_id' => NULL,
					'cat_id' => 23,
					'active' => 1,
					'loc_id' => $master_loc
				)
			);

			$tag2 = $wpdb->insert_id;

		elseif( !empty( $tag2_id ) ) :

			$tag2 = $tag2_id->id;

		else :

			$tag2 = NULL;

		endif;

		$tag3_id = $wpdb->get_row( "SELECT id FROM custom_tag WHERE tag='$tag3_value' AND cat_id=30 AND loc_id=$master_loc" );
		if( empty( $tag3_id ) && !empty( $tag3_value ) ) :

			$wpdb->insert( 'custom_tag',
				array(
					'tag' => $tag3_value,
					'tag_id' => NULL,
					'unit_id' => NULL,
					'cat_id' => 30,
					'active' => 1,
					'loc_id' => $master_loc
				)
			);

			$tag3 = $wpdb->insert_id;

		elseif( !empty( $tag3_id ) ) :

			$tag3 = $tag3_id->id;

		else :

			$tag3 = NULL;

		endif;

		$tag4_id = $wpdb->get_row( "SELECT id FROM custom_tag WHERE tag='$tag4_value' AND cat_id=31 AND loc_id=$master_loc" );
		if( empty( $tag4_id ) && !empty( $tag4_value ) ) :

			$wpdb->insert( 'custom_tag',
				array(
					'tag' => $tag4_value,
					'tag_id' => NULL,
					'unit_id' => NULL,
					'cat_id' => 31,
					'active' => 1,
					'loc_id' => $master_loc
				)
			);

			$tag4 = $wpdb->insert_id;

		elseif( !empty( $tag4_id ) ) :

			$tag4 = $tag4_id->id;

		else :

			$tag4 = NULL;

		endif;

		if( empty( $note_value ) ) : $note = NULL; else : $note = $note_value; endif;

		$wpdb->insert(
			'data_supply',
			array(
				'measure' => $measure,
				'measure_date' => $measure_date,
				'amount' => $amount,
				'tax' => $tax,
				'location' => $location,
				'tag1' => $tag1,
				'tag2' => $tag2,
				'tag3' => $tag3,
				'tag4' => $tag4,
				'note' => $note,
				'loc_id' => $loc_id
			)
		);
	
	elseif( $mod_query == 4 ) :
	
		if( $measure_toggle == 86 && $tag_toggle == 1 && $donation_type != 66 ) :

			$value_type_value = clean_values( $cell[3] );
			$amount = (float)$cell[4];
			$location_value = clean_values( $cell[5] );
			$tag1_value = clean_values( $cell[6] );
			$tag2_value = clean_values( $cell[7] );
			$tag3_value = clean_values( $cell[8] );
			$tag4_value = clean_values( $cell[9] );
			$note_value = clean_values( $cell[10] );

		elseif( $measure_toggle == 86 && $tag_toggle == 0 && $donation_type != 66 ) :

			$value_type_value = clean_values( $cell[3] );
			$amount = $cell[4];
			$location_value = clean_values( $cell[5] );
			$note_value = clean_values( $cell[6] );

		elseif( $measure_toggle != 86 && $tag_toggle == 1 && $donation_type != 66 ) :

			$value_type_value = clean_values( $cell[1] );
			$amount = (float)$cell[2];
			$location_value = clean_values( $cell[3] );
			$tag1_value = clean_values( $cell[4] );
			$tag2_value = clean_values( $cell[5] );
			$tag3_value = clean_values( $cell[6] );
			$tag4_value = clean_values( $cell[7] );
			$note_value = clean_values( $cell[8] );

		elseif( $measure_toggle != 86 && $tag_toggle == 0 && $donation_type != 66 ) :

			$value_type_value = clean_values( $cell[1] );
			$amount = (float)$cell[2];
			$location_value = clean_values( $cell[3] );
			$note_value = clean_values( $cell[4] );

		elseif( $measure_toggle == 86 && $tag_toggle == 1 && $donation_type == 66 ) :

			$duration_value = (float)$cell[3];
			$amount = (float)$cell[4];
			$location_value = clean_values( $cell[5] );
			$tag1_value = clean_values( $cell[6] );
			$tag2_value = clean_values( $cell[7] );
			$tag3_value = clean_values( $cell[8] );
			$tag4_value = clean_values( $cell[9] );
			$note_value = clean_values( $cell[10] );

		elseif( $measure_toggle == 86 && $tag_toggle == 0 && $donation_type == 66 ) :

			$duration_value = (float)$cell[3];
			$amount = (float)$cell[4];
			$location_value = clean_values( $cell[5] );
			$note_value = clean_values( $cell[6] );

		elseif( $measure_toggle != 86 && $tag_toggle == 1 && $donation_type == 66 ) :

			$duration_value = (float)$cell[1];
			$amount = (float)$cell[2];
			$location_value = clean_values( $cell[3] );
			$tag1_value = clean_values( $cell[4] );
			$tag2_value = clean_values( $cell[5] );
			$tag3_value = clean_values( $cell[6] );
			$tag4_value = clean_values( $cell[7] );
			$note_value = clean_values( $cell[8] );

		elseif( $measure_toggle != 86 && $tag_toggle == 0 && $donation_type == 66 ) :

			$duration_value = (float)$cell[1];
			$amount = (float)$cell[2];
			$location_value = clean_values( $cell[3] );
			$note_value = clean_values( $cell[4] );

		endif;

		$value_type_id = $wpdb->get_row( "SELECT id FROM master_tag WHERE tag='$value_type_value' AND cat_id=5");
		if( empty( $value_type_id ) && $donation_type != 66 ) : $value_type = -1; elseif( $donation_type == 66 ) : $value_type = 67; else : $value_type = $value_type_id->id; endif;

		if( $donation_type == 66 && empty( $duration_value ) ) : 
			
			$duration = -1;
				
		elseif( $donation_type == 66 && !empty( $duration_value ) ) : 
			
			$duration = $duration_value;
	
		else :
	
			$duration = NULL;
	
		endif;

		$location_id = $wpdb->get_row( "SELECT id FROM custom_location WHERE location='$location_value' AND loc_id=$master_loc" );
		if( empty( $location_id ) && !empty( $location_value ) ) :

			$wpdb->insert( 'custom_location',
				array(
					'location' => $location_value,
					'active' => -1,
					'loc_id' => $master_loc
				)
			);

			$location = $wpdb->insert_id;

		elseif( !empty( $location_id ) ) :

			$location = $location_id->id;

		else :

			$location = 0;

		endif;

		$tag1_id = $wpdb->get_row( "SELECT id FROM custom_tag WHERE tag='$tag1_value' AND cat_id=22 AND loc_id=$master_loc" );
		if( empty( $tag1_id ) && !empty( $tag1_value ) ) :

			$wpdb->insert( 'custom_tag',
				array(
					'tag' => $tag1_value,
					'tag_id' => NULL,
					'unit_id' => NULL,
					'cat_id' => 22,
					'active' => 1,
					'loc_id' => $master_loc
				)
			);

			$tag1 = $wpdb->insert_id;

		elseif( !empty( $tag1_id ) ) :

			$tag1 = $tag1_id->id;

		else :

			$tag1 = NULL;

		endif;

		$tag2_id = $wpdb->get_row( "SELECT id FROM custom_tag WHERE tag='$tag2_value' AND cat_id=23 AND loc_id=$master_loc" );
		if( empty( $tag2_id ) && !empty( $tag2_value ) ) :

			$wpdb->insert( 'custom_tag',
				array(
					'tag' => $tag2_value,
					'tag_id' => NULL,
					'unit_id' => NULL,
					'cat_id' => 23,
					'active' => 1,
					'loc_id' => $master_loc
				)
			);

			$tag2 = $wpdb->insert_id;

		elseif( !empty( $tag2_id ) ) :

			$tag2 = $tag2_id->id;

		else :

			$tag2 = NULL;

		endif;

		$tag3_id = $wpdb->get_row( "SELECT id FROM custom_tag WHERE tag='$tag3_value' AND cat_id=28 AND loc_id=$master_loc" );
		if( empty( $tag3_id ) && !empty( $tag3_value ) ) :

			$wpdb->insert( 'custom_tag',
				array(
					'tag' => $tag3_value,
					'tag_id' => NULL,
					'unit_id' => NULL,
					'cat_id' => 28,
					'active' => 1,
					'loc_id' => $master_loc
				)
			);

			$tag3 = $wpdb->insert_id;

		elseif( !empty( $tag3_id ) ) :

			$tag3 = $tag3_id->id;

		else :

			$tag3 = NULL;

		endif;

		$tag4_id = $wpdb->get_row( "SELECT id FROM custom_tag WHERE tag='$tag4_value' AND cat_id=29 AND loc_id=$master_loc" );
		if( empty( $tag4_id ) && !empty( $tag4_value ) ) :

			$wpdb->insert( 'custom_tag',
				array(
					'tag' => $tag4_value,
					'tag_id' => NULL,
					'unit_id' => NULL,
					'cat_id' => 29,
					'active' => 1,
					'loc_id' => $master_loc
				)
			);

			$tag4 = $wpdb->insert_id;

		elseif( !empty( $tag4_id ) ) :

			$tag4 = $tag4_id->id;

		else :

			$tag4 = NULL;

		endif;

		if( empty( $note_value ) ) : $note = NULL; else : $note = $note_value; endif;

		$wpdb->insert( 'data_charity',
			array(
				'measure' => $measure,
				'measure_date' => $measure_date,
				'donation_type' => $donation_type,
				'value_type' => $value_type,
				'amount' => $amount,
				'duration' => $duration,
				'location' => $location,
				'tag1' => $tag1,
				'tag2' => $tag2,
				'tag3' => $tag3,
				'tag4' => $tag4,
				'note' => $note,
				'loc_id' => $loc_id
			)
		);
	
	endif;
}