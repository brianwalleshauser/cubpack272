<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <header>
		<?php
        if ( is_single() or is_page()  ) {
    ?>
        <h1 class="entry-title">
            <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>" rel="bookmark">
                <?php the_title(); ?>
            </a>
        </h1>
<?php
}
else {
?>
        <h2 class="entry-title">
            <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>" rel="bookmark">
                <?php the_title(); ?>
            </a>
        </h2>
<?php
}
?>
        <p class="entry-meta">
            Posted on <time datetime="<?php the_time('F j, Y'); ?>"><?php the_time( 'F j, Y' ); ?></time> 
            by <?php the_author_link(); ?> 
        <?php 
            // Are the comments open?
            if ( comments_open() ) : ?>
            &bull; <?php comments_popup_link( 'No comments', '1 comment', '% comments' ); ?>
        <?php endif; ?>
        </p>
    </header>
    <?php
        // The content
        the_content();
				
				?><p><?php the_tags(); ?></p><?php
				wp_link_pages('before=<p>&after= </p>&next_or_number=number&pagelink=page %');
        
        // If singular and comments are open
        if ( is_singular() && comments_open() )
        comments_template( '', true );
    ?>
</article>