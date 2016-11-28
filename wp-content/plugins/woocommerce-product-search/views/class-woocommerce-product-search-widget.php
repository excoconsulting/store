<?php
/**
 * class-woocommerce-product-search-widget.php
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
 * Product search widget.
 */
class WooCommerce_Product_Search_Widget extends WP_Widget {

	static $the_name = '';

	/**
	 * @var string cache id
	 */
	static $cache_id = 'woocommerce_product_search_widget';

	/**
	 * @var string cache flag
	 */
	static $cache_flag = 'widget';

	static $defaults = array();

	/**
	 * Initialize.
	 */
	static function init() {
		add_action( 'widgets_init', array( __CLASS__, 'widgets_init' ) );
		self::$the_name = __( 'WooCommerce Instant Product Search', WOO_PS_PLUGIN_DOMAIN );
	}

	/**
	 * Registers the widget.
	 */
	static function widgets_init() {
		register_widget( 'WooCommerce_Product_Search_Widget' );
	}

	/**
	 * Creates the widget.
	 */
	function __construct() {
		parent::__construct(
			self::$cache_id,
			self::$the_name,
			array(
				'description' => __( 'A dynamic product search widget', WOO_PS_PLUGIN_DOMAIN )
			)
		);
	}

	/**
	 * Clears cached widget.
	 */
	static function cache_delete() {
		wp_cache_delete( self::$cache_id, self::$cache_flag );
	}

	/**
	 * Widget output
	 * 
	 * @see WP_Widget::widget()
	 * @link http://codex.wordpress.org/Class_Reference/WP_Object_Cache
	 */
	function widget( $args, $instance ) {

		// This is done within the shortcode but the required scripts can
		// go missing if we don't do it here, too.
		WooCommerce_Product_Search_Shortcodes::load_resources();

		$cache = wp_cache_get( self::$cache_id, self::$cache_flag );
		if ( ! is_array( $cache ) ) {
			$cache = array();
		}
		if ( isset( $cache[$args['widget_id']] ) ) {
			echo $cache[$args['widget_id']];
			return;
		}

		extract( $args );

		$title = apply_filters( 'widget_title', $instance['title'] );

		$output = '';

		$output .= $before_widget;
		if ( !empty( $title ) ) {
			$output .= $before_title . $title . $after_title;
		}
		$instance['title'] = $instance['query_title'];
		$output .= WooCommerce_Product_Search_Shortcodes::woocommerce_product_search( $instance );
		$output .= $after_widget;

		echo $output;

		$cache[$args['widget_id']] = $output;
		wp_cache_set( self::$cache_id, $cache, self::$cache_flag );

	}

