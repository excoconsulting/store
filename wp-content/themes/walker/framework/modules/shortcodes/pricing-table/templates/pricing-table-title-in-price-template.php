<div <?php walker_edge_class_attribute($pricing_table_classes)?>>
	<div class="edgtf-price-table-inner">
		<ul <?php echo walker_edge_get_inline_style($pricing_table_styles); ?>>
			<li class="edgtf-table-prices" <?php echo walker_edge_get_inline_style($header_styles); ?>>
                <div class="edgtf-price-in-table">
                    <span class="edgtf-price-value">
                        <sup class="edgtf-value"><?php echo esc_html($currency); ?></sup>
                        <span class="edgtf-price"><?php echo esc_html($price); ?></span>
                    </span>
                    <span class="edgtf-title-mark">
                        <span class="edgtf-mark">/ <?php echo esc_html($price_period); ?></span>
                    </span>
                </div>
			</li>
            <li class="edgtf-table-title" <?php echo walker_edge_get_inline_style($pricing_title_styles); ?>>
                <span class="edgtf-title-content"><?php echo esc_html($title); ?></span>
            </li>
			<li class="edgtf-table-content">
                <?php echo do_shortcode($content); ?>
			</li>
			<?php 
			if($show_button == "yes" && $button_text !== ''){ ?>
				<li class="edgtf-price-button">
					<?php echo walker_edge_get_button_html(array(
						'link' 						=> $link,
						'text' 						=> $button_text,
						'type' 						=> $button_type,
                        'size' 						=> $button_size,
						'color' 					=> $button_color,
						'hover_color' 				=> $hover_button_color,
						'background_color' 			=> $button_background_color,
						'hover_background_color' 	=> $hover_button_background_color,
						'border_color' 				=> $button_border_color,
						'hover_border_color' 		=> $hover_button_border_color
					)); ?>
				</li>				
			<?php } ?>
		</ul>
	</div>
</div>