<?php
	$author_info_box = esc_attr(walker_edge_options()->getOptionValue('blog_author_info'));
	$author_info_email = esc_attr(walker_edge_options()->getOptionValue('blog_author_info_email'));
	$author_id = esc_attr(get_the_author_meta('ID'));
	$social_networks   = walker_edge_get_user_custom_fields();
?>
<?php if($author_info_box === 'yes' && get_the_author_meta('description') != "") { ?>
	<div class="edgtf-author-description">
		<div class="edgtf-author-description-inner">
			<div class="edgtf-author-description-image">
				<a itemprop="url" href="<?php echo esc_url(get_author_posts_url($author_id)); ?>" title="<?php the_title_attribute(); ?>" target="_self">
					<?php echo walker_edge_kses_img(get_avatar(get_the_author_meta( 'ID' ), 110)); ?>
				</a>	
			</div>
			<div class="edgtf-author-description-text-holder">
				<h4 class="edgtf-author-name vcard author">
					<span class="edgtf-author-name-label"><?php echo esc_html_e('WRITTEN BY:', 'walker'); ?></span>
					<a itemprop="url" href="<?php echo esc_url(get_author_posts_url($author_id)); ?>" title="<?php the_title_attribute(); ?>" target="_self">
						<span class="fn">
						<?php
							if(get_the_author_meta('first_name') != "" || get_the_author_meta('last_name') != "") {
								echo esc_attr(get_the_author_meta('first_name')) . " " . esc_attr(get_the_author_meta('last_name'));
							} else {
								echo esc_attr(get_the_author_meta('display_name'));
							}
						?>
						</span>
					</a>	
				</h4>
				<?php if($author_info_email === 'yes' && is_email(get_the_author_meta('email'))){ ?>
					<p class="edgtf-author-email"><?php echo sanitize_email(get_the_author_meta('email')); ?></p>
				<?php } ?>
				<?php if(get_the_author_meta('description') !== "") { ?>
					<div class="edgtf-author-text">
						<p><?php echo esc_attr(get_the_author_meta('description')); ?></p>
						<?php if($display_author_social) { ?>
							<?php if(is_array($social_networks) && count($social_networks)){ ?>
								<div class="edgtf-author-social-icons clearfix">
									<?php foreach($social_networks as $network){ ?>
										<a itemprop="url" href="<?php echo esc_attr($network['link'])?>" target="_blank">
											<?php echo walker_edge_icon_collections()->renderIcon($network['class'], 'font_elegant'); ?>
										</a>
									<?php }?>
								</div>
							<?php } ?>
						<?php } ?>
					</div>
				<?php } ?>
			</div>
		</div>
	</div>
<?php } ?>