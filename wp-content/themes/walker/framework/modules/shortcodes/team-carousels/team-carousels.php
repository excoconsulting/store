<?php
namespace WalkerEdgeNamespace\Modules\Shortcodes\TeamCarousels;

use WalkerEdgeNamespace\Modules\Shortcodes\Lib\ShortcodeInterface;
/**
 * Class TeamCarousels
 */
class TeamCarousels implements ShortcodeInterface {
	/**
	 * @var string
	 */
	private $base;

	public function __construct() {
		$this->base = 'edgtf_team_carousels';

		add_action('vc_before_init', array($this, 'vcMap'));
	}

	/**
	 * Returns base for shortcode
	 * @return string
	 */
	public function getBase() {
		return $this->base;
	}

	/**
	 * Maps shortcode to Visual Composer. Hooked on vc_before_init
	 *
	 * @see edgt_core_get_carousel_slider_array_vc()
	 */
	public function vcMap()	{

		vc_map( array(
			'name' => esc_html__('Edge Team Carousel', 'walker'),
			'base' => $this->getBase(),
			'as_parent' => array('only' => 'edgtf_team_carousel'),
			'content_element' => true,
			'show_settings_on_create' => true,
			'category' => 'by EDGE',
			'icon' => 'icon-wpb-team-carousels extended-custom-icon',
			'js_view' => 'VcColumnView',
			'params' => array (
                array(
                    'type'        => 'textfield',
                    'heading'     => 'Custom CSS class',
                    'param_name'  => 'custom_class',
                    'admin_label' => true
                ),
				array(
					'type' => 'dropdown',
					'admin-label' => true,
					'heading' => 'Number Of Visible Items',
					'param_name' => 'items',
					'value' => array(
						'One' => '1',
						'Two' => '2',
						'Three' => '3',
						'Four' => '4',
						'Five' => '5',
						'Six' => '6'
					),
					'save_always' => true,
					'description' => ''
				),
				array(
					'type' => 'textfield',
					'heading' => 'Carousel Speed',
					'admin_label' => true,
                    'value' => '5000',
					'param_name' => 'speed',
                    'save_always' => true,
					'description' => 'Default value is 5000. Value is in ms.'
				),
                array(
                    'type' => 'dropdown',
                    'heading' => 'Show navigation?',
                    'param_name' => 'show_navigation',
                    'value' => array(
                        'No' => '',
                        'Next/Prev' => 'next-prev',
                        'Paging' => 'paging',
                    ),
                    'admin_label' => true,
                    'description' => ''
                )
			)
		) );
	}

	/**
	 * Renders shortcodes HTML
	 *
	 * @param $atts array of shortcode params
	 * @param $content string shortcode content
	 * @return string
	 */
	public function render($atts, $content = null) {
		$args = array(
			'custom_class' 	 => '',
			'items' 	 => '4',
			'speed'      => '5000',
			'show_navigation' => '',
		);

		$params = shortcode_atts($args, $atts);

        $team_data = $this->getTeamCarouselDataAttributes($params);
        $team_holder_class = $this->getHolderClasses($params);

		extract($params);

		$html = '';

		$html .= '<div class="edgtf-team-carousel-holder '.esc_html($team_holder_class).'">';
			$html .= '<div class="edgtf-team-carousel-inner edgtf-grid" '.walker_edge_get_inline_attrs($team_data).'>';
				$html .= do_shortcode($content);
			$html .= '</div>';

		$html .= '</div>';

		return $html;
	}

    /**
     * Return all data that team carousel needs
     *
     * @param $params
     * @return array
     */
    private function getTeamCarouselDataAttributes($params) {

        $data_attr = array();

        if(!empty($params['show_navigation'])){
            $data_attr['data-navigation'] = $params['show_navigation'];
        }
        if(!empty($params['speed'])) {
            $data_attr['data-speed'] = $params['speed'];
        }
        if(!empty($params['items'])) {
            $data_attr['data-items'] = $params['items'];
        }

        return $data_attr;
    }

    /**
     * Returns array of holder classes
     *
     * @param $params
     *
     * @return array
     */
    private function getHolderClasses($params) {
        $classes = '';

        if(!empty($params['custom_class'])) {
            $classes .= $params['custom_class'];
        }

        return $classes;
    }
}