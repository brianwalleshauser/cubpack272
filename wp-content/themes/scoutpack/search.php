<?php get_header(); ?>
<div id="page-content">
	<div id="content-wide">
		<section>

			<?php if ( have_posts() ) : ?>
				<h1>Search Results for <?php echo get_search_query(); ?></h1>

			<?php /* The loop */ ?>
			<?php while ( have_posts() ) : the_post(); ?>
				<h2 class="entry-title">
					<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>" rel="bookmark">
						<?php the_title(); ?>
					</a>
				</h2>
				<div class="entry-summary" style="border-bottom:1px dotted #ccc;margin-bottom:13px;">
					<?php the_excerpt(); ?>
						<p><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>" rel="bookmark">
							Read <?php the_title(); ?>
						</a></p>
					</div><!-- .entry-summary -->
				<?php endwhile; ?>

				<?php if ( get_next_posts_link() ) : ?>
					<div class="nav-previous"><?php next_posts_link( 'Older Results', $the_query->max_num_pages ); ?></div>
				<?php endif; ?>

				<?php if ( get_previous_posts_link() ) : ?>
					<div class="nav-next"><?php previous_posts_link( 'Newer Results' ); ?></div>
				<?php endif; ?>

			<?php else : ?>
				<h1>Search Results for <?php echo get_search_query(); ?></h1>
				<p>There are no results for your search.</p>
			<?php endif; ?>

		</section>		
	</div> <!-- End content-wide -->
	
	<?php get_sidebar(); ?>

</div> <!-- End page-content -->

<?php get_footer(); ?>