<?php
namespace EdgeCore\Lib;

/**
 * interface PostTypeInterface
 * @package EdgeCore\Lib;
 */
interface PostTypeInterface {
    /**
     * @return string
     */
    public function getBase();

    /**
     * Registers custom post type with WordPress
     */
    public function register();
}