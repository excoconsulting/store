<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class WAPL_Condition.
 *
 * Create a condition rule.
 *
 * @class       WAPL_Condition
 * @author     	Jeroen Sormani
 * @package		WooCommerce Advanced Product Labels
 * @version		1.0.0
 */
class WAPL_Condition {

	public $condition;
	public $operator;
	public $value;
	public $group;
	public $id;


	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct( $id = null, $group = 0, $condition = null, $operator = null, $value = null ) {

		 $this->id			= $id;
		 $this->group 		= $group;
		 $this->condition 	= $condition;
		 $this->operator 	= $operator;
		 $this->value 		= $value;

		 if ( ! $id )
		 	$this->id = rand();

		 $this->create_object();

	}


	/**
	 * Create condition.
	 *
	 * Created a condition rule object
	 *
	 * @since 1.0.0
	 */
	public function create_object() {

		?><div class='wapl-condition-wrap'><?php

			do_action( 'wapl_before_condition' );

			$this->condition_conditions();
			$this->condition_operator();
			$this->condition_values();

			$this->add_condition_button();
			$this->remove_condition_button();

			$this->condition_description();

			do_action( 'wapl_after_condition' );

		?></div><?php

	}


	/**
	 * Condition dropdown.
	 *
	 * Load and output condition dropdown.
	 *
	 * @since 1.0.0
	 */
	public function condition_conditions() {

		wapl_condition_conditions( $this->id, $this->group, $this->condition );

	}


	/**
	 * Operator dropdown.
	 *
	 * Load and ouput operator dropdown.
	 *
	 * @since 1.0.0
	 */
	public function condition_operator() {

		wapl_condition_operator( $this->id, $this->group, $this->operator );

	}


	/**
	 * Value dropdown.
	 *
	 * Load and ouput value dropdown.
	 *
	 * @since 1.0.0
	 */
	public function condition_values() {

		wapl_condition_values( $this->id, $this->group, $this->condition, $this->value );

	}


	/**
	 * Add button.
	 *
	 * Output add condition button.
	 *
	 * @since 1.0.0
	 */
	public function add_condition_button() {

		?> <a class='button condition-add' data-group='<?php echo $this->group; ?>' href='javascript:void(0);'>+</a><?php
	}


	/**
	 * Remove button.
	 *
	 * Output remove button.
	 *
	 * @since 1.0.0
	 */
	public function remove_condition_button() {

		?> <a class='button condition-delete' href='javascript:void(0);'>-</a><?php
	}


	/**
	 * Description.
	 *
	 * Output condition description.
	 *
	 * @since 1.0.0
	 */
	public function condition_description() {

		wapl_condition_description( $this->condition );

	}


}


/**
 * Load condition keys dropdown.
 */
require_once plugin_dir_path( __FILE__ ) . 'condition-conditions.php';

/**
 * Load condition operator dropdown.
 */
require_once plugin_dir_path( __FILE__ ) . 'condition-operators.php';

/**
 * Load condition value dropdown.
 */
require_once plugin_dir_path( __FILE__ ) . 'condition-values.php';

/**
 * Load condition descriptions.
 */
require_once plugin_dir_path( __FILE__ ) . 'condition-descriptions.php';
