	<footer>
			
		<div class="footer-third">	
			<?php
			if ( is_active_sidebar( 'footer-sidebar' ) ) : ?>
				<div id="tertiary" class="sidebar-container footer" role="complementary">
					<div class="sidebar-inner">
						<div class="widget-area">
							<?php dynamic_sidebar( 'footer-sidebar' ); ?>
						</div><!-- .widget-area -->
					</div><!-- .sidebar-inner -->
				</div><!-- #tertiary -->
			<?php else: ?><?php get_search_form('Search . . .'); ?>
			<?php endif; ?>
		</div>

		<div class="footer-third">
			<?php
			if ( is_active_sidebar( 'footer-middle-sidebar' ) ) : ?>
				<div id="footer-middle-sidebar" class="sidebar-container footer" role="complementary">
					<div class="sidebar-inner">
						<div class="widget-area">
							<?php dynamic_sidebar( 'footer-middle-sidebar' ); ?>
						</div><!-- .widget-area -->
					</div><!-- .sidebar-inner -->
				</div><!-- #footer-middle-sidebar -->
			<?php else: ?>&nbsp;
			<?php endif; ?>		
		</div>

		<div class="footer-third">
			<?php
			if ( is_active_sidebar( 'footer-right-sidebar' ) ) : ?>
				<div id="footer-right-sidebar" class="sidebar-container footer" role="complementary">
					<div class="sidebar-inner">
						<div class="widget-area">
							<?php dynamic_sidebar( 'footer-right-sidebar' ); ?>
						</div><!-- .widget-area -->
					</div><!-- .sidebar-inner -->
				</div><!-- #footer-right-sidebar -->
			<?php else: ?><img id="footer-prepared" src="<?php echo get_template_directory_uri(); ?>/images/prepared_standard_rev.gif" alt="Do Your Best!">	
			<?php endif; ?>			
		</div>
		
		<div class="clear-both"></div>
		
	</footer>

	<div id="credit-left">
		<div class="no-mobile">
			&copy; <?php echo date("Y"); ?> <?php bloginfo( 'name' ); ?> - <a href="http://www.scouting.org/">Boy Scouts of America</a> | <a href="<?php echo admin_url(); ?>"><span class="no-mobile">WordPress </span>Admin</a>
		</div>
		<div class="mobile-only">
			&copy; <?php echo date("Y"); ?> <?php bloginfo( 'name' ); ?> - <a href="http://www.scouting.org/">Boy Scouts of America</a><br /><a href="<?php echo admin_url(); ?>"><span class="no-mobile">WordPress </span>Admin</a>
		</div>
	</div>

	<div id="credit">	
		<div class="no-mobile">
			Powered By <a href="http://www.wordpress.org">WordPress</a> | <a href="http://www.handsomeweb.com/cubscouts/">WordPress theme for Cub Scout Packs.</a>
		</div>
		<div class="mobile-only">
			Powered By <a href="http://www.wordpress.org">WordPress</a><br /><a href="http://www.handsomeweb.com/cubscouts/">WordPress theme for Cub Scout Packs.</a>
		</div>
	</div>

</div>

<!--
 _____                                   _     ______           _      _  __       
|  __ \                                 | |   |  ____|         | |    (_)/ _| 
| |__) | __ ___ _ __   __ _ _ __ ___  __| |   | |__ ___  _ __  | |     _| |_ ___  
|  ___/ '__/ _ \ '_ \ / _` | '__/ _ \/ _` |   |  __/ _ \| '__| | |    | |  _/ _ \ 
| |   | | |  __/ |_) | (_| | | |  __/ (_| |_  | | | (_) | |    | |____| | ||  __/_
|_|   |_|  \___| .__/ \__,_|_|  \___|\__,_(_) |_|  \___/|_|    |______|_|_| \___(_) 
               | |                                            
               |_|                                            
-->
     
<?php wp_footer(); ?>
<script>
	jQuery(document).ready(function($){
		jQuery(".flexnav").flexNav({ 'animationSpeed' : 50, 'calcItemWidths': true, 'hoverIntent': true, 'hoverIntentTimeout': 25, 'hover': true }); 
	});
</script>
</body>
</html>