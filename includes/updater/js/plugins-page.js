jQuery(document).ready(function($){

  var mp_core_update_plugin_vars = eval('mp_core_update_plugin_vars' + global_plugin_update_num );

  if ( $( '[data-slug="' + mp_core_update_plugin_vars.name_slug + '"]' + ' .column-description' ).length != 0 ) {

	  $( $( '[data-slug="' + mp_core_update_plugin_vars.name_slug + '"]' + ' .column-description' ) ).append( $( '#' + mp_core_update_plugin_vars.name_slug + '-plugin-license-wrap' ));

	$( '#' + mp_core_update_plugin_vars.name_slug + '-plugin-license-wrap' ).css( 'display', 'block' );

  }

  global_plugin_update_num = global_plugin_update_num + 1;

  // When someone clicks the "Submit License" button on the plugins page.
  $( document ).on( 'click', '.' + mp_core_update_plugin_vars.name_slug + '-submit', function( event ){

	  var postData = {
		  action: 'mp_core_license_capture',
		  mp_core_verify_license_ajax_nonce_value: mp_core_update_plugin_vars.ajax_nonce_value,
		  software_name: mp_core_update_plugin_vars.name_slug,
		  software_api_url: mp_core_update_plugin_vars.api_url,
		  software_license_key: $( '[name="' + mp_core_update_plugin_vars.name_slug + '_license_key' + '"]' ).val(),
		  get_license_link: $( '#' + mp_core_update_plugin_vars.name_slug + '-plugin-license-wrap .mp-get-license-link' ).attr( 'href' )
	  }

	  //Ajax verify license
	  $.ajax({
		  type: "POST",
		  data: postData,
		  dataType:"json",
		  url: mp_core_update_plugin_vars.ajaxurl,
		  success: function (response) {

			  if ( response.success ){

				 //Update the license output to show if was verified or not
				 $( '#' + mp_core_update_plugin_vars.name_slug + '-plugin-license-wrap .mp-core-true-false-light' ).html(response.red_light_green_light_output);

			  }

		  }
	  }).fail(function (data) {
		  console.log(data);
	  });
  });

});
