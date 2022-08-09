<?php ob_start();

/* Template Name: Dashboard

Template Post Type: Page

@package	Yardstick
@author		Digital Rockpool
@link		https://yardstick.co.uk
@copyright	Copyright (c) 2018, Digital Rockpool LTD
@license	GPL-2.0+ */

get_header();

global $wpdb;
global $post;

$site_url = get_site_url().'/yardstick';
$slug = $post->post_name;
	
$master_loc = $_SESSION['master_loc'];
	
$user_id = get_current_user_id();
$user_role = $_SESSION['user_role'];
$user_role_tag = $_SESSION['user_role_tag'];
$wp_user_role = $_SESSION['wp_user_role'];

$plan_id = $_SESSION['plan_id'];

$report_active = $_SESSION['report_active'];
$measure_toggle = $_SESSION['measure_toggle'];

$setup_checks = $wpdb->get_results( "SELECT cat_id FROM custom_tag WHERE loc_id=$master_loc" );
foreach( $setup_checks as $setup_check ) :
	$setup_check_array[] = $setup_check->cat_id;
endforeach;
$profile_setup_checks = $wpdb->get_results( "SELECT county FROM profile_location WHERE county <> '' AND master_loc=$master_loc" ); 
$location_setup_checks = $wpdb->get_results( "SELECT id FROM custom_location WHERE loc_id=$master_loc" );
$categories_setup_checks = $wpdb->get_results( "SELECT id FROM custom_category WHERE loc_id=$master_loc" );
$tags_checks = $wpdb->get_results( "SELECT cat_id FROM custom_tag WHERE cat_id=22 AND tag IS NOT NULL AND loc_id=$master_loc" ); ?>
	
