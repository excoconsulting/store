<?php

class WC_Recommender_Admin {

	private static $instance;

	public static function register() {
		if ( self::$instance == null ) {
			self::$instance = new WC_Recommender_Admin();
		}
	}

	private $is_admin_page = false;

	private function __construct() {

		add_action( 'wp_ajax_wc_recommender_build_recommendation', array($this, 'build_recommendation') );

		add_action( 'wp_ajax_wc_recommender_rebuild_recommendations', array($this, 'rebuild_recommendations') );

		add_action( 'wp_ajax_wc_recommender_install_stats', array($this, 'install_stats') );

		add_action( 'wp_ajax_wc_recommender_execute_cron_job', array($this, 'execute_cron_job') );

		add_action( 'admin_enqueue_scripts', array($this, 'admin_enqueue_scripts') );
		add_action( 'admin_init', array($this, 'maybe_set_page'), 0 );
		add_action( 'admin_init', array($this, 'maybe_handle_request'), 10 );
		add_action( 'admin_menu', array($this, 'register_admin_menu') );

		add_filter( 'woocommerce_screen_ids', array($this, 'get_woocommerce_screen_ids') );
	}

	public function admin_enqueue_scripts( $handle ) {
		if ( $handle == 'woocommerce_page_wc_recommender_admin' ) {
			wp_enqueue_style( 'wc-recommender-admin-style', wcre()->plugin_url() . '/assets/admin/css/admin.css' );


			$params = array(
			    'ajax_url' => admin_url( 'admin-ajax.php' ),
			    'build_recommendations_security' => wp_create_nonce( "build-recommendation" ),
			    'execute_cron_job_security' => wp_create_nonce( "execute-cron-job" ),
			    'start_count' => 1
			);
			
			wp_enqueue_script( 'wc-recommender-admin-script', wcre()->plugin_url() . '/assets/admin/js/admin.js', array('jquery'), wcre()->version );
			wp_localize_script( 'wc-recommender-admin-script', 'wc_recommender_params', $params );
		}
	}

	public function maybe_set_page() {
		if ( isset( $_GET['page'] ) && $_GET['page'] == 'wc_recommender_admin' ) {
			$this->is_admin_page = true;
		}
	}

	public function get_woocommerce_screen_ids( $screens ) {

		$screens[] = 'woocommerce_page_wc_recommender_admin';
		return $screens;
	}

	public function maybe_handle_request() {

		if ( $this->is_admin_page ) {

			$result = false;

			$bulk_action = '';
			if ( isset( $_REQUEST['filter_action'] ) && !empty( $_REQUEST['filter_action'] ) ) {
				$bulk_action = $_REQUEST['filter_action'];
			}

			if ( isset( $_REQUEST['action'] ) && -1 != $_REQUEST['action'] ) {
				$bulk_action = $_REQUEST['action'];
			}

			if ( isset( $_REQUEST['action2'] ) && -1 != $_REQUEST['action2'] ) {
				$bulk_action = $_REQUEST['action2'];
			}

			if ( !empty( $bulk_action ) ) {
				$result = $this->handle_bulk_action( $bulk_action );
			}


			if ( $result !== false && $result !== true ) {
				wp_redirect( $result );
				die();
			}
		}

		return;
	}

	public function register_admin_menu() {
		$show_in_menu = current_user_can( 'manage_woocommerce' ) ? 'woocommerce' : false;
		$slug = add_submenu_page( $show_in_menu, __( 'Recommendations', 'wc_recommender' ), __( 'Recommendations', 'wc_recommender' ), 'manage_woocommerce', 'wc_recommender_admin', array($this, 'do_recommendation_admin_page') );
	}

