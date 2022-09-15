<?php

/* Header

@package	Sustainable Tools
@author		Digital Rockpool
@link		https://www.sustainable.tools/yardstick
@copyright	Copyright (c) 2022, Digital Rockpool LTD
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

	<div class="container-fluid">
		
		<div class="row"><?php 
				
			if( is_page_template( 'templates/charts.php' ) || is_page_template( 'templates/dashboard.php' ) || is_page_template( 'templates/data.php' ) || is_page_template( 'templates/settings.php' ) || is_page_template( 'templates/standard.php' ) || is_page_template( 'templates/stock.php' ) || is_page_template( 'templates/yardstick.php' ) ) :
				
				$plan_attributes = $wpdb->get_row( "SELECT plan, membership_id FROM master_plan WHERE id=$plan_id" );
				$plan = strtolower( $plan_attributes->plan );

				if ( $wp_user_role == 'subscriber' ) : wp_redirect( home_url().'/subscription/'.$plan.'-yearly/?plan='.$plan_id.'&interval=y' ); endif; // THIS IS WHERE PEOPLE ARE REDIRECTED IF NOT SUBSCRIPTION HAS EXPIRED ?>
				
				<nav class="sidebar-nav col-xl-2 overflow-scroll" id="sidebar"><?php
					$args = array(
						'user_role' => $user_role
					);
					get_template_part('/parts/sidebars/sidebar', $post_parent, $args ); ?>
				</nav> <?php

					
			elseif( is_page_template( 'templates/fullscreen.php' ) ) : ?>
					
				<div id="background-fullscreen" class="col-12"> <?php
					
			else : ?>

				<header id="masthead" class="col-12"> 

					<p class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></p> <?php

					$site_description = get_bloginfo( 'description', 'display' );
					if ( $site_description || is_customize_preview() ) : ?>
						<p class="site-description"><?php echo $site_description; /* WPCS: xss ok. */ ?></p> <?php
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

				</header><!-- #masthead --> <?php

			endif; ?>
				
		<div id="content" class="col-xl-10 page-content">
			<aside class="row py-2"> <!-- My Account Bar -->
				<div class="col-2">
					<button id="sidebarCollapse" type="button"><i class="fa fa-bars"></i></button>
				</div>
				
				<div class="col-10 d-flex justify-content-end">
					<div class="dropdown">
						<div class="" type="button" data-bs-toggle="dropdown" aria-expanded="false">
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
			</aside>

			<main id="sticky-footer" class="row"> <?php
		







			