	/**
	 * Save widget options
	 * 
	 * @see WP_Widget::update()
	 */
	function update( $new_instance, $old_instance ) {

		global $wpdb;

		$settings = $old_instance;

		// widget title
		$settings['title'] = trim( strip_tags( $new_instance['title'] ) );

		// search in titles, excerpt, content, tags
		$settings['query_title']   = !empty( $new_instance['query_title'] ) ? 'yes' : 'no';
		$settings['excerpt'] = !empty( $new_instance['excerpt'] ) ? 'yes' : 'no';
		$settings['content'] = !empty( $new_instance['content'] ) ? 'yes' : 'no';
		$settings['tags']    = !empty( $new_instance['tags'] ) ? 'yes' : 'no';

		$settings['order']    = !empty( $new_instance['order'] ) ? $new_instance['order'] : 'DESC';
		$settings['order_by'] = !empty( $new_instance['order_by'] ) ? $new_instance['order_by'] : 'date';

		$limit = !empty( $new_instance['limit'] ) ? intval( $new_instance['limit'] ) : WooCommerce_Product_Search_Service::DEFAULT_LIMIT;
		if ( $limit < 0 ) {
			$limit = WooCommerce_Product_Search_Service::DEFAULT_LIMIT;
		}
		$settings['limit'] = $limit;

		$settings['category_results'] = !empty( $new_instance['category_results'] ) ? 'yes' : 'no';
		$category_limit = !empty( $new_instance['category_limit'] ) ? intval( $new_instance['category_limit'] ) : WooCommerce_Product_Search_Service::DEFAULT_CATEGORY_LIMIT;
		if ( $category_limit < 0 ) {
			$category_limit = WooCommerce_Product_Search_Service::DEFAULT_CATEGORY_LIMIT;
		}
		$settings['category_limit'] = $category_limit;

		$settings['product_thumbnails'] = !empty( $new_instance['product_thumbnails'] ) ? 'yes' : 'no';

		$settings['show_description'] = !empty( $new_instance['show_description'] ) ? 'yes' : 'no';

		$settings['show_price'] = !empty( $new_instance['show_price'] ) ? 'yes' : 'no';

		$delay = !empty( $new_instance['delay'] ) ? intval( $new_instance['delay'] ) : WooCommerce_Product_Search::DEFAULT_DELAY;
		if ( $delay < WooCommerce_Product_Search::MIN_DELAY ) {
			$delay = WooCommerce_Product_Search::MIN_DELAY;
		}
		$settings['delay'] = $delay;

		$characters = !empty( $new_instance['characters'] ) ? intval( $new_instance['characters'] ) : WooCommerce_Product_Search::DEFAULT_CHARACTERS;
		if ( $characters < WooCommerce_Product_Search::MIN_CHARACTERS ) {
			$characters = WooCommerce_Product_Search::MIN_CHARACTERS;
		}
		$settings['characters'] = $characters;

		$settings['placeholder'] = trim( strip_tags( $new_instance['placeholder'] ) );

		$settings['dynamic_focus'] = !empty( $new_instance['dynamic_focus'] ) ? 'yes' : 'no';
		$settings['floating']      = !empty( $new_instance['floating'] ) ? 'yes' : 'no';
		$settings['inhibit_enter'] = !empty( $new_instance['inhibit_enter'] ) ? 'yes' : 'no';
		$settings['submit_button'] = !empty( $new_instance['submit_button'] ) ? 'yes' : 'no';
		$settings['submit_button_label'] = strip_tags( $new_instance['submit_button_label'] );
		$settings['navigable']     = !empty( $new_instance['navigable'] ) ? 'yes' : 'no';
		$settings['no_results']    = trim( strip_tags( $new_instance['no_results'] ) );
		$settings['auto_adjust']   = !empty( $new_instance['auto_adjust'] ) ? 'yes' : 'no';

		if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
			$settings['wpml']   = !empty( $new_instance['wpml'] ) ? 'yes' : 'no';
		}

		$this->cache_delete();

