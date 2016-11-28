<?php
/**
 * WooCommerce URL Coupons
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@skyverge.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade WooCommerce URL Coupons to newer
 * versions in the future. If you wish to customize WooCommerce URL Coupons for your
 * needs please refer to http://docs.woothemes.com/document/url-coupons/ for more information.
 *
 * @package     WC-URL-Coupons/Admin
 * @author      SkyVerge
 * @copyright   Copyright (c) 2013-2016, SkyVerge, Inc.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;


/**
 * Admin class
 *
 * @since 2.0.0
 */
class WC_URL_Coupons_Admin {


	/**
	 * Setup admin class
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		// add per-coupon options
		add_action( 'woocommerce_coupon_options', array( $this, 'add_coupon_options' ) );
		add_action( 'woocommerce_coupon_options', array( $this, 'render_coupon_option_js' ), 11 );

		// save per-coupon options
		add_action( 'woocommerce_process_shop_coupon_meta', array( $this, 'save_coupon_options' ) );

		// purge unique URL from active list when parent coupon is trashed or deleted
		add_action( 'wp_trash_post', array( $this, 'purge_coupon_url' ) );

		// add settings to hide coupon code field
		add_filter( 'woocommerce_payment_gateways_settings', array( $this, 'admin_settings' ) );

		// add coupon meta to Smart Coupons export headers
		add_filter( 'wc_smart_coupons_export_headers', array( $this, 'smart_coupons_export_headers' ) );

		// add a 'URL slug' column to the coupon list table
		add_filter( 'manage_edit-shop_coupon_columns',        array( $this, 'add_url_slug_column_header' ), 20 );
		add_action( 'manage_shop_coupon_posts_custom_column', array( $this, 'add_url_slug_column' ) );
	}


	/**
	 * Add coupon options to the Coupon edit page
	 *
	 * @since 1.0
	 */
	public function add_coupon_options() {
		global $post;

		?>
		<div class="options_group">
			<?php

			/**
			 * Unique URL
			 *
			 * @since 2.2.1
			 * @param string $unique_url The unique URL for the coupon (defaults to empty string)
			 * @param int $coupon_id The shop coupon id
			 */
			$unique_url = apply_filters( 'wc_url_coupons_unique_url', get_post_meta( $post->ID, '_wc_url_coupons_unique_url', true ), $post->ID );

			// unique URL field
			woocommerce_wp_text_input( array(
				'id'          => '_wc_url_coupons_unique_url',
				'label'       => __( 'Unique URL', 'woocommerce-url-coupons' ),
				'description' => __( 'The URL that a customer can visit to have this coupon / product added to their cart.', 'woocommerce-url-coupons' ),
				'desc_tip'    => true,
				'value'       => $unique_url,
			) );

			// dropdown for product(s) to add to cart
			?>
			<p class="form-field _wc_url_coupons_product_ids_field">

				<label for="_wc_url_coupons_product_ids"><?php esc_html_e( 'Products to Add to Cart', 'woocommerce-url-coupons' ); ?></label>

				<input type="hidden" class="wc-product-search" data-multiple="true" style="width: 50%;" name="_wc_url_coupons_product_ids" data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'woocommerce-url-coupons' ); ?>" data-action="woocommerce_json_search_products_and_variations" data-selected="<?php

					/**
					 * Products to add to cart
					 *
					 * @since 2.2.1
					 * @param false|array $product_ids The product ids to add to cart
					 * @param int $coupon_id The shop coupon id
					 */
					$url_coupon_product_ids = apply_filters( 'wc_url_coupons_product_ids', get_post_meta( $post->ID, '_wc_url_coupons_product_ids', true ), $post->ID );

					$product_ids = array_filter( array_map( 'absint', (array) $url_coupon_product_ids ) );
					$json_ids    = array();

					foreach ( $product_ids as $product_id ) {
						$product = wc_get_product( $product_id );
						$json_ids[ $product_id ] = wp_kses_post( $product->get_formatted_name() );
					}

					echo esc_attr( json_encode( $json_ids ) );
				?>" value="<?php echo implode( ',', array_keys( $json_ids ) ); ?>" />

				<?php echo SV_WC_Plugin_Compatibility::wc_help_tip( __( 'Add these products to the customers cart when they visit the URL.', 'woocommerce-url-coupons' ) ); ?>
			</p>
			<?php

