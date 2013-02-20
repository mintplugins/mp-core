<?php
//Podcast Feed URL
function wpfc_podcast_url($feed_type = false){ 
	if ($feed_type == false){
		//return URL to feed page
		return home_url() . '/feed/podcast'; 
	}
	else{
		//return URL to itpc itunes-loaded feed page
		$itunes_url = str_replace("http", "itpc", home_url() );
		return $itunes_url . '/feed/podcast';
	}
}