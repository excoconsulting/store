<div class="edgtf-plc-holder <?php echo esc_attr($holder_classes) ?>" <?php echo walker_edge_get_inline_attrs($holder_data); ?>>
	<div class="edgtf-plc-outer">
		<?php if($query_result->have_posts()): while ($query_result->have_posts()) : $query_result->the_post(); ?>
			<?php 
				$product = walker_edge_return_woocommerce_global_variable();

				$rating_enabled = false;
				if ( get_option( 'woocommerce_enable_review_rating' ) !== 'no' ) {
					$rating_enabled = true;
					$average      = $product->get_average_rating();
				}
				$new_layout = walker_edge_get_meta_field_intersect('single_product_new');
			?>
			<div class="edgtf-plc-item">
				<?php if($type === 'simple') { ?>
					<div class="edgtf-grid">
				<?php } ?>
				<div class="edgtf-plc-image-outer">
					<div class="edgtf-plc-image">
						<?php if ( $product->is_on_sale() ) : ?>
							<span class="edgtf-plc-onsale"><?php esc_html_e('SALE', 'walker'); ?></span>
						<?php endif; ?>
						<?php if (!$product->is_in_stock()) : ?>
							<span class="edgtf-plc-out-of-stock"><?php esc_html_e('OUT OF STOCK', 'walker'); ?></span>
						<?php endif; ?>
						<?php if ($new_layout === 'yes') : ?>
							<span class="edgtf-plc-new-product"><?php esc_html_e('NEW', 'walker'); ?></span>
						<?php endif; ?>
						<?php 
							$product_image_size = 'shop_single';
							if($image_size === 'original') {
								$product_image_size = 'full';
							} else if ($image_size === 'square') {
								$product_image_size = 'walker_edge_square';
							}
							echo get_the_post_thumbnail( get_the_ID(), apply_filters( 'walker_edge_product_list_carousel_image_size', $product_image_size ));
						?>
					</div>
					<?php if($type === 'standard') { ?>
					<?php if(($display_quick_view === 'yes' && walker_edge_is_yith_wcqv_install()) || ($display_wishlist === 'yes' && walker_edge_is_yith_wishlist_install())) { ?>
						<div class="edgtf-pl-yith <?php echo esc_attr($yith_holder_classes); ?>">
							<div class="edgtf-pl-yith-inner">
								<?php if($display_quick_view === 'yes' && walker_edge_is_yith_wcqv_install()) {
									walker_edge_woocommerce_quick_view_button();
								} ?>
								<?php if($display_wishlist === 'yes' && walker_edge_is_yith_wishlist_install()) {
									walker_edge_woocommerce_wishlist_shortcode();
								} ?>
							</div>
						</div>		
					<?php } ?>
					<div class="edgtf-plc-text" <?php echo walker_edge_get_inline_style($shader_styles); ?>>
						<div class="edgtf-plc-text-outer">
							<div class="edgtf-plc-text-inner">
								<?php if($display_title === 'yes') { ?>
									<<?php echo esc_attr($title_tag); ?> itemprop="name" class="entry-title edgtf-plc-title" <?php echo walker_edge_get_inline_style($title_styles); ?>><?php the_title(); ?></<?php echo esc_attr($title_tag); ?>>
								<?php } ?>
								<?php if($display_price === 'yes') { ?>
									<div class="edgtf-plc-price"><?php print $product->get_price_html(); ?></div>
								<?php } ?>	
								<?php if ($rating_enabled && $display_rating === 'yes') { ?>
									<div class="edgtf-plc-rating-holder">
										<div class="edgtf-plc-rating" title="<?php printf( esc_html__( 'Rated %s out of 5', 'walker' ), $average ); ?>">
											<span style="width:<?php echo ( ( $average / 5 ) * 100 ); ?>%"></span>
										</div>
									</div>	
								<?php } ?>
								<?php if($display_button === 'yes') {									
									if (!$product->is_in_stock()) {
										$button_classes = 'button ajax_add_to_cart edgtf-button';
									} else if ($product->product_type === 'variable') {
										$button_classes = 'button product_type_variable add_to_cart_button edgtf-button';
									} else if ($product->product_type === 'external') {
										$button_classes = 'button product_type_external edgtf-button';
									} else {
										$button_classes = 'button add_to_cart_button ajax_add_to_cart edgtf-button';
									}

		                            echo apply_filters( 'walker_edge_product_list_carousel_add_to_cart_link',
										sprintf( '<a rel="nofollow" href="%s" data-quantity="%s" data-product_id="%s" data-product_sku="%s" class="%s" data-title="%s">%s</a>',
											esc_url( $product->add_to_cart_url() ),
											esc_attr( isset( $quantity ) ? $quantity : 1 ),
											esc_attr( $product->id ),
											esc_attr( $product->get_sku() ),
											esc_attr( $button_classes ),
											esc_html( $product->add_to_cart_text() ),
											esc_html( $product->add_to_cart_text() )
										),
									$product );
								} ?>
							</div>
						</div>	
					</div>
					<?php } ?>
					<a class="edgtf-plc-link" itemprop="url" href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"></a>
				</div>
				<?php if($type === 'simple') { ?>
					<div class="edgtf-plc-text-wrapper">
						<div class="edgtf-plc-text">
							<div class="edgtf-plc-text-outer">
								<div class="edgtf-plc-text-inner">
									<?php if($display_title === 'yes') { ?>
										<<?php echo esc_attr($title_tag); ?> itemprop="name" class="entry-title edgtf-plc-title" <?php echo walker_edge_get_inline_style($title_styles); ?>><?php the_title(); ?></<?php echo esc_attr($title_tag); ?>>
									<?php } ?>
									<?php if($display_price === 'yes') { ?>
										<div class="edgtf-plc-price"><?php print $product->get_price_html(); ?></div>
									<?php } ?>	
									<?php if ($rating_enabled && $display_rating === 'yes') { ?>
										<div class="edgtf-plc-rating-holder">
											<div class="edgtf-plc-rating" title="<?php printf( esc_html__( 'Rated %s out of 5', 'walker' ), $average ); ?>">
												<span style="width:<?php echo ( ( $average / 5 ) * 100 ); ?>%"></span>
											</div>
										</div>	
									<?php } ?>
									<?php if ($display_excerpt === 'yes') { ?>
										<div class="edgtf-plc-excerpt">
											<p itemprop="description"><?php echo get_the_excerpt(); ?></p>
										</div>	
									<?php } ?>
									<?php if($display_button === 'yes') {									
										if (!$product->is_in_stock()) {
											$button_classes = 'button ajax_add_to_cart edgtf-button';
										} else if ($product->product_type === 'variable') {
											$button_classes = 'button product_type_variable add_to_cart_button edgtf-button';
										} else if ($product->product_type === 'external') {
											$button_classes = 'button product_type_external edgtf-button';
										} else {
											$button_classes = 'button add_to_cart_button ajax_add_to_cart edgtf-button';
										}

			                            echo apply_filters( 'walker_edge_product_list_carousel_add_to_cart_link',
											sprintf( '<a rel="nofollow" href="%s" data-quantity="%s" data-product_id="%s" data-product_sku="%s" class="%s" data-title="%s">%s</a>',
												esc_url( $product->add_to_cart_url() ),
												esc_attr( isset( $quantity ) ? $quantity : 1 ),
												esc_attr( $product->id ),
												esc_attr( $product->get_sku() ),
												esc_attr( $button_classes ),
												esc_html( $product->add_to_cart_text() ),
												esc_html( $product->add_to_cart_text() )
											),
										$product );
									} ?>
								</div>
							</div>	
						</div>
					</div>
				<?php } ?>
				<?php if($type === 'simple') { ?>
					</div>
				<?php } ?>
			</div>
		<?php endwhile;	else: ?>
			<div class="edgtf-plc-messsage">
				<p><?php esc_html_e('No posts were found.', 'walker'); ?></p>
			</div>
		<?php endif;
			wp_reset_postdata();
		?>
	</div>
</div>