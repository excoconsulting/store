<?php
/**
 * Plugin Name: WooCommerce Free Gift Coupons
 * Plugin URI: http://www.woothemes.com/products/free-gift-coupons/
 * Description: Add a free product to the cart when a coupon is entered
 * Version: 1.1.0
 * Author: Kathy Darling
 * Author URI: http://kathyisawesome.com
 * Requires at least: 3.9
 * Tested up to: 4.4.0
 * Requires at least WooCommerce: 2.1
 * WooCommerce tested up to: 2.5.0 
 *
 * Text Domain: wc_free_gift_coupons
 * Domain Path: /languages/
 *
 * @package WooCommerce Free Gift Coupons
 * @category Core
 * @author Kathy Darling
 *
 * Copyright: © 2012 Kathy Darling.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

if ( ! defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

/**
 * Required functions
 */
if ( ! function_exists( 'woothemes_queue_update' ) )
	require_once( 'woo-includes/woo-functions.php' );

/**
 * Plugin updates
 */
woothemes_queue_update( plugin_basename( __FILE__ ), 'e1c4570bcc412b338635734be0536062', '414577' );

// Quit right now if WooCommerce is not active
if ( ! is_woocommerce_active() )
	return;


if ( ! class_exists( 'WC_Free_Gift_Coupons' ) ) :

/**
 * Main WC_Free_Gift_Coupons Class
 *
 * @class WC_Free_Gift_Coupons
 * @version	1.0
 */
class WC_Free_Gift_Coupons {

	/**
	 * @var string
	 */
	public static $version = '1.1.0';