	public function do_recommendation_admin_page() {
		
		$current_tab = (isset( $_GET['tab'] )) ? $_GET['tab'] : 'recommendations';
		$current_view = (isset( $_GET['view'] )) ? $_GET['view'] : 0;
		?>
		<div class="wrap woocommerce">
			<div class="icon32 woocommerce-dynamic-pricing" id="icon-woocommerce"><br></div><h2 class="nav-tab-wrapper woo-nav-tab-wrapper">
				<?php
				$tabs = apply_filters( 'woocommerce_recommender_tabs', array(
				    'recommendations' => array(
					array(
					    'title' => __( 'View Recommendations', 'wc_recommender' ),
					    'description' => __( 'A list of the all products and the recommendations associated with them', 'wc_recommender' ),
					    'function' => 'recommendations_tab'
					)
				    ),
				    'maintenance' => array(
					array(
					    'title' => __( 'Maintenance', 'wc_recommender' ),
					    'description' => '',
					    'function' => 'maintenance_rebuild_tab'
					),
					array(
					    'title' => __( 'History Installation', 'wc_recommender' ),
					    'description' => __( 'Install statistics based on orders present in the system before the plugin was activated', 'wc_recommender' ),
					    'function' => 'maintenance_install_stats'
					),
				    ),
				) );



				foreach ( $tabs as $name => $value ) :
					echo '<a href="' . admin_url( 'admin.php?page=wc_recommender_admin&tab=' . $name ) . '" class="nav-tab ';
					if ( $current_tab == $name )
						echo 'nav-tab-active';
					echo '">' . ucfirst( $name ) . '</a>';
				endforeach;
				?>
			</h2>

			<?php if ( sizeof( $tabs[$current_tab] ) > 0 ) : ?><ul class="subsubsub"><li><?php
				$links = array();
				foreach ( $tabs[$current_tab] as $key => $tab ) :
					$link = '<a href="admin.php?page=wc_recommender_admin&tab=' . $current_tab . '&amp;view=' . $key . '" class="';
					if ( $key == $current_view )
						$link .= 'current';
					$link .= '">' . $tab['title'] . '</a>';
					$links[] = $link;
				endforeach;
				echo implode( ' | </li><li>', $links );
				?></li></ul><br class="clear" /><?php endif; ?>

			<?php if ( isset( $tabs[$current_tab][$current_view] ) ) : ?> 
				<?php if ( !isset( $tabs[$current_tab][$current_view]['hide_title'] ) || $tabs[$current_tab][$current_view]['hide_title'] != true ) : ?>
					<div class="tab_top"><h3 class="has-help"><?php echo $tabs[$current_tab][$current_view]['title']; ?></h3>
						<?php if ( $tabs[$current_tab][$current_view]['description'] ) : ?>
							<p class="help"><?php echo $tabs[$current_tab][$current_view]['description']; ?></p>
						<?php endif; ?>
					</div>
				<?php endif; ?>
				<?php
				$func = $tabs[$current_tab][$current_view]['function'];
				if ( $func && method_exists( $this, $func ) ) {
					$this->$func();
				}
				?>
			<?php endif; ?>
		</div>
		<?php
	}

	public function recommendations_tab() {
		if (isset($_GET['wc_recommender_admin_view']) && $_GET['wc_recommender_admin_view'] == 'view-recommendations'){
			include 'views/admin/view-recommendations.php';
		}
		else {
			include 'views/admin/index.php';
		}
	}

	public function maintenance_rebuild_tab() {
		include 'views/admin/maintenance-rebuild-tab.php';
	}

	public function maintenance_install_stats() {
		include 'views/admin/maintenance-install-stats-tab.php';
	}

	public function maintenance_database_tab() {
		include 'views/admin/maintenance-database-tab.php';
	}

	public function cron_jobs_tab() {
		include 'views/admin/cron-jobs-tab.php';
	}

