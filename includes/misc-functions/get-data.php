<?php
/**
 * This file contains various functions for getting data.
 *
 * @since 1.0.0
 *
 * @package    MP Core
 * @subpackage Functions
 *
 * @copyright  Copyright (c) 2014, Mint Plugins
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @author     Philip Johnston
 */
 
 
/**
 * Get all Pages into a tidy associative array containing just the page ID as the key and the Page Title as the value
 *
 * @since    1.0.0
 * @link     http://mintplugins.com/doc/mp_core_get_all_pages/
 * @see      get_pages()
 * @return   array $output An array of pages containing just the page ID as the key and the Page Title as the value
 */
function mp_core_get_all_pages() {
	
	$output = array();
	$terms = get_pages(); 
	
	foreach ( $terms as $term ) {
		$output[ $term->ID ] = $term->post_title;
	}
	
	return $output;
}

/**
 * Get all Post Types into a tidy associative array with just the Post Type ID as the key and the Post Type Name as the value
 *
 * Note: Only use this function on or after the 'register_sidebar' hook 
 *
 * @since    1.0.0
 * @link     http://mintplugins.com/doc/mp_core_get_all_post_types/
 * @see      get_post_types()
 * @param    array $args (required) See link for description.
 * @return   array $return_array An array with all the post types structured with the key as the post_type ID and the value as the Post Type Name
 */
function mp_core_get_all_post_types( $args = array('public' => true, '_builtin' => false ) ) {
	
	$return_array = array();
	
	$output = 'objects'; // names or objects
	$post_types = get_post_types( $args, $output ); 
			
	foreach ( $post_types as $id => $post_type ) {
		
			$return_array[$id] = $post_type->labels->name;
	}
	return ( $return_array );
	
}

/**
 * Get all Post by a certain post type
 *
 * @since    1.0.0
 * @link     http://mintplugins.com/doc/mp_core_get_all_posts_by_type/
 * @see      get_posts()
 * @param    string $slug (required) The slug of the Post Type
 * @return   array $return_array An array with all the posts in the given Post Type structured with the key as the Post ID and the value as the Post Title
 */
function mp_core_get_all_posts_by_type( $slug ) {
	
	$return_array = array();
	
	$args = array(
		'posts_per_page'  => -1,
		'post_type'       => $slug,
		'post_status'     => 'publish',
		'suppress_filters' => true 
	);
	
	$cpts = get_posts( $args );
	
	foreach ($cpts as $cpt) {
		$return_array[$cpt->ID] = $cpt->post_title;
	}
		
	return $return_array;
}

/**
 * Get all Post Types that are hierarchical into an associative array
 *
 * Note: Only use this function on or after the 'register_sidebar' hook
 *
 * @since    1.0.0
 * @link     http://mintplugins.com/doc/mp_core_get_all_hierarchical_post_types/
 * @see      get_post_types()
 * @param    array $args (required) See link for description.
 * @return   array $return_array An array with all the  hierarchical post types structured with the key as the post_type ID and the value as the Post Type Name
 */
function mp_core_get_all_hierarchical_post_types($args = array('public' => true, '_builtin' => false, 'hierarchical' => true ) ) {
	
	$return_array = array();
	
	$output = 'objects'; // names or objects
	$post_types = get_post_types($args,$output); 
			
	foreach ($post_types as $id => $post_type ) {
		
		$return_array[$id] = $post_type->labels->name;
	}
	return ( $return_array );

}

/**
 * Get all terms in a certain taxonomy
 *
 * @since    1.0.0
 * @link     http://mintplugins.com/doc/mp_core_get_all_terms_by_tax/
 * @see      taxonomy_exists()
 * @see      get_terms()
 * @param    string $slug (required) The slug of the taxonomy
 * @return   array $return_array An array with all the  terms in a given tax structured with the key as the term ID and the value as the Term Name
 */
function mp_core_get_all_terms_by_tax($slug) {
	if (taxonomy_exists($slug)){
		$output = array();
		$terms  = get_terms( array( $slug ), array( 'hide_empty' => 0 ) );
		foreach ( $terms as $term ) {
			$output[ $term->term_id ] = $term->name;
		}
		
		return $output;
	}
}

