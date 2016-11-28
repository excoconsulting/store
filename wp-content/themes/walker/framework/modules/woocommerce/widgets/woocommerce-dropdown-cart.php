<?php
class WalkerEdgeClassWoocommerceDropdownCart extends WP_Widget {
	public function __construct() {
		parent::__construct(
			'edgtf_woocommerce_dropdown_cart', // Base ID
			'Edge Woocommerce Dropdown Cart', // Name
			array( 'description' => esc_html__( 'Edge Woocommerce Dropdown Cart', 'walker' ), ) // Args
		);

		$this->setParams();
	}

	protected function setParams() {

		$this->params = array(
			array(
				'name'			=> 'woocommerce_dropdown_cart_margin',
				'type'			=> 'textfield',
				'title'			=> 'Margin (top right bottom left)',
				'description'	=> 'Define margin for woocommerce dropdown cart icon'
			),
			array(
                'type' => 'dropdown',
                'title' => 'Enable Cart Info',
                'name' => 'woocommerce_enable_cart_info',
                'options' => array(
                    'no' => 'No',
                    'yes' => 'Yes'
                ),
                'description' => 'Enabling this option will show cart info (products number and price) at the right side of dropdown cart icon'
            ),
		);
    }

    /**
     * Generate widget form based on $params attribute
     *
     * @param array $instance
     *
     * @return null
     */
    public function form($instance) {
        if(is_array($this->params) && count($this->params)) {
            foreach($this->params as $param_array) {
                $param_name    = $param_array['name'];
                ${$param_name} = isset($instance[$param_name]) ? esc_attr($instance[$param_name]) : '';
            }

            foreach($this->params as $param) {
                switch($param['type']) {
                    case 'textfield':
                        ?>
                        <p>
                            <label for="<?php echo esc_attr($this->get_field_id($param['name'])); ?>"><?php echo
                                esc_html($param['title']); ?>:</label>
                            <input class="widefat" id="<?php echo esc_attr($this->get_field_id($param['name'])); ?>" name="<?php echo esc_attr($this->get_field_name($param['name'])); ?>" type="text" value="<?php echo esc_attr(${$param['name']}); ?>"/>
                            <?php if(!empty($param['description'])) : ?>
                                <span class="edgtf-field-description"><?php echo esc_html($param['description']); ?></span>
                            <?php endif; ?>
                        </p>
                        <?php
                        break;
                    case 'dropdown':
                        ?>
                        <p>
                            <label for="<?php echo esc_attr($this->get_field_id($param['name'])); ?>"><?php echo
                                esc_html($param['title']); ?>:</label>
                            <?php if(isset($param['options']) && is_array($param['options']) && count($param['options'])) { ?>
                                <select class="widefat" name="<?php echo esc_attr($this->get_field_name($param['name'])); ?>" id="<?php echo esc_attr($this->get_field_id($param['name'])); ?>">
                                    <?php foreach($param['options'] as $param_option_key => $param_option_val) {
                                        $option_selected = '';
                                        if(${$param['name']} == $param_option_key) {
                                            $option_selected = 'selected';
                                        }
                                        ?>
                                        <option <?php echo esc_attr($option_selected); ?> value="<?php echo esc_attr($param_option_key); ?>"><?php echo esc_attr($param_option_val); ?></option>
                                    <?php } ?>
                                </select>
                            <?php } ?>
                            <?php if(!empty($param['description'])) : ?>
                                <span class="edgtf-field-description"><?php echo esc_html($param['description']); ?></span>
                            <?php endif; ?>
                        </p>

                        <?php
                        break;    
                }
            }
        } else { ?>
            <p><?php esc_html_e('There are no options for this widget.', 'walker'); ?></p>
        <?php }
    }

    /**
     * @param array $new_instance
     * @param array $old_instance
     *
     * @return array
     */
    public function update($new_instance, $old_instance) {
        $instance = array();
        foreach($this->params as $param) {
            $param_name = $param['name'];

            $instance[$param_name] = sanitize_text_field($new_instance[$param_name]);
        }

        return $instance;
    }

