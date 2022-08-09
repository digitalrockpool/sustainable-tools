<?php

/* Footer

@package	Yardstick
@author		Digital Rockpool
@link		https://logstock.co.uk
@copyright	Copyright (c) 2018, Digital Rockpool LTD
@license	GPL-2.0+ */

?>

</main><!-- #sticky-footer --> <?php

if( is_page_template( 'template-fullscreen.php' ) ) : ?>
		
	</div><!-- #background-fullscreen --> <?php

else : ?>

	<footer id="colophon" class="site-footer row no-gutters clearfix">

		<section class="site-info col-sm-6 pl-5">
			Copyright &copy; <?php echo date('Y'); ?> Digital Rockpool LTD
		</section>

		<section class="legal-info col-sm-6 pr-5 text-right"> <?php
			wp_nav_menu( array(
				'theme_location' => 'menu-3',
				'menu_id'        => 'legal-menu',
			) ); ?>
		</section><!-- .site-info -->
	</footer><!-- #colophon --> <?php
	
endif;
	
if( is_page_template( 'template-charts.php' ) || is_page_template( 'template-dashboard.php' ) || is_page_template( 'template-data.php' ) || is_page_template( 'template-settings.php' ) || is_page_template( 'template-standard.php' ) || is_page_template( 'template-yardstick.php' ) ) : ?>

	</div><!-- .col-10 -->
	</div><!-- .row --> <?php 

endif;

wp_footer(); ?>

</body>
</html>