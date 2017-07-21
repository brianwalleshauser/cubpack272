<?php get_header(); ?>
	<div id="page-content">
		<div id="content-wide">
				<?php 
				// Let's ignore posts in the "home-page" category when possible.
				// But only when a category or archive is not being called or if the page is paged (pagination used past first page)
				// All of this seems very limited, but it will keep the home-page category from appearing on the first page of a
				// general post page, the first page of any archive, or the first page of any archive.
				if ( !is_paged() ) {  //code will break on paged pages, so we'll go ahead and show it on paged pages.
					$var = get_query_var( 'cat' ); //code will bread widget categories, so we skip categories
					if ((strlen($var) ==0)){ 
						$year = get_query_var('year'); //code will break widget archives, so we skip archives
						if ($year==0){ 
							$cat = get_term_by( 'slug', 'home-page', 'category' );
							$exclude = $cat->term_id; // The IDs of the excluding categories
							$q = 'cat='.'-'.$exclude;
							query_posts($q);
						}
					}	
				}
				?>
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

				<?php endif; 
				
				// Reset Query in case we do something at another time.
				wp_reset_query();
				?>
					
		</div> <!-- End content-wide -->

		<?php get_sidebar(); ?>

 	</div> <!-- End page-content -->

	<?php get_footer(); ?>