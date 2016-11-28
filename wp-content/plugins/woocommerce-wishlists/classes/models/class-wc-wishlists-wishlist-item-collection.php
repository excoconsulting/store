<?php

class WC_Wishlists_Wishlist_Item_Collection {
	private $id;

	public function __construct($id) {
		$this->id = $id;
	}

	public static function get_items($id, $update_cached_price = false) {
		$items = get_post_meta($id, '_wishlist_items', true);
		$wishlist_items = !empty($items) ? maybe_unserialize($items) : array();

		$contents = array();

		if ($update_cached_price) {
			self::update_cached_prices($id);
		}

		foreach ($wishlist_items as $key => $values) {
			if (function_exists('get_product')) {
				$_product = get_product(isset($values['variation_id']) && !empty($values['variation_id']) ? $values['variation_id'] : $values['product_id']);
			} else {
				if ($values['variation_id'] > 0)
					$_product = new WC_Product_Variation($values['variation_id']);
				else
					$_product = new WC_Product($values['product_id']);
			}

			if ($_product && $_product->exists() && $values['quantity'] > 0) {
				// Put session data into array. Run through filter so other plugins can load their own session data
				$contents[$key] = apply_filters('woocommerce_get_cart_item_from_session', array(
				    'product_id' => $values['product_id'],
				    'variation_id' => $values['variation_id'],
				    'variation' => $values['variation'],
				    'quantity' => $values['quantity'],
				    'data' => $_product,
				    'wl_price' => isset($values['wl_price']) ? $values['wl_price'] : false,
				    'date' => isset($values['date']) ? $values['date'] : strtotime('now')
					), $values, $key);
			}
		}

		return $contents;
	}

	public static function get_first_image($id, $size = 'full') {
		$wishlist_items = self::get_items($id);

		$result = false;
		foreach ($wishlist_items as $item) {
			$_product = $item['data'];
			if ($_product->exists()) {
				if (has_post_thumbnail($_product->id)) {
					$image = wp_get_attachment_image_src(get_post_thumbnail_id($_product->id), $size);
				} elseif (( $parent_id = wp_get_post_parent_id($_product->id) ) && has_post_thumbnail($parent_id)) {
					$image = wp_get_attachment_image_src(get_post_thumbnail_id($parent_id), $size);
				} else {
					$image = false;
				}

				if ($image) {
					$result = $image[0];
					break;
				}
			}
		}

		return $result;
	}

	public static function get_items_from_session() {
		$items = WC_Wishlist_Compatibility::WC()->session->get('_wishlist_items');
		$wishlist_items = !empty($items) ? maybe_unserialize($items) : array();
		
		$contents = array();
		foreach ($wishlist_items as $key => $values) {
			if (!isset($values['variation_id']) && !isset($values['product_id'])) {
				continue;
			}

			$_product = null;

			if (!function_exists('get_product')) {
				if ($values['variation_id'] > 0) {
					$_product = new WC_Product_Variation($values['variation_id']);
				} else {
					$_product = new WC_Product($values['product_id']);
				}
			} else {
				$_product = get_product($values['variation_id'] ? $values['variation_id'] : $values['product_id']);
			}

			if ($_product->exists() && $values['quantity'] > 0) {

				// Put session data into array. Run through filter so other plugins can load their own session data
				$contents[$key] = apply_filters('woocommerce_get_cart_item_from_session', array(
				    'product_id' => $values['product_id'],
				    'variation_id' => $values['variation_id'],
				    'variation' => $values['variation'],
				    'quantity' => $values['quantity'],
				    'data' => $_product,
				    'date' => isset($values['date']) ? $values['date'] : strtotime('now')
					), $values, $key);
			}
		}

		return $contents;
	}

	public static function get_items_categories($id) {
		$items = self::get_items($id);
		$cats = array();
		$ids = array();
		foreach ($items as $item) {
			$item_cats = wp_get_object_terms($item['product_id'], 'product_cat');
			if ($item_cats && !is_wp_error($item_cats)) {
				foreach ($item_cats as $item_cat) {
					if (!array_key_exists($item_cat->term_id, $cats)) {
						$cats[$item_cat->term_id] = $item_cat;
					}
				}
			}
		}

		return $cats;
	}