/**
 * Get all taxonomiy terms for ALL taxonomies. That's right, EVERY taxonomy term in existence on this WordPress.
 *
 * This is a sample return array. Do an explode at the * for the key
 * array( '12*my_category' => 'My Category Term' );
 *
 * @since    1.0.0
 * @link     http://mintplugins.com/doc/mp_core_get_all_tax_terms/
 * @see      get_taxonomies()
 * @see       get_terms()
 * @param    array $exclude_slugs Array containing taxonomy slugs to skip
 * @return   array $return_array An array with all of the taxonomy terms. Each array key = the ID tax term id + taxonomy slug separated by an *, and the value being the Title. 
 */
function mp_core_get_all_tax_terms( $exclude_slugs = array() ) {
	
	//Should probably add the ability to exlude tax terms you know you dont want
	
	$taxonomies = get_taxonomies();
	
	$return_array = array();
	
	$all_taxonomies = array();
	
	foreach ($taxonomies as $taxonomy ) {
		
		//exclude post types that match the $exclude_slugs array
		if ( !in_array( $taxonomy, $exclude_slugs ) ) { 
		
			$all_taxonomies[$taxonomy] = get_terms( $taxonomy, array(
				'orderby'    => 'count',
				'hide_empty' => 0
			 ) );
		 
		}
	 	
	}
	
	//Loop through all taxonomies
	foreach ( $all_taxonomies as $taxonomy_name => $taxonomy_user_items ){
		//Loop through each user-created taxonomy item within each taxonomy
		foreach (  $taxonomy_user_items as $id => $taxonomy_user_item ){
			$return_array[$taxonomy_user_item->term_id . '*' . $taxonomy_name] = $taxonomy_user_item->name;
		}
	}
	
	//sample return array. Do an explode at the *
	//array( '12*my_category' => 'My Category Term' );
	
	return ( $return_array );
}

/**
 * Get number of posts per tax term
 *
 * @since    1.0.0
 * @link     http://mintplugins.com/doc/mp_core_number_postpercat/
 * @see      get_taxonomies()
 * @see      get_terms()
 * @param    int $term_id The ID of the tax term (IE The category "Books"'s ID)
 * @return   int 
 */
function mp_core_number_postpercat($term_id) {
    global $wpdb;
    $query = "SELECT count FROM $wpdb->term_taxonomy WHERE term_id = $term_id";
    $num = $wpdb->get_col($query);
    return $num[0];
}

/**
 * Get all tax terms that have a related tax term. For Example: all the posts in a "tshirt" 'category' with the 'tag' "red"
 *
 * Get all taxonomy terms (ie 'tags' like 'green', 'blue', or 'red') applied to posts that also have a specific, separate taxonomy's term applied to them.
 * A simpler way to put it is to get all tags that are in a specific category. However, they could be any taxonomy - not just tags and cats.
 *
 * I need to do further testing with this function. - Phil Johnston
 *
 * @since    1.0.0
 * @link     http://mintplugins.com/doc/mp_core_get_all_tags_in_cat/
 * @see      wpdb::get_results()
 * @see      get_tag_link()
 * @global   object $wpdb wpdb Object
 * @param    array $args See link for description
 * @return   array $return_array An array with all of the tax terms that have a related tax term
 */
