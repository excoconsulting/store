<?php

if(!function_exists('walker_edge_is_responsive_on')) {
    /**
     * Checks whether responsive mode is enabled in theme options
     * @return bool
     */
    function walker_edge_is_responsive_on() {
        return walker_edge_options()->getOptionValue('responsiveness') !== 'no';
    }
}