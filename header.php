<?php

/* Header

@package	Yardstick
@author		Digital Rockpool
@link		https://yardstick.co.uk
@copyright	Copyright (c) 2018, Digital Rockpool LTD
@license	GPL-2.0+ */

?>

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
	
	$master_loc = $_SESSION['master_loc'];
	
	$user_id = get_current_user_id();
	$user_role = $_SESSION['user_role'];
	$user_role_tag = $_SESSION['user_role_tag'];
	$wp_user_role = $_SESSION['wp_user_role'];
	
	$plan_id = $_SESSION['plan_id'];
	$standards_assigned = $_SESSION['standards_assigned']; ?>
	
	<style> <?php
		
	if( $user_role == 225 ) : /* subscriber | contributor */ ?>
		
		#mega-menu-item-569 { display: none!important; } /* data */
		#mega-menu-item-1483 { display: none!important; } /* settings */
		#mega-menu-item-1638 { display: none!important; } /* standards */
		#mega-menu-item-1779 { display: none!important; } /* property profile */
		#mega-menu-item-1710 { display: none!important; } /* my account: manage teams */ <?php 
		
	endif;
		
	if( $user_role == 224 ) : /* editor */ ?>
		
		#mega-menu-item-1483 { display: none!important; } /* settings */
		#mega-menu-item-1779 { display: none!important; } /* property profile */
		#mega-menu-item-1710 { display: none!important; } /* my account: manage teams */ <?php 
		
	endif;
		
	if( $plan_id == 2 ) : ?>
		
		#mega-menu-item-1678 { display: none!important; } /* settings: categories and tags */
		#mega-menu-item-1595 { display: none!important; } /* settings: measures */ <?php
	
	endif;
		
	if( $plan_id != 4 ) : /* plan: enterprise */ ?>
		
		#mega-menu-item-1710 { display: none!important; } /* my account: manage teams */ <?php
	
	endif;
		
	if( $plan_id != 5 ) : /* plan: organisation */ ?>
		
		#mega-menu-item-2063 { display: none!important; } /* my account: manage members */ <?php
	
	endif;
		
	$employee_types = $wpdb->get_results( "SELECT id FROM master_tag WHERE cat_id=6 AND id NOT IN (SELECT tag_id FROM custom_tag WHERE cat_id=6 AND active=1 AND loc_id=$master_loc)");
	
	foreach( $employee_types as $employee_type ) :
		
		$employee_id = $employee_type->id;
		
		if( $employee_id == 69 ) : ?> #mega-menu-item-1662 { display: none!important; } /* permanent */ <?php endif;
		if( $employee_id == 71 ) : ?> #mega-menu-item-1663 { display: none!important; } /* seasonal */ <?php endif;
		if( $employee_id == 70 ) : ?> #mega-menu-item-1664 { display: none!important; } /* fixed term */ <?php endif;
		if( $employee_id == 228 ) : ?> #mega-menu-item-1665 { display: none!important; } /* contract */ <?php endif;
		if( $employee_id == 72 ) : ?> #mega-menu-item-1666 { display: none!important; } /* casual */ <?php endif;
		if( $employee_id == 73 ) : ?> #mega-menu-item-1667 { display: none!important; } /* intern */ <?php endif;
		
	endforeach;
		
	$donation_types = $wpdb->get_results( "SELECT id FROM master_tag WHERE cat_id=4 AND id NOT IN (SELECT tag_id FROM custom_tag WHERE cat_id=4 AND active=1 AND loc_id=$master_loc AND id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id))");
	
	foreach( $donation_types as $donation_type ) :
		
		$donation_id = $donation_type->id;
		
		if( $donation_id == 64 ) : ?> #mega-menu-item-1658 { display: none!important; } /* company donation */ <?php endif;
		if( $donation_id == 65 ) : ?> #mega-menu-item-1659 { display: none!important; } /* facilitated donation */ <?php endif;
		if( $donation_id == 66 ) : ?> #mega-menu-item-1660 { display: none!important; } /* staff time donation */ <?php endif;
		
	endforeach;
		
	if( $standards_assigned <= 0 ) : ?>
		
		#mega-menu-item-1638 { display: none!important; } /* standards */ <?php
	
	endif; ?>

