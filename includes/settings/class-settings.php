<?php
/**
 * mp_core Settings Class
 *
 * @package mp_core
 * @since mp_core 1.0
 */

/**
 * Class to create new options page
 *
 * This contains a call to register_setting() registers a validation callback, mp_core_settings_validate(),
 * which is used when the option is saved, to ensure that our option values are properly
 * formatted, and safe.
 *
 * It also creates the page for settings and outputs the settings passed-in to the class.
 *
 * @since mp_core 1.0
 */
 
class MP_CORE_Settings{
	
	protected $_args;
	protected $_settings_array = array();
	
	public function __construct($args){
		$this->_args = $args;
		
		add_action( 'admin_enqueue_scripts', array( $this, 'mp_core_enqueue_scripts' ) );
		add_action( 'admin_menu', array( $this, 'mp_core_add_page')  );
	}
	
	public function mp_core_enqueue_scripts(){
		//mp_core_metabox_css
		wp_enqueue_style( 'mp_core_settings_css', plugins_url('css/core/mp-core-settings.css', dirname(__FILE__)) );
		//color picker scripts
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker-load', plugins_url( 'js/core/wp-color-picker.js', dirname(__FILE__)),  array( 'jquery', 'wp-color-picker' ) );
		//media upload scripts
		wp_enqueue_media();
		//image uploader script
		wp_enqueue_script( 'image-upload', plugins_url( 'js/core/image-upload.js', dirname(__FILE__) ),  array( 'jquery' ) );	
	}
	
	/**
	 * Add our options page to the menu.
	 *
	 * This function is attached to the admin_menu action hook.
	 *
	 * @since mp_core 1.0
	 */
	public function mp_core_add_page() {
		
		//Create admin menu. It will be one of the functions found here: http://codex.wordpress.org/Administration_Menus
		$page_function_name = 'add_' . $this->_args['type'] . '_page';
		
		//Call function 'add_menu_page'
		if ($this->_args['type'] == 'menu' || $this->_args['type'] == 'object' || $this->_args['type'] == 'utility'){
			if (isset($this->_args['icon']) && !isset($this->_args['position'])){ 
				//Icon has been specified but position has not
				$menu_page = $page_function_name( $this->_args['title'], $this->_args['title'], 'manage_options', $this->_args['slug'], array( &$this, 'mp_core_render_page' ), $this->_args['icon']);
			}
			elseif (!isset($this->_args['icon']) && isset($this->_args['position'])){ 
				//Position has been specified but icon has not
				$menu_page = $page_function_name( $this->_args['title'], $this->_args['title'], 'manage_options', $this->_args['slug'], array( &$this, 'mp_core_render_page' ), NULL, $this->_args['position']);
			}
			elseif (isset($this->_args['icon']) && isset($this->_args['position'])){ 
				//Both Icon and position have been specified
				$menu_page = $page_function_name( $this->_args['title'], $this->_args['title'], 'manage_options', $this->_args['slug'], array( &$this, 'mp_core_render_page' ), $this->_args['icon'], $this->_args['position']);
			}
			else {
				//Neither icon nor position have been specified
				$menu_page = $page_function_name( $this->_args['title'], $this->_args['title'], 'manage_options', $this->_args['slug'], array( &$this, 'mp_core_render_page' ));
			}
		}
		//Call function 'add_submenu_page'
		elseif ($this->_args['type'] == 'submenu'){
			//Args if this is a 'submenu'
			$menu_page = $page_function_name( $this->_args['parent_slug'], $this->_args['title'], $this->_args['title'], 'manage_options', $this->_args['slug'], array( &$this, 'mp_core_render_page' ));
		//Call one of the administration menus funtions: 
		}else{
			//Basic page args
			$menu_page = $page_function_name( $this->_args['title'], $this->_args['title'], 'manage_options', $this->_args['slug'], array( &$this, 'mp_core_render_page' ) );
		}		
	}
	
