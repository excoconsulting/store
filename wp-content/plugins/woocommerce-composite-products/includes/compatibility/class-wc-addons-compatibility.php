<?php
/**
 * Product Addons and NYP Compatibility.
 *
 * @version 3.3.6
 * @since   3.3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_CP_Addons_Compatibility {

	public static $addons_prefix             = '';
	public static $nyp_prefix                = '';
	public static $compat_composited_product = '';

	public static function init() {

		// Support for Product Addons.
		add_action( 'woocommerce_composited_product_add_to_cart', array( __CLASS__, 'addons_display_support' ), 10, 3 );
		add_filter( 'product_addons_field_prefix', array( __CLASS__, 'addons_cart_prefix' ), 9, 2 );

		add_filter( 'woocommerce_addons_price_for_display_product', array( __CLASS__, 'addons_price_for_display_product' ) );

		// Support for NYP.
		add_action( 'woocommerce_composited_product_add_to_cart', array( __CLASS__, 'nyp_display_support' ), 9, 3 );
		add_filter( 'nyp_field_prefix', array( __CLASS__, 'nyp_cart_prefix' ), 9, 2 );

		// Validate add to cart NYP and Addons.
		add_filter( 'woocommerce_composite_component_add_to_cart_validation', array( __CLASS__, 'validate_component_nyp_and_addons' ), 10, 7 );

		// Add addons identifier to composited item stamp.
		add_filter( 'woocommerce_composite_component_cart_item_identifier', array( __CLASS__, 'composited_item_addons_identifier' ), 10, 2 );

		// Add NYP identifier to composited item stamp.
		add_filter( 'woocommerce_composite_component_cart_item_identifier', array( __CLASS__, 'composited_item_nyp_stamp' ), 10, 2 );

		// Before and after add-to-cart handling.
		add_action( 'woocommerce_composited_product_before_add_to_cart', array( __CLASS__, 'before_composited_add_to_cart' ), 10, 5 );
		add_action( 'woocommerce_composited_product_after_add_to_cart', array( __CLASS__, 'after_composited_add_to_cart' ), 10, 5 );

		// Load child NYP/Addons data from the parent cart item data array.
		add_filter( 'woocommerce_composited_cart_item_data', array( __CLASS__, 'get_composited_cart_item_data_from_parent' ), 10, 2 );

		// Add option to disable Addons at component level.
		add_action( 'woocommerce_composite_component_admin_advanced_html', array( __CLASS__, 'component_addons_disable' ), 40, 3 );

		// Save option to disable Addons at component level.
		add_filter( 'woocommerce_composite_process_component_data', array( __CLASS__, 'process_component_addons_disable' ), 10, 4 );
	}

	/**
	 * Save option to disable Addons at component level.
	 *
	 * @since  3.6.6
	 *
	 * @param  array  $component_data
	 * @param  array  $posted_component_data
	 * @param  string $component_id
	 * @param  string $composite_id
	 * @return array
	 */
	public static function process_component_addons_disable( $component_data, $posted_component_data, $component_id, $composite_id ) {

		if ( isset( $posted_component_data[ 'disable_addons' ] ) ) {
			$component_data[ 'disable_addons' ] = 'yes';
		}

		return $component_data;
	}

	/**
	 * Show option to disable addons at Component level.
	 *
	 * @since  3.6.6
	 *
	 * @param  string $id
	 * @param  array  $data
	 * @param  string $product_id
	 * @return void
	 */
	public static function component_addons_disable( $id, $data, $product_id ) {

		$disable_addons = ( isset( $data[ 'disable_addons' ] ) && $data[ 'disable_addons' ] === 'yes' ) ? 'yes' : 'no';

		?>
		<div class="component_disable_addons" >
			<div class="form-field">
				<label for="group_disable_addons_<?php echo $id; ?>">
					<?php echo __( 'Disable Product Add-ons', 'woocommerce-composite-products' ); ?>
				</label>
				<input type="checkbox" class="checkbox"<?php echo ( $disable_addons === 'yes' ? ' checked="checked"' : '' ); ?> name="bto_data[<?php echo $id; ?>][disable_addons]" <?php echo ( $disable_addons === 'yes' ? 'value="1"' : '' ); ?>/>
				<?php echo WC_CP_Core_Compatibility::wc_help_tip( __( 'Check this option to diable any Product Add-ons associated with the selected Component Option.', 'woocommerce-composite-products' ) ); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Outputs add-ons for composited products.
	 *
	 * @param  WC_Product           $product
	 * @param  int                  $component_id
	 * @param  WC_Product_Composite $composite_product
	 * @return void
	 */
	public static function addons_display_support( $composited_product, $component_id, $composite_product ) {

		global $Product_Addon_Display, $product;

		if ( ! empty( $Product_Addon_Display ) ) {

			$component_data = $composite_product->get_component_data( $component_id );

			if ( ! empty( $component_data ) && isset( $component_data[ 'disable_addons' ] ) && $component_data[ 'disable_addons' ] === 'yes' ) {
				return;
			}

			$product_bak = isset( $product ) ? $product : false;
			$product     = $composited_product;

			self::$compat_composited_product = $composited_product;
			$Product_Addon_Display->display( $composited_product->id, $component_id . '-' );
			self::$compat_composited_product = '';

			if ( $product_bak ) {
				$product = $product_bak;
			}
		}
	}

	/**
	 * Sets a prefix for unique add-ons.
	 *
	 * @param  string 	$prefix
	 * @param  int 		$product_id
	 * @return string
	 */
	public static function addons_cart_prefix( $prefix, $product_id ) {

		if ( ! empty( self::$addons_prefix ) ) {
			return self::$addons_prefix . '-';
		}

		return $prefix;
	}

	/**
	 * Filter the product which add-ons prices are displayed for.
	 *
	 * @param  WC_Product  $product
	 * @return WC_Product
	 */
	function addons_price_for_display_product( $product ) {

		if ( ! empty( self::$compat_composited_product ) ) {
			return self::$compat_composited_product;
		}

		return $product;
	}

	/**
	 * Outputs nyp markup.
	 *
	 * @param  WC_Product           $product
	 * @param  int                  $component_id
	 * @param  WC_Product_Composite $composite_product
	 * @return void
	 */
	public static function nyp_display_support( $product, $component_id, $composite_product ) {

		if ( false === $composite_product->is_priced_per_product() ) {
			return;
		}

		if ( function_exists( 'WC_Name_Your_Price' ) && ( $product->product_type === 'simple' || $product->product_type === 'bundle' ) ) {
			WC_Name_Your_Price()->display->display_price_input( $product->id, '-' . $component_id );
		}

	}

	/**
	 * Sets a prefix for unique nyp.
	 *
	 * @param  string 	$prefix
	 * @param  int 		$product_id
	 * @return string
	 */
	public static function nyp_cart_prefix( $prefix, $product_id ) {

		if ( ! empty( self::$nyp_prefix ) ) {
			return '-' . self::$nyp_prefix;
		}

		return $prefix;
	}


	/**
	 * Validate composited item NYP and Addons.
	 *
	 * @param  bool                  $add
	 * @param  int                   $composite_id
	 * @param  int                   $component_id
	 * @param  int                   $product_id
	 * @param  int                   $quantity
	 * @param  array                 $cart_item_data
	 * @param  WC_Product_Composite  $composite
	 * @return bool
	 */
	public static function validate_component_nyp_and_addons( $add, $composite_id, $component_id, $product_id, $quantity, $cart_item_data, $composite ) {

		// Ordering again? When ordering again, do not revalidate addons & nyp.
		$order_again = isset( $_GET[ 'order_again' ] ) && isset( $_GET[ '_wpnonce' ] ) && wp_verify_nonce( $_GET[ '_wpnonce' ], 'woocommerce-order_again' );

		if ( $order_again ) {
			return $add;
		}

		// Validate add-ons.
		global $Product_Addon_Cart;

		if ( ! empty( $Product_Addon_Cart ) ) {

			$component_data = $composite->get_component_data( $component_id );

			if ( ! empty( $component_data ) && isset( $component_data[ 'disable_addons' ] ) && $component_data[ 'disable_addons' ] === 'yes' ) {
				return $add;
			}

			self::$addons_prefix = $component_id;

			if ( ! $Product_Addon_Cart->validate_add_cart_item( true, $product_id, $quantity ) ) {
				$add = false;
			}

			self::$addons_prefix = '';
		}

		// Validate nyp
		if ( get_post_meta( $composite_id, '_per_product_pricing_bto', true ) === 'yes' && function_exists( 'WC_Name_Your_Price' ) ) {

			self::$nyp_prefix = $component_id;

			if ( ! WC_Name_Your_Price()->cart->validate_add_cart_item( true, $product_id, $quantity ) ) {
				$add = false;
			}

			self::$nyp_prefix = '';
		}

		return $add;
	}

	/**
	 * Add addons identifier to composited item stamp, in order to generate new cart ids for composites with different addons configurations.
	 *
	 * @param  array  $composited_item_identifier
	 * @param  string $composited_item_id
	 * @return array
	 */
	public static function composited_item_addons_identifier( $composited_item_identifier, $composited_item_id ) {

		global $Product_Addon_Cart;

		// Store composited item addons add-ons config in indentifier to avoid generating the same composite cart id
		if ( ! empty( $Product_Addon_Cart ) ) {

			$addon_data = array();

			// Set addons prefix
			self::$addons_prefix = $composited_item_id;

			$composited_product_id = $composited_item_identifier[ 'product_id' ];

			$addon_data = $Product_Addon_Cart->add_cart_item_data( $addon_data, $composited_product_id );

			// Reset addons prefix
			self::$addons_prefix = '';

			if ( ! empty( $addon_data[ 'addons' ] ) ) {
				$composited_item_identifier[ 'addons' ] = $addon_data[ 'addons' ];
			}
		}

		return $composited_item_identifier;
	}

	/**
	 * Add nyp identifier to composited item stamp, in order to generate new cart ids for composites with different nyp configurations.
	 *
	 * @param  array  $composited_item_identifier
	 * @param  string $composited_item_id
	 * @return array
	 */
	public static function composited_item_nyp_stamp( $composited_item_identifier, $composited_item_id ) {

		if ( function_exists( 'WC_Name_Your_Price' ) ) {

			$nyp_data = array();

			// Set nyp prefix
			self::$nyp_prefix = $composited_item_id;

			$composited_product_id = $composited_item_identifier[ 'product_id' ];

			$nyp_data = WC_Name_Your_Price()->cart->add_cart_item_data( $nyp_data, $composited_product_id, '' );

			// Reset nyp prefix
			self::$nyp_prefix = '';

			if ( ! empty( $nyp_data[ 'nyp' ] ) ) {
				$composited_item_identifier[ 'nyp' ] = $nyp_data[ 'nyp' ];
			}
		}

		return $composited_item_identifier;
	}

	/**
	 * Runs before adding a composited item to the cart.
	 *
	 * @param  int        $product_id
	 * @param  int        $quantity
	 * @param  int        $variation_id
	 * @param  array      $variations
	 * @param  array      $composited_item_cart_data
	 * @return void
	 */
	public static function before_composited_add_to_cart( $product_id, $quantity, $variation_id, $variations, $composited_item_cart_data ) {

		global $Product_Addon_Cart;

		// Set addons and nyp prefixes
		self::$addons_prefix = self::$nyp_prefix = $composited_item_cart_data[ 'composite_item' ];

		// Add-ons cart item data is already stored in the composite_data array, so we can grab it from there instead of allowing Addons to re-add it
		// Not doing so results in issues with file upload validation

		if ( ! empty ( $Product_Addon_Cart ) ) {
			remove_filter( 'woocommerce_add_cart_item_data', array( $Product_Addon_Cart, 'add_cart_item_data' ), 10, 2 );
		}

		// Similarly with NYP
		if ( function_exists( 'WC_Name_Your_Price' ) ) {
			remove_filter( 'woocommerce_add_cart_item_data', array( WC_Name_Your_Price()->cart, 'add_cart_item_data' ), 5, 3 );
		}
	}

	/**
	 * Runs after adding a composited item to the cart.
	 *
	 * @param  int      $product_id
	 * @param  int      $quantity
	 * @param  int      $variation_id
	 * @param  array    $variations
	 * @param  array    $composited_item_cart_data
	 * @return void
	 */
	public static function after_composited_add_to_cart( $product_id, $quantity, $variation_id, $variations, $composited_item_cart_data ) {

		global $Product_Addon_Cart;

		// Reset addons and nyp prefix
		self::$addons_prefix = self::$nyp_prefix = '';

		if ( ! empty ( $Product_Addon_Cart ) ) {
			add_filter( 'woocommerce_add_cart_item_data', array( $Product_Addon_Cart, 'add_cart_item_data' ), 10, 2 );
		}

		// Similarly with NYP
		if ( function_exists( 'WC_Name_Your_Price' ) ) {
			add_filter( 'woocommerce_add_cart_item_data', array( WC_Name_Your_Price()->cart, 'add_cart_item_data' ), 5, 3 );
		}
	}

	/**
	 * Retrieve child cart item data from the parent cart item data array, if necessary.
	 *
	 * @param  array  $composited_item_cart_data
	 * @param  array  $cart_item_data
	 * @return array
	 */
	public static function get_composited_cart_item_data_from_parent( $composited_item_cart_data, $cart_item_data ) {

		// Add-ons cart item data is already stored in the composite_data array, so we can grab it from there instead of allowing Addons to re-add it
		if ( isset( $composited_item_cart_data[ 'composite_item' ] ) && isset( $cart_item_data[ 'composite_data' ][ $composited_item_cart_data[ 'composite_item' ] ][ 'addons' ] ) ) {
			$composited_item_cart_data[ 'addons' ] = $cart_item_data[ 'composite_data' ][ $composited_item_cart_data[ 'composite_item' ] ][ 'addons' ];
		}

		// Similarly with NYP
		if ( isset( $composited_item_cart_data[ 'composite_item' ] ) && isset( $cart_item_data[ 'composite_data' ][ $composited_item_cart_data[ 'composite_item' ] ][ 'nyp' ] ) ) {
			$composited_item_cart_data[ 'nyp' ] = $cart_item_data[ 'composite_data' ][ $composited_item_cart_data[ 'composite_item' ] ][ 'nyp' ];
		}

		return $composited_item_cart_data;
	}
}

WC_CP_Addons_Compatibility::init();
