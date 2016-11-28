<?php do_action('walker_edge_before_top_navigation'); ?>

<nav class="edgtf-vertical-menu edgtf-vertical-dropdown-on-click">
    <?php
        wp_nav_menu(array(
            'theme_location'  => 'main-navigation',
            'container'       => '',
            'container_class' => '',
            'menu_class'      => '',
            'menu_id'         => '',
            'fallback_cb'     => 'top_navigation_fallback',
            'link_before'     => '<span>',
            'link_after'      => '</span>',
            'walker'          => new WalkerEdgeClassStickyNavigationWalker()
        ));
    ?>
</nav>

<?php do_action('walker_edge_after_top_navigation'); ?>