			/**
			 * Redirect target ID
			 *
			 * @since 2.2.1
			 * @param false|int $redirect_content_id The content id (or false if none set)
			 * @param int $coupon_id The shop coupon id
			 */
			$selected_page_id = apply_filters( 'wc_url_coupons_redirect_page_id', get_post_meta( $post->ID, '_wc_url_coupons_redirect_page', true ), $post->ID );

			/**
			 * Redirect target type
			 *
			 * @since 2.2.1
			 * @param false|string $redirect_content_type The content type (or false if none set)
			 * @param int $coupon_id The shop coupon id
			 */
			$selected_page_type = apply_filters( 'wc_url_coupons_redirect_page_type', get_post_meta( $post->ID, '_wc_url_coupons_redirect_page_type', true ), $post->ID );

			// redirect target selection formatted for enhanced input
			$selected_page_title = $this->get_selected_redirect_page_title( $selected_page_id, $selected_page_type );

			// enhanced select value
			$selected_value = ! empty( $selected_page_title )
				? array( $selected_page_type . '|' . $selected_page_id => esc_html( $selected_page_title ) )
				: array();

			// redirect to page dropdown field ?>
			<p class="form-field _wc_url_coupons_redirect_page_field">

				<label for="_wc_url_coupons_redirect_page"><?php esc_html_e( 'Page Redirect', 'woocommerce-url-coupons' ); ?></label>

				<input type="hidden"
				       name="_wc_url_coupons_redirect_page"
				       id="_wc_url_coupons_redirect_page"
				       class="sv-wc-enhanced-search"
				       style="min-width: 300px;"
				       data-multiple="false"
				       data-action="wc_url_coupons_json_search_page_redirects"
				       data-nonce="<?php echo wp_create_nonce( 'search-page-redirects' ); ?>"
				       data-placeholder="<?php esc_attr_e( 'Select a page to redirect to&hellip;', 'woocommerce-url-coupons' ); ?>"
				       data-allow_clear="true"
				       data-selected="<?php echo esc_attr( current( $selected_value ) );  ?>"
				       value="<?php echo esc_attr( key( $selected_value ) ); ?>" />

				<?php SV_WC_Helper::render_select2_ajax(); ?>
				<?php echo SV_WC_Plugin_Compatibility::wc_help_tip( __( 'Select the page the customer will be redirected to after visiting the URL. Leave blank to disable redirect.', 'woocommerce-url-coupons' ) ); ?>

				<input type="hidden" name="_wc_url_coupons_redirect_page_type" value="" id="_wc_url_coupons_redirect_page_type" />
			</p>

			<?php

				/**
				 * Defer coupon application
				 *
				 * @since 2.2.1
				 * @param false|string $defer_apply Checkbox option: 'yes', 'no' or false if not set
				 * @param int $coupon_id The shop coupon id
				 */
				$defer_apply = apply_filters( 'wc_url_coupons_defer_apply', get_post_meta( $post->ID, '_wc_url_coupons_defer_apply', true ), $post->ID );

				// defer apply option
				woocommerce_wp_checkbox( array(
					'id'          => '_wc_url_coupons_defer_apply',
					'label'       => __( 'Defer Apply', 'woocommerce-url-coupons' ),
					'description' => __( "Check this box to defer applying the coupon until the customer's cart meets the coupon's requirements.", 'woocommerce-url-coupons' ),
					'value'       => $defer_apply,
				) );

