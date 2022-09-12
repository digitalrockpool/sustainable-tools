<?php

/* Includes: FORM SUBMISSION

@package	Sustainable Tools
@author		Digital Rockpool
@link		https://www.sustainable.tools/yardstick
@copyright	Copyright (c) 2022, Digital Rockpool LTD
@license	GPL-2.0+ 

Table of Contents
	- My Account
		- Add / Edit Location Profile


	* **/


// My Account: Add / Edit Location Profile
add_action( 'gform_after_submission_114', 'edit_entry_location_profile', 10, 2 );
function edit_entry_location_profile( $entry, $form ) {
	
	global $wpdb;
	global $post;
	
	$page_id = $post->ID;
	
	$entry_date = date( 'Y-m-d H:i:s' );
	
	$user_id = get_current_user_id();
	$master_loc = $_SESSION['master_loc'];
	$plan_id = $_SESSION['plan_id'];
	$licence = $_SESSION['licence'];
	$loc_country = $_SESSION['loc_country'];
	
	$loc_name = $entry[3];
	$street_entry = $entry[55];
	if( empty( $street_entry ) ) : $street = NULL; else : $street = $street_entry; endif;
	
	$city = $entry[53];
	$county = $entry[54];
	$country = $entry[51];
	$coords = maybe_unserialize( $entry[6] );
	$latitude  = $coords['latitude'];
    $longitude = $coords['longitude'];

	if( $page_id == 1507 ) : // add property
	
		$wpdb->insert(
			'profile_location',
			array(
				'entry_date' => $entry_date,
				'record_type' => 'entry',
				'master_loc' => $master_loc,
				'plan_id' => $plan_id,
				'licence' => $licence,
				'loc_name' => $loc_name,
				'street' => $street,
				'city' => $city,
				'county' => $county,
				'country' => $country,
				'latitude' => $latitude,
				'longitude' => $longitude,
				'active' => 1,
				'parent_id' => 0,
				'user_id' => $user_id
			)
		);
	
		$last_loc_id = $wpdb->insert_id;
	
		$wpdb->update(
			'profile_location',
			array(
				'parent_id' => $last_loc_id
			),
			array(
				'id' => $last_loc_id
			)
		);
	
	else : // update master location for first submission
	
		if( empty( $loc_country ) ) : 
	
			$wpdb->update( 'profile_location',
				array(
					'entry_date' => $entry_date,
					'record_type' => 'entry',
					'master_loc' => $master_loc,
					'plan_id' => $plan_id,
					'licence' => $licence,
					'loc_name' => $loc_name,
					'street' => $street,
					'city' => $city,
					'county' => $county,
					'country' => $country,
					'latitude' => $latitude,
					'longitude' => $longitude,
					'active' => 1,
					'parent_id' => $master_loc,
					'user_id' => $user_id
				),
				array( 
					'id' => $master_loc
				)
			);
	
		else : 
	
			$wpdb->insert( 'profile_location',
				array(
					'entry_date' => $entry_date,
					'record_type' => 'entry_revision',
					'master_loc' => $master_loc,
					'plan_id' => $plan_id,
					'licence' => $licence,
					'loc_name' => $loc_name,
					'street' => $street,
					'city' => $city,
					'county' => $county,
					'country' => $country,
					'latitude' => $latitude,
					'longitude' => $longitude,
					'active' => 1,
					'parent_id' => $master_loc,
					'user_id' => $user_id
				)
			);
	
		endif;
	
	endif;
}



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