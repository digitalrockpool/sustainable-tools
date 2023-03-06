<?php

/* ***

Template Part:  Site - Header - Tool

@package	      Sustainable Tools
@author		      Digital Rockpool
@link		        https://www.sustainable.tools/
@copyright	    Copyright (c) 2022, Digital Rockpool LTD
@license	      GPL-2.0+ 

*** */ ?>

<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">

	<?php wp_head();
	
	global $wpdb;
	global $post;

	$site_url = get_site_url();
	$slug = $post->post_name;
	$post_parent = strtolower( get_the_title( $post->post_parent ));
	
	$master_loc = $_SESSION['master_loc'];
	$loc_name = $_SESSION['loc_name'];
	$plan_id = $_SESSION['plan_id'];
	
	$user_id = get_current_user_id();
	$user_role = $_SESSION['user_role'];
	$user_role_tag = $_SESSION['user_role_tag'];

	$user_meta = get_userdata( $user_id );
	$wp_user_roles = $user_meta->roles;
	$wp_user_role = $wp_user_roles[0];
	$wp_user_name = $user_meta->display_name; ?>
	
	<style> <?php
		
		if( $user_role == 225 ) : /* subscriber */ ?>
			#menu-item-2697 { display: none!important; } /* my account: property profile */
			#menu-item-2698 { display: none!important; } /* my account: add team member */
			#menu-item-2699 { display: none!important; } /* my account: manage members */
			#menu-item-2700 { display: none!important; } /* my account: manage subscription */
			#menu-item-2701 { display: none!important; } /* my account: contact support */ <?php 
		endif;
			
		if( $user_role == 224 ) : /* editor */ ?>
			#menu-item-2697 { display: none!important; } /* my account: property profile */
			#menu-item-2698 { display: none!important; } /* my account: add team member */
			#menu-item-2699 { display: none!important; } /* my account: manage members */
			#menu-item-2700 { display: none!important; } /* my account: manage subscription */ <?php 
		endif;
		
		if( $user_role == 223 ) : /* admin */ ?>
			#menu-item-2700 { display: none!important; } /* my account: manage subscription */ <?php 
		endif;
			
		if( $plan_id != 4 && $wp_user_role != 'administrator' ) : /* plan: enterprise */ ?>
			#menu-item-2698 { display: none!important; } /* my account: add team member */ <?php
		endif;
			
		if( $plan_id != 5 && $wp_user_role != 'administrator' ) : /* plan: organisation */ ?>
			#menu-item-2699 { display: none!important; } /* my account: manage members */ <?php
		endif; ?>

	</style> 
</head>

<body <?php body_class(); ?>>

	<div class="container-fluid"><!-- start div container -->

		<main id="sticky-footer" class="row"><!-- start main row --><?php 

			$plan_attributes = $wpdb->get_row( "SELECT plan, membership_id FROM master_plan WHERE id=$plan_id" );
			$plan = strtolower( $plan_attributes->plan );

			if ( $wp_user_role == 'subscriber' && $user_role == 222 ) :/* super_admin */ 
				wp_redirect( home_url().'/account/manage-subscription/' ); endif; // NEED TO WORK OUT HOW THEY CAN UP THIER USER ROLE AGAIN BY PAYING ON THIS PAGE. NEED TO DO SOMETHING WITH NON SUPER ADMINS LIKE MESSAGE SAYING YOUR ACCOUNT NEEDS PAYMENT PLEASE CONTACT YOUR SUPER ADMIN ?>
			
			<nav class="sidebar-nav col-xl-2 overflow-scroll" id="sidebar"><?php
				$args = array(
					'user_role' => $user_role
				);
				get_template_part('/parts/sidebars/sidebar', $post_parent, $args ); ?>
			</nav> 
				
			<div id="content" class="col-xl-10 page-content"><!-- start div col -->
				<aside id="my-account" class="row align-items-center py-2"><!-- start aside row -->
					<div class="col-2">
						<button id="sidebarCollapse" type="button"><i class="fa fa-bars"></i></button>
					</div>
					
					<div class="col-10 d-flex justify-content-end">
						<div class="dropdown">
							<div class="user-info" type="button" data-bs-toggle="dropdown" aria-expanded="false">
								<p class="user-text"><?php echo $loc_name.'<br />'.$wp_user_name ?></p>
								<div class="user-circle"><i class="fa-solid fa-user"></i></div>
							</div>
							<nav class="dropdown-menu"><?php
								wp_nav_menu( array(
									'theme_location' => 'my-account'
								) ); ?>
							</nav>
						</div>
					</div>
				</aside><!-- end aside row -->
				
				<article class="row g-3"><!-- start article row --> <?php