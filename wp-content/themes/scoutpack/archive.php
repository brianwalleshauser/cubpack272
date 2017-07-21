<?php get_header(); ?>
	<div id="page-content">
		<div id="content-wide">
		
				<h1 class="page-title">
				<?php if (is_tag()) : ?>
					<?php single_tag_title('Tag: '); ?>
				<?php elseif ( is_category() ) : ?>
					<?php single_cat_title('Category: '); ?>
				<?php else: ?>
				  Archives
				<?php endif; ?>
				</h1>
				
				<?php
				// Start the loop
				while(have_posts() ) : the_post();
				
				// Get the correct content type
				get_template_part( 'content', get_post_format() );
				
				// Loop ends
				endwhile;
				?>
					
		</div> <!-- End content-wide -->

		<?php get_sidebar(); ?>

 	</div> <!-- End page-content -->

	<?php get_footer(); ?>