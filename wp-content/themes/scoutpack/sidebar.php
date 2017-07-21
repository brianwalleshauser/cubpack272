		<aside>
			<article>
<?php
if ( is_active_sidebar( 'first-sidebar' ) ) : ?>
	<div id="secondary" class="sidebar-container" role="complementary">
		<div class="sidebar-inner">
			<div class="widget-area">
				<?php dynamic_sidebar( 'first-sidebar' ); ?>
			</div><!-- .widget-area -->
		</div><!-- .sidebar-inner -->
	</div><!-- #secondary -->
<?php endif; ?>
			</article>
		</aside>