	/**
	 * This function creates a function which is hooked into the render page to add a new tab
	 *
	 * @since mp_core 1.0
	 */
	function mp_core_new_tab($tab_title, $tab_slug) {
		
		$parent_slug = isset($this->_args['parent_slug']) ? $this->_args['parent_slug'] : NULL;
		
		$settings_slug = $this->_args['slug'];
		
		/**
		* Display tab at top of Theme Options page
		*/
		$mp_core_display_tab_title = function ($active_tab) use ($settings_slug, $tab_title, $tab_slug, $parent_slug){ 
			
			//Set tab link based on whether there is a parent_slug
			if (empty($parent_slug)){
				//This is not a submenu
				$tab_link = '?page=' . $settings_slug . '&tab=' . $settings_slug . '_' . $tab_slug;
			}else{
				//This is a submenu
				$question_char = strrpos($parent_slug, "?");
				if ($question_char === false) { // note: three equal signs
					$tab_link = get_admin_url() . $parent_slug . '?page=' . $settings_slug . '&tab=' . $settings_slug . '_' . $tab_slug;
				}else{
					$tab_link = get_admin_url() . $parent_slug . '&page=' . $settings_slug . '&tab=' . $settings_slug . '_' . $tab_slug;
				}
			}
			
			//Add this tab to the page
			if ($active_tab == $settings_slug . '_' . $tab_slug){ $active_class = 'nav-tab-active'; }else{$active_class = "";}
			echo ('<a href="' . $tab_link . '" class="nav-tab ' . $active_class . '">' . $tab_title . '</a>');
		};
		add_action( $this->_args['slug'] . '_new_tab_hook', $mp_core_display_tab_title );

	}
	
	/**
	 * Renders the Theme Options administration screen.
	 *
	 * @since mp_core 1.0
	 */
	public function mp_core_render_page() {
		?>
		<div class="wrap">
			<?php 
			//Show screen icon if this is not a menu, object, utility, or submenu page
			if ( $this->_args['type'] != 'menu' && $this->_args['type'] != 'object' && $this->_args['type'] != 'utility' && $this->_args['type'] != 'submenu' ){screen_icon();} 
			//settings_errors is already called on the options page so don't call it if this is an options page
			$this->_args['type'] == 'options' ? '' : settings_errors(); 
			//set the active tab to the one set in the URL. If there isn't one set in the URL, set it to be the slug + _general
			$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : $this->_args['slug'] . '_general'; ?>
		 
			<h2 class="nav-tab-wrapper">  
				<?php do_action($this->_args['slug'] . '_new_tab_hook', $active_tab); ?>  
			</h2>  
	
			<form method="post" action="options.php">
				<?php
				
					/**
					* Check Permissions
					*/
					if ( !current_user_can( 'manage_options' ) )  {
						wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
					}
	
					/**
					* Display the options for the active tab
					*/
					settings_fields( $active_tab );
					do_settings_sections( $active_tab );
		
					submit_button();
				?>
			</form>
		</div>
		<?php
	}
}

/* Fields ***************************************************************/
/**
 * Number Field
 *
 * @since mp_core 1.0
 */
function mp_core_number( $args = array() ) {
	$defaults = array(
		'menu'        => '', 
		'min'         => 1,
		'max'         => 9999999999999999, //<- this should have a filter added
		'step'        => 1,
		'name'        => '',
		'value'       => '',
		'description' => '',
		'registration' => '' ,
	);
	
	$args = wp_parse_args( $args, $defaults );
	extract( $args );
	
	$id   = esc_attr( $name );
	$name = esc_attr( sprintf( $registration . '[%s]', $name ) );
?>
	<label for="<?php echo esc_attr( $id ); ?>">
		<input type="number" min="<?php echo absint( $min ); ?>" max="<?php echo absint( $max ); ?>" step="<?php echo absint( $step ); ?>" name="<?php echo $name; ?>" id="<?php echo $id ?>" value="<?php echo esc_attr( $value ); ?>" />
		<?php echo $description; ?>
	</label>
<?php
} 

