<?php if(get_post_meta(get_the_ID(), "edgtf_post_audio_link_meta", true) !== ""){ ?>
	<div class="edgtf-blog-audio-holder">
		<audio class="edgtf-blog-audio" src="<?php echo esc_url(get_post_meta(get_the_ID(), "edgtf_post_audio_link_meta", true)) ?>" controls="controls">
			<?php esc_html_e("Your browser don't support audio player","walker"); ?>
		</audio>
	</div>
<?php } ?>