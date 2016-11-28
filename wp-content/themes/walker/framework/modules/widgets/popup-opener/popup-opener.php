<?php

/**
 * Widget that adds popup icon that triggers opening of popup form
 *
 * Class Edge_Popup_Opener
 */
class WalkerEdgeClassEdgefPopupOpener extends WalkerEdgeClassWidget {
    public function __construct() {
        parent::__construct(
            'edgtf_popup_opener', // Base ID
            'Edge Pop-up Opener' // Name
        );

        $this->setParams();
    }

    protected function setParams() {

        $this->params = array(
            array(
                'name'			=> 'popup_opener_text',
                'type'			=> 'textfield',
                'title'			=> 'Pop-up Opener Text',
                'description'	=> 'Enter text for pop-up opener'
            ),
            array(
                'name'			=> 'popup_opener_color',
                'type'			=> 'textfield',
                'title'			=> 'Pop-up Opener Color',
                'description'	=> 'Define color for pop-up opener'
            )
        );
    }

    public function widget($args, $instance) {

        $popup_styles = array();
        $popup_text = 'NEWSLETTER';

        if ( !empty($instance['popup_opener_color']) ) {
            $popup_styles[] = 'color: ' . $instance['popup_opener_color'];
        }
        if ( !empty($instance['popup_opener_text']) ) {
            $popup_text = $instance['popup_opener_text'];
        }
        ?>
        <a class="edgtf-popup-opener" <?php walker_edge_inline_style($popup_styles) ?> href="javascript:void(0)">
            <span class="edgtf-popup-opener-text"><?php echo esc_html($popup_text); ?></span>
        </a>
    <?php }
}