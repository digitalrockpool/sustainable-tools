<?php

/* Includes: FORM DYNAMIC

@package	Yardstick
@author		Digital Rockpool
@link		https://yardstick.co.uk
@copyright	Copyright (c) 2018, Digital Rockpool LTD
@license	GPL-2.0+ */



// LOCATION DETAILS
add_filter( 'gform_field_value', 'loc_fields', 10, 3 );
function loc_fields( $value, $field, $name ) {
	
	global $wpdb;
	global $post;

	$page_id = $post->ID;
	$user_id = get_current_user_id();
	
	$plan_id = $_SESSION['plan_id'];
	$licence = $_SESSION['licence'];
	
	$master_loc = $_SESSION['master_loc']; 
	
	/* //////////////////////////// TO BE DELETED ///////////////////////////
	
	WILL ANY OF THIS BE NEEDED ONE GF IS GONE?

	
	
	$loc = (int)$_GET['loc']; 
	
	if( empty( $loc ) && $plan_id == 4 && $licence >= 1 ) :
		$loc_id = -1;
	elseif( !empty( $loc ) && $plan_id == 4 ) :
		$loc_id = $loc;
	else :
		$loc_id = $master_loc;
	endif; */
   
	$loc_edit = $wpdb->get_row( "SELECT loc_name, street, city, county, country, latitude, longitude FROM profile_location WHERE master_loc=$master_loc ORDER BY id DESC" );
	$loc_profile = $wpdb->get_row( "SELECT profile_locationmeta.id, industry, master_tag.tag AS industry_tag, sector, sector.tag AS sector_tag, subsector, subsector.tag AS subsector_tag, other, fy_day, fy_month, currency, geo_type, very_local, local, calendar, parent_id FROM profile_locationmeta LEFT JOIN master_tag ON profile_locationmeta.industry=master_tag.id LEFT JOIN master_tag sector ON profile_locationmeta.sector=sector.id LEFT JOIN master_tag subsector ON profile_locationmeta.subsector=subsector.id WHERE profile_locationmeta.id IN (SELECT MAX(id) FROM profile_locationmeta WHERE loc_id=$master_loc)" );

	$labour_dpw = $wpdb->get_row( "SELECT tag FROM custom_tag WHERE loc_id=$master_loc AND active=1 AND id IN (SELECT MAX(id) FROM custom_tag WHERE tag_id=281)" );
	$labour_wpy = $wpdb->get_row( "SELECT tag FROM custom_tag WHERE loc_id=$master_loc AND active=1 AND id IN (SELECT MAX(id) FROM custom_tag WHERE tag_id=282)" );
	$labour_al = $wpdb->get_row( "SELECT tag FROM custom_tag WHERE loc_id=$master_loc AND active=1 AND id IN (SELECT MAX(id) FROM custom_tag WHERE tag_id=283)" );
	$measure_count = $wpdb->get_row( "SELECT id FROM data_measure WHERE active=1 AND loc_id=$master_loc" );
	
	//////////////////////////// TO BE DELETED ///////////////////////////
	// $permission_toggle = $wpdb->get_var( "SELECT count(*) FROM relation_user WHERE loc_id=$master_loc");
	
	// $loc_id_compare = $loc_profile->loc_id;
	// $master_loc_compare = $loc_profile->master_loc;
	$loc_city = $loc_edit->city;
	$loc_county = $loc_edit->county;
	$very_local_distance = $loc_profile->very_local;
	$local_distance = $loc_profile->local;
	$industry = $loc_profile->industry;
	$sector = $loc_profile->sector;
	$subsector = $loc_profile->subsector;
	$industry_tag = $loc_profile->industry_tag;
	$sector_tag = $loc_profile->sector_tag;
	$subsector_tag = $loc_profile->subsector_tag;
	$other = $loc_profile->other;
	$split = explode( "|", $other );
	$industry_other = $split[0];
	$sector_other = $split[1];
	$subsector_other = $split[2];
	
	$fy_day = $loc_profile->fy_day;
	$fy_month = $loc_profile->fy_month;
	
	$fy_month_name = date("F", mktime(0, 0, 0, $fy_month, 10));
	$geo_type_tag_id = $loc_profile->geo_type;
	
	if( $geo_type_tag_id == 143 ) : $geo_type = 'By distance'; else : $geo_type = 'By location'; endif;
	
	//////////////////////////// TO BE DELETED ///////////////////////////
	// if ( $loc_id_compare == $master_loc_compare && !empty( $loc_id_compare ) && !empty( $master_loc_compare ) ) : $master = 1; else : $master = 0; endif;
	
	if( $page_id == 1100 ) :
	
		$values = array(
			'loc_id' => $master_loc,
			'loc_name' => $loc_edit->loc_name,
			'loc_street' => $loc_edit->street,
			'loc_city' => $loc_city,
			'loc_county' => $loc_county,
			'loc_country' => $loc_edit->country,
			'loc_latitude_longitude' => $loc_edit->latitude.'|'.$loc_edit->longitude
		);
	
	else : 
	
		$values = array(
			'first_submission_id' => $loc_profile->id, /* used for first submission of report settings only */
			'report_active' => $loc_profile->parent_id,
			'is_master' => 1, // THIS WILL NEED UPDATING WHEN MULTILOCATION LAUNCHES
			'plan_id' => $plan_id,
			'licence' => $licence,
			'fy_day' => $fy_day,
			'fy_month' => $fy_month,
			'fy_month_name' => $fy_month_name,
			'fy_start_date' => $fy_day.'/'.$fy_month.'/'.date( 'Y',strtotime( '-1 year' ) ),
			'currency' => $loc_profile->currency,
			'geo_type_id' => $loc_profile->geo_type,
			'geo_type' => $geo_type,
			'local' => $loc_county,
			'very_local' => $loc_city,
			'local_distance' => $local_distance,
			'very_local_distance' => $very_local_distance,
			'industry' => $industry,
			'sector' => $sector,
			'subsector' => $subsector,
			'industry_tag' => $industry_tag,
			'sector_tag' => $sector_tag,
			'subsector_tag' => $subsector_tag,
			'industry_other' => $industry_other,
			'sector_other' => $sector_other,
			'subsector_other' => $subsector_other,
			'calendar' => $loc_profile->calendar,
			// 'permission_toggle' => $permission_toggle,
			'measure_toggle' => $_SESSION['measure_toggle'],
			'measure_count' => $measure_count->id,
			'tag_toggle' => $_SESSION['tag_toggle'],
			// 'labour_dpw' => $labour_dpw->tag,  dont think I need
			// 'labour_wpy' => $labour_wpy->tag,
			// 'labour_al' => $labour_al->tag
			'upload_loc_id' => $master_loc,
		);
	
	endif;
	
	return isset( $values[ $name ] ) ? $values[ $name ] : $value;
}

