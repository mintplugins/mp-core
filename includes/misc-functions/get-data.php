<?php
/**
 * Get all Pages insto an associative array
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
 * Get all Post Types into an associative array
 */
function mp_core_get_all_post_types($args = array('public' => true, '_builtin' => false ) ) {
	
	$output = 'objects'; // names or objects
	$post_types = get_post_types($args,$output); 
			
	foreach ($post_types as $id => $post_type ) {
		$return_array[$id] = $post_type->labels->name;
	}
	return ( $return_array );
}

/**
 * Get all Post by a certain post type
 */
function mp_core_get_all_posts_by_type($slug) {
	
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
 * Get all Posts by a certain taxonomy
 */
function mp_core_get_all_posts_by_tax($slug) {
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
 * Get all taxonomiy terms - ALL of them. Yeah...ALL of them
 */
function mp_core_get_all_tax_terms() {
	
	//Should probably add the ability to exlude tax terms you know you dont want
	
	$taxonomies = get_taxonomies();
	
	$all_taxonomies = array();
	
	foreach ($taxonomies as $taxonomy ) {
		
		$all_taxonomies[$taxonomy] = get_terms( $taxonomy, array(
			'orderby'    => 'count',
			'hide_empty' => 0
		 ) );
	 	
	}
	//print_r ($all_taxonomies);
	//Loop through all taxonomies
	foreach ( $all_taxonomies as $taxonomy_name => $taxonomy_user_items ){
		//Loop through each user-created taxonomy item within each taxonomy
		foreach (  $taxonomy_user_items as $id => $taxonomy_user_item ){
			$return_array[$taxonomy_user_item->term_id . '*' . $taxonomy_name] = $taxonomy_user_item->name;
		}
	}
	
	//sample return array. Do an explode at the *
	//array( '12*my_category' => 'My Category' );
	
	return ( $return_array );
}

/**
 * Get all Posts in a tax term that have a related tax term. Ie all the posts in a "tshirt" category with the tag "red"
 */
function mp_core_get_all_posts_in_tax_by_tax( $args = array(array('base_archive' => 'true', 'base_taxonomy' => 'product_cat', 'related_taxonomy_items' => 'product_tag')) ){
	global $wpdb;

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
					t1.taxonomy = '". $args['base_taxonomy'] . "' AND p1.post_status = 'publish' AND
					t2.taxonomy = '" . $args['related_taxonomy_items'] ."' AND p2.post_status = 'publish'
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
					t1.taxonomy = '". $args['base_taxonomy'] . "' AND p1.post_status = 'publish' AND terms1.term_id IN (".$args['current_taxonomy_item'].") AND
					t2.taxonomy = '" . $args['related_taxonomy_items'] ."' AND p2.post_status = 'publish'
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