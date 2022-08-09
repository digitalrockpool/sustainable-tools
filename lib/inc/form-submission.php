<?php

/* Includes: FORM SUBMISSION

@package	Yardstick
@author		Digital Rockpool
@link		https://yardstick.co.uk
@copyright	Copyright (c) 2018, Digital Rockpool LTD
@license	GPL-2.0+ */





// SETTINGS: LOCATION - MODAL
add_action( 'gform_after_submission_131', 'submit_settings_location_modal', 10, 2 );
function submit_settings_location_modal( $entry, $form ) {
	
  	global $wpdb;
	$master_loc = $_SESSION['master_loc'];
	$user_id = get_current_user_id();
	
	$entry_date = date( 'Y-m-d H:i:s' );
	
	$location = $entry[3];
	$street_entry = $entry[20];
	$city = $entry[21];
	$county = $entry[22];
	$country = $entry[18];
	$coords = maybe_unserialize( $entry[6] );
	$latitude  = $coords['latitude'];
    $longitude = $coords['longitude'];
	$parent_id = $entry[26];
	
	if( empty( $street_entry) ) : $street = NULL; else : $street = $street_entry; endif;
	
	$wpdb->insert( 'custom_location',
		array(
			'entry_date' => $entry_date,
			'record_type' => 'entry_revision',
			'location' => $location,
			'street' => $street,
			'city' => $city,
			'county' => $county,
			'country' => $country,
		  	'latitude' => $latitude,
		  	'longitude' => $longitude,
			'parent_id' => $parent_id,
			'user_id' => $user_id,
			'active' => 1,
		  	'loc_id' => $master_loc
		)
	);
}