	public function build_recommendation() {
		global $wpdb, $woocommerce_recommender;

		check_ajax_referer( 'build-recommendation', 'security' );

		$product_id = $_POST['post_id'];

		$builder = new WC_Recommender_Recorder( );
		$wpdb->query( $wpdb->prepare( "DELETE FROM $woocommerce_recommender->db_tbl_recommendations WHERE product_id = %d", $product_id ) );

		$builder->woocommerce_recommender_build_simularity( $product_id, array('viewed') );
		$builder->woocommerce_recommender_build_simularity( $product_id, array('completed') );
		$builder->woocommerce_build_purchased_together( $product_id, array('completed') );

		$items_table_name = $wpdb->posts;
		$items_sql = "SELECT ID, post_title, cviewed.activity_count as views, cordered.activity_count as orders FROM $items_table_name p ";
		$items_sql .= "LEFT JOIN ( SELECT product_id, COUNT(DISTINCT session_ID) as activity_count FROM $woocommerce_recommender->db_tbl_session_activity WHERE activity_type = 'viewed' GROUP BY product_id) cviewed ON p.ID = cviewed.product_id ";
		$items_sql .= "LEFT JOIN ( SELECT product_id, COUNT(DISTINCT session_ID) as activity_count FROM $woocommerce_recommender->db_tbl_session_activity WHERE activity_type = 'ordered' GROUP BY product_id) cordered ON p.ID = cordered.product_id ";
		$items_sql .= "WHERE p.ID = %d";

		$record = $wpdb->get_row( $wpdb->prepare( $items_sql, $product_id ) );

		$recommendations = array();

		$recommendations['viewed_similar'] = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(related_product_id) FROM $woocommerce_recommender->db_tbl_recommendations WHERE product_id = %d AND rkey = %s", $product_id, 'wc_recommender_viewed_' . $product_id ) );
		$recommendations['ordered_similar'] = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(related_product_id) FROM $woocommerce_recommender->db_tbl_recommendations WHERE product_id = %d AND rkey = %s", $product_id, 'wc_recommender_completed_' . $product_id ) );
		$recommendations['purchased_together'] = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(related_product_id) FROM $woocommerce_recommender->db_tbl_recommendations WHERE product_id = %d AND rkey = %s", $product_id, 'wc_recommender_fpt_completed_' . $product_id ) );

		$results = array(
		    'views' => $record->views,
		    'orders' => $record->orders,
		    'viewed_similar' => $recommendations['viewed_similar'],
		    'ordered_similar' => $recommendations['ordered_similar'],
		    'purchased_together' => $recommendations['purchased_together']
		);

		wp_send_json_success( $results );
		die();
	}

	public function rebuild_recommendations() {
		global $wpdb;

		$total = $wpdb->get_var( "SELECT COUNT(ID) FROM $wpdb->posts WHERE post_type = 'product' and post_status='publish'" );
		$start = isset( $_POST['start'] ) ? (int) $_POST['start'] : 0;
		$count = isset( $_POST['count'] ) ? (int) $_POST['count'] : 10;

		$time_pre = microtime( true );
		
		$builder = new WC_Recommender_Recorder();
		$builder->woocommerce_recommender_begin_build_simularity( $start, $count );
			
		$next_start = $start + $count;
		if ( $next_start < $total ) {
			$time_post = microtime( true );
			$exec_time = floatval( ($time_post - $time_pre) * ( ($total - $start) / $count) );

			$d = date( 'H:i:s', $exec_time );

			$result = array(
			    'total' => $total,
			    'start' => $next_start,
			    'count' => $count,
			    'countremaining' => $total - $next_start,
			    'timeremaining' => $d
			);
		} else {
			$result = array(
			    'total' => $total,
			    'done' => true
			);
		}

		wp_send_json_success( $result );
		die();
	}

