<?php

/* ***

Header

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
</head> <?php
	
$hero = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'full' );
if( $hero ) : $hero_image = $hero['0']; else : $hero_image = ''; endif; ?>

<body <?php body_class(); ?> style="background-image: url('<?php echo $hero_image ?>');">

	<div class="container-fluid"><!-- start div container -->