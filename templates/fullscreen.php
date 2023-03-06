<?php

/* Template Name: Fullscreen

Template Post Type: Page

@package	Sustainable Tools
@author		Digital Rockpool
@link		https://www.sustainable.tools/yardstick
@copyright	Copyright (c) 2022, Digital Rockpool LTD
@license	GPL-2.0+ */

get_header(); ?>

<aside class="col-lg-4 py-5 px-4" style="background-color: #263238">
	<img src="<?php echo get_template_directory_uri(); ?>/lib/img/logo-yardstick-light.png" alt="Yardstick">
	<p class="text-white py-5">All ready have account?<br /><a href="http://sustainable-tools.sandbox/sign-in/">Sign In</a></p>
</aside>

<article class="col-lg-6 py-5 px-4"><?php 
	if ( have_posts() ) : while ( have_posts() ) : the_post();
				
		the_content();
			
	endwhile; endif; ?>
</article><?php

get_footer(); 
