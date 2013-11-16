<?php

App::uses('AppModel', 'Model');

/**
 * Multiattach Model
 *
 */
class Multiattach extends AppModel {

/**
 * Use table
 *
 * @var mixed False or table name
 */
	public $useTable = 'multiattachments';

/**
 * Display field
 *
 * @var string
 */
	public $displayField = 'filename';

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'node_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
			),
		),
		'order' => array(
			'numeric' => array(
				'rule' => array('numeric'),
			),
		),
	);

/**
 * hasOne associations
 *
 * @var array
 */
	public $hasOne = array(
		'NodeFolder' => array(
			'className' => 'Node',
			'foreignKey' => 'id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

	public function beforeDelete($cascade = true) {
		$deleteAttach = $this->findById($this->id);
		if (file_exists(APP . $deleteAttach["Multiattach"]["real_filename"])) {
			if (unlink(APP . $deleteAttach["Multiattach"]["real_filename"])) {
				return true;
			} else {
				return false;
			}
		}
	}

	public function afterFind($results, $primary = false) {
		if (is_array($results)) {
			foreach ($results as $keyA => $att) {
				// Beautify Meta results

				if (isset($att["Multiattach"]["meta"]) && $att["Multiattach"]["meta"] != "") {
					$results[$keyA]["Multiattach"]["metaDisplay"] = $this->formatDisplayMeta($att["Multiattach"]["meta"]);
				}
			}
		}
		return $results;
	}

	public function parseUrl($url2parse) {
		$datasources = App::objects('Multiattach.Model/Datasource');
		$index = array_search("DefaultWebsite", $datasources);
		unset($datasources[$index]);
		$url2parseNS = str_replace("www.", '', $url2parse);
		$urlParts = parse_url($url2parseNS);
		$host = explode(".", strtolower($urlParts['host']));

		if (isset($host)) {
			switch (count($host)) {
				case "0":
				case "1":
					$busca = false;
					break;
				case "2":
					$busca = array(implode("_", $host), $host[0]);
					break;
				default:
					$busca = array(implode("_", $host), $host[count($host) - 2] . "_" . $host[count($host) - 1]);
					break;
			}
			if ($busca !== false) {
				$b0 = array_search(Inflector::camelize($busca[0]), $datasources);
				$index = ($b0 === false) ? array_search(Inflector::camelize($busca[1]), $datasources) : $b0;
				if ($index !== false) {
					$dstl = ($datasources[$index]);
				} else {
					$dstl = "DefaultWebsite";
				}
			} else {
				$dstl = "DefaultWebsite";
			}
			App::uses($dstl, 'Multiattach.Model/Datasource');
			$ds = new $dstl();
			if (method_exists($ds, 'findByURL')) {
				$dataArr = $ds->findByURL($url2parse);
			} elseif (method_exists($ds, 'findById')) {
				$dataArr = $ds->findById($url2parse);
			} else {
				$dataArr = $ds->find($url2parse);
			}
			if (method_exists($ds, 'formatData')) {
				// Implement this in your datasources, so we can pick what we need
				$dataArr = $ds->formatData($dataArr);
				// We need an array with keys:
				// title, description, image, player (all optional)
			}

			$dataArr['url'] = $url2parse;
			return $dataArr;
		}
		return false;
	}

	public function formatDisplayMeta($meta) {
		$retText = "";
		foreach (json_decode($meta, true) as $key => $value) {
			if (!is_numeric($key)) {
				$retText .= $key . ":" . $value . PHP_EOL;
			} else {
				$retText .= $value . PHP_EOL;
			}
		}
		return $retText;
	}

/**
 * formats the settings gotten from the Settings model to a usable format for
 * this plugin (this plugin saves some settings in json, and this returns the
 * values in an array, if thats the case)
 * @param type $settings
 * @return type
 */
	public function getSettings($settings) {
		$retSet = false;
		if (is_array($settings)) {
			foreach ($settings as $setting) {
				$cleanedKey = explode('.', $setting['Setting']['key']);
				$retSet[$cleanedKey[1]]['id'] = $setting['Setting']['id'];
				$retset = array();
				if (strpos($cleanedKey[1], "__json") !== false) {
					$settingJson = json_decode($setting['Setting']['value'], true);
					foreach ($settingJson as $settingValue) {
						if (strpos($settingValue, ":")) {
							$microsetting = explode(":", $settingValue);
							$retset[$microsetting[0]] = $microsetting[1];
						}
						if (count($retset) > 0) {
							$retSet[$cleanedKey[1]]['values'] = $retset;
						} else {
							$retSet[$cleanedKey[1]]['values'] = $settingJson;
						}
					}
				}
				$retSet[$cleanedKey[1]]['value'] = $setting['Setting']['value'];
			}
		}
		return $retSet;
	}

	public function decodeMeta($attachments) {
		if (is_array($attachments)) {
			foreach ($attachments as $keyA => $att) {
				if (isset($att["Multiattach"]["meta"]) && $att["Multiattach"]["meta"] != "") {
					$retText = "";
					foreach (json_decode($att["Multiattach"]["meta"], true) as $key => $value) {
						if (!is_numeric($key)) {
							$retText .= $key . ":" . $value . PHP_EOL;
						} else {
							$retText .= $value . PHP_EOL;
						}
					}
					$attachments[$keyA]["Multiattach"]["meta"] = $retText;
				}
			}
		}
		return $attachments;
	}

/**
 * Upload selected files
 */
	public function uploadFiles($formdata, $itemId = null, $allowedMime) {
		$itemId = isset($itemId) ? $itemId : 0;
		// http://www.jamesfairhurst.co.uk/posts/view/uploading_files_and_images_with_cakephp
		// setup dir names absolute and relative
		$folderUrl = APP . 'files' . DS;
		$relUrl = 'files';

		// create the folder if it does not exist
		if (!is_dir($folderUrl)) {
			mkdir($folderUrl);
		}

		// if itemId is set create an item folder
		if ($itemId) {
			// set new absolute folder
			$folderUrl = APP . 'files' . DS . $itemId;
			// set new relative folder
			$relUrl = 'files' . DS . $itemId;
			// create directory
			if (!is_dir($folderUrl)) {
				mkdir($folderUrl);
			}
		}

		// loop through and deal with the files
		foreach ($formdata["name"] as $key => $file) {
			// replace spaces with underscores
			$filenameO = str_replace(' ', '_', $formdata['name'][$key]);
			$filename = md5(str_replace(' ', '_', $formdata['name'][$key]));
			// assume filetype is false
			$typeOK = false;
			// check filetype is ok
			if (in_array($formdata['type'][$key], $allowedMime)) {
				$typeOK = true;
			}
			// if file type ok upload the file
			if ($typeOK) {
				$now = "";
				// switch based on error code
				switch ($formdata['error'][$key]) {
					case 0:
						// check filename already exists
						if (!file_exists($folderUrl . DS . $filename)) {
							// create full filename
							$fullUrl = $folderUrl . DS . $filename;
							$url = $relUrl . DS . $filename;
							// upload the file
							$success = move_uploaded_file($formdata['tmp_name'][$key], $fullUrl);
						} else {
							// create unique filename and upload file
							ini_set('date.timezone', 'America/Los_Angeles');
							$now = date('Y-m-d-His');
							$fullUrl = $folderUrl . DS . $now . $filename;
							$url = $relUrl . DS . $now . $filename;
							$success = move_uploaded_file($formdata['tmp_name'][$key], $fullUrl);
						}
						// if upload was successful
						if ($success) {
							// save the url of the file
							$result['urls'][] = $url;
							$result['urlO'][] = $itemId . '-' . $now . $filenameO;
							$result['mime'][] = $formdata['type'][$key];
						} else {
							$result['errors'][] = "Error uploaded $filename. Please try again.";
						}
						break;
					case 3:
						// an error occured
						$result['errors'][] = "Error uploading $filename. Please try again.";
						break;
					default:
						// an error occured
						$result['errors'][] = "System error uploading $filename. Contact webmaster.";
						break;
				}
			} elseif ($formdata['error'][$key] == 4) {
				// no file was selected for upload
				$result['errors'][] = "No file Selected";
			} else {
				// unacceptable file type
				$result['errors'][] = "$filename cannot be uploaded.";
			}
		}
		// Previous code
		//return $result;
		// Moved this from controller to here, makes more sense

		$returnStatus = 0;
		if (array_key_exists('urls', $result)) {
			$returnStatus = 2;
			if (array_key_exists('errors', $result)) {
				$this->Session->setFlash(__('There were some errors in the process of uploading'));
				$returnStatus = 1;
			}
			$attach = array();
			foreach ($result['urls'] as $key => $elemento) {
				$this->create();
				$this->set(array('node_id' => $itemId, 'real_filename' => $elemento, 'filename' => $result['urlO'][$key], 'mime' => $result['mime'][$key]));
				$this->save();
				$attach[] = $this->id;
			}
			$this->recursive = -1;
			$subidos = $this->find('all', array(
				'conditions' => array(
					"Multiattach.id" => $attach
				)
					));
		}
		return $returnStatus;
	}
}
