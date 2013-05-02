<?php

function mp_core_enqueue_theme_specific_css(){
	wp_enqueue_style( 'mp_core_the_content_css', plugins_url('css/the-content.css', dirname(__FILE__)) );
}
add_action( 'wp_enqueue_scripts', 'mp_core_enqueue_theme_specific_css' );