			?>
		</div>
		<?php
	}


	/**
	 * Get selected redirect page title
	 *
	 * @since 2.1.5
	 * @param int $page_id Object id
	 * @param string $page_type Object type
	 * @return string Term name or post type title
	 */
	private function get_selected_redirect_page_title( $page_id, $page_type ) {

		switch ( $page_type ) {

			case 'page':
			case 'pages':
			case 'posts':
				$page_title = get_the_title( $page_id );
			break;

			case 'product':
			case 'products':

				$product = wc_get_product( $page_id );
				$page_title = $product ? $product->get_title() : '';

			break;

			case 'category':
			case 'tag':
			case 'post_tag':
			case 'product_cat':
			case 'product_tag':

				$taxonomy   = 'post_tag' === $page_type ? 'tag' : $page_type;
				$term       = get_term_by( 'id', $page_id, $taxonomy );

				$page_title = isset( $term->name ) ? $term->name : '';

			break;

			default:
				$page_title = '';
			break;
		}

		return $page_title;
	}


	/**
	 * Render JS to add live preview
	 *
	 * @since 2.0.0
	 */
	public function render_coupon_option_js() {

		$home_url = home_url( '/' );

		wc_enqueue_js( "
			$( 'p._wc_url_coupons_unique_url_field' ).append( \"<span id='_wc_url_coupons_url_preview' class='description'>{$home_url}</span>\" );

			$( 'input[id=_wc_url_coupons_unique_url]' ).on( 'keyup change input', function() {
				$( 'span#_wc_url_coupons_url_preview' ).text( '{$home_url}' + $( this ).val() );
			} ).change();

			$( '#_wc_url_coupons_redirect_page' ).change( function() {
				var page      = $( this ).val(),
					page_type = '';
				if ( page ) {
					page_type = page.substr( 0, page.indexOf( '|' ) );
				}
				$( '#_wc_url_coupons_redirect_page_type' ).val( page_type );
			} ).change();
		" );
	}


	/**
	 * Get the redirect page data, used for the redirect page select
	 *
	 * @since 2.0.0
	 * @param string $search Optional search keyword
	 * @return array Associative array
	 */
	public function get_redirect_pages( $search = '' ) {

		$pages = array(
			'pages'       => array(),
			'products'    => array(),
			'category'    => array(),
			'post_tag'    => array(),
			'product_cat' => array(),
			'product_tag' => array(),
		);

		// add homepage
		$pages['pages'][-1] = array( 'type' => 'page', 'title' => __( 'Homepage', 'woocommerce-url-coupons' ) );

		// add pages
		foreach ( get_pages( array( 'sort_column' => 'menu_order' ) ) as $page ) {

			// indent child page titles
			$title = ( 0 === $page->post_parent ) ? $page->post_title : '&nbsp;&nbsp;&nbsp;' . $page->post_title;

			$pages['pages'][ $page->ID ] = array( 'type' => 'page', 'title' => $title );
		}

		// add products
		$args = array(
			'fields'      => 'ids',
			'post_type'   => array( 'product', 'product_variation' ),
			'post_status' => 'publish',
			'orderby'     => 'title',
			'order'       => 'ASC',
			'nopaging'    => true,
		);

		if ( ! empty( $search ) ) {
			$args['s'] = $search;
		}

		$products = get_posts( $args );

		foreach ( $products as $product_id ) {

			$product = wc_get_product( $product_id );
			$pages['products'][ $product_id ] = array( 'type' => 'product', 'title' => $product->get_formatted_name() );
		}

		// add taxonomies
		foreach ( $pages as $page_group => $_ ) {

			// bail for invalid or non-taxonomies (pages, products)
			if ( ! taxonomy_exists( $page_group ) || in_array( $page_group, array( 'pages', 'products' ) ) ) {
				continue;
			}

			$terms = get_terms( $page_group, array( 'hide_empty' => false, 'number' => 250 ) );

			if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {

				foreach ( $terms as $term ) {
					$pages[ $page_group ][ $term->term_id ] = array( 'type' => $page_group, 'title' => $term->name );
				}
			}
		}

		$groups = array(
			'pages'       => __( 'Pages', 'woocommerce-url-coupons' ),
			'products'    => __( 'Products', 'woocommerce-url-coupons' ),
			'category'    => __( 'Categories', 'woocommerce-url-coupons' ),
			'post_tag'    => __( 'Tags', 'woocommerce-url-coupons' ),
			'product_cat' => __( 'Product Categories', 'woocommerce-url-coupons' ),
			'product_tag' => __( 'Product Tags', 'woocommerce-url-coupons' ),
		);

		// set translated group titles, this is done here in order to allow
		// simplify the taxonomy handling code
		foreach ( $groups as $group => $title ) {
			if ( isset( $pages[ $group ] ) ) {
				$pages[ $title ] = $pages[ $group ];
				unset( $pages[ $group ] );
			}
		}

		/**
		 * Get redirect pages
		 *
		 * @since 2.0.0
		 * @param array $pages Associative array
		 * @param \WC_URL_Coupons_Admin $url_coupons Instance of this class
		 */
		return apply_filters( 'wc_url_coupons_redirect_pages', $pages, $this );
	}


	/**
	 * Save coupon options on Coupon edit page
	 *
	 * @since 1.0
	 * @param int $post_id coupon ID
	 */
	public function save_coupon_options( $post_id ) {

		if ( isset( $_POST['_wc_url_coupons_unique_url'] ) || isset( $_POST['_wc_url_coupons_redirect_page'] ) ) {

			$unique_url         = $_POST['_wc_url_coupons_unique_url'];
			$redirect_page      = $_POST['_wc_url_coupons_redirect_page'];
			$redirect_page_type = isset( $_POST['_wc_url_coupons_redirect_page_type'] ) ? $_POST['_wc_url_coupons_redirect_page_type'] : '';
			$page               = explode( '|', $redirect_page );
			$redirect_page_id   = isset( $page[1] ) ? $page[1] : '';

			// unique URL
			if ( empty( $unique_url ) ) {
				delete_post_meta( $post_id, '_wc_url_coupons_unique_url' );
			} else {
				update_post_meta( $post_id, '_wc_url_coupons_unique_url', sanitize_text_field( $unique_url ) );
			}

			// redirect
			if ( empty( $redirect_page ) ) {
				update_post_meta( $post_id, '_wc_url_coupons_redirect_page', 0 ); // 0 is checked in maybe_apply_coupons() to redirect to shop page since redirect is empty
				delete_post_meta( $post_id, '_wc_url_coupons_redirect_page_type' );
			} elseif ( $redirect_page_id && $redirect_page_type ) {
				update_post_meta( $post_id, '_wc_url_coupons_redirect_page', (int) $redirect_page_id );
				update_post_meta( $post_id, '_wc_url_coupons_redirect_page_type', sanitize_key( $redirect_page_type ) );

			}

			// products to add to cart
			$product_ids = isset( $_POST['_wc_url_coupons_product_ids'] ) ? $_POST['_wc_url_coupons_product_ids'] : array();

			// Select2 saves values as a comma-separated string
			if ( is_string( $product_ids ) ) {
				$product_ids = explode( ',', $product_ids );
			}

			if ( ! empty( $product_ids ) && is_array( $product_ids ) ) {
				update_post_meta( $post_id, '_wc_url_coupons_product_ids', array_map( 'absint', (array) $product_ids ) );
			} else {
				delete_post_meta( $post_id, '_wc_url_coupons_product_ids' );
			}

			// defer apply
			if ( isset( $_POST['_wc_url_coupons_defer_apply'] ) ) {
				$defer_apply = $_POST['_wc_url_coupons_defer_apply'];
				update_post_meta( $post_id, '_wc_url_coupons_defer_apply', sanitize_text_field( $defer_apply ) );
			} else {
				$defer_apply = '';
				delete_post_meta( $post_id, '_wc_url_coupons_defer_apply' );
			}

			$options = array(
				'coupon_id'          => $post_id,
				'unique_url'         => $unique_url,
				'redirect_page'      => $redirect_page_id,
				'redirect_page_type' => ! empty( $redirect_page_type ) ? $redirect_page_type : 'page',
				'product_ids'        => $product_ids,
				'defer_apply'        => $defer_apply,
			);

			// update active coupon array option
			$this->update_coupons( $options );
		}
	}


	/**
	 * Helper function to update the active coupon option array
	 *
	 * @since 1.0
	 * @param array $options coupon options
	 */
	private function update_coupons( $options ) {

		// load existing coupon urls
		$coupons = get_option( 'wc_url_coupons_active_urls', array() );

		// add coupon URL & Redirect page ID
		$coupons[ $options['coupon_id'] ] = array(
			'url'                => strtolower( $options['unique_url'] ),
			'redirect'           => (int) $options['redirect_page'],
			'redirect_page_type' => $options['redirect_page_type'],
			'products'           => ! empty( $options['product_ids'] ) && is_array( $options['product_ids'] )
				? array_map( 'absint', (array) $options['product_ids'] )
				: array(),
			'defer'              => $options['defer_apply'],
		);

		// remove coupon URL if blank
		if ( ! $options['unique_url'] ) {
			unset( $coupons[ $options['coupon_id'] ] );
		}

		// update the array
		update_option( 'wc_url_coupons_active_urls', $coupons );

		// clear the transient
		delete_transient( 'wc_url_coupons_active_urls' );
	}


	/**
	 * Remove the unique URL associated with a coupon when the coupon is trashed. This prevents a "coupon does not exist"
	 * error message when the unique URL is visited but the coupon is trashed
	 *
	 * @since 1.0.2
	 * @param int $coupon_id coupon ID
	 */
	public function purge_coupon_url( $coupon_id ) {

		// only purge for coupons
		if ( 'shop_coupon' !== get_post_type( $coupon_id ) ) {
			return;
		}

		$coupons = get_option( 'wc_url_coupons_active_urls' );

		// remove from active list
		if ( isset( $coupons[ $coupon_id ] ) ) {
			unset( $coupons[ $coupon_id ] );
		}

		// update active list
		update_option( 'wc_url_coupons_active_urls', $coupons );

		// clear transient
		delete_transient( 'wc_url_coupons_active_urls' );
	}


	/**
	 * Inject our admin settings into the Settings > Checkout page
	 *
	 * @since 1.2
	 * @param array $settings array of WooCommerce settings
	 * @return array array of WooCommerce settings
	 */
	public function admin_settings( $settings ) {

		$updated_settings = array();

		foreach ( $settings as $section ) {

			$updated_settings[] = $section;

			$section_id = 'woocommerce_calc_discounts_sequentially';

			// New section after the "General Options" section
			if ( isset( $section['id'] ) && $section_id === $section['id'] ) {

				$updated_settings[] = array(
					'title'         => __( 'Hide Coupon Code Field', 'woocommerce-url-coupons' ),
					'desc'          => __( 'Hide on cart page.', 'woocommerce-url-coupons' ),
					'desc_tip'      => __( 'Enable to hide the coupon code field on the cart page.', 'woocommerce-url-coupons' ),
					'id'            => 'wc_url_coupons_hide_coupon_field_cart',
					'type'          => 'checkbox',
					'default'       => 'no',
					'checkboxgroup' => 'start'
				);

				$updated_settings[] = array(
					'desc'          => __( 'Hide on checkout page.', 'woocommerce-url-coupons' ),
					'desc_tip'      => __( 'Enable to hide the coupon code field on the checkout page.', 'woocommerce-url-coupons' ),
					'id'            => 'wc_url_coupons_hide_coupon_field_checkout',
					'type'          => 'checkbox',
					'default'       => 'no',
					'checkboxgroup' => 'end'
				);

			}
		}

		return $updated_settings;
	}


	/**
	 * Smart Coupons export headers
	 *
	 * @since 1.2.0
	 * @param  array $coupon_postmeta_headers associative-array of meta keys and their associated titles to be included as column headers in the Smart Coupons export file
	 * @return array filtered associative-array of meta keys and their associated titles to be included as column headers
	 */
	public function smart_coupons_export_headers( $coupon_postmeta_headers ) {

		$wc_url_coupons_headers = array(
			'_wc_url_coupons_unique_url'    => __( 'Unique URL', 'woocommerce-url-coupons' ),
			'_wc_url_coupons_product_ids'   => __( 'Products to Add to Cart', 'woocommerce-url-coupons' ),
			'_wc_url_coupons_redirect_page' => __( 'Page Redirect', 'woocommerce-url-coupons' ),
			'_wc_url_coupons_defer_apply'   => __( 'Defer Apply', 'woocommerce-url-coupons' )
		);

		return array_merge( $coupon_postmeta_headers, $wc_url_coupons_headers );
	}


	/**
	 * Add 'URL Slug' column header to the Coupons list table
	 *
	 * @since 2.0.0
	 * @param array $column_headers
	 * @return array new columns
	 */
	public function add_url_slug_column_header( $column_headers ) {

		$column_headers['url_slug'] = __( 'URL Slug', 'woocommerce-url-coupons' );

		return $column_headers;
	}


	/**
	 * Add 'URL Slug' column content to the Coupons list table
	 *
	 * @since 2.0.0
	 * @param array $column
	 */
	public function add_url_slug_column( $column ) {

		if ( 'url_slug' === $column ) {

			$slug = get_post_meta( $GLOBALS['post']->ID, '_wc_url_coupons_unique_url', true );

			echo $slug ? esc_html( $slug ) : '&ndash;';
		}
	}


}
