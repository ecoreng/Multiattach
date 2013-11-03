<?php

App::uses('AppController', 'Controller');
App::uses('Sanitize', 'Utility');
App::uses('ConnectionManager', 'Model');

/**
 * Multiattach Controller
 *
 * @category Controller
 * @author   Elias Coronado <coso.del.cosito@gmail.com>
 * @license  http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link     http://www.github.com/ecoreng
 */
class MultiattachController extends AppController {

	public $components = array(
		'Multiattach.Multiattaches'
	);
	
	public $name = 'Multiattach';

	public $uses = array('Setting', 'Multiattach.Multiattach');

	public $defaults = array();

	public $pluginPrefix = "Multiattach";

	public function beforeFilter() {
		parent::beforeFilter();
		// This gets all the settings and sets them for views in the variable
		// 'default'. This will be available inside Multiattach views, and
		// $this->defaults can be used in this controller.
		$this->loadModel('Settings.Setting');
		$settings = $this->Setting->find('all', array('conditions' => array('Setting.key LIKE' => $this->pluginPrefix . '.%')));
		$this->defaults = $this->Multiattach->getSettings($settings);
		$this->set('defaults', $this->defaults);
        $this->Security->unlockedActions += array('admin_add');
	}

/**
 * admin_add_web
 * Attach website from url
 * @param string $node
 */
	public function admin_add_web($node = '') {
		$this->components[] = 'Session';
		$this->layout = 'admin_popup';
		if ($this->request->is('post')) {
			switch ($this->request->data["Multiattach"]["step"]) {
				case 1:
					$url2parse = Sanitize::clean($this->request->data["Multiattach"]["url2parse"]);
					$dataArr = $this->Multiattach->parseUrl($url2parse);
					if ($dataArr !== false) {
						$this->set('attachmentData', $dataArr);
						$this->render('Multiattach/WebAttachmentEdit');
					}
					$this->Session->setFlash(__('That is not a valid URL or i couldnt parse it.'));
					break;
				case 2:
					$dataArray = json_decode($this->request->data["Multiattach"]["data"], true);
					$dataArray["description"] = $this->request->data["Multiattach"]["description"];
					$dataArray["title"] = $this->request->data["Multiattach"]["title"];
					$dataJson = json_encode($dataArray);
					$this->Multiattach->create();
					$this->Multiattach->set(array('node_id' => $node, 'filename' => $dataArray['url'], 'comment' => $dataArray['title'], 'mime' => 'application/json', 'content' => $dataJson));
					$this->Multiattach->save();
					$this->render('Multiattach/attachmentReady');
					break;
			}
		}
	}

/**
 * admin_add
 * Upload files and link them to $node
 * @param int $node
 */
	public function admin_add($node = '') {
		$this->helpers[] = "Html";
		$this->components[] = 'Session';
		$this->layout = 'admin_popup';
		if ($this->request->is('post')) {
			switch ($this->Multiattach->uploadFiles($this->request->params['form']['uploads'], $node, $this->defaults['allowed_mime__json']['values'])) {
				case "1":
					$this->Session->setFlash(__('There were some errors in the process of uploading'));
				case "2":
					$this->render('Multiattach/attachmentReady');
					break;
				case "0":
					$this->Session->setFlash(__('Could not upload any file'));
					break;
			}
		}
	}

/**
 * Get $dimension string and try to get a number from that, returns an array with (height,width)
 * @param type $dimension
 * @return array
 */
	protected function _getDimension($dimension) {
		if (array_key_exists($dimension, $this->defaults['thumbnail_sizes__json']['values'])) {
			return explode(',', $this->defaults['thumbnail_sizes__json']['values'][$dimension]);
		} else {
			return array(0, 0, 1);
		}
	}

/**
 * _resizeImage
 * Resizes the image given in $filename to $size (array[width,height]), returns the filename of the resized image
 * @param string $filename
 * @param array $size
 * @return string
 */
	protected function _resizeImage($filename, $size, $node) {
		$cacheDir = 'files' . DS . 'cache';
		//https://gist.github.com/bchapuis/1562272

		$path = $filename;
		$dstW = (int)(isset($size[0])) ? (int)$size[0] : null;
		$dstH = (int)(isset($size[1])) ? (int)$size[1] : null;
		$mult = $dstW + $dstH; // if $mult=0 then it means no resizing;
		$types = array(1 => "gif", "jpeg", "png", "swf", "psd", "wbmp"); // used to determine image type
		$fullpath = APP;
		$url = $fullpath . $path;
		list($w, $h, $type) = getimagesize($url);
		$r = $w / $h;
		if ($dstW != null || $dstH != null) {
			$dstW = (int)(!isset($size[0])) ? $dstH * $r : $size[0];
			$dstH = (int)(!isset($size[1])) ? $dstW / $r : $size[1];
		} else {
			$dstW = (int)$w;
			$dstH = (int)$h;
		}
		$dstR = $dstW / $dstH;
		if ($r > $dstR) {
			$srcW = $h * $dstR;
			$srcH = $h;
			$srcX = ($w - $srcW) / 2;
			$srcY = 0;
		} else {
			$srcW = $w;
			$srcH = $w / $dstR;
			$srcX = 0;
			$srcY = ($h - $srcH) / 2;
		}
		if (!is_dir(APP . $cacheDir) || !file_exists(APP . $cacheDir)) {
			mkdir(APP . $cacheDir);
		}
		$relfile = $cacheDir . DS . (int)$dstW . 'x' . (int)$dstH . '_' . $node . '_' . basename($path);
		$cachefile = $fullpath . $relfile;
		if (file_exists($cachefile)) {
			if (filemtime($cachefile) >= filemtime($url)) {
				$cached = true;
			} else {
				$cached = false;
			}
		} else {
			$cached = false;
		}
		if (!$cached) {
			$image = call_user_func('imagecreatefrom' . $types[$type], $url);
			if (function_exists("imagecreatetruecolor")) {
				$temp = imagecreatetruecolor($dstW, $dstH);
				if ($types[$type] == "gif" || $types[$type] == "png") {
					imagealphablending($temp, false);
					imagesavealpha($temp, true);
					$transparent = imagecolorallocatealpha($temp, 255, 255, 255, 127);
					imagefilledrectangle($temp, 0, 0, $srcX, $srcY, $transparent);
				}
				imagecopyresampled($temp, $image, 0, 0, $srcX, $srcY, $dstW, $dstH, $srcW, $srcH);
			} else {
				$temp = imagecreate($dstW, $dstH);
				imagecopyresized($temp, $image, 0, 0, $srcX, $srcY, $dstW, $dstH, $srcW, $srcH);
			}
			call_user_func("image" . $types[$type], $temp, $cachefile);
			imagedestroy($image);
			imagedestroy($temp);
		}
		return $cachefile;
	}

/**
 * displayFile
 * Returns the file, it gets it from outside the webroot, and sets it for download if its not an image
 * @param string $filename
 * @param string $dimension
 * @return file
 * @throws NotFoundException
 */
	public function displayFile($filename, $dimension = 'normal') {
		$size = $this->_getDimension($dimension);
		$filename = Sanitize::clean($filename);
		$archivo = $this->Multiattach->findByFilename($filename);
		$isImage = $this->Multiattaches->isImage($archivo['Multiattach']['mime']);
		if (isset($size[2]) || (!$isImage && strtolower($dimension) != 'normal' )) {
			// Something bad happened with the dimension parameter, someone linked it wrong
			// (e.g. text files cant have thumbnail size) or the event is not returning the
			// dimension correctly, so we redirect the client to the normal dimension image.
			// SEO friendly 302 redirect (moved permanently)
			$this->redirect(array(
				'plugin' => 'Multiattach',
				'controller' => 'Multiattach',
				'action' => 'displayFile',
				'admin' => false,
				'dimension' => 'normal',
				'filename' => $filename
					), array('status' => 302));
		}
		$ext = explode('.', $filename);
		$ext = $ext[(count($ext) - 1)];
		$nodeN = explode("-", $filename);
		$nodeN = $nodeN[0];
		if (count($archivo) > 0) {
			$this->response->type($archivo['Multiattach']['mime']);
			$this->response->cache('-1 minute', '+2 days');
			if ($isImage) {
				$img = $this->_resizeImage($archivo['Multiattach']['real_filename'], $size, $nodeN);
				$this->response->file($img, array('download' => false, 'name' => $filename));
				$this->response->body($img);
			} else {
				$this->response->file($archivo['Multiattach']['real_filename'], array('download' => true, 'name' => $filename));
			}
			return $this->response;
		} else {
			throw new NotFoundException();
		}
	}

/**
 * admin_AjaxGetAttachmentJson
 * get attachments from node $nodeId and return json information
 * return meta information in form cleaned parameters
 * @param int $nodeId
 */
	public function admin_AjaxGetAttachmentJson($nodeId) {
		$attachments = $this->Multiattach->find('all', array('recursive' => -1, 'order' => array("Multiattach.order ASC"), 'conditions' => array('node_id' => $nodeId)));
		$this->set('multiattachments', $attachments);
		$this->render('Multiattach/admin_ajax_get_attachment_json', 'json/admin');
	}

/**
 * getLatest
 * Returns the latest attachments
 * @return array
 * @throws ForbiddenException
 */
	public function getLatest() {
		if (empty($this->request->params['requested'])) {
			throw new ForbiddenException();
		}
		$this->loadModel('Nodes');
		$this->Nodes->Behaviors->attach('Multiattach.Multiattach');
		$settings = $this->request->params['named'];
		$this->Nodes->recursive = 1;
		if ($settings['node_id'] == 0) {
			$nodes = $this->Nodes->find('all', array('conditions' => array('type' => $settings['node_type']), 'order' => 'Nodes.created DESC', 'limit' => 10));
			$attachments = array();
			foreach ($nodes as $node) {
				foreach ($node["Multiattach"] as $attachment) {
					$filters = explode(";", $settings["filter"]);
					$pass = 1;
					foreach ($filters as $filter) {
						if ($filter == "") {
							break;
						}
						$filter = explode(":", $filter);
						$field = $filter[0];
						$value = $filter[1];
						if (array_key_exists($field, $attachment["Multiattach"])) {
							if (substr($field, 0, 8) == "content[") {
								$content = json_decode($attachment["Multiattach"]["content"], true);
								$field = str_replace("]", "", str_replace("content[", "", $field));
								if (!preg_match($value, $content[$field])) {
									$pass = 0;
								}
							} else {
								if (!preg_match($value, $attachment["Multiattach"][$field])) {
									$pass = 0;
								}
							}
						}
					}
					if ($pass) {
						$attachments = array_merge($attachments, $node["Multiattach"]);
					}
				}
				if (count($attachments) >= $settings['length']) {
					break;
				}
			}
		} else {
			$nodes = $this->Nodes->findById($settings['node_id']);
			$attachments = $nodes["Multiattach"];
		}
		$attachments = array_slice($attachments, 0, $settings["length"]);
		$this->Nodes->Behaviors->detach('Multiattach.Multiattach');
		return $attachments;
	}

/**
 * admin_AjaxKillAttachmentJson
 * Deletes the attachments via ajax, return json status
 * @param type $attachment
 * @param type $node
 */
	public function admin_AjaxKillAttachmentJson($attachment, $node) {
		$attachment = Sanitize::paranoid($attachment);
		$node = Sanitize::paranoid($node);
		$attaM = $this->Multiattach->find('first', array('recursive' => -1, 'conditions' => array('id' => $attachment, 'node_id' => $node)));
		if (isset($attaM["Multiattach"]["real_filename"]) && $attaM["Multiattach"]["real_filename"] != "") {
			$file = APP . DS . $attaM["Multiattach"]["real_filename"];
			$status = unlink($file) ? 1 : 0;
		} else {
			$status = 1;
		}
		$status .= $this->Multiattach->delete($attaM["Multiattach"]["id"]) ? 1 : 0;
		$status = array('status' => $status);
		$this->set('status', $status);
		$this->render('Multiattach/admin_ajax_kill_attachment_json', 'json/admin');
	}
/**
 * Set order to attachmets
 * @param type $node
 */
	public function admin_AjaxOrderAttachmentJson($node) {
		$data = array();
		foreach ($_GET['s'] as $key => $value) {
			$data[] = array("Multiattach" => array('id' => $value, 'order' => $key));
		}
		$this->Multiattach->saveMany($data);
		$this->set('status', array('status' => 1));
		$this->render('Multiattach/admin_order_attachment_json', 'json/admin');
	}
/**
 * Sets the comment and meta for an attachment
 */
	public function admin_PostFieldAttachmentJson() {
		$allowedColumns = array("meta", "comment");
		$id = (int)Sanitize::paranoid($_GET['pk']);
		$name = Sanitize::paranoid($_GET['name']);
		if (!in_array($name, $allowedColumns)) {
			$name = "comment";
		}
		$value = Sanitize::paranoid($_GET['value'], array(' ', '@', '_', '+', '-', '$', '%', '#', '!', '?', '.', ',', '(', ')', '+', '[', ']', ':', PHP_EOL));
		$printValue = $value;
		if ($name == "meta") {
			$array = explode(PHP_EOL, $value);
			foreach ($array as $k => $v) {
				$prev = explode(':', $v);
				if (key_exists(1, $prev)) {
					$retArr[$prev[0]] = $prev[1];
				} else {
					$retArr[] = $prev[0];
				}
			}
			unset($prev);
			unset($array);
			$value = json_encode($retArr);
			unset($retArr);
		}
		$this->Multiattach->read(null, $id);
		$this->Multiattach->set($name, $value);
		$this->Multiattach->save();
		$status = array('status' => 1, 'newValue' => $printValue);
		$this->set('status', $status);
		$this->render('Multiattach/admin_post_comment_attachment_json', 'json/admin');
	}

/**
 * admin_settings
 * Multiattach settings
 */
	public function admin_settings() {
		$this->set('title_for_layout', __('', true));
		if (!empty($this->data)) {
			$settings = &ClassRegistry::init('Setting');
			foreach ($this->data as $key => $setting) {
				$settings->id = $setting['id'];
				if (strpos($key, "__json") !== false) {
					$setting['value'] = preg_split('/\r\n|[\r\n]/', $setting['value']);
					$setting['value'] = json_encode($setting['value']);
				}
				$settings->saveField('value', $setting['value']);
			}
			$this->redirect(array('action' => 'settings'));
			$this->Session->setFlash(__('Plugin settings have been saved', true));
		}
		$this->set('defaults', $this->defaults);
	}
}
