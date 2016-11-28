<?php
/**
 * Plugin Name: WooCommerce Nested Category Layout
 * Plugin URI: http://www.woothemes.com/products/woocommerce-nested-category-layout/
 * Description: WooCommerce Nested Category Catalog Page Layout
 * Author: WooThemes / SkyVerge
 * Author URI: http://www.woothemes.com
 * Version: 1.9.0
 * Text Domain: woocommerce-nested-category-layout
 * Domain Path: /i18n/languages/
 *
 * Copyright: (c) 2012-2016 SkyVerge, Inc. (info@skyverge.com)
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package   WC-Nested-Category-Layout
 * @author    SkyVerge
 * @category  Plugin
 * @copyright Copyright (c) 2012-2016, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

// Required functions
if ( ! function_exists( 'woothemes_queue_update' ) ) {
	require_once( plugin_dir_path( __FILE__ ) . 'woo-includes/woo-functions.php' );
}

// Plugin updates
woothemes_queue_update( plugin_basename( __FILE__ ), '60d07379d28e80cf143790b8aea869a7', '142840' );

// WC active check
if ( ! is_woocommerce_active() ) {
	return;
}

// Required library class
if ( ! class_exists( 'SV_WC_Framework_Bootstrap' ) ) {
	require_once( plugin_dir_path( __FILE__ ) . 'lib/skyverge/woocommerce/class-sv-wc-framework-bootstrap.php' );
}

SV_WC_Framework_Bootstrap::instance()->register_plugin( '4.4.0', __( 'WooCommerce Nested Category Layout', 'woocommerce-nested-category-layout' ), __FILE__, 'init_woocommerce_nested_category_layout', array(
	'minimum_wc_version'   => '2.4.13',
	'minimum_wp_version'   => '4.1',
	'backwards_compatible' => '4.4.0',
) );

function init_woocommerce_nested_category_layout() {

/**
 * Reference:
 *  woocommerce_front_page_archive()
 *  get_products_in_view()
 */
class WC_Nested_Category_Layout extends SV_WC_Plugin {


	/** plugin version number */
	const VERSION = '1.9.0';

	/** @var WC_Nested_Category_Layout single instance of this plugin */
	protected static $instance;

	/** plugin id */
	const PLUGIN_ID = 'nested_category_layout';

	/** plugin text domain, DEPRECATED as of 1.8.0 */
	const TEXT_DOMAIN = 'woocommerce-nested-category-layout';


