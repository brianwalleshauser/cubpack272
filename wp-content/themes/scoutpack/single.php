<?php get_header(); ?>
	<div id="page-content">
		<div id="content-wide">
			<section>					
		
				<?php if ( have_posts() ) : ?>

					<?php /* The loop */ ?>
					<?php while ( have_posts() ) : the_post(); ?>
						<?php get_template_part( 'content', get_post_format() ); ?>
					<?php endwhile; ?>

					<?php if ( get_next_posts_link() ) : ?>
						<div class="nav-previous"><?php next_posts_link( 'Older Entries', $the_query->max_num_pages ); ?></div>
					<?php endif; ?>

					<?php if ( get_previous_posts_link() ) : ?>
						<div class="nav-next"><?php previous_posts_link( 'Newer Entries' ); ?></div>
					<?php endif; ?>

				<?php else : ?>

					<?php get_template_part( 'content', 'none' ); ?>

				<?php endif; ?>
				
			</section>		
		</div> <!-- End content-wide -->

		<?php get_sidebar(); ?>

 	</div> <!-- End page-content -->

	<?php get_footer(); ?>