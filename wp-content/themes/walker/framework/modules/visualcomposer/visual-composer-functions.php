<?php

if(!function_exists('walker_edge_get_vc_version')) {
	/**
	 * Return Visual Composer version string
	 *
	 * @return bool|string
	 */
	function walker_edge_get_vc_version() {
		if(walker_edge_visual_composer_installed()) {
			return WPB_VC_VERSION;
		}

		return false;
	}
}