	/**
	 * Initialize the plugin
	 *
	 * @see SV_WC_Plugin::__construct()
	 */
	public function __construct() {

		parent::__construct( self::PLUGIN_ID, self::VERSION );

		// Include required files
		$this->includes();

		// Override the WooCommerce template functions.
		add_action( 'after_setup_theme', array( $this, 'include_template_functions' ) );

		// initialize the plugin
		add_action( 'wp', array( $this, 'wp_init' ) );

		if ( is_admin() ) {
			add_action( 'sv_wc_framework_plugins_loaded', array( $this, 'init_admin_settings' ) );
		} else {
			// no pagination: return all products when displaying nested categories/products
			// keep this out of the admin
			add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );
		}
	}


	/**
	 * Init WooCommerce Nested Category Layout when WordPress initializes
	 *
	 * @see SV_WC_Plugin::load_translation()
	 */
	public function load_translation() {
		// localisation
		load_plugin_textdomain( 'woocommerce-nested-category-layout', false, dirname( plugin_basename( $this->get_file() ) ) . '/i18n/languages' );
	}


	/**
	 * Initialize admin settings, depending on version of WooCommerce
	 *
	 * @since 1.3
	 */
	public function init_admin_settings() {
		// inject our admin options
		add_filter( 'woocommerce_product_settings', array( $this, 'nested_category_layout_settings' ) );
	}


	/**
	 * At this point we can determine the page we're on because the
	 * Conditional Query Tag functions are available.
	 *
	 * @since 1.0
	 */
	public function wp_init() {

		// if we're on the shop page or product category page and the
		//  nested layout option is enabled
		if ( ( is_shop() && 'yes' === get_option( 'woocommerce_nested_subcat_shop', 'no' ) ) ||
		     ( is_product_category() && $this->is_nested_subcategories_enabled_for_category( $this->get_current_product_category_id() ) ) ) {

			add_action( 'woocommerce_archive_description', array( $this, 'list_categories_and_products' ), 15 );
			add_action( 'woocommerce_pagination',          array( $this, 'fix_query_object' ), 1 );
		}
	}


	/**
	 * Include required core files
	 *
	 * @since 1.0
	 */
	private function includes() {

		// Walker to pre-determine the categories depth.
		require_once( $this->get_plugin_path() . '/classes/class-walker-category-depth.php' );

		if ( ! is_admin() || defined( 'DOING_AJAX' ) ) {
			$this->frontend_includes();
		}
	}


	/**
	 * Include required frontend files
	 *
	 * @since 1.0
	 **/
	private function frontend_includes() {

		// Walker to render the categories/products.
		require_once( $this->get_plugin_path() . '/classes/class-walker-category-products.php' );
	}


	/**
	 * Override some template functions from woocommerce/woocommerce-template.php
	 * with our own template functions file.  Largely to allow us to load
	 * template parts first from this plugin's template directory.
	 *
	 * @since 1.0
	 */
	public function include_template_functions() {

		// Load any deprecated functions for previous WooCommerce versions first.
		require_once( $this->get_plugin_path() . '/woocommerce-template-deprecated.php' );

		// Then load the current ones.
		require_once( $this->get_plugin_path() . '/woocommerce-template.php' );
	}


	/**
	 * Filter to set the products per page to unlimited, since pagination
	 * with this sort of layout would be quite challenging:
	 * How would we limit ourselves to just the products that need to be
	 * displayed in a hierarchy of categories where the products are
	 * shown only at the deepest levels?
	 *
	 * TODO: should I be using loop_shop_per_page rather than pre_get_posts
	 *
	 * @param WP_Query $q wordpress query object
	 *
	 * @since 1.0
	 */
	public function pre_get_posts( $q ) {

		// Only apply to product categories, the product post archive, the shop page, product tags, and product attribute taxonomies
		if (
				( ! $q->is_main_query() ) // Abort if this isn't the main query
				||
				( ! $q->is_post_type_archive( 'product' ) && ! $q->is_tax( array_merge( array( 'product_cat', 'product_tag' ), wc_get_attribute_taxonomy_names() ) ) ) // Abort if we're not on a post type archive/product taxonomy
			)
			return;

		// don't mess with the pagination on the search page
		if ( $q->is_search() ) {
			return;
		}

		// product category page:  is the nested layout enabled for this category?
		if ( $q->is_tax( 'product_cat' ) && 'no' === get_option( 'woocommerce_nested_subcat_' . $this->get_current_product_category_id(), 'no' ) ) {
			return;
		}

		// main shop page:  is the nested layout enabled?
		if ( ( $q->is_post_type_archive( 'product' ) || $q->is_page( woocommerce_get_page_id( 'shop' ) ) ) && 'no' === get_option( 'woocommerce_nested_subcat_shop', 'no' ) ) {
			return;
		}

		// if this is a leaf category, bail and allow the normal pagination to take over
		if ( 0 === count( get_categories( array( 'taxonomy' => 'product_cat', 'child_of' => $this->get_current_product_category_id() ) ) ) ) {
			return;
		}


		// unlimited number of products
		$q->set( 'posts_per_page', -1 );

		// Unless cache_results is disabled, then we run into a memory allocation
		//  error when we query for more than around 125 products on a page, with a memory
		//  limit of 64MB.  Upping the memory limit to 128MB safely allows us at
		//  least 200 products, but is this reasonable?  On the other hand, I don't
		//  know what the consequences are of disabling cache_results, though I'd
		//  imagine you take some sort of performance hit...
		// $q->set( 'cache_results', false );

		// And remove the pre_get_posts hook
		remove_filter( 'pre_get_posts', array( $this, 'pre_get_posts' ) );

		do_action( 'wc_nested_category_layout_pre_get_posts', $q );
	}


	/**
	 * Render our nested categories, and the products they contain.  This
	 * is the heart of the plugin.
	 *
	 * @since 1.0
	 */
	public function list_categories_and_products() {
		global $wp_the_query, $wp_query, $woocommerce_loop;

		// if home page/front page, bail
		if (  $wp_the_query->is_home() || ( $wp_the_query->is_front_page() && wc_get_page_id( 'shop' ) !== (int) get_option( 'page_on_front' ) ) ) {
			return;
		}

		// Previously I had a check here to bail if $wp_query->is_main_query() was false,
		//  however this was incorrectly happening on a clients site, even though
		//  when I printed out and compared $wp_query and $wp_the_query they appeared
		//  to be the same, as expected.  I'm not sure that this check is even 100%
		//  necessary since this is only invoked from the action on the archive-product.php
		//  template, so I figured it was safe to remove for now.
		//  WooCommerce Nested Category support issue #13

		// search page, or not a product category or shop page, bail
		if ( is_search() ||
		   ( ! is_product_category() && ! is_shop() ) ) {
			return;
		}

		// get the category depths so we can display products only in their most specific categories
		$category_depths = $this->get_product_category_depths();

		$args = apply_filters( 'wc_nested_category_layout_list_categories_args', array(
			'taxonomy'   => 'product_cat',
			'child_of'   => $this->get_current_product_category_id(),
			'walker'     => new Walker_Category_Products( $category_depths ),
			'echo'       => false,
			'show_count' => 0,    // Note:  originally I had this true, which worked fine, except for leaf categories, where it would generate a WordPress SQL Error notice due to the custom taxonomy 'product_cat' with 'term_taxonomy_id IN ()'
		) );

		// remove template actions which are unnecessary with nested categories
		remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
		remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );

		// fire the before/after shop loop actions so that we integrate better with other plugins (ie Advanced Ajax Layered Nav Widget)
		do_action( 'woocommerce_before_shop_loop' );
		wp_list_categories( $args );
		do_action( 'woocommerce_after_shop_loop' );

		// if we rendered products and categories
		if ( isset( $woocommerce_loop['has_products'] ) && $woocommerce_loop['has_products'] ||
			isset( $woocommerce_loop['has_categories'] ) && $woocommerce_loop['has_categories'] ) {
			// force the have_posts() call on the archive-product.php template to fail
			$wp_query->current_post = $wp_query->post_count + 2;
		}
	}


	/**
	 * Fix the global query object as need be if we altered it from the
	 * archive-product.php template
	 */
	public function fix_query_object() {
		global $woocommerce_loop, $wp_query;
		if ( isset( $woocommerce_loop['has_products'] ) && $woocommerce_loop['has_products'] ||
			isset( $woocommerce_loop['has_categories'] ) && $woocommerce_loop['has_categories'] ) {
			$wp_query->current_post = $wp_query->post_count + 1;
			$wp_query->rewind_posts();
		}
	}


	/** Admin methods ******************************************************/


	/**
	 * Inject our admin settings (nested list of product categories) so
	 * the shop admin can determine which catalog pages display our
	 * nested categories/products.
	 *
	 * @param array $settings associative-array of WooCommerce settings
	 * @return array associative-array of WooCommerce settings
	 *
	 * @since 1.0
	 */
	public function nested_category_layout_settings( $settings ) {

		$updated_settings = array();

		if ( is_array( $settings ) ) {
			foreach ( $settings as $section ) {

				$updated_settings[] = $section;

				// after the Catalog Options section
				if ( 'catalog_options' === $section['id'] && 'sectionend' === $section['type'] ) {

					// we're only interested in categories that have sub-categories
					$categories = array();
					$tmp_categories = $this->get_product_category_depths();
					foreach ( $tmp_categories as $term_id => $depth ) {
						$child_categories = get_categories( array( 'taxonomy' => 'product_cat', 'child_of' => $term_id ) );
						if ( count( $child_categories ) > 0 ) $categories[ $term_id ] = $depth;
					}

					$updated_settings[] = array(
						'name' => __( 'Catalog Pages by Category', 'woocommerce-nested-category-layout' ),
						'type' => 'title',
						'desc' => __( 'The following options determine which catalog pages will display nested subcategories and products.', 'woocommerce-nested-category-layout' ),
						'id'   => 'nested_category_layout_options',
					);

					$updated_settings[] = array(
						'name'     => __( 'Products per Subcategory', 'woocommerce-nested-category-layout' ),
						'desc'     => __( 'The number of products to display per subcategory', 'woocommerce-nested-category-layout' ),
						'id'       => 'woocommerce_subcat_posts_per_page',
						'std'      => apply_filters( 'loop_shop_per_page', get_option( 'posts_per_page' ) ),
						'type'     => 'text',
						'desc_tip' => true,
					);

					// special case: shop page
					$shop_page_section = array(
						'name' => __( 'Show products/subcategories', 'woocommerce-nested-category-layout' ),
						'desc' => __( 'Shop Page', 'woocommerce-nested-category-layout' ),
						'id'   => 'woocommerce_nested_subcat_shop',
						'std'  => 'no',
						'type' => 'checkbox',
					);
					if ( count( $categories ) > 0 ) $shop_page_section['checkboxgroup'] = 'start';
					$updated_settings[] = $shop_page_section;

					// go through the product categories, if any
					foreach ( $categories as $term_id => $depth ) {

						$category = get_term( $term_id, 'product_cat' );

						$updated_settings[] = array(
							'desc'          => str_repeat( '-', $depth ) . ' ' . $category->name,
							'id'            => 'woocommerce_nested_subcat_' . $term_id,
							'std'           => 'no',
							'type'          => 'checkbox',
							'checkboxgroup' => '',
						);

					}

					// end the checkbox group if needed
					if ( count( $categories ) > 0 ) {
						$updated_settings[ count( $updated_settings ) - 1 ]['checkboxgroup'] = 'end';
					}

					// end the section
					$updated_settings[] = array( 'type' => 'sectionend', 'id' => 'nested_category_layout_options' );
				}
			}
		}

		return $updated_settings;
	}


	/**
	 * Gets the plugin configuration URL
	 *
	 * @since 1.4
	 * @see SV_WC_Plugin::get_settings_url()
	 * @see SV_WC_Plugin::get_settings_link()
	 * @param string $plugin_id optional plugin identifier.  Note that this can be a
	 *        sub-identifier for plugins with multiple parallel settings pages
	 *        (ie a gateway that supports both credit cards and echecks)
	 * @return string plugin settings URL
	 */
	public function get_settings_url( $plugin_id = null ) {

		return admin_url( 'admin.php?page=wc-settings&tab=products&section=display' );
	}


	/** Helper methods ******************************************************/


	/**
	 * Main Nested Category Layout Instance, ensures only one instance is/can be loaded
	 *
	 * @since 1.6.0
	 * @see wc_nested_category_layout()
	 * @return WC_Nested_Category_Layout
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	/**
	 * Get product category depths relative to the current category
	 *
	 * @return array of category id to depth
	 *
	 * @since 1.0
	 */
	private function get_product_category_depths() {

		$categories = get_categories( array( 'taxonomy' => 'product_cat', 'child_of' => $this->get_current_product_category_id() ) );
		$categories = walk_category_tree( $categories, 0, array( 'walker' => new Walker_Category_Depth() ) );

		if ( is_array( $categories ) ) {
			return $categories;
		}

		// no categories found
		return array();
	}


	/**
	 * Return the current product category id
	 *
	 * @return int current product category id, or 0
	 *
	 * @since 1.0
	 */
	public function get_current_product_category_id() {
		// get the category id of the current page
		if ( $product_cat_slug = get_query_var( 'product_cat' ) ) {
			$product_cat = get_term_by( 'slug', $product_cat_slug, 'product_cat' );
			return $product_cat->term_id;
		} else {
			return 0;
		}
	}


	/**
	 * Returns true if nested subcategories are enabled for the given category
	 *
	 * @since 1.2.5
	 * @param int $category category id
	 * @return boolean true if nested subcategories are enabled for $category, false otherwise
	 */
	public function is_nested_subcategories_enabled_for_category( $category ) {
		return 'yes' === get_option( 'woocommerce_nested_subcat_' . $category, 'no' );
	}


	/**
	 * Returns the plugin name, localized
	 *
	 * @since 1.4
	 * @see SV_WC_Plugin::get_plugin_name()
	 * @return string the plugin name
	 */
	public function get_plugin_name() {

		return __( 'WooCommerce Nested Category Layout', 'woocommerce-nested-category-layout' );
	}


	/**
	 * Returns __FILE__
	 *
	 * @since 1.4
	 * @see SV_WC_Plugin::get_file()
	 * @return string the full path and filename of the plugin file
	 */
	protected function get_file() {

		return __FILE__;
	}


	/**
	 * Gets the plugin documentation URL
	 *
	 * @since 1.7.0
	 * @see SV_WC_Plugin::get_documentation_url()
	 * @return string
	 */
	public function get_documentation_url() {

		return 'http://docs.woothemes.com/document/woocommerce-nested-category-layout/';
	}


	/**
	 * Gets the plugin support URL
	 *
	 * @since 1.7.0
	 * @see SV_WC_Plugin::get_support_url()
	 * @return string
	 */
	public function get_support_url() {

		return 'http://support.woothemes.com/';
	}


	/** Lifecycle methods ******************************************************/


	/**
	 * Install default settings
	 *
	 * @see SV_WC_Plugin::install()
	 */
	protected function install() {

		// in version 1.4 the database
		$legacy_version = get_option( 'wc_nested_category_layout_db_version' );

		if ( $legacy_version ) {
			delete_option( 'wc_nested_category_layout_db_version' );
			return $this->upgrade( $legacy_version );
		}

		// initial install

		// settings defaults
		add_option( 'woocommerce_subcat_posts_per_page', apply_filters( 'loop_shop_per_page', get_option( 'posts_per_page' ) ) );
	}

}


/**
 * Returns the One True Instance of Nested Category Layout
 *
 * @since 1.6.0
 * @return WC_Nested_Category_Layout
 */
function wc_nested_category_layout() {
	return WC_Nested_Category_Layout::instance();
}


// fire it up!
wc_nested_category_layout();

} // init_woocommerce_nested_category_layout()
