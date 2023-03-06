<?php

/* Includes: Registration Code Snippets

@package	Yardstick
@author		Digital Rockpool
@link		https://yardstick.co.uk
@copyright	Copyright (c) 2018, Digital Rockpool LTD
@license	GPL-2.0+ */

/* CHOOSE YOUR PLAN: COUNTRY SELECTOR */
function country_selector() {

    global $wpdb;

    $results = $wpdb->get_results("SELECT country FROM master_country ORDER BY country ASC");

    foreach ($results as $rows) :
        $option .= '<option value="'.$rows->country.'">';
        $option .= $rows->country;
        $option .= '</option>';
    endforeach;  ?>

	<script>
		jQuery( function($) {

			$( '#country_selector' ).change( function() {
				var countryPOP = $( '#country_selector' ).val();

				$.ajax( {
				url: '<?php echo admin_url( 'admin-ajax.php') ?>',
				type: 'POST',
				dataType: 'json',
				data: 'action=country_calculation&countryID=' + countryPOP,

				success: function( results ) {
					$( '.micro-price' ).empty();
					$( '.micro-price' ).text( results.micro_annual_month_price );
					}
				});
			});

		});
	</script> <?php

	return '<select class="selectpicker form-control" data-live-search="true" id="country_selector"><option value="0" disabled selected>Select Your Country</option>'.$option.'</select>';
	

}
add_shortcode('country-selector', 'country_selector');


/* add_filter('gform_pre_render_141', 'populate_user_register_country');
add_filter('gform_admin_pre_render_141', 'populate_user_register_country');
function populate_user_register_country( $form ) {
	
	global $wpdb;
	
	$results = $wpdb->get_results( "SELECT country FROM master_country ORDER BY country ASC" );

	foreach( $results as $rows ) :
		$choices[] = array("text" => $rows->country, "value" => $rows->country );
	endforeach;

	foreach( $form["fields"] as &$field ) :
		if( $field["id"] == 19) :
			$field["placeholder"] = "Select your Country";
			$field["choices"] = $choices;
		endif;
	endforeach; ?>
	
	<script>
		jQuery( function($) {

			$( '#input_141_19' ).change( function() {
				var countryPOP = $( '#input_141_19' ).val();
				
				$.ajax( {
				url: '<?php echo admin_url( 'admin-ajax.php') ?>',
				type: 'POST',
				dataType: 'json',
				data: 'action=country_calculation&countryID=' + countryPOP,

				success: function( results ) {
					//$( '#input_141_35, #input_141_44, #input_141_43, #input_141_47, #input_141_48, #input_141_49, #ginput_base_price_141_35, #ginput_base_price_141_44, #ginput_base_price_141_43, #ginput_base_price_141_47, #ginput_base_price_141_48, #ginput_base_price_141_49' ).empty();
					$( '#input_141_35' ).text( results.micro_month_plan );
					$( '#input_141_47' ).text( results.micro_annual_plan );
					$( '#ginput_base_price_141_35' ).val( results.micro_month_plan );
					$( '#ginput_base_price_141_47' ).val( results.micro_annual_plan );
					$( '#gfield_description_141_35' ).text( results.micro_month_plan_description );
					$( '#gfield_description_141_47' ).text( results.micro_annual_plan_description );
					}
				});
			});
		});
	</script> <?php 

	return $form;
} */

function ajax_country_calculation() {
   $data = country_calculation();
  
   wp_send_json( $data );
}

