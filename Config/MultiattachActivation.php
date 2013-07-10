<?php

App::uses('CakeSchema', 'Model');
App::uses('ConnectionManager', 'Model');

/**
 * Multiattach Activation
 *
 * Activation class for Example plugin.
 * This is optional, and is required only if you want to perform tasks when your plugin is activated/deactivated.
 *
 * @package  Croogo
 * @author   Fahad Ibnay Heylaal <contact@fahad19.com>
 * @license  http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link http://www.croogo.org
 */
class MultiattachActivation {

/**
 * onActivate will be called if this returns true
 *
 * @param  object $controller Controller
 * @return boolean
 */
	public function beforeActivation(&$controller) {
		return true;
	}

/**
 * Called after activating the plugin in ExtensionsPluginsController::admin_toggle()
 *
 * @param object $controller Controller
 * @return void
 */
	public function onActivation(&$controller) {

		$tableName = 'multiattachments';
		$pluginName = 'Multiattach';
		$db = ConnectionManager::getDataSource('default');
		$tables = $db->listSources();

		// Revisar si existe la tabla, si no usar el schema que se proporciona para generarla
		if (!in_array(strtolower($tableName), $tables)) {
			$schema = & new CakeSchema(array(
						'name' => $pluginName,
						'path' => APP . 'Plugin' . DS . $pluginName . DS . 'Config' . DS . 'schema',
							)
			);
			$schema = $schema->load();
			foreach ($schema->tables as $table => $fields) {
				$create = $db->createSchema($schema, $table);
				try {
					$db->execute($create);
				} catch (PDOException $e) {
					die(__('Could not create table: %s', $e->getMessage()));
				}
			}
		}
		// ACL: set ACOs with permissions
		$controller->Croogo->addAco('Multiattach');
		$controller->Croogo->addAco('Multiattach/Multiattach');
		$controller->Croogo->addAco('Multiattach/Multiattach/getLatest', array('registered', 'public'));
		$controller->Croogo->addAco('Multiattach/Multiattach/admin_add_web');
		$controller->Croogo->addAco('Multiattach/Multiattach/admin_add');
		$controller->Croogo->addAco('Multiattach/Multiattach/displayFile', array('registered', 'public'));
		$controller->Croogo->addAco('Multiattach/Multiattach/viewFile', array('registered', 'public'));
		$controller->Croogo->addAco('Multiattach/Multiattach/admin_AjaxKillAttachmentJson');
		$controller->Croogo->addAco('Multiattach/Multiattach/admin_AjaxGetAttachmentJson');
		$controller->Croogo->addAco('Multiattach/Multiattach/admin_PostCommentAttachmentJson');
		$controller->Croogo->addAco('Multiattach/Multiattach/admin_settings');
		$controller->Setting->write('Multiattach.remove_settings', '0', array('description' => 'Remove settings on deactivate', 'editable' => 1));
		$mime = array(
			'image/gif',
			'image/x-xbitmap',
			'image/gi_',
			'image/jpeg',
			'image/pjpeg',
			'image/jpg',
			'image/jp_',
			'application/jpg',
			'application/x-jpg',
			'image/pjpeg',
			'image/pipeg',
			'image/vnd.swiftview-jpeg',
			'image/x-xbitmap',
			'image/png',
			'application/png',
			'application/x-png',
			'application/pdf',
			'application/x-pdf',
			'application/acrobat',
			'applications/vnd.pdf',
			'text/pdf',
			'text/x-pdf',
			'image/bmp',
			'image/x-bmp',
			'image/x-bitmap',
			'image/x-xbitmap',
			'image/x-win-bitmap',
			'image/x-windows-bmp',
			'image/ms-bmp',
			'image/x-ms-bmp',
			'application/bmp',
			'application/x-bmp',
			'application/x-win-bitmap',
			'image/bmp',
			'image/x-bmp',
			'image/x-bitmap',
			'image/x-xbitmap',
			'image/x-win-bitmap',
			'image/x-windows-bmp',
			'image/ms-bmp',
			'image/x-ms-bmp',
			'application/bmp',
			'application/x-bmp',
			'application/x-win-bitmap',
			'text/plain',
			'application/txt',
			'browser/internal',
			'text/anytext',
			'widetext/plain',
			'widetext/paragraph',
		);
		$thumbnail = array(
			'thumb:150',
			'square-thumb:150,150',
			'normal:0,0',
		);
		$mime = json_encode($mime);
		$thumbnail = json_encode($thumbnail);
		$controller->Setting->write('Multiattach.allowed_mime__json', $mime, array('description' => 'Allowed MIME types for upload', 'editable' => 0));
		$controller->Setting->write('Multiattach.thumbnail_sizes__json', $thumbnail, array('description' => 'Defined alias for thumbnail sizes', 'editable' => 0));
	}

/**
 * onDeactivate will be called if this returns true
 *
 * @param  object $controller Controller
 * @return boolean
 */
	public function beforeDeactivation(&$controller) {
		return true;
	}

/**
 * Called after deactivating the plugin in ExtensionsPluginsController::admin_toggle()
 *
 * @param object $controller Controller
 * @return void
 */
	public function onDeactivation(&$controller) {
		// ACL: remove ACOs with permissions
		$controller->Croogo->removeAco('Multiattach');

		// Remove Allowed MIME types
		if (Configure::read('Multiattach.remove_settings') == '1') {
			$controller->Setting->deleteKey('Multiattach.allowed_mime__json');
			$controller->Setting->deleteKey('Multiattach.thumbnail_sizes__json');
		}
	}

}
