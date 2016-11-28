<?php 
/*
Template Name: WooCommerce
*/ 
?>
<?php

$id = get_option('woocommerce_shop_page_id');
$shop = get_post($id);
$sidebar = walker_edge_sidebar_layout();

if(get_post_meta($id, 'edgt_page_background_color', true) != ''){
	$background_color = 'background-color: '.esc_attr(get_post_meta($id, 'edgt_page_background_color', true));
}else{
	$background_color = '';
}

$disable_content_top_padding = get_post_meta(get_the_ID(), "edgtf_disable_page_content_top_padding_meta", true);
if($disable_content_top_padding === 'yes' && is_singular('product')) {
	$disable_content_top_padding = true;
} else {
	$disable_content_top_padding = false;
}

$content_style = '';
if(get_post_meta($id, 'edgtf_page_content_top_padding', true) !== '' && !$disable_content_top_padding) {
    if(get_post_meta($id, 'edgtf_page_content_top_padding_mobile', true) == 'yes') {
        $content_style = 'padding-top:'.esc_attr(get_post_meta($id, 'edgtf_page_content_top_padding', true)).'px !important';
    } else {
        $content_style = 'padding-top:'.esc_attr(get_post_meta($id, 'edgtf_page_content_top_padding', true)).'px';
    }
} else if ($disable_content_top_padding) {
	$content_style = 'padding-top: 0px';
}

if ( get_query_var('paged') ) {
	$paged = get_query_var('paged');
} elseif ( get_query_var('page') ) {
	$paged = get_query_var('page');
} else {
	$paged = 1;
}

$product_single_layout = walker_edge_get_meta_field_intersect('single_product_layout');

get_header();

walker_edge_get_title();
do_action('walker_edge_before_slider_action');
get_template_part('slider');
do_action('walker_edge_after_slider_action');

//Woocommerce content
if ( ! is_singular('product') ) { ?>
	<div class="edgtf-container" <?php walker_edge_inline_style($background_color); ?>>
		<div class="edgtf-container-inner clearfix" <?php walker_edge_inline_style($content_style); ?>>
			<?php
			switch( $sidebar ) {
				case 'sidebar-33-right': ?>
					<div class="edgtf-two-columns-66-33 edgtf-content-has-sidebar edgtf-woocommerce-with-sidebar clearfix">
						<div class="edgtf-column1">
							<div class="edgtf-column-inner">
								<?php walker_edge_woocommerce_content(); ?>
							</div>
						</div>
						<div class="edgtf-column2">
							<?php get_sidebar();?>
						</div>
					</div>
				<?php
					break;
				case 'sidebar-25-right': ?>
					<div class="edgtf-two-columns-75-25 edgtf-content-has-sidebar edgtf-woocommerce-with-sidebar clearfix">
						<div class="edgtf-column1 edgtf-content-left-from-sidebar">
							<div class="edgtf-column-inner">
								<?php walker_edge_woocommerce_content(); ?>
							</div>
						</div>
						<div class="edgtf-column2">
							<?php get_sidebar();?>
						</div>
					</div>
				<?php
					break;
				case 'sidebar-33-left': ?>
					<div class="edgtf-two-columns-33-66 edgtf-content-has-sidebar edgtf-woocommerce-with-sidebar clearfix">
						<div class="edgtf-column1">
							<?php get_sidebar();?>
						</div>
						<div class="edgtf-column2">
							<div class="edgtf-column-inner">
								<?php walker_edge_woocommerce_content(); ?>
							</div>
						</div>
					</div>
				<?php
					break;
				case 'sidebar-25-left': ?>
					<div class="edgtf-two-columns-25-75 edgtf-content-has-sidebar edgtf-woocommerce-with-sidebar clearfix">
						<div class="edgtf-column1">
							<?php get_sidebar();?>
						</div>
						<div class="edgtf-column2 edgtf-content-right-from-sidebar">
							<div class="edgtf-column-inner">
								<?php walker_edge_woocommerce_content(); ?>
							</div>
						</div>
					</div>
				<?php
					break;
				default:
					walker_edge_woocommerce_content();
			} ?>		
		</div>
	</div>			
<?php } else { ?>
	<?php if ($product_single_layout === 'full-width') { ?>
		<div class="edgtf-full-width" <?php walker_edge_inline_style($background_color); ?>>
			<div class="edgtf-full-width-inner" <?php walker_edge_inline_style($content_style); ?>>
	<?php } else { ?>
		<div class="edgtf-container" <?php walker_edge_inline_style($background_color); ?>>
			<div class="edgtf-container-inner clearfix" <?php walker_edge_inline_style($content_style); ?>>
	<?php } ?>
			<?php walker_edge_woocommerce_content(); ?>
		</div>
	</div>
<?php } ?>
<?php get_footer(); ?>