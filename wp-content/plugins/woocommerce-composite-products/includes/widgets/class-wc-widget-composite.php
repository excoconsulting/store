<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Composite Product Config Summary Widget.
 *
 * Displays configuration summary of the currently displayed composite product.
 * By default applicable to Multi-page Composites only.
 *
 * @version  3.6.0
 * @since    3.0.0
 * @extends  WC_Widget
 */
class WC_Widget_Composite extends WC_Widget {

	const BASE_ID = 'woocommerce_widget_composite_summary';

	/**
	 * Constructor
	 */
	public function __construct() {

		$this->widget_cssclass    = 'woocommerce widget_composite_summary composite_summary cp-no-js';
		$this->widget_description = __( "Shows a Composite product configuration summary. Displayed only when viewing single-product pages of the Composite type.", 'woocommerce-composite-products' );
		$this->widget_id          = self::BASE_ID;
		$this->widget_name        = __( 'WooCommerce Composite Product Summary', 'woocommerce-composite-products' );
		$this->settings           = array(
			'title'  => array(
				'type'  => 'text',
				'std'   => __( 'Your Configuration', 'woocommerce-composite-products' ),
				'label' => __( 'Title', 'woocommerce' )
			)
		);

		parent::__construct();
	}

	/**
	 * Widget function.
	 *
	 * @see WP_Widget
	 *
	 * @param array $args
	 * @param array $instance
	 *
	 * @return void
	 */
	public function widget( $args, $instance ) {

		global $product;

		if ( ! self::is_visible() ) {
			// Normally, this should never happen since 'sidebars_widgets' are filtered by 'wc_cp_remove_composite_summary_widget' to remove non-visible composite summary widgets.
			return;
		}

		echo $args[ 'before_widget' ];

		$default = isset( $this->settings[ 'title' ][ 'std' ] ) ? $this->settings[ 'title' ][ 'std' ] : '';
		if ( $title = apply_filters( 'widget_title', empty( $instance[ 'title' ] ) ? $default : $instance[ 'title' ], $instance, $this->id_base ) ) {
			echo $args[ 'before_title' ] . $title . $args[ 'after_title' ];
		}

		// Insert widget placeholder elements - code in woocommerce.js will update them as required
		ob_start();

		?><div class="widget_composite_summary_content widget_composite_summary_content_<?php echo $product->id; ?>" data-container_id="<?php echo $product->id; ?>"><?php

			$components = $product->get_composite_data();

			if ( ! empty( $components ) ) {
				/**
				 * 'woocommerce_composite_summary_widget_content' hook:
				 * @since  3.6.0
				 *
				 * @hooked wc_cp_summary_widget_content       - 10
				 * @hooked wc_cp_summary_widget_price         - 20
				 * @hooked wc_cp_summary_widget_message       - 30
				 * @hooked wc_cp_summary_widget_availability  - 40
				 * @hooked wc_cp_summary_widget_button        - 50
				 */
				do_action( 'woocommerce_composite_summary_widget_content', $components, $product );
			}

		?></div><?php

		echo ob_get_clean();

		echo $args[ 'after_widget' ];
	}

	/**
	 * True if the widget can be viewed.
	 *
	 * @return boolean
	 */
	public static function is_visible() {

		global $post, $product;

		$show_widget = false;

		if ( function_exists( 'is_product' ) && is_product() ) {

			if ( false === ( $product instanceof WC_Product ) ) {
				$product = wc_get_product( $post->ID );
			}

			if ( 'composite' === $product->product_type ) {
				$layout_style           = $product->get_composite_layout_style();
				$layout_style_variation = $product->get_composite_layout_style_variation();
				$show_widget            = apply_filters( 'woocommerce_composite_summary_widget_display', true, $layout_style, $layout_style_variation, $product );
			}
		}

		return $show_widget;
	}

	/**
	 * True if the widget is visible.
	 *
	 * @return boolean
	 */
	public static function is_active() {

		return is_active_widget( false, false, self::BASE_ID, true );
	}
}
