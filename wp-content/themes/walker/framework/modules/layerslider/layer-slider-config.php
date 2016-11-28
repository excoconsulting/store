<?php
	if(!function_exists('walker_edge_layerslider_overrides')) {
		/**
		 * Disables Layer Slider auto update box
		 */
		function walker_edge_layerslider_overrides() {
			$GLOBALS['lsAutoUpdateBox'] = false;
		}

		add_action('layerslider_ready', 'walker_edge_layerslider_overrides');
	}
?>