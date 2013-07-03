<?php
//Front end scripts
function mp_core_enqueue_scripts(){
 
	//Animate CSS
	wp_enqueue_style( 'mp_core_animate_css', plugins_url( 'css/animate-custom.css', dirname(__FILE__) ) );
		
}
add_action( 'wp_enqueue_scripts', 'mp_core_enqueue_scripts' );