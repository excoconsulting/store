<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if( ! function_exists( 'woocommerce_coupons_campigns_report' ) ) {
/**
 * Report for coupon campaigns
 * @return void
 */
function woocommerce_coupons_campigns_report() {
	global $wc_coupon_campaigns, $selected_campaign, $start_date, $end_date, $wp_locale, $woocommerce;

	$selected_campaign = isset( $_POST['campaign'] ) ? $_POST['campaign'] : '';

	$campaign_args = array(
		'orderby'    => 'name',
		'order'      => 'ASC',
		'hide_empty' => false
	);

	$campaigns = get_terms( $wc_coupon_campaigns->tax, $campaign_args );
	$campaign_options = '<option value="0">No campaign selected</option>';
	foreach( $campaigns as $campaign ) {
		$campaign_options .= '<option value="' . $campaign->term_id . '"' . selected( $campaign->term_id, $selected_campaign, false ) . '>' . $campaign->name . '</option>';
	}

	if ( $selected_campaign ) {
		$coupon_args = array(
			'post_type'      => 'shop_coupon',
			'posts_per_page' => -1,
			'post_status'    => array( 'publish', 'draft' ),
			'tax_query'      => array(
				array(
					'taxonomy' => $wc_coupon_campaigns->tax,
					'field'    => 'id',
					'terms'    => $selected_campaign
				)
			),
			'orderby'        => 'title',
			'order'          => 'ASC'
		);

		$coupons_qry = new WP_Query( $coupon_args );

		$total_discount   = 0;
		$total_usage      = 0;
		$total_revenue    = 0;
		$campaign_coupons = array();
		$coupon_count     = 0;
		$total_coupons    = 0;

		if ( $coupons_qry->have_posts() ) {

			while ( $coupons_qry->have_posts() ) {
				$coupons_qry->the_post();

				$coupon_usage = absint( get_post_meta( get_the_ID(), 'usage_count', true ) );
				$total_usage += $coupon_usage;

				$coupon_limit = esc_html( get_post_meta( get_the_ID(), 'usage_limit', true) );
				if ( ! $coupon_limit ) {
					$coupon_limit = '&infin;';
				}

				$coupon_discount = absint( get_post_meta( get_the_ID(), '_total_discount', true ) );
				$total_discount += $coupon_discount;

				$coupon_revenue = absint( get_post_meta( get_the_ID(), '_total_revenue', true ) );
				$total_revenue += $coupon_revenue;

				$campaign_coupons[ get_the_ID() ] = array(
					'code'     => get_the_title(),
					'usage'    => $coupon_usage,
					'limit'    => $coupon_limit,
					'discount' => $coupon_discount,
					'revenue'  => $coupon_revenue,
				);

				if ( $coupon_discount > 0 ) {
					++$coupon_count;
				}
				++$total_coupons;

			}
		}
	}
	// print the campaign selector
	woocommerce_coupons_campigns_report_select_html( $campaign_options );

	if ( $selected_campaign ) {
		// print the report
		woocommerce_coupons_campigns_report_html( $selected_campaign, $total_coupons, $coupon_count, $total_discount, $total_revenue, $campaign_coupons );
	}
}


/**
* HTML for coupon campaigns selector
* @return void
*/
function woocommerce_coupons_campigns_report_select_html( $campaign_options ) {
	?>
	<form method="post" action="">
		<p>
			<label for="campaign"><?php _e( 'Campaign:', 'wc_coupon_campaigns' ); ?></label> <select name="campaign" id="campaign" class="chosen_select" style="width: 300px;"><?php echo $campaign_options; ?></select>
			<input type="submit" class="button" value="<?php _e( 'Show', 'woocommerce' ); ?>" />
		</p>
	</form>
	<?php
}

/**
* HTML for coupon campaigns report
* @return void
*/
function woocommerce_coupons_campigns_report_html( $selected_campaign, $total_coupons, $coupon_count, $total_discount, $total_revenue, $campaign_coupons ) {

	global $wc_coupon_campaigns, $selected_campaign, $start_date, $end_date, $wp_locale, $woocommerce;
	?>
	<div id="poststuff" class="woocommerce-reports-wrap">
		<div class="woocommerce-reports-sidebar">
			<div class="postbox">
				<h3><span><?php _e( 'Total coupons in campaign', 'wc_coupon_campaigns' ); ?></span></h3>
				<div class="inside">
					<p class="stat"><?php if ( $total_coupons ) echo absint( $total_coupons ); else _e( 'n/a', 'woocommerce' ); ?></p>
				</div>
			</div>
			<div class="postbox">
				<h3><span><?php _e( 'Total campaign coupons used', 'wc_coupon_campaigns' ); ?></span></h3>
				<div class="inside">
					<p class="stat"><?php if ( $coupon_count ) echo absint( $coupon_count ); else _e( 'n/a', 'woocommerce' ); ?></p>
				</div>
			</div>
			<div class="postbox">
				<h3><span><?php _e( 'Total campaign discount', 'wc_coupon_campaigns' ); ?></span></h3>
				<div class="inside">
					<p class="stat"><?php if ( $total_discount ) echo woocommerce_price( absint( $total_discount ) ); else _e( 'n/a', 'woocommerce' ); ?></p>
				</div>
			</div>
			<div class="postbox">
				<h3><span><?php _e( 'Total revenue from campaign', 'wc_coupon_campaigns' ); ?></span></h3>
				<div class="inside">
					<p class="stat"><?php if ( $total_revenue ) echo woocommerce_price( absint( $total_revenue ) ); else _e( 'n/a', 'woocommerce' ); ?></p>
				</div>
			</div>
		</div>
		<div class="woocommerce-reports-main">
			<div class="woocommerce-reports">
				<?php if( $selected_campaign ) { ?>
				<div class="postbox">
					<h3><span><?php _e( 'Campaign Coupon Usage', 'woocommerce' ); ?></span></h3>
					<div class="inside chart">
						<div id="placeholder" style="width:100%; overflow:hidden; height:568px; position:relative;"></div>
						<div id="cart_legend"></div>
					</div>
				</div>
				<?php } ?>
				<div class="postbox">
					<h3><span><?php _e( 'Campaign Coupons', 'woocommerce' ); ?></span></h3>
					<div class="inside">
						<ul class="wc_coupon_list wc_coupon_list_block">
							<?php
								$coupon_data = '';
								if ( $campaign_coupons ) {
									foreach ( $campaign_coupons as $coupon_id => $coupon ) {

										$link = $coupon_id ? admin_url( 'post.php?post=' . $coupon_id . '&action=edit' ) : admin_url( 'edit.php?s=' . esc_url( $coupon['code'] ) . '&post_status=all&post_type=shop_coupon' );

										$coupon_data .= '<li>
															<a href="' . $link . '" class="code"><span><span>' . esc_html( $coupon['code'] ). '</span></span></a>
															<b>' . __( 'Usage:', 'wc_coupon_campaigns' ) . '</b> ' . absint( $coupon['usage'] ) . ' / ' . esc_html( $coupon['limit'] ) . ' -
															<b>' . __( 'Discount:', 'wc_coupon_campaigns' ) . '</b> ' . woocommerce_price( absint( $coupon['discount'] ) ) . ' -
															<b>' . __( 'Revenue:', 'wc_coupon_campaigns' ) . '</b> ' . woocommerce_price( absint( $coupon['revenue'] ) ) . '
														</li>';
									}
								} else {
									$coupon_data .= '<li>' . __( 'No coupons found', 'wc_coupon_campaigns' ) . '</li>';
								}

								echo $coupon_data;
							?>
						</ul>
					</div>
				</div>

				<?php
				// Get date of earliest order using campaign coupon
				$args = array(
					'post_type'      => 'shop_order',
					'posts_per_page' => 1,
					'tax_query'      => array(
						array(
							'taxonomy' => $wc_coupon_campaigns->tax,
							'field'    => 'id',
							'terms'    => $selected_campaign
						)
					),
					'orderby'        => 'date',
					'order'          => 'ASC'
				);
				$qry = new WP_Query( $args );
				if ( $qry->have_posts() ) { $count = 0;
					while ( $qry->have_posts() ) { $qry->the_post();
						$start_date = strtotime( get_the_date( 'Y-m-d' ) );
						break;
					}
				}
				wp_reset_postdata();

				// Get date of latest order using campaign coupon
				$args['order'] = 'DESC';
				$qry = new WP_Query( $args );
				if ( $qry->have_posts() ) {
					$count = 0;
					while ( $qry->have_posts() ) {
						$qry->the_post();
						$end_date = strtotime( get_the_date( 'Y-m-d' ) );
						break;
					}
				}
				wp_reset_postdata();

				$campaign = get_term( $selected_campaign, $wc_coupon_campaigns->tax );
				$campaign_name = $campaign->name;

				for( $date = $start_date; $date <= $end_date; $date = strtotime( '+1 day', $date ) ) {
					$year        = date( 'Y', $date );
					$month       = date( 'n', $date );
					$day         = date( 'j', $date );
					$day_total   = 0;
					$order_count = 0;

					$args = array(
						'post_type'      => 'shop_order',
						'posts_per_page' => -1,
						'tax_query'      => array(
							array(
								'taxonomy' => $wc_coupon_campaigns->tax,
								'field'    => 'id',
								'terms'    => $selected_campaign
							)
						),
						'year'           => $year,
						'monthnum'       => $month,
						'day'            => $day,
						'orderby'        => 'date',
						'order'          => 'ASC'
					);
					$qry = new WP_Query( $args );
					if ( $qry->have_posts() ) {
						while ( $qry->have_posts() ) { $qry->the_post();
							$order      = new WC_Order( get_the_ID() );
							$day_total += $order->order_total;
							++$order_count;
						}
					}
					wp_reset_postdata();

					$chart_data[ __( 'Total revenue', 'wc_coupon_campaigns' ) ][] = array(
						$date . '000',
						$day_total
					);

					$chart_data[ __( 'Number of orders', 'wc_coupon_campaigns' ) ][] = array(
						$date . '000',
						$order_count
					);
				}

				?>
				<?php if ( $selected_campaign ) { ?>
				<script type="text/javascript">
					jQuery(function(){

						<?php
							// Variables
							foreach ( $chart_data as $name => $data ) {
								$varname = str_replace( '-', '_', sanitize_title( $name ) ) . '_data';
								echo 'var ' . $varname . ' = jQuery.parseJSON( \'' . json_encode( $data ) . '\' );';
							}
						?>

						var placeholder = jQuery("#placeholder");

						var plot = jQuery.plot(placeholder, [
							<?php
							$labels = array();

							foreach ( $chart_data as $name => $data ) {
								if( 'Number of orders' == $name ) {
									$labels[] = '{ label: "' . esc_js( $name ) . '", data: ' . str_replace( '-', '_', sanitize_title( $name ) ) . '_data, yaxis: 2 }';
								} else {
									$labels[] = '{ label: "' . esc_js( $name ) . '", data: ' . str_replace( '-', '_', sanitize_title( $name ) ) . '_data }';
								}
							}

							echo implode( ',', $labels );
							?>
						], {
							legend: {
								container: jQuery('#cart_legend'),
								noColumns: 2
							},
							series: {
								lines: { show: true, fill: true },
								points: { show: true }
							},
							grid: {
								show: true,
								aboveData: false,
								color: '#aaa',
								backgroundColor: '#fff',
								borderWidth: 2,
								borderColor: '#aaa',
								clickable: false,
								hoverable: true
							},
							xaxis: {
								mode: "time",
								timeformat: "%d %b %y",
								monthNames: <?php echo json_encode( array_values( $wp_locale->month_abbrev ) ) ?>,
								tickLength: 1,
								minTickSize: [1, "day"]
							},
							yaxes: [ { min: 0, tickSize: 10, tickDecimals: 2 }, { position: "right", min: 0, tickDecimals: 2 } ]
						});

						placeholder.resize();

						drawGraph();
					});
				</script>
				<?php } ?>
			</div>
		</div>
	</div>
	<script type="text/javascript">
		jQuery(function(){
			jQuery("select.chosen_select").chosen();
		});
	</script>
	<?php
}

} // end if
