<?php
namespace WalkerEdgeNamespace\Modules\Shortcodes\Counter;

use WalkerEdgeNamespace\Modules\Shortcodes\Lib\ShortcodeInterface;
/**
 * Class Counter
 */
class Counter implements ShortcodeInterface {

	/**
	 * @var string
	 */
	private $base;

	public function __construct() {
		$this->base = 'edgtf_counter';

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
	public function vcMap() {

		vc_map( array(
			'name' => esc_html__('Edge Counter', 'walker'),
			'base' => $this->getBase(),
			'category' => 'by EDGE',
			'admin_enqueue_css' => array(walker_edge_get_skin_uri().'/assets/css/edgtf-vc-extend.css'),
			'icon' => 'icon-wpb-counter extended-custom-icon',
			'allowed_container_element' => 'vc_row',
			'params' => array(
				array(
					'type' => 'dropdown',
					'admin_label' => true,
					'heading' => 'Type',
					'param_name' => 'type',
					'value' => array(
						'Zero Counter' => 'zero',
						'Random Counter' => 'random'
					),
					'save_always' => true,
					'description' => ''
				),
				array(
					'type' => 'dropdown',
					'heading' => 'Position',
					'param_name' => 'position',
					'value' => array(
						'Left' => 'left',
						'Right' => 'right',
						'Center' => 'center'
					),
					'save_always' => true,
					'description' => ''
				),
				array(
					'type' => 'textfield',
					'admin_label' => true,
					'heading' => 'Digit',
					'param_name' => 'digit',
					'description' => ''
				),
				array(
					'type' => 'textfield',
					'heading' => 'Digit Font Size (px)',
					'param_name' => 'font_size',
					'description' => '',
					'group' => 'Design Options',
				),
                array(
					'type' => 'colorpicker',
                    'admin_label' => true,
                    'heading' => 'Digit color',
                    'param_name' => 'color',
                    'description' => '',
                    'group' => 'Design Options'
                ),
				array(
					'type' => 'textfield',
					'heading' => 'Title',
					'param_name' => 'title',
					'admin_label' => true,
					'description' => ''
				),
				array(
					'type' => 'dropdown',
					'heading' => 'Title Tag',
					'param_name' => 'title_tag',
					'value' => array(
						''   => '',
						'h2' => 'h2',
						'h3' => 'h3',
						'h4' => 'h4',
						'h5' => 'h5',
						'h6' => 'h6',
					),
					'description' => ''
				),
                array(
                    'type' => 'colorpicker',
                    'admin_label' => true,
                    'heading' => 'Title color',
                    'param_name' => 'title_color',
                    'description' => '',
                    'group' => 'Design Options'
                ),
				array(
					'type' => 'textfield',
					'heading' => 'Text',
					'param_name' => 'text',
					'admin_label' => true,
					'description' => ''
				),
                array(
                    'type' => 'colorpicker',
                    'admin_label' => true,
                    'heading' => 'Text color',
                    'param_name' => 'text_color',
                    'description' => '',
                    'group' => 'Design Options'
                ),
				array(
					'type' => 'textfield',
					'heading' => 'Padding Bottom(px)',
					'param_name' => 'padding_bottom',
					'description' => '',
					'group' => 'Design Options',
				),
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
			'type' => '',
			'position' => '',
			'digit' => '',
			'underline_digit' => '',
			'title' => '',
			'title_tag' => 'h4',
			'title_color' => '',
			'font_size' => '',
			'color' => '',
			'text' => '',
			'text_color' => '',
			'padding_bottom' => '',

		);

		$params = shortcode_atts($args, $atts);

		//get correct heading value. If provided heading isn't valid get the default one
		$headings_array = array('h2', 'h3', 'h4', 'h5', 'h6');
		$params['title_tag'] = (in_array($params['title_tag'], $headings_array)) ? $params['title_tag'] : $args['title_tag'];

		$params['counter_holder_styles'] = $this->getCounterHolderStyle($params);
		$params['counter_styles'] = $this->getCounterStyle($params);
		$params['counter_title_styles'] = $this->getCounterTitleStyle($params);
		$params['counter_text_styles'] = $this->getCounterTextStyle($params);

		//Get HTML from template
		$html = walker_edge_get_shortcode_module_template_part('templates/counter-template', 'counter', '', $params);

		return $html;

	}

	/**
	 * Return Counter holder styles
	 *
	 * @param $params
	 * @return string
	 */
	private function getCounterHolderStyle($params) {
		$counterHolderStyle = array();

		if ($params['padding_bottom'] !== '') {

			$counterHolderStyle[] = 'padding-bottom: ' . $params['padding_bottom'] . 'px';

		}

		return implode(';', $counterHolderStyle);
	}

	/**
	 * Return Counter styles
	 *
	 * @param $params
	 * @return string
	 */
	private function getCounterStyle($params) {
		$counterStyle = array();

		if ($params['font_size'] !== '') {
			$counterStyle[] = 'font-size: ' . $params['font_size'] . 'px';
		}

        if ($params['color'] !== '') {
            $counterStyle[] = 'color: ' . $params['color'];
        }

		return implode(';', $counterStyle);
	}

    private function getCounterTitleStyle($params) {
        $counterTitleStyle = array();

        if ($params['title_color'] !== '') {
            $counterTitleStyle[] = 'color: ' . $params['title_color'];
        }

        return implode(';', $counterTitleStyle);
    }

    private function getCounterTextStyle($params) {
        $counterTextStyle = array();

        if ($params['text_color'] !== '') {
            $counterTextStyle[] = 'color: ' . $params['text_color'];
        }

        return implode(';', $counterTextStyle);
    }

}