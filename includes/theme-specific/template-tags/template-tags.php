<?php
/**
 * This page contains template tag functions usable by themes
 * 
 * @link http://mintplugins.com/doc/move-plugins-core-api/
 *
 * @since 1.0.0
 *
 * @package    MP Core
 * @subpackage Theme Specific Functions
 *
 * @copyright  Copyright (c) 2014, Mint Plugins
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @author     Philip Johnston
 */
 
/**
 * Display navigation to next/previous pages when applicable (like in a single.php template)
 *
 * @link     http://mintplugins.com/doc/mp_core_content_nav/
 * @see      is_single()
 * @see      is_attachment()
 * @see      get_post()  
 * @see      get_adjacent_post()
 * @see      is_home()
 * @see      is_archive()
 * @see      is_search()
 * @see      previous_post_link()
 * @see      next_post_link()
 * @see      get_next_posts_link()
 * @see      previous_posts_link()
 * @global   object $wp_query WP Query object.
 * @global   object $post WP Post object.
 * @param    string $nav_id The HTML ID to use for this div upon output
 * @return   void
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

/**
 * Prints HTML with meta information for the current post-date/time and author.
 *
 * @link     http://mintplugins.com/doc/mp_core_posted_on/
 * @see      esc_url()
 * @see      get_permalink()
 * @see      esc_attr()  
 * @see      get_the_time()
 * @see      get_the_date()
 * @see      get_author_posts_url()
 * @see      get_the_author_meta()
 * @see      get_the_author()
 * @return   void
 */
if ( ! function_exists( 'mp_core_posted_on' ) ) :
	function mp_core_posted_on() {
		printf( __( '<a href="%1$s" title="%2$s" rel="bookmark"><time class="entry-date" datetime="%3$s" pubdate>%4$s</time></a>', 'mp_core' ),
			esc_url( get_permalink() ),
			esc_attr( get_the_time() ),
			esc_attr( get_the_date( 'c' ) ),
			esc_html( get_the_date() )
		);
	}
endif;

/**
 * Prints HTML with meta information for the current post-date/time and author.
 *
 * @link     http://mintplugins.com/doc/mp_core_posted_on/
 * @see      esc_url()
 * @see      get_author_posts_url()
 * @see      get_the_author_meta()
 * @see      get_the_author()
 * @return   void
 */
if ( ! function_exists( 'mp_core_author' ) ) :
	function mp_core_author( $author_id = NULL, $added_output = NULL ) {
		printf( __( '<span class="author vcard"><a class="url fn n" href="%1$s" title="%2$s" rel="author">%3$s</a></span>', 'mp_core' ),
			esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
			esc_attr( sprintf( __( 'View all posts by %s', 'mp_core' ), get_the_author() ) ),
			get_the_author()
		);
	}
endif;

/**
 * This template tag displays the title of the page whether it is an archive, category, tag, page, post, custom post type, search page, or anything.
 *
 * @link     http://mintplugins.com/doc/mp_core_posted_on/
 * @see      is_category()
 * @see      single_cat_title()
 * @see      is_tag()  
 * @see      is_author()
 * @see      the_post()
 * @see      get_author_posts_url()
 * @see      get_the_author_meta()
 * @see      get_the_author()
 * @see      rewind_posts()
 * @see      get_post_type()
 * @see      single_tag_title()
 * @see      post_type_archive_title()
 * @see      is_tax()
 * @see      is_single()
 * @see      the_title()
 * @see      is_page()
 * @see      single_tag_title()
 * @see      is_search()
 * @see      get_search_query()
 * @see      is_day()
 * @see      is_month()
 * @see      is_year()
 * @see      get_the_date()
 * @return   void
 */
function mp_core_page_title(){

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
	} elseif( is_404() ){
		_e( 'Nothing Found!', 'mp_core' );
	} elseif ( get_post_type() ) {
		
		//If taxonomy
		if ( is_tax() ){
			 printf( '<span>' . single_tag_title( '', false ) . '</span>' );
		}
		//If page or single
		else if( is_page() || is_single() || is_singular() ) {
			the_title();
		}
		//If Search
		else if ( is_search() ){
			printf( __( 'Search Results for: %s', 'mp_core' ), '<span>' . get_search_query() . '</span>' );
		}
		//If Custom Post Type
		else{
			post_type_archive_title();
		}
		
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


/**
 * This template tag displays everything needed to keep Google happy for microformats - but when you don't want to actually show them to the user.
 *
 * @link     http://mintplugins.com/doc/mp_core_invisible_microformats/
 * @return   void
 */
function mp_core_invisible_microformats(){
	?>
	<div class="microformats" style="display:none;">
        <h1 class="entry-title"><?php the_title(); ?></h1>
        <span class="author vcard"><span class="fn"><?php the_author(); ?></span></span>
        <time class="published" datetime="<?php the_time('Y-m-d H:i:s'); ?>"><?php the_date(); ?></time>
        <time class="updated" datetime="<?php the_modified_date('Y-m-d H:i:s'); ?>"><?php the_modified_date(); ?></time>
        <div class="entry-summary"><?php the_excerpt(); ?></div>
    </div>
    <?php	
}