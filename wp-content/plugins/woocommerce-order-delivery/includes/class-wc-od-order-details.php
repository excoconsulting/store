<?php
/**
 * Class to handle the delivery date section in the order details and emails templates
 *
 * @author     WooThemes
 * @package    WC_OD
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_OD_Order_Details' ) ) {

	class WC_OD_Order_Details {

		/**
		 * The order ID.
		 *
		 * @since  1.0.0
		 * @access public
		 * @var int The order ID.
		 */
		public $order_id;


		/**
		 * Initializes the class.
		 *
		 * @since 1.0.0
		 * @staticvar WC_OD_Order_Details $instance The *Singleton* instances of this class.
		 * @return WC_OD_Order_Details The *Singleton* instance.
		 */
		public static function instance() {
			static $instance = null;
			if ( null === $instance ) {
				$instance = new self();
			}

			return $instance;
		}

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 * @access protected
		 */
		protected function __construct() {
			// WooCommerce order details hooks.
			add_action( 'woocommerce_view_order', array( $this, 'order_details' ), 20 );
			add_action( 'woocommerce_thankyou', array( $this, 'order_details' ), 20 );

			// WooCommerce mailing hooks.
			add_filter( 'woocommerce_email_subject_new_order', array( $this, 'capture_order' ), 10, 2 );
			add_filter( 'woocommerce_email_subject_customer_note', array( $this, 'capture_order' ), 10, 2 );
			add_filter( 'woocommerce_email_subject_customer_processing_order', array( $this, 'capture_order' ), 10, 2  );
			add_filter( 'woocommerce_email_subject_customer_completed_order', array( $this, 'capture_order' ), 10, 2 );
			add_action( 'woocommerce_email_footer', array( $this, 'email_footer' ), 1 );
		}

		/**
		 * Throw error on object clone.
		 *
		 * @since 1.0.0
		 * @access private
		 */
		private function __clone() {
			// Cloning instances of the class is forbidden.
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'woocommerce-order-delivery' ), '1.0.0' );
		}

		/**
		 * Disable unserializing of the class.
		 *
		 * @since 1.0.0
		 * @access private
		 */
		private function __wakeup() {
			// Unserializing instances of the class is forbidden.
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'woocommerce-order-delivery' ), '1.0.0' );
		}

		/**
		 * Displays the delivery date section at the end of the order details.
		 *
		 * @since 1.0.0
		 * @param int $order_id The order ID.
		 */
		public function order_details( $order_id ) {
			$delivery_date = get_post_meta( $order_id, '_delivery_date', true );
			if ( $delivery_date ) {
				$delivery_date_i18n = wc_od_localize_date( $delivery_date );
				if ( $delivery_date_i18n ) {
					$args = array( 'delivery_date' => $delivery_date_i18n );

					wc_od_get_template( 'order/delivery-date.php', $args );
				}
			}
		}

		/**
		 * We use the email subject filter to capture the order data and
		 * include the delivery date section at the end of the emails.
		 *
		 * @since 1.0.0
		 * @param string   $email_subject The email subject.
		 * @param WC_Order $order         The order.
		 * @return string The email subject.
		 */
		public function capture_order( $email_subject, $order ) {
			$this->order_id = (int) $order->id;

			return $email_subject;
		}

		/**
		 * Displays the delivery date section at the end of the emails.
		 *
		 * There is no other way compatible with all the WooCommerce versions.
		 *
		 * @since 1.0.0
		 */
		public function email_footer() {
			if ( $this->order_id ) {
				$this->order_details( $this->order_id );
			}
		}
	}
}
