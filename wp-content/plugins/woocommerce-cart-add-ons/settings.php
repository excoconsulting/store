<?php
global $wpdb, $woocommerce;
$settings           = get_option( 'sfn_cart_addons', array() );
$terms              = get_terms( 'product_cat', array('hide_empty' => false) );
$categories         = array();
$category_addons    = get_option( 'sfn_cart_addons_categories', array() );
$product_addons     = get_option( 'sfn_cart_addons_products', array() );

foreach ( $terms as $term ) {
    $used = false;
    foreach ( $category_addons as $c_addon ) {
        if ( $c_addon['category_id'] == $term->term_id ) {
            $used = true;
            break;
        }
    }

    if ( !$used ) {
        $categories[] = array('id' => $term->term_id, 'name' => $term->name);
    }
}
?>
<div class="wrap woocommerce">
	<div id="icon-edit" class="icon32 icon32-posts-product"><br></div>
    <h2><?php _e('Cart Add-Ons', 'sfn_cart_addons'); ?></h2>

    <?php if (isset($_GET['updated'])): ?>
    <div id="message" class="updated"><p><?php _e('Settings updated', 'sfn_cart_addons'); ?></p></div>
    <?php endif; ?>

    <form action="admin-post.php" method="post">
        <table class="form-table">
            <tbody>
                <tr valign="top">
                    <th scope="row">
                        <label for="heading"><?php _e('Display Title', 'sfn_cart_addons'); ?></label>
                    </th>
                    <td>
                        <?php $settings['header_title'] = isset( $settings['header_title'] ) ? $settings['header_title'] : ''; ?>
                        <input type="text" name="header_title" id="heading" value="<?php echo esc_attr($settings['header_title']); ?>" class="regular-text" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="number"><?php _e('Maximum number of upsells to show in the cart.', 'sfn_cart_addons'); ?></label>
                    </th>
                    <td>
                        <?php $settings['upsell_number'] = isset( $settings['upsell_number'] ) ? $settings['upsell_number'] : ''; ?>
                        <input type="text" name="upsell_number" id="number" value="<?php echo esc_attr($settings['upsell_number']); ?>" class="small-text" placeholder="6" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="default_products"><?php _e('Default Add-Ons', 'sfn_cart_addons'); ?></label>
                    </th>
                    <td>
                        <?php if ( version_compare( WOOCOMMERCE_VERSION, '2.3', '<' ) ): ?>
                        <select id="default_products" name="default_products[]" class="sfn-product-search" multiple data-placeholder="<?php _e('Search for a product&hellip;', 'woocommerce'); ?>" style="width: 600px">
                        <?php if ( !empty($settings['default_addons'])) foreach ( $settings['default_addons'] as $product_id ): ?>
                            <option value="<?php echo esc_attr($product_id); ?>" selected><?php echo strip_tags(get_the_title($product_id) .' - #'. $product_id); ?></option>
                        <?php endforeach; ?>
                        </select>
                        <?php
                        else:
                            $product_ids = array_filter( array_map( 'absint', $settings['default_addons'] ) );
                            $json_ids    = array();

                            foreach ( $product_ids as $product_id ) {
                                $product = wc_get_product( $product_id );

                                if ( $product && $product->exists() )
                                    $json_ids[ $product_id ] = wp_kses_post( $product->get_title() );
                            }
                        ?>
                            <input
                                type="hidden"
                                data-multiple="true"
                                id="default_products"
                                name="default_products[]"
                                class="sfn-product-search"
                                data-placeholder="<?php _e('Search for a product&hellip;', 'woocommerce'); ?>"
                                style="width: 600px"
                                value="<?php echo implode( ',', array_keys( $json_ids ) ); ?>"
                                data-selected="<?php echo esc_attr( json_encode( $json_ids ) ); ?>"
                            >
                        <?php
                        endif;
                        ?>
                        <p class="description">
                            <?php _e('These products will be displayed on the cart page if there are no matching products and/or categories in the shopping cart from the settings below.', 'sfn_cart_addons'); ?>
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>

        <h4><?php _e('Category Matches', 'sfn_cart_addons'); ?></h4>
        <p class="description">
            <?php _e('If a product in the shopping cart matches a category defined below, the cart upsells will display the matching products to show. Set the priority order to define which category upsells should be shown when items in the shopping cart match multiple categories. Categories with the highest priority will be the upsells that are displayed when there are multiple category matches in the cart.', 'sfn_cart_addons'); ?>
        </p>

        <table class="wp-list-table widefat fixed posts">
            <thead>
                <tr>
                    <th width="50" scope="col" id="priority" class="manage-column column-usage_count" style=""><?php _e('Priority', 'sfn_cart_addons'); ?></th>
                    <th width="20%" scope="col" id="category" class="manage-column column-type" style=""><?php _e('Category', 'sfn_cart_addons'); ?></th>
                    <th scope="col" id="products" class="manage-column column-products" style=""><?php _e('Product Add-Ons', 'sfn_cart_addons'); ?></th>
                    <th width="10%" scope="col">&nbsp;</th>
                </tr>
            </thead>
            <tbody id="cat_tbody">
                <?php
                if ( !empty($category_addons) ):
                    $p = 0;
                    foreach ( $category_addons as $x => $addons ):
                        $p++;
                        $category   = get_term( $addons['category_id'], 'product_cat' );
                ?>
                <tr scope="row">
                    <td style="text-align: center; vertical-align:middle;">
                        <span class="priority"><?php echo $p; ?></span>
                        <input type="hidden" name="category_priorities[]" value="<?php echo $x; ?>" size="3" />
                    </td>
                    <td class="post-title column-title">
                        <strong><?php echo stripslashes($category->name); ?></strong>
                        <input type="hidden" name="category[<?php echo $x; ?>]" value="<?php echo $addons['category_id']; ?>" />
                    </td>
                    <td>
                        <?php if ( version_compare( WOOCOMMERCE_VERSION, '2.3', '<' ) ): ?>
                        <select id="cselect_<?php echo $x; ?>" name="category_products[<?php echo $x; ?>][]" class="sfn-product-search" multiple data-placeholder="<?php _e('Search for a product&hellip;', 'woocommerce'); ?>" style="width: 95%">
                        <?php foreach ($addons['products'] as $product_id): ?>
                            <option value="<?php echo $product_id; ?>" selected><?php echo strip_tags(get_the_title($product_id) .' - #'. $product_id); ?></option>
                        <?php endforeach; ?>
                        </select>
                        <?php else:
                            $product_ids = array_filter( array_map( 'absint', $addons['products'] ) );
                            $json_ids    = array();

                            foreach ( $product_ids as $product_id ) {
                                $product = wc_get_product( $product_id );

                                if ( $product && $product->exists() )
                                    $json_ids[ $product_id ] = wp_kses_post( $product->get_title() );
                            }
                            ?>
                            <input
                                type="hidden"
                                data-multiple="true"
                                id="cselect_<?php echo $x; ?>"
                                name="category_products[<?php echo $x; ?>][]"
                                class="sfn-product-search"
                                data-placeholder="<?php _e('Search for a product&hellip;', 'woocommerce'); ?>"
                                style="width: 400px"
                                value="<?php echo implode( ',', array_keys( $json_ids ) ); ?>"
                                data-selected="<?php echo esc_attr( json_encode( $json_ids ) ); ?>"
                            >
                        <?php endif; ?>
                    </td>
                    <td align="center">
                        <a class="remove button" href="#" title="<?php _e('Remove Row', 'sfn_cart_addons'); ?>">&times;</a>
                    </td>
                </tr>
                <?php
                    endforeach;
                else:
                ?>
                <tr scope="row" id="no_categories">
                    <td colspan="4" align="center"><?php _e('No add-ons defined', 'sfn_cart_addons'); ?></td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="fue_table_footer">
            <div class="order_message"></div>
        </div>
        <br />
        <button type="button" id="add_category" class="button"><?php _e('+ Add Category', 'sfn_cart_addons'); ?></button>

        <h4><?php _e('Product Matches', 'sfn_cart_addons'); ?></h4>
        <p class="description">
            <?php _e('If a product in the shopping cart matches one of the products defined below, the cart upsells will display the matching products below to show. Set the priority to define which product upsells should be shown when items in the shopping cart are all defined. Products with the highest priority will be the upsells that are displayed when there are multiple products in the cart.', 'sfn_cart_addons'); ?>
        </p>

        <table class="wp-list-table widefat fixed posts">
            <thead>
                <tr>
                    <th width="50" scope="col" id="priority" class="manage-column column-usage_count" style=""><?php _e('Priority', 'sfn_cart_addons'); ?></th>
                    <th width="20%" scope="col" id="products" class="manage-column column-type" style=""><?php _e('Product', 'sfn_cart_addons'); ?></th>
                    <th scope="col" id="products" class="manage-column column-products" style=""><?php _e('Product Add-Ons', 'sfn_cart_addons'); ?></th>
                    <th width="10%" scope="col">&nbsp;</th>
                </tr>
            </thead>
            <tbody id="product_tbody">
                <?php
                if ( !empty($product_addons) ):
                    $p = 0;
                    foreach ( $product_addons as $x => $addons ):
                        $product    = sfn_get_product( $addons['product_id'] );

                        if ( !$product ) {
                            continue;
                        }

                        $p++;
                ?>
                <tr scope="row">
                    <td style="text-align: center; vertical-align:middle;">
                        <span class="priority"><?php echo $p; ?></span>
                        <input type="hidden" name="product_priorities[]" value="<?php echo $x; ?>" size="3" />
                    </td>
                    <td class="post-title column-title">
                        <strong><?php echo get_the_title( $addons['product_id'] ); ?></strong>
                        <input type="hidden" name="product[<?php echo $x; ?>]" value="<?php echo $addons['product_id']; ?>" />

                        <select name="product[<?php echo $x; ?>]" class="product-select" style="display:none;">
                            <option value="<?php echo $addons['product_id']; ?>" selected><?php echo $addons['product_id']; ?></option>
                        </select>

                        <?php
                        $addon_product = function_exists('wc_get_product' ) ? wc_get_product( $addons['product_id'] ) : new WC_Product( $addons['product_id'] );
                        $display = $addon_product->is_type('variable') ? 'block' : 'none';
                        ?>
                        <label style="display:<?php echo $display; ?>;">
                            <input type="checkbox" id="product_include_variations_{number}" name="product_include_variations[<?php echo $x; ?>]" value="1" <?php checked( 1, @$addons['include_variations'] ); ?> />
                            <?php _e('include variations', 'sfn_cart_addons'); ?>
                        </label>
                    </td>
                    <td>
                        <?php if ( version_compare( WOOCOMMERCE_VERSION, '2.3', '<' ) ): ?>
                            <select id="pselect_<?php echo $x; ?>" name="product_products[<?php echo $x; ?>][]" class="sfn-product-search" multiple data-placeholder="<?php _e('Search for a product&hellip;', 'woocommerce'); ?>" style="width: 95%">
                            <?php foreach ($addons['products'] as $product_id): ?>
                                <option value="<?php echo $product_id; ?>" selected><?php echo strip_tags(get_the_title($product_id) .' - #'. $product_id); ?></option>
                            <?php endforeach; ?>
                            </select>
                        <?php else:
                            $product_ids = array_filter( array_map( 'absint', $addons['products'] ) );
                            $json_ids    = array();

                            foreach ( $product_ids as $product_id ) {
                                $product = wc_get_product( $product_id );

                                if ( $product ) {
                                    $json_ids[ $product_id ] = wp_kses_post( get_the_title( $product_id ) );
                                }
                            }
                            ?>
                            <input
                                type="hidden"
                                data-multiple="true"
                                id="pselect_<?php echo $x; ?>"
                                name="product_products[<?php echo $x; ?>][]"
                                class="sfn-product-search"
                                data-placeholder="<?php _e('Search for a product&hellip;', 'woocommerce'); ?>"
                                style="width: 95%"
                                value="<?php echo implode( ',', array_keys( $json_ids ) ); ?>"
                                data-selected="<?php echo esc_attr( json_encode( $json_ids ) ); ?>"
                            >
                        <?php endif; ?>
                    </td>
                    <td align="center">
                        <a class="remove" href="#"><?php _e('remove row', 'sfn_cart_addons'); ?></a>
                    </td>
                </tr>
                <?php
                    endforeach;
                else:
                ?>
                <tr scope="row" id="no_products">
                    <td colspan="4" align="center"><?php _e('No add-ons defined', 'sfn_cart_addons'); ?></td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="fue_table_footer">
            <div class="order_message"></div>
        </div>
        <br />
        <button type="button" id="add_product" class="button"><?php _e('+ Add Product', 'sfn_cart_addons'); ?></button>

        <p class="submit">
            <input type="hidden" name="action" value="sfn_cart_addons_update_settings" />
            <input type="submit" name="save" value="<?php _e('Update Settings', 'sfn_cart_addons'); ?>" class="button-primary" />
        </p>

        <h4><?php _e('Use shortcodes on any page or post', 'sfn_cart_addons'); ?></h4>
        <p>[display-addons length=5 mode=loop] - Will use your theme's template<br />[display-addons length=4 mode=images_name] - Shows the product image along with the product name<br />[display-addons length=8 mode=images_name_price] - Will display product images with name and price
        </p>

        <h4><?php _e('Use directly in your theme', 'sfn_cart_addons'); ?></h4>
        <p>&lt;?php if ( function_exists('sfn_display_cart_addons') ) sfn_display_cart_addons($num, $display); ?&gt;<br />$num = the maximum number of add-ons to display, and $display can be one of the following: 'loop', 'images', 'images_name', 'images_name_price', 'names', 'names_price'
        </p>

    </form>
