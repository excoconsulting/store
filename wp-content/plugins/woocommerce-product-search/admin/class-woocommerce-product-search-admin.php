<?php
/**
 * class-woocommerce-product-search-admin.php
 *
 * Copyright (c) "kento" Karim Rahimpur www.itthinx.com
 *
 * This code is provided subject to the license granted.
 * Unauthorized use and distribution is prohibited.
 * See COPYRIGHT.txt and LICENSE.txt
 *
 * This code is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * This header and all notices must be kept intact.
 *
 * @author itthinx
 * @package woocommerce-product-search
 * @since 1.0.0
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings.
 */
class WooCommerce_Product_Search_Admin {

	const NONCE                 = 'woocommerce-product-search-admin-nonce';
	const SETTINGS_POSITION     = 999;
	const SETTINGS_ID           = 'product-search';
	const SECTION_WEIGHTS       = 'weights';
	const SECTION_THUMBNAILS    = 'thumbnails';
	const SECTION_CSS           = 'css';
	const SECTION_PERFORMANCE   = 'performance';
	const SECTION_HELP          = 'help';
	const HELP_POSITION         = 999;
	const MYSQL_INNODB_FULLTEXT = '5.6.4';
	const WPS_FT_DB_UPDATE_CAP  = 'install_plugins';
	const N_INDEXES             = 4;

	/**
	 * Register a hook on the init action.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'wp_init' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue_scripts' ) );
	}

	/**
	 * Registers the updater script and styles.
	 */
	public static function admin_enqueue_scripts() {
		wp_register_script( 'wps-ft-updater', WOO_PS_PLUGIN_URL . '/js/updater.js', array( 'jquery' ), WOO_PS_PLUGIN_VERSION, true );
		wp_register_style( 'wps-admin', WOO_PS_PLUGIN_URL . '/css/admin.css', array(), WOO_PS_PLUGIN_VERSION );
	}

	/**
	 * Admin setup.
	 */
	public static function wp_init() {

		global $wpdb;

		add_filter( 'plugin_action_links_'. plugin_basename( WOO_PS_FILE ), array( __CLASS__, 'admin_settings_link' ) );
		add_filter( 'woocommerce_settings_tabs_array', array( __CLASS__, 'woocommerce_settings_tabs_array' ), self::SETTINGS_POSITION );
		add_action( 'woocommerce_settings_' . self::SETTINGS_ID, array( __CLASS__, 'woocommerce_product_search' ) );
		add_action( 'woocommerce_settings_save_' . self::SETTINGS_ID, array( __CLASS__, 'save' ) );
		add_action( 'current_screen', array( __CLASS__, 'current_screen' ), self::HELP_POSITION );

		// create indexes
		if (
			isset( $_REQUEST['action'] ) &&
			( $_REQUEST['action'] == 'wps_ft_idx' ) &&
			isset( $_REQUEST['nonce'] ) &&
			wp_verify_nonce( $_REQUEST['nonce'], 'wps-ft-updater-js' ) &&
			current_user_can( self::WPS_FT_DB_UPDATE_CAP )
		) {
			@set_time_limit( 0 );
			@ignore_user_abort( true );

			global $wps_ft_db_update_errors;
			global $wps_ft_db_update_notices;

			$start = function_exists( 'microtime' ) ? microtime( true ) : time();

			// Create FULLTEXT indexes on wp_posts.post_title, .post_excerpt and .post_content
			$indexes = array();
			$results = $wpdb->get_results( "SHOW INDEX FROM $wpdb->posts WHERE Key_name = 'wps_ft_title'" );
			if ( empty( $results ) ) {
				$indexes[] = "ADD FULLTEXT INDEX wps_ft_title (post_title)";
			}
			$results = $wpdb->get_results( "SHOW INDEX FROM $wpdb->posts WHERE Key_name = 'wps_ft_excerpt'" );
			if ( empty( $results ) ) {
				$indexes[] = "ADD FULLTEXT INDEX wps_ft_excerpt (post_excerpt)";
			}
			$results = $wpdb->get_results( "SHOW INDEX FROM $wpdb->posts WHERE Key_name = 'wps_ft_content'" );
			if ( empty( $results ) ) {
				$indexes[] = "ADD FULLTEXT INDEX wps_ft_content (post_content)";
			}
			if ( !empty( $indexes ) ) {
				// We can't run the index creation for all in one go.
				// At MySQL 5.6.24 "InnoDB presently supports one FULLTEXT index creation at a time".
				foreach( $indexes as $index ) {
					$query = "ALTER TABLE $wpdb->posts $index";
					if ( $wpdb->query( $query ) === false ) {
						error_log( $wpdb->last_error );
						$wps_ft_db_update_errors[] = sprintf( __( "The table <code>$wpdb->posts</code> could not be updated; <code>%s</code> while running the query <code>%s</code>", WOO_PS_PLUGIN_DOMAIN ), $wpdb->last_error, $query );
					} else {
						$wps_ft_db_update_notices[] = sprintf( __( "The table <code>$wpdb->posts</code> has been updated using the query <code>%s</code>", WOO_PS_PLUGIN_DOMAIN ), $query );
					}
				}
			}

			// Create a FULLTEXT index on wp_terms.name
			$results = $wpdb->get_results( "SHOW INDEX FROM $wpdb->terms WHERE Key_name = 'wps_ft_name'" );
			if ( empty( $results ) ) {
				$query = "ALTER TABLE $wpdb->terms ADD FULLTEXT INDEX wps_ft_name (name)";
				if ( $wpdb->query( $query ) === false ) {
					error_log( $wpdb->last_error );
					$wps_ft_db_update_errors[] = sprintf( __( "The table <code>$wpdb->terms</code> could not be updated; <code>%s</code> while running the query <code>%s</code>", WOO_PS_PLUGIN_DOMAIN ), $wpdb->last_error, $query );
				} else {
					$wps_ft_db_update_notices[] = sprintf( __( "The table <code>$wpdb->terms</code> has been updated using the query <code>%s</code>", WOO_PS_PLUGIN_DOMAIN ), $query );
				}
			}

			$result = array(
				'time'    => ( function_exists( 'microtime' ) ? microtime( true ) : time() ) - $start,
				'notices' => $wps_ft_db_update_notices,
				'errors'  => $wps_ft_db_update_errors
			);
			echo json_encode( $result );
			exit;
		}

		// remove indexes
		if (
			isset( $_REQUEST['action'] ) &&
			( $_REQUEST['action'] == 'wps_ft_rmidx' ) &&
			isset( $_REQUEST['nonce'] ) &&
			wp_verify_nonce( $_REQUEST['nonce'], 'wps-ft-updater-js' ) &&
			current_user_can( self::WPS_FT_DB_UPDATE_CAP )
		) {
			@set_time_limit( 0 );
			@ignore_user_abort( true );

			global $wps_ft_db_update_errors;
			global $wps_ft_db_update_notices;

			$start = function_exists( 'microtime' ) ? microtime( true ) : time();

			$indexes = array();
			$results = $wpdb->get_results( "SHOW INDEX FROM $wpdb->posts WHERE Key_name = 'wps_ft_title'" );
			if ( !empty( $results ) ) {
				$indexes[] = "DROP INDEX wps_ft_title";
			}
			$results = $wpdb->get_results( "SHOW INDEX FROM $wpdb->posts WHERE Key_name = 'wps_ft_excerpt'" );
			if ( !empty( $results ) ) {
				$indexes[] = "DROP INDEX wps_ft_excerpt";
			}
			$results = $wpdb->get_results( "SHOW INDEX FROM $wpdb->posts WHERE Key_name = 'wps_ft_content'" );
			if ( !empty( $results ) ) {
				$indexes[] = "DROP INDEX wps_ft_content";
			}
			foreach( $indexes as $index ) {
				$query = "ALTER TABLE $wpdb->posts $index";
				if ( $wpdb->query( $query ) === false ) {
					error_log( $wpdb->last_error );
					$wps_ft_db_update_errors[] = sprintf( __( "The table <code>$wpdb->posts</code> could not be updated; <code>%s</code> while running the query <code>%s</code>", WOO_PS_PLUGIN_DOMAIN ), $wpdb->last_error, $query );
				} else {
					$wps_ft_db_update_notices[] = sprintf( __( "The table <code>$wpdb->posts</code> has been updated using the query <code>%s</code>", WOO_PS_PLUGIN_DOMAIN ), $query );
				}
			}

			$results = $wpdb->get_results( "SHOW INDEX FROM $wpdb->terms WHERE Key_name = 'wps_ft_name'" );
			if ( !empty( $results ) ) {
				$query = "ALTER TABLE $wpdb->terms DROP INDEX wps_ft_name";
				if ( $wpdb->query( $query ) === false ) {
					error_log( $wpdb->last_error );
					$wps_ft_db_update_errors[] = sprintf( __( "The table <code>$wpdb->terms</code> could not be updated; <code>%s</code> while running the query <code>%s</code>", WOO_PS_PLUGIN_DOMAIN ), $wpdb->last_error, $query );
				} else {
					$wps_ft_db_update_notices[] = sprintf( __( "The table <code>$wpdb->terms</code> has been updated using the query <code>%s</code>", WOO_PS_PLUGIN_DOMAIN ), $query );
				}
			}

			$result = array(
				'time'    => ( function_exists( 'microtime' ) ? microtime( true ) : time() ) - $start,
				'notices' => $wps_ft_db_update_notices,
				'errors'  => $wps_ft_db_update_errors
			);
			echo json_encode( $result );
			exit;
		}

	}

