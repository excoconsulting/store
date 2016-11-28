<?php

function edgt_slides_category_taxonomy_custom_fields($tag) {
    $t_id = $tag->term_id; // Get the ID of the term you're editing  
    $term_meta = get_option( "taxonomy_term_$t_id" );
    ?>

    <tr class="form-field">
        <th scope="row" valign="top">
            <label for="shortcode"><?php esc_html_e('Slider shortcode', 'edgt_core'); ?></label>
        </th>
        <td>
            <input type="text" name="term_meta[shortcode]" id="term_meta[shortcode]" size="25" style="width:60%;" value="<?php echo esc_attr($tag->slug) ? "[edgtf_slider slider='".$tag->slug."']" : ""; ?>" readonly><br />
            <span class="description"><?php esc_html_e('Use this shortcode to insert it on page', 'edgt_core'); ?></span>
        </td>
    </tr>
    <tr class="form-field">
        <th scope="row" valign="top">
            <label for="shortcode"><?php esc_html_e('Anchor', 'edgt_core'); ?></label>
        </th>
        <td>
            <input type="text" name="term_meta[anchor]" id="term_meta[anchor]" value="<?php if( isset($term_meta['anchor']) && $term_meta['anchor'] != '' ){ echo esc_attr($term_meta['anchor']); } ?>">
        </td>
    </tr>
    <tr class="form-field">
        <th scope="row" valign="top">
            <label for="shortcode"><?php esc_html_e('Effect on header (dark/light style)', 'edgt_core'); ?></label>
        </th>
        <td>
            <select name="term_meta[header_effect]" id="term_meta[header_effect]">
                <option <?php if( $term_meta['header_effect'] == 'no' ){ echo "selected='selected'"; } ?> value="no">No</option>
                <option <?php if( $term_meta['header_effect'] == 'yes' ){ echo "selected='selected'"; } ?> value="yes">Yes</option>
            </select>
        </td>
    </tr>
    <tr class="form-field">
        <th scope="row" valign="top">
            <label for="shortcode"><?php esc_html_e('Insert Passepartout in % ', 'edgt_core'); ?></label>
        </th>
        <td>
            <input type="text" name="term_meta[paspartu]" id="term_meta[paspartu]" value="<?php if( isset($term_meta['paspartu']) && $term_meta['paspartu'] != '' ){ echo esc_attr($term_meta['paspartu']); } ?>">
        </td>
    </tr>
    <tr class="form-field">
        <th scope="row" valign="top">
            <label for="shortcode"><?php esc_html_e('Parallax effect', 'edgt_core'); ?></label>
        </th>
        <td>
            <select name="term_meta[slider_parallax_effect]" id="term_meta[slider_parallax_effect]">
                <option <?php if( isset($term_meta['slider_parallax_effect']) && $term_meta['slider_parallax_effect'] == 'yes' ){ echo "selected='selected'"; } ?> value="yes">Yes</option>
                <option <?php if( isset($term_meta['slider_parallax_effect']) && $term_meta['slider_parallax_effect'] == 'no' ){ echo "selected='selected'"; } ?> value="no">No</option>
            </select>
        </td>
    </tr>
    <tr class="form-field">
        <th scope="row" valign="top">
            <label for="shortcode"><?php esc_html_e('Auto start', 'edgt_core'); ?></label>
        </th>
        <td>
            <select name="term_meta[auto_start]" id="term_meta[auto_start]">
                <option <?php if( isset($term_meta['auto_start']) && $term_meta['auto_start'] == 'yes' ){ echo "selected='selected'"; } ?> value="yes">Yes</option>
                <option <?php if( isset($term_meta['auto_start']) && $term_meta['auto_start'] == 'no' ){ echo "selected='selected'"; } ?> value="no">No</option>
            </select>
        </td>
    </tr>
    <tr class="form-field">
        <th scope="row" valign="top">
            <label for="shortcode"><?php esc_html_e('Slides Animation', 'edgt_core'); ?></label>
        </th>
        <td>
            <select name="term_meta[animation_type]" id="term_meta[animation_type]">
                <option <?php if( isset($term_meta['animation_type']) && $term_meta['animation_type'] == 'slide' ){ echo "selected='selected'"; } ?> value="slide">Slide Horizontally</option>
                <option <?php if( isset($term_meta['animation_type']) && $term_meta['animation_type'] == 'slide-vertical-up' ){ echo "selected='selected'"; } ?> value="slide-vertical-up">Slide Vertically - Up</option>
                <option <?php if( isset($term_meta['animation_type']) && $term_meta['animation_type'] == 'slide-vertical-down' ){ echo "selected='selected'"; } ?> value="slide-vertical-down">Slide Vertically - Down</option>
                <option <?php if( isset($term_meta['animation_type']) && $term_meta['animation_type'] == 'slide-cover' ){ echo "selected='selected'"; } ?> value="slide-cover">Slide - Cover Previous Slide</option>
                <option <?php if( isset($term_meta['animation_type']) && $term_meta['animation_type'] == 'slide-peek' ){ echo "selected='selected'"; } ?> value="slide-peek">Slide - Peek Into Next Slide</option>
                <option <?php if( isset($term_meta['animation_type']) && $term_meta['animation_type'] == 'fade' ){ echo "selected='selected'"; } ?> value="fade">Fade</option>
            </select>
        </td>
    </tr>
    <tr class="form-field">
        <th scope="row" valign="top">
            <label for="shortcode"><?php esc_html_e('Time between slide transitions in milliseconds', 'edgt_core'); ?></label>
        </th>
        <td>
            <input type="text" name="term_meta[animation_timeout]" id="term_meta[animation_timeout]" value="<?php if( isset($term_meta['animation_timeout']) && $term_meta['animation_timeout'] != '' ){ echo esc_attr($term_meta['animation_timeout']); } ?>">
        </td>
    </tr>
    <tr class="form-field">
        <th scope="row" valign="top">
            <label for="shortcode"><?php esc_html_e('Slider height in px', 'edgt_core'); ?></label>
        </th>
        <td>
            <input type="text" name="term_meta[height]" id="term_meta[height]" value="<?php if( isset($term_meta['height']) && $term_meta['height'] != '' ){ echo esc_attr($term_meta['height']); } ?>">
        </td>
    </tr>
    <tr class="form-field">
        <th scope="row" valign="top">
            <label for="shortcode"><?php esc_html_e('Responsive height', 'edgt_core'); ?></label>
        </th>
        <td>
            <select name="term_meta[responsive_height]" id="term_meta[responsive_height]">
                <option <?php if( isset($term_meta['responsive_height']) && $term_meta['responsive_height'] == 'yes' ){ echo "selected='selected'"; } ?> value="yes">Yes</option>
                <option <?php if( isset($term_meta['responsive_height']) && $term_meta['responsive_height'] == 'no' ){ echo "selected='selected'"; } ?> value="no">No</option>
            </select>
        </td>
    </tr>
    <tr class="form-field">
        <th scope="row" valign="top">
            <label for="shortcode"><?php esc_html_e('Show Arrows Navigation', 'edgt_core'); ?></label>
        </th>
        <td>
            <select name="term_meta[show_navigation_arrows]" id="term_meta[show_navigation_arrows]">
                <option <?php if( isset($term_meta['show_navigation_arrows']) && $term_meta['show_navigation_arrows'] == 'no' ){ echo "selected='selected'"; } ?> value="no">No</option>
                <option <?php if( isset($term_meta['show_navigation_arrows']) && $term_meta['show_navigation_arrows'] == 'yes' ){ echo "selected='selected'"; } ?> value="yes">Yes</option>
            </select>
        </td>
    </tr>
    <tr class="form-field">
        <th scope="row" valign="top">
            <label for="shortcode"><?php esc_html_e('Show Custom Arrows Navigation', 'edgt_core'); ?></label>
        </th>
        <td>
            <select name="term_meta[show_custom_navigation_arrows]" id="term_meta[show_custom_navigation_arrows]">
                <option <?php if( isset($term_meta['show_custom_navigation_arrows']) && $term_meta['show_custom_navigation_arrows'] == 'yes' ){ echo "selected='selected'"; } ?> value="yes">Yes</option>
                <option <?php if( isset($term_meta['show_custom_navigation_arrows']) && $term_meta['show_custom_navigation_arrows'] == 'no' ){ echo "selected='selected'"; } ?> value="no">No</option>
            </select>
        </td>
    </tr>
    <tr class="form-field">
        <th scope="row" valign="top">
            <label for="shortcode"><?php esc_html_e('Arrows Navigation Skin', 'edgt_core'); ?></label>
        </th>
        <td>
            <select name="term_meta[navigation_arrows_skin]" id="term_meta[navigation_arrows_skin]">
                <option <?php if( isset($term_meta['navigation_arrows_skin']) && $term_meta['navigation_arrows_skin'] == '' ){ echo "selected='selected'"; } ?> value="">Default</option>
	            <option <?php if( isset($term_meta['navigation_arrows_skin']) && $term_meta['navigation_arrows_skin'] == 'light' ){ echo "selected='selected'"; } ?> value="light">Light</option>
                <option <?php if( isset($term_meta['navigation_arrows_skin']) && $term_meta['navigation_arrows_skin'] == 'dark' ){ echo "selected='selected'"; } ?> value="dark">Dark</option>
            </select>
        </td>
    </tr>
    <tr class="form-field">
        <th scope="row" valign="top">
            <label for="shortcode"><?php esc_html_e('Show Bullets Navigation', 'edgt_core'); ?></label>
        </th>
        <td>
            <select name="term_meta[show_navigation_circles]" id="term_meta[show_navigation_circles]">
                <option <?php if( isset($term_meta['show_navigation_circles']) && $term_meta['show_navigation_circles'] == 'yes' ){ echo "selected='selected'"; } ?> value="yes">Yes</option>
                <option <?php if( isset($term_meta['show_navigation_circles']) && $term_meta['show_navigation_circles'] == 'no' ){ echo "selected='selected'"; } ?> value="no">No</option>
            </select>
        </td>
    </tr>
    <tr class="form-field">
        <th scope="row" valign="top">
            <label for="shortcode"><?php esc_html_e('Bullets Navigation Position', 'edgt_core'); ?></label>
        </th>
        <td>
            <select name="term_meta[navigation_circles_position]" id="term_meta[navigation_circles_position]">
                <option <?php if( isset($term_meta['navigation_circles_position']) && $term_meta['navigation_circles_position'] == 'right' ){ echo "selected='selected'"; } ?> value="right">Right</option>
                <option <?php if( isset($term_meta['navigation_circles_position']) && $term_meta['navigation_circles_position'] == 'center' ){ echo "selected='selected'"; } ?> value="center">Center</option>
                <option <?php if( isset($term_meta['navigation_circles_position']) && $term_meta['navigation_circles_position'] == 'left' ){ echo "selected='selected'"; } ?> value="left">Left</option>
            </select>
        </td>
    </tr>
	<tr class="form-field">
		<th scope="row" valign="top">
			<label for="shortcode"><?php esc_html_e('Bullets Navigation Skin', 'edgt_core'); ?></label>
		</th>
		<td>
			<select name="term_meta[navigation_circles_skin]" id="term_meta[navigation_circles_skin]">
				<option <?php if( isset($term_meta['navigation_circles_skin']) && $term_meta['navigation_circles_skin'] == '' ){ echo "selected='selected'"; } ?> value="">Default</option>
				<option <?php if( isset($term_meta['navigation_circles_skin']) && $term_meta['navigation_circles_skin'] == 'light' ){ echo "selected='selected'"; } ?> value="light">Light</option>
				<option <?php if( isset($term_meta['navigation_circles_skin']) && $term_meta['navigation_circles_skin'] == 'dark' ){ echo "selected='selected'"; } ?> value="dark">Dark</option>
			</select>
		</td>
	</tr>

<?php
}

