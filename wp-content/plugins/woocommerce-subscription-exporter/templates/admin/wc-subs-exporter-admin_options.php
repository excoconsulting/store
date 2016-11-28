<p>
  <?php _e( 'Configure the export options below.', 'wc-subs-exporter' ); ?>
</p>
<form method="post" action="<?php echo add_query_arg( array( 'failed' => null, 'empty' => null ) ); ?>" id="postform">
  <div id="poststuff">
    <div class="postbox" id="export-options">
      <h3>
        <?php _e( 'Options for Subscriptions Exporter', 'wc-subs-exporter' ); ?>
      </h3>
      <div class="inside">
		<p class="description">
          <?php _e( 'Configure the options for the data exportation.', 'wc-subs-exporter' ); ?>
        </p>	  
        <table class="form-table">
          <tr>
            <th> <label for="delimiter">
              <?php _e( 'Field delimiter', 'wc-subs-exporter' ); ?>
              </label>
            </th>
            <td><input type="text" id="delimiter" name="delimiter" value="<?php echo $delimiter; ?>" size="1" maxlength="1" class="text" />
              <p class="description">
                <?php _e( 'The field delimiter is the character separating each cell in your CSV. This is typically the \',\' (comma) character.', 'woo_pc' ); ?>
              </p></td>
          </tr>
          <tr>
            <th> <label for="category_separator">
              <?php _e( 'Category separator', 'wc-subs-exporter' ); ?>
              </label>
            </th>
            <td><input type="text" id="category_separator" name="category_separator" value="<?php echo $category_separator; ?>" size="1" class="text" />
              <p class="description">
                <?php _e( 'The Product Category separator allows you to assign individual Products to multiple Product Categories/Tags/Images at a time. It is suggested to use the \'|\' (vertical pipe) character between each item. For instance: <code>Clothing|Mens|Shirts</code>.', 'wc-subs-exporter' ); ?>
              </p></td>
          </tr>
          <tr>
            <th> <label for="bom">
              <?php _e( 'Add BOM character', 'wc-subs-exporter' ); ?>
              : </label>
            </th>
            <td><select id="bom" name="bom">
                <option value="1"<?php selected( $bom, 1 ); ?>>
                <?php _e( 'Yes', 'wc-subs-exporter' ); ?>
                </option>
                <option value="0"<?php selected( $bom, 0 ); ?>>
                <?php _e( 'No', 'wc-subs-exporter' ); ?>
                </option>
              </select>
              <p class="description">
                <?php _e( 'Mark the CSV file as UTF8 by adding a byte order mark (BOM) to the export, useful for non-English character sets.', 'wc-subs-exporter' ); ?>
              </p></td>
          </tr>
          <tr>
            <th> <label for="escape_formatting">
              <?php _e( 'Field escape formatting', 'wc-subs-exporter' ); ?>
              : </label>
            </th>
            <td><label>
              <input type="radio" name="escape_formatting" value="all"<?php checked( $escape_formatting, 'all' ); ?> />
              &nbsp;
              <?php _e( 'Escape all fields', 'wc-subs-exporter' ); ?>
              </label>
              <br />
              <label>
              <input type="radio" name="escape_formatting" value="excel"<?php checked( $escape_formatting, 'excel' ); ?> />
              &nbsp;
              <?php _e( 'Escape fields as Excel would', 'wc-subs-exporter' ); ?>
              </label>
              <p class="description">
                <?php _e( 'Choose the field escape format that suits your spreadsheet software (e.g. Excel).', 'wc-subs-exporter' ); ?>
              </p></td>
          </tr>
          <tr>
            <th> <label for="offset">
              <?php _e( 'Volume offset', 'wc-subs-exporter' ); ?>
              </label>
            </th>
            <td><input type="text" id="offset" name="offset" value="<?php echo $offset; ?>" size="5" class="text" />
              <p class="description">
                <?php _e( 'Volume offset allows for partial exporting of a dataset, to be used in conjuction with Limit volume option above. By default this is not used and is left empty.', 'wc-subs-exporter' ); ?>
              </p></td>
          </tr>
          <tr>
            <th> <label for="limit_volume">
              <?php _e( 'Limit volume', 'wc-subs-exporter' ); ?>
              </label>
            </th>
            <td><input type="text" id="limit_volume" name="limit_volume" value="<?php echo $limit_volume; ?>" size="5" class="text" />
              <p class="description">
                <?php _e( 'Limit volume allows for partial exporting of a dataset. This is useful when encountering timeout and/or memory errors during the default export. By default this is not used and is left empty.', 'wc-subs-exporter' ); ?>
              </p></td>
          </tr>
          <tr>
            <th> <label for="encoding">
              <?php _e( 'Character encoding', 'wc-subs-exporter' ); ?>
              : </label>
            </th>
            <td><select id="encoding" name="encoding">
                <option>
                <?php _e( 'System default', 'wc-subs-exporter' ); ?>
                </option>
                <?php foreach( $file_encodings as $key => $chr ) { ?>
                <option value="<?php echo $chr; ?>"<?php selected( $encoding, $chr ); ?>><?php echo $chr; ?></option>
                <?php } ?>
              </select>
            </td>
          </tr>
          <tr>
            <th> <label for="save_csv_archive">
              <?php _e( 'Save CSV archive', 'wc-subs-exporter' ); ?>
              </label>
            </th>
            <td><select id="save_csv_archive" name="save_csv_archive">
                <option value="1"<?php selected( $save_csv_archive, 1 ); ?>>
                <?php _e( 'Yes', 'wc-subs-exporter' ); ?>
                </option>
                <option value="0"<?php selected( $save_csv_archive, 0 ); ?>>
                <?php _e( 'No', 'wc-subs-exporter' ); ?>
                </option>
              </select>
			  <p class="description">
                <?php _e( 'Save the generated archive for later download.', 'wc-subs-exporter' ); ?>
              </p>
            </td>
          </tr>
          <?php if ( !ini_get( 'safe_mode' ) ) { ?>
          <tr>
            <th> <label for="timeout">
              <?php _e( 'Script timeout:', 'wc-subs-exporter' ); ?>
              </label>
            </th>
            <td><select id="timeout" name="timeout">
                <option value="600"<?php selected( $timeout, 600 ); ?>><?php echo sprintf( __( '%s minutes', 'wc-subs-exporter' ), 10 ); ?></option>
                <option value="1800"<?php selected( $timeout, 1800 ); ?>><?php echo sprintf( __( '%s minutes', 'wc-subs-exporter' ), 30 ); ?></option>
                <option value="3600"<?php selected( $timeout, 3600 ); ?>><?php echo sprintf( __( '%s hour', 'wc-subs-exporter' ), 1 ); ?></option>
                <option value="0"<?php selected( $timeout, 0 ); ?>>
                <?php _e( 'Unlimited', 'wc-subs-exporter' ); ?>
                </option>
              </select>
              <p class="description">
                <?php _e( 'Script timeout defines how long WooCommerce Subscriptions Exporter is \'allowed\' to process your CSV file, once the time limit is reached the export process halts.', 'wc-subs-exporter' ); ?>
              </p></td>
          </tr>
          <?php } ?>
        </table>
        <p class="submit">
          <input type="submit" value="<?php _e( 'Save options', 'wc-subs-exporter' ); ?>" class="button-primary" />
        </p>
      </div>
    </div>
    <!-- .postbox -->
  </div>
  <!-- #poststuff -->
  <input type="hidden" name="action" value="save-options" />
</form>
