<?php

Croogo::hookRoutes('Multiattach');
Croogo::hookComponent('Nodes', 'Multiattach.Multiattaches');
Croogo::hookBehavior('Node', 'Multiattach.Multiattach', array());

//Croogo::hookAdminTab('Nodes/admin_add', 'Attachments', 'Multiattach.admin_tab_node');
Croogo::hookAdminTab('Nodes/admin_edit', 'Attachments', 'Multiattach.admin_tab_node');

CroogoNav::add('settings.children.multiattach',array(
	'title' => __('Multiattach'),
	'url' => array('plugin' => 'Multiattach', 'controller' => 'Multiattach', 'action' => 'settings'),
	'access' => array('admin')
));