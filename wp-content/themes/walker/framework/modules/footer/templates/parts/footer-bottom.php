<div class="edgtf-footer-bottom-holder">
	<div class="edgtf-footer-bottom-holder-inner">
		<?php if($footer_in_grid) { ?>
			<div class="edgtf-container">
				<div class="edgtf-container-inner">
		<?php }

		switch ($footer_bottom_columns) {
			case 3:
				walker_edge_get_footer_bottom_sidebar_three_columns();
				break;
			case 2:
				walker_edge_get_footer_bottom_sidebar_two_columns();
				break;
			case 1:
				walker_edge_get_footer_bottom_sidebar_one_column();
				break;
		}
		if($footer_in_grid){ ?>
				</div>
			</div>
		<?php } ?>
	</div>
</div>