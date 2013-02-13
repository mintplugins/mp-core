<?php
/**
 * Customize
 *
 * Theme options are lame! Manage any customizations through the Theme
 * Customizer. Expose the customizer in the Appearance panel for easy access.
 *
 * @package mp_core
 * @since mp_core 1.0
 */

/**
 * Expose a "Customize" link in the main admin menu.
 *
 * By default, the only way to access a theme customizer is via
 * the themes.php page, which is totally lame.
 *
 * @since mp_core 1.0
 *
 * @return void
 */
function mp_core_customize_menu() {
    add_theme_page( __( 'Customize', 'mp_core' ), __( 'Customize', 'mp_core' ), 'edit_theme_options', 'customize.php' );
}
add_action ( 'admin_menu', 'mp_core_customize_menu' );

/**
 * Get Theme Mod
 *
 * Instead of options, customizations are stored/accessed via Theme Mods
 * (which are still technically settings). This wrapper provides a way to
 * check for an existing mod, or load a default in its place.
 *
 * @since mp_core 1.0
 *
 * @param string $key The key of the theme mod to check. Prefixed with 'mp_core_'
 * @return mixed The theme modification setting
 */
function mp_core_theme_mod( $key ) {
	$defaults = mp_core_get_theme_mods();
	$mod      = get_theme_mod( $key, $defaults[ $key ] );

	return apply_filters( 'mp_core_theme_mod_' . $key, $mod );
}

/**
 * Default theme customizations.
 *
 * @since mp_core 1.0
 *
 * @return $options an array of default theme options
 */
function mp_core_get_theme_mods() {
	$defaults = array(
		'mp_core_site_title'                 => '',
		'mp_core_hero_description'           => '',
		'mp_core_hero_appstore_link'         => '',
		'mp_core_hero_playstore_link'        => '',
		'mp_core_hero_image'                 => '',
		'mp_core_hero_text_color'            => '#ffffff',
		'mp_core_hero_background_color'      => '#17649a',
		'mp_core_hero_background_image'      => '',
		'mp_core_hero_background_repeat'     => 'no-repeat',
		'mp_core_hero_background_position'   => 'center',
		'mp_core_footer_twitter'             => '',
		'mp_core_footer_facebook'            => '',
		'mp_core_footer_image'               => ''
	);

	$options = wp_parse_args( $saved, $defaults );
	$options = array_intersect_key( $options, $defaults );

	return $options;
}

/**
 * Hero Customization
 *
 * Register settings and controls for customizing the "Hero" section
 * of the theme. This includes title, description, images, colors, etc.
 *
 * @since mp_core 1.0
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 * @return void
 */
