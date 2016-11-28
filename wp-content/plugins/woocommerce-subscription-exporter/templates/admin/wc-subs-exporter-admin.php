<div id="content">
  <h2 class="nav-tab-wrapper"> <a data-tab-id="export" class="nav-tab<?php wc_subs_exporter_admin_active_tab( 'export' ); ?>" href="<?php echo add_query_arg( array( 'page' => 'wc-subs-exporter', 'tab' => 'export' ), 'admin.php' ); ?>">
    <?php _e( 'Export', 'wc-subs-exporter' ); ?>
    </a><a data-tab-id="options" class="nav-tab<?php wc_subs_exporter_admin_active_tab( 'options' ); ?>" href="<?php echo add_query_arg( array( 'page' => 'wc-subs-exporter', 'tab' => 'options' ), 'admin.php' ); ?>">
    <?php _e( 'Options', 'wc-subs-exporter' ); ?>
    </a><a data-tab-id="archive" class="nav-tab<?php wc_subs_exporter_admin_active_tab( 'archive' ); ?>" href="<?php echo add_query_arg( array( 'page' => 'wc-subs-exporter', 'tab' => 'archive' ), 'admin.php' ); ?>">
    <?php _e( 'Archives', 'wc-subs-exporter' ); ?>
    </a> </h2>
  <?php wc_subs_exporter_tab_template( $tab ); ?>
</div>
<!-- #content -->