<?php
namespace WalkerEdgeNamespace\Modules\Shortcodes\SectionTitle;

use WalkerEdgeNamespace\Modules\Shortcodes\Lib\ShortcodeInterface;

/**
 * Class SectionTitle
 */
class SectionTitle implements ShortcodeInterface	{
	private $base; 
	
	function __construct() {
		$this->base = 'edgtf_section_title';

		add_action('vc_before_init', array($this, 'vcMap'));
	}
	
	/**
		* Returns base for shortcode
		* @return string
	 */
	public function getBase() {
		return $this->base;
	}	
	public function vcMap() {
						
		vc_map( array(
			'name' => esc_html__('Edge Section Title', 'walker'),
			'base' => $this->base,
			'category' => 'by EDGE',
			'icon' => 'icon-wpb-section-title extended-custom-icon',
			'allowed_container_element' => 'vc_row',
			'params' =>	array(
                array(
                    'type' => 'textfield',
                    'heading' => 'Title',
                    'param_name' => 'title',
                    'admin_label' => true,
                    'description' => ''
                ),
                array(
                    "type" => "dropdown",
                    "heading" => "Title Tag",
                    "param_name" => "title_tag",
                    "value" => array(
                        "h2" => "h2",
                        "h3" => "h3",
                        "h4" => "h4",
                        "h5" => "h5",
                        "h6" => "h6"
                    ),
                    'save_always' => true,
					'dependency' => array('element' => 'title', 'not_empty' => true)
                ),
                array(
                    'type'        => 'dropdown',
                    'heading'     => 'Title Position',
                    'param_name'  => 'title_position',
                    'value'       => array(
                        'Center'  => 'center',
                        'Left'    => 'left'
                    ),
                    'description' => '',
                    'save_always' => true,
                    'dependency' => array('element' => 'title', 'not_empty' => true)
                ),
                array(
                    "type" => "colorpicker",
                    "heading" => "Title Color",
                    "param_name" => "title_color",
                    "description" => "",
					'dependency' => array('element' => 'title', 'not_empty' => true)
                ),
                array(
                    'type' => 'textfield',
                    'heading' => 'Top Margin (px)',
                    'param_name' => 'top_margin',
                    'value' => '',
                    'description' => '',
					'dependency' => array('element' => 'title', 'not_empty' => true)
                ),
                array(
                    'type' => 'textfield',
                    'heading' => 'Bottom Margin (px)',
                    'param_name' => 'bottom_margin',
                    'value' => '',
					'dependency' => array('element' => 'title', 'not_empty' => true)
                ),
				array(
                    "type" => "colorpicker",
                    "heading" => "Separator Color",
                    "param_name" => "separator_color",
                    "description" => "",
					'dependency' => array('element' => 'title', 'not_empty' => true)
                )
                
            )
		) );

	}

	public function render($atts, $content = null) {
		
		$args = array(
            'title' => '',
            'title_tag' => 'h2',
            'title_position' => 'center',
            'title_color' => '',
            'top_margin' => '',
            'bottom_margin' => '',
			'separator_color' => ''
        );

		$params = shortcode_atts($args, $atts);
		
		$tag_array = array('h2', 'h3', 'h4', 'h5', 'h6');
        $params['title_tag'] = (in_array($params['title_tag'], $tag_array)) ? $params['title_tag'] : $args['title_tag'];

        $params['title_position_class'] = $this->getAlignmentClass($params);
		$params['holder_styles'] = $this->getHolderStyles($params);
        $params['title_styles'] = $this->getTitleStyles($params);
        $params['separator_styles'] = $this->getSeparatorStyles($params);
		
		//Get HTML from template based on section title
		$html = walker_edge_get_shortcode_module_template_part('templates/section-title', 'section-title', '', $params);
		
		return $html;
	}

    /**
     * Generates Alignment class
     *
     * @param $params
     *
     * @return string
     */
    private function getAlignmentClass($params){
       return ($params['title_position'] == 'left') ? 'edgtf-st-title-position-left' : 'edgtf-st-title-position-center';
    }

	/**
     * Return Style for Holder
     *
     * @param $params
     * @return string
     */
    private function getHolderStyles($params) {
        $holder_styles = array();

        if ($params['top_margin'] !== '') {
            $holder_styles[] = 'margin-top: '.walker_edge_filter_px($params['top_margin']).'px';
        }

        if ($params['bottom_margin'] !== '') {
            $holder_styles[] = 'margin-bottom: '.walker_edge_filter_px($params['bottom_margin']).'px';
        }

        return implode(';', $holder_styles);
    }
	
    /**
     * Return Style for Title
     *
     * @param $params
     * @return string
     */
    private function getTitleStyles($params) {
        $title_styles = array();
		
        if ($params['title_color'] !== '') {
            $title_styles[] = 'color: '.$params['title_color'];
        }

		return implode(';', $title_styles);
    }

    /**
     * Return Title Tag. If provided heading isn't valid get the default one
     *
     * @param $params
     * @return string
     */
    private function getTitleTag($params,$args) {
        $tag_array = array('h2', 'h3', 'h4', 'h5', 'h6');
        return (in_array($params['title_tag'], $tag_array)) ? $params['title_tag'] : $args['title_tag'];
    }

    private function getSeparatorStyles($params) {
        $separator_styles = array();
		
        if ($params['separator_color'] !== '') {
            $separator_styles[] = 'background-color: '.$params['separator_color'];
        }

		return implode(';', $separator_styles);
    }

}