<?php

/* Includes: FORM EXTRAS

@package	Yardstick
@author		Digital Rockpool
@link		https://yardstick.co.uk
@copyright	Copyright (c) 2018, Digital Rockpool LTD
@license	GPL-2.0+ */

// THIS FILE WILL GO WHEN ALL FORMS ARE MOVED TO BOOTSTRAP

// READ ONLY FIELDS
add_filter('gform_pre_render', 'add_readonly_script');
function add_readonly_script($form){
    ?>
    
    <script type="text/javascript">
        jQuery(document).ready(function(){
            jQuery("li.gf_readonly input").attr("readonly","readonly");
        });
    </script>
    
    <?php
    return $form;
}

//HTML BLOCK SHORTCODE
add_shortcode('master-loc-name', 'master_loc_name');
function master_loc_name() {

	return $_SESSION['master_loc_name'];
}

add_shortcode('measure-frequency', 'measure_frequency');
function measure_frequency() {
	
	global $wpdb;
	
	$tag_id = $_SESSION['measure_toggle'];
	$measure_lookup = $wpdb->get_row( "SELECT tag FROM master_tag WHERE id=$tag_id" );
	return $measure_lookup->tag;
	
}

add_shortcode('reporting-frequency', 'reporting_frequency');
function reporting_frequency() {
	
	global $wpdb;
	
	$master_loc = $_SESSION['master_loc'];
	$reporting_lookup = $wpdb->get_row( "SELECT fy_day, fy_month FROM profile_locationmeta WHERE loc_id=$master_loc" );
	
	$fy_day = $reporting_lookup->fy_day;
	$fy_month = $reporting_lookup->fy_month;
	
	$day_number = date('d', mktime(0, 0, 0, 0, $fy_day, 10));
	$month_name = date('F', mktime(0, 0, 0, $fy_month, 10));
	
	return $day_number.' '.$month_name;
	
}

add_shortcode('tag-enable', 'tag_enable');
function tag_enable() {

	return str_replace( [1, 0], ["Enable", "Disable"], $_SESSION['tag_toggle'] );
}
	
/* add_filter( 'gform_upload_path', 'change_upload_path', 10, 2 );
function change_upload_path( $path_info, $form_id ) {
	
	$loc_id = $_SESSION['loc_id'];
	$site_url = get_site_url();
	$year = date('Y');
	$month = date('m');
	
	$path_info['path'] = '/nas/content/staging/justreport/wp-content/themes/yardstick/uploads/'.$loc_id.'/'.$year.'/'.$month.'/';
	$path_info['url'] = ''.$site_url.'/uploads/'.$loc_id.'/'.$year.'/'.$month.'/';
	return $path_info;
} */

/* add_filter( 'gform_field_validation_83_3', 'unique_geoname', 10, 3 );
function unique_geoname( $result, $value, $form, $field ) {

	global $wpdb;

	$results = $wpdb->get_results( "SELECT name FROM custom_geolocation" );

	$values = array(
		'name' => $results->name,
	);

	if ( !array_key_exists( $values ) ) {
		$result['is_valid'] = false;
		$result['message'] = 'You have already used this code. Please try another.';
	}
	return $result;
} */