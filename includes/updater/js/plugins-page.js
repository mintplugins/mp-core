jQuery(document).ready(function($){
	
  var mp_core_update_plugin_vars = eval('mp_core_update_plugin_vars' + global_plugin_update_num );
  
  if ($( '#' + mp_core_update_plugin_vars.name_slug + ' .column-description' ).length != 0 ) {
 
  	$( '#' + mp_core_update_plugin_vars.name_slug + ' .column-description' ).append($( '#' + mp_core_update_plugin_vars.name_slug + '-plugin-license-wrap' ));
	
	$( '#' + mp_core_update_plugin_vars.name_slug + '-plugin-license-wrap' ).css( 'display', 'block' );
	
  }
 
  global_plugin_update_num = global_plugin_update_num + 1;
  
});

