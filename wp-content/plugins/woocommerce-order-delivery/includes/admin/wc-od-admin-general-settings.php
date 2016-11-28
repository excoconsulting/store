<?php
/**
 * Admin custom settings
 *
 * @author      WooThemes
 * @package     WC_OD
 * @since       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/** Custom settings *********************************************************/


/**
 * Outputs the content for a custom field within a wrapper.
 *
 * @since 1.0.0
 * @global WooCommerce $woocommerce The WooCommerce instance.
 * @param array $field The field data.
 */
function wc_od_field_wrapper( $field ) {
	global $woocommerce;

	// Description handling.
	if ( true === $field['desc_tip'] ) {
		$field['desc_tip'] = $field['desc'];
		$field['desc'] = '';
	}

	$field['desc'] = wp_kses_post( $field['desc'] );

	// Custom attributes handling.
	$custom_attributes = array();
	if ( !empty( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) ) {
		foreach ( $field['custom_attributes'] as $attribute => $attribute_value ) {
			$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
		}
	}

	$field['custom_attributes'] = $custom_attributes;
	$tip = '<img class="help_tip" data-tip="' . esc_attr( $field['desc_tip'] ) . '" src="' . $woocommerce->plugin_url() . '/assets/images/help.png" height="16" width="16" />';

	/**
	 * Filters the function used for output the field content within a wrapper.
	 *
	 * @since 1.0.0
	 * @param callable $callable The callable function.
	 * @param array    $field    The field data.
	 */
	$callback = apply_filters( 'wc_od_field_wrapper_callback', "{$field['type']}_field", $field );
	?>
	<tr valign="top">
		<th scope="row"><?php echo esc_html( $field['title'] ) . ( $field['desc_tip'] ? " {$tip}" : '' ) ?></th>
		<td class="forminp forminp-<?php echo sanitize_title( $field['type'] ) ?>">
		<?php
			if ( $callback && is_callable( $callback ) ) :
				call_user_func( $callback, $field );
			endif;
		?>
		</td>
	</tr>
	<?php
}

/**
 * Outputs the content for the wc_od_shipping_days field.
 *
 * @since 1.0.0
 * @param array $field The field data.
 */
function wc_od_shipping_days_field( $field ) {
	$week_days = wc_od_get_week_days();
	$shipping_days = WC_OD()->settings()->get_setting( $field['id'] );
	// The field name must be different of the field ID to allow a custom validation and save.
	$field_id = $field['id'];
	$name = wc_od_no_prefix( $field_id );
	?>
	<fieldset>
	<?php foreach ( $shipping_days as $key => $data ) : ?>
		<label for="<?php echo esc_attr( "{$field_id}_{$key}" ); ?>" style="display:inline-block;width:125px;">
		<input id="<?php echo esc_attr( "{$field_id}_{$key}" ); ?>" type="checkbox" name="<?php echo esc_attr( $name . "[{$key}][enabled]" ); ?>" <?php checked( (bool) $data['enabled'], true ); ?> />
		<?php echo wp_kses_post( $week_days[ $key ] ); ?></label>
		<label for="<?php echo esc_attr( "shipping_days_time_{$key}" ); ?>">
			<span class="shipping-days-time-label" style="font-size:12px;"><?php _e( 'limit:', 'woocommerce-order-delivery' ); ?></span>
			<input class="timepicker" id="<?php echo esc_attr( "shipping_days_time_{$key}" ); ?>" type="text" name="<?php echo esc_attr( $name . "[{$key}][time]" ); ?>" value="<?php echo esc_attr( $data['time'] ); ?>" style="width:80px;" />
		</label>
		<br>
	<?php endforeach; ?>
		<?php if ( $field['desc'] ) : ?>
			<p class="description"><?php echo $field['desc']; ?></p>
		<?php endif; ?>
	</fieldset>
	<?php
}

/**
 * Outputs the content for the wc_od_day_range field.
 *
 * @since 1.0.0
 * @param array $field The field data.
 */
function wc_od_day_range_field( $field ) {
	// The field name must be different of the field ID to allow a custom validation and save.
	$field_id = $field['id'];
	$name = wc_od_no_prefix( $field_id );
	$value = WC_OD()->settings()->get_setting( $field['id'] );
	?>
	<label for="<?php echo $field_id; ?>">
	<?php
		printf( __( 'Between %1$s and %2$s days.', 'woocommerce-order-delivery' ),
			sprintf(
				'<input id="%1$s" name="%2$s[min]" type="number" value="%3$s" style="%4$s" %5$s />',
				$field_id,
				$name,
				esc_attr( $value['min'] ),
				esc_attr( $field['css'] ),
				implode( ' ', $field['custom_attributes'] )
			),
			sprintf(
				'<input id="%1$s" name="%2$s[max]" type="number" value="%3$s" style="%4$s" %5$s />',
				$field_id,
				$name,
				esc_attr( $value['max'] ),
				esc_attr( $field['css'] ),
				implode( ' ', $field['custom_attributes'] )
			)
		);
	?>
	</label>
	<?php if ( $field['desc'] ) : ?>
		<p class="description"><?php echo $field['desc']; ?></p>
	<?php endif; ?>
	<?php
}

/**
 * Outputs the content for the wc_od_delivery_days field.
 *
 * @since 1.0.0
 * @param array $field The field data.
 */
function wc_od_delivery_days_field( $field ) {
	$week_days = wc_od_get_week_days();
	$delivery_days = WC_OD()->settings()->get_setting( $field['id'] );
	// The field name must be different of the field ID to allow a custom validation and save.
	$field_id = $field['id'];
	$name = wc_od_no_prefix( $field_id );
	?>
	<fieldset>
	<?php foreach ( $delivery_days as $key => $data ) : ?>
		<label for="<?php echo esc_attr( "{$field_id}_{$key}" ); ?>">
		<input id="<?php echo esc_attr( "{$field_id}_{$key}" ); ?>" type="checkbox" name="<?php echo esc_attr( $name . "[{$key}][enabled]" ); ?>" <?php checked( (bool) $data['enabled'], true ); ?> />
		<?php echo wp_kses_post( $week_days[ $key ] ); ?></label>
		<br>
	<?php endforeach; ?>
		<?php if ( $field['desc'] ) : ?>
			<p class="description"><?php echo $field['desc']; ?></p>
		<?php endif; ?>
	</fieldset>
	<?php
}


/** Helper functions **********************************************************/


/**
 * Gets the week days in a pair index => label.
 *
 * @since 1.0.0
 * @return array The week days.
 */
function wc_od_get_week_days() {
	$week_days = array(
		__( 'Sunday', 'woocommerce-order-delivery' ),
		__( 'Monday', 'woocommerce-order-delivery' ),
		__( 'Tuesday', 'woocommerce-order-delivery' ),
		__( 'Wednesday', 'woocommerce-order-delivery' ),
		__( 'Thursday', 'woocommerce-order-delivery' ),
		__( 'Friday', 'woocommerce-order-delivery' ),
		__( 'Saturday', 'woocommerce-order-delivery' ),
	);

	return $week_days;
}