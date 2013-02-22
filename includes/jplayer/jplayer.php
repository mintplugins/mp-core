<?php
/**
 * Enqueue jPlayer script
 */
function mp_core_enqueue_jplayer_script(){
	//jplayer
	wp_enqueue_script('mp_jplayer', plugins_url('js/jquery.jplayer.min.js', dirname(__FILE__)),  array( 'jquery') );
	//jplayer playlist addon
	wp_enqueue_script('mp_jplayer_playlist', plugins_url('js/jplayer.playlist.min.js', dirname(__FILE__)),  array( 'jquery', 'mp_jplayer') );
	
	wp_enqueue_style('mp_jplayer_playlist', plugins_url('css/jplayer-skin.css', dirname(__FILE__)));
	
}
add_action('wp_enqueue_scripts', 'mp_core_enqueue_jplayer_script');

/**
 * Jquery for new player
 */
function mp_core_jplayer($post_id, $slug, $single = true){
	
	//Get this repeater or single mp3
	$mp3s = get_post_meta( $post_id, $key = $slug, $single = true );
	
	/**
	 * Output Jquery and HTML for new player
	 */
	 
	?>
	<script type="text/javascript">

	//<![CDATA[
	
	jQuery(document).ready(function(){
	
		new jPlayerPlaylist({
	
			jPlayer: "#<?php echo $post_id; ?>_jquery_jplayer_1",
	
			cssSelectorAncestor: "#<?php echo $post_id; ?>_jp_container_1"
	
		}, [
	
		<?php 
		
		if ($single == true){ ?>
			{
				title:"<?php the_title($post_id); ?>",
				mp3:"<?php echo $mp3s; ?>",
			}, <?php
		}
		else{
			foreach ($mp3s as $mp3){ ?>
					{
						title:"<?php echo $mp3['mp3_title']; ?>",
						mp3:"<?php echo $mp3['mp3_file']; ?>",
					},<?php
			 } 
		}
		
		?>
	
		], {
			swfPath: "<?php echo plugins_url( 'jplayer', dirname(__FILE__)); ?>",
			supplied: "mp3",
			wmode: "window"
		});
	
	});
	
	//]]>
	
	</script>
	
    <div id="<?php echo $post_id; ?>_jquery_jplayer_1" class="jp-jplayer"></div>



		<div id="<?php echo $post_id; ?>_jp_container_1" class="jp-audio">

			<div class="jp-type-playlist">

				<div class="jp-gui jp-interface">

					<ul class="jp-controls">

						<li><a href="javascript:;" class="jp-previous" tabindex="1">previous</a></li>

						<li><a href="javascript:;" class="jp-play" tabindex="1">play</a></li>

						<li><a href="javascript:;" class="jp-pause" tabindex="1">pause</a></li>

						<li><a href="javascript:;" class="jp-next" tabindex="1">next</a></li>

						<li><a href="javascript:;" class="jp-stop" tabindex="1">stop</a></li>

						<li><a href="javascript:;" class="jp-mute" tabindex="1" title="mute">mute</a></li>

						<li><a href="javascript:;" class="jp-unmute" tabindex="1" title="unmute">unmute</a></li>

						<li><a href="javascript:;" class="jp-volume-max" tabindex="1" title="max volume">max volume</a></li>

					</ul>

					<div class="jp-progress">

						<div class="jp-seek-bar">

							<div class="jp-play-bar"></div>



						</div>

					</div>

					<div class="jp-volume-bar">

						<div class="jp-volume-bar-value"></div>

					</div>

					<div class="jp-current-time"></div>

					<div class="jp-duration"></div>

					<ul class="jp-toggles">

						<li><a href="javascript:;" class="jp-shuffle" tabindex="1" title="shuffle">shuffle</a></li>

						<li><a href="javascript:;" class="jp-shuffle-off" tabindex="1" title="shuffle off">shuffle off</a></li>

						<li><a href="javascript:;" class="jp-repeat" tabindex="1" title="repeat">repeat</a></li>

						<li><a href="javascript:;" class="jp-repeat-off" tabindex="1" title="repeat off">repeat off</a></li>

					</ul>

				</div>

				<div class="jp-playlist">

					<ul>

						<li></li>

					</ul>

				</div>

				<div class="jp-no-solution">

					<span>Update Required</span>

					To play the media you will need to either update your browser to a recent version or update your <a href="http://get.adobe.com/flashplayer/" target="_blank">Flash plugin</a>.

				</div>

			</div>

		</div>

    <?php
}