<article class="col-xl-8 px-3"> 
	<section class="primary-box p-3 pb-4 mb-4 bg-white shadow-sm clearfix">
		
		<header class="header-flexbox">
			<h1 class="h4-style">Getting Started</h1>
		</header> 
						
		<p>Use the links below to customise the tool before you start to collect data. It may look a lot but you will only need to do this once.</p> <?php 
	
		if ( empty( $profile_setup_checks ) ) : ?>

			<h5>Property Profile</h5>
			<ul class="plus-circle">
		
				<li><a href="<?php echo $site_url; ?>/account/property-profile" title="my account - property profile">Complete your property profile location</a></li>
			
			</ul> <?php
		
		endif;
		
		if ( $report_active == 0 ) : ?>

			<h5>Reporting</h5>
			<ul class="plus-circle">
		
				<li><a href="<?php echo $site_url; ?>/settings/?setting=report_settings" title="setting - report settings">Set your reporting data range, local currency, geographical boundaries and industry</a></li>
			
			</ul> <?php
		
		endif;
		
		if ( empty( $tags_setup_checks ) ) : // NEED TO HIDE TAGS WHEN ADDED ?>

			<h5>Categories &amp; Tags</h5>
			<ul class="plus-circle"> <?php
		
				 if( empty( $categories_setup_checks ) ) : ?><li><a href="<?php echo $site_url; ?>/settings/?setting=categories" title="setting - categories">Add categories to organise your tags</a></li><?php endif;
				 if( empty( $tags_setup_checks ) ) : ?><li><a href="<?php echo $site_url; ?>/settings/?setting=tags" title="setting - tags">Add tags to attach additional information to your data</a></li><?php endif; ?>
			
			</ul> <?php
		
		endif;
		
		if ( !in_array( 32, $setup_check_array ) && !in_array( 13, $setup_check_array ) ) : /* cat_id = measures, measure type */ ?>

			<h5>Measures</h5>
			<ul class="plus-circle"> <?php 
				
				if ( !in_array( 32, $setup_check_array ) ) : ?> <li><a href="<?php echo $site_url; ?>/settings/?setting=measures" title="setting - measures">Change the frequency you want to collect data</a></li> <?php endif;
				if ( !in_array( 13, $setup_check_array ) ) : ?> <li><a href="<?php echo $site_url; ?>/settings/?setting=measures" title="setting - measures">Give your custom time periods names</a></li> <?php endif; ?>
			
			</ul> <?php
		
		endif;
		
		if ( empty( $location_setup_checks ) ) : ?>

			<h5>Locations</h4>
			<ul class="plus-circle">
		
				<li><a href="<?php echo $site_url; ?>/settings/?setting=locations" title="setting - locations">Add the hometown of your employees, the source of your supplies and the location your donations support</a></li>
			
			</ul> <?php
		
		endif;
		
		$operations_cat_id_array = array( 42, 15, 18, 19, 16, 40 ); /* cat_id = operation setting, fuel, water, waste, disposal, plastic */
		
		if ( count( array_intersect( $operations_cat_id_array, $setup_check_array ) ) >= 0 && count( array_intersect( $operations_cat_id_array, $setup_check_array ) ) < 6 ) : ?>
			
			<h5>Operations</h5>
			<ul class="plus-circle"> <?php
					
				if ( !in_array( 42, $setup_check_array ) ) : ?> <li><a href="<?php echo $site_url; ?>/settings/?setting=operation_settings" title="setting - operation settings">Set the days your business is open, the total area of your property and the operating capacity</a></li> <?php endif;
				if ( !in_array( 15, $setup_check_array ) ) : ?> <li><a href="<?php echo $site_url; ?>/settings/?setting=fuels" title="setting - fuel">Add the fuels you use and units you measure in</a></li> <?php endif;
				if ( !in_array( 18, $setup_check_array ) ) : ?> <li><a href="<?php echo $site_url; ?>/settings/?setting=water" title="setting - water">Add the  types of water you consume and units you measure in</a></li> <?php endif;
				if ( !in_array( 19, $setup_check_array ) ) : ?> <li><a href="<?php echo $site_url; ?>/settings/?setting=waste" title="setting - waste">Add the types of waste you generate and units you measure in</a></li> <?php endif;
				if ( !in_array( 16, $setup_check_array ) ) : ?> <li><a href="<?php echo $site_url; ?>/settings/?setting=disposal_methods" title="setting - waste disposal">Add the methods you use to dispose of your waste</a></li> <?php endif;
				if ( !in_array( 40, $setup_check_array ) ) : ?> <li><a href="<?php echo $site_url; ?>/settings/?setting=plastics" title="setting - plastic">Add the types of plastics you use, a description and the units and quanity you purchase in</a></li> <?php endif; ?>
					
			</ul> <?php
			
		endif;
		
		if ( !in_array( 4, $setup_check_array ) ) : /* cat_id = donation type */ ?>

			<h5>Charity</h5>
		
			<ul class="plus-circle">
		
				<li><a href="<?php echo $site_url; ?>/settings/?setting=donation_types" title="setting - donation types">Add the types of donations you make</a></li>
			
			</ul> <?php
		
		endif;
		
		$labour_cat_id_array = array( 43, 6, 20, 21 ); /* cat_id = labour settings, employee types, ethnicity, role */
		
		if ( count( array_intersect( $labour_cat_id_array, $setup_check_array ) ) >= 0 && count( array_intersect( $labour_cat_id_array, $setup_check_array ) ) < 4 ) : ?>
			
			<h5>Labour</h5>

			<ul class="plus-circle"> <?php
					
				if ( !in_array( 43, $setup_check_array ) ) : ?> <li><a href="<?php echo $site_url; ?>/settings/?setting=labour_settings" title="setting - labour settings">Set the standard days worked per week, weeks per year and annual leave</a></li> <?php endif;
				if ( !in_array( 6, $setup_check_array ) ) : ?> <li><a href="<?php echo $site_url; ?>/settings/?setting=employee_types" title="setting - employee types">Add the different employee types contracted by your business</a></li> <?php endif;
				if ( !in_array( 20, $setup_check_array ) ) : ?> <li><a href="<?php echo $site_url; ?>/settings/?setting=ethnicities" title="setting - ethnicities">Add the different ethnicities of your employees</a></li> <?php endif;
				if ( !in_array( 21, $setup_check_array ) ) : ?> <li><a href="<?php echo $site_url; ?>/settings/?setting=roles" title="setting - roles">Add the job roles within your business</a></li> <?php endif; ?>
					
			</ul> <?php
			
		endif; ?>
			
	</section>
</article> <?php

get_footer();