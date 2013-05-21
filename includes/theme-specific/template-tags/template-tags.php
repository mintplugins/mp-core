<?php
/**
 * Custom template tags for this theme.
 *
 * Eventually, some of the functionality here could be replaced by core features
 *
 * @package mp_core
 * @since mp_core 1.0
 */
 
/**
 * Display navigation to next/previous pages when applicable
 *
 * @since mp_core 1.0
 */
if ( ! function_exists( 'mp_core_content_nav' ) ) :
function mp_core_content_nav( $nav_id ) {
	global $wp_query, $post;

	// Don't print empty markup on single pages if there's nowhere to navigate.
	if ( is_single() ) {
		$previous = ( is_attachment() ) ? get_post( $post->post_parent ) : get_adjacent_post( false, '', true );
		$next = get_adjacent_post( false, '', false );

		if ( ! $next && ! $previous )
			return;
	}

	// Don't print empty markup in archives if there's only one page.
	if ( $wp_query->max_num_pages < 2 && ( is_home() || is_archive() || is_search() ) )
		return;

	$nav_class = 'site-navigation paging-navigation';
	if ( is_single() )
		$nav_class = 'site-navigation post-navigation';

	?>
	<nav role="navigation" id="<?php echo $nav_id; ?>" class="<?php echo $nav_class; ?>">
		<h1 class="assistive-text"><?php _e( 'Post navigation', 'mp_core' ); ?></h1>

	<?php if ( is_single() ) : // navigation links for single posts ?>

		<?php previous_post_link( '<div class="nav-previous">%link</div>', '<span class="meta-nav">' . _x( '&larr;', 'Previous post link', 'mp_core' ) . '</span> %title' ); ?>
		<?php next_post_link( '<div class="nav-next">%link</div>', '%title <span class="meta-nav">' . _x( '&rarr;', 'Next post link', 'mp_core' ) . '</span>' ); ?>

	<?php elseif ( $wp_query->max_num_pages > 1 && ( is_home() || is_archive() || is_search() ) ) : // navigation links for home, archive, and search pages ?>

		<?php if ( get_next_posts_link() ) : ?>
		<div class="nav-previous"><?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Older posts', 'mp_core' ) ); ?></div>
		<?php endif; ?>

		<?php if ( get_previous_posts_link() ) : ?>
		<div class="nav-next"><?php previous_posts_link( __( 'Newer posts <span class="meta-nav">&rarr;</span>', 'mp_core' ) ); ?></div>
		<?php endif; ?>

	<?php endif; ?>

	</nav><!-- #<?php echo $nav_id; ?> -->
	<?php
}
endif; // mp_core_content_nav

if ( ! function_exists( 'mp_core_posted_on' ) ) :
/**
 * Prints HTML with meta information for the current post-date/time and author.
 *
 * @since mp_core 1.0
 */
function mp_core_posted_on() {
	printf( __( 'Posted on <a href="%1$s" title="%2$s" rel="bookmark"><time class="entry-date" datetime="%3$s" pubdate>%4$s</time></a><span class="byline"> by <span class="author vcard"><a class="url fn n" href="%5$s" title="%6$s" rel="author">%7$s</a></span></span>', 'mp_core' ),
		esc_url( get_permalink() ),
		esc_attr( get_the_time() ),
		esc_attr( get_the_date( 'c' ) ),
		esc_html( get_the_date() ),
		esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
		esc_attr( sprintf( __( 'View all posts by %s', 'mp_core' ), get_the_author() ) ),
		get_the_author()
	);
}
endif;

/**
 * Returns true if a blog has more than 1 category
 *
 * @since mp_core 1.0
 */
function mp_core_categorized_blog() {
	if ( false === ( $all_the_cool_cats = get_transient( 'all_the_cool_cats' ) ) ) {
		// Create an array of all the categories that are attached to posts
		$all_the_cool_cats = get_categories( array(
			'hide_empty' => 1,
		) );

		// Count the number of categories that are attached to the posts
		$all_the_cool_cats = count( $all_the_cool_cats );

		set_transient( 'all_the_cool_cats', $all_the_cool_cats );
	}

	if ( '1' != $all_the_cool_cats ) {
		// This blog has more than 1 category so mp_core_categorized_blog should return true
		return true;
	} else {
		// This blog has only 1 category so mp_core_categorized_blog should return false
		return false;
	}
}

/**
 * Flush out the transients used in mp_core_categorized_blog
 *
 * @since mp_core 1.0
 */
function mp_core_category_transient_flusher() {
	// Like, beat it. Dig?
	delete_transient( 'all_the_cool_cats' );
}
add_action( 'edit_category', 'mp_core_category_transient_flusher' );
add_action( 'save_post', 'mp_core_category_transient_flusher' );

/**
 * Archive page Title
 *
 * @since mp_core 1.0
 */
function mp_core_archive_page_title(){

	if ( is_category() ) {
		printf( '<span>' . single_cat_title( '', false ) . '</span>' );

	} elseif ( is_tag() ) {
		printf('<span>' . single_tag_title( '', false ) . '</span>' );

	} elseif ( is_author() ) {
		/* Queue the first post, that way we know
		 * what author we're dealing with (if that is the case).
		*/
		the_post();
		printf( __( 'Author Archives: %s', 'mp_core' ), '<span class="vcard"><a class="url fn n" href="' . get_author_posts_url( get_the_author_meta( "ID" ) ) . '" title="' . esc_attr( get_the_author() ) . '" rel="me">' . get_the_author() . '</a></span>' );
		/* Since we called the_post() above, we need to
		 * rewind the loop back to the beginning that way
		 * we can run the loop properly, in full.
		 */
		rewind_posts();
	} elseif ( get_post_type() ) {
		
		//If taxonomy
		is_tax() ? printf( '<span>' . single_tag_title( '', false ) . '</span>' ) : post_type_archive_title();
		
		//If page or single
		is_page() || is_single() ? the_title( ) : NULL;
		
		//If search 
		is_search() ? printf( __( 'Search Results for: %s', 'mp_core' ), '<span>' . get_search_query() . '</span>' ) : NULL;
		
	} elseif ( is_day() ) {
		printf( __( 'Daily Archives: %s', 'mp_core' ), '<span>' . get_the_date() . '</span>' );

	} elseif ( is_month() ) {
		printf( __( 'Monthly Archives: %s', 'mp_core' ), '<span>' . get_the_date( 'F Y' ) . '</span>' );

	} elseif ( is_year() ) {
		printf( __( 'Yearly Archives: %s', 'mp_core' ), '<span>' . get_the_date( 'Y' ) . '</span>' );

	} 
	else {
		_e( 'Archives', 'mp_core' );

	}
}