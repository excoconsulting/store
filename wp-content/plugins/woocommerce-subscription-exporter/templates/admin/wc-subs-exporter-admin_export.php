<?php
	if ( isset( $_POST['from_date'] ) )
		$from_date = $_POST['from_date'];
	else 
		$from_date = '';

	if ( isset( $_POST['to_date'] ) )
		$to_date = $_POST['to_date'];
	else 
		$to_date = '';

// initialize subscription status count
	$subscription_statuses = wc_subs_exporter_get_subscription_statuses();

	foreach( $subscription_statuses as $key => $status ) {
		$subscription_count[ $key ] = WC_Subscriptions::get_subscription_count( array( 'subscription_status' => $key ) );
	}
?>
<p>
  <?php _e( 'Make a selection below to export entries.', 'wc-subs-exporter' ); ?>
<br />
  <?php _e( 'When you click the export button below, Subscription Exporter will create a CSV file for you to save to your computer.', 'wc-subs-exporter' ); ?>
</p>
<form method="post" action="<?php echo add_query_arg( array( 'failed' => null, 'empty' => null ) ); ?>" id="postform">
  <div id="poststuff">
    <div class="postbox" id="export-selection">
      <h3>
        <?php _e( 'Selection', 'wc-subs-exporter' ); ?>
      </h3>
      <div class="inside">
        <p class="description">
          <?php _e( 'Select the data you want to export.', 'wc-subs-exporter' ); ?>
        </p>
        <table class="form-table">
          <tr>
            <th><label for="subscriptions">
              <?php _e( 'Subscriptions', 'wc-subs-exporter' ); ?>
              </label></th>
            <td><span class="description">(<?php echo array_sum( $subscription_count ); ?>)</span> </td>
          </tr>
          <?php				
		foreach( $subscription_statuses as $status => $label ) { 
?>
          <tr>
            <td><label>
              <input type="checkbox" name="status[<?php echo $status ?>]" value="1"<?php if ( $subscription_count[$status] ) echo ' checked="checked"'; ?> />
              <?php echo $label ?> </label></td>
            <td><span class="description">(<?php echo $subscription_count[ $status ] ?>)</span> </td>
          </tr>
          <?php 
		}
?>
          <tr>
            <th><label for="date">
              <?php _e('Start date', 'wc-subs-exporter' ); ?>
              </label></th>
            <td><input id="date" style="width: 10em;display:inline-table" name="from_date" type="text" class="date" value="<?php echo $from_date ?>" />
              <span class="description">
              <?php _e( 'Select the starting date of subscription start.', 'wc-subs-exporter' ); ?>
              </span> </td>
          </tr>
          <tr>
            <th><label for="date2">
              <?php _e('End date', 'wc-subs-exporter' ); ?>
              </label></th>
            <td><input id="date2" style="width: 10em;display:inline-table" name="to_date" type="text" class="date" value="<?php echo $to_date ?>" />
              <span class="description">
              <?php _e( 'Select the end date of subscription start.', 'wc-subs-exporter' ); ?>
              </span> </td>
          </tr>
        </table>
        <p class="submit">
<?php
			if ( 'calculate-export-size' != wc_subs_exporter_get_action() ) {
?>
          <input type="submit" value="<?php _e( 'Calculate Export Size', 'wc-subs-exporter' ); ?>" class="button-primary" />
		  <input type="hidden" name="action" value="calculate-export-size" />
<?php
			} else {
				$subscriptions = get_transient( wc_subs_exporter_get_transient_name() );
				if ( false == $subscriptions ) {
					echo '<p>' . __( 'No subscriptions found.', 'wc-subs-exporter' ) . '</p>';
				} else {
					$subscriptions_count = sizeof( $subscriptions );
					$offset       = wc_subs_exporter_get_option( 'offset', 0 );
					$limit_volume = wc_subs_exporter_get_option( 'limit_volume', -1 );
					if ( 0 == $offset ) {
						if ( 1 > $limit_volume ) 
							echo '<p>' . sprintf( __ ( 'Found %s subscriptions to export.', 'wc-subs-exporter' ), 
														number_format_i18n( $subscriptions_count ) ) . '</p>';
						else 
							echo '<p>' . sprintf( __ ( 'Found %s subscriptions, because of volume limit of %s.', 'wc-subs-exporter' ), 
														number_format_i18n( $subscriptions_count ),
														number_format_i18n( $limit_volume )
														 ) . '</p>';
					} else {
						if ( 1 > $limit_volume ) 
							echo '<p>' . sprintf( __ ( 'Found %s subscriptions, after skipping %s of them due to volume offset.', 'wc-subs-exporter' ), 
											number_format_i18n( $subscriptions_count ), 
											number_format_i18n( $offset ) 
											 ) . '</p>';
						else
							echo '<p>' . sprintf( __ ( 'Found %s subscriptions, after skipping %s of them due to volume offset. Volume limit is %s.', 'wc-subs-exporter' ), 
											number_format_i18n( $subscriptions_count ), 
											number_format_i18n( $offset ), 
											number_format_i18n( $limit_volume ) ) . '</p>';
					}

					if ( $subscriptions_count > 2000 && 1 > $limit_volume && 0 == $offset ) {
						echo '<p class="error">' . sprintf( __ ( 'You have more than %s subscriptions to export. Unfortunately not all servers can process that much, so it is advised that you do a partial export.', 'wc-subs-exporter' ), number_format_i18n( '2000' ) ) . '</p>';
					}
					
				}
?>			
          <input type="submit" value="<?php _e( 'Export', 'wc-subs-exporter' ); ?>" class="button-primary" />
		  <input type="hidden" name="action" value="export" />
<?php
			}
?>
        </p>
      </div>
    </div>
    <!-- .postbox -->
  </div>
  <!-- #poststuff -->
</form>