	public static function move_item($source, $destination, $wishlist_item_key) {
		$source_items = self::get_items($source);
		$destination_items = self::get_items($destination);

		$the_item = isset($source_items[$wishlist_item_key]) ? $source_items[$wishlist_item_key] : false;
		if (!$the_item) {
			return false;
		}

		if (isset($destination_items[$wishlist_item_key])) {
			$destination_items[$wishlist_item_key]['quantity'] = (int) $destination_items[$wishlist_item_key]['quantity'] + (int) $source_items[$wishlist_item_key]['quantity'];
		} else {
			$destination_items[$wishlist_item_key] = $source_items[$wishlist_item_key];
		}

		update_post_meta($destination, '_wishlist_items', $destination_items);
		do_action('wc_wishlists_wishlist_items_updated,', $source);

		self::remove_item($source, $wishlist_item_key);

		return true;
	}

	public static function move_item_to_session($source, $wishlist_item_key) {
		$source_items = self::get_items($source);
		$destination_items = WC_Wishlist_Compatibility::WC()->session->get('_wishlist_items', array());
			
		$the_item = isset($source_items[$wishlist_item_key]) ? $source_items[$wishlist_item_key] : false;
		if (!$the_item) {
			return false;
		}

		if (isset($destination_items[$wishlist_item_key])) {
			// $destination_items[$wishlist_item_key]['quantity'] = (int) $destination_items[$wishlist_item_key]['quantity'] + (int) $source_items[$wishlist_item_key]['quantity'];
		} else {
			$destination_items[$wishlist_item_key] = $source_items[$wishlist_item_key];
		}
		
		WC_Wishlist_Compatibility::WC()->session->set('_wishlist_items', $destination_items);
		return true;
	}

	public static function move_item_to_list_from_session($destination, $wishlist_item_key) {
		$source_items = WC_Wishlist_Compatibility::WC()->session->get('_wishlist_items', array());
		$destination_items = self::get_items($destination);

		$the_item = isset($source_items[$wishlist_item_key]) ? $source_items[$wishlist_item_key] : false;
		if (!$the_item) {
			return 0;
		}

		if (isset($destination_items[$wishlist_item_key])) {
			$destination_items[$wishlist_item_key]['quantity'] = (int) $destination_items[$wishlist_item_key]['quantity'] + (int) $source_items[$wishlist_item_key]['quantity'];
		} else {
			$destination_items[$wishlist_item_key] = $source_items[$wishlist_item_key];
		}

		update_post_meta($destination, '_wishlist_items', $destination_items);
		do_action('wc_wishlists_wishlist_items_updated,', $destination);
		self::remove_item_from_session($wishlist_item_key);

		return 1;
	}

	public static function remove_item($wishlist_id, $wishlist_item_key) {
		$wishlist_items = self::get_items($wishlist_id);
		unset($wishlist_items[$wishlist_item_key]);
		update_post_meta($wishlist_id, '_wishlist_items', $wishlist_items);
		do_action('wc_wishlists_wishlist_items_updated,', $wishlist_id);
		return true;
	}

	public static function remove_item_from_session($wishlist_item_key) {
		$items = WC_Wishlist_Compatibility::WC()->session->get('_wishlist_items', array());
		
		if (isset($items[$wishlist_item_key])) {
			unset($items[$wishlist_item_key]);
		}

		$valid = 0;
		foreach ($items as $item_key => $item) {
			if ($item_key) {
				$valid++;
			}
		}

		if ($valid == 0) {
			WC_Wishlist_Compatibility::WC()->session->set('_wishlist_items', null);
		} else {
			WC_Wishlist_Compatibility::WC()->session->set('_wishlist_items', $items);
		}

		return true;
	}

