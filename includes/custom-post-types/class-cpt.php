<?php
/**
 * Same arguments that `register_post_type()`
 *
 * -ttp://codex.wordpress.org/Function_Reference/register_post_type
 * -ttp://seebz.net/notes/?note_id=145
 */
class MP_Core_Custom_Post_Type_With_Dates {
	
	protected $_post_type;
	protected $_args;
	
	public function __construct( $post_type, $args = array() ) {
		$this->_post_type = $post_type;
		$this->_args = $args;
		
		add_action('init', array(&$this, 'register_post_type'));
		add_action('rewrite_rules_array', array(&$this, 'rewrite_rules_array'));
		add_action('wp_loaded', array(&$this, 'wp_loaded'));
	}
	
	public function register_post_type() {
		register_post_type($this->_post_type, $this->_args);
	}
	
	public function rewrite_rules_array( $rules ) {
		$post_type = $this->_post_type;
		if ( isset($this->_args['rewrite']) && is_array($this->_args['rewrite'])
			&& isset($this->_args['rewrite']['slug']) ) {
			$slug = $this->_args['rewrite']['slug'];
		} else {
			$slug = $post_type;
		}
		$new_rules = $this->_get_new_rules();
		return ($new_rules + $rules);
	}
	
	public function wp_loaded() {
		$test_key = current(array_keys($this->_get_new_rules()));
		$rules = get_option('rewrite_rules');
		if (!array_key_exists($test_key, $rules)) {
			global $wp_rewrite;
			$wp_rewrite->flush_rules();
		}
	}
	
	protected function _get_new_rules() {
		$post_type = $this->_post_type;
		if ( isset($this->_args['rewrite']) && is_array($this->_args['rewrite'])
			&& isset($this->_args['rewrite']['slug']) ) {
			$slug = $this->_args['rewrite']['slug'];
		} else {
			$slug = $post_type;
		}
		return array(
			"{$slug}/([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/feed/(feed|rdf|rss|rss2|atom)/?$" => 'index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&feed=$matches[4]' . '&post_type=' .  $post_type,
			"{$slug}/([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/(feed|rdf|rss|rss2|atom)/?$" => 'index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&feed=$matches[4]' . '&post_type=' .  $post_type,
			"{$slug}/([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/page/?([0-9]{1,})/?$" => 'index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&paged=$matches[4]' . '&post_type=' .  $post_type,
			"{$slug}/([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/?$" => 'index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]' . '&post_type=' .  $post_type,
			"{$slug}/([0-9]{4})/([0-9]{1,2})/feed/(feed|rdf|rss|rss2|atom)/?$" => 'index.php?year=$matches[1]&monthnum=$matches[2]&feed=$matches[3]' . '&post_type=' .  $post_type,
			"{$slug}/([0-9]{4})/([0-9]{1,2})/(feed|rdf|rss|rss2|atom)/?$" => 'index.php?year=$matches[1]&monthnum=$matches[2]&feed=$matches[3]' . '&post_type=' .  $post_type,
			"{$slug}/([0-9]{4})/([0-9]{1,2})/page/?([0-9]{1,})/?$" => 'index.php?year=$matches[1]&monthnum=$matches[2]&paged=$matches[3]' . '&post_type=' .  $post_type,
			"{$slug}/([0-9]{4})/([0-9]{1,2})/?$" => 'index.php?year=$matches[1]&monthnum=$matches[2]' . '&post_type=' .  $post_type,
			"{$slug}/([0-9]{4})/feed/(feed|rdf|rss|rss2|atom)/?$" => 'index.php?year=$matches[1]&feed=$matches[2]' . '&post_type=' .  $post_type,
			"{$slug}/([0-9]{4})/(feed|rdf|rss|rss2|atom)/?$" => 'index.php?year=$matches[1]&feed=$matches[2]' . '&post_type=' .  $post_type,
			"{$slug}/([0-9]{4})/page/?([0-9]{1,})/?$" => 'index.php?year=$matches[1]&paged=$matches[2]' . '&post_type=' .  $post_type,
			"{$slug}/([0-9]{4})/?$" => 'index.php?year=$matches[1]' . '&post_type=' .  $post_type,
		);
	}
}
?>