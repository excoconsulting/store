<section class="edgtf-side-menu right">
	<?php if ($show_side_area_title) {
		walker_edge_get_side_area_title();
	} ?>
	<div class="edgtf-close-side-menu-holder">
		<div class="edgtf-close-side-menu-holder-inner">
			<a href="#" target="_self" class="edgtf-close-side-menu">
				<span class="edgtf-side-menu-lines">
					<span class="edgtf-side-menu-line edgtf-line-1"></span>
					<span class="edgtf-side-menu-line edgtf-line-2"></span>
			        <span class="edgtf-side-menu-line edgtf-line-3"></span>
				</span>
			</a>
		</div>
	</div>
	<?php if(is_active_sidebar('sidearea')) {
		dynamic_sidebar('sidearea');
	} ?>
</section>