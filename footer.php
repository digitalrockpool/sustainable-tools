<?php

/* ***

Footer

@package	Sustainable Tools
@author		Digital Rockpool
@link		https://www.sustainable.tools/
@copyright	Copyright (c) 2023, Digital Rockpool LTD
@license	GPL-2.0+ 

*** */

if( is_page_template( 'templates/charts.php' ) || is_page_template( 'templates/dashboard.php' ) || is_page_template( 'templates/data.php' ) || is_page_template( 'templates/report.php' ) || is_page_template( 'templates/setting.php' ) || is_page_template( 'templates/standard.php' ) || is_page_template( 'templates/stock.php' ) || is_page_template( 'templates/tool.php' )  ): ?>
			</article><!-- end article row -->
		</div><!-- end div col --><?php

		$bg_transparent = 'bg-transparent';
endif; ?>

		</main><!-- end main row -->

		<footer id="site-footer" class="row g-0 <?php echo $bg_transparent ?>"><!-- start footer row -->

			<section class="site-info col-lg-4 ps-3 <?php if( is_page_template( 'templates/fullscreen.php' ) ) : echo 'aside_bg_dark'; endif; ?>">
				Copyright &copy; <?php echo date('Y'); ?> Digital Rockpool LTD
			</section>

			<section class="legal-info col-lg-8 pe-3 text-end"> <?php
				wp_nav_menu( array(
					'theme_location' => 'menu-3',
					'menu_id'        => 'legal-menu',
				) ); ?>
			</section>
		</footer><!-- end footer row -->

	</div><!-- end div container --><?php

	wp_footer(); ?>

</body>
</html>