/**
 * Textarea Field
 *
 * @since mp_core 1.0
 */
function mp_core_textarea( $args = array() ) {
	$defaults = array(
		'name'        => '',
		'value'       => '',
		'description' => '',
		'registration' => '' ,
	);
	
	$args = wp_parse_args( $args, $defaults );
	extract( $args );
	
	$id   = esc_attr( $name );
	$name = esc_attr( sprintf( $registration . '[%s]', $name ) );
?>
	<label for="<?php echo $id; ?>">
		<textarea name="<?php echo $name; ?>" id="<?php echo $id; ?>" class="code large-text" rows="3" cols="30"><?php echo esc_textarea( $value ); ?></textarea>
		<br />
		<?php echo $description; ?>
	</label>
<?php
} 

/**
 * Tiny MCE editor Field
 *
 * @since mp_core 1.0
 */
function mp_core_wp_editor( $args = array() ) {
	$defaults = array(
		'name'        => '',
		'value'       => '',
		'description' => '',
		'registration' => '' ,
	);
	
	$args = wp_parse_args( $args, $defaults );
	extract( $args );
	
	$id   = esc_attr( $name );
	$name = esc_attr( sprintf( $registration . '[%s]', $name ) );

	?><label for="<?php echo $id; ?>"><?php
		
		wp_editor( html_entity_decode($value) , $name, $settings = array('textarea_rows' => 5));
		
		echo $description; ?>
		
	</label><?php

} 


/**
 * Image Upload Field
 *
 * @since mp_core 1.0
 */
function mp_core_mediaupload( $args = array() ) {
	$defaults = array(
		'name'        => '',
		'value'       => '',
		'description' => '',
		'registration' => '' ,
	);
	
	$args = wp_parse_args( $args, $defaults );
	extract( $args );
	
	$id   = esc_attr( $name );
	$name = esc_attr( sprintf( $registration . '[%s]', $name ) );
	
	if (isset($_REQUEST['file'])){
			$value = wp_get_attachment_url( $_REQUEST['file'] );
	}

	echo '<label for="' . $id . '">';?>       
		       
        <!-- Upload button and text field -->
        <div class="mp_media_upload">
            <input class="custom_media_url" id="<?php echo $id; ?>" type="text" name="<?php echo $name; ?>" value="<?php echo esc_attr( $value ); ?>" style="margin-bottom:10px; clear:right;">
			<a href="#" class="button custom_media_upload"><?php _e('Upload', 'mp_core'); ?></a>
        </div>
		
		<?php
		//Image thumbnail
		if (isset($value)){
			$ext = pathinfo($value, PATHINFO_EXTENSION);
			if ($ext == 'png' || $ext == 'jpg'){
				?><img class="custom_media_image" src="<?php echo $value; ?>" style="max-width:30px; display:inline-block;" /><?php
			}else{
				?><img class="custom_media_image" src="<?php echo $value; ?>" style="max-width:30px; display: none;" /><?php
			}
		}
	echo '</label>';   
} 

/**
 * Textbox Field
 *
 * @since mp_core 1.0
 */
function mp_core_textbox( $args = array() ) {
	
	$defaults = array(
		'name'        => '',
		'value'       => '',
		'description' => '',
		'registration' => '' ,
	);
	
	$args = wp_parse_args( $args, $defaults );
	extract( $args );
	
	$id   = esc_attr( $name );
	$name = esc_attr( sprintf( $registration . '[%s]', $name ) );
?>
	<label for="<?php echo $id; ?>">
		<input type="text" id="<?php echo $id; ?>" name="<?php echo $name; ?>" value="<?php echo esc_attr( $value ); ?>">
		<br /><?php echo $description; ?>
	</label>
<?php
} 

/**
 * Email Field
 *
 * @since mp_core 1.0
 */