	/**
	 * Adds plugin links.
	 *
	 * @param array $links
	 * @param array $links with additional links
	 */
	public static function admin_settings_link( $links ) {
		if ( current_user_can( 'manage_woocommerce' ) ) {
			$url = self::get_admin_section_url();
			$links[] = '<a href="' . esc_url( $url ) . '">' . __( 'Settings', WOO_PS_PLUGIN_DOMAIN ) . '</a>';
			$links[] = '<a href="http://docs.woothemes.com/document/woocommerce-product-search/">' . __( 'Documentation', WOO_PS_PLUGIN_DOMAIN ) . '</a>';
		}
		return $links;
	}

	/**
	 * Adds the help section.
	 */
	public static function current_screen() {
		global $current_section;
		$screen = get_current_screen();
		if ( $screen && function_exists( 'wc_get_screen_ids' ) && in_array( $screen->id, wc_get_screen_ids() ) ) {
			$id = 'woocommerce_product_search_tab';
			$title = __( 'Search', WOO_PS_PLUGIN_DOMAIN );
			$content = '';
			$content .= '<div id="product-search-help-tab">';

			$content .= '<h3 class="section-heading">' . __( 'Search', WOO_PS_PLUGIN_DOMAIN ) . '</h3>';

			$content .= '<h4 style="border-bottom:1px solid #9d9d9d">' . __( 'Documentation', WOO_PS_PLUGIN_DOMAIN ) . '</h4>';
			$content .= '<p>';
			$content .= __( 'Please refer to the <a href="http://docs.woothemes.com/document/woocommerce-product-search/">WooCommerce Product Search</a> documentation page for more details.', WOO_PS_PLUGIN_DOMAIN );
			$content .= '</p>';

			$content .= '<h4 style="border-bottom:1px solid #9d9d9d">' . __( 'Setup', WOO_PS_PLUGIN_DOMAIN ) . '</h4>';
			$content .= '<p>';
			$content .= __( 'Enable the use of weights to improve the relevance in product search results, based on matches in product titles, excerpts, contents and tags.', WOO_PS_PLUGIN_DOMAIN );
			$content .= '</p>';
			$content .= '<p>';
			$content .= __( 'Weights can also be set for specific products and product categories.', WOO_PS_PLUGIN_DOMAIN );
			$content .= '</p>';
			$content .= '<p>';
			$content .= __( 'Positive weights inrease the relevance in product searches while negative weights decrease it.', WOO_PS_PLUGIN_DOMAIN );
			$content .= '</p>';
			$content .= '<p>';
			$content .= __( 'Place the <code>[woocommerce_product_search]</code> shortcode on a page or use the <em>WooCommerce Instant Product Search</em> widget in a sidebar.', WOO_PS_PLUGIN_DOMAIN );
			$content .= '</p>';

			$content .= '<h4 style="border-bottom:1px solid #9d9d9d">' . __( 'Search Weights', WOO_PS_PLUGIN_DOMAIN ) . '</h4>';
			$content .= '<p>' . sprintf( __( 'Go to <a href="%s"><strong>WooCommerce > Settings > Search</strong></a>, check the option <em>Use weights</em> and save the changes.', WOO_PS_PLUGIN_DOMAIN ), self::get_admin_section_url() ) . '</p>';
			$content .= '<p>' . __( 'The default values provide search results with an increased relevance for products with matching Titles, Product Short Descriptions and Tags in that order.', WOO_PS_PLUGIN_DOMAIN ) . '</p>';
			$content .= '<p>' . __( 'The relevance of a product in search results is based on matches in its Title, the Product Short Description (Excerpt), its Content and related Tags. Its relevance increases with the search weight of the categories that the product belongs to and its specific search weight.', WOO_PS_PLUGIN_DOMAIN ) . '</p>';
			$content .= '<p>' . __( 'To adjust the relevance of a product category, go to <strong>Products > Categories</strong>. Edit the desired category and indicate a <em>Search Weight</em> - the relevance of all products that belong to the category can be increased by indicating a positive value. If a product belongs to several categories, the maximum <em>Search Weight</em> is used.', WOO_PS_PLUGIN_DOMAIN ) . '</p>';
			$content .= '<p>' . __( 'To adjust the relevance of a single product, go to <strong>Products</strong>. Edit the desired product and under <strong>Product Data > Search</strong> indicate the desired <em>Search Weight</em>.', WOO_PS_PLUGIN_DOMAIN ) . '</p>';

			$content .= '<h4 style="border-bottom:1px solid #9d9d9d">' . __( 'Instant Search on a Page', WOO_PS_PLUGIN_DOMAIN ) . '</h4>';
			$content .= '<p>' . __( 'The following procedure provides describes how you can place an instant product search field on a page.', WOO_PS_PLUGIN_DOMAIN ) . '</p>';
			$content .= '<p>' . __( 'From your WordPress Dashboard go to <strong>Pages > Add New</strong>. Place the following shortcode on the page:', WOO_PS_PLUGIN_DOMAIN ) . '</p>';
			$content .= '<p>' . __( '<code>[woocommerce_product_search]</code>', WOO_PS_PLUGIN_DOMAIN ) . '</p>';
			$content .= '<p>' . __( 'Please make sure that the spelling is correct, all letters must be in lower case.', WOO_PS_PLUGIN_DOMAIN ) . '</p>';
			$content .= '<p>' . __( 'Click <em>Publish</em> to save the page content and publish the page.', WOO_PS_PLUGIN_DOMAIN ) . '</p>';
			$content .= '<p>' . __( 'Now click <em>View Page</em> which will show you the search field on your newly created page.', WOO_PS_PLUGIN_DOMAIN ) . '</p>';
			$content .= '<p>' . __( 'To test the field, at least one product must be published in your store. Start typing a search keyword, search results will show up below the field after you stop typing for an instant.', WOO_PS_PLUGIN_DOMAIN ) . '</p>';
			$content .= '<p>' . __( 'To refine the settings used, please refer to the advanced configuration options and the shortcode attributes described in the documentation.', WOO_PS_PLUGIN_DOMAIN ) . '</p>';

			$content .= '<h4 style="border-bottom:1px solid #9d9d9d">' . __( 'Instant Search Widget', WOO_PS_PLUGIN_DOMAIN ) . '</h4>';
			$content .= '<p>' . __( 'To use the widget, go to <strong>Appearance > Widgets</strong> and locate the <em>WooCommerce Instant Product Search</em> in the <em>Available Widgets</em> section.', WOO_PS_PLUGIN_DOMAIN ) . '</p>';
			$content .= '<p>' . __( 'Click the widget, then click one of the sidebar options that appear below and then click <em>Add Widget</em>. You can also drag and drop the widget onto an available sidebar.', WOO_PS_PLUGIN_DOMAIN ) . '</p>';
			$content .= '<p>' . __( 'Visit a page on your site where the sidebar appears and type in a keyword related to one or more of your products.', WOO_PS_PLUGIN_DOMAIN ) . '</p>';
			$content .= '<p>' . __( 'To fine-tune the widget, click the widget after placing it in one of your sidebars and review the available options. Please refer to the documentation for details on the advanced options.', WOO_PS_PLUGIN_DOMAIN ) . '</p>';
			$content .= '<p>' . __( 'Note that you can place more than one widget in one or more sidebars and that each widget can use its individual settings.', WOO_PS_PLUGIN_DOMAIN ) . '</p>';

			$content .= '<br/>';

			$content .= '</div>'; // #product-search-help-tab

			$screen->add_help_tab( array(
				'id'      => $id,
				'title'   => $title,
				'content' => $content
			) );
		}
	}

