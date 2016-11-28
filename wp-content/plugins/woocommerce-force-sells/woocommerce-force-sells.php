<?php
/*
Plugin Name: WooCommerce Force Sells
Plugin URI: http://woothemes.com/woocommerce
Description: Allows you to select products which will be used as force-sells - items which get added to the cart along with other items.
Version: 1.1.9
Author: WooThemes
Author URI: http://woothemes.com/woocommerce
*/

/**
 * Required functions
 */
if ( ! function_exists( 'woothemes_queue_update' ) ) {
	require_once( 'woo-includes/woo-functions.php' );
}

/**
 * Plugin updates
 */
woothemes_queue_update( plugin_basename( __FILE__ ), '3ebddfc491ca168a4ea4800b893302b0', '18678' );

add_action( 'plugins_loaded', array( 'WC_Force_Sells', 'get_instance' ) );

if ( ! class_exists( 'WC_Force_Sells' ) ) :

class WC_Force_Sells {

	/** @var Meta data for synced products */
	private $synced_types = array(
		'normal' => array(
			'field_name' => 'force_sell_ids',
			'meta_name'  => '_force_sell_ids',
		),
		'synced' => array(
			'field_name' => 'force_sell_synced_ids',
			'meta_name'  => '_force_sell_synced_ids',
		),
	);

	/** @var Class instance */
	protected static $instance = null;

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
		add_action( 'woocommerce_product_options_related', array( $this, 'write_panel_tab' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'process_extra_product_meta' ), 1, 2 );
		add_action( 'woocommerce_after_add_to_cart_button', array( $this, 'show_force_sell_products' ) );
		add_action( 'woocommerce_add_to_cart', array( $this, 'add_force_sell_items_to_cart' ), 1, 6 );
		add_action( 'woocommerce_after_cart_item_quantity_update', array( $this, 'update_force_sell_quantity_in_cart'), 1, 2 );
		add_action( 'woocommerce_before_cart_item_quantity_zero', array( $this, 'update_force_sell_quantity_in_cart'), 1, 2 );

		// Keep force sell data in the cart
		add_filter( 'woocommerce_get_cart_item_from_session', array($this, 'get_cart_item_from_session'), 10, 2 );
		add_filter( 'woocommerce_get_item_data', array( $this, 'get_linked_to_product_data' ), 10, 2 );
		add_action( 'woocommerce_cart_loaded_from_session', array( $this, 'remove_orphan_force_sells' ) );

		// Don't allow synced force sells to be removed or change quantity
		add_filter( 'woocommerce_cart_item_remove_link', array( $this, 'cart_item_remove_link' ), 10, 2 );
		add_filter( 'woocommerce_cart_item_quantity', array( $this, 'cart_item_quantity' ), 10, 2 );