function mp_core_customize_register_hero( $wp_customize ) {
	$theme = wp_get_theme();

	$wp_customize->add_section( 'mp_core_hero', array(
		'title'      => sprintf( __( '%s Hero Unit', 'mp_core' ), $theme->Name ),
		'priority'   => 95,
	) );

	/** Title */
	$wp_customize->add_setting( 'mp_core_site_title', array(
		'default'    => mp_core_theme_mod( 'mp_core_site_title' )
	) );

	$wp_customize->add_control( 'mp_core_site_title', array(
		'label'      => __( 'Title', 'mp_core' ),
		'section'    => 'mp_core_hero',
		'settings'   => 'mp_core_site_title',
		'type'       => 'text',
		'priority'   => 10
	) );

	/** Description */
	$wp_customize->add_setting( 'mp_core_hero_description', array(
		'default'    => mp_core_theme_mod( 'hero_description' )
	) );

	$wp_customize->add_control( new mp_core_Customize_Textarea_Control( $wp_customize, 'mp_core_hero_description', array(
		'label'      => __( 'Introduction Paragraph', 'mp_core' ),
		'section'    => 'mp_core_hero',
		'settings'   => 'mp_core_hero_description',
		'type'       => 'textarea',
		'priority'   => 20
	) ) );

	/** App Store Link */
	$wp_customize->add_setting( 'mp_core_hero_appstore_link', array(
		'default'    => ''
	) );

	$wp_customize->add_control( 'mp_core_hero_appstore_link', array(
		'label'      => __( 'App Store Link', 'mp_core' ),
		'section'    => 'mp_core_hero',
		'settings'   => 'mp_core_hero_appstore_link',
		'type'       => 'text',
		'priority'   => 30
	) );

	/** Play Store Link */
	$wp_customize->add_setting( 'mp_core_hero_playstore_link', array(
		'default'    => ''
	) );

	$wp_customize->add_control( 'mp_core_hero_playstore_link', array(
		'label'      => __( 'Play Store Link', 'mp_core' ),
		'section'    => 'mp_core_hero',
		'settings'   => 'mp_core_hero_playstore_link',
		'type'       => 'text',
		'priority'   => 35
	) );

	/** Image */
	$wp_customize->add_setting( 'mp_core_hero_image', array(
		'default'    => mp_core_theme_mod( 'hero_image' ),
	) );

	$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'mp_core_hero_image', array(
		'label'      => __( 'App Image', 'mp_core' ),
		'section'    => 'mp_core_hero',
		'settings'   => 'mp_core_hero_image',
		'priority'   => 40
	) ) );

	/** Text Color */
	$wp_customize->add_setting( 'mp_core_hero_text_color', array(
		'default'    => mp_core_theme_mod( 'hero_text_color' ),
	) );

	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'mp_core_hero_text_color', array(
		'label'      => __( 'Hero Text Color', 'mp_core' ),
		'section'    => 'colors',
		'settings'   => 'mp_core_hero_text_color',
		'priority'   => 30
	) ) );

	/** Background Color */
	$wp_customize->add_setting( 'mp_core_hero_background_color', array(
		'default'    => mp_core_theme_mod( 'hero_background_color' ),
	) );

	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'mp_core_hero_background_color', array(
		'label'      => __( 'Hero Background Color', 'mp_core' ),
		'section'    => 'colors',
		'settings'   => 'mp_core_hero_background_color',
		'priority'   => 40
	) ) );

	/** Background Image */
	$wp_customize->add_setting( 'mp_core_hero_background_image', array(
		'default'        => mp_core_theme_mod( 'hero_background_image' )
	) );

	$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'mp_core_hero_background_image', array(
		'label'          => __( 'Background Image', 'mp_core' ),
		'section'        => 'mp_core_hero',
		'settings'       => 'mp_core_hero_background_image',
		'priority'       => 50
	) ) );

	$wp_customize->add_setting( 'mp_core_hero_background_repeat', array(
		'default'        => mp_core_theme_mod( 'hero_background_repeat' )
	) );

	$wp_customize->add_control( 'mp_core_hero_background_repeat', array(
		'label'      => __( 'Background Repeat' ),
		'section'    => 'mp_core_hero',
		'type'       => 'radio',
		'choices'    => array(
			'no-repeat'  => __('No Repeat'),
			'repeat'     => __('Tile'),
			'repeat-x'   => __('Tile Horizontally'),
			'repeat-y'   => __('Tile Vertically'),
		),
		'priority'       => 60
	) );

	$wp_customize->add_setting( 'mp_core_hero_background_position', array(
		'default'        => mp_core_theme_mod( 'hero_background_position' )
	) );

	$wp_customize->add_control( 'mp_core_hero_background_position', array(
		'label'      => __( 'Background Position' ),
		'section'    => 'mp_core_hero',
		'type'       => 'radio',
		'choices'    => array(
			'left'       => __('Left'),
			'center'     => __('Center'),
			'right'      => __('Right'),
		),
		'priority'       => 70
	) );

	do_action( 'mp_core_customize_hero', $wp_customize );

	return $wp_customize;
}
add_action( 'customize_register', 'mp_core_customize_register_hero' );

/**
 * Footer Customization
 *
 * Register settings and controls for customizing the Footer section
 * of the theme.
 *
 * @since mp_core 1.0
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 * @return void
 */
