jQuery(document).ready(function($){
  
  $('.of-color').wpColorPicker();
  
  $( document ).ajaxComplete( function(){
	 $('.of-color').wpColorPicker(); 
  });
	
});

