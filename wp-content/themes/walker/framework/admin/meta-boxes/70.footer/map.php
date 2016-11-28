<?php

$footer_meta_box = walker_edge_add_meta_box(
    array(
        'scope' => array('page', 'portfolio-item', 'post'),
        'title' => 'Footer',
        'name' => 'footer_meta'
    )
);

    walker_edge_add_meta_box_field(
        array(
            'name' => 'edgtf_disable_footer_meta',
            'type' => 'yesno',
            'default_value' => 'no',
            'label' => 'Disable Footer for this Page',
            'description' => 'Enabling this option will hide footer on this page',
            'parent' => $footer_meta_box,
        )
    );

    walker_edge_add_meta_box_field(
        array(
            'name' => 'edgtf_set_footer_skin_meta',
            'type' => 'select',
            'default_value' => 'no',
            'label' => 'Footer Elements Skin',
            'description' => 'Choose a footer style to make footer elements in that predefined style',
            'parent' => $footer_meta_box,
            'options' => array(
                '' => 'Default',
                'light' => 'Light'
            ),
        )
    );