	public function widget( $args, $instance ) {
		global $post;
		extract( $args );
		
		global $woocommerce;
		
		$icon_styles = array();

		if (!empty($instance['woocommerce_dropdown_cart_margin'])) {
			$icon_styles[] = 'padding: ' . $instance['woocommerce_dropdown_cart_margin'];
		}

		$icon_class = 'edgtf-cart-info-is-disabled';

		if (!empty($instance['woocommerce_enable_cart_info']) && $instance['woocommerce_enable_cart_info'] === 'yes') {
			$icon_class = 'edgtf-cart-info-is-active';
		}

		$cart_description = walker_edge_options()->getOptionValue('edgtf_woo_dropdown_cart_description');
		?>
		<div class="edgtf-shopping-cart-holder <?php echo esc_html($icon_class); ?>" <?php walker_edge_inline_style($icon_styles) ?>>
			<div class="edgtf-shopping-cart-inner">
				<?php $cart_is_empty = sizeof( $woocommerce->cart->get_cart() ) <= 0; ?>
				<a itemprop="url" class="edgtf-header-cart" href="<?php echo esc_url($woocommerce->cart->get_cart_url()); ?>">
					<span class="edgtf-cart-icon icon-ecommerce-bag"><span class="edgtf-cart-number"><?php echo sprintf (_n( '%d', '%d', WC()->cart->cart_contents_count, 'walker' ), WC()->cart->cart_contents_count ); ?></span></span>
					<span class="edgtf-cart-info">
						<span class="edgtf-cart-info-number"><?php echo sprintf (_n( '%d item', '%d items', WC()->cart->cart_contents_count, 'walker' ), WC()->cart->cart_contents_count ); ?></span>
						<span class="edgtf-cart-info-total"><?php echo wp_kses($woocommerce->cart->get_cart_subtotal(), array('span' => array('class' => true,'id' => true))); ?></span>
					</span>			
				</a>
				<?php if ( !$cart_is_empty ) : ?>
					<div class="edgtf-shopping-cart-dropdown">
						<ul>
							<?php foreach ( $woocommerce->cart->get_cart() as $cart_item_key => $cart_item ) :
								$_product = $cart_item['data'];
								// Only display if allowed
								if ( ! $_product->exists() || $cart_item['quantity'] == 0 ) {
									continue;
								}
								// Get price
								$product_price = get_option( 'woocommerce_tax_display_cart' ) == 'excl' ? $_product->get_price_excluding_tax() : $_product->get_price_including_tax();
								?>
								<li>
									<div class="edgtf-item-image-holder">
										<a itemprop="url" href="<?php echo esc_url(get_permalink( $cart_item['product_id'] )); ?>">
											<?php echo wp_kses($_product->get_image(), array(
												'img' => array(
												'src' => true,
												'width' => true,
												'height' => true,
												'class' => true,
												'alt' => true,
												'title' => true,
												'id' => true
												)
											)); ?>
										</a>
									</div>
									<div class="edgtf-item-info-holder">
										<h5 itemprop="name" class="edgtf-product-title"><a itemprop="url" href="<?php echo esc_url(get_permalink( $cart_item['product_id'] )); ?>"><?php echo apply_filters('walker_edge_woo_widget_cart_product_title', $_product->get_title(), $_product ); ?></a></h5>
										<span class="edgtf-quantity"><?php esc_html_e('Quantity: ','walker'); echo esc_html($cart_item['quantity']); ?></span>
										<?php echo apply_filters( 'walker_edge_woo_cart_item_price_html', wc_price( $product_price ), $cart_item, $cart_item_key ); ?>
										<?php echo apply_filters( 'walker_edge_woo_cart_item_remove_link', sprintf('<a href="%s" class="remove" title="%s"><span class="ion-ios-close-empty"></span></a>', esc_url( $woocommerce->cart->get_remove_url( $cart_item_key ) ), esc_html__('Remove this item', 'walker') ), $cart_item_key ); ?>
									</div>
								</li>
							<?php endforeach; ?>
							<div class="edgtf-cart-bottom">
								<div class="edgtf-subtotal-holder clearfix">
									<span class="edgtf-total"><?php esc_html_e( 'ORDER TOTAL:', 'walker' ); ?></span>
									<span class="edgtf-total-amount">
										<?php echo wp_kses($woocommerce->cart->get_cart_subtotal(), array(
											'span' => array(
											'class' => true,
											'id' => true
											)
										)); ?>
									</span>
								</div>
								<?php if(!empty($cart_description)) { ?>
									<div class="edgtf-cart-description">
										<div class="edgtf-cart-description-inner">
											<span><?php echo esc_html($cart_description); ?></span>
										</div>		
									</div>
								<?php } ?>	
								<div class="edgtf-btn-holder clearfix">
									<a itemprop="url" href="<?php echo esc_url($woocommerce->cart->get_cart_url()); ?>" class="edgtf-view-cart" data-title="<?php esc_html_e('VIEW BAG & CHECKOUT','walker'); ?>"><span><?php esc_html_e('VIEW BAG & CHECKOUT','walker'); ?></span></a>
								</div>
							</div>
						</ul>
					</div>
				<?php else : ?>
					<div class="edgtf-shopping-cart-dropdown">
						<ul>
							<li class="edgtf-empty-cart"><?php esc_html_e( 'No products in the cart.', 'walker' ); ?></li>
						</ul>
					</div>		
				<?php endif; ?>
			</div>	
		</div>
		<?php
	}
}
add_action( 'widgets_init', create_function( '', 'register_widget( "WalkerEdgeClassWoocommerceDropdownCart" );' ) );

add_filter('add_to_cart_fragments', 'walker_edge_woocommerce_header_add_to_cart_fragment');

