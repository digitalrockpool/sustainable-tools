<?php 
/* ***

Includes:  User Sessions

@package	      Sustainable Tools
@author		      Digital Rockpool
@link		        https://www.sustainable.tools/
@copyright	    Copyright (c) 2022, Digital Rockpool LTD
@license	      GPL-2.0+ 

*** */

if ( is_user_logged_in() ) :

	session_start();

	global $wpdb;

	$user_id = get_current_user_id();

	$master_locs = $wpdb->get_row( "SELECT master_loc FROM profile_location INNER JOIN relation_user ON relation_user.loc_id=profile_location.id WHERE relation_user.user_id=$user_id" );
	$_SESSION['master_loc'] = $master_locs->master_loc;
	$master_loc = $_SESSION['master_loc'];

	$user_role = $wpdb->get_row( "SELECT role_id, tag FROM relation_user INNER JOIN master_tag ON relation_user.role_id=master_tag.id WHERE user_id=$user_id AND active=1 ORDER BY relation_user.id DESC" ) ;
	$_SESSION['user_role'] = $user_role->role_id;
	$_SESSION['user_role_tag'] = $user_role->tag;

	$plan = $wpdb->get_row( "SELECT plan_id, licence, loc_name, city, county, profile_location.country, discount FROM profile_location INNER JOIN master_country ON profile_location.country=master_country.country WHERE master_loc=$master_loc ORDER BY profile_location.id DESC" );
	$_SESSION['plan_id'] = $plan->plan_id;
	$_SESSION['licence'] = $plan->licence;
	$_SESSION['loc_name'] = $plan->loc_name;
	$_SESSION['loc_city'] = $plan->city;
	$_SESSION['loc_county'] = $plan->county;
	$_SESSION['loc_country'] = $plan->country;
	$_SESSION['discount'] = $plan->discount;

	$registration_dates = $wpdb->get_row( "SELECT profile_location.entry_date FROM profile_location INNER JOIN relation_user ON relation_user.loc_id=profile_location.id WHERE relation_user.user_id=$user_id" );
	$_SESSION['registration_date'] = $registration_dates->entry_date;

	/* $micro_value = $wpdb->get_row( "SELECT monthly_gbp, annual_gbp FROM master_plan WHERE id=2" );
	$business_value = $wpdb->get_row( "SELECT monthly_gbp, annual_gbp FROM master_plan WHERE id=3" );
	$enterprise_value = $wpdb->get_row( "SELECT monthly_gbp, annual_gbp FROM master_plan WHERE id=4" );
	$organisation_value = $wpdb->get_row( "SELECT annual_gbp FROM master_plan WHERE id=5" );

	$_SESSION['micro_month_value'] = $micro_value->monthly_gbp;
	$_SESSION['micro_annual_value'] = $micro_value->annual_gbp;
	/* $_SESSION['business_month_value'] = $business_value->monthly_gbp;
	$_SESSION['business_annual_value'] = $business_value->annual_gbp;
	$_SESSION['enterprise_month_value'] = $enterprise_value->monthly_gbp;
	$_SESSION['enterprise_annual_value'] = $enterprise_value->annual_gbp;
	$_SESSION['organisation_annual_value'] = $organisation_value->annual_gbp;

	$GBPUSD_exchange = $wpdb->get_row( "SELECT usd_exchange FROM master_country WHERE id=77" );
	$_SESSION['GBPUSD'] = $GBPUSD_exchange->usd_exchange;
	$GBPUSD = $_SESSION['GBPUSD'];

	$subscription_country = $_SESSION['loc_country'];
    $subscription_conversions = $wpdb->get_row( "SELECT discount, coupon FROM master_country WHERE country='$subscription_country'" );
	$_SESSION['subscription_discount'] = $subscription_conversions->discount;
	$_SESSION['subscription_coupon'] = $subscription_conversions->coupon;

	$micro_month_GBP = $_SESSION['micro_month_value'];
	$micro_annual_GBP = $_SESSION['micro_annual_value'];
	$micro_month = ($micro_month_GBP) / 100 * (100 - $discount);
	$micro_month_cap = $micro_month=( $micro_month <= $micro_month_GBP )?$micro_month:$micro_month_GBP;
	$micro_annual = ($micro_annual_GBP) / 100 * (100 - $discount);
	$micro_annual_cap = $micro_annual=( $micro_annual <= $micro_annual_GBP )?$micro_annual:$micro_annual_GBP;
	$_SESSION['subscription_micro_month_value'] = $micro_month_cap;
	$_SESSION['subscription_micro_annual_value'] = $micro_annual_cap; */

	$standards_assigned = $wpdb->get_var( "SELECT COUNT(DISTINCT parent_id) FROM relation_standard WHERE user_id=$user_id AND active=1" );
	$_SESSION['standards_assigned'] = $standards_assigned;

	$loc_preferences = $wpdb->get_row( "SELECT profile_locationmeta.id, industry, master_tag.tag AS industry_tag, sector, sector.tag AS sector_tag, subsector, subsector.tag AS subsector_tag, other, fy_day, fy_month, currency, geo_type, very_local, local, calendar, calendar.tag AS calendar_tag, parent_id FROM profile_locationmeta LEFT JOIN master_tag ON profile_locationmeta.industry=master_tag.id LEFT JOIN master_tag sector ON profile_locationmeta.sector=sector.id LEFT JOIN master_tag subsector ON profile_locationmeta.subsector=subsector.id LEFT JOIN master_tag calendar ON profile_locationmeta.calendar=calendar.id WHERE profile_locationmeta.id IN (SELECT MAX(id) FROM profile_locationmeta WHERE loc_id=$master_loc)" );

	$_SESSION['report_active'] = $loc_preferences->parent_id; /* used to identify first report settings submission */
	$_SESSION['report_initiate_id'] = $loc_preferences->id; /* used to update first report settings submission */
	$_SESSION['industry_id'] = $loc_preferences->industry;
	$_SESSION['sector_id'] = $loc_preferences->sector;
	$_SESSION['subsector_id'] = $loc_preferences->subsector;
	$_SESSION['industry'] = $loc_preferences->industry_tag;
	$_SESSION['sector'] = $loc_preferences->sector_tag;
	$_SESSION['subsector'] = $loc_preferences->subsector_tag;
	$_SESSION['other'] = $loc_preferences->other;
	$_SESSION['fy_day'] = $loc_preferences->fy_day;
	$_SESSION['fy_month'] = $loc_preferences->fy_month;
	$_SESSION['currency'] = $loc_preferences->currency;
	$_SESSION['geo_type'] = $loc_preferences->geo_type;

	if( $_SESSION['geo_type'] == 143  ) :

		$_SESSION['very_local'] = $loc_preferences->very_local;
		$_SESSION['local'] = $loc_preferences->local;

	else :

		$_SESSION['very_local'] = $plan->city;
		$_SESSION['local'] = $plan->county;

	endif;

	$_SESSION['calendar'] = $loc_preferences->calendar_tag;
	$_SESSION['calendar_id'] = $loc_preferences->calendar;

	$measure_toggle = $wpdb->get_row( "SELECT master_tag.tag, tag_id FROM custom_tag INNER JOIN master_tag ON custom_tag.tag_id=master_tag.id WHERE loc_id=$master_loc AND custom_tag.cat_id=13 AND custom_tag.id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id)" );
	$_SESSION['measure_toggle'] = $measure_toggle->tag_id;
	$_SESSION['measure_toggle_name'] = $measure_toggle->tag;

	$tag_toggle = $wpdb->get_row( "SELECT active FROM custom_tag WHERE loc_id=$master_loc AND cat_id=22 AND tag IS NULL AND custom_tag.id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id)" );
	$_SESSION['tag_toggle'] = $tag_toggle->active;

	$measure_count = $wpdb->get_var( "SELECT COUNT(id) FROM data_measure WHERE active=1 AND loc_id=$master_loc" );
	$_SESSION['measure_count'] = $measure_count;

else :

	$_SESSION['fy_day'] = 1;
	$_SESSION['fy_month'] = 1;

endif;