		// Sync with remove/restore link in cart
		add_action( 'woocommerce_cart_item_removed', array( $this, 'cart_item_removed' ), 30 );
		add_action( 'woocommerce_cart_item_restored', array( $this, 'cart_item_restored' ), 30 );
	}

	/**
	 * Load translations
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'woocommerce-force-sells', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * If the single instance hasn't been set, set it now.
	 * @return WC_Force_Sells
	 */
	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	/**
	 * Looks to see if a product with the key of 'forced_by' actually exists and
	 * deletes it if not.
	 */
	public function remove_orphan_force_sells() {
		$cart_contents = WC()->cart->get_cart();
		foreach( $cart_contents as $key => $value ) {
			if ( isset( $value['forced_by'] ) ) {
				if ( ! array_key_exists( $value['forced_by'], $cart_contents ) ) {
					WC()->cart->remove_cart_item( $key );
				}
			}
		}
	}

	public function get_cart_item_from_session( $cart_item, $values ) {
		if ( isset( $values['forced_by'] ) ) {
			$cart_item['forced_by'] = $values['forced_by'];
		}
		return $cart_item;
	}

	public function get_linked_to_product_data( $data, $cart_item ) {
		if ( isset ( $cart_item['forced_by'] ) ) {
			$product_key = WC()->cart->find_product_in_cart( $cart_item['forced_by'] );

			if ( ! empty( $product_key ) ) {
				$product_name = WC()->cart->cart_contents[ $product_key ]['data']->post->post_title;
				$data[] = array(
					'name'    => __( 'Linked to', 'woocommerce-force-sells' ),
					'display' => $product_name,
				);
			}
		}

		return $data;
	}

	public function cart_item_remove_link( $link, $cart_item_key ) {
		if ( isset ( WC()->cart->cart_contents[ $cart_item_key ]['forced_by'] ) ) {
			return '';
		}

		return $link;
	}

	public function cart_item_quantity( $quantity, $cart_item_key ) {
		if ( isset ( WC()->cart->cart_contents[ $cart_item_key ]['forced_by'] ) ) {
			return WC()->cart->cart_contents[ $cart_item_key ]['quantity'];
		}

		return $quantity;
	}

	public function write_panel_tab() {
		global $post;
		?>
		<p class="form-field">
			<label for="force_sell_ids"><?php _e('Force Sells', 'woocommerce-force-sells'); ?></label>
			<?php if ( version_compare( WOOCOMMERCE_VERSION, '2.3.0', '>=' ) ) : ?>
				<input type="hidden" class="wc-product-search" style="width: 50%;" id="force_sell_ids" name="force_sell_ids" data-placeholder="<?php _e( 'Search for a product&hellip;', 'woocommerce-force-sells' ); ?>" data-action="woocommerce_json_search_products_and_variations" data-multiple="true" data-selected="<?php
					$product_ids = $this->get_force_sell_ids( $post->ID, array( 'normal' ) );
					$json_ids    = array();

					foreach ( $product_ids as $product_id ) {
						$product = wc_get_product( $product_id );
						$json_ids[ $product_id ] = wp_kses_post( $product->get_formatted_name() );
					}

					echo esc_attr( json_encode( $json_ids ) );
				?>" value="<?php echo implode( ',', array_keys( $json_ids ) ); ?>" />
			<?php else : ?>
				<select id="force_sell_ids" name="force_sell_ids[]" class="ajax_chosen_select_products" multiple="multiple" data-placeholder="<?php _e( 'Search for a product&hellip;', 'woocommerce-force-sells' ); ?>">
					<?php
					if ( $product_ids = $this->get_force_sell_ids( $post->ID, array( 'normal' ) ) ) {
						foreach ( $product_ids as $product_id ) {
							$title 	= get_the_title( $product_id );
							$sku 	= get_post_meta( $product_id, '_sku', true );

							if ( ! $title ) {
								continue;
							}

							if ( isset( $sku ) && $sku ) {
								$sku = ' (SKU: ' . $sku . ')';
							}

							echo '<option value="' . $product_id . '" selected="selected">#'. $product_id . ' &ndash; ' . $title . $sku .'</option>';
						}
					}
					?>
				</select>
			<?php endif; ?>
			<img class="help_tip" width="16" data-tip='<?php _e( 'These products will be added to the cart when the main product is added. Quantity will not be synced in case the main product quantity changes.', 'woocommerce-force-sells') ?>' src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" />
		</p>
		<p class="form-field">
			<label for="force_sell_synced_ids"><?php _e( 'Synced Force Sells', 'woocommerce-force-sells'); ?></label>
			<?php if ( version_compare( WOOCOMMERCE_VERSION, '2.3.0', '>=' ) ) : ?>
				<input type="hidden" class="wc-product-search" style="width: 50%;" id="force_sell_synced_ids" name="force_sell_synced_ids" data-placeholder="<?php _e( 'Search for a product&hellip;', 'woocommerce-force-sells' ); ?>" data-action="woocommerce_json_search_products_and_variations" data-multiple="true" data-selected="<?php
					$product_ids = $this->get_force_sell_ids( $post->ID, array( 'synced' ) );
					$json_ids    = array();

					foreach ( $product_ids as $product_id ) {
						$product = wc_get_product( $product_id );
						$json_ids[ $product_id ] = wp_kses_post( $product->get_formatted_name() );
					}

					echo esc_attr( json_encode( $json_ids ) );
				?>" value="<?php echo implode( ',', array_keys( $json_ids ) ); ?>" />
			<?php else : ?>
				<select id="force_sell_synced_ids" name="force_sell_synced_ids[]" class="ajax_chosen_select_products" multiple="multiple" data-placeholder="<?php _e( 'Search for a product&hellip;', 'woocommerce-force-sells' ); ?>">
					<?php
					if ( $product_ids = $this->get_force_sell_ids( $post->ID, array( 'synced' ) ) ) {
						foreach ( $product_ids as $product_id ) {
							$title 	= get_the_title( $product_id );
							$sku 	= get_post_meta( $product_id, '_sku', true );

							if ( ! $title ) {
								continue;
							}

							if ( isset( $sku ) && $sku ) {
								$sku = ' (SKU: ' . $sku . ')';
							}

							echo '<option value="' . $product_id . '" selected="selected">#'. $product_id . ' &ndash; ' . $title . $sku .'</option>';
						}
					}
					?>
				</select>
			<?php endif; ?>
			<img class="help_tip" width="16" data-tip='<?php _e( 'These products will be added to the cart when the main product is added and quantity will be synced in case the main product quantity changes.', 'woocommerce-force-sells') ?>' src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" />
		</p>
		<?php
	}

	public function process_extra_product_meta( $post_id, $post ) {
		foreach ( $this->synced_types as $key => $value ) {
			if ( isset( $_POST[ $value['field_name'] ] ) ) {
				$force_sells = array();
				$ids         = $_POST[ $value['field_name'] ];

				if ( version_compare( WOOCOMMERCE_VERSION, '2.3.0', '>=' ) ) {
					$ids = explode( ',', $ids );
					$ids = array_filter( $ids );
				}

				foreach ( $ids as $id ) {
					if ( $id && $id > 0 ) {
						$force_sells[] = $id;
					}
				}

				update_post_meta( $post_id, $value['meta_name'], $force_sells );
			} else {
				delete_post_meta( $post_id, $value['meta_name'] );
			}
		}
	}

	public function show_force_sell_products() {
		global $post;

		if ( $product_ids = $this->get_force_sell_ids( $post->ID, array( 'normal', 'synced' ) ) ) {
			echo '<div class="clear"></div>';
			echo '<div class="wc-force-sells">';
			echo '<p>' . __( 'This will also add the following products to your cart:', 'woocommerce-force-sells' ) . '</p>';
			echo '<ul>';

			foreach ( $product_ids as $product_id ) {
				$title 	= get_the_title( $product_id );
				echo '<li>' . $title . '</li>';
			}

			echo '</ul></div>';
		}
	}

	public function add_force_sell_items_to_cart( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {
		// Check if this product is forced in itself, so it can't force in others (to prevent adding in loops)
		if ( isset( WC()->cart->cart_contents[ $cart_item_key ]['forced_by'] ) ) {
			$forced_by_key = WC()->cart->cart_contents[ $cart_item_key ]['forced_by'];

			if ( isset( WC()->cart->cart_contents[ $forced_by_key ] ) ) {
				return;
			}
		}

		if ( $product_ids = $this->get_force_sell_ids( $product_id, array( 'normal', 'synced' ) ) ) {
			foreach ( $product_ids as $id ) {
				$cart_id = WC()->cart->generate_cart_id( $id, '', '', array( 'forced_by' => $cart_item_key ) );
				$key = WC()->cart->find_product_in_cart( $cart_id );

				if ( ! empty( $key ) ) {
					WC()->cart->set_quantity( $key, WC()->cart->cart_contents[ $key ]['quantity'] );
				} else {
					$args = array();

					if ( $synced_ids = $this->get_force_sell_ids( $product_id, array( 'synced' ) ) ) {
						if ( in_array( $id, $synced_ids ) ) {
							$args['forced_by'] = $cart_item_key;
						}
					}

					$params = apply_filters( 'wc_force_sell_add_to_cart_product', array( 'id' => $id, 'quantity' => $quantity, 'variation_id' => '', 'variation' => '' ), WC()->cart->cart_contents[ $cart_item_key ] );
					WC()->cart->add_to_cart( $params['id'], $params['quantity'], $params['variation_id'], $params['variation'], $args );

				}
			}
		}
	}

	public function update_force_sell_quantity_in_cart( $cart_item_key, $quantity = 0 ) {
		if ( ! empty( WC()->cart->cart_contents[ $cart_item_key ] ) ) {
			if ( $quantity == 0 || $quantity < 0 ) {
				$quantity = 0;
			} else {
				$quantity = WC()->cart->cart_contents[ $cart_item_key ]['quantity'];
			}

			foreach ( WC()->cart->cart_contents as $key => $value ) {
				if ( isset( $value['forced_by'] ) && $cart_item_key == $value['forced_by'] ) {
					$quantity = apply_filters( 'wc_force_sell_update_quantity', $quantity, WC()->cart->cart_contents[ $key ] );
					WC()->cart->set_quantity( $key, $quantity );
				}
			}
		}
	}

	private function get_force_sell_ids( $product_id, $types ) {
		if ( ! is_array( $types ) || empty( $types ) ) {
			return false;
		}

		$ids = array();

		foreach ( $types as $type ) {
			$new_ids = array();

			if ( isset( $this->synced_types[ $type ] ) ) {
				$new_ids = get_post_meta( $product_id, $this->synced_types[ $type ]['meta_name'], true );

				if ( is_array( $new_ids ) && ! empty( $new_ids ) ) {
					$ids = array_merge( $ids, $new_ids );
				}
			}
		}

		if ( is_array( $ids ) && ! empty( $ids ) ) {
			return $ids;
		}

		return false;
	}

	/**
	 * When an item gets removed from the cart, do the same for forced sells
	 * @param  string $cart_item_key
	 */
	public function cart_item_removed( $cart_item_key ) {
		foreach ( WC()->cart->get_cart() as $key => $value ) {
			if ( isset( $value['forced_by'] ) && $cart_item_key === $value['forced_by'] ) {
				WC()->cart->remove_cart_item( $key );
			}
		}
	}

	/**
	 * When an item gets removed from the cart, do the same for forced sells
	 * @param  string $cart_item_key
	 */
	public function cart_item_restored( $cart_item_key ) {
		foreach ( WC()->cart->removed_cart_contents as $key => $value ) {
			if ( isset( $value['forced_by'] ) && $cart_item_key === $value['forced_by'] ) {
				WC()->cart->restore_cart_item( $key );
			}
		}
	}
}

endif;
