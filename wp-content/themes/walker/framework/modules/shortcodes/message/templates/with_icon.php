<?php
$icon_html = walker_edge_icon_collections()->renderIcon($icon, $icon_pack);
?>
<div class="edgtf-message-icon-holder" <?php echo walker_edge_get_inline_style($icon_attributes['style']); ?>><?php print $icon_html; ?></div>