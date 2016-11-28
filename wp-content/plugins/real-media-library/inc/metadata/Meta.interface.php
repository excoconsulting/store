<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Fast implement Copy&Paste this:
 * 
 public function content($content, $folder) {
     // Your Code
 }
 public function save($response, $folder) {
     // Your Code
 }
 public function scripts() {
     // Your Code
 }
 */
interface iRML_Meta {
    /**
     * Return modified content for the meta box.
     * 
     * <strong>Note:</strong> If you want to use a more complex content
     * in a meta table use something like this:
     * 
        <tr>
            <th scope="row">Medium size</th>
            <td><fieldset>
                <legend class="screen-reader-text"><span>Medium size</span></legend>
                <label for="medium_size_w">Max Width</label>
                <input name="medium_size_w" type="number" step="1" min="0" id="medium_size_w" value="300" class="small-text">
                <label for="medium_size_h">Max Height</label>
                <input name="medium_size_h" type="number" step="1" min="0" id="medium_size_h" value="300" class="small-text">
            </fieldset></td>
        </tr>
     * 
     * Please note: To group the different meta boxes use this table
     * row at the end of your content:
     * <tr class="rml-meta-margin"></tr>
     * 
     * @param $content the HTML formatted string for the dialog
     * @param $folder a RML_Folder object (can be null!!!!!)
     * @return modified $content
     * @filter RML/Folder/Meta/Content
     * @see this::save
     */
    public function content($content, $folder);
    
    /**
     * Save the infos.
     * 
     * @param $response array of errors and successful data. Add an
     *                  error to the array to show on the frontend dialog. Add an
     *                  successful data to work with it in the javascript.
     *        $response["errors"] = array() => push to it!
     *        $response["data"] = array() => push to it!
     * @param $folder a RML_Folder object (can be null!!!!!)
     * @filter RML/Folder/Meta/Save
     * @see this::content
     * @see meta.js
     */
    public function save($response, $folder);
    
    /**
     * Enqueue scripts and styles for this meta box.
     * 
     * Note: This resources are only loaded in the
     * media library.
     * 
     * @action RML/Backend/Scripts/MediaLibrary
     */
    public function scripts();
}