</div>
<table id="category_form_template" style="display: none;">
    <tbody>
    <tr scope="row">
        <td>
            <input type="hidden" name="category_priorities[]" value="{number}" size="3" />
        </td>
        <td class="post-title column-title">
            <select name="category[{number}]" id="category_{number}" class="category-select"></select>
        </td>
        <td>
            <?php if ( version_compare( WOOCOMMERCE_VERSION, '2.3', '<' ) ): ?>
                <select id="cselect_{number}" name="category_products[{number}][]" class="sfn-product-search-tpl" multiple data-placeholder="<?php _e('Select a category &hellip;', 'sfn_cart_addons'); ?>" style="width: 95%"></select>
            <?php else: ?>
                <input type="hidden" id="cselect_{number}" name="category_products[{number}][]" class="sfn-product-search-tpl" data-multiple="true" data-placeholder="<?php _e('Search for a product &hellip;', 'sfn_cart_addons'); ?>" style="width: 95%"></select>
            <?php endif; ?>
        </td>
        <td align="center">
            <a class="remove" href="#"><?php _e('remove row', 'sfn_cart_addons'); ?></a>
        </td>
    </tr>
    </tbody>
</table>

<table id="product_form_template" style="display: none;">
    <tbody>
    <tr scope="row">
        <td>
            <input type="hidden" name="product_priorities[]" value="{number}" size="3" />
        </td>
        <td class="post-title column-title">
            <?php if ( version_compare( WOOCOMMERCE_VERSION, '2.3', '<' ) ): ?>
                <select id="product_{number}" name="product[{number}]" class="sfn-product-search-tpl product-select" data-placeholder="<?php _e('Search for a product &hellip;', 'sfn_cart_addons'); ?>" style="width: 95%"></select>
            <?php else: ?>
                <input type="hidden" id="product_{number}" name="product[{number}]" class="sfn-product-search-tpl product-select" data-placeholder="<?php _e('Search for a product &hellip;', 'sfn_cart_addons'); ?>" style="width: 95%"></select>
            <?php endif; ?>
            <label class="include-variations-label" style="display: none;">
                <input type="checkbox" id="product_include_variations_{number}" name="product_include_variations[{number}]" value="1" />
                <?php _e('include variations', 'sfn_cart_addons'); ?>
            </label>
        </td>
        <td>
            <?php if ( version_compare( WOOCOMMERCE_VERSION, '2.3', '<' ) ): ?>
            <select id="pselect_{number}" name="product_products[{number}][]" class="sfn-product-search-tpl" multiple data-placeholder="<?php _e('Search for a product &hellip;', 'sfn_cart_addons'); ?>" style="width: 95%"></select>
            <?php else: ?>
            <input type="hidden" id="pselect_{number}" name="product_products[{number}][]" class="sfn-product-search-tpl" data-multiple="true" data-placeholder="<?php _e('Search for a product &hellip;', 'sfn_cart_addons'); ?>" style="width: 95%"></select>
            <?php endif; ?>
        </td>
        <td align="center">
            <a class="remove" href="#"><?php _e('remove row', 'sfn_cart_addons'); ?></a>
        </td>
    </tr>
    </tbody>
</table>

<script type="text/javascript">
    var store_categories = <?php echo json_encode($categories); ?>;
</script>
