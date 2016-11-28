<?php if(($sidebar == "default")||($sidebar == "")) : ?>
	<div class="edgtf-blog-holder edgtf-blog-single">
		<?php walker_edge_get_single_html(); ?>
	</div>
<?php elseif($sidebar == 'sidebar-33-right' || $sidebar == 'sidebar-25-right'): ?>
	<div <?php echo walker_edge_sidebar_columns_class(); ?>>
		<div class="edgtf-column1 edgtf-content-left-from-sidebar">
			<div class="edgtf-column-inner">
				<div class="edgtf-blog-holder edgtf-blog-single">
					<?php walker_edge_get_single_html(); ?>
				</div>
			</div>
		</div>
		<div class="edgtf-column2">
			<?php get_sidebar(); ?>
		</div>
	</div>
<?php elseif($sidebar == 'sidebar-33-left' || $sidebar == 'sidebar-25-left'): ?>
	<div <?php echo walker_edge_sidebar_columns_class(); ?>>
		<div class="edgtf-column1">
			<?php get_sidebar(); ?>
		</div>
		<div class="edgtf-column2 edgtf-content-right-from-sidebar">
			<div class="edgtf-column-inner">
				<div class="edgtf-blog-holder edgtf-blog-single">
					<?php walker_edge_get_single_html(); ?>
				</div>
			</div>
		</div>
	</div>
<?php endif; ?>