function mp_core_email( $args = array() ) {
	
	$defaults = array(
		'name'        => '',
		'value'       => '',
		'description' => '',
		'registration' => '' ,
	);
	
	$args = wp_parse_args( $args, $defaults );
	extract( $args );
	
	$id   = esc_attr( $name );
	$name = esc_attr( sprintf( $registration . '[%s]', $name ) );
?>
	<label for="<?php echo $id; ?>">
		<input type="email" id="<?php echo $id; ?>" name="<?php echo $name; ?>" value="<?php echo esc_attr( $value ); ?>">
		<br /><?php echo $description; ?>
	</label>
<?php
} 


/**
 * Checkbox Field
 *
 * @since mp_core 1.0
 */
function mp_core_checkbox( $args = array() ) {
	
	$defaults = array(
		'name'        => '',
		'value'       => '',
		'description' => '',
		'registration' => '',
		'checked_by_default' => ''
	);
	
	$args = wp_parse_args( $args, $defaults );
	extract( $args );
	
	$id   = esc_attr( $name );
	$null_name = esc_attr( sprintf( $registration . '[%s]', $name . '_null' ) );
	$null_value = mp_core_get_option( $registration,  $name . '_null' );
	$value = empty( $null_value ) && $checked_by_default == 'true' ? $name : $value;
	$name = esc_attr( sprintf( $registration . '[%s]', $name ) );
	
?>
	<label for="<?php echo $id; ?>">
		<input type="checkbox" id="<?php echo $id; ?>" name="<?php echo $name; ?>" value="<?php echo esc_attr( $name ); ?>" <?php echo empty($value) ? '' : 'checked'; ?>>
        <!--This null field exists for the situation where a checkbox is the only value on a page and is saved with it being un-checked - the null field gives it something to save -->
        <input type="hidden" id="<?php echo $id; ?>_null" name="<?php echo $null_name; ?>" value="<?php echo esc_attr( $name ); ?>_null">
		<br /><?php echo $description; ?>
	</label>
<?php
} 

/**
 * Radio Field
 *
 * @since mp_core 1.0
 */
function mp_core_radio( $args = array() ) {
	$defaults = array(
		'name'        => '',
		'value'       => '',
		'options'     => array(),
		'description' => '',
		'registration' => '' ,
	);
	
	$args = wp_parse_args( $args, $defaults );
	extract( $args );
	
	$id   = esc_attr( $name );
	$name = esc_attr( sprintf( $registration . '[%s]', $name ) );
?>
	<?php foreach ( $options as $option_id => $option_label ) : ?>
	<label title="<?php echo esc_attr( $option_label ); ?>">
		<input type="radio" name="<?php echo $name; ?>" value="<?php echo $option_id; ?>" <?php checked( $option_id, $value ); ?>>
		<?php echo esc_attr( $option_label ); ?>
	</label>
		<br />
	<?php endforeach; ?>
<?php
}

/**
 * Select Field
 *
 * @since mp_core 1.0
 */
function mp_core_select( $args = array() ) {
	$defaults = array(
		'name'        => '',
		'value'       => '',
		'options'     => array(),
		'description' => '',
		'registration' => '' 
	);
	
	$args = wp_parse_args( $args, $defaults );
	extract( $args );
	
	$id   = esc_attr( $name );
	$name = esc_attr( sprintf( $registration . '[%s]', $name ) );
	?>
	<label for="<?php echo $id; ?>">
		<select name="<?php echo $name; ?>">
        	<option value="null">
			<?php foreach ( $options as $option_id => $option_label ) : ?>
			<option value="<?php echo esc_attr( $option_id ); ?>" <?php selected( $option_id, $value ); ?>>
				<?php echo esc_attr( $option_label ); ?>
			</option>
			<?php endforeach; ?>
		</select>
		<?php echo $description; ?>
	</label>
<?php
}

/**
 * Color Picker
 *
 * @since mp_core 1.0
 */
