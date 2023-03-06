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

	<?php wp_head(); ?>
	
</head> <?php

if( is_page_template( 'templates/fullscreen.php' ) ) : ?>
	
	<body <?php body_class(); ?> style="background-image: url('<?php echo $hero_image ?>');"><?php

else :

	$hero = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'full' );
	if( $hero ) : $hero_image = $hero['0']; else : $hero_image = ''; endif; ?>
	
	<body <?php body_class(); ?> style="background-image: url('<?php echo $hero_image ?>');">

	<div class="container-fluid p-0"><!-- start div container -->

		<header class="row g-0"> 

			<div class="site-title col-xl-4"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></div>

			<nav id="site-navigation" class="col-xl-8 main-navigation<?php if( empty( $hero ) ) : echo ' remove-nav-transparency '; endif; ?>"> <?php
				wp_nav_menu( array(
					'theme_location'	=> 'menu-1',
					'menu_id'        	=> 'primary-menu',

				) ); ?>
			</nav><!-- #site-navigation -->
					
			<section class="hero-panel <?php if( !empty( $hero ) ) : echo 'hero-panel-image'; endif; ?> row g-0 justify-content-md-center">
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

		</header><?php
endif; ?>

		<main id="sticky-footer" class="row g-0">