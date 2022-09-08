<?php 
/* ***

Template Part:  Settings - Locations

@package	      Sustainable Tools
@author		      Digital Rockpool
@link		        https://www.sustainable.tools/yardstick
@copyright	    Copyright (c) 2022, Digital Rockpool LTD
@license	      GPL-2.0+ 

*** */

$master_loc = $_SESSION['master_loc'];
$user_id = get_current_user_id();

$entry_date = date( 'Y-m-d H:i:s' );

echo do_shortcode( '[gravityform id="83" title="false" description="false" ajax="false"]' );

$location = $entry[3];
$street_entry = $entry[20];
$city = $entry[21];
$county = $entry[22];
$country = $entry[18];
$coords = maybe_unserialize( $entry[6] );
$latitude  = $coords['latitude'];
$longitude = $coords['longitude'];

if( empty( $street_entry) ) : $street = NULL; else : $street = $street_entry; endif;

$location_cleanse = str_replace( "'", "\'", $location );
$loc_check = $wpdb->get_row( "SELECT location FROM custom_location WHERE location='$location_cleanse' AND loc_id=$master_loc" );

if( empty( $loc_check ) ) :

  $wpdb->insert( 'custom_location',
    array(
      'entry_date' => $entry_date,
      'record_type' => 'entry',
      'location' => $location,
      'street' => $street,
      'city' => $city,
      'county' => $county,
      'country' => $country,
      'latitude' => $latitude,
      'longitude' => $longitude,
      'parent_id' => 0,
      'user_id' => $user_id,
      'active' => 1,
      'loc_id' => $master_loc
    )
  );

  $parent_id = $wpdb->insert_id;

  $wpdb->update( 'custom_location',
    array(
      'parent_id' => $parent_id,
    ),
    array(
      'id' => $parent_id
    )
  );

endif;