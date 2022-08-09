<?php

/* Page

@package	Yardstick
@author		Digital Rockpool
@link		https://yardstick.co.uk
@copyright	Copyright (c) 2020, Digital Rockpool LTD
@license	GPL-2.0+ */

get_header(); ?>

<article class="col-xl-12"> <?php

	if( have_posts() ) : while( have_posts() ) : the_post();
		the_content();
	endwhile; endif;  ?>
		
</article> <?php

get_footer(); 

// if( is_page ( 1497 ) ) : populate_subscription_fields(); endif; ?>