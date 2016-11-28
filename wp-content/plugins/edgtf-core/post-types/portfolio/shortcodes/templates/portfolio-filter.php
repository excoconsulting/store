<div class="edgtf-portfolio-filter-holder <?php echo esc_attr($masonry_filter); ?>">
	<div class="edgtf-portfolio-filter-holder-inner">
		<?php 
		$rand_number = rand();
		if(is_array($filter_categories) && count($filter_categories)){ ?>
			<ul>
				<?php if($type == 'pinterest'){ ?>
					<li class="filter" data-filter="*"><span><?php esc_html_e('SHOW ALL', 'edgt_core')?></span></li>
				<?php } else { ?>
					<li data-class="filter_<?php echo esc_attr($rand_number); ?>" class="filter_<?php echo esc_attr($rand_number); ?>" data-filter="all">
						<span><?php esc_html_e('SHOW ALL', 'edgt_core')?></span>
					</li>
				<?php } ?>
				<?php foreach($filter_categories as $cat){				
					if($type == 'pinterest'){?>
						<li data-class="filter"  class="filter" data-filter=".portfolio_category_<?php echo esc_attr($cat->term_id); ?>">
							<span><?php echo esc_html($cat->name); ?></span>
						</li>
					<?php }else{ ?>
						<li data-class="filter_<?php echo esc_attr($rand_number); ?>" class="filter_<?php echo esc_attr($rand_number); ?>" data-filter=".portfolio_category_<?php echo esc_attr($cat->term_id); ?>">
							<span><?php echo esc_html($cat->name); ?></span>
						</li>
				<?php }} ?>
			</ul>
		<?php } ?>
	</div>	
</div>