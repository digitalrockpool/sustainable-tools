<?php

/* Template Name: Fullscreen

Template Post Type: Page

@package	Yardstick
@author		Digital Rockpool
@link		https://yardstick.co.uk
@copyright	Copyright (c) 2019, Digital Rockpool LTD
@license	GPL-2.0+ */

get_header();

/* $plan_url = $_GET['plan']; 
$interval_url = $_GET['interval']; */ ?>
	
<article class="col-8 offset-2 mt-5">
	<section class="p-5 mb-4 clearfix" style="background-color:rgba(255, 255, 255, 0.7);"> 
		<?php 

		if ( have_posts() ) : while ( have_posts() ) : the_post();
			
			the_content();
		
		endwhile; endif; ?>
			
	</section>
</article> <?php

get_footer(); 

/* if( !empty ( $plan_url ) && !empty ( $interval_url ) ) : populate_subscription_fields(); endif; */ ?>
