<?php
/**
 * This class outputs a license form in the admin notices area of the site. 
 * Use the mp_core_listen_for_license_and_get_validity function to listen for it and save
 * 
 *
 * @author     Philip Johnston
 * @link       http://mintplugins.com/doc/
 * @since      1.0.0
 * @return     void
 */
 		
class MP_CORE_Show_License_Form_In_Notices{
				
		/**
		 * Constructor
		 *
		 * @access   public
		 * @since    1.0.0
		 * @link     http://mintplugins.com/doc/metabox-class/
		 * @author   Philip Johnston
		 * @see      MP_CORE_Show_License_Form_In_Notices::mp_core_add_metabox()
		 * @see      wp_parse_args()
		 * @see      sanitize_title()
		 * @param    array $args (required) See link for description.
		 * @return   void
		 */	
		public function __construct($args){
											
			//Set defaults for args		
			$args_defaults = array(
				'plugin_name' => NULL 
			);
			
			//Get and parse args
			$this->_args = wp_parse_args( $args, $args_defaults );
			
			$this->_plugin_name_slug = sanitize_title( $this->_args['plugin_name'] );
			$this->_license_key = get_option( $this->_plugin_name_slug . '_license_key' );
			
			//Show license form in the admin notices area
			add_action( 'admin_notices', array( $this, 'show_license_form_in_notices' ) );
			
		}
			
		/**
		 * Create License Input Form in the admin notices area for this plugin
		 *
		 * @since    1.0.0
		 * @return   void
		 */
		function show_license_form_in_notices(){
			
			?>
			<div id="<?php echo $this->_plugin_name_slug; ?>-plugin-license-wrap-in-notices" class="error wrap">
				
				<p class="plugin-description"><?php echo __( "Enter your license to complete installation of ", 'mp_core' ) . $this->_args['plugin_name']; ?></p>
				
				<form method="post">
								
					<input style="float:left; margin-right:10px;" id="<?php echo $this->_plugin_name_slug; ?>_license_key_in_notices" name="<?php echo $this->_plugin_name_slug; ?>_license_key" type="text" class="regular-text" value="<?php esc_attr_e( $this->_license_key ); ?>" />		
				   
					<?php echo mp_core_true_false_light( array( 'value' => false, 'description' => __('This license is not valid! ', 'mp_core') ) ); ?>
					
					<?php wp_nonce_field( $this->_plugin_name_slug . '_nonce', $this->_plugin_name_slug . '_nonce' ); ?>
					
					<div class="mp-core-clearedfix"></div>
					
					<?php submit_button(__('Submit License', 'mp_core') ); ?>
				
				</form>
			</div>
			
			<?php
		}
}