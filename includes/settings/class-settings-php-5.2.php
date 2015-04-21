<?php
/**
 * This file contains the MP_CORE_Settings class 
 *
 * @link http://mintplugins.com/doc/settings-class/
 * @since 1.0.0
 *
 * @package    MP Core
 * @subpackage Classes
 *
 * @copyright  Copyright (c) 2014, Mint Plugins
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @author     Philip Johnston
 */
 
/**
 * Class to create new options page
 *
 * This contains a call to register_setting(), registers a validation callback, mp_core_settings_validate(),
 * which is used when the option is saved, to ensure that our option values are properly
 * formatted, and safe.
 *
 * It also creates the page for settings and outputs the settings passed-in to the class.
 *
 * @author     Philip Johnston
 * @link http://mintplugins.com/doc/settings-class/
 * @since      1.0.0
 * @return     void
 */
 
class MP_CORE_Settings{
	
	protected $_args;
	protected $_settings_array = array();
	
	/**
	 * Constructor
	 *
	 * @access   public
	 * @since    1.0.0
	 * @link     http://mintplugins.com/doc/settings-class-args/
	 * @see      MP_CORE_Settings::mp_core_enqueue_scripts()
	 * @see      MP_CORE_Settings::mp_core_add_page()
	 * @see      wp_parse_args()
	 * @see      add_action()
	 * @param    array $args {
	 *      This array contains info for creating the directory page
	 *		@type string 'parent_slug' The slug name for the parent menu (or the file name of a standard WordPress admin page)
	 *		@type string 'title' This is the title of the page.
	 *		@type string 'slug' This will identify the page and should be all lowercase with no spaces.
	 *		@type string 'type' Tells WordPress where this page will sit in the WP menu. See link for details.
	 *		@type string 'icon' (optional) The url to the icon to be used for this menu
	 *		@type string 'position' (optional) The position in the menu order this menu should appear
	 * }
	 * @return   void
	 */
	public function __construct($args){
														
		//Set defaults for args		
		$args_defaults = array(
			'parent_slug' => NULL, 
			'title' => NULL, 
			'slug' => NULL, 
			'type' => NULL,
			'icon' => NULL,
    		'position' => NULL
		);
		
		//Get and parse args
		$this->_args = wp_parse_args( $args, $args_defaults );
			
		add_action( 'admin_enqueue_scripts', array( $this, 'mp_core_enqueue_scripts' ) );
		add_action( 'admin_menu', array( $this, 'mp_core_add_page')  );
	}
	
