<?php
namespace EdgeCore\CPT;

use EdgeCore\Lib;

/**
 * Class PostTypesLoader
 * @package EdgeCore\CPT
 */
class PostTypesRegister {
    /**
     * @var private instance of the class
     */
    private static $instance;
    /**
     * @var array
     */
    private $postTypes = array();

    /**
     * Private construct because of Singletone
     */
    private function __construct() {}

    /**
     * Private wake up method because of Singletone
     */
    private function __wakeup() {}

    /**
     * Private clone method because of singletone
     */
    private function __clone() {}

    /**
     * Returns current instance of class
     * @return PostTypesRegister
     */
    public static function getInstance() {
        if(self::$instance == null) {
            return new self;
        }

        return self::$instance;
    }

    /**
     * Adds post type to post types array
     * @param Lib\PostTypeInterface $postType
     */
    private function addPostType(Lib\PostTypeInterface $postType) {
        if(!array_key_exists($postType->getBase(), $this->postTypes)) {
            $this->postTypes[$postType->getbase()] = $postType;
        }
    }

    /**
     * Returns post type object by it's slug
     * @param $slug
     * @return mixed
     */
    public function getPostType($slug) {
        if(array_key_exists($slug, $this->postTypes)) {
            return $this->postTypes[$slug];
        }
    }

    /**
     * Adds all post types
     *
     * @see PostTypesLoader::addPostType()
     */
    private function addPostTypes() {
        $this->addPostType(new Portfolio\PortfolioRegister());
        $this->addPostType(new Testimonials\TestimonialsRegister());
        $this->addPostType(new Carousels\CarouselRegister());
        $this->addPostType(new Slider\SliderRegister());
    }

    /**
     * Calss addPostTypes method, loops through each post type in array and calls register method
     */
    public function register() {
        $this->addPostTypes();

        foreach ($this->postTypes as $postType) {
            $postType->register();
        }
    }
}