add_filter( 'gform_field_value', 'measure_toggle', 10, 3 );
function measure_toggle( $value, $field, $name ) {

	global $wpdb;

	$master_loc = $_SESSION['master_loc'];

	$measures= $wpdb->get_row( "SELECT parent_id, tag_id FROM custom_tag WHERE loc_id=$master_loc AND cat_id=13 AND id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id)" );

	$values = array(
		'measure_parent_id' => $measures->parent_id,
		'measure_toggle' => $measures->tag_id
	);

	return isset( $values[ $name ] ) ? $values[ $name ] : $value;
}

add_filter( 'gform_field_value', 'category_count', 10, 3 );
function category_count( $value, $field, $name ) {

	global $wpdb;

	$master_loc = $_SESSION['master_loc'];

	$category_count = $wpdb->get_var( "SELECT COUNT(id) FROM custom_category WHERE active=1 AND loc_id=$master_loc AND id IN (SELECT MAX(id) FROM custom_category GROUP BY parent_id)" );

	$values = array(
		'category_count' => $category_count
	);

	return isset( $values[ $name ] ) ? $values[ $name ] : $value;
}

// LOCATION NUMBER
add_filter( 'gform_field_value', 'loc_number_field', 10, 3 );
function loc_number_field( $value, $field, $name ) {
	
	$loc_number = $_SESSION['loc_number'];
	
    $values = array(
        'loc_number'  => $loc_number
    );

    return isset( $values[ $name ] ) ? $values[ $name ] : $value;
}

// WEEK, MONTH & YEAR
add_filter( 'gform_field_value', 'month_year', 10, 3 );
function month_year( $value, $field, $name ) {
	
	$month = date('F');
	$year = date('Y');
	$yesterday = date('d/m/Y',strtotime('-1 days'));
	$last_week = date('d/m/Y',strtotime('-2 Monday'));
	$last_month = date('01/m/Y',strtotime('-1 month'));
	$last_year = date('Y',strtotime('-1 year'));
	
    $values = array(
		'day' => date( 'd/m/Y' ),
        'month' => $month,
        'year' => $year,
		'yesterday' => $yesterday,
		'last_week' => $last_week,
        'last_month' => $last_month,
        'last_year' => $last_year,
    );

    return isset( $values[ $name ] ) ? $values[ $name ] : $value;
}

// EDIT LOCATION PROFILE: BOUNDARIES ??????????????????????????????????????????????????????
add_filter( "gform_pre_render_114", "edit_location_boundaries" );
function edit_location_boundaries( $form ){ ?>
	<script type="text/javascript">
		jQuery(document).ready(function(){

			jQuery('#input_114_53').on('change', function()
			{
				var selectedValue = jQuery("#input_114_53").val();
				jQuery("#input_114_57").val(selectedValue);
			});

			jQuery('#input_114_54').on('change', function()
			{
				var selectedValue = jQuery("#input_114_54").val();
				jQuery("#input_114_58").val(selectedValue);
			});

			jQuery('#input_114_51').on('change', function()
			{
				var selectedValue = jQuery("#input_114_51").val();
				jQuery("#input_114_60").val(selectedValue);
			});
		});
    </script> <?php

return $form;
}