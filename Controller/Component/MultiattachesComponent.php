<?php
App::uses('Component', 'Controller');

/**
 * Multiattach Component
 *
 * Hooked to the Nodes Controller
 *
 */
class MultiattachesComponent extends Component {

	public $components = array('Session');

/**
 * _isImage
 * Returns if filetype is an image comparing the mime type to known values
 * @param type $mime
 * @return boolean
 */
	public function isImage($mime) {
		if ( strpos($mime, 'image') !== false || strpos($mime, 'png') !== false || strpos($mime, 'jpg') !== false || strpos($mime, 'gif') !== false || strpos($mime, 'bmp') !== false || strpos($mime, 'bitmap') !== false) {
				return true;
		} else {
			return false;
		}
	}

	public function initialize(Controller $controller) {
		if ($controller->request->params['controller'] == "nodes") {
			switch($controller->request->params['action']){
				case 'admin_edit':
					$compare = true;
					$compare = is_numeric($controller->request->params['pass'][0]);
					if ($compare) {
						$rq = $controller->Node->findById($controller->request->params['pass'][0]);
						$controller->set(array('Multiattach' => $rq['Multiattach']));
						$controller->set(array('node_id' => $controller->request->params['pass'][0]));
					}
					break;
			}
		}
	}
}