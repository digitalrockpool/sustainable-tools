<?php

/* ***

Template Part:  Site - Footer - Tool

@package	      Sustainable Tools
@author		      Digital Rockpool
@link		        https://www.sustainable.tools/
@copyright	    Copyright (c) 2022, Digital Rockpool LTD
@license	      GPL-2.0+ 

*** */ ?>

        </article><!-- end article row -->
			</div><!-- end div col -->

		</main><!-- end main row -->

		<footer id="site-footer" class="row"><!-- start footer row -->

			<section class="site-info col-sm-6 pl-5">
				Copyright &copy; <?php echo date('Y'); ?> Digital Rockpool LTD
			</section>

			<section class="legal-info col-sm-6 pr-5 text-end"> <?php
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