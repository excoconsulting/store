<?php if(walker_edge_options()->getOptionValue('blog_list_tags') == 'yes' && has_tag()){ ?>
	<div class="edgtf-single-tags-holder edgtf-list-tags">
		<div class="edgtf-tags">
			<?php the_tags('', '', ''); ?>
		</div>
	</div>
<?php } ?>