<?php

/**
 * Class WalkerEdgeClassPeIcons
 */
class WalkerEdgeClassPeIcons implements iWalkerEdgeInterfaceIconCollection {
    /**
     * @var array of all available icons
     */
    public $icons;
    /**
     * @var array of all social icons
     */
    public $socialIcons;
    /**
     * @var string title of icon collection
     */
    public $title;
    /**
     * @var string parameter that will be used in shortcodes
     */
    public $param;
    /**
     * @var string URL to css file of icon collection
     */
    public $styleUrl;

    /**
     * @param string $title title of icon collection
     * @param string $param param that will be used in shortcodes
     */
    public function __construct($title = "", $param = "") {
        $this->socialIcons = array();
        $this->title = $title;
        $this->param = $param;
        $this->setIconsArray();
        $this->setSocialIconsArray();
        $this->styleUrl = EDGE_ASSETS_ROOT . "/css/pe-icon-7-stroke/css/pe-icon-7-stroke.css";
    }

     
    /**
     * Sets icon property
     */
    private function setIconsArray() {
        $this->icons = array(
            'pe-7s-add-user' => '\e6a9', 
            'pe-7s-airplay' => '\e67f', 
            'pe-7s-alarm' => '\e67e', 
            'pe-7s-album' => '\e6aa', 
            'pe-7s-albums' => '\e67d', 
            'pe-7s-anchor' => '\e67c', 
            'pe-7s-angle-down' => '\e688', 
            'pe-7s-angle-down-circle' => '\e689', 
            'pe-7s-angle-left' => '\e686', 
            'pe-7s-angle-left-circle' => '\e687', 
            'pe-7s-angle-right' => '\e684', 
            'pe-7s-angle-right-circle' => '\e685', 
            'pe-7s-angle-up' => '\e682', 
            'pe-7s-angle-up-circle' => '\e683', 
            'pe-7s-arc' => '\e6ab', 
            'pe-7s-attention' => '\e67b', 
            'pe-7s-back' => '\e67a', 
            'pe-7s-back-2' => '\e6ac', 
            'pe-7s-ball' => '\e679', 
            'pe-7s-bandaid' => '\e6ad', 
            'pe-7s-battery' => '\e678', 
            'pe-7s-bell' => '\e677', 
            'pe-7s-bicycle' => '\e676', 
            'pe-7s-bluetooth' => '\e68d', 
            'pe-7s-bookmarks' => '\e675', 
            'pe-7s-bottom-arrow' => '\e6a8', 
            'pe-7s-box1' => '\e674', 
            'pe-7s-box2' => '\e673', 
            'pe-7s-browser' => '\e672', 
            'pe-7s-calculator' => '\e671', 
            'pe-7s-call' => '\e670', 
            'pe-7s-camera' => '\e66f', 
            'pe-7s-car' => '\e6ae', 
            'pe-7s-cart' => '\e66e', 
            'pe-7s-cash' => '\e68c', 
            'pe-7s-chat' => '\e66d', 
            'pe-7s-check' => '\e66c', 
            'pe-7s-clock' => '\e66b', 
            'pe-7s-close' => '\e680', 
            'pe-7s-close-circle' => '\e681', 
            'pe-7s-cloud' => '\e66a', 
            'pe-7s-cloud-download' => '\e68b', 
            'pe-7s-cloud-upload' => '\e68a', 
            'pe-7s-coffee' => '\e669', 
            'pe-7s-comment' => '\e668', 
            'pe-7s-compass' => '\e667', 
            'pe-7s-config' => '\e666', 
            'pe-7s-copy-file' => '\e665', 
            'pe-7s-credit' => '\e664', 
            'pe-7s-crop' => '\e663', 
            'pe-7s-culture' => '\e662', 
            'pe-7s-cup' => '\e661', 
            'pe-7s-date' => '\e660', 
            'pe-7s-delete-user' => '\e6a7', 
            'pe-7s-diamond' => '\e6af', 
            'pe-7s-disk' => '\e6a6', 
            'pe-7s-diskette' => '\e65f', 
            'pe-7s-display1' => '\e65e', 
            'pe-7s-display2' => '\e65d', 
            'pe-7s-door-lock' => '\e6b0', 
            'pe-7s-download' => '\e65c', 
            'pe-7s-drawer' => '\e65b', 
            'pe-7s-drop' => '\e65a', 
            'pe-7s-edit' => '\e659', 
            'pe-7s-exapnd2' => '\e658', 
            'pe-7s-expand1' => '\e657', 
            'pe-7s-eyedropper' => '\e6b1', 
            'pe-7s-female' => '\e6b2', 
            'pe-7s-file' => '\e656', 
            'pe-7s-film' => '\e6a5', 
            'pe-7s-filter' => '\e655', 
            'pe-7s-flag' => '\e654', 
            'pe-7s-folder' => '\e653', 
            'pe-7s-gift' => '\e652', 
            'pe-7s-glasses' => '\e651', 
            'pe-7s-gleam' => '\e650', 
            'pe-7s-global' => '\e64f', 
            'pe-7s-graph' => '\e64e', 
            'pe-7s-graph1' => '\e64d', 
            'pe-7s-graph2' => '\e64c', 
            'pe-7s-graph3' => '\e64b', 
            'pe-7s-gym' => '\e6b3', 
            'pe-7s-hammer' => '\e6b4', 
            'pe-7s-headphones' => '\e6b5', 
            'pe-7s-helm' => '\e6b6', 
            'pe-7s-help1' => '\e64a', 
            'pe-7s-help2' => '\e649', 
            'pe-7s-home' => '\e648', 
            'pe-7s-hourglass' => '\e6b7', 
            'pe-7s-id' => '\e68f', 
            'pe-7s-info' => '\e647', 
            'pe-7s-joy' => '\e6a4', 
            'pe-7s-junk' => '\e646', 
            'pe-7s-key' => '\e6a3', 
            'pe-7s-keypad' => '\e645', 
            'pe-7s-leaf' => '\e6b8', 
            'pe-7s-left-arrow' => '\e6a2', 
            'pe-7s-less' => '\e644', 
            'pe-7s-light' => '\e643', 
            'pe-7s-like' => '\e642', 
            'pe-7s-like2' => '\e6a1', 
            'pe-7s-link' => '\e641', 
            'pe-7s-lintern' => '\e640', 
            'pe-7s-lock' => '\e63f', 
            'pe-7s-look' => '\e63e', 
            'pe-7s-loop' => '\e63d', 
            'pe-7s-magic-wand' => '\e6b9', 
            'pe-7s-magnet' => '\e63c', 
            'pe-7s-mail' => '\e639', 
            'pe-7s-mail-open' => '\e63a', 
            'pe-7s-mail-open-file' => '\e63b', 
            'pe-7s-male' => '\e6ba', 
            'pe-7s-map' => '\e637', 
            'pe-7s-map-2' => '\e6bb', 
            'pe-7s-map-marker' => '\e638', 
            'pe-7s-medal' => '\e6a0', 
            'pe-7s-menu' => '\e636', 
            'pe-7s-micro' => '\e635', 
            'pe-7s-monitor' => '\e634', 
            'pe-7s-moon' => '\e633', 
            'pe-7s-more' => '\e632', 
            'pe-7s-mouse' => '\e631', 
            'pe-7s-music' => '\e630', 
            'pe-7s-musiclist' => '\e62f', 
            'pe-7s-mute' => '\e69f', 
            'pe-7s-network' => '\e69e', 
            'pe-7s-news-paper' => '\e62e', 
            'pe-7s-next' => '\e62d', 
            'pe-7s-next-2' => '\e6bc', 
            'pe-7s-note' => '\e62c', 
            'pe-7s-note2' => '\e69d', 
            'pe-7s-notebook' => '\e62b', 
            'pe-7s-paint' => '\e62a', 
            'pe-7s-paint-bucket' => '\e6bd', 
            'pe-7s-paper-plane' => '\e629', 
            'pe-7s-paperclip' => '\e69c', 
            'pe-7s-pen' => '\e628', 
            'pe-7s-pendrive' => '\e6be', 
            'pe-7s-phone' => '\e627', 
            'pe-7s-photo' => '\e6bf', 
            'pe-7s-photo-gallery' => '\e626', 
            'pe-7s-piggy' => '\e6c0', 
            'pe-7s-pin' => '\e69b', 
            'pe-7s-plane' => '\e625', 
            'pe-7s-play' => '\e624', 
            'pe-7s-plug' => '\e69a', 
            'pe-7s-plugin' => '\e6c1', 
            'pe-7s-plus' => '\e623', 
            'pe-7s-portfolio' => '\e622', 
            'pe-7s-power' => '\e621', 
            'pe-7s-prev' => '\e620', 
            'pe-7s-print' => '\e61f', 
            'pe-7s-radio' => '\e61e', 
            'pe-7s-refresh' => '\e61c', 
            'pe-7s-refresh-2' => '\e6c2', 
            'pe-7s-refresh-cloud' => '\e61d', 
            'pe-7s-repeat' => '\e61b', 
            'pe-7s-ribbon' => '\e61a', 
            'pe-7s-right-arrow' => '\e699', 
            'pe-7s-rocket' => '\e6c3', 
            'pe-7s-safe' => '\e698', 
            'pe-7s-science' => '\e619', 
            'pe-7s-scissors' => '\e697', 
            'pe-7s-search' => '\e618', 
            'pe-7s-server' => '\e617', 
            'pe-7s-settings' => '\e6c4', 
            'pe-7s-share' => '\e616', 
            'pe-7s-shield' => '\e6c5', 
            'pe-7s-shopbag' => '\e615', 
            'pe-7s-shuffle' => '\e614', 
            'pe-7s-signal' => '\e613', 
            'pe-7s-smile' => '\e6c6', 
            'pe-7s-speaker' => '\e612', 
            'pe-7s-star' => '\e611', 
            'pe-7s-stopwatch' => '\e610', 
            'pe-7s-study' => '\e60f', 
            'pe-7s-sun' => '\e60e', 
            'pe-7s-switch' => '\e696', 
            'pe-7s-target' => '\e60d', 
            'pe-7s-ticket' => '\e60c', 
            'pe-7s-timer' => '\e60b', 
            'pe-7s-tools' => '\e60a', 
            'pe-7s-trash' => '\e609', 
            'pe-7s-umbrella' => '\e608', 
            'pe-7s-unlock' => '\e607', 
            'pe-7s-up-arrow' => '\e695', 
            'pe-7s-upload' => '\e606', 
            'pe-7s-usb' => '\e6c7', 
            'pe-7s-user' => '\e605', 
            'pe-7s-user-female' => '\e694', 
            'pe-7s-users' => '\e693', 
            'pe-7s-vector' => '\e6c8', 
            'pe-7s-video' => '\e604', 
            'pe-7s-voicemail' => '\e603', 
            'pe-7s-volume' => '\e692', 
            'pe-7s-volume1' => '\e602', 
            'pe-7s-volume2' => '\e601', 
            'pe-7s-wallet' => '\e600', 
            'pe-7s-way' => '\e68e', 
            'pe-7s-wine' => '\e6c9', 
            'pe-7s-world' => '\e691', 
            'pe-7s-wristwatch' => '\e690'
        );

        $icons = array();
        $icons[""] = "";
        foreach ($this->icons as $key => $value) {
            $icons[$key] = $key;
        }

        $this->icons = $icons;
    }

