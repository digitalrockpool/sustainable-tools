<?php

/* Footer

@package	Sustainable Tools
@author		Digital Rockpool
@link		https://www.sustainable.tools/yardstick
@copyright	Copyright (c) 2022, Digital Rockpool LTD
@license	GPL-2.0+ */

?>

</main><!-- #sticky-footer --> <?php

if( is_page_template( 'templates/fullscreen.php' ) ) : ?>
		
	</div><!-- #background-fullscreen --> <?php

else : ?>

</div><!-- #content -->
</div><!-- .row --> 

<footer id="colophon" class="site-footer row">

	<section class="site-info col-sm-6 pl-5">
			Copyright &copy; <?php echo date('Y'); ?> Digital Rockpool LTD
		</section>

		<section class="legal-info col-sm-6 pr-5 align-self-end"> <?php
			wp_nav_menu( array(
				'theme_location' => 'menu-3',
				'menu_id'        => 'legal-menu',
			) ); ?>
		</section><!-- .site-info -->
	</footer><!-- #colophon --> <?php
	
endif;
	
if( is_page_template( 'templates/charts.php' ) || is_page_template( 'templates/dashboard.php' ) || is_page_template( 'templates/data.php' ) || is_page_template( 'templates/settings.php' ) || is_page_template( 'templates/standard.php' ) || is_page_template( 'templates/yardstick.php' ) ) : ?>

	</div><!-- .container --> <?php 

endif;

wp_footer(); ?>

</body>
</html>