	public static function add_item($wishlist_id, $product_id, $quantity = 1, $variation_id = '', $variation = '', $cart_item_data = array()) {
		global $woocommerce;

		if ($wishlist_id != 'session') {
			$wishlist = new WC_Wishlists_Wishlist($wishlist_id);
			if (!$wishlist->post) {
				WC_Wishlist_Compatibility::wc_add_notice(__('List could not be located', 'wc_wishlist'), 'error');
				return false;
			}
			$wishlist_items = self::get_items($wishlist_id);
		} elseif ($wishlist_id == 'session') {
			$wishlist_items = self::get_items_from_session();
		}

		// Load cart item data - may be added by other plugins
		
		$cart_item_data = (array) apply_filters( 'woocommerce_add_cart_item_data', $cart_item_data, $product_id, $variation_id );
		$cart_item_data = array_merge($cart_item_data, apply_filters('woocommerce_add_wishlist_item_data', $cart_item_data, $product_id, $wishlist_id));

		// Generate a ID based on product ID, variation ID, variation data, and other cart item data
		$cart_id = $woocommerce->cart->generate_cart_id($product_id, $variation_id, $variation, $cart_item_data);

		if ($quantity < 1) {
			if ($wishlist_items && is_array($wishlist_items) && isset($wishlist_items[$cart_id])) {
				unset($wishlist_items[$cart_id]);
				WC_Wishlist_Compatibility::wc_add_notice(__('Item has been removed from the list.', 'wc_wishlist'));
				return true;
			}
		}


		// See if this product and its options is already in the wishlist
		$cart_item_key = false;
		if ($wishlist_items && is_array($wishlist_items)) {
			foreach ($wishlist_items as $wishlist_item_key => $item) {
				if ($wishlist_item_key == $cart_id) {
					$cart_item_key = $wishlist_item_key;
					break;
				}
			}
		}

		if (function_exists('get_product')) {
			$product_data = get_product($variation_id > 0 ? $variation_id : $product_id);
		} else {
			if ($variation_id > 0) {
				$product_data = new WC_Product_Variation($variation_id);
			} else {
				$product_data = new WC_Product($product_id);
			}
		}



		// If cart_item_key is set, the item is already in the cart
		if ($cart_item_key) {
			$new_quantity = $quantity + $wishlist_items[$cart_item_key]['quantity'];
			$wishlist_items[$cart_item_key]['quantity'] = $new_quantity;
			$wishlist_items[$cart_item_key]['wl_price'] = $product_data->get_price_excluding_tax();
		} else {
			$cart_item_key = $cart_id;

			// Add item after merging with $cart_item_data - hook to allow plugins to modify cart item
			$wishlist_item = apply_filters('woocommerce_add_cart_item', array_merge($cart_item_data, array(
			    'product_id' => $product_id,
			    'variation_id' => $variation_id,
			    'variation' => $variation,
			    'quantity' => $quantity,
			    'data' => $product_data,
			    'wl_date' => strtotime('now'),
			)), $cart_item_key);

			$wishlist_item = apply_filters('woocommerce_add_wishlist_item', $wishlist_item, $cart_item_key);

			$wishlist_item['wl_price'] = $wishlist_item['data']->get_price_excluding_tax();
			
			//Unset serialized product data from wishlist item. 
			unset($wishlist_item['data']);
			
			$wishlist_items[$cart_item_key] = $wishlist_item;
		}

		do_action('woocommerce_wishlist_add_item', $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data, $wishlist_id);

		if ($wishlist_id == 'session') {
			WC_Wishlist_Compatibility::WC()->session->set('_wishlist_items', $wishlist_items);
		} else {
			update_post_meta($wishlist_id, '_wishlist_items', $wishlist_items);
			do_action('wc_wishlists_wishlist_items_updated', $wishlist_id);
		}

		return true;
	}

	public static function update_item_quantity($wishlist_id, $wishlist_item_key, $quantity) {
		$wishlist_items = self::get_items($wishlist_id);
		if (isset($wishlist_items[$wishlist_item_key])) {
			$wishlist_items[$wishlist_item_key]['quantity'] = $quantity;
		}

		update_post_meta($wishlist_id, '_wishlist_items', $wishlist_items);
		do_action('wc_wishlists_wishlist_items_updated', $wishlist_id);
		return true;
	}

	public static function update_cached_prices($wishlist_id) {
		$wishlist_items = self::get_items($wishlist_id);
		if ($wishlist_items) {
			foreach ($wishlist_items as &$values) {
				$_product = null;

				if (!function_exists('get_product')) {
					if ($values['variation_id'] > 0) {
						$_product = new WC_Product_Variation($values['variation_id']);
					} else {
						$_product = new WC_Product($values['product_id']);
					}
				} else {
					$_product = get_product($values['variation_id'] ? $values['variation_id'] : $values['product_id']);
				}

				if ($_product->exists()) {
					$values['wl_price'] = $_product->get_price_excluding_tax();
				}
			}

			update_post_meta($wishlist_id, '_wishlist_items', $wishlist_items);
			do_action('wc_wishlists_wishlist_items_updated', $wishlist_id);
			return true;
		}
	}

}

?>