    /**
     * Sets social icons property
     */
    private function setSocialIconsArray() {
        $this->socialIcons = array();
    }

    /**
     * Method that returns $icons property
     * @return mixed
     */
    public function getIconsArray() {
       return $this->icons;
    }

    /**
     * Generates HTML for provided icon and parameters
     * @param $icon string
     * @param array $params
     * @return mixed
     */
    public function render($icon, $params = array()) {
        $html = '';
        extract($params);
        $iconAttributesString = '';
        $iconClass = '';
        if (isset($icon_attributes) && count($icon_attributes)) {
            foreach ($icon_attributes as $icon_attr_name => $icon_attr_val) {
                if ($icon_attr_name === 'class') {
                    $iconClass = $icon_attr_val;
                    unset($icon_attributes[$icon_attr_name]);
                } else {
                    $iconAttributesString .= $icon_attr_name . '="' . $icon_attr_val . '" ';
                }
            }
        }

        if (isset($before_icon) && $before_icon !== '') {
            $beforeIconAttrString = '';
            if (isset($before_icon_attributes) && count($before_icon_attributes)) {
                foreach ($before_icon_attributes as $before_icon_attr_name => $before_icon_attr_val) {
                    $beforeIconAttrString .= $before_icon_attr_name . '="' . $before_icon_attr_val . '" ';
                }
            }

            $html .= '<' . $before_icon . ' ' . $beforeIconAttrString . '>';
        }

        $html .= '<i class="edgtf-icon-pe-7s-icon ' . $icon . ' ' . $iconClass . '" ' . $iconAttributesString . '></i>';

        if (isset($before_icon) && $before_icon !== '') {
            $html .= '</' . $before_icon . '>';
        }

        return $html;
    }

    /**
     * Checks if icon collection has social icons
     * @return mixed
     */
    public function hasSocialIcons() {
        return false;
    }

    /**
     * @return mixed
     */
    public function getSearchIcon() {

        return $this->render('pe-7s-search');
    }

    /**
     * @return mixed
     */
    public function getSearchClose() {

        return $this->render('pe-7s-close');
    }

    /**
     * @return mixed
     */
    public function getMenuSideIcon() {

        return $this->render('pe-7s-menu');
    }

    /**
     * @return mixed
     */
    public function getBackToTopIcon() {

        return $this->render('pe-7s-angle-up');
    }

    /**
     * @return mixed
     */
    public function getMobileMenuIcon() {

        return $this->render('pe-7s-menu');
    }
}