	public function install_stats() {
		global $wpdb, $woocommerce_recommender;


		$post_status = wc_get_order_statuses();
		$posts = get_posts( array('post_status' => $post_status, 'post_type' => 'shop_order', 'posts_per_page' => 500) );

		if ( $posts && count( $posts ) ) {
			foreach ( $posts as $post ) {
				$order_id = $post->ID;

				$wc_order = new WC_Order( $order_id );
				$wc_order_items = $wc_order->get_items();
				if ( $wc_order_items && count( $wc_order_items ) ) {
					foreach ( $wc_order_items as $wc_order_item ) {
						$wc_ordered_product = @$wc_order->get_product_from_item( $wc_order_item );

						if ( $wc_ordered_product && is_object( $wc_ordered_product ) && isset( $wc_ordered_product->id ) && $wc_ordered_product->id ) {
							$sql = $wpdb->prepare( "SELECT COUNT(*) FROM $woocommerce_recommender->db_tbl_session_activity WHERE order_id = %d AND product_id = %d", $order_id, $wc_ordered_product->id );
							$order_tracking_exists = $wpdb->get_var( $sql );
							if ( !$order_tracking_exists ) {

								$session_id = isset( $wc_order->customer_user ) ? $wc_order->customer_user : (isset( $wc_order->user_id ) ? $wc_order->user_id : $wc_order->billing_email);
								$session_id = md5( $session_id );

								$activity_date = date( 'Y-m-d H:i:s', strtotime( $wc_order->order_date ) );

								$user_id = isset( $wc_order->customer_user ) ? $wc_order->customer_user : (isset( $wc_order->user_id ) ? $wc_order->user_id : 0);

								woocommerce_recommender_record_product( $wc_ordered_product->id, $session_id, $user_id, $wc_order->id, $wc_order->status, $activity_date );

								if ( $wc_ordered_product->is_type( 'variable' ) && isset( $wc_ordered_product->variation_id ) && $wc_ordered_product->variation_id ) {
									woocommerce_recommender_record_product( $wc_ordered_product->variation_id, $session_id, $user_id, $order_id, $wc_order->status, $activity_date );
								}
							} else {

								woocommerce_recommender_update_recorded_product( $wc_order->id, $wc_ordered_product->id, $wc_order->status );
								if ( $wc_ordered_product->is_type( 'variable' ) && isset( $wc_ordered_product->variation_id ) && $wc_ordered_product->variation_id ) {
									woocommerce_recommender_update_recorded_product( $wc_order->id, $wc_ordered_product->variation_id, $wc_order->status );
								}
							}

							$order_viewed_exists = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $woocommerce_recommender->db_tbl_session_activity WHERE order_id = %d AND product_id = %d AND activity_type = 'viewed'", 0, $wc_ordered_product->id ) );

							if ( !$order_viewed_exists ) {
								$product_id = $wc_ordered_product->id;

								$session_id = isset( $wc_order->customer_user ) ? $wc_order->customer_user : (isset( $wc_order->user_id ) ? $wc_order->user_id : $wc_order->billing_email);
								$session_id = md5( $session_id );

								$activity_date = date( 'Y-m-d H:i:s', strtotime( $wc_order->order_date ) );
								$activity_type = 'viewed';
								$user_id = 0;

								woocommerce_recommender_record_product( $product_id, $session_id, $user_id, 0, $activity_type, $activity_date );
							}
						}
					}
				}
			}
		}

		wp_send_json_success( array('done' => true) );
		die();
	}

	public function execute_cron_job() {
		check_ajax_referer( 'execute-cron-job', 'security' );

		$running = get_option( 'woocommerce_recommender_build_running', false );
		if ( empty( $running ) ) {
			ob_start();
			
			do_action( 'wc_recommender_build' );

			ob_end_clean();

			wp_send_json_success( array('status' => 'started') );
		} else {
			wp_send_json_success( array('status' => 'alreadyRunning') );
		}


		die();
	}

	public function handle_bulk_action( $action ) {
		global $wpdb, $woocommerce_recommender;

		if ( $action == 'build-recommendations' ) {



			if ( isset( $_POST['product_ids'] ) && !empty( $_POST['product_ids'] ) ) {
				$product_ids = array_map( 'intval', $_POST['product_ids'] );
				$builder = new WC_Recommender_Recorder( );

				foreach ( $product_ids as $product_id ) {
					$wpdb->query( $wpdb->prepare( "DELETE FROM $woocommerce_recommender->db_tbl_recommendations WHERE product_id = %d", $product_id ) );

					$builder->woocommerce_recommender_build_simularity( $product_id, array('viewed') );
					$builder->woocommerce_recommender_build_simularity( $product_id, array('completed') );
					$builder->woocommerce_build_purchased_together( $product_id, array('completed') );
				}

				WC_Recommendation_Engine::add_message( sprintf( __( 'Recommendations rebuilt for %d products', 'wc_recommender' ), count( $product_ids ) ) );
				return admin_url( 'admin.php?page=wc_recommender_admin' );
			} else {
				WC_Recommendation_Engine::add_error( __( 'Please choose at least one product', 'wc_recommender' ) );
				return false;
			}
		}
	}

}
