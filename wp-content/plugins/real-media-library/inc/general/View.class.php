<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class RML_View {
    private $structure;
    
    public function __construct($structure) {
        $this->structure = $structure;
    }
    
    /**
     * Gets a HTML formatted string for <option>.
     * 
     * @recursive
     */
    public function optionsHTML($selected = -1, $tree = null, $slashed = "", $spaces = "--", $useAll = true, $disabled = null) {
        $return = '';
        if ($disabled === null) {
            $disabled = array(RML_TYPE_COLLECTION);
        }
        
        if ($tree == null) {
            $tree = $this->structure->getTree();
            if ($useAll) {
                $return .= '<option value="" ' . $this->optionsSelected($selected, "") . '
                                    ' . ((in_array(RML_TYPE_ALL, $disabled)) ? 'disabled="disabled"' : '') . '
                                    >' . __('All', RML_TD) . '</option>';
            }
            $return .= '<option value="-1" ' . $this->optionsSelected($selected, "-1") . '
                            data-slug="/"
                            ' . ((in_array(RML_TYPE_ROOT, $disabled)) ? 'disabled="disabled"' : '') . '
                            data-id="-1">' . __('Unorganized pictures', RML_TD) . '</option>';
        }
        
        if(!is_null($tree) && count($tree) > 0) {
            foreach($tree as $parent) {
                $return .= '<option value="' . $parent->id . '" ' . $this->optionsSelected($selected, $parent->id) . '
                                    data-slug="/' . $parent->absolutePath() . '"
                                    data-id="' . $parent->id . '"
                                    ' . ((in_array($parent->type, $disabled)) ? 'disabled="disabled"' : '') . '>
                                    ' . $spaces . '&nbsp;' . $parent->name . '
                            </option>';
                
                if (isset($parent->children) &&
                    is_array($parent->children) &&
                    count($parent->children) > 0
                    ) {
                    $return .= $this->optionsHTML($selected, $parent->children, $slashed, $spaces . "--", $useAll, $disabled);
                }
            }
        }
        
        return $return;
    }
    
    /**
     * Gets the html string for the left tree.
     * 
     * @param $selected the current selected id ("" = All files, -1 = Root)
     * @param $tree the tree array, default is the structure tree
     * @param $list the list id (for custom lists)
     * @recursive
     * @uses this::createNode
     */
    public function treeHTML($selected = -1, $tree = null, $list = "") {
        $return = '';
        
        // First item
        if ($tree == null) {
            $tree = $this->structure->getTree();
        }
        
        // Create list
        $return .= '<ul>';
        if(!is_null($tree) && count($tree) > 0) {
            foreach($tree as $parent) { // the parent here is the actual folder
                /**
                 * Filter the li classes
                 * 
                 * @see RML_Permissions::liClass
                 */
                $liClasses = apply_filters('RML/Folder/TreeNodeLi/Class', array(), $parent);
            
                // Create the output
                $return .= '<li id="list_' . $parent->id . '" class="' . implode(' ', $liClasses) . '">';
                $return .= $this->createNode($parent, $parent->id, $parent->type, "/" . $parent->absolutePath(), $parent->name, $parent->getCnt(),
                                $selected, array(), $list);
                
                // Recusrive functionality call
                if (isset($parent->children) &&
                    is_array($parent->children) &&
                    count($parent->children) > 0
                    ) {
                    $return .= $this->treeHTML($selected, $parent->children, $list);
                }else{
                    $return .= '<ul></ul>';
                }
                
                $return .= '</li>';
            }
        }
        $return .= '</ul>';
        
        return $return;
    }
    
    /**
     * Create a <a>-Node for the treeHTML function.
     * 
     * @param $obj the folder object
     * @param $fid the folder ID
     * @param $type the type of the folder
     * @param $slug the slug for the node
     * @param $name the name for the node
     * @param $cnt the shown count for the node
     * @param $currentFid the current selected folder ID for the this::treeActive function
     * @param $classes an array of classes for this node
     * @param $list the list type ("" is the list in the media library,
     *              otherwise it is used for the customList ID)
     * @return formatted HTML string
     * 
     * @see this::treeHTML
     * @uses this::treeHref
     * @uses this::treeActive
     * 
     * @filter RML/Folder/TreeNode/Icon (Parameters: func_get_args())
     * @filter RML/Folder/TreeNode/Class (Parameters: func_get_args())
     * @filter RML/Folder/TreeNode/Content (Parameters: func_get_args())
     */
    public function createNode($obj, $fid, $type, $slug, $name, $cnt, $currentFid, $classes = array(), $list = "") {
        // Get href
        $href = $this->treeHref($fid, $type, $list);
        $icon = $this->typeIcon($type, $fid);

        /**
         * Get classes for this tree node
         */
        $funcArgs = func_get_args();
        $classes = implode(
                    ' ',
                    // Please only append to this array
                    // The first item must be the rml-fid-%
                    apply_filters('RML/Folder/TreeNode/Class',
                        array_merge(array(
                            "rml-fid-" . $fid,
                            "rml-type-" . $type,
                            $this->treeActive($currentFid, $fid)
                        ), $classes), $funcArgs)
                );
        
        /**
         * The output
         */
        // Create attributes
        $slug = empty($slug) ? "" : 'data-slug="' . $slug . '"';
        
        $restrictions = $obj !== null ? $obj->getRowData("restrictions") : "";
        
        // The result
        return '
        <a href="' . $href . '" class="' . $classes . '" ' . $slug . ' data-aio-type="' . $type . '" data-aio-id="' . $fid . '" data-restrictions="' . $restrictions . '">
            ' . $icon . '
            <div class="aio-node-name">' . $name . '</div>
            ' . apply_filters('RML/Folder/TreeNode/Content', "", $funcArgs) . '
            <span class="aio-cnt aio-cnt-' .  $cnt . '">' . $cnt . '</span>
        </a>
        ';
    }
    
    /**
     * Get type name for a given folder type.
     * 
     * @param $type the type of folder
     * @param $fid the folder ID (can be null)
     * @return string
     */
    public function typeName($type, $fid = null) {
        $name = "";
        if ($type == RML_TYPE_COLLECTION) {
            $name = __('Collection', RML_TD);
        }else if ($type == RML_TYPE_GALLERY) {
            $name = __('Gallery', RML_TD);
        }else if ($type == RML_TYPE_ROOT) {
            $name = __('Uncategorized', RML_TD);
        }else{
            // Normal folder
            $name = apply_filters("RML/Folder/Type/Name", __('Folder', RML_TD), $type, $fid);
        }
        return $name;
    }
    
    /**
     * Get icon for a given folder type.
     * 
     * @param $type the type of folder
     * @param $fid the folder ID (can be null)
     * @return <i> tag with font awesome icon
     */
    public function typeIcon($type, $fid = null) {
        $icon = "";
        // Get icon for the type
        if ($type == RML_TYPE_ALL) {
            $icon = '<i class="fa fa-files-o"></i>';
        }else if ($type == RML_TYPE_ROOT) {
            $icon = '<i class="fa fa-dot-circle-o"></i>';
        }else if ($type == RML_TYPE_COLLECTION) {
            $icon = '<i class="mwf-collection"></i>';
        }else if ($type == RML_TYPE_GALLERY) {
            $icon = '<i class="mwf-gallery"></i>';
        }else{
            $icon = apply_filters("RML/Folder/Type/Icon", '<i class="fa fa-folder-open"></i><i class="fa fa-folder"></i>', $type, $fid);
        }
        return $icon;
    }
    
    /**
     * Get description for a given folder type.
     * 
     * @param $type the type of folder
     * @param $fid the folder ID (can be null)
     * @return string
     */
    public function typeDescription($type, $fid = null) {
        $desc = "";
        // Get icon for the type
        if ($type == RML_TYPE_COLLECTION) {
            $desc = __('A collection can contain no files. But you can create there other collections and <strong>galleries</strong>.', RML_TD);
        }else if ($type == RML_TYPE_GALLERY) {
            $desc = __('A gallery can contain only images. If you want to display a gallery go to a post and have a look at the visual editor buttons.', RML_TD);
        }else if ($type == RML_TYPE_ROOT) {
            $desc = __('Uncategorized is the same as a root folder. Here you can find all files which are not assigned to a folder.', RML_TD);
        }else{
            // Normal folder
            $desc = apply_filters("RML/Folder/Type/Description", __('A folder can contain every type of file or a collection, but no gallery.', RML_TD), $type, $fid);
        }
        return $desc;
    }
    
    public function optionsSelected($selected, $value) {
        if ($selected == $value) {
            return 'selected="selected"';
        }else{
            return '';
        }
    }
    
    /**
     * Create link for a tree node.
     * 
     * @param $id the folder id
     * @param $type the type of the folder
     * @filter RML/Folder/TreeNode/Href
     */
    public function treeHref($id, $type, $list = "") {
        $query = $_GET;
        $query['rml_folder'] = $type === RML_TYPE_ALL ? "" : $id;
        $query_result = http_build_query(apply_filters("RML/Folder/TreeNode/Href", $query, $id, $type, $list));
        return admin_url('upload.php?' . $query_result);
    }
    
    public function treeActive($selected, $value) {
        if ($selected == $value) {
            return 'active';
        }else{
            return '';
        }
    }
        
    /**
     * Get array for the javascript backbone view.
     * The private namesSlugArray is for caching purposes
     * and can be resetted with the given function.
     */
    private $namesSlugArrayCache = null;

    public function namesSlugArray($tree = null, $spaces = "--", $forceReload = false) {
        if ($forceReload || $this->namesSlugArrayCache == null) {
            $result = $this->namesSlugArrayRec($tree, $spaces);
        }else{
            $result = $this->namesSlugArrayCache;
        }
        $this->namesSlugArrayCache = $result;
        return $result;
    }
    
    private function namesSlugArrayRec($tree = null, $spaces = "--") {
        $return = array(
            "names" => array(),
            "slugs" => array(),
            "types" => array()
        );
        
        if ($tree == null) {
            $tree = $this->structure->getTree();
            $return["names"][] = __('Unorganized pictures', RML_TD);
            $return["slugs"][] = -1;
            $return["types"][] = 0;
        }
        
        if(!is_null($tree) && count($tree) > 0) {
            foreach($tree as $parent) {
                $return["names"][] = $spaces . ' ' . $parent->name;
                $return["slugs"][] = $parent->id;
                $return["types"][] = $parent->type;
                
                if (isset($parent->children) &&
                    is_array($parent->children) &&
                    count($parent->children) > 0
                    ) {
                    $append = $this->namesSlugArrayRec($parent->children, $spaces . "--");
                    $return["names"] = array_merge($return["names"], $append["names"]);
                    $return["slugs"] = array_merge($return["slugs"], $append["slugs"]);
                    $return["types"] = array_merge($return["types"], $append["types"]);
                }
            }
        }
        
        return $return;
    }
    
    public function getHTMLBreadcrumbByID($id) {
        $breadcrumb = $this->structure->getBreadcrumbByID($id);
        
        $output = '<i class="fa fa-folder-open"></i>';
        
        if (count($breadcrumb) == 0) {
            return $output . ' ' . __('Unorganized pictures', RML_TD);
        }
        
        for ($i = 0; $i < count($breadcrumb); $i++) {
            $output .= '<span class="folder">' . $breadcrumb[$i]->name . '</span>';
            
            // When not last, insert seperator
            if ($i < count($breadcrumb) - 1) {
                $output .= '<i class="fa fa-chevron-right"></i>';
            }
        }
        
        return $output;
    }
    
    public function getStructure() {
        return $this->structure;
    }
}

?>