function mp_core_customize_register_footer( $wp_customize ) {
	$theme = wp_get_theme();

	$wp_customize->add_section( 'mp_core_footer', array(
		'title'      => sprintf( __( '%s Footer', 'mp_core' ), $theme->Name ),
		'priority'   => 105,
	) );

	/** Twitter */
	$wp_customize->add_setting( 'mp_core_footer_twitter', array(
		'default'    => mp_core_theme_mod( 'footer_twitter' )
	) );

	$wp_customize->add_control( 'mp_core_footer_twitter', array(
		'label'      => __( 'Twitter URL', 'mp_core' ),
		'section'    => 'mp_core_footer',
		'settings'   => 'mp_core_footer_twitter',
		'type'       => 'text',
		'priority'   => 10
	) );

	/** Facebook */
	$wp_customize->add_setting( 'mp_core_footer_facebook', array(
		'default'    => mp_core_theme_mod( 'footer_facebook' )
	) );

	$wp_customize->add_control( 'mp_core_footer_facebook', array(
		'label'      => __( 'Facebook URL', 'mp_core' ),
		'section'    => 'mp_core_footer',
		'settings'   => 'mp_core_footer_facebook',
		'type'       => 'text',
		'priority'   => 20
	) );

	/** Image */
	$wp_customize->add_setting( 'mp_core_footer_image', array(
		'default'    => mp_core_theme_mod( 'footer_image' ),
	) );

	$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'mp_core_footer_image', array(
		'label'      => __( 'Footer Logo', 'mp_core' ),
		'section'    => 'mp_core_footer',
		'settings'   => 'mp_core_footer_image',
		'priority'   => 30
	) ) );

	do_action( 'mp_core_customize_footer', $wp_customize );

	return $wp_customize;
}
add_action( 'customize_register', 'mp_core_customize_register_footer' );

/**
 * Textarea Control
 *
 * Attach the custom textarea control to the `customize_register` action
 * so the WP_Customize_Control class is initiated.
 *
 * @since mp_core 1.0
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 * @return void
 */
function mp_core_customize_textarea_control($wp_customize) {
	/**
	 * Textarea Control
	 *
	 * @since CLoudify 1.0
	 */
	class mp_core_Customize_Textarea_Control extends WP_Customize_Control {
		public $type = 'textarea';

		public function render_content() {
	?>
		<label>
			<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
			<textarea rows="8" style="width:100%;" <?php $this->link(); ?>><?php echo esc_textarea( $this->value() ); ?></textarea>
		</label>
	<?php
		}
	} 
}
add_action( 'customize_register', 'mp_core_customize_textarea_control', 1, 1 );

/**
 * Add postMessage support for all default fields, as well
 * as the site title and desceription for the Theme Customizer.
 *
 * @since mp_core 1.0
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 * @return void
 */
function mp_core_customize_register_transport( $wp_customize ) {
	$transport = array_merge( array( 'blogname' => '', 'blogdescription' => '' ), mp_core_get_theme_mods() );

	foreach ( $transport as $key => $default ) {
		$wp_customize->get_setting( $key )->transport = 'postMessage';
	}
}
add_action( 'customize_register', 'mp_core_customize_register_transport' );

/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 *
 * @since mp_core 1.0
 */
function mp_core_customize_preview_js() {
	wp_enqueue_script( 'customizer_js', get_template_directory_uri() . '/js/theme-customizer.js', array( 'customize-preview' ), '20120101.3', true );
}
add_action( 'customize_preview_init', 'mp_core_customize_preview_js' );

/**
 * Any CSS customizations we make need to be outputted in the document <head>
 * This does that.
 *
 * @since mp_core 1.0
 *
 * @return void
 */
function mp_core_header_css() {
?>
	<style>
		#iphone .text, 
		#iphone .text h1 {
			color: <?php echo mp_core_theme_mod( 'hero_text_color' ); ?>;
		}
	</style>

	<style id="cloudify-hero-custom-background-css">
		#masthead {
			background-color: <?php echo mp_core_theme_mod( 'hero_background_color' ); ?>;
			<?php if ( mp_core_theme_mod( 'hero_background_image' ) ) : ?>
			background-image: url(<?php echo esc_url( mp_core_theme_mod( 'hero_background_image' ) ); ?>);
			background-repeat: <?php echo mp_core_theme_mod( 'hero_background_repeat' ); ?>;
			background-position-x: <?php echo mp_core_theme_mod( 'hero_background_position' ); ?>;
			<?php endif; ?>
		}
	</style>
<?php
}
add_action( 'wp_head', 'mp_core_header_css' );