</style> 
</head> <?php
	
$hero = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'full' ); ?>

<body <?php body_class(); ?> style="background-image: url('<?php echo $hero['0'] ?>');">
	
<div class="row no-gutters">
<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'yardstick' ); ?></a> <?php 
	
$site_url = get_site_url();
	
if( is_page_template( 'template-charts.php' ) || is_page_template( 'template-dashboard.php' ) || is_page_template( 'template-data.php' ) || is_page_template( 'template-settings.php' ) || is_page_template( 'template-standard.php' ) || is_page_template( 'template-yardstick.php' ) ) :
	
	$registration_date = $_SESSION['registration_date'];

	$plan_attributes = $wpdb->get_row( "SELECT plan, membership_id FROM master_plan WHERE id=$plan_id" );
	$plan = strtolower( $plan_attributes->plan );

	if ( $wp_user_role == 'not_subscribed' && strtotime( $registration_date ) < strtotime( '-15 days' ) ) : wp_redirect( home_url().'/subscription/'.$plan.'-yearly/?plan='.$plan_id.'&interval=y' ); endif; ?>
	
	<header id="masthead" class="col-xl-2 site-header">
		<p class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></p> <?php

		$yardstick_description = get_bloginfo( 'description', 'display' );
		if ( $yardstick_description || is_customize_preview() ) : ?>
			<p class="site-description"><?php echo $yardstick_description; /* WPCS: xss ok. */ ?></p> <?php
		endif; ?>

		<nav id="site-navigation" class="main-navigation"> <?php
			wp_nav_menu( array(
				'theme_location' => 'menu-2',
				'menu_id'        => 'secondary-menu',
			) ); ?>
		</nav><!-- #logged in site-navigation -->

	</header><!-- #masthead -->
	
	<div class="col-xl-10">

		<header id="admin-header">
			<a href="<?php echo wp_logout_url('sign-in') ?>">Logout <i class="far fa-sign-out"></i></a>
		</header> <?php
		
elseif( is_page_template( 'template-fullscreen.php' ) ) : ?>
		
	<div id="background-fullscreen" class="col-12"> <?php
		
else : ?>

		<header id="masthead" class="col-12"> 

			<p class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></p> <?php

			$yardstick_description = get_bloginfo( 'description', 'display' );
			if ( $yardstick_description || is_customize_preview() ) : ?>
				<p class="site-description"><?php echo $yardstick_description; /* WPCS: xss ok. */ ?></p> <?php
			endif; ?>

			<nav id="site-navigation" class="main-navigation<?php if( empty( $hero ) ) : echo ' remove-nav-transparency '; endif; ?>"> <?php
				wp_nav_menu( array(
					'theme_location' => 'menu-1',
					'menu_id'        => 'primary-menu',
				) ); ?>
			</nav><!-- #site-navigation -->
			
			<section class="hero-panel <?php if( !empty( $hero ) ) : echo 'hero-panel-image'; endif; ?> row justify-content-md-center no-gutters">
				<div class="col-9"><?php 
					
					$hero_subtitle = get_field('hero_subtitle'); 
					$hero_link = get_field('hero_link'); ?>

					<h1><?php the_title(); ?></h1> <?php
					if( !empty( $hero_subtitle ) ) : echo '<h3>'.$hero_subtitle.'</h3>'; endif;
					
					if( $hero_link ): 
					
						$hero_link_url = $hero_link['url'];
						$hero_link_title = $hero_link['title'];
						$hero_link_target = $hero_link['target'] ? $hero_link['target'] : '_self'; ?>
						
						<a class="btn btn-primary float-none" href="<?php echo esc_url( $hero_link_url ); ?>" target="<?php echo esc_attr( $hero_link_target ); ?>"><?php echo esc_html( $hero_link_title ); ?></a> <?php 
					endif; ?>
				</div>
			</section> 

		</header><!-- #masthead --> 
	</div> <?php


endif; ?>
		
<main id="sticky-footer" class="row no-gutters">
	