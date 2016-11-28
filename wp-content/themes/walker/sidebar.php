<?php
$sidebar = walker_edge_get_sidebar();

$sidebar_id = walker_edge_get_page_id();
$sidebar_css = array();
$sidebar_boxed_enabled = get_post_meta($sidebar_id, "edgtf_sidebar_boxed_layout_meta", true);
if(!empty($sidebar_boxed_enabled) && $sidebar_boxed_enabled === 'yes' ) {
	$sidebar_css[] = 'padding: 0';
	$sidebar_css[] = 'background-color: transparent';
}
?>
<div class="edgtf-column-inner">
    <aside class="edgtf-sidebar" <?php walker_edge_inline_style($sidebar_css); ?>>
        <?php
            if (is_active_sidebar($sidebar)) {
                dynamic_sidebar($sidebar);
            }
        ?>
    </aside>
</div>