	/**
	 * Enqueue Scripts
	 *
	 * @access   public
	 * @since    1.0.0
	 * @see      get_current_screen()
	 * @see      wp_enqueue_style()
	 * @see      wp_enqueue_script()
	 * @see      wp_enqueue_media()
	 * @return   void
	 */
	public function mp_core_enqueue_scripts(){
		
		//Get current page
		$current_page = get_current_screen();
		
		//Only load if we are not on the nav menu page - where some of our scripts seem to be conflicting
		if ( $current_page->base != 'nav-menus' ){
			//mp_core_settings_css
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
	}
	
	/**
	 * Add our options page to the menu.
	 *
	 * This function is attached to the admin_menu action hook.
	 *
	 * @access   public
	 * @since    1.0.0
	 * @see      add_ TYPE _page() - http://codex.wordpress.org/Administration_Menus
	 * @return   void
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
	 * Renders a new tab on the settings page. This is called by the settings page.
	 *
	 * @access   public
	 * @since    1.0.0
	 * @see      mp_core_add_query_arg()
	 * @see      get_admin_url()
	 * @return   void
	 */
	public function new_tab( $active_tab, $tab_info ){
		
		//Get Parent Slug
		$parent_slug = isset($this->_args['parent_slug']) ? $this->_args['parent_slug'] : NULL;
		
		//If active tab is equal to the passd in tab slug, add the "active class" to the class atrribute
		$active_class = $active_tab == $this->_args['slug'] . '_' . $tab_info['slug'] ? 'nav-tab-active' : NULL;
		
		//Set tab link based on whether there is a parent_slug
		$tab_link = mp_core_add_query_arg( array('page' => $this->_args['slug'], 'tab' => $this->_args['slug'] . '_' . $tab_info['slug']), get_admin_url() . $parent_slug );
		
		//echo HTML for tab
		echo '<a href="' . $tab_link . '" class="nav-tab ' . $active_class . '">' . $tab_info['title'] . '</a>';
	}
		
	/**
	 * Renders the Theme Options administration screen.
	 *
	 * @access   public
	 * @since    1.0.0
	 * @see      do_action()
	 * @see      current_user_can()
	 * @see      wp_die()
	 * @see      settings_fields()
	 * @see      do_settings_sections()
	 * @see      submit_button()
	 * @return   void
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
 * Basic Text Field
 *
 * @access   public
 * @since    1.0.0
 * @see      wp_parse_args()
 * @see      esc_attr()
 * @see      absint()
 * @param    array $args
 * @return   void
 */
function mp_core_basictext( $args = array() ) {
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
		<div id="<?php echo $id ?>"><?php echo esc_attr( $value ); ?>
		<?php echo $description; ?>
        </div>
	</label>
<?php
} 

/**
 * Number Field
 *
 * @access   public
 * @since    1.0.0
 * @see      wp_parse_args()
 * @see      esc_attr()
 * @see      absint()
 * @param    array $args
 * @return   void
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
 * @access   public
 * @since    1.0.0
 * @see      wp_parse_args()
 * @see      esc_attr()
 * @see      esc_textarea()
 * @param    array $args
 * @return   void
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
 * @access   public
 * @since    1.0.0
 * @see      wp_parse_args()
 * @see      esc_attr()
 * @see      wp_editor()
 * @param    array $args
 * @return   void
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
 * @access   public
 * @since    1.0.0
 * @see      wp_parse_args()
 * @see      esc_attr()
 * @see      wp_get_attachment_url()
 * @param    array $args
 * @return   void
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
 * @access   public
 * @since    1.0.0
 * @see      wp_parse_args()
 * @see      esc_attr()
 * @param    array $args
 * @return   void
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
 * Password Field
 *
 * @access   public
 * @since    1.0.0
 * @see      wp_parse_args()
 * @see      esc_attr()
 * @param    array $args
 * @return   void
 */
function mp_core_password( $args = array() ) {
	
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
		<input type="password" id="<?php echo $id; ?>" name="<?php echo $name; ?>" value="<?php echo esc_attr( $value ); ?>">
		<br /><?php echo $description; ?>
	</label>
<?php
} 


/**
 * Email Field
 *
 * @access   public
 * @since    1.0.0
 * @see      wp_parse_args()
 * @see      esc_attr()
 * @param    array $args
 * @return   void
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
 * @access   public
 * @since    1.0.0
 * @see      wp_parse_args()
 * @see      esc_attr()
 * @param    array $args
 * @return   void
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
 * @access   public
 * @since    1.0.0
 * @see      wp_parse_args()
 * @see      esc_attr()
 * @see      checked()
 * @param    array $args
 * @return   void
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
 * Input Range Field
 *
 * @access   public
 * @since    1.0.0
 * @see      wp_parse_args()
 * @see      esc_attr()
 * @see      checked()
 * @param    array $args
 * @return   void
 */
function mp_core_input_range( $args = array() ) {
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
		<input type="range" name="<?php echo $name; ?>" value="<?php echo $option_id; ?>" min="1" max="100">
		<?php echo esc_attr( $option_label ); ?>
	</label>
		<br />
	<?php endforeach; ?>
<?php
}

/**
 * Select Field
 *
 * @access   public
 * @since    1.0.0
 * @see      wp_parse_args()
 * @see      esc_attr()
 * @see      selected()
 * @param    array $args
 * @return   void
 */
function mp_core_select( $args = array() ) {
	$defaults = array(
		'name'        => '',
		'value'       => '',
		'options'     => array(),
		'description' => '',
		'registration' => '' ,
		'use_labels' => false
	);
	
	$args = wp_parse_args( $args, $defaults );
	extract( $args );
	
	$id   = esc_attr( $name );
	$name = esc_attr( sprintf( $registration . '[%s]', $name ) );
	?>
	<label for="<?php echo $id; ?>">
		<select id="<?php echo $id; ?>" name="<?php echo $name; ?>">
			<option value=""></option>
			<?php foreach ( $options as $option_id => $option_label ) { 
				if ($use_labels){ $option_id = str_replace("-", "_", sanitize_title( $option_label ) ); }
			?>
			<option value="<?php echo esc_attr( $option_id ); ?>" <?php selected( $option_id, $value ); ?>>
				<?php echo esc_attr( $option_label ); ?>
			</option>
			<?php }; ?>
		</select>
		<?php echo $description; ?>
	</label>
<?php
}

/**
 * Color Picker
 *
 * @access   public
 * @since    1.0.0
 * @see      wp_parse_args()
 * @see      esc_attr()
 * @param    array $args
 * @return   void
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
 * Easy Digital Downloads Product 
 *
 * @access   public
 * @since    1.0.0
 * @see      wp_parse_args()
 * @see      esc_attr()
 * @param    array $args
 * @return   void
 */
function mp_core_edd_download_select( $args = array() ) {
	
	//If there is no EDD function - It's not installed so don't do anything with this.
	if (!function_exists( 'EDD' ) ){
		
		echo __( 'You need to install and activate Easy Digital Downloads', 'mp_core' );
		return false;
	}
	
	$defaults = array(
		'name'        => '',
		'value'       => '',
		'options'     => array(),
		'description' => '',
		'registration' => '' ,
		'use_labels' => false
	);
	
	$args = wp_parse_args( $args, $defaults );
	extract( $args );
	
	$id   = esc_attr( $name );
	$name = esc_attr( sprintf( $registration . '[%s]', $name ) );
	?>
	<label for="<?php echo $id; ?>">
		<?php echo EDD()->html->product_dropdown( array( 'id' => $id, 'name' => $name, 'chosen' => true, 'selected' => esc_attr( $value ) )); ?><br />
		<?php echo $description; ?>
	</label>
<?php
}
/**
 * Returns the options array 
 * The $registration variable must match the name of the set of options. It is set in the register_settings function. 
 * If the $key variable is set, it will return just that setting. If not, it will return the entire set of settings as an array.
 *
 * @access   public
 * @since    1.0.0
 * @see      wp_parse_args()
 * @see      esc_attr()
 * @param    string $registration - The registration slug for this option group
 * @param    string $key - (Optional) The slug of this actual option
 * @return   void
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
 * @access   public
 * @since    1.0.0
 * @see      wp_kses()
 * @see      wp_parse_args()
 * @see      apply_filters()
 * @param    array $input Unknown values.
 * @return   array Sanitized theme options ready to be stored in the database.
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