function mp_core_colorpicker($args = array() ) {
	$defaults = array(
		'name'        => '',
		'value'       => '',
		'description' => '',
		'registration' => '' 
	);
	
	$args = wp_parse_args( $args, $defaults );
	extract( $args );
	
	$id   = esc_attr( $name );
	$name = esc_attr( sprintf( $registration . '[%s]', $name ) );
	?>
	<div class="color-picker">
		<input type="text" class="of-color" id="<?php echo $id; ?>" name="<?php echo $name; ?>" value="<?php echo esc_attr( $value ); ?>" size="25" />
		<?php echo $description; ?>
		
	</div>
	<?php
}

/**
 * Light
 *
 * @since mp_core 1.0
 */
function mp_core_true_false_light($args = array() ) {
	$defaults = array(
		'name'        => '',
		'value'       => '',
		'description' => '',
	);
	
	$args = wp_parse_args( $args, $defaults );
	extract( $args );
	
	$class = $value == true ? 'mp-core-green-light' : 'mp-core-red-light';
	
	?>
	<div class="mp-core-true-false-light">
		<div class="<?php echo $class; ?>"></div>
		<?php echo $description; ?>
	</div>
	<?php
}

/* Helpers ***************************************************************/

function mp_core_get_all_pages() {
	$output = array();
	$terms = get_pages(); 
	
	foreach ( $terms as $term ) {
		$output[ $term->ID ] = $term->post_title;
	}
	
	return $output;
}

function mp_core_get_all_cpt($slug) {
	
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

function mp_core_get_all_tax($slug) {
	if (taxonomy_exists($slug)){
		$output = array();
		$terms  = get_terms( array( $slug ), array( 'hide_empty' => 0 ) );
		foreach ( $terms as $term ) {
			$output[ $term->term_id ] = $term->name;
		}
		
		return $output;
	}
}

function mp_core_get_posts_by_cat($catid){
	$category_query_args = array(
		'cat' => $catid
	);
	
	$category_query = new WP_Query( $category_query_args );	
	
	if ( $category_query->have_posts() ) {
		 while ( $category_query->have_posts() ) { 
		 	 $category_query->the_post();
			// Loop output goes here
			$return_array[get_the_ID()] = get_the_title(); 
			
		 }
	}
	
	return $return_array;
}

/**
 * Returns the options array 
 * The $registration variable must match the name of the set of options. It is set in the register_settings function. 
 * If the $key variable is set, it will return just that setting. If not, it will return the entire set of settings as an array.
 *
 * @since mp_core 1.0
 */
function mp_core_get_option($registration, $key='') {
	$saved = (array) get_option( $registration );	
	$defaults = array();
	if (array_key_exists('0', $saved) ){ 
		//These options have never been saved so set them to be empty
		$saved = ""; 
	}else{ 
		//Set each key in the array to have a default setting of '';
		foreach ($saved as $keyname => $setting){
				$defaults[$keyname] = '';
		}
	}
	
	$defaults = apply_filters( $registration . 'default', $defaults );
	
	$options = wp_parse_args( $saved, $defaults );
	
	$options = array_intersect_key( $options, $defaults );
	
	//Return a single option if the key has been set
	if ($key != '') {
		if (isset($options[ $key ])){
			return html_entity_decode($options[ $key ]);
		}else{
			return '';	
		}
	}else{
		return $options;
	}
}

/**
 * Sanitize and validate form input. Accepts an array, return a sanitized array.
 *
 * @param array $input Unknown values.
 * @return array Sanitized theme options ready to be stored in the database.
 *
 * @since Lighthouse 1.0
 */
function mp_core_settings_validate( $input ) {
	$output = array();
	
	$allowed_tags = array(
		'a' => array(
			'href' => array(),
			'title' => array()
		),
		'br' => array(),
		'em' => array(),
		'strong' => array()
	);
	
	if (isset($input)){
		foreach ($input as $key => $option){
			if ( isset ($option) ) {
				$output[ $key ] = wp_kses(htmlentities($option, ENT_QUOTES), $allowed_tags );
			}
			else{
				$output[ $key ] = '';
			}			
		}	
	}
	
	$output = wp_parse_args( $output, $input );	
	
	return apply_filters( 'mp_core_settings_validate', $output, $input );
}