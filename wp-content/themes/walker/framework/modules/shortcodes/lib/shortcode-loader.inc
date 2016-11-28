<?php
namespace WalkerEdgeNamespace\Modules\Shortcodes\Lib;

use WalkerEdgeNamespace\Modules\Shortcodes\Accordion\Accordion;
use WalkerEdgeNamespace\Modules\Shortcodes\AccordionTab\AccordionTab;
use WalkerEdgeNamespace\Modules\Shortcodes\AnimatedImage\AnimatedImage;
use WalkerEdgeNamespace\Modules\Shortcodes\AnimationHolder\AnimationHolder;
use WalkerEdgeNamespace\Modules\Shortcodes\BlogList\BlogList;
use WalkerEdgeNamespace\Modules\Shortcodes\Button\Button;
use WalkerEdgeNamespace\Modules\Shortcodes\CallToAction\CallToAction;
use WalkerEdgeNamespace\Modules\Shortcodes\Counter\Countdown;
use WalkerEdgeNamespace\Modules\Shortcodes\Counter\Counter;
use WalkerEdgeNamespace\Modules\Shortcodes\CustomFont\CustomFont;
use WalkerEdgeNamespace\Modules\Shortcodes\Dropcaps\Dropcaps;
use WalkerEdgeNamespace\Modules\Shortcodes\ElementsHolder\ElementsHolder;
use WalkerEdgeNamespace\Modules\Shortcodes\ElementsHolderItem\ElementsHolderItem;
use WalkerEdgeNamespace\Modules\Shortcodes\FrameSlider\FrameSlider;
use WalkerEdgeNamespace\Modules\Shortcodes\FrameSliderLeftPanel\FrameSliderLeftPanel;
use WalkerEdgeNamespace\Modules\Shortcodes\FrameSliderRightPanel\FrameSliderRightPanel;
use WalkerEdgeNamespace\Modules\Shortcodes\FrameSliderContentItem\FrameSliderContentItem;
use WalkerEdgeNamespace\Modules\Shortcodes\FrameSliderImage\FrameSliderImage;
use WalkerEdgeNamespace\Modules\Shortcodes\GoogleMap\GoogleMap;
use WalkerEdgeNamespace\Modules\Shortcodes\Highlight\Highlight;
use WalkerEdgeNamespace\Modules\Shortcodes\Icon\Icon;
use WalkerEdgeNamespace\Modules\Shortcodes\IconListItem\IconListItem;
use WalkerEdgeNamespace\Modules\Shortcodes\IconWithText\IconWithText;
use WalkerEdgeNamespace\Modules\Shortcodes\ImageGallery\ImageGallery;
use WalkerEdgeNamespace\Modules\Shortcodes\ImageWithText\ImageWithText;
use WalkerEdgeNamespace\Modules\Shortcodes\InteractiveBanner\InteractiveBanner;
use WalkerEdgeNamespace\Modules\Shortcodes\Message\Message;
use WalkerEdgeNamespace\Modules\Shortcodes\OrderedList\OrderedList;
use WalkerEdgeNamespace\Modules\Shortcodes\ParallaxSections\ParallaxSections;
use WalkerEdgeNamespace\Modules\Shortcodes\ParallaxSection\ParallaxSection;
use WalkerEdgeNamespace\Modules\Shortcodes\PieCharts\PieChartBasic\PieChartBasic;
use WalkerEdgeNamespace\Modules\Shortcodes\PricingTables\PricingTables;
use WalkerEdgeNamespace\Modules\Shortcodes\PricingTable\PricingTable;
use WalkerEdgeNamespace\Modules\Shortcodes\ProgressBar\ProgressBar;
use WalkerEdgeNamespace\Modules\Shortcodes\ProductList\ProductList;
use WalkerEdgeNamespace\Modules\Shortcodes\ProductListAnimated\ProductListAnimated;
use WalkerEdgeNamespace\Modules\Shortcodes\ProductListCarousel\ProductListCarousel;
use WalkerEdgeNamespace\Modules\Shortcodes\ProductListSimple\ProductListSimple;
use WalkerEdgeNamespace\Modules\Shortcodes\SectionTitle\SectionTitle;
use WalkerEdgeNamespace\Modules\Shortcodes\Separator\Separator;
use WalkerEdgeNamespace\Modules\Shortcodes\ShowcaseCarousel\ShowcaseCarousel;
use WalkerEdgeNamespace\Modules\Shortcodes\SocialButton\SocialButton;
use WalkerEdgeNamespace\Modules\Shortcodes\SocialShare\SocialShare;
use WalkerEdgeNamespace\Modules\Shortcodes\Tabs\Tabs;
use WalkerEdgeNamespace\Modules\Shortcodes\Tab\Tab;
use WalkerEdgeNamespace\Modules\Shortcodes\Team\Team;
use WalkerEdgeNamespace\Modules\Shortcodes\TeamCarousels\TeamCarousels;
use WalkerEdgeNamespace\Modules\Shortcodes\TeamCarousel\TeamCarousel;
use WalkerEdgeNamespace\Modules\Shortcodes\UnorderedList\UnorderedList;


