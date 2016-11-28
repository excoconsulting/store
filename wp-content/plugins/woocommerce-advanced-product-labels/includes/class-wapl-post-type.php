<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class WAPL_Post_Type
 *
 * Initialize the WAPL post type
 *
 * @class       WAPL_Post_Type
 * @author     	Jeroen Sormani
 * @package		WooCommerce Advanced Product Labels
 * @version		1.0.0
 */
class WAPL_Post_Type {


	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Register post type
		add_action( 'init', array( $this, 'register_post_type' ) );

		 // Edit user notices
		 add_filter( 'post_updated_messages', array( $this, 'custom_post_type_messages' ) );

		 // Add meta box
 		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
 		// Save meta box
		add_action( 'save_post', array( $this, 'woocommerce_update_options' ) );

		 // Redirect after delete
		 add_action('load-edit.php', array( $this, 'redirect_after_trash' ) );

	}


	/**
	 * Register post type.
	 *
	 * Register the WCAM post type.
	 *
	 * @since 1.0.0
	 */
	public function register_post_type() {

		$labels = array(
		    'name' 					=> __( 'Global Labels', 'woocommerce-advanced-product-labels' ),
			'singular_name' 		=> __( 'Global Label', 'woocommerce-advanced-product-labels' ),
		    'add_new' 				=> __( 'Add New', 'woocommerce-advanced-product-labels' ),
		    'add_new_item' 			=> __( 'Add New Global Label', 'woocommerce-advanced-product-labels' ),
		    'edit_item' 			=> __( 'Edit Global Label', 'woocommerce-advanced-product-labels' ),
		    'new_item' 				=> __( 'New Global Label', 'woocommerce-advanced-product-labels' ),
		    'view_item' 			=> __( 'View Global Label', 'woocommerce-advanced-product-labels' ),
		    'search_items' 			=> __( 'Search Global Labels', 'woocommerce-advanced-product-labels' ),
		    'not_found' 			=> __( 'No Global Labels', 'woocommerce-advanced-product-labels' ),
		    'not_found_in_trash'	=> __( 'No Global Labels found in Trash', 'woocommerce-advanced-product-labels' ),
		);

		register_post_type( 'wapl', array(
			'label' 				=> 'wapl',
			'show_ui' 				=> true,
			'show_in_menu' 			=> false,
			'capability_type' 		=> 'post',
			'map_meta_cap' 			=> true,
			'rewrite' 				=> array( 'slug' => 'wapl', 'with_front' => true ),
			'_builtin' 				=> false,
			'query_var' 			=> true,
			'supports' 				=> array( 'title' ),
			'labels' 				=> $labels,
		) );

	}


	/**
	 * Messages.
	 *
	 * Modify the notice messages text for the 'wapl' post type.
	 *
	 * @since 1.0.0
	 *
	 * @param 	array $messages Existing list of messages.
	 * @return 	array			Modified list of messages.
	 */
	function custom_post_type_messages( $messages ) {

		$post 				= get_post();
		$post_type      	= get_post_type( $post );
		$post_type_object 	= get_post_type_object( $post_type );

		$messages['wapl'] = array(
			0  => '',
			1  => __( 'Global product label updated.', 'woocommerce-advanced-product-labels' ),
			2  => __( 'Custom field updated.', 'woocommerce-advanced-product-labels' ),
			3  => __( 'Custom field deleted.', 'woocommerce-advanced-product-labels' ),
			4  => __( 'Global product label updated.', 'woocommerce-advanced-product-labels' ),
			5  => isset( $_GET['revision'] ) ?
				sprintf( __( 'Product label restored to revision from %s', 'woocommerce-advanced-product-labels' ), wp_post_revision_title( (int) $_GET['revision'], false ) )
				: false,
			6  => __( 'Global product label published.', 'woocommerce-advanced-product-labels' ),
			7  => __( 'Global product label saved.', 'woocommerce-advanced-product-labels' ),
			8  => __( 'Global product label submitted.', 'woocommerce-advanced-product-labels' ),
			9  => sprintf(
				__( 'Global product label scheduled for: <strong>%1$s</strong>.', 'woocommerce-advanced-product-labels' ),
				date_i18n( __( 'M j, Y @ G:i', 'woocommerce-advanced-product-labels' ), strtotime( $post->post_date ) )
			),
			10 => __( 'Global product label draft updated.', 'woocommerce-advanced-product-labels' )
		);

		$permalink = admin_url( 'admin.php?page=wc-settings&tab=labels' );

		$view_link = sprintf( ' <a href="%s">%s</a>', esc_url( $permalink ), __( 'Return to overview.', 'woocommerce-advanced-product-labels' ) );
		$messages['wapl'][1] .= $view_link;
		$messages['wapl'][6] .= $view_link;
		$messages['wapl'][9] .= $view_link;

		$preview_link = sprintf( ' <a target="_blank" href="%s">%s</a>', esc_url( $permalink ), __( 'Return to overview.', 'woocommerce-advanced-product-labels' ) );
		$messages['wapl'][8]  .= $preview_link;
		$messages['wapl'][10] .= $preview_link;

		return $messages;

	}


	/**
	 * Save WAPL data.
	 *
	 * Save the meta box data from the WAPL post type.
	 *
	 * @since 1.0.0
	 *
	 * @param int $post_id
	 */
	public function woocommerce_update_options( $post_id ) {

		if ( !isset( $_POST['wapl_global_label_meta_box_nonce'] ) )
			return $post_id;

		$nonce = $_POST['wapl_global_label_meta_box_nonce'];

		// verify nonce
		if ( !wp_verify_nonce( $nonce, 'wapl_global_label_meta_box' ) )
			return $post_id;

		// if autosave, don't save
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return $post_id;

		// check capability
		if ( !current_user_can( apply_filters( 'wapl_global_label_capability', 'manage_woocommerce' ) ) )
			return $post_id;

		// check if post_type is wapl
		if ( $_POST['post_type'] != 'wapl' )
			return $post_id;


		foreach ( $_POST as $key => $value ) :

			if ( strpos( $key, 'wapl' ) !== false ) :
				update_post_meta( $post_id, $key, $value );
			endif;

		endforeach;

	}


	/**
	 * Add meta boxes.
	 *
	 * Add two meta boxes to the 'wapl' posts.
	 *
	 * @since 1.0.0
	 */
	public function add_meta_boxes() {

		add_meta_box( 'wapl_conditions', 'Global Label conditions', array( $this, 'meta_boxes_conditions' ),'wapl', 'normal' );
		add_meta_box( 'wapl_label', 'Global Label settings', array( $this, 'meta_boxes_label_settings' ), 'wapl', 'normal' );

	}


	/**
	 * Display conditions.
	 *
	 * Display Gobal label conditions in condition groups and rows.
	 *
	 * @since 1.0.0
	 */
	public function meta_boxes_conditions() {

		?><p>
			<strong><?php _e( 'A product must match all these conditions to show the label:', 'woocommerce-advanced-product-labels' ); ?></strong>
		</p>

		<div id='conditions-wrap'>

			<div id='conditions-container'><?php

				global $post;
				$current_values = get_post_meta( $post->ID, '_wapl_global_label', true );

				// Show something when empty
				if ( @count( $current_values['conditions'] ) == 0 || empty( $current_values ) ) :

					?><div class='conditions-group-wrap'>

						<div class='conditions-group' data-group='0'>
							<?php $new_meta_box_condition = new WAPL_Condition( null ); ?>
						</div>

						<p>
							<strong><?php _e( 'Or', 'woocommerce-advanced-product-labels' ); ?></strong>
						</p>
					</div><?php

				else :

					foreach ( $current_values['conditions'] as $group_key => $group ) :

						?><div class='conditions-group-wrap'>

							<div class='conditions-group' data-group='<?php echo $group_key; ?>'>
								<p>
									<strong><?php _e( 'Or a product must match all these condititions to show the label:', 'woocommerce-advanced-product-labels' ); ?></strong>
								</p><?php

								foreach ( $group as $id => $condition ) :

									$condition = new WAPL_Condition( $id, $group_key, $condition['condition'], $condition['operator'], $condition['value'], $id );

								endforeach;

							?></div>

							<p>
								<strong><?php _e( 'Or', 'woocommerce-advanced-product-labels' ); ?></strong>
							</p>

						</div><?php

					endforeach;

				endif;

			?></div>

			<a href='javascript:void(0);' class='button add-group'><?php _e( 'Add \'or\' group', 'woocommerce-advanced-product-labels' ); ?></a>

		</div><?php

	}


	/**
	 * Meta box label settings.
	 *
	 * Load the file to display the meta box label settings.
	 *
	 * @since 1.0.0
	 */
	public function meta_boxes_label_settings() {

		/**
		 * Require file for meta box label settings
		 */
		require_once plugin_dir_path( __FILE__ ) . 'admin/settings/global-meta-box-label.php';

	}


	/**
	 * Redirect trash.
	 *
	 * Redirect user after trashing a WCAM post.
	 *
	 * @since 1.0.0
	 */
	public function redirect_after_trash() {

		$screen = get_current_screen();

		if( 'edit-wapl' == $screen->id ) :

			if( isset( $_GET['trashed'] ) &&  intval( $_GET['trashed'] ) > 0 ) :

				$redirect = admin_url( '/admin.php?page=wc-settings&tab=labels' );
				wp_redirect( $redirect );
				exit();

			endif;

		endif;


	}


}
