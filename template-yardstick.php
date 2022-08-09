<?php

/* Template Name: Yardstick

Template Post Type: Post, Page, Help

@package	Yardstick
@author		Digital Rockpool
@link		https://yardstick.co.uk
@copyright	Copyright (c) 2019, Digital Rockpool LTD
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

$page_id = $post->ID;

if( get_field( 'full_width' ) ) :
    $col_article = 'col-xl-12';
else :
    $col_article = 'col-xl-8';
endif;  ?>

<article class="<?php echo $col_article ?> px-3"> 
	<section class="primary-box p-3 pb-4 mb-4 bg-white shadow-sm clearfix"> <?php
		
		if( ( $page_id == 1100 || $page_id == 1505 ) && ( $user_role == 224 || $user_role == 225 ) ) : ?>
		
			<p>You have been assigned the <?php echo strtolower( $user_role_tag ); ?> user role that does not have access to this section. Please contact your adminstrator.</p> <?php
		
		else : ?>
		
			<header class="header-flexbox">
				<h1 class="h4-style"><?php echo get_the_title( $post->post_parent ).' <i class="fal fa-chevron-double-right small"></i> '.get_the_title(); ?></h1> 
			</header> <?php 
		
			if( $wp_user_role == 'not_subscribed' && $page_id == 2060 ) : /* page_id == manage subscription */
		
				echo do_shortcode( "[gravityform id='141' title='false' description='false' ajax='true']" ); ?>
				
				<hr class="clearfix" /> <?php
		
				$membership_attributes = $wpdb->get_row( "SELECT membership_id FROM master_plan WHERE id=$plan_id" );
		
				$membership_db = $membership_attributes->membership_id;
				$membership_url = $_GET['subscription'];

				if( empty( $membership_url ) ) : $membership_id = $membership_db; else : $membership_id = $membership_url; endif;
		
		 		echo do_shortcode( "[mepr-membership-registration-form id='$membership_id']" );
		
			else :

				if( have_posts() ) : while( have_posts() ) : the_post();
					the_content(); 

					if ( is_singular( 'help' ) ) : ?>
						<form method="post"><input type="button" value="Back to Previous Page" OnClick="history.go( -1 );return true;"></form> <?php
					endif;

				endwhile;
				endif;
			endif;
		
		endif; ?>
			
	</section>
	
</article> <?php

if( get_field( 'full_width' ) ) : // then do nothing
else : ?>
	<aside class="col-xl-4 pr-3"> <?php
		
		if( $page_id == 805 ) : ?>
		
			<section class="dark-box p-3 pb-4 mb-4 bg-white shadow-sm clearfix"> 

				<h2 class="h4-style">Alerts</h2> <?php
				
				$member_subscribed = $wpdb->get_row( "SELECT status FROM yard_mailster_subscribers WHERE wp_id=$user_id" );
				$subscribe_status = $member_subscribed->status;
				
				if( $subscribe_status == 1 ) : ?>
				
					<p>You are currently recieving alerts.</p> <?php
				
					$url = $_SERVER['REQUEST_URI'];
				
					if( strpos( $url, 'unsubscribe' ) == false ) : ?>
				
					<a href="<?php echo $site_url ?>/account/my-profile/unsubscribe" class="btn btn-dark">Stop alerts</a> <?php
					   
					 endif;
				
				else :  ?>
				
					<p>You are currently not receiving alerts.</p> <?php
				
					echo mailster_form( 1 );
				
				endif;

				echo do_shortcode( "[newsletter_signup]Signup for the newsletter[newsletter_signup_form id=1][/newsletter_signup]");

				echo do_shortcode( "[newsletter_confirm]Thanks for your interest![/newsletter_confirm]");

				echo do_shortcode( "[newsletter_unsubscribe]Do you really want to unsubscribe?[/newsletter_unsubscribe]"); ?>
				
			</section>
		
			<section class="secondary-box p-3 pb-4 mb-4 bg-white shadow-sm clearfix"> 

				<h2 class="h4-style delete-account">Delete account</h2> <?php
				
				if( $wp_user_role == 'has_account' ) : ?>
				
					<p>Please cancel your subscription before deleting your account.</p>
				
					<a href="<?php echo $site_url ?>/account/manage-subscription/?action=subscriptions" class="btn btn-secondary">Manage Subscription</a><?php
				
				else : ?>
				
					<p>By deleting your account your profile information will be removed and your data anonymised.</p> <?php
				
					echo do_shortcode( '[plugin_delete_me /]' );
				
				endif; ?>
				
			</section> <?php
				
		endif;

		if( $page_id == 1100 && ( $user_role == 222 || $user_role == 223 ) ) : property_profile_edit(); endif; /* property profile && super admin || admin */
		if( $page_id == 1505 && ( $user_role == 222 || $user_role == 223 ) && $plan_id == 4 ) : team_member_edit(); endif;  /* team member && super admin || admin && enterprise */ ?>

	</aside> <?php
endif;

get_footer(); ?>