function country_calculation( ) {

    if( isset( $_POST['countryID'] ) ) :

        $parentCat = $_POST['countryID'];
		$_SESSION['session_country'] = $parentCat;

        global $wpdb;
	
        $conversions = $wpdb->get_row( "SELECT discount FROM master_country WHERE country='$parentCat'" );

		/* $micro_month_GBP = $_SESSION['micro_month_value']; */
		$micro_annual_GBP = $_SESSION['micro_annual_value'];
		$discount = $conversions->discount;
	
        /* $micro_month = ($micro_month_GBP) / 100 * (100 - $discount);
		$micro_month_cap = $micro_month=( $micro_month <= $micro_month_GBP )?$micro_month:$micro_month_GBP; */
		$micro_annual = ($micro_annual_GBP) / 100 * (100 - $discount);
		$micro_annual_cap = $micro_annual=( $micro_annual <= $micro_annual_GBP )?$micro_annual:$micro_annual_GBP;
	
		return array(
        	/* 'micro_month_price' => number_format( $micro_month_cap,2 ), */
        	'micro_annual_price' => number_format( $micro_annual_cap*12,2 ),
        	'micro_annual_month_price' => number_format( $micro_annual_cap,2 ),
			/* 'micro_month_plan' => '£ '.number_format( $micro_month_cap,2 ).' /month',
			'micro_annual_plan' => '£ '.number_format( $micro_annual_cap*12,2 ),
			'micro_month_plan_description' => 'Annual total £'.number_format( $micro_month_cap*12,2 ),
			'micro_annual_plan_description' => 'Annual saving £'.number_format( ($micro_month_cap*12) - ($micro_annual_cap*12),2 ) */
		);

	endif;
}
add_action('wp_ajax_nopriv_country_calculation', 'ajax_country_calculation');
add_action('wp_ajax_country_calculation', 'ajax_country_calculation');