	/**
	 * Returns the admin URL for the default or given section.
	 * 
	 * @param string $section
	 */
	public static function get_admin_section_url( $section = '' ) {
		// In WC < 2.1.0 the admin URL was different and WC_VERSION was not defined.
		if ( ! defined( 'WC_VERSION' ) ) {
			$path = 'admin.php?page=woocommerce_settings&tab=product-search';
			
		} else {
			$path = 'admin.php?page=wc-settings&tab=product-search';
		}
		switch( $section ) {
			case self::SECTION_WEIGHTS :
			case self::SECTION_THUMBNAILS :
			case self::SECTION_CSS :
			case self::SECTION_PERFORMANCE :
			case self::SECTION_HELP :
				break;
			default :
				$section = '';
		}
		if ( !empty( $section ) ) {
			 $path .= '&section=' . $section;
		}
		return admin_url( $path );
	}

	/**
	 * Adds the Product Search tab to the WooCommerce Settings.
	 * 
	 * @param array $pages
	 * @return array of settings pages
	 */
	public static function woocommerce_settings_tabs_array( $pages ) {
		$pages['product-search'] = __( 'Search', WOO_PS_PLUGIN_DOMAIN );
		return $pages;
	}

	/**
	 * Records changes made to the settings.
	 */
	public static function save() {

		global $current_section;

		if ( empty( $current_section ) ) {
			$current_section = self::SECTION_WEIGHTS;
		}

		if ( !current_user_can( 'manage_woocommerce' ) ) {
			wp_die( __( 'Access denied.', WOO_PS_PLUGIN_DOMAIN ) );
		}

		$options = get_option( 'woocommerce-product-search', null );
		if ( $options === null ) {
			if ( add_option( 'woocommerce-product-search', array(), null, 'no' ) ) {
				$options = get_option( 'woocommerce-product-search' );
			}
		}

		if ( isset( $_POST['submit'] ) ) {
			if ( wp_verify_nonce( $_POST[self::NONCE], 'set' ) ) {

				switch( $current_section ) {

					case self::SECTION_WEIGHTS :
						$options[WooCommerce_Product_Search::USE_WEIGHTS]    = isset( $_POST[WooCommerce_Product_Search::USE_WEIGHTS] );
						$options[WooCommerce_Product_Search::WEIGHT_TITLE]   = isset( $_POST[WooCommerce_Product_Search::WEIGHT_TITLE] ) ? intval( $_POST[WooCommerce_Product_Search::WEIGHT_TITLE] ) : WooCommerce_Product_Search::WEIGHT_TITLE_DEFAULT;
						$options[WooCommerce_Product_Search::WEIGHT_EXCERPT] = isset( $_POST[WooCommerce_Product_Search::WEIGHT_EXCERPT] ) ? intval( $_POST[WooCommerce_Product_Search::WEIGHT_EXCERPT] ) : WooCommerce_Product_Search::WEIGHT_EXCERPT_DEFAULT;
						$options[WooCommerce_Product_Search::WEIGHT_CONTENT] = isset( $_POST[WooCommerce_Product_Search::WEIGHT_CONTENT] ) ? intval( $_POST[WooCommerce_Product_Search::WEIGHT_CONTENT] ) : WooCommerce_Product_Search::WEIGHT_CONTENT_DEFAULT;
						$options[WooCommerce_Product_Search::WEIGHT_TAGS]    = isset( $_POST[WooCommerce_Product_Search::WEIGHT_TAGS] ) ? intval( $_POST[WooCommerce_Product_Search::WEIGHT_TAGS] ) : WooCommerce_Product_Search::WEIGHT_TAGS_DEFAULT;
						break;

					case self::SECTION_THUMBNAILS :
						$thumbnail_width = isset( $_POST[WooCommerce_Product_Search_Thumbnail::THUMBNAIL_WIDTH] ) ? intval( $_POST[WooCommerce_Product_Search_Thumbnail::THUMBNAIL_WIDTH] ) : WooCommerce_Product_Search_Thumbnail::THUMBNAIL_DEFAULT_WIDTH;
						if ( ( $thumbnail_width < 0 ) || $thumbnail_width > WooCommerce_Product_Search_Thumbnail::THUMBNAIL_MAX_DIM ) {
							$thumbnail_width = WooCommerce_Product_Search_Thumbnail::THUMBNAIL_DEFAULT_WIDTH;
						}
						$options[WooCommerce_Product_Search_Thumbnail::THUMBNAIL_WIDTH] = $thumbnail_width;

						$thumbnail_height = isset( $_POST[WooCommerce_Product_Search_Thumbnail::THUMBNAIL_HEIGHT] ) ? intval( $_POST[WooCommerce_Product_Search_Thumbnail::THUMBNAIL_HEIGHT] ) : WooCommerce_Product_Search_Thumbnail::THUMBNAIL_DEFAULT_HEIGHT;
						if ( ( $thumbnail_height < 0 ) || $thumbnail_height > WooCommerce_Product_Search_Thumbnail::THUMBNAIL_MAX_DIM ) {
							$thumbnail_height = WooCommerce_Product_Search_Thumbnail::THUMBNAIL_DEFAULT_HEIGHT;
						}
						$options[WooCommerce_Product_Search_Thumbnail::THUMBNAIL_HEIGHT] = $thumbnail_height;

						$options[WooCommerce_Product_Search_Thumbnail::THUMBNAIL_CROP] = isset( $_POST[WooCommerce_Product_Search_Thumbnail::THUMBNAIL_CROP] );
						$options[WooCommerce_Product_Search_Thumbnail::THUMBNAIL_USE_PLACEHOLDER] = isset( $_POST[WooCommerce_Product_Search_Thumbnail::THUMBNAIL_USE_PLACEHOLDER] );
						break;

					case self::SECTION_CSS :
						$options[WooCommerce_Product_Search::ENABLE_CSS]        = isset( $_POST[WooCommerce_Product_Search::ENABLE_CSS] );
						$options[WooCommerce_Product_Search::ENABLE_INLINE_CSS] = isset( $_POST[WooCommerce_Product_Search::ENABLE_INLINE_CSS] );
						$options[WooCommerce_Product_Search::INLINE_CSS]        = isset( $_POST[WooCommerce_Product_Search::INLINE_CSS] ) ? trim( strip_tags( $_POST[WooCommerce_Product_Search::INLINE_CSS] ) ) : WooCommerce_Product_Search::INLINE_CSS_DEFAULT;
						break;

					case self::SECTION_PERFORMANCE :
						$options[WooCommerce_Product_Search::USE_FULLTEXT] =
							isset( $_POST[WooCommerce_Product_Search::USE_FULLTEXT] ) &&
							empty( $_POST['innodb_fulltext_unsupported'] ) &&
							empty( $_POST['not_all_indexes'] );
						$options[WooCommerce_Product_Search::FULLTEXT_WILDCARDS] =
							isset( $_POST[WooCommerce_Product_Search::FULLTEXT_WILDCARDS] );
						$options[WooCommerce_Product_Search::LOG_QUERY_TIMES] =
							isset( $_POST[WooCommerce_Product_Search::LOG_QUERY_TIMES] );
						break;
				}

				update_option( 'woocommerce-product-search', $options );
			}
		}
	}

