<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <?php
    /**
     * @see walker_edge_header_meta() - hooked with 10
     * @see edgt_user_scalable - hooked with 10
     */
    do_action('walker_edge_header_meta');

	wp_head(); ?>
</head>
<body <?php body_class();?> itemscope itemtype="http://schema.org/WebPage">
<?php walker_edge_get_side_area(); ?>
<?php 
if(walker_edge_options()->getOptionValue('smooth_page_transitions') == "yes") {
    $ajax_class = 'edgtf-mimic-ajax';
?>
<div class="edgtf-smooth-transition-loader <?php echo esc_attr($ajax_class); ?>">
    <div class="edgtf-st-loader">
        <div class="edgtf-st-loader1">
            <?php walker_edge_loading_spinners(); ?>
        </div>
    </div>
</div>
<?php } ?>

<div class="edgtf-wrapper">
    <div class="edgtf-wrapper-inner">
        <?php walker_edge_get_header(); ?>

        <?php if (walker_edge_options()->getOptionValue('show_back_button') == "yes") { ?>
            <a id='edgtf-back-to-top'  href='#'>
                <span class="edgtf-icon-stack">
                     <?php walker_edge_icon_collections()->getBackToTopIcon('font_awesome');?>
                </span>
            </a>
        <?php } ?>
        <?php walker_edge_get_full_screen_menu(); ?>
        <div class="edgtf-content" <?php walker_edge_content_elem_style_attr(); ?>>
            <div class="edgtf-content-inner">