/**
 * Class ShortcodeLoader
 */
class ShortcodeLoader {
	/**
	 * @var private instance of current class
	 */
	private static $instance;
	/**
	 * @var array
	 */
	private $loadedShortcodes = array();

	/**
	 * Private constuct because of Singletone
	 */
	private function __construct() {}

	/**
	 * Private sleep because of Singletone
	 */
	private function __wakeup() {}

	/**
	 * Private clone because of Singletone
	 */
	private function __clone() {}

	/**
	 * Returns current instance of class
	 * @return ShortcodeLoader
	 */
	public static function getInstance() {
		if(self::$instance == null) {
			return new self;
		}

		return self::$instance;
	}

	/**
	 * Adds new shortcode. Object that it takes must implement ShortcodeInterface
	 * @param ShortcodeInterface $shortcode
	 */
	private function addShortcode(ShortcodeInterface $shortcode) {
		if(!array_key_exists($shortcode->getBase(), $this->loadedShortcodes)) {
			$this->loadedShortcodes[$shortcode->getBase()] = $shortcode;
		}
	}

	/**
	 * Adds all shortcodes.
	 *
	 * @see ShortcodeLoader::addShortcode()
	 */
	private function addShortcodes() {
		$this->addShortcode(new Accordion());
		$this->addShortcode(new AccordionTab());
        $this->addShortcode(new AnimatedImage());
        $this->addShortcode(new AnimationHolder());
		$this->addShortcode(new BlogList());
		$this->addShortcode(new Button());
		$this->addShortcode(new CallToAction());
		$this->addShortcode(new Counter());
		$this->addShortcode(new Countdown());
		$this->addShortcode(new CustomFont());
		$this->addShortcode(new Dropcaps());
		$this->addShortcode(new ElementsHolder());
		$this->addShortcode(new ElementsHolderItem());
		$this->addShortcode(new FrameSlider());
		$this->addShortcode(new FrameSliderLeftPanel());
		$this->addShortcode(new FrameSliderRightPanel());
		$this->addShortcode(new FrameSliderContentItem());
		$this->addShortcode(new FrameSliderImage());
		$this->addShortcode(new GoogleMap());
		$this->addShortcode(new Highlight());
		$this->addShortcode(new Icon());
		$this->addShortcode(new IconListItem());
		$this->addShortcode(new IconWithText());
		$this->addShortcode(new ImageGallery());
		$this->addShortcode(new ImageWithText());
		$this->addShortcode(new InteractiveBanner());
		$this->addShortcode(new Message());
		$this->addShortcode(new OrderedList());
		$this->addShortcode(new ParallaxSections());
		$this->addShortcode(new ParallaxSection());
		$this->addShortcode(new PieChartBasic());
		$this->addShortcode(new PricingTables());
		$this->addShortcode(new PricingTable());
		if(walker_edge_is_woocommerce_installed()){
			$this->addShortcode(new ProductList());
			$this->addShortcode(new ProductListAnimated());		
			$this->addShortcode(new ProductListCarousel());
			$this->addShortcode(new ProductListSimple());
		}
		$this->addShortcode(new ProgressBar());
		$this->addShortcode(new SectionTitle());
		$this->addShortcode(new Separator());
		$this->addShortcode(new ShowcaseCarousel());
		$this->addShortcode(new SocialButton());
		$this->addShortcode(new SocialShare());
		$this->addShortcode(new Tabs());
		$this->addShortcode(new Tab());
		$this->addShortcode(new Team());
		$this->addShortcode(new TeamCarousels());
		$this->addShortcode(new TeamCarousel());
		$this->addShortcode(new UnorderedList());
	}
	/**
	 * Calls ShortcodeLoader::addShortcodes and than loops through added shortcodes and calls render method
	 * of each shortcode object
	 */
	public function load() {
		$this->addShortcodes();

		foreach ($this->loadedShortcodes as $shortcode) {
			add_shortcode($shortcode->getBase(), array($shortcode, 'render'));
		}
	}
}

$shortcodeLoader = ShortcodeLoader::getInstance();
$shortcodeLoader->load();