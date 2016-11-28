<?php

if (!function_exists('walker_edge_register_widgets')) {

	function walker_edge_register_widgets() {

		$widgets = array(
			'WalkerEdgeClassBlogListWidget',
			'WalkerEdgeClassFullScreenMenuOpener',
			'WalkerEdgeClassImageWidget',
			'WalkerEdgeClassEdgefPopupOpener',
			'WalkerEdgeClassSearchOpener',
			'WalkerEdgeClassSeparatorWidget',
			'WalkerEdgeClassSideAreaOpener',
			'WalkerEdgeClassStickySidebar',
			'WalkerEdgeClassSocialIconWidget'
		);

		foreach ($widgets as $widget) {
			register_widget($widget);
		}
	}
}

add_action('widgets_init', 'walker_edge_register_widgets');