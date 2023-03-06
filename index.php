<?php

/* Index

@package	Sustainable Tools
@author		Digital Rockpool
@link		https://www.sustainable.tools/yardstick
@copyright	Copyright (c) 2022, Digital Rockpool LTD
@license	GPL-2.0+ */

if ( ! defined( 'ABSPATH' ) ) :
	exit; // Exit if accessed directly.
endif;

get_header(); ?>

<article class="col-xl-12"><?php

	if( have_posts() ) : while( have_posts() ) : the_post();
		the_content();
	endwhile; endif;  ?>
		
</article> <?php

get_footer(); 