// ACCOUNT: ACCOUNT ACTIVATION
add_action( 'gform_user_registered', 'add_entry_relation_user', 10, 4 );
function add_entry_relation_user( $user_id, $feed, $entry, $password ) {
	
	global $wpdb;
	
	$form_id = rgar( $feed, 'form_id' );
	$user = get_userdata( $user_id );
	$entry_date = date( 'Y-m-d H:i:s' );
	
	if( $form_id == 119 ) : /* user registration form */
	
		$user_login = $user->user_login;
		$user_password = $password;
		$plan = $entry[2];
		$licence = 1; 
		$country = $entry[19];
		$loc_name = $entry[37];
		$org = str_replace( '-', ' ', $_GET['org'] );
		$standards = $wpdb->get_results( "SELECT master_standard.id FROM master_standard INNER JOIN profile_location ON master_standard.loc_id=profile_location.parent_id WHERE loc_name='$org'" );
		$plans = $wpdb->get_row( "SELECT id FROM master_plan WHERE plan='$plan'" );
		$plan_id = $plans->id;

		wp_signon(
			array(
				'user_login' => $user_login,
				'user_password' =>  $user_password,
				'remember' => false
			)
		);
	
		$wpdb->insert( 'profile_location',
			array(
				'entry_date' => $entry_date,
				'record_type' => 'entry',
				'plan_id' => $plan_id,
				'licence' => $licence,
				'loc_name' => $loc_name,
				// 'country' => $country,
				'active' => 1,
				'user_id' => $user->ID
				)
		);

		$last_loc_id = $wpdb->insert_id;

		$wpdb->update( 'profile_location',
			array(
				'master_loc' => $last_loc_id,
				'parent_id' => $last_loc_id
			),
			array(
				'id' => $last_loc_id
			)
		);
	
		$wpdb->insert( 'profile_locationmeta',
			array(
				'entry_date' => $entry_date,
				'record_type' => 'entry',
				'parent_id' => 0, /* if parent == 0 report settings is not active */
				'user_id' => $user->ID,
				'loc_id' => $last_loc_id
				)
		);
	
		$user_role = 222; /* super admin */

		$wpdb->insert( 'relation_user',
			array(
				'entry_date' => $entry_date,
				'record_type' => 'entry',
				'user_id' => $user->ID,
				'loc_id' => $last_loc_id,
				'role_id' => $user_role,
				'active' => 1,
				'deleted_user' => NULL,
				'parent_id' => 0,
				'edited_by' => $user->ID,
			)
		);
	
		$parent_id = $wpdb->insert_id;

		$wpdb->update( 'relation_user',
			array(
				'parent_id' => $parent_id,
			),
			array(
				'id' => $parent_id
			)
		);

		$wpdb->insert( 'custom_tag', /* set up monthly measures */
			array(
				'entry_date' => $entry_date,
				'record_type' => 'entry',
				'tag' => NULL,
				'tag_id' => 84, /* monthly */
				'size' => NULL,
				'unit_id' => NULL,
				'custom_cat' => NULL,
				'cat_id' => 13, /* measures */
				'active' => 1,
				'parent_id' => 0,
				'user_id' => $user->ID,
				'loc_id' => $last_loc_id,
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
	
		if( $plan_id == 2 ) : $active = 0; else : $active = 1; endif; /* switch off tags for micro plan */

		$wpdb->insert( 'custom_tag', /* set up categories and tags */
			array(
				'entry_date' => $entry_date,
				'record_type' => 'entry',
				'tag' => NULL,
				'tag_id' => NULL,
				'size' => NULL,
				'unit_id' => NULL,
				'custom_cat' => NULL,
				'cat_id' => 22, /* categories and tags */
				'active' => $active,
				'parent_id' => 0,
				'user_id' => $user->ID,
				'loc_id' => $last_loc_id,
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
	
		if( !empty( $standards ) ) :
	
			foreach( $standards as $standard ) :

				$standard_id = $standard->id;

				$wpdb->insert( 'relation_standard',
					array(
						'entry_date' => $entry_date,
						'record_type' => 'entry',
						'user_id' => $user->ID,
						'loc_id' => $last_loc_id,
						'std_id' => $standard_id,
						'active' => 1,
						'parent_id' => 0
					)
				);
	
				$parent_id = $wpdb->insert_id;

				$wpdb->update( 'relation_standard',
					array(
						'parent_id' => $parent_id,
					),
					array(
						'id' => $parent_id
					)
				);
			
			endforeach;
	
		endif;
	
	elseif( $form_id == 120 ) : /* add team member */
	
		$master_loc = $_SESSION['master_loc'];
		$user_role = $entry[17];
		$user_id = get_current_user_id();
	
		$wpdb->insert( 'relation_user',
			array(
				'entry_date' => $entry_date,
				'record_type' => 'entry',
				'user_id' => $user->ID,
				'loc_id' => $master_loc,
				'role_id' => $user_role,
				'active' => 1,
				'deleted_user' => NULL,
				'parent_id' => 0,
				'edited_by' => $user_id
			)
		);
	
		$parent_id = $wpdb->insert_id;

		$wpdb->update( 'relation_user',
			array(
				'parent_id' => $parent_id,
			),
			array(
				'id' => $parent_id
			)
		);
	
	endif;
		
}


// REGISTRATION: PLAN DROPDOWN
add_filter( 'gform_pre_render_119', 'populate_plan' );
add_filter( 'gform_admin_pre_render_119', 'populate_plan' );
function populate_plan( $form ) {
	
	global $wpdb;

	$results = $wpdb->get_results( "SELECT id, plan FROM master_plan WHERE active=1 AND plan != 'Organisation' ORDER BY id ASC" );

	foreach( $results as $rows ) :
		$choices[] = array( 'text' => $rows->plan, 'value' => strtolower( $rows->plan ) );
	endforeach;

	foreach( $form['fields'] as &$field ) :
		if( $field['id'] == 2 ) :
			$field['placeholder'] = 'Select Plan';
			$field['choices'] = $choices;
		endif;
	endforeach;

	return $form;
}


// REGISTRATION: COUNTRY DROPDOWN
add_filter( 'gform_pre_render_119', 'populate_plan_country' );
add_filter( 'gform_admin_pre_render_119', 'populate_plan_country' );
function populate_plan_country( $form ) {
	
	global $wpdb;

	$results = $wpdb->get_results( "SELECT country FROM master_country ORDER BY country ASC" );

	foreach( $results as $rows ) :
		$choices[] = array( 'text' => $rows->country, 'value' => $rows->country );
	endforeach;

	foreach( $form['fields'] as &$field ) :
		if( $field['id'] == 19 ) :
			$field['placeholder'] = 'Select Country';
			$field['choices'] = $choices;
		endif;
	endforeach;

	return $form;
}


// SUBSCRIPTION: PLAN DROPDOWN
add_filter( 'gform_pre_render_141', 'populate_subscription_plan' );
add_filter( 'gform_admin_pre_render_141', 'populate_subscription_plan' );
function populate_subscription_plan( $form ) {
	
	global $wpdb;

	$results = $wpdb->get_results( "SELECT id, plan FROM master_plan WHERE active=1 AND plan!='Organisation' ORDER BY id ASC" );

	foreach( $results as $rows ) :
		$choices[] = array( 'text' => $rows->plan, 'value' => $rows->id );
	endforeach;

	foreach( $form['fields'] as &$field ) :
		if( $field['id'] == 2 ) :
			$field['placeholder'] = 'Select Plan';
			$field['choices'] = $choices;
		endif;
	endforeach;

	return $form;
}


// SUBSCRIPTION UPDATE PLAN
add_action( 'gform_after_submission_141', 'update_subscription_licence', 10, 2 );
function update_subscription_licence( $entry, $form ) {
	
  	global $wpdb;
	$master_loc = $_SESSION['master_loc'];
	
	$subscription = $wpdb->get_row( "SELECT * FROM profile_location WHERE master_loc=$master_loc ORDER BY id DESC" );
	
	$entry_date = date( 'Y-m-d H:i:s' );
	$plan_id = $entry[2];
	$street_entry = $subscription->street;
	
	if( empty( $street_entry) ) : $street = NULL; else : $street = $street_entry; endif;
	
	$wpdb->insert( 'profile_location',
		array(
			'entry_date' => $entry_date,
			'record_type' => $subscription->record_type,
			'master_loc' => $subscription->master_loc,
			'plan_id' => $plan_id,
			'licence' => 1,
			'loc_name' => $subscription->loc_name,
			'street' => $street,
			'city' => $subscription->city,
			'county' => $subscription->county,
			'country' => $subscription->country,
		  	'latitude' => $subscription->latitude,
		  	'longitude' => $subscription->longitude,
			'active' => 1,
			'parent_id' => $subscription->parent_id,
			'user_id' => $subscription->user_id
		)
	);
}


// SUBSCRIPTION PLAN AND DISCOUNT && REGISTRATION COUNTRY
add_filter( 'gform_field_value', 'prepopulate_subscription', 10, 3 );
function prepopulate_subscription( $value, $field, $name ) {
	
	/* $plan_id = $_SESSION['plan_id'];
	$loc_country = $_SESSION['loc_country'];
	$session_country = $_SESSION['session_country'];
	$discount = $_SESSION['subscription_discount'];
	$business_month_value = $_SESSION['business_month_value'];
	$business_annual_value = $_SESSION['business_annual_value'];
	$enterprise_month_value = $_SESSION['enterprise_month_value'];
	$enterprise_annual_value = $_SESSION['enterprise_annual_value'];
	$organisation_annual_value = $_SESSION['organisation_annual_value'];
	$subscription_micro_month_value = $_SESSION['subscription_micro_month_value'];
	$subscription_micro_annual_value = $_SESSION['subscription_micro_annual_value'];
	$subscription_coupon = $_SESSION['subscription_coupon']; */
	
    $values = array(
        'plan_id'  => $_SESSION['plan_id'],
		'discount' => $_SESSION['discount'],
		'session_country' => $_SESSION['session_country']
		/* 'loc_country'  => $_SESSION['loc_country'],
		'micro_month_price' => number_format( $subscription_micro_month_value,2 ),
        'micro_annual_price' => number_format( $subscription_micro_annual_value*12,2 ),
        'micro_annual_month_price' => number_format( $subscription_micro_annual_value,2 ),
		'business_month_price' => number_format( $business_month_value,2 ),
        'business_annual_price' => number_format( $business_annual_value*12,2 ),
        'business_annual_month_price' => number_format( $business_annual_value,2 ),
		'enterprise_month_price' => number_format( $enterprise_month_value,2 ),
		'enterprise_annual_price' => number_format( $enterprise_annual_value*12,2 ),
		'enterprise_annual_month_price' => number_format( $enterprise_annual_value,2 ),
		'organisation_annual_price' => number_format( $organisation_annual_value,2 ) */
    );

    return isset( $values[ $name ] ) ? $values[ $name ] : $value;
}
	
/* function populate_subscription_fields() {
	
	$subscription_micro_month_value = $_SESSION['subscription_micro_month_value'];
	$subscription_micro_annual_value = $_SESSION['subscription_micro_annual_value'];
	$business_month_value = $_SESSION['business_month_value'];
	$business_annual_value = $_SESSION['business_annual_value'];
	$enterprise_month_value = $_SESSION['enterprise_month_value'];
	$enterprise_annual_value = $_SESSION['enterprise_annual_value'];
	$organisation_annual_value = $_SESSION['organisation_annual_value'];
	
	$subscription_micro_month_plan_description = 'Annual total £'.number_format( $subscription_micro_month_value*12,2 );
	$subscription_micro_annual_plan_description = 'Annual saving £'.number_format( ($subscription_micro_month_value*12) - ($subscription_micro_annual_value*12),2 );
	$business_month_plan_description = 'Annual total £'.number_format( $business_month_value*12,2 );
	$business_annual_plan_description = 'Annual saving £'.number_format( ($business_month_value*12) - ($business_annual_value*12),2 );
	$enterprise_month_plan_description = 'Annual total £'.number_format( $enterprise_month_value*12,2 );
	$enterprise_annual_plan_description = 'Annual saving £'.number_format( ( $enterprise_month_value*12 ) - ($enterprise_annual_value*12),2 ); ?>

	<script>
		$(document).ready(function(){
			
			var business_annual_value = "<?php echo number_format( $business_annual_value ) ?>";
			var enterprise_annual_value = "<?php echo $enterprise_annual_value ?>";
			var organisation_annual_value = "<?php echo number_format( $organisation_annual_value*12 ) ?>";
			
			var subscription_micro_month_plan_description = "<?php echo $subscription_micro_month_plan_description ?>";
			var subscription_micro_annual_plan_description = "<?php echo $subscription_micro_annual_plan_description ?>";
			var business_month_plan_description = "<?php echo $business_month_plan_description ?>";
			var business_annual_plan_description = "<?php echo $business_annual_plan_description ?>";
			var enterprise_month_plan_description = "<?php echo $enterprise_month_plan_description ?>";
			var enterprise_annual_plan_description = "<?php echo $enterprise_annual_plan_description ?>";
			
			$( '.business-price' ).html( business_annual_value );
			$( '.enterprise-price' ).html( enterprise_annual_value );
			$( '.organisation-price' ).html( organisation_annual_value );
			
			$( '#input_141_35, #input_141_44, #input_141_43' ).append( ' /month' );
			$( '#gfield_description_141_35' ).html( subscription_micro_month_plan_description );
			$( '#gfield_description_141_47' ).html( subscription_micro_annual_plan_description );
			$( '#gfield_description_141_44' ).html( business_month_plan_description );
			$( '#gfield_description_141_48' ).html( business_annual_plan_description );
			$( '#gfield_description_141_43' ).html( enterprise_month_plan_description );
			$( '#gfield_description_141_49' ).html( enterprise_annual_plan_description );
			
			
		});	
	</script> <?php 
} */


// SUBSCRIPTION COUPON - FORM ID == 141
function shortcode_coupon () {
	
	global $wpdb;
	
	$subscription_country = $_SESSION['loc_country'];
	$subscription_conversions = $wpdb->get_row( "SELECT discount, coupon FROM master_country WHERE country='$subscription_country'" );
	$discount = $subscription_conversions->discount;
	$coupon = $subscription_conversions->coupon;
	
	return $subscription_country.' gets <b>'.$discount.'%</b> off.<br />To claim this discount please select <i>have a coupon</i> below and enter <b>'.$coupon.'</b>';
	
}
add_shortcode('shortcode-coupon', 'shortcode_coupon');