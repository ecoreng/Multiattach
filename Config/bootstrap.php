<?php
/**
 * Routes
 *
 * example_routes.php will be loaded in main app/config/routes.php file.
 */
Croogo::hookRoutes('Multiattach');

/**
 * Component
 *
 * This plugin's Example component will be loaded in Node
 */
// if croogo < 1.5 then the next line should be uncommented
//Croogo::hookComponent('Node', 'Multiattach.Multiattach');
// else the next line should be uncommented
Croogo::hookComponent('Nodes', 'Multiattach.Multiattach');


/**
 * Behavior
 *
 * This plugin's Example behavior will be attached whenever Node model is loaded.
 */
Croogo::hookBehavior('Node', 'Multiattach.Multiattach', array());


/**
 * Admin tab
 *
 * When adding/editing Content (Nodes),
 * an extra tab with title 'Example' will be shown with markup generated from the plugin's admin_tab_node element.
 *
 * Useful for adding form extra form fields if necessary.
 */
 
// This is going to be enabled when i get an AJAX interface so when the page updates you dont lose the info you just entered in the node
//Croogo::hookAdminTab('Nodes/admin_add', 'Attachments', 'Multiattach.admin_tab_node');
Croogo::hookAdminTab('Nodes/admin_edit', 'Attachments', 'multiattach.admin_tab_node');
