<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <?php
    /**
     * walker_edge_header_meta hook
     *
     * @see walker_edge_header_meta() - hooked with 10
     * @see edgt_user_scalable_meta() - hooked with 10
     */
    do_action('walker_edge_header_meta');

    wp_head(); ?>
</head>
<body <?php body_class();?> itemscope itemtype="http://schema.org/WebPage">
	<?php 
		if(walker_edge_options()->getOptionValue('smooth_page_transitions') == "yes") {
			$ajax_class = 'edgtf-mimic-ajax';
		?>
		<div class="edgtf-smooth-transition-loader <?php echo esc_attr($ajax_class); ?>">
		    <div class="edgtf-st-loader">
		        <div class="edgtf-st-loader1">
		            <?php walker_edge_loading_spinners(); ?>
		        </div>
		    </div>
		</div>
	<?php } ?>
	<div class="edgtf-wrapper edgtf-404-page">
	    <div class="edgtf-wrapper-inner">
			<div class="edgtf-content" <?php walker_edge_content_elem_style_attr(); ?>>
	            <?php 
	            	$logo_image = walker_edge_options()->getOptionValue('logo_image');
			        $logo_dimensions = walker_edge_get_image_dimensions($logo_image);

			        $logo_height = '';
			        $logo_styles = '';
			        if(is_array($logo_dimensions) && array_key_exists('height', $logo_dimensions)) {
			            $logo_height = $logo_dimensions['height'];
			            $logo_styles = 'height: '.intval($logo_height / 2).'px;';
			        }
	            ?>
	            <?php if(!empty($logo_image)) { ?>
		            <div class="edgtf-404-logo">
					    <a itemprop="url" href="<?php echo esc_url(home_url('/')); ?>" <?php walker_edge_inline_style($logo_styles); ?>>
					        <img itemprop="image" class="edgtf-normal-logo" src="<?php echo esc_url($logo_image); ?>" alt="<?php esc_html_e('logo','walker'); ?>" />
					    </a>
					</div>
				<?php } ?>	
	            <div class="edgtf-content-inner">
					<div class="edgtf-page-not-found">
						<h1><span>
							<?php if(walker_edge_options()->getOptionValue('404_title')){
								echo esc_html(walker_edge_options()->getOptionValue('404_title'));
							} else {
								esc_html_e('PAGE NOT FOUND', 'walker');
							} ?>
						</span></h1>
						<h3><span>
							<?php if(walker_edge_options()->getOptionValue('404_subtitle')){
								echo esc_html(walker_edge_options()->getOptionValue('404_subtitle'));
							} else {
								esc_html_e('Oops! The page you are looking for does not exist. It might have been moved or deleted.', 'walker');
							} ?>
						</span></h3>
						<?php
							$params = array();
							if (walker_edge_options()->getOptionValue('404_back_to_home')){
								$params['text'] = walker_edge_options()->getOptionValue('404_back_to_home');
							} else {
								$params['text'] = "BACK TO HOME";
							}
							$params['link'] = esc_url(home_url('/'));
							$params['target'] = '_self';
							$params['type'] = 'solid';
							$params['size'] = 'large';
						echo walker_edge_execute_shortcode('edgtf_button',$params);?>
					</div>
				</div>	
			</div>
		</div>
	</div>		
	<?php wp_footer(); ?>
</body>
</html>