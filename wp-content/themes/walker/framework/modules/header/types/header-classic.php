<?php
namespace WalkerEdgeNamespace\Modules\Header\Types;

use WalkerEdgeNamespace\Modules\Header\Lib\HeaderType;

/**
 * Class that represents Header Classic layout and option
 *
 * Class HeaderClassic
 */
class HeaderClassic extends HeaderType {
    protected $heightOfTransparency;
    protected $heightOfCompleteTransparency;
    protected $headerHeight;
    protected $mobileHeaderHeight;

    /**
     * Sets slug property which is the same as value of option in DB
     */
    public function __construct() {
        $this->slug = 'header-classic';

        if(!is_admin()) {

            $logoAreaHeight       = walker_edge_filter_px(walker_edge_options()->getOptionValue('logo_area_height_header_classic'));
            $this->logoAreaHeight = $logoAreaHeight !== '' ? walker_edge_filter_px($logoAreaHeight) : 118;

            $menuAreaHeight       = walker_edge_filter_px(walker_edge_options()->getOptionValue('menu_area_height_header_classic'));
            $this->menuAreaHeight = $menuAreaHeight !== '' ? intval($menuAreaHeight) : 70;

            $mobileHeaderHeight       = walker_edge_filter_px(walker_edge_options()->getOptionValue('mobile_header_height'));
            $this->mobileHeaderHeight = $mobileHeaderHeight !== '' ? intval($mobileHeaderHeight) : 80;

            add_action('wp', array($this, 'setHeaderHeightProps'));

            add_filter('walker_edge_js_global_variables', array($this, 'getGlobalJSVariables'));
            add_filter('walker_edge_per_page_js_vars', array($this, 'getPerPageJSVariables'));
        }
    }

    /**
     * Loads template file for this header type
     *
     * @param array $parameters associative array of variables that needs to passed to template
     */
    public function loadTemplate($parameters = array()) {

        $parameters = apply_filters('walker_edge_header_standard_parameters', $parameters);

        walker_edge_get_module_template_part('templates/types/'.$this->slug, $this->moduleName, '', $parameters);
    }

    /**
     * Sets header height properties after WP object is set up
     */
    public function setHeaderHeightProps(){
        $this->heightOfTransparency         = $this->calculateHeightOfTransparency();
        $this->heightOfCompleteTransparency = $this->calculateHeightOfCompleteTransparency();
        $this->headerHeight                 = $this->calculateHeaderHeight();
        $this->mobileHeaderHeight           = $this->calculateMobileHeaderHeight();
    }

    /**
     * Returns total height of transparent parts of header
     *
     * @return int
     */
    public function calculateHeightOfTransparency() {
        $id = walker_edge_get_page_id();
        $transparencyHeight = 0;

        if(get_post_meta($id, 'edgtf_menu_area_background_transparency_header_classic_meta', true) !== '1' && get_post_meta($id, 'edgtf_menu_area_background_transparency_header_classic_meta', true) !== ''){
            $menuAreaTransparent = true;
        } else if (walker_edge_options()->getOptionValue('menu_area_background_transparency_header_classic') !== '1' && walker_edge_options()->getOptionValue('menu_area_background_transparency_header_classic') !== '') {
            $menuAreaTransparent = true;
        } else {
            $menuAreaTransparent = false;
        }     

        if($menuAreaTransparent) {
            $transparencyHeight = $this->logoAreaHeight + $this->menuAreaHeight;

            if(walker_edge_is_top_bar_enabled() || walker_edge_is_top_bar_enabled() && walker_edge_is_top_bar_transparent()) {
                $transparencyHeight += walker_edge_get_top_bar_height();
            }
        }

        return $transparencyHeight;
    }

    /**
     * Returns height of completely transparent header parts
     *
     * @return int
     */
    public function calculateHeightOfCompleteTransparency() {
        $id = walker_edge_get_page_id();
        $transparencyHeight = 0;

        $menuAreaTransparent = walker_edge_options()->getOptionValue('fixed_header_transparency') === '0';

        if($menuAreaTransparent) {
            $transparencyHeight = $this->logoAreaHeight + $this->menuAreaHeight;
        }

        return $transparencyHeight;
    }

    /**
     * Returns total height of header
     *
     * @return int|string
     */
    public function calculateHeaderHeight() {
        $headerHeight = $this->logoAreaHeight + $this->menuAreaHeight;
        if(walker_edge_is_top_bar_enabled()) {
            $headerHeight += walker_edge_get_top_bar_height();
        }

        return $headerHeight;
    }

    /**
     * Returns total height of mobile header
     *
     * @return int|string
     */
    public function calculateMobileHeaderHeight() {
        $mobileHeaderHeight = $this->mobileHeaderHeight;

        return $mobileHeaderHeight;
    }

    /**
     * Returns global js variables of header
     *
     * @param $globalVariables
     * @return int|string
     */
    public function getGlobalJSVariables($globalVariables) {
        $globalVariables['edgtfLogoAreaHeight'] = $this->logoAreaHeight;
        $globalVariables['edgtfMenuAreaHeight'] = $this->headerHeight;
        $globalVariables['edgtfMobileHeaderHeight'] = $this->mobileHeaderHeight;

        return $globalVariables;
    }

    /**
     * Returns per page js variables of header
     *
     * @param $perPageVars
     * @return int|string
     */
    public function getPerPageJSVariables($perPageVars) {
        //calculate transparency height only if header has no sticky behaviour
        if(!in_array(walker_edge_options()->getOptionValue('header_behaviour'), array('sticky-header-on-scroll-up','sticky-header-on-scroll-down-up'))) {
            $perPageVars['edgtfHeaderTransparencyHeight'] = $this->headerHeight - (walker_edge_get_top_bar_height() + $this->heightOfCompleteTransparency);
        }else{
            $perPageVars['edgtfHeaderTransparencyHeight'] = 0;
        }

        return $perPageVars;
    }
}