	/**
	 * Free Gift Coupons pseudo constructor
	 * @access public
	 * @return WC_Free_Gift_Coupons
	 * @since 1.0
	 */
	public static function init() {
		// make translation-ready
		add_action( 'plugins_loaded', array( __CLASS__, 'load_textdomain_files' ), 10, 0 );

		// add the free_gift coupon type
		add_filter( 'woocommerce_coupon_discount_types', array( __CLASS__, 'discount_types' ) );

		// load a little script for the coupon interface
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_scripts' ) );

		// add and save coupon meta
		add_action( 'woocommerce_coupon_options', array( __CLASS__, 'coupon_options' ) );
		add_action( 'woocommerce_process_shop_coupon_meta', array( __CLASS__, 'process_shop_coupon_meta' ), 10, 2 );

		// front end scripts
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'frontend_scripts' ), 20 );

		// add the gift item when coupon is applied
		add_action( 'woocommerce_applied_coupon', array( __CLASS__, 'apply_coupon' ) );

		// add compatibility for Subscriptions
		add_filter( 'woocommerce_subscriptions_validate_coupon_type', array( __CLASS__, 'ignore_free_gift' ), 10, 2 );

		// change the price to ZERO/Free on gift item
		add_filter( 'woocommerce_add_cart_item', array( __CLASS__, 'add_cart_item' ), 5, 1 );
		add_filter( 'woocommerce_get_cart_item_from_session', array( __CLASS__, 'get_cart_item_from_session' ), 5, 2 );

		// disable multiple quantities of free item
		add_filter( 'woocommerce_cart_item_quantity', array( __CLASS__, 'cart_item_quantity' ), 5, 2 );

		// Remove Bonus item if coupon code is removed or no longer valid
		add_action( 'woocommerce_check_cart_items', array( __CLASS__, 'check_cart_items' ) );

		// Display as Free! in cart and in orders
		add_filter( 'woocommerce_cart_item_price', array( __CLASS__, 'cart_item_price' ), 10, 2 );
		add_filter( 'woocommerce_cart_item_subtotal', array( __CLASS__, 'cart_item_price' ), 10, 2 );
		add_filter( 'woocommerce_order_formatted_line_subtotal', array( __CLASS__, 'cart_item_price' ), 10, 2 );

		// Remove free gifts from shipping calcs & enable free shipping if required
		add_filter( 'woocommerce_cart_shipping_packages', array( __CLASS__, 'remove_free_shipping_items' ) );
		add_filter( 'woocommerce_shipping_free_shipping_is_available', array( __CLASS__, 'enable_free_shipping'), 20, 2 );
		add_filter( 'woocommerce_shipping_legacy_free_shipping_is_available', array( __CLASS__, 'enable_free_shipping'), 20, 2 );

		// add order item meta
		add_action( 'woocommerce_add_order_item_meta', array( __CLASS__, 'add_order_item_meta' ), 10, 3 );

	}

	/**
	 * Check is the installed version of WooCommerce is 2.3 or newer.
	 * props to Brent Shepard
	 *
	 * @return	boolean
	 * @access 	public
	 * @since 2.1
	 */
	public static function is_woocommerce_2_3() {
		_deprecated( __METHOD__, '1.1.0', 'wc_is_version()' );
		return self::wc_is_version( '2.3' );
	}

	/**
	 * Check the installed version of WooCommerce is greater than $version argument
	 *
	 * @param   $version
	 * @return	boolean
	 * @access 	public
	 * @since   1.1.0
	 */
	public static function wc_is_version( $version = '2.6' ) {
		if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, $version ) >= 0 ) {
			return true;
		} else {
			return false;
		}
	}


	/**
	 * Load localisation files
	 * @access public
	 * @return void
	 * @since 1.0
	 */
	public static function load_textdomain_files() {
		load_plugin_textdomain( 'wc_free_gift_coupons', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	* Add a new coupon type
	*
	* @access public
	* @param array $types - available coupon types
	* @return array
	* @since 1.0
	*/
	public static function discount_types( $types ){
		$types['free_gift'] = __( 'Free Gift', 'wc_free_gift_coupons' );
		return $types;
	}

	/**
	 * Load admin script
	 * @access public
	* @return void
	* @since 1.0
	 */
	public static function admin_scripts() {

		$screen = get_current_screen();
	
		// Coupon scripts
		if ( in_array( $screen->id, array( 'shop_coupon', 'edit-shop_coupon' ) ) ){
			$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
			wp_enqueue_script( 'woocommerce_free_gift_coupon_meta', plugins_url( 'assets/js/free-gift-coupon-meta-box' . $suffix . '.js' , __FILE__ ), false, self::$version );
		}

	}

	/**
	 * Output the new Coupon metabox fields
	 * @access public
	 * @return HTML
	 * @since 1.0
	 */
	public static function coupon_options() {
		global $post;

		$gift_ids = get_post_meta( $post->ID, 'gift_ids', true );
		if ( ! empty( $gift_ids ) ) {
			$gift_ids = array_map( 'absint', explode( ',', $gift_ids ) );
		}

		// Free Gift Product ids
?>
		<p class="form-field show_if_free_gift"><label for="gift_ids"><?php _e( 'Free Gifts', 'wc_free_gift_coupons' ) ?></label>
		<?php if( self::wc_is_version( '2.3' ) ){ ?>

				<input type="hidden" class="wc-product-search" style="width: 50%;" name="gift_ids" data-placeholder="<?php _e( 'Search for a product&hellip;', 'wc_free_gift_coupons' ); ?>" data-action="woocommerce_json_search_products_and_variations" data-allow_clear="true" data-multiple="true" data-selected="<?php

					$json_ids    = array();
					if ( is_array( $gift_ids ) && 0 < count( $gift_ids ) ) {
						foreach ( $gift_ids as $gift_id ) {
							$product = wc_get_product( $gift_id );
							$json_ids[ $gift_id ] = wp_kses_post( $product->get_formatted_name() );
						}
					}

					echo esc_attr( json_encode( $json_ids ) );
				?>" value="<?php echo implode( ',', array_keys( $json_ids ) ); ?>" />

			<?php } else { ?>
			<select id="gift_ids" name="gift_ids[]" class="ajax_chosen_select_products_and_variations" multiple="multiple" data-placeholder="<?php _e( 'Search for a product&hellip;', 'wc_free_gift_coupons' ); ?>">
				<?php
					if ( is_array( $gift_ids ) && 0 < count( $gift_ids ) ) {
						foreach ( $gift_ids as $gift_id) {
							$product = get_product( $gift_id );
							echo '<option value="' . esc_attr( $gift_id ) . '" selected="selected">' . wp_kses_post( $product->get_formatted_name() ) . '</option>';
						}
					}
				?>
			</select>

		<?php } ?>

		<img class="help_tip" data-tip="<?php _e( 'This is the product you are giving away with this coupon. It will automatically be added to the cart.', 'wc_free_gift_coupons' ) ?>" src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" />
		</p>

		<?php 				
		// free shipping for free gift

		if( self::wc_is_version( '2.6' ) ){

			woocommerce_wp_checkbox( array( 'id' => 'free_gift_shipping', 'label' => __( 'Free Shipping', 'wc_free_gift_coupons' ), 'wrapper_class' => 'show_if_free_gift', 'description' => sprintf( __( 'Check this box if the free gift should not incur a shipping cost. A %sfree shipping rate%s must be enabled in the shipping zones and be set to require "a valid free shipping coupon" (see the "Free Shipping Requires" setting).', 'wc_free_gift_coupons' ), '<a href="' . admin_url('admin.php?page=wc-settings&tab=shipping&section=WC_Shipping_Free_Shipping' ) . '">', '</a>' ) ) );
		
			// show a warning if deprecated method is being used
			$options = get_option( 'woocommerce_free_shipping_settings' );
			if ( $options && isset( $options['enabled'] ) && 'yes' === $options['enabled'] ) {  ?>
				<p class="form-field"><strong><?php printf( __( 'Notice: The Free Shipping method is deprecated in WooCommerce 2.6.0 - to continue offering free shipping for gifts, please set up a new rate within your %sShipping Zones%s.', 'wc_free_gift_coupons' ), '<a href="' . admin_url('admin.php?page=wc-settings&tab=shipping&section') . '">', '</a>' );?></strong></p>
			<?php
			}

		} else {

			woocommerce_wp_checkbox( array( 'id' => 'free_gift_shipping', 'label' => __( 'Free Shipping', 'wc_free_gift_coupons' ), 'wrapper_class' => 'show_if_free_gift', 'description' => sprintf( __( 'Check this box if the free gift should not incur a shipping cost. The <a href="%s">free shipping method</a> must be enabled and be set to require "a valid free shipping coupon" (see the "Free Shipping Requires" setting).', 'wc_free_gift_coupons' ), admin_url('admin.php?page=wc-settings&tab=shipping&section=WC_Shipping_Free_Shipping')) ) );

		}

	}

	/**
	 * Save the new coupon metabox field data
	 * @access public
	 * @param integer $post_id
	 * @param obect $post
	 * @return void
	* @since 1.0
	 */
	public static function process_shop_coupon_meta( $post_id, $post ) {

		// sanitize data
		if ( isset( $_POST['gift_ids'] ) ) {
			if( self::wc_is_version( '2.3' ) ){
				$gift_ids = implode( ',', array_filter( array_map( 'intval', explode( ',', $_POST['gift_ids'] ) ) ) );
			} else {
				$gift_ids = implode( ',', array_filter( array_map( 'intval', (array) $_POST['gift_ids'] ) ) );
			}
		} else {
			$gift_ids = '';
		}

		$free_gift_shipping = isset( $_POST['free_gift_shipping'] ) ? 'yes' : 'no';

		// save
		update_post_meta( $post_id, 'gift_ids', $gift_ids );
		update_post_meta( $post_id, 'free_gift_shipping', $free_gift_shipping );

	}

	/**
	 * Add the gift item to the cart when coupon is applied
	 * @access public
	 * @param string $coupon_code
	 * @return void
	 * @since 1.1.0
	 */
	public static function frontend_scripts(){
		if ( is_cart() && defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.6.0-beta-1', '>' ) ) {
			$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
			wp_enqueue_script( 'wc_free_gift_coupons', plugins_url( 'assets/js/free-gift-coupon-frontend' . $suffix . '.js' , __FILE__ ), array( 'wc-cart' ), self::$version );
		}
	}

	/**
	 * Add the gift item to the cart when coupon is applied
	 * @access public
	 * @param string $coupon_code
	 * @return void
	 * @since 1.0
	 */
	public static function apply_coupon( $coupon_code ){

		// Sanitize coupon code
		$coupon_code = apply_filters( 'woocommerce_coupon_code', $coupon_code );

		// Get the coupon
		$coupon = new WC_Coupon( $coupon_code );

		if ( $coupon->id && $coupon->discount_type && $coupon->discount_type == 'free_gift' ) {

			$gift_ids = get_post_meta( $coupon->id, 'gift_ids', true );
			$gift_ids = array_map( 'absint', explode( ',', $gift_ids ) );

			if ( ! empty ( $gift_ids ) ) {
				// add the coupon code with a `free_gift` parameter
				foreach ( $gift_ids as $product_id ){

					$variation_id = '';
					$variations = array();

					// Ensure we don't add a variation to the cart directly by variation ID
					if ( 'product_variation' == get_post_type( $product_id ) ) {
						$variation_id = $product_id;
						$product_id   = wp_get_post_parent_id( $variation_id );
						// get the variation
						$variation = get_product( $variation_id );
						$variations = $variation->get_variation_attributes();
					}

					WC()->cart->add_to_cart( $product_id, 1, $variation_id, $variations, array( 'free_gift' => $coupon_code ) );
				}
			}

			WC()->cart->calculate_totals();

		}
	}


	/**
	 * Prevent Subscriptions validating free gift coupons
	 * @access public
	 * @param bool $validate
	 * @param obj $coupon
	 * @return bool
	 * @since 1.0.7
	 */
	public static function ignore_free_gift( $validate, $coupon ) {

	    if ( 'free_gift' === $coupon->type ) {
	        $validate = false;
	    }

	    return $validate;
	}


	/**
	 * Change the price on the gift item to be zero
	 * @access public
	 * @param array $cart_item
	 * @return array
	 * @since 1.0
	 */
	public static function add_cart_item( $cart_item ) {

		// Adjust price in cart if bonus item
		if ( ! empty ( $cart_item['free_gift'] ) ){
			$cart_item['data']->set_price( 0 );
		}
			
		return $cart_item;
	}

	/**
	 * Adjust session values on the gift item
	 * @access public
	 * @param array $cart_item
	 * @param array $values
	 * @return array
	 * @since 1.0
	 */
	public static function get_cart_item_from_session( $cart_item, $values ) {

		if ( ! empty( $values['free_gift'] ) ) {
			$cart_item['free_gift'] = $values['free_gift'];
			$cart_item = self::add_cart_item( $cart_item );
		}

		return $cart_item;

	}

	/**
	 * Disable quantity inputs in cart
	 * @access public
	 * @param string $product_quantity
	 * @param string $cart_item_key
	 * @return string
	 * @since 1.0
	 */
	public static function cart_item_quantity( $product_quantity, $cart_item_key ){

		if ( ! empty ( WC()->cart->cart_contents[$cart_item_key]['free_gift'] ) )
			$product_quantity = sprintf( '1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key );

		return $product_quantity;
	}

	/**
	 * Removes gift item from cart if coupon is removed or invalid
	 * @access public
	 * @return void
	 * @since 1.0
	 */
	public static function check_cart_items() {

		$cart_coupons = (array) WC()->cart->applied_coupons;

		foreach ( WC()->cart->get_cart() as $cart_item_key => $values ) {

			if( isset( $values['free_gift'] ) && ! in_array( $values['free_gift'], $cart_coupons ) ){

				WC()->cart->set_quantity( $cart_item_key, 0 );

				wc_add_notice( __( 'A gift item which is no longer available was removed from your cart.', 'wc_free_gift_coupons' ), 'error' );

				// Flag totals for refresh
				WC()->session->set( 'refresh_totals', true );
			}
		}

	}

	/**
	 * Instead of $0, show Free! in the cart/order summary
	 * @access public
	 * @param string $price
	 * @param array $cart_item
	 * @return string
	 * @since 1.0
	 */
	public static function cart_item_price( $price, $cart_item ){
		if( isset( $cart_item['free_gift' ] ) )
				$price = __( 'Free!', 'wc_free_gift_coupons' );

			return $price;
	}


	/**
	 * Unset the free items from the packages needing shipping calculations
	 * @access public
	 * @param array $packages
	 * @return array
	 * @since 1.0.7
	 */
	public static function remove_free_shipping_items( $packages ) {

		if( $packages ) foreach( $packages as $i => $package ){ 

			$free_shipping_count = 0;
			$remove_items = array();
			$total_count = count($package['contents']);

			foreach ( $package['contents'] as $key => $item ) {
				
				// if the item is a free gift item get free shipping status
				if( isset( $item['free_gift'] ) ){

					$coupon_code = apply_filters( 'woocommerce_coupon_code', $item['free_gift'] );

					// Get the coupon
					$coupon = new WC_Coupon( $coupon_code );

					if ( 'yes' == get_post_meta( $coupon->id, 'free_gift_shipping', true ) ) {
						$remove_items[$key] = $item;
						$free_shipping_count++;
					} 

				} 

				// if the free gift with free shipping is the only item then switch 
				// shipping to free shipping. otherwise delete free gift from package calcs
				if ( $total_count == $free_shipping_count ){

					$ship_via = array( 'free_shipping' );

					// WC 2.6+: check for legacy mode
					if( self::wc_is_version( '2.6.' ) ){

						// get available shipping methods
						$methods = WC()->shipping()->get_shipping_methods();
						
						// if legacy free shipping is enabled use that, otherwise use regular free shipping
						if( isset( $methods['legacy_free_shipping'] ) && $methods['legacy_free_shipping']->is_enabled() ){
							$ship_via = array( 'legacy_free_shipping' );
						} 

					}

					$packages[$i]['ship_via'] = $ship_via;
					
				} else {
					$remaining_packages = array_diff_key( $packages[$i]['contents'], $remove_items );
					$packages[$i]['contents'] = $remaining_packages;
				}

			}

		}

		return $packages;
	}


	/**
	 * If the free gift w/ free shipping is the only item in the cart, enable free shipping
	 * @access public
	 * @param array $packages
	 * @return array
	 * @since 1.0.7
	 */
	public static function enable_free_shipping( $is_available, $package ) { 

		if( count( $package['contents'] == 1 ) && self::check_for_free_gift_with_free_shipping( $package ) ){
			$is_available = true;
		}
	 
		return $is_available;
	}


	/**
	 * Check shipping package for a free gift with free shipping
	 * @access public
	 * @param array $package
	 * @return boolean
	 * @since 1.1.0
	 */
	public static function check_for_free_gift_with_free_shipping( $package ) { 

		$has_free_gift_with_free_shipping = false;

		// loop through the items looking for one in the eligible array
		foreach ( $package['contents'] as $item ) {

			// if the item is a free gift item get free shipping status
			if( isset( $item['free_gift'] ) ){ 

				$coupon_code = apply_filters( 'woocommerce_coupon_code', $item['free_gift'] );

				// Get the coupon
				$coupon = new WC_Coupon( $coupon_code );

				if( 'yes' == get_post_meta( $coupon->id, 'free_gift_shipping', true ) ){
					$has_free_gift_with_free_shipping = true;
					break;
				} 

			} 

		}
	 
		return $has_free_gift_with_free_shipping;
	}


	/**
	 * When a new order is inserted, add item meta noting this item was a free gift
	 * @access public
	 * @param integer $item_id
	 * @param array $values
	 * @return void
	 * @since 1.0
	 */
	public static function add_order_item_meta( $item_id, $values ) {
		if ( isset( $values['free_gift'] ) ){
			wc_add_order_item_meta( $item_id, '_free_gift', $values['free_gift'] );
		}
	}

} // end class

endif; // class exists check

// boot up the plugin
WC_Free_Gift_Coupons::init();