		return $settings;
	}

	/**
	 * Output admin widget options form
	 * 
	 * @see WP_Widget::form()
	 */
	function form( $instance ) {

		extract( self::$defaults );

		// title
		$widget_title = isset( $instance['title'] ) ? $instance['title'] : "";
		echo '<p>';
		echo sprintf( '<label title="%s">', sprintf( __( 'The widget title.', WOO_PS_PLUGIN_DOMAIN ) ) );
		echo __( 'Title', WOO_PS_PLUGIN_DOMAIN );
		echo '<input class="widefat" id="' . $this->get_field_id( 'title' ) . '" name="' . $this->get_field_name( 'title' ) . '" type="text" value="' . esc_attr( $widget_title ) . '" />';
		echo '</label>';
		echo '</p>';

		echo '<h5>' . __( 'Search Results', WOO_PS_PLUGIN_DOMAIN ) . '</h5>';

		$title = isset( $instance['query_title'] ) ? $instance['query_title'] : 'yes';
		echo '<p>';
		echo sprintf( '<label title="%s">', sprintf( __( 'Search results should include matching titles.', WOO_PS_PLUGIN_DOMAIN ) ) );
		printf(
			'<input type="checkbox" id="%s" name="%s" %s />',
			$this->get_field_id( 'query_title' ),
			$this->get_field_name( 'query_title' ),
			$title == 'yes' ? ' checked="checked" ' : ''
		);
		echo ' ';
		echo __( 'Search in titles', WOO_PS_PLUGIN_DOMAIN );
		echo '</label>';
		echo '</p>';

		$excerpt = isset( $instance['excerpt'] ) ? $instance['excerpt'] : 'yes';
		echo '<p>';
		echo sprintf( '<label title="%s">', sprintf( __( 'Search results should include matches in excerpts.', WOO_PS_PLUGIN_DOMAIN ) ) );
		printf(
			'<input type="checkbox" id="%s" name="%s" %s />',
			$this->get_field_id( 'excerpt' ),
			$this->get_field_name( 'excerpt' ),
			$excerpt == 'yes' ? ' checked="checked" ' : ''
		);
		echo ' ';
		echo __( 'Search in excerpts', WOO_PS_PLUGIN_DOMAIN );
		echo '</label>';
		echo '</p>';

		$content = isset( $instance['content'] ) ? $instance['content'] : 'yes';
		echo '<p>';
		echo sprintf( '<label title="%s">', sprintf( __( 'Search results should include matches in contents.', WOO_PS_PLUGIN_DOMAIN ) ) );
		printf(
			'<input type="checkbox" id="%s" name="%s" %s />',
			$this->get_field_id( 'content' ),
			$this->get_field_name( 'content' ),
			$content == 'yes' ? ' checked="checked" ' : ''
		);
		echo ' ';
		echo __( 'Search in contents', WOO_PS_PLUGIN_DOMAIN );
		echo '</label>';
		echo '</p>';

		$tags = isset( $instance['tags'] ) ? $instance['tags'] : 'yes';
		echo '<p>';
		echo sprintf( '<label title="%s">', sprintf( __( 'Search results should include entries with matching tags.', WOO_PS_PLUGIN_DOMAIN ) ) );
		printf(
			'<input type="checkbox" id="%s" name="%s" %s />',
			$this->get_field_id( 'tags' ),
			$this->get_field_name( 'tags' ),
			$tags == 'yes' ? ' checked="checked" ' : ''
		);
		echo ' ';
		echo __( 'Search in tags', WOO_PS_PLUGIN_DOMAIN );
		echo '</label>';
		echo '</p>';

		$order_by = isset( $instance['order_by'] ) ? $instance['order_by'] : 'date';
		echo '<p>';
		echo sprintf( '<label title="%s">', sprintf( __( 'Order the results by the chosen property.', WOO_PS_PLUGIN_DOMAIN ) ) );
		echo __( 'Order by ...', WOO_PS_PLUGIN_DOMAIN );
		echo ' ';
		printf(
			'<select id="%s" name="%s">',
			$this->get_field_id( 'order_by' ),
			$this->get_field_name( 'order_by' )
		);
		$options = array(
			'date'  => __( 'Date', WOO_PS_PLUGIN_DOMAIN ),
			'title' => __( 'Title', WOO_PS_PLUGIN_DOMAIN ),
			'ID'    => __( 'ID', WOO_PS_PLUGIN_DOMAIN ),
			'rand'  => __( 'Random', WOO_PS_PLUGIN_DOMAIN )
		);
		foreach( $options as $key => $value ) {
			printf( '<option value="%s" %s>%s</option>', $key, $order_by == $key ? ' selected="selected" ' : '', $value );
		}
		echo '</select>';
		echo '</label>';
		echo '</p>';

		$order = isset( $instance['order'] ) ? $instance['order'] : 'DESC';
		echo '<p>';
		echo '<label>';
		printf( '<input type="radio" name="%s" value="ASC" %s />', $this->get_field_name( 'order' ), $order == 'ASC' ? ' checked="checked" ' : '' );
		echo ' ';
		echo __( 'Ascending', WOO_PS_PLUGIN_DOMAIN );
		echo '</label>';
		echo ' ';
		echo '<label>';
		printf( '<input type="radio" name="%s" value="DESC" %s />', $this->get_field_name( 'order' ), $order == 'DESC' ? ' checked="checked" ' : '' );
		echo ' ';
		echo __( 'Descending', WOO_PS_PLUGIN_DOMAIN );
		echo '</label>';
		echo '</p>';

		// limit
		$limit = isset( $instance['limit'] ) ? intval( $instance['limit'] ) : WooCommerce_Product_Search_Service::DEFAULT_LIMIT;
		echo '<p>';
		echo sprintf( '<label title="%s">', sprintf( __( 'Limit the maximum number of results shown.', WOO_PS_PLUGIN_DOMAIN ) ) );
		echo __( 'Limit', WOO_PS_PLUGIN_DOMAIN );
		echo ' ';
		echo '<input id="' . $this->get_field_id( 'limit' ) . '" name="' . $this->get_field_name( 'limit' ) . '" type="text" value="' . esc_attr( $limit ) . '" />';
		echo '</label>';
		echo '</p>';

		// category results
		$category_results = isset( $instance['category_results'] ) ? $instance['category_results'] : 'yes';
		echo '<p>';
		echo sprintf( '<label title="%s">', sprintf( __( 'Search results should include categories with matching results.', WOO_PS_PLUGIN_DOMAIN ) ) );
		printf(
			'<input type="checkbox" id="%s" name="%s" %s />',
			$this->get_field_id( 'category_results' ),
			$this->get_field_name( 'category_results' ),
			$category_results == 'yes' ? ' checked="checked" ' : ''
		);
		echo ' ';
		echo __( 'Show category matches', WOO_PS_PLUGIN_DOMAIN );
		echo '</label>';
		echo '</p>';

		// category limit
		$category_limit = isset( $instance['category_limit'] ) ? intval( $instance['category_limit'] ) : WooCommerce_Product_Search_Service::DEFAULT_CATEGORY_LIMIT;
		echo '<p>';
		echo sprintf( '<label title="%s">', sprintf( __( 'Limit the maximum number of category results shown.', WOO_PS_PLUGIN_DOMAIN ) ) );
		echo __( 'Category Limit', WOO_PS_PLUGIN_DOMAIN );
		echo ' ';
		echo '<input id="' . $this->get_field_id( 'category_limit' ) . '" name="' . $this->get_field_name( 'category_limit' ) . '" type="text" value="' . esc_attr( $category_limit ) . '" />';
		echo '</label>';
		echo '</p>';

		$product_thumbnails = isset( $instance['product_thumbnails'] ) ? $instance['product_thumbnails'] : 'yes';
		echo '<p>';
		echo sprintf( '<label title="%s">', sprintf( __( 'Show a product thumbnail for each result.', WOO_PS_PLUGIN_DOMAIN ) ) );
		printf(
			'<input type="checkbox" id="%s" name="%s" %s />',
			$this->get_field_id( 'product_thumbnails' ),
			$this->get_field_name( 'product_thumbnails' ),
			$product_thumbnails == 'yes' ? ' checked="checked" ' : ''
		);
		echo ' ';
		echo __( 'Show product thumbnails', WOO_PS_PLUGIN_DOMAIN );
		echo '</label>';
		echo '</p>';

		$show_description = isset( $instance['show_description'] ) ? $instance['show_description'] : 'no';
		echo '<p>';
		echo sprintf( '<label title="%s">', sprintf( __( 'Show short product descriptions.', WOO_PS_PLUGIN_DOMAIN ) ) );
		printf(
			'<input type="checkbox" id="%s" name="%s" %s />',
			$this->get_field_id( 'show_description' ),
			$this->get_field_name( 'show_description' ),
			$show_description == 'yes' ? ' checked="checked" ' : ''
		);
		echo ' ';
		echo __( 'Show descriptions', WOO_PS_PLUGIN_DOMAIN );
		echo '</label>';
		echo '</p>';

		$show_price = isset( $instance['show_price'] ) ? $instance['show_price'] : 'no';
		echo '<p>';
		echo sprintf( '<label title="%s">', sprintf( __( 'Show product prices.', WOO_PS_PLUGIN_DOMAIN ) ) );
		printf(
			'<input type="checkbox" id="%s" name="%s" %s />',
			$this->get_field_id( 'show_price' ),
			$this->get_field_name( 'show_price' ),
			$show_price == 'yes' ? ' checked="checked" ' : ''
		);
		echo ' ';
		echo __( 'Show prices', WOO_PS_PLUGIN_DOMAIN );
		echo '</label>';
		echo '</p>';

		echo '<h5>' . __( 'Search Form and UI Interaction', WOO_PS_PLUGIN_DOMAIN ) . '</h5>';

		// delay
		$delay = isset( $instance['delay'] ) ? intval( $instance['delay'] ) : WooCommerce_Product_Search::DEFAULT_DELAY;
		echo '<p>';
		echo sprintf( '<label title="%s">', sprintf( __( 'The delay until the search starts after the user stops typing (in milliseconds, minimum %d).', WOO_PS_PLUGIN_DOMAIN ), WooCommerce_Product_Search::MIN_DELAY ) );
		echo __( 'Delay', WOO_PS_PLUGIN_DOMAIN );
		echo ' ';
		echo '<input id="' . $this->get_field_id( 'delay' ) . '" name="' . $this->get_field_name( 'delay' ) . '" type="text" value="' . esc_attr( $delay ) . '" />';
		echo ' ';
		echo __( 'ms', WOO_PS_PLUGIN_DOMAIN );
		echo '</label>';
		echo '</p>';

		// characters
		$characters = isset( $instance['characters'] ) ? intval( $instance['characters'] ) : WooCommerce_Product_Search::DEFAULT_CHARACTERS;
		echo '<p>';
		echo sprintf( '<label title="%s">', sprintf( __( 'The minimum number of characters required to start a search.', WOO_PS_PLUGIN_DOMAIN ) ) );
		echo __( 'Characters', WOO_PS_PLUGIN_DOMAIN );
		echo ' ';
		echo '<input id="' . $this->get_field_id( 'characters' ) . '" name="' . $this->get_field_name( 'characters' ) . '" type="text" value="' . esc_attr( $characters ) . '" />';
		echo '</label>';
		echo '</p>';

		// inhibit the enter key
		$inhibit_enter = isset( $instance['inhibit_enter'] ) ? $instance['inhibit_enter'] : 'no';
		echo '<p>';
		echo sprintf( '<label title="%s">', sprintf( __( 'If the Enter key is not inhibited, a normal product search is requested when the visitor presses the Enter key in the search field.', WOO_PS_PLUGIN_DOMAIN ) ) );
		printf(
			'<input type="checkbox" id="%s" name="%s" %s />',
			$this->get_field_id( 'inhibit_enter' ),
			$this->get_field_name( 'inhibit_enter' ),
			$inhibit_enter == 'yes' ? ' checked="checked" ' : ''
		);
		echo ' ';
		echo __( 'Inhibit form submission via the <em>Enter</em> key', WOO_PS_PLUGIN_DOMAIN );
		echo '</label>';
		echo '</p>';

		$navigable = isset( $instance['navigable'] ) ? $instance['navigable'] : 'yes';
		echo '<p>';
		echo sprintf( '<label title="%s">', sprintf( __( 'If enabled, the visitor can use the cursor keys to navigate through the search results and visit a search result link by pressing the Enter key.', WOO_PS_PLUGIN_DOMAIN ) ) );
		printf(
			'<input type="checkbox" id="%s" name="%s" %s />',
			$this->get_field_id( 'navigable' ),
			$this->get_field_name( 'navigable' ),
			$navigable == 'yes' ? ' checked="checked" ' : ''
		);
		echo ' ';
		echo __( 'Navigable results', WOO_PS_PLUGIN_DOMAIN );
		echo '</label>';
		echo '</p>';

		// placeholder
		$placeholder = isset( $instance['placeholder'] ) ? $instance['placeholder'] : __( 'Search', WOO_PS_PLUGIN_DOMAIN );
		echo '<p>';
		echo sprintf( '<label title="%s">', sprintf( __( 'The placeholder text for the search field.', WOO_PS_PLUGIN_DOMAIN ) ) );
		echo __( 'Placeholder', WOO_PS_PLUGIN_DOMAIN );
		echo ' ';
		echo '<input id="' . $this->get_field_id( 'placeholder' ) . '" name="' . $this->get_field_name( 'placeholder' ) . '" type="text" value="' . esc_attr( $placeholder ) . '" />';
		echo '</label>';
		echo '</p>';

		// submit button
		$submit_button = isset( $instance['submit_button'] ) ? $instance['submit_button'] : 'no';
		echo '<p>';
		echo sprintf( '<label title="%s">', sprintf( __( 'Show a submit button along with the search field.', WOO_PS_PLUGIN_DOMAIN ) ) );
		printf(
			'<input type="checkbox" id="%s" name="%s" %s />',
			$this->get_field_id( 'submit_button' ),
			$this->get_field_name( 'submit_button' ),
			$submit_button == 'yes' ? ' checked="checked" ' : ''
		);
		echo ' ';
		echo __( 'Submit button', WOO_PS_PLUGIN_DOMAIN );
		echo '</label>';
		echo '</p>';

		// submit button label
		$submit_button_label = isset( $instance['submit_button_label'] ) ? $instance['submit_button_label'] : __( 'Search', WOO_PS_PLUGIN_DOMAIN );
		echo '<p>';
		echo sprintf( '<label title="%s">', sprintf( __( 'The text shown on the submit button.', WOO_PS_PLUGIN_DOMAIN ) ) );
		echo __( 'Submit button label', WOO_PS_PLUGIN_DOMAIN );
		echo ' ';
		echo '<input id="' . $this->get_field_id( 'submit_button_label' ) . '" name="' . $this->get_field_name( 'submit_button_label' ) . '" type="text" value="' . esc_attr( $submit_button_label ) . '" />';
		echo '</label>';
		echo '</p>';

		// dynamic focus
		$dynamic_focus = isset( $instance['dynamic_focus'] ) ? $instance['dynamic_focus'] : 'yes';
		echo '<p>';
		echo sprintf( '<label title="%s">', sprintf( __( 'Show/hide search results when the search input field gains/loses focus.', WOO_PS_PLUGIN_DOMAIN ) ) );
		printf(
			'<input type="checkbox" id="%s" name="%s" %s />',
			$this->get_field_id( 'dynamic_focus' ),
			$this->get_field_name( 'dynamic_focus' ),
			$dynamic_focus == 'yes' ? ' checked="checked" ' : ''
		);
		echo ' ';
		echo __( 'Dynamic focus', WOO_PS_PLUGIN_DOMAIN );
		echo '</label>';
		echo '</p>';

		// floating results
		$floating = isset( $instance['floating'] ) ? $instance['floating'] : 'no';
		echo '<p>';
		echo sprintf( '<label title="%s">', sprintf( __( 'Search results are shown floating below the search field.', WOO_PS_PLUGIN_DOMAIN ) ) );
		printf(
			'<input type="checkbox" id="%s" name="%s" %s />',
			$this->get_field_id( 'floating' ),
			$this->get_field_name( 'floating' ),
			$floating == 'yes' ? ' checked="checked" ' : ''
		);
		echo ' ';
		echo __( 'Floating results', WOO_PS_PLUGIN_DOMAIN );
		echo '</label>';
		echo '</p>';

		// no results
		$no_results = isset( $instance['no_results'] ) ? $instance['no_results'] : '';
		echo '<p>';
		echo sprintf( '<label title="%s">', sprintf( __( 'The text shown when no search results are obtained.', WOO_PS_PLUGIN_DOMAIN ) ) );
		echo __( 'No results', WOO_PS_PLUGIN_DOMAIN );
		echo ' ';
		echo '<input id="' . $this->get_field_id( 'no_results' ) . '" name="' . $this->get_field_name( 'no_results' ) . '" type="text" value="' . esc_attr( $no_results ) . '" />';
		echo '</label>';
		echo '</p>';

		// auto adjust the results width
		$auto_adjust = isset( $instance['auto_adjust'] ) ? $instance['auto_adjust'] : 'yes';
		echo '<p>';
		echo sprintf( '<label title="%s">', sprintf( __( 'Automatically adjust the width of the results to match that of the search field.', WOO_PS_PLUGIN_DOMAIN ) ) );
		printf(
			'<input type="checkbox" id="%s" name="%s" %s />',
			$this->get_field_id( 'auto_adjust' ),
			$this->get_field_name( 'auto_adjust' ),
			$auto_adjust == 'yes' ? ' checked="checked" ' : ''
		);
		echo ' ';
		echo __( 'Auto-adjust results width', WOO_PS_PLUGIN_DOMAIN );
		echo '</label>';
		echo '</p>';
		
		if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
			$wpml = isset( $instance['wpml'] ) ? $instance['wpml'] : 'no';
			echo '<p>';
			echo sprintf( '<label title="%s">', sprintf( __( 'Filter search results based on the current language.', WOO_PS_PLUGIN_DOMAIN ) ) );
			printf(
				'<input type="checkbox" id="%s" name="%s" %s />',
				$this->get_field_id( 'wpml' ),
				$this->get_field_name( 'wpml' ),
				$wpml == 'yes' ? ' checked="checked" ' : ''
			);
			echo ' ';
			echo __( 'WMPL Language Filter', WOO_PS_PLUGIN_DOMAIN );
			echo '</label>';
			echo '</p>';
		}
	}

}

WooCommerce_Product_Search_Widget::init();