function edgt_save_taxonomy_custom_fields( $term_id ) {
    if ( isset( $_POST['term_meta'] ) ) {
        $t_id = $term_id;
        $term_meta = get_option( "taxonomy_term_$t_id" );
        $cat_keys = array_keys( $_POST['term_meta'] );
        foreach ( $cat_keys as $key ){
            if ( isset( $_POST['term_meta'][$key] ) ){
                $term_meta[$key] = $_POST['term_meta'][$key];
            }
        }
        update_option( "taxonomy_term_$t_id", $term_meta );
    }
}

add_action( 'slides_category_edit_form_fields', 'edgt_slides_category_taxonomy_custom_fields', 10, 2 );
add_action( 'edited_slides_category', 'edgt_save_taxonomy_custom_fields', 10, 2 );



add_filter("manage_edit-slides_category_columns", 'edgt_theme_columns');
function edgt_theme_columns($theme_columns) {
    $new_columns = array(
        'cb' => '<input type="checkbox" />',
        'name' => esc_html__('Name', 'edgt_core'),
        'shortcode' => esc_html__('Shortcode', 'edgt_core'),
        'slug' => esc_html__('Slug', 'edgt_core'),
        'posts' => esc_html__('Posts', 'edgt_core')
    );
    return $new_columns;
}

add_filter("manage_slides_category_custom_column", 'edgt_manage_theme_columns', 10, 3);
function edgt_manage_theme_columns($out, $column_name, $theme_id) {
    $theme = get_term($theme_id, 'slides_category');
    switch ($column_name) {
        case 'shortcode':
            $out .= "[edgtf_slider slider='".$theme->slug."']";
            break;

        default:
            break;
    }
    return $out;
}

?>