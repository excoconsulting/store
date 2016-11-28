<?php
/**
 * WooCommerce POS  Settings
 *
 * @author    Actuality Extensions
 * @package   WoocommercePointOfSale/Classes/settings
 * @category	Class
 * @since     0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_POS_Admin_Settings_Payment_Methods' ) ) :

/**
 * WC_POS_Admin_Settings_Payment_Methods
 */
class WC_POS_Admin_Settings_Payment_Methods extends WC_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'payment_methods_pos';
		$this->label = __( 'Payment Methods', 'woocommerce' );

		add_filter( 'wc_pos_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'woocommerce_sections_' . $this->id, array( $this, 'output_sections' ) );
		add_action( 'wc_pos_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'woocommerce_admin_field_installed_payment_gateways', array( $this, 'installed_payment_gateways_setting' ) );
		add_action( 'wc_pos_settings_save_' . $this->id, array( $this, 'save' ) );

	}

		/**
	 * Output installed payment gateway settings.
	 *
	 * @access public
	 * @return void
	 */
	public function installed_payment_gateways_setting() {
		?>
		<tr valign="top">
	    <td class="forminp" colspan="2">
	    <style>
	    .wc_gateways th {width: auto;}
	    </style>
				<table class="wc_gateways widefat" cellspacing="0">
					<thead>
						<tr>
							<?php
								$columns = array(
									'sort'     => '',
									'enabled'   => __( 'Enabled', 'woocommerce' ),
									'name'     => __( 'Gateway', 'woocommerce' )
								);

								foreach ( $columns as $key => $column ) {
									echo '<th class="' . esc_attr( $key ) . '">' . esc_html( $column ) . '</th>';
								}
							?>
						</tr>
					</thead>
					<tbody>
			        	<?php
			        	$enabled_gateways = get_option( 'pos_enabled_gateways', array() );
			        	$payment_gateways = array();
			        	$load_gateways    = array();

			        	foreach ( WC()->payment_gateways->payment_gateways() as $gateway ) {
			        		$load_gateways[esc_attr( $gateway->id )] = (object) array('id' => esc_attr( $gateway->id ), 'title' => $gateway->get_title() );
			        	}
			        	$load_gateways['pos_chip_pin'] = (object) array('id' => 'pos_chip_pin', 'title' => __('Chip & PIN', 'wc_point_of_sale') );
			        	
			        	// Get sort order option
			        	$ordering  = (array) get_option( 'pos_exist_gateways' );
			        	
			        	$order_end = 999;

			        	// Load gateways in order
						foreach ( $load_gateways as $id => $load_gateway ) {

							if ( in_array( $id, $ordering ) ) {
								$key = array_search( $id, $ordering );
								$payment_gateways[ $key ] = $load_gateway;
							} else {
								// Add to end of the array
								$payment_gateways[ $order_end ] = $load_gateway;
								$order_end++;
							}
						}

						ksort( $payment_gateways );

			        	foreach ( $payment_gateways as $gateway ) {
			        		
			        		echo '<tr>';

			        		foreach ( $columns as $key => $column ) {
								switch ( $key ) {
									case 'sort' :
										echo '<td width="1%" class="sort"></td>';
									break;

									case 'enabled' :
									$checked = in_array( $gateway->id, $enabled_gateways);
										echo '<td width="1%" class="enabled">
					        				<input type="checkbox" name="pos_enabled_gateways[]" value="' . $gateway->id . '" ' . checked( $checked, true, false ) . ' />
					        				<input type="hidden" name="pos_exist_gateways[]" value="' . $gateway->id . '" />
					        			</td>';
									break;

									case 'name' :
										echo '<td class="name">
					        				' . $gateway->title . '
					        			</td>';
									break;
									default :
										do_action( 'woocommerce_payment_gateways_setting_column_' . $key, $gateway->id );
									break;
								}
							}
							echo '</tr>';
			        	}
			        	?>
					</tbody>
				</table>
				<p><?php  _e( 'To configure each payment gateway, please go to the Checkout tab under WooCommerce > Settings or click <a href="admin.php?page=wc-settings&tab=checkout">here</a>', 'wc_point_of_sale' ); ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Get settings array
	 *
	 * @return array
	 */
	public function get_settings() {
		global $woocommerce;
		return apply_filters( 'woocommerce_point_of_sale_payment_methods_settings_fields', array(

			array( 'title' => __( 'Payment Gateways', 'woocommerce' ), 'type' => 'title', 'id' => 'payment_gateways_options' ),

			array( 'type' => 'installed_payment_gateways' ),			

			array( 'type' => 'sectionend', 'id' => 'payment_gateways_options'),

		) ); // End general settings

	}

	/**
	 * Save settings
	 */
	public function save() {
		$settings = $this->get_settings();

		$pos_enabled_gateways = ( isset( $_POST['pos_enabled_gateways'] ) ) ?  $_POST['pos_enabled_gateways'] : array();
		update_option( 'pos_enabled_gateways', $pos_enabled_gateways );

		$pos_exist_gateways = ( isset( $_POST['pos_exist_gateways'] ) ) ?  $_POST['pos_exist_gateways'] : array();
		update_option( 'pos_exist_gateways', $pos_exist_gateways );
		
		WC_POS_Admin_Settings::save_fields( $settings );
	}

}

endif;

return new WC_POS_Admin_Settings_Payment_Methods();
