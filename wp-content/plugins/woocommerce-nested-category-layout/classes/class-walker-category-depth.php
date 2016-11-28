<?php
/**
 * WooCommerce Nested Category Layout
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
 * Do not edit or add to this file if you wish to upgrade WooCommerce Nested Category Layout to newer
 * versions in the future. If you wish to customize WooCommerce Nested Category Layout for your
 * needs please refer to http://docs.woothemes.com/document/woocommerce-nested-category-layout/ for more information.
 *
 * @package   WC-Nested-Category-Layout/Class
 * @author    SkyVerge
 * @copyright Copyright (c) 2012-2016, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

/**
 * Category walker
 *
 * @since 1.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * A category walker which walks through the categories, and determines their
 * depths
 *
 * @since 1.0
 */
class Walker_Category_Depth extends Walker {
	var $tree_type = 'product_cat';  // not sure whether this is even used for anything
	var $db_fields = array( 'parent' => 'parent', 'id' => 'term_id' );

	/**
	 * Starts the list before the elements are added.
	 *
	 * @see Walker::start_el()
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $category Category data object.
	 * @param int $depth Depth of category. Used for padding.
	 * @param array $args Uses 'selected', 'show_count', and 'show_last_update' keys, if they exist.
	 *
	 * @since 1.0
	 */
	function start_el( &$output, $category, $depth = 0, $args = array(), $current_object_id = 0 ) {
		$output[ $category->term_id ] = $depth;
	}
}
