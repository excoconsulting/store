<?php
/**
 * Setup Wizard Class
 *
 * Takes new users through some basic steps to setup their store.
 *
 * @author      Actuality Extensions
 * @category    Admin
 * @package     WC_CRM/Admin
 * @version     1.0.0
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_CRM_Admin_Setup_Wizard class
 */
class WC_CRM_Admin_Setup_Wizard {

	/** @var string Currenct Step */
	private $step   = '';

	/** @var array Steps for the setup wizard */
	private $steps  = array();


	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		if ( current_user_can( 'manage_woocommerce' ) ) {
			add_action( 'admin_menu', array( $this, 'admin_menus' ) );
			add_action( 'admin_init', array( $this, 'setup_wizard' ) );
		}
	}

	/**
	 * Add admin menus/screens.
	 */
	public function admin_menus() {
		add_dashboard_page( '', '', 'manage_options', WC_CRM_TOKEN . '-setup', '' );
	}

	/**
	 * Show the setup wizard
	 */
	public function setup_wizard() {
		wc_crm_clear_transient();
		if ( empty( $_GET['page'] ) || WC_CRM_TOKEN . '-setup' !== $_GET['page'] ) {
			return;
		}
		$this->steps = array(
			'introduction' => array(
				'name'    =>  __( 'Introduction', 'wc_crm' ),
				'view'    => array( $this, 'wc_crm_setup_introduction' ),
				'handler' => ''
			),
			'general_options' => array(
				'name'    =>  __( 'General Options', 'wc_crm' ),
				'view'    => array( $this, 'wc_crm_setup_general_options' ),
				'handler' => array( $this, 'wc_crm_setup_general_options_save' )
			),
			'fetch_customers' => array(
				'name'    =>  __( 'Fetch Customers', 'wc_crm' ),
				'view'    => array( $this, 'wc_crm_setup_fetch_customers' ),
				'handler' => array( $this, 'wc_crm_setup_fetch_customers_save' )
			),
			'load_customers' => array(
				'name'    =>  __( 'Load Customers', 'wc_crm' ),
				'view'    => array( $this, 'wc_crm_setup_load_customers' ),
				'handler' => array( $this, 'wc_crm_setup_load_customers_save' )
			),
			'next_steps' => array(
				'name'    =>  __( 'Finished!', 'wc_crm' ),
				'view'    => array( $this, 'wc_crm_setup_ready' ),
				'handler' => ''
			)
		);
		$this->step = isset( $_GET['step'] ) ? sanitize_key( $_GET['step'] ) : current( array_keys( $this->steps ) );
		$suffix     = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_register_script( 'jquery-blockui', WC()->plugin_url() . '/assets/js/jquery-blockui/jquery.blockUI' . $suffix . '.js', array( 'jquery' ), '2.70', true );
		wp_register_script( 'select2', WC()->plugin_url() . '/assets/js/select2/select2' . $suffix . '.js', array( 'jquery' ), '3.5.2' );
		wp_register_script( 'wc-enhanced-select', WC()->plugin_url() . '/assets/js/admin/wc-enhanced-select' . $suffix . '.js', array( 'jquery', 'select2' ), WC_VERSION );
		wp_localize_script( 'wc-enhanced-select', 'wc_enhanced_select_params', array(
			'i18n_matches_1'            => _x( 'One result is available, press enter to select it.', 'enhanced select', 'wc_crm' ),
			'i18n_matches_n'            => _x( '%qty% results are available, use up and down arrow keys to navigate.', 'enhanced select', 'wc_crm' ),
			'i18n_no_matches'           => _x( 'No matches found', 'enhanced select', 'wc_crm' ),
			'i18n_ajax_error'           => _x( 'Loading failed', 'enhanced select', 'wc_crm' ),
			'i18n_input_too_short_1'    => _x( 'Please enter 1 or more characters', 'enhanced select', 'wc_crm' ),
			'i18n_input_too_short_n'    => _x( 'Please enter %qty% or more characters', 'enhanced select', 'wc_crm' ),
			'i18n_input_too_long_1'     => _x( 'Please delete 1 character', 'enhanced select', 'wc_crm' ),
			'i18n_input_too_long_n'     => _x( 'Please delete %qty% characters', 'enhanced select', 'wc_crm' ),
			'i18n_selection_too_long_1' => _x( 'You can only select 1 item', 'enhanced select', 'wc_crm' ),
			'i18n_selection_too_long_n' => _x( 'You can only select %qty% items', 'enhanced select', 'wc_crm' ),
			'i18n_load_more'            => _x( 'Loading more results&hellip;', 'enhanced select', 'wc_crm' ),
			'i18n_searching'            => _x( 'Searching&hellip;', 'enhanced select', 'wc_crm' ),
			'ajax_url'                  => admin_url( 'admin-ajax.php' ),
			'search_products_nonce'     => wp_create_nonce( 'search-products' ),
			'search_customers_nonce'    => wp_create_nonce( 'search-customers' )
		) );
		wp_enqueue_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION );
		wp_enqueue_style( WC_CRM_TOKEN . '-setup', esc_url( WC_CRM()->assets_url ) . 'css/wc-crm-setup.css', array( 'dashicons', 'install' ), WC_VERSION );

		//wp_enqueue_script();

		wp_register_script( WC_CRM_TOKEN . '-setup', WC()->plugin_url() . '/assets/js/admin/wc-setup.min.js', array( 'jquery', 'wc-enhanced-select', 'jquery-blockui', 'jquery-ui-progressbar' ), WC_VERSION );
		wp_localize_script( WC_CRM_TOKEN . '-setup', 'wc_setup_params', array(
			'locale_info' => json_encode( include( WC()->plugin_path() . '/i18n/locale-info.php' ) )
		) );

		if ( ! empty( $_POST['save_step'] ) && isset( $this->steps[ $this->step ]['handler'] ) ) {
			call_user_func( $this->steps[ $this->step ]['handler'] );
		}

		header('Content-Type: text/html; charset=utf-8');
		ob_start();
		$this->setup_wizard_header();
		if( $this->step != 'update_customers'){
			$this->setup_wizard_steps();			
			$this->setup_wizard_content();
		}else{
			$this->wc_crm_setup_update_customers();
		}
		$this->setup_wizard_footer();
		exit;
	}

	public function get_next_step_link() {
		$keys = array_keys( $this->steps );
		return add_query_arg( 'step', $keys[ array_search( $this->step, array_keys( $this->steps ) ) + 1 ], remove_query_arg( 'translation_updated' ) );
	}

	/**
	 * Setup Wizard Header
	 */
	public function setup_wizard_header() {
		?>
		<!DOCTYPE html>
		<html <?php language_attributes(); ?>>
		<head>
			<meta name="viewport" content="width=device-width" />
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			<title><?php _e( 'WooCommerce &rsaquo; Setup Wizard', 'wc_crm' ); ?></title>
			<?php wp_print_scripts( WC_CRM_TOKEN . '-setup' ); ?>
			<?php do_action( 'admin_print_styles' ); ?>
			<?php #do_action( 'admin_head' ); ?>
		</head>
		<body class="wc-setup wp-core-ui">
			<h2 id="logos">
				<img id="ae-logo" src="<?php echo esc_url( WC_CRM()->assets_url ); ?>img/ae-logo.png" alt="Actuality Extensions" />
			</h2>
		<?php
	}

	/**
	 * Setup Wizard Footer
	 */
	public function setup_wizard_footer() {
		?>
			<?php if ( 'next_steps' === $this->step ) : ?>
				<a class="wc-return-to-dashboard" href="<?php echo esc_url( admin_url() ); ?>"><?php _e( 'Return to the WordPress Dashboard', 'wc_crm' ); ?></a>
			<?php endif; ?>
			</body>
		</html>
		<?php
	}

	/**
	 * Output the steps
	 */
	public function setup_wizard_steps() {
		$ouput_steps = $this->steps;
		array_shift( $ouput_steps );
		?>
		<ol class="wc-setup-steps">
			<?php foreach ( $ouput_steps as $step_key => $step ) : ?>
				<li class="<?php
					if ( $step_key === $this->step ) {
						echo 'active';
					} elseif ( array_search( $this->step, array_keys( $this->steps ) ) > array_search( $step_key, array_keys( $this->steps ) ) ) {
						echo 'done';
					}
				?>"><?php echo esc_html( $step['name'] ); ?></li>
			<?php endforeach; ?>
		</ol>
		<?php
	}

	/**
	 * Output the content for the current step
	 */
	public function setup_wizard_content() {
		echo '<div class="wc-setup-content">';
		call_user_func( $this->steps[ $this->step ]['view'] );
		echo '</div>';
	}

	/**
	 * Introduction step
	 */
	public function wc_crm_setup_introduction() {
		?>
		<h1><?php _e( 'Welcome to WooCommerce Customer Relationship Manager!', 'wc_crm' ); ?></h1>
		<p><?php _e( 'Thank you for choosing WooCommerce Customer Relationship Manager to manage your customers! This quick setup wizard will help you configure the basic settings.', 'wc_crm' ); ?></p>
		<p><?php _e( 'No time right now? If you don’t want to go through the wizard, you can skip and return to the WordPress dashboard. Come back anytime if you change your mind!', 'wc_crm' ); ?></p>
		<p class="wc-setup-actions step">
			<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>" class="button-primary button button-large button-next"><?php _e( 'Let\'s Go!', 'wc_crm' ); ?></a>
			<a href="<?php echo esc_url( admin_url( 'plugins.php' ) ); ?>" class="button button-large"><?php _e( 'Not right now', 'wc_crm' ); ?></a>
		</p>
		<?php
	}
	

	/**
	 * General settings
	 */
	public function wc_crm_setup_general_options() {		

		// Defaults
		$username       = get_option( 'wc_crm_username_add_customer', 1 );
		$total_value    = get_option( 'wc_crm_total_value', array('wc-completed') );
		?>
		<h1><?php _e( 'General Options', 'wc_crm' ); ?></h1>
		<form method="post">
			<table class="form-table">
				<tr>
					<th scope="row"><?php echo _e( 'Username', 'wc_crm' ); ?></th>
					<td>
						<select id="wc_crm_username_add_customer" name="wc_crm_username_add_customer" class="wc-enhanced-select">
							<option value="1" <?php selected( $username, 1 ); ?>><?php echo __( 'First & last name e.g. johnsmith', 'wc_crm' ); ?></option>
							<option value="2" <?php selected( $username, 2 ); ?>><?php echo __( 'Hyphen separated e.g. john-smith', 'wc_crm' ); ?></option>
							<option value="3" <?php selected( $username, 3 ); ?>><?php echo __( 'Email address', 'wc_crm' ); ?></option>
						</select>
						<span class="description">
							<?php _e( 'Choose what the username is when customers are added.', 'wc_crm' ); ?>
						</span>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php echo _e( 'Value', 'wc_crm' ); ?></th>
					<td>
						<select id="wc_crm_total_value" name="wc_crm_total_value[]" required style="width:100%;" multiple="true" data-placeholder="<?php esc_attr_e( 'Choose a statuses&hellip;', 'wc_crm' ); ?>" class="wc-enhanced-select">
								<option value=""><?php _e( 'Choose a status&hellip;', 'wc_crm' ); ?></option>
								<?php
								foreach ( wc_get_order_statuses() as $slug => $name ) {
									echo '<option value="' . esc_attr( $slug ) . '" ' . selected( in_array( $slug, $total_value), true, false ) . '>' . esc_html( $name ) . '</option>';
								}
								?>
							</select>
						<span class="description">
							<?php _e( 'Choose which statuses the customer orders must be before appearing in the Value column.', 'wc_crm' ); ?>
						</span>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="wc_crm_automatic_emails_new_customer"><?php echo _e( 'Automatic Emails', 'wc_crm' ); ?></label></th>
					<td>
						<input type="checkbox" id="wc_crm_automatic_emails_new_customer" <?php checked( get_option( 'wc_crm_automatic_emails_new_customer', 'yes' ), 'yes' ); ?> name="wc_crm_automatic_emails_new_customer" class="input-checkbox" value="1" />
						<label for="wc_crm_automatic_emails_new_customer"><?php _e( 'Check this box to send an email with username and password when creating a new customer.', 'wc_crm' ); ?></label>
					</td>
				</tr>
			</table>
			<p class="wc-setup-actions step">
				<input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e( 'Continue', 'wc_crm' ); ?>" name="save_step" />
				<?php wp_nonce_field( WC_CRM_TOKEN . '-setup' ); ?>
			</p>
		</form>
		<?php
	}

	/**
	 * Save Locale Settings
	 */
	public function wc_crm_setup_general_options_save() {
		check_admin_referer( WC_CRM_TOKEN . '-setup' );

		$username         = $_POST['wc_crm_username_add_customer'];
		$total_value      = $_POST['wc_crm_total_value'];
		$automatic_emails = isset( $_POST['wc_crm_automatic_emails_new_customer'] ) ? 'yes' : 'no';

		update_option( 'wc_crm_username_add_customer', $username );
		update_option( 'wc_crm_total_value', $total_value );
		update_option( 'wc_crm_automatic_emails_new_customer', $automatic_emails );

		wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
		exit;
	}

	/**
	 * Fetch customers setup
	 */
	public function wc_crm_setup_fetch_customers() {
		global $wp_roles;

		$user_roles          = get_option( 'wc_crm_user_roles', array('customer') );
		$customer_name       = get_option( 'wc_crm_customer_name', 'fl' );
		?>
		<h1><?php _e( 'Fetch Customers', 'wc_crm' ); ?></h1>
		<form method="post">
			<p><?php _e( 'The following options affects how the customers in the customers table should be fetched.', 'wc_crm' ); ?></p>
			<table class="form-table" cellspacing="0">
				<tbody>
					<tr>
						<th scope="row"><?php echo _e( 'User Roles', 'wc_crm' ); ?></th>
						<td>
							<select id="wc_crm_user_roles" name="wc_crm_user_roles[]" required style="width:100%;" multiple="true" data-placeholder="<?php esc_attr_e( 'Choose a roles&hellip;', 'wc_crm' ); ?>" class="wc-enhanced-select">
								<option value=""><?php _e( 'Choose a role&hellip;', 'wc_crm' ); ?></option>
								<?php
								foreach ( $wp_roles->role_names as $role => $name ) {
									echo '<option value="' . esc_attr( $role ) . '" ' . selected( in_array( $role, $user_roles), true, false ) . '>' . esc_html( $name ) . '</option>';
								}
								?>
							</select>
							<span class="description">
								<?php _e( 'Choose which User Roles of the customers/users that will be shown in the customers table.', 'wc_crm' ); ?>
							</span>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="wc_crm_guest_customers"><?php echo _e( 'Guest Customers', 'wc_crm' ); ?></label></th>
						<td>
							<input type="checkbox" id="wc_crm_guest_customers" <?php checked( get_option( 'wc_crm_guest_customers', 'no' ), 'yes' ); ?> name="wc_crm_guest_customers" class="input-checkbox" value="1" />
							<label for="wc_crm_guest_customers"><?php _e( 'Select whether Guest customers appear on the customers table.', 'wc_crm' ); ?></label>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php echo _e( 'Customer Name Format', 'wc_crm' ); ?></th>
						<td>
							<select id="wc_crm_customer_name" name="wc_crm_customer_name" class="wc-enhanced-select">
								<option value="fl" <?php selected( $customer_name, 'fl' ); ?>><?php echo __( 'First Last', 'wc_crm' ); ?></option>
								<option value="lf" <?php selected( $customer_name, 'lf' ); ?>><?php echo __( 'Last, First', 'wc_crm' ); ?></option>
							</select>
							<span class="description">
								<?php _e( 'Choose the format of the names displayed on the Customers page.', 'wc_crm' ); ?>
							</span>
						</td>
					</tr>
				</tbody>
			</table>

			<p class="wc-setup-actions step">
				<input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e( 'Continue', 'wc_crm' ); ?>" name="save_step" />
			</p>
		</form>
		<?php
	}

	/**
	 * Fetch customers Settings
	 */
	public function wc_crm_setup_fetch_customers_save() {
		$user_roles        = $_POST['wc_crm_user_roles'];
		$guest_customers   = isset( $_POST['wc_crm_guest_customers'] ) ? 'yes' : 'no';
		$customer_name     = $_POST['wc_crm_customer_name'];

		update_option( 'wc_crm_user_roles', $user_roles );
		update_option( 'wc_crm_guest_customers', $guest_customers );
		update_option( 'wc_crm_customer_name', $customer_name );

		wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
		exit;
	}

	/**
	 * Fetch customers setup
	 */
	public function wc_crm_setup_load_customers() {
		?>
		<h1><?php _e( 'Load Customers', 'wc_crm' ); ?></h1>
		<p><?php _e("Please be patient while the customers are loaded. You will be notified via this page when the loading is completed.", 'wc_crm'); ?></p>
		<noscript><p><em><?php _e("You must enable Javascript in order to proceed!", 'wc_crm'); ?></em></p></noscript>

		<?php include_once 'views/html-reload-customers.php'; ?>

		<a href="#" class="button" id="togle_options"><?php _e('Advanced Options', 'wc_crm');?></a>
		<table class="form-table" cellspacing="0" style="display: none;" id="advanced_options">
			<tbody>
				<tr>
					<th scope="row"><?php _e('Offset', 'wc_crm');?></th>
					<td>
						<input type="number" step="1" min="0" id="offset" style="width:100px;">
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e('Limit', 'wc_crm');?></th>
					<td>
						<input type="number" step="1" min="0" id="limit" style="width:100px;">
					</td>
				</tr>
			</tbody>
		</table>
		<p class="wc-setup-actions step" id="load_customers_buttons">
			<input type="button" id="force_reload_customers" name="force_reload_customers" class="button-primary hide-if-no-js button button-large" value="<?php esc_attr_e( 'Start load', 'wc_crm' ); ?>" />
			<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>" class="hidden button-primary button button-large button-next"><?php _e( 'Continue', 'wc_crm' ); ?></a>
		</p>
		<?php
	}
	/**
	 * Fetch customers setup
	 */
	public function wc_crm_setup_update_customers() {
		?>
		<div class="wc-setup-content">
		<h1><?php _e( 'Update Customers', 'wc_crm' ); ?></h1>
		<p><?php _e("Please be patient while the customers are loaded. You will be notified via this page when the loading is completed.", 'wc_crm'); ?></p>
		<noscript><p><em><?php _e("You must enable Javascript in order to proceed!", 'wc_crm'); ?></em></p></noscript>

		<?php include_once 'views/html-reload-customers.php'; ?>

		<a href="#" class="button" id="togle_options"><?php _e('Advanced Options', 'wc_crm');?></a>
		<table class="form-table" cellspacing="0" style="display: none;" id="advanced_options">
			<tbody>
				<tr>
					<th scope="row"><?php _e('Offset', 'wc_crm');?></th>
					<td>
						<input type="number" step="1" min="0" id="offset" style="width:100px;">
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e('Limit', 'wc_crm');?></th>
					<td>
						<input type="number" step="1" min="0" id="limit" style="width:100px;">
					</td>
				</tr>
			</tbody>
		</table>

		<p class="wc-setup-actions step" id="load_customers_buttons">
			<input type="button" id="force_reload_customers" name="force_reload_customers" class="button-primary hide-if-no-js button button-large" value="<?php esc_attr_e( 'Start load', 'wc_crm' ); ?>" />
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . WC_CRM_TOKEN ) ); ?>" class="hidden button-primary button button-large button-next"><?php _e( 'Return to the customers list', 'wc_crm' ); ?></a>
		</p>
		</div>
		<?php
	}

	/**
	 * Fetch customers Settings
	 */
	public function wc_crm_setup_load_customers_save() {
		wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
		exit;
	}


	/**
	 * Final step
	 */
	public function wc_crm_setup_ready() {
		WC_CRM_Admin_Notices::remove_notice( 'crm_install' );
		update_option('wc_crm_customers_loaded', 'yes');
		?>

		<h1><?php _e( 'WooCommerce CRM is Ready!', 'wc_crm' ); ?></h1>

		<div class="wc-setup-next-steps">
			<div class="wc-setup-next-steps-first">
				<h2><?php _e( 'Next Steps', 'wc_crm' ); ?></h2>
				<ul>
					<li class="setup-product"><a class="button button-primary button-large" href="<?php echo esc_url( admin_url( 'admin.php?page=' . WC_CRM_TOKEN ) ); ?>"><?php _e( 'View your customers!', 'wc_crm' ); ?></a></li>
				</ul>
			</div>
			<div class="wc-setup-next-steps-last">
				<h2><?php _e( 'Learn More', 'wc_crm' ); ?></h2>
				<ul>
					<li class="newsletter"><a href="http://eepurl.com/Ybb5j" target="_blank"><?php _e( 'Subscribe to our newlsetter', 'wc_crm' ); ?></a></li>
					<li class="learn-more"><a href="http://actualityextensions.com/documentation/" target="_blank"><?php _e( 'Read more about getting started', 'wc_crm' ); ?></a></li>
					<li class="shop-more"><a href="http://codecanyon.net/user/actualityextensions/portfolio/" target="_blank"><?php _e( 'Explore our other powerful extensions', 'wc_crm' ); ?></a></li>
				</ul>
			</div>
		</div>
		<?php
	}
}

new WC_CRM_Admin_Setup_Wizard();