	/**
	 * Renders the admin section.
	 */
	public static function woocommerce_product_search() {

		global $current_section, $wpdb;

		if ( empty( $current_section ) ) {
			$current_section = self::SECTION_WEIGHTS;
		}

		if ( !current_user_can( 'manage_woocommerce' ) ) {
			wp_die( __( 'Access denied.', WOO_PS_PLUGIN_DOMAIN ) );
		}

		wp_enqueue_script( 'product-search-admin', WOO_PS_PLUGIN_URL . '/js/product-search-admin.js', array( 'jquery', ), WOO_PS_PLUGIN_VERSION, true );
		wp_enqueue_style( 'wps-admin' );

		$options = get_option( 'woocommerce-product-search', null );
		if ( $options === null ) {
			if ( add_option( 'woocommerce-product-search', array(), null, 'no' ) ) {
				$options = get_option( 'woocommerce-product-search' );
			}
		}

		$innodb_fulltext_supported = false;
		$all_indexes               = false;
		if ( $mysql_version = $wpdb->get_var( "SELECT version() AS version" ) ) {
			$innodb_fulltext_supported = ( version_compare( $mysql_version, self::MYSQL_INNODB_FULLTEXT ) >= 0 );
		}
		if ( $innodb_fulltext_supported ) {
			$indexes = array();
			$missing_indixes = array();
			$results = $wpdb->get_results( "SHOW INDEX FROM $wpdb->posts WHERE Key_name = 'wps_ft_title'" );
			if ( empty( $results ) ) {
				$missing_indixes[] = __( 'The wps_ft_title index is missing.', WOO_PS_PLUGIN_DOMAIN );
			} else {
				$indexes[] = __( 'The wps_ft_title index is present.', WOO_PS_PLUGIN_DOMAIN );
			}
			$results = $wpdb->get_results( "SHOW INDEX FROM $wpdb->posts WHERE Key_name = 'wps_ft_excerpt'" );
			if ( empty( $results ) ) {
				$missing_indixes[] = __( 'The wps_ft_excerpt index is missing.', WOO_PS_PLUGIN_DOMAIN );
			} else {
				$indexes[] = __( 'The wps_ft_excerpt index is present.', WOO_PS_PLUGIN_DOMAIN );
			}
			$results = $wpdb->get_results( "SHOW INDEX FROM $wpdb->posts WHERE Key_name = 'wps_ft_content'" );
			if ( empty( $results ) ) {
				$missing_indixes[] = __( 'The wps_ft_content index is missing.', WOO_PS_PLUGIN_DOMAIN );
			} else {
				$indexes[] = __( 'The wps_ft_content index is present.', WOO_PS_PLUGIN_DOMAIN );
			}
			$results = $wpdb->get_results( "SHOW INDEX FROM $wpdb->terms WHERE Key_name = 'wps_ft_name'" );
			if ( empty( $results ) ) {
				$missing_indixes[] = __( 'The wps_ft_name index is missing.', WOO_PS_PLUGIN_DOMAIN );
			} else {
				$indexes[] = __( 'The wps_ft_name index is present.', WOO_PS_PLUGIN_DOMAIN );
			}
			$all_indexes = ( count( $indexes ) == self::N_INDEXES );
		}
		if ( !$innodb_fulltext_supported || !$all_indexes ) {
			$options[WooCommerce_Product_Search::USE_FULLTEXT] = false;
			update_option( 'woocommerce-product-search', $options );
		}
		$use_fulltext      = $innodb_fulltext_supported && $all_indexes && isset( $options[WooCommerce_Product_Search::USE_FULLTEXT] ) ? $options[WooCommerce_Product_Search::USE_FULLTEXT] : WooCommerce_Product_Search::USE_FULLTEXT_DEFAULT;
		$ft_min_word_len   = $wpdb->get_var( "SELECT variable_value FROM information_schema.global_variables WHERE variable_name = 'ft_min_word_len'" );
		$fulltext_wildcards = isset( $options[WooCommerce_Product_Search::FULLTEXT_WILDCARDS] ) ? $options[WooCommerce_Product_Search::FULLTEXT_WILDCARDS] : WooCommerce_Product_Search::FULLTEXT_WILDCARDS_DEFAULT;
		$log_query_times    = isset( $options[WooCommerce_Product_Search::LOG_QUERY_TIMES] ) ? $options[WooCommerce_Product_Search::LOG_QUERY_TIMES] : WooCommerce_Product_Search::LOG_QUERY_TIMES_DEFAULT;

		$use_weights       = isset( $options[WooCommerce_Product_Search::USE_WEIGHTS] ) ? $options[WooCommerce_Product_Search::USE_WEIGHTS] : WooCommerce_Product_Search::USE_WEIGHTS_DEFAULT;
		$weight_title      = isset( $options[WooCommerce_Product_Search::WEIGHT_TITLE] ) ? $options[WooCommerce_Product_Search::WEIGHT_TITLE] : WooCommerce_Product_Search::WEIGHT_TITLE_DEFAULT;
		$weight_excerpt    = isset( $options[WooCommerce_Product_Search::WEIGHT_EXCERPT] ) ? $options[WooCommerce_Product_Search::WEIGHT_EXCERPT] : WooCommerce_Product_Search::WEIGHT_EXCERPT_DEFAULT;
		$weight_content    = isset( $options[WooCommerce_Product_Search::WEIGHT_CONTENT] ) ? $options[WooCommerce_Product_Search::WEIGHT_CONTENT] : WooCommerce_Product_Search::WEIGHT_CONTENT_DEFAULT;
		$weight_tags       = isset( $options[WooCommerce_Product_Search::WEIGHT_TAGS] ) ? $options[WooCommerce_Product_Search::WEIGHT_TAGS] : WooCommerce_Product_Search::WEIGHT_TAGS_DEFAULT;

		$thumbnail_width   = isset( $options[WooCommerce_Product_Search_Thumbnail::THUMBNAIL_WIDTH] ) ? $options[WooCommerce_Product_Search_Thumbnail::THUMBNAIL_WIDTH] : WooCommerce_Product_Search_Thumbnail::THUMBNAIL_DEFAULT_WIDTH;
		$thumbnail_height  = isset( $options[WooCommerce_Product_Search_Thumbnail::THUMBNAIL_HEIGHT] ) ? $options[WooCommerce_Product_Search_Thumbnail::THUMBNAIL_HEIGHT] : WooCommerce_Product_Search_Thumbnail::THUMBNAIL_DEFAULT_HEIGHT;
		$thumbnail_crop    = isset( $options[WooCommerce_Product_Search_Thumbnail::THUMBNAIL_CROP] ) ? $options[WooCommerce_Product_Search_Thumbnail::THUMBNAIL_CROP] : WooCommerce_Product_Search_Thumbnail::THUMBNAIL_DEFAULT_CROP;
		$thumbnail_use_placeholder = isset( $options[WooCommerce_Product_Search_Thumbnail::THUMBNAIL_USE_PLACEHOLDER] ) ? $options[WooCommerce_Product_Search_Thumbnail::THUMBNAIL_USE_PLACEHOLDER] : WooCommerce_Product_Search_Thumbnail::THUMBNAIL_USE_PLACEHOLDER_DEFAULT;

		$enable_css        = isset( $options[WooCommerce_Product_Search::ENABLE_CSS] ) ? $options[WooCommerce_Product_Search::ENABLE_CSS] : WooCommerce_Product_Search::ENABLE_CSS_DEFAULT;
		$enable_inline_css = isset( $options[WooCommerce_Product_Search::ENABLE_INLINE_CSS] ) ? $options[WooCommerce_Product_Search::ENABLE_INLINE_CSS] : WooCommerce_Product_Search::ENABLE_INLINE_CSS_DEFAULT;
		$inline_css        = isset( $options[WooCommerce_Product_Search::INLINE_CSS] ) ? $options[WooCommerce_Product_Search::INLINE_CSS] : WooCommerce_Product_Search::INLINE_CSS_DEFAULT;

		echo '<style type="text/css">';
		echo 'div.product-search-tabs ul li a { outline: none; }';
		echo '</style>';

		echo '<div class="woocommerce-product-search">';

		echo '<div class="product-search-tabs">';
		echo '<ul class="subsubsub">';
		echo '<li class="tab-header">';
		printf( '<a class="%s" href="%s">', isset( $current_section ) && $current_section == 'weights' ? 'current' : '', esc_url( self::get_admin_section_url( self::SECTION_WEIGHTS ) ) );
		echo __( 'Weights', WOO_PS_PLUGIN_DOMAIN );
		echo '</a>';
		echo '|';
		echo '</li>';
		echo '<li class="tab-header">';
		printf( '<a class="%s" href="%s">', isset( $current_section ) && $current_section == 'thumbnails' ? 'current' : '', esc_url( self::get_admin_section_url( self::SECTION_THUMBNAILS ) ) );
		echo __( 'Thumbnails', WOO_PS_PLUGIN_DOMAIN );
		echo '</a>';
		echo '|';
		echo '</li>';
		echo '<li class="tab-header">';
		printf( '<a class="%s" href="%s">', isset( $current_section ) && $current_section == 'css' ? 'current' : '', esc_url( self::get_admin_section_url( self::SECTION_CSS ) ) );
		echo __( 'CSS', WOO_PS_PLUGIN_DOMAIN );
		echo '</a>';
		echo '|';
		echo '</li>';
		echo '<li class="tab-header">';
		printf( '<a class="%s" href="%s">', isset( $current_section ) && $current_section == 'performance' ? 'current' : '', esc_url( self::get_admin_section_url( self::SECTION_PERFORMANCE ) ) );
		echo __( 'Performance', WOO_PS_PLUGIN_DOMAIN );
		echo '</a>';
		echo ' ';
		echo '</li>';

		echo '<li class="tab-header">';
		echo '<a id="wps-faq-help-trigger" href="#">';
		printf(
			'<img class="help_tip" data-tip="%s" src="%s" height="16" width="16" alt="%s" />',
			__( 'The <strong>Search</strong> section in the <strong>Help</strong> tab above provides a brief overview.', WOO_PS_PLUGIN_DOMAIN ),
			esc_url( WC()->plugin_url() . '/assets/images/help.png' ),
			__( 'FAQ', WOO_PS_PLUGIN_DOMAIN )
		);
		echo '</a>';
		echo '</li>';
		echo '</ul>';
		echo '</div>'; // .product-search-tabs

		echo '<div style="clear:both"></div>';

		echo '<form action="" name="options" method="post">';
		echo '<div>';

		switch( $current_section ) {

			case self::SECTION_WEIGHTS :
				echo '<div id="product-search-weights-tab" class="product-search-tab">';
				echo '<h3 class="section-heading">' . __( 'Search Weights', WOO_PS_PLUGIN_DOMAIN ) . '</h3>';

				echo '<p>';
				echo '<label>';
				printf( '<input name="%s" type="checkbox" %s />', WooCommerce_Product_Search::USE_WEIGHTS, $use_weights ? ' checked="checked" ' : '' );
				echo ' ';
				_e( 'Use weights', WOO_PS_PLUGIN_DOMAIN);
				echo '</label>';
				echo '</p>';
				echo '<p class="description">';
				_e( 'If enabled, the relevance in product search results is enhanced by taking weights into account.', WOO_PS_PLUGIN_DOMAIN );
				echo '</p>';

				echo '<h4>' . __( 'Relevance', WOO_PS_PLUGIN_DOMAIN ) . '</h4>';

				echo '<p class="description">';
				_e( 'The following weights determine the relevance of matches in the product title, excerpt, content and tags.', WOO_PS_PLUGIN_DOMAIN );
				echo ' ';
				_e( 'By default, the higher title weight will promote search results that have matches in the title.', WOO_PS_PLUGIN_DOMAIN );
				echo ' ';
				_e( 'The weight of products and product categories can be modified individually, the computed sum of weights determines the relevance of a product in search results.', WOO_PS_PLUGIN_DOMAIN );
				echo '</p>';

				echo '<table>';

				echo '<tr>';
				echo '<td>';
				printf( '<label for="%s">', WooCommerce_Product_Search::WEIGHT_TITLE );
				_e( 'Title', WOO_PS_PLUGIN_DOMAIN);
				echo '</label>';
				echo '</td>';
				echo '<td>';
				printf( '<input name="%s" style="width:5em;text-align:right;" type="number" value="%d" />', WooCommerce_Product_Search::WEIGHT_TITLE, esc_attr( $weight_title ) );
				echo '</td>';
				echo '</tr>';

				echo '<tr>';
				echo '<td>';
				printf( '<label for="%s">', WooCommerce_Product_Search::WEIGHT_EXCERPT );
				_e( 'Excerpt', WOO_PS_PLUGIN_DOMAIN);
				echo '</label>';
				echo '</td>';
				echo '<td>';
				printf( '<input name="%s" style="width:5em;text-align:right;" type="number" value="%d" />', WooCommerce_Product_Search::WEIGHT_EXCERPT, esc_attr( $weight_excerpt ) );
				echo '</td>';
				echo '</tr>';

				echo '<tr>';
				echo '<td>';
				printf( '<label for="%s">', WooCommerce_Product_Search::WEIGHT_CONTENT );
				_e( 'Content', WOO_PS_PLUGIN_DOMAIN);
				echo '</label>';
				echo '</td>';
				echo '<td>';
				printf( '<input name="%s" style="width:5em;text-align:right;" type="number" value="%d" />', WooCommerce_Product_Search::WEIGHT_CONTENT, esc_attr( $weight_content ) );
				echo '</td>';
				echo '</tr>';

				echo '<tr>';
				echo '<td>';
				printf( '<label for="%s">', WooCommerce_Product_Search::WEIGHT_TAGS );
				_e( 'Tags', WOO_PS_PLUGIN_DOMAIN);
				echo '</label>';
				echo '</td>';
				echo '<td>';
				printf( '<input name="%s" style="width:5em;text-align:right;" type="number" value="%d" />', WooCommerce_Product_Search::WEIGHT_TAGS, esc_attr( $weight_tags ) );
				echo '</td>';
				echo '</tr>';

				echo '</table>';

				echo '</div>'; // #product-search-weights-tab
				break;

			case self::SECTION_THUMBNAILS :
				echo '<div id="product-search-thumbnails-tab" class="product-search-tab">';
				echo '<h3 class="section-heading">' . __( 'Thumbnails', WOO_PS_PLUGIN_DOMAIN ) . '</h3>';

				echo '<p>';
				echo __( 'These settings are related to the <code>[woocommerce_product_search]</code> shortcode, the <em>WooCommerce Instant Product Search</em> widget and the <em>WooCommerce Product Search</em> extension\'s API functions.', WOO_PS_PLUGIN_DOMAIN );
				echo '</p>';

				echo '<h4>' . __( 'Presentation', WOO_PS_PLUGIN_DOMAIN ) . '</h4>';

				echo '<p class="description">';
				_e( 'Width and height in pixels used for thumbnails displayed in product search results.', WOO_PS_PLUGIN_DOMAIN);
				echo '</p>';

				echo '<p>';
				echo '<label>';
				_e( 'Width', WOO_PS_PLUGIN_DOMAIN);
				echo ' ';
				printf( '<input name="%s" style="width:5em;text-align:right;" type="text" value="%d" />', WooCommerce_Product_Search_Thumbnail::THUMBNAIL_WIDTH, esc_attr( $thumbnail_width ) );
				echo ' ';
				_e( 'px', WOO_PS_PLUGIN_DOMAIN);
				echo '</label>';
				echo '</p>';

				echo '<p>';
				echo '<label>';
				_e( 'Height', WOO_PS_PLUGIN_DOMAIN);
				echo ' ';
				printf( '<input name="%s" style="width:5em;text-align:right;" type="text" value="%d" />', WooCommerce_Product_Search_Thumbnail::THUMBNAIL_HEIGHT, esc_attr( $thumbnail_height ) );
				echo ' ';
				_e( 'px', WOO_PS_PLUGIN_DOMAIN);
				echo '</label>';
				echo '</p>';

				echo '<p>';
				echo '<label>';
				printf( '<input name="%s" type="checkbox" %s />', WooCommerce_Product_Search_Thumbnail::THUMBNAIL_CROP, $thumbnail_crop ? ' checked="checked" ' : '' );
				echo ' ';
				_e( 'Crop thumbnails', WOO_PS_PLUGIN_DOMAIN);
				echo '</label>';
				echo '</p>';
				echo '<p class="description">';
				_e( 'If enabled, the thumbnail images are cropped to match the dimensions exactly. Otherwise the thumbnails will be adjusted in size while matching the aspect ratio of the original image.', WOO_PS_PLUGIN_DOMAIN);
				echo '</p>';

				echo '<p>';
				echo '<label>';
				printf( '<input name="%s" type="checkbox" %s />', WooCommerce_Product_Search_Thumbnail::THUMBNAIL_USE_PLACEHOLDER, $thumbnail_use_placeholder ? ' checked="checked" ' : '' );
				echo ' ';
				_e( 'Placeholder thumbnails', WOO_PS_PLUGIN_DOMAIN);
				echo '</label>';
				echo '</p>';
				echo '<p class="description">';
				_e( 'If enabled, products without a featured product image will show a default placeholder thumbnail image.', WOO_PS_PLUGIN_DOMAIN);
				echo '</p>';

				echo '</div>'; #product-search-thumbnails-tab
				break;

			case self::SECTION_CSS :
				echo '<div id="product-search-css-tab" class="product-search-tab">';
				echo '<h3 class="section-heading">' . __( 'CSS', WOO_PS_PLUGIN_DOMAIN ) . '</h3>';

				echo '<p>';
				echo __( 'These settings are related to the <code>[woocommerce_product_search]</code> shortcode, the <em>WooCommerce Instant Product Search</em> widget and the <em>WooCommerce Product Search</em> extension\'s API functions.', WOO_PS_PLUGIN_DOMAIN );
				echo '</p>';

				echo '<h4>' . __( 'Standard Stylesheet', WOO_PS_PLUGIN_DOMAIN ) . '</h4>';

				echo '<p>';
				echo '<label>';
				printf( '<input name="%s" type="checkbox" %s />', WooCommerce_Product_Search::ENABLE_CSS, $enable_css ? ' checked="checked" ' : '' );
				echo ' ';
				_e( 'Use the standard stylesheet', WOO_PS_PLUGIN_DOMAIN);
				echo '</label>';
				echo '</p>';
				echo '<p class="description">';
				_e( 'If this option is enabled, the standard stylesheet is loaded when the product search is displayed.', WOO_PS_PLUGIN_DOMAIN);
				echo '</p>';

				echo '<h4>' . __( 'Inline Styles', WOO_PS_PLUGIN_DOMAIN ) . '</h4>';

				echo '<p>';
				echo '<label>';
				printf( '<input name="%s" type="checkbox" %s />', WooCommerce_Product_Search::ENABLE_INLINE_CSS, $enable_inline_css ? ' checked="checked" ' : '' );
				echo ' ';
				_e( 'Use inline styles', WOO_PS_PLUGIN_DOMAIN);
				echo '</label>';
				echo '</p>';
				echo '<p class="description">';
				_e( 'If this option is enabled, the inline styles are used when the product search is displayed.', WOO_PS_PLUGIN_DOMAIN);
				echo '</p>';

				echo '<p>';
				echo '<label>';
				_e( 'Inline styles', WOO_PS_PLUGIN_DOMAIN);
				echo '<br/>';
				printf( '<textarea style="font-family:monospace;width:50%%;height:25em;" name="%s">%s</textarea>', WooCommerce_Product_Search::INLINE_CSS, stripslashes( esc_textarea( $inline_css ) ) );
				echo '</label>';
				echo '</p>';

				echo '</div>'; // #product-search-css-tab
				break;

			case self::SECTION_PERFORMANCE :
				wp_enqueue_script( 'wps-ft-updater' );
				echo '<div id="product-search-performance-tab" class="product-search-tab">';
				echo '<h3 class="section-heading">' . __( 'Performance', WOO_PS_PLUGIN_DOMAIN ) . '</h3>';

				echo '<h4>';
				echo __( 'Full-Text Search', WOO_PS_PLUGIN_DOMAIN );
				echo '</h4>';

				echo '<p class="description">';
				_e( 'If enabled, MySQL´s <a href="http://dev.mysql.com/doc/refman/5.6/en/fulltext-search.html">Full-Text Search Functions</a> are used to find search results with performance improvements.', WOO_PS_PLUGIN_DOMAIN );
				echo '</p>';

				echo '<p class="description">';
				printf( __( 'The minimum length of a word to be included in a Full-Text Search on your setup is %d.', WOO_PS_PLUGIN_DOMAIN ), $ft_min_word_len );
				echo ' ';
				_e( 'See <a href="https://dev.mysql.com/doc/refman/5.6/en/server-system-variables.html#sysvar_ft_min_word_len">ft_min_word_len</a> for details on how to adjust this value.', WOO_PS_PLUGIN_DOMAIN );
				echo ' ';
				_e( 'If you modify this parameter, the indexes must be rebuilt by removing and creating them again here.', WOO_PS_PLUGIN_DOMAIN );
				echo ' ';
				_e( 'The desired minimum number of characters used with the <code>[woocommerce_product_search]</code> shortcode, the <em>WooCommerce Instant Product Search</em> widget or the API functions used in templates should be synchronized for consistent results.', WOO_PS_PLUGIN_DOMAIN );
				echo ' ';
				_e( 'The minimum does not apply if Wildcard Mode is enabled.', WOO_PS_PLUGIN_DOMAIN );
				echo '</p>';

				if ( $innodb_fulltext_supported ) {

					if ( current_user_can( self::WPS_FT_DB_UPDATE_CAP ) ) {
						global $wps_ft_db_update_errors;
						if ( !empty( $wps_ft_db_update_errors ) ) {
							echo '<p style="color:#f00">';
							echo implode( '<br/>', $wps_ft_db_update_errors );
							echo '</p>';
						}
						global $wps_ft_db_update_notices;
						if ( !empty( $wps_ft_db_update_notices ) ) {
							echo '<p style="color:#060">';
							echo implode( '<br/>', $wps_ft_db_update_notices );
							echo '</p>';
						}

						if ( $all_indexes ) {
							echo '<p style="color:#060">';
							echo __( 'All indexes are present. Full-Text Search can be used.', WOO_PS_PLUGIN_DOMAIN );
							echo '</p>';
						} else {
							if ( $all_indexes == 0 ) {
								echo '<p>';
								echo __( 'To enable Full-Text Search, the database needs to be updated and indexes created.', WOO_PS_PLUGIN_DOMAIN );
								echo '</p>';
							} else {
								echo '<p style="color:#f00">';
								echo __( 'Some indexes are still missing and Full-Text Search can not yet be used. Please try to create the missing indexes using the button below.', WOO_PS_PLUGIN_DOMAIN );
								echo '</p>';
								if ( count( $missing_indixes ) > 0 ) {
									echo '<ul><li>';
									echo implode( '</li><li>', $missing_indixes );
									echo '</li></ul>';
								}
							}
						}

						if ( !$all_indexes ) {
							echo '<p>';
							echo __( 'Creating indexes can take a while and may make your site inaccessible to your visitors during the process.', WOO_PS_PLUGIN_DOMAIN );
							echo ' ';
							echo __( 'Especially for sites with a large product base, it is highly recommended to run this process only during low traffic hours.', WOO_PS_PLUGIN_DOMAIN );
							echo '</p>';
							echo '<p>';
							echo '<strong>';
							echo __( 'Make a FULL BACKUP of your site and database before creating indexes!', WOO_PS_PLUGIN_DOMAIN );
							echo '</strong>';
							echo '</p>';

							$js_nonce = wp_create_nonce( 'wps-ft-updater-js' );
							echo '<p>';

							printf( '<input class="button wps-ft-update-button" type="button" id="wps_ft_idxs" name="wps_ft_idxs" value="%s" />', __( 'Create Indexes', WOO_PS_PLUGIN_DOMAIN ) );

							echo ' ';
							echo __( 'This will start the update process immediately.', WOO_PS_PLUGIN_DOMAIN );
							echo '</p>';

							echo '<div id="wps-ft-updater-status"></div>';
							echo '<div id="wps-ft-updater-update"></div>';
							echo '<div id="wps-ft-updater-blinker"></div>';

							echo '<script type="text/javascript">';
							echo 'if ( typeof jQuery !== "undefined" ) {';
							echo 'jQuery(document).ready(function(){';
							echo 'jQuery("#wps_ft_idxs").click(function(e){';
							echo 'e.stopPropagation();';
							printf(
								'wpsFtUpdater.start("%s","%s");',
								add_query_arg(
									array(
										'action' => 'wps_ft_idx',
										'nonce'  => $js_nonce
									),
									admin_url( 'admin-ajax.php' )
								),
								self::get_admin_section_url( self::SECTION_PERFORMANCE )
							);
							echo '});'; // #wps_ft_idxs click
							echo '});'; // ready
							echo '}';
							echo '</script>';
						}

						if ( count( $indexes ) > 0 ) {
							echo '<div style="float:right;padding:1em;" id="wps_ft_idx_remove_toggle">';
							echo sprintf( '<span title="%s" style="cursor:pointer;border-bottom:1px dotted #000;">', __( 'Click to display index removal options.', WOO_PS_PLUGIN_DOMAIN ) );
							echo __( 'Index removal', WOO_PS_PLUGIN_DOMAIN );
							echo '</span>';
							echo '</div>';
							echo '<div id="wps_ft_idx_remove">';
							echo '<p>';
							echo __( 'If you wish to revert the changes made to your database, you can use the index removal process provided below.', WOO_PS_PLUGIN_DOMAIN );
							echo ' ';
							echo __( 'If you want to keep using Full-Text Search, do not remove the indexes.', WOO_PS_PLUGIN_DOMAIN );
							echo ' ';
							echo __( 'Removing indexes can take a while and may make your site inaccessible to your visitors during the process.', WOO_PS_PLUGIN_DOMAIN );
							echo ' ';
							echo '<em>';
							echo __( 'Make a FULL BACKUP of your site and database before removing indexes!', WOO_PS_PLUGIN_DOMAIN );
							echo '</em>';
							echo '</p>';
							echo '<p>';

							printf( '<input class="button wps-ft-update-button" type="button" id="wps_ft_rmidxs" name="wps_ft_rmidxs" value="%s" />', __( 'Remove Indexes', WOO_PS_PLUGIN_DOMAIN ) );

							echo ' ';
							echo __( 'This will process removals immediately.', WOO_PS_PLUGIN_DOMAIN );
							echo '</p>';

							echo '<div id="wps-ft-updater-status"></div>';
							echo '<div id="wps-ft-updater-update"></div>';
							echo '<div id="wps-ft-updater-blinker"></div>';

							$js_nonce = wp_create_nonce( 'wps-ft-updater-js' );
							echo '<script type="text/javascript">';
							echo 'if ( typeof jQuery !== "undefined" ) {';
							echo 'jQuery(document).ready(function(){';
							echo 'jQuery("#wps_ft_rmidxs").click(function(e){';
							echo 'e.stopPropagation();';
							printf(
								'wpsFtUpdater.start("%s","%s");',
								add_query_arg(
									array(
										'action' => 'wps_ft_rmidx',
										'nonce'  => $js_nonce
									),
									admin_url( 'admin-ajax.php' )
								),
								self::get_admin_section_url( self::SECTION_PERFORMANCE )
							);
							echo '});'; // #wps_ft_rmidxs click
							echo '});'; // ready
							echo '}';
							echo '</script>';
							echo '</div>';

							echo '<script type="text/javascript">';
							echo 'if (typeof jQuery !== "undefined") {';
							echo 'jQuery("#wps_ft_idx_remove").hide();';
							echo 'jQuery("#wps_ft_idx_remove_toggle").click(function(e){';
							echo 'jQuery("#wps_ft_idx_remove").toggle();';
							echo '});';
							echo '}';
							echo '</script>';
						}
					} else {
						echo '<p style="color:#f00">';
						printf( __( 'You do not have the required privileges to run the required database update. The <code>%s</code> capability is required.', WOO_PS_PLUGIN_DOMAIN ), self::WPS_FT_DB_UPDATE_CAP );
						echo '</p>';
					}

					if ( $all_indexes ) {
						echo '<div class="wps-ft-options">';

						echo '<p>';
						echo '<label>';
						printf( '<input name="%s" type="checkbox" %s />', WooCommerce_Product_Search::USE_FULLTEXT, $use_fulltext ? ' checked="checked" ' : '' );
						echo ' ';
						_e( 'Enable Full-Text Search', WOO_PS_PLUGIN_DOMAIN);
						echo '</label>';
						echo '</p>';

						echo '<p>';
						echo '<label>';
						printf( '<input name="%s" type="checkbox" %s />', WooCommerce_Product_Search::FULLTEXT_WILDCARDS, $fulltext_wildcards ? ' checked="checked" ' : '' );
						echo ' ';
						_e( 'Wildcard Mode', WOO_PS_PLUGIN_DOMAIN);
						echo '</label>';
						echo '</p>';
						echo '<p class="description">';
						_e( 'With Wildcard Mode enabled, words match if they begin with a search term.', WOO_PS_PLUGIN_DOMAIN );
						echo ' ';
						_e( 'If disabled, only full words matching a search term will produce results.', WOO_PS_PLUGIN_DOMAIN );
						echo ' ';
						_e( 'This option is only effective when Full-Text Search is enabled.', WOO_PS_PLUGIN_DOMAIN );
						echo '</p>';

						echo '</div>';

					} else {
						echo '<input type="hidden" name="not_all_indexes" value="1"/>';
					}

				} else {
					echo '<p style="color:#f00">';
					echo sprintf( __( 'Your current setup does not support InnoDB Full-Text Search. MySQL version %s or later is required. See <a href="http://dev.mysql.com/doc/refman/5.6/en/fulltext-restrictions.html">Full-Text Restrictions</a> for details.', WOO_PS_PLUGIN_DOMAIN ), self::MYSQL_INNODB_FULLTEXT );
					echo '<input type="hidden" name="innodb_fulltext_unsupported" value="1"/>';
					echo '</p>';
				}

				echo '<div class="wps-performance-options">';

				echo '<h4>';
				echo __( 'Logs', WOO_PS_PLUGIN_DOMAIN );
				echo '</h4>';

				echo '<p>';
				echo '<label>';
				printf( '<input name="%s" type="checkbox" %s />', WooCommerce_Product_Search::LOG_QUERY_TIMES, $log_query_times ? ' checked="checked" ' : '' );
				echo ' ';
				_e( 'Log main query times', WOO_PS_PLUGIN_DOMAIN);
				echo '</label>';
				echo '</p>';
				echo '<p class="description">';
				_e( 'If enabled, the query times for search terms will be logged. <a href="https://codex.wordpress.org/Debugging_in_WordPress">Debugging</a> must be enabled for query times to be recorded in the log.', WOO_PS_PLUGIN_DOMAIN );
				echo '</p>';

				echo '</div>'; // .wps-performance-options

				echo '</div>'; // #product-search-performance-tab
				break;
		}

		global $hide_save_button;
		$hide_save_button = true;

		wp_nonce_field( 'set', self::NONCE );
		wp_nonce_field( 'woocommerce-settings' );
		echo '<p>';
		echo '<input class="button button-primary" type="submit" name="submit" value="' . __( 'Save changes', WOO_PS_PLUGIN_DOMAIN ) . '"/>';
		echo '</p>';
		echo '</div>';

		echo '</form>';

		echo '</div>';

	}
}
WooCommerce_Product_Search_Admin::init();
