/* global jQuery wp RMLWpIs */

/**
 * Change the AllInOneTree for the normal based WP List Table.
 */
window.rml.hooks.register("afterInit/list", function($) {
    window.rml.library.updateRestrictions($(this).allInOneTree("active"), $(this));
});