function walker_edge_woocommerce_header_add_to_cart_fragment( $fragments ) {
	global $woocommerce;

	$cart_description = walker_edge_options()->getOptionValue('edgtf_woo_dropdown_cart_description');

	ob_start();

	?>

	<div class="edgtf-shopping-cart-inner">
		<?php $cart_is_empty = sizeof( $woocommerce->cart->get_cart() ) <= 0; ?>
		<a itemprop="url" class="edgtf-header-cart" href="<?php echo esc_url($woocommerce->cart->get_cart_url()); ?>">
			<span class="edgtf-cart-icon icon-ecommerce-bag"><span class="edgtf-cart-number"><?php echo sprintf (_n( '%d', '%d', WC()->cart->cart_contents_count, 'walker' ), WC()->cart->cart_contents_count ); ?></span></span>
			<span class="edgtf-cart-info">
				<span class="edgtf-cart-info-number"><?php echo sprintf (_n( '%d item', '%d items', WC()->cart->cart_contents_count, 'walker' ), WC()->cart->cart_contents_count ); ?></span>
				<span class="edgtf-cart-info-total"><?php echo wp_kses($woocommerce->cart->get_cart_subtotal(), array('span' => array('class' => true,'id' => true))); ?></span>
			</span>	
		</a>
		<?php if ( !$cart_is_empty ) : ?>
			<div class="edgtf-shopping-cart-dropdown">
				<ul>
					<?php foreach ( $woocommerce->cart->get_cart() as $cart_item_key => $cart_item ) :
						$_product = $cart_item['data'];
						// Only display if allowed
						if ( ! $_product->exists() || $cart_item['quantity'] == 0 ) {
							continue;
						}
						// Get price
						$product_price = get_option( 'woocommerce_tax_display_cart' ) == 'excl' ? $_product->get_price_excluding_tax() : $_product->get_price_including_tax();
						?>
						<li>
							<div class="edgtf-item-image-holder">
								<a itemprop="url" href="<?php echo esc_url(get_permalink( $cart_item['product_id'] )); ?>">
									<?php echo wp_kses($_product->get_image(), array(
										'img' => array(
										'src' => true,
										'width' => true,
										'height' => true,
										'class' => true,
										'alt' => true,
										'title' => true,
										'id' => true
										)
									)); ?>
								</a>
							</div>
							<div class="edgtf-item-info-holder">
								<h5 itemprop="name" class="edgtf-product-title"><a itemprop="url" href="<?php echo esc_url(get_permalink( $cart_item['product_id'] )); ?>"><?php echo apply_filters('walker_edge_woo_widget_cart_product_title', $_product->get_title(), $_product ); ?></a></h5>
								<span class="edgtf-quantity"><?php esc_html_e('Quantity: ','walker'); echo esc_html($cart_item['quantity']); ?></span>
								<?php echo apply_filters( 'walker_edge_woo_cart_item_price_html', wc_price( $product_price ), $cart_item, $cart_item_key ); ?>
								<?php echo apply_filters( 'walker_edge_woo_cart_item_remove_link', sprintf('<a href="%s" class="remove" title="%s"><span class="ion-ios-close-empty"></span></a>', esc_url( $woocommerce->cart->get_remove_url( $cart_item_key ) ), esc_html__('Remove this item', 'walker') ), $cart_item_key ); ?>
							</div>
						</li>
					<?php endforeach; ?>
					<div class="edgtf-cart-bottom">
						<div class="edgtf-subtotal-holder clearfix">
							<span class="edgtf-total"><?php esc_html_e( 'ORDER TOTAL:', 'walker' ); ?></span>
							<span class="edgtf-total-amount">
								<?php echo wp_kses($woocommerce->cart->get_cart_subtotal(), array(
									'span' => array(
									'class' => true,
									'id' => true
									)
								)); ?>
							</span>
						</div>
						<?php if(!empty($cart_description)) { ?>
							<div class="edgtf-cart-description">
								<div class="edgtf-cart-description-inner">
									<span><?php echo esc_html($cart_description); ?></span>
								</div>		
							</div>
						<?php } ?>	
						<div class="edgtf-btn-holder clearfix">
							<a itemprop="url" href="<?php echo esc_url($woocommerce->cart->get_cart_url()); ?>" class="edgtf-view-cart" data-title="<?php esc_html_e('VIEW BAG & CHECKOUT','walker'); ?>"><span><?php esc_html_e('VIEW BAG & CHECKOUT','walker'); ?></span></a>
						</div>
					</div>
				</ul>
			</div>
		<?php else : ?>
			<div class="edgtf-shopping-cart-dropdown">
				<ul>
					<li class="edgtf-empty-cart"><?php esc_html_e( 'No products in the cart.', 'walker' ); ?></li>
				</ul>
			</div>		
		<?php endif; ?>
	</div>

	<?php
	$fragments['div.edgtf-shopping-cart-inner'] = ob_get_clean();

	return $fragments;
}
?>