function mp_core_get_all_tags_in_cat( $args ){
		
		global $wpdb;
		
		$defaults = array(
			'base_taxonomy_slug' => 'category', 
			'base_taxonomy_term_id' => NULL,
			'related_taxonomy_slug' => 'post_tag',
			'base_archive' => true
		);

		if (isset($args['base_archive'])){
			$tags = $wpdb->get_results
			("
				SELECT DISTINCT terms2.term_id as tag_id, terms2.name as tag_name, terms2.slug as tag_slug, null as tag_link
				FROM
					". $wpdb->posts . " as p1
					LEFT JOIN ". $wpdb->term_relationships . " as r1 ON p1.ID = r1.object_ID
					LEFT JOIN ". $wpdb->term_taxonomy . " as t1 ON r1.term_taxonomy_id = t1.term_taxonomy_id
					LEFT JOIN ". $wpdb->terms . " as terms1 ON t1.term_id = terms1.term_id,
		
					" . $wpdb->posts . " as p2
					LEFT JOIN ". $wpdb->term_relationships . " as r2 ON p2.ID = r2.object_ID
					LEFT JOIN ". $wpdb->term_taxonomy . "  as t2 ON r2.term_taxonomy_id = t2.term_taxonomy_id
					LEFT JOIN ". $wpdb->terms . " as terms2 ON t2.term_id = terms2.term_id
				WHERE
					t1.taxonomy = '". $args['base_taxonomy_slug'] . "' AND p1.post_status = 'publish' AND
					t2.taxonomy = '" . $args['related_taxonomy_slug'] ."' AND p2.post_status = 'publish'
					AND p1.ID = p2.ID
				ORDER by tag_name
			");
			$count = 0;
			foreach ($tags as $tag) {
				$tags[$count]->tag_link = get_tag_link($tag->tag_id);
				$count++;
			}
		}else{
			$tags = $wpdb->get_results
			("
				SELECT DISTINCT terms2.term_id as tag_id, terms2.name as tag_name, terms2.slug as tag_slug, null as tag_link
				FROM
					" . $wpdb->posts . " as p1
					LEFT JOIN ". $wpdb->term_relationships . " as r1 ON p1.ID = r1.object_ID
					LEFT JOIN ". $wpdb->term_taxonomy . " as t1 ON r1.term_taxonomy_id = t1.term_taxonomy_id
					LEFT JOIN ". $wpdb->terms . " as terms1 ON t1.term_id = terms1.term_id,
		
					" . $wpdb->posts . " as p2
					LEFT JOIN ". $wpdb->term_relationships . " as r2 ON p2.ID = r2.object_ID
					LEFT JOIN ". $wpdb->term_taxonomy . " as t2 ON r2.term_taxonomy_id = t2.term_taxonomy_id
					LEFT JOIN ". $wpdb->terms . " as terms2 ON t2.term_id = terms2.term_id
				WHERE
					t1.taxonomy = '". $args['base_taxonomy_slug'] . "' AND p1.post_status = 'publish' AND terms1.term_id IN (".$args['base_taxonomy_term_id'].") AND
					t2.taxonomy = '" . $args['related_taxonomy_slug'] ."' AND p2.post_status = 'publish'
					AND p1.ID = p2.ID
				ORDER by tag_name
			");
			$count = 0;
			foreach ($tags as $tag) {
				$tags[$count]->tag_link = get_tag_link($tag->tag_id);
				$count++;
			}
		}
		return $tags;
}

/**
 * Get the attachment id using a url
 *
 * @since    1.0.0
 * @link     http://mintplugins.com/doc/mp_core_get_attachment_id_from_url/
 * @see      wpdb::get_results()
 * @see      wpdb::get_var()
 * @see      wpdb::prepare()
 * @see      wp_upload_dir()
 * @global   object $wpdb wpdb Object
 * @param    string $attachment_url See link for description
 * @return   mixed $return If the attachment exists, return the attachment id. If not, return false
 */
function mp_core_get_attachment_id_from_url( $attachment_url = '' ) {
 
	global $wpdb;
	$attachment_id = false;
 
	// If there is no url, return.
	if ( '' == $attachment_url )
		return false;
 
	// Get the upload directory paths
	$upload_dir_paths = wp_upload_dir();
 
	// Make sure the upload path base directory exists in the attachment URL, to verify that we're working with a media library image
	if ( false !== strpos( $attachment_url, $upload_dir_paths['baseurl'] ) ) {
 
		// If this is the URL of an auto-generated thumbnail, get the URL of the original image
		$attachment_url = preg_replace( '/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $attachment_url );
 
		// Remove the upload path base directory from the attachment URL
		$attachment_url = str_replace( $upload_dir_paths['baseurl'] . '/', '', $attachment_url );
 
		// Finally, run a custom database query to get the attachment ID from the modified attachment URL
		$attachment_id = $wpdb->get_var( $wpdb->prepare( "SELECT wposts.ID FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta WHERE wposts.ID = wpostmeta.post_id AND wpostmeta.meta_key = '_wp_attached_file' AND wpostmeta.meta_value = '%s' AND wposts.post_type = 'attachment'", $attachment_url ) );
 
	}
 
	return $attachment_id;
}

/**
 * Get the Current URL 
 *
 * @since    1.0.0
 * @link     http://mintplugins.com/doc/mp_core_get_current_url/
 * @return   string $url The current Page's URL
 */
function mp_core_get_current_url() {
  $url  = @( $_SERVER["HTTPS"] != 'on' ) ? 'http://'.$_SERVER["SERVER_NAME"] :  'https://'.$_SERVER["SERVER_NAME"];
  $url .= $_SERVER["REQUEST_URI"];
  return $url;
}

/**
 * Get the taxonomy terms a post has. 
 * This function can be used before the query is run - where the WP default wp_get_post_terms can't.
 *
 * @since    1.0.0
 * @link     http://mintplugins.com/doc/mp_core_get_current_url/
 * @global   $wpdb 
 * @param    int $post_id The id of the post who's terms we want.
 * @return   array $terms The terms attached to this post array( 0 => term_id, 1 => term_id2 )
 */
function mp_core_get_post_terms_before_query( $post_id ){
	
	global $wpdb;
				
	//Get the term taxonomy ids for this post. The term taxonomy id is a link between the post id and taxonomy term id
	$term_taxonomy_ids = $wpdb->get_col($wpdb->prepare( "SELECT term_taxonomy_id FROM $wpdb->term_relationships WHERE object_id=%d", $post_id ) );
	
	$terms = array();
	
	//Now that we have the links, loop through each link id and find the linked term id
	foreach( $term_taxonomy_ids as $term_taxonomy_id ){
		$term = $wpdb->get_col($wpdb->prepare( "SELECT term_id FROM $wpdb->term_taxonomy WHERE term_taxonomy_id=%d", $term_taxonomy_id ) );
		array_push( $terms, $term[0] );
	}
	
	return $terms;
	
}

/**
 * Get a post's excerpt using an id
 *
 * @since    1.0.0
 * @link     http://mintplugins.com/doc/mp_core_get_excerpt_by_id/
 * @param    int $post_id The id of the post who's excerpt we want.
 * @return   string $the_excerpt The Excerpt of the post ID we passed in
 */
function mp_core_get_excerpt_by_id($post_id){
    $the_post = get_post($post_id); //Gets post ID
	$the_excerpt = $the_post->post_excerpt;
    if ( empty( $the_excerpt ) ){
		$the_excerpt = $the_post->post_content; //Gets post_content to be used as a basis for the excerpt
	}
	
	//Strip Shortcodes
	$the_excerpt = preg_replace( '|\[(.+?)\](.+?\[/\\1\])?|s', '', $the_excerpt);
    $excerpt_length = 35; //Sets excerpt length by word count
    $the_excerpt = strip_tags(strip_shortcodes($the_excerpt)); //Strips tags and images
    $words = explode(' ', $the_excerpt, $excerpt_length + 1);

    if(count($words) > $excerpt_length) :
        array_pop($words);
        array_push($words, '…');
        $the_excerpt = implode(' ', $words);
    endif;

    $the_excerpt = '<p>' . $the_excerpt . '</p>';

    return $the_excerpt;
}

/**
 * Get the highest parent of any taxonomy term
 *
 * @since    1.0.0
 * @link     http://mintplugins.com/doc/mp_core_get_excerpt_by_id/
 * @param    string $term_id The id of the term
 * @return   string $taxonomy The slug of the taxonomy
 */
function mp_core_get_term_top_most_parent( $term_id, $taxonomy ){
    $parent  = get_term_by( 'id', $term_id, $taxonomy );
    while ( $parent->parent != 0 ){
        $parent  = get_term_by( 'id', $parent->parent, $taxonomy );
    }
    return $parent;
}

/**
 * Determines if a post, identified by the specified ID, exist
 * within the WordPress database.
 *
 * @link     https://tommcfarlin.com/wordpress-post-exists-by-id/
 * @param    int    $id    The ID of the post to check
 * @return   bool          True if the post exists; otherwise, false.
 * @since    1.0.0
 */
function mp_core_post_exists( $id ){

	$post_status = get_post_status( $id );
	
	//Posts in the trash don't REALLY "exist" - at least in the context we are talking about here.
	if ( $post_status == 'trash' ){
		return false;
	}
	
  	return is_string( $post_status );
}