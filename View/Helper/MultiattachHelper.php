<?php
App::uses('AppHelper', 'View/Helper');
App::uses('Helper', 'View/Helper');
App::uses('LayoutHelper', 'View/Helper');

/**
 * Multiattach Helper
 *
 * An example hook helper for demonstrating hook system.
 *
 * @category Helper
 * @author   Elias Coronado <coso.del.cosito@gmail.com>
 * @license  http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link     http://github.com/ecoreng
 */
class MultiattachHelper extends LayoutHelper {

	 protected function _tags($input){
		// Notificaciones
		$output=NULL;
		if(isset(parent::$this->request->params['named']['Multiattach_notify'])) {
			$Multiattach_notify=(parent::$this->request->params['named']['Multiattach_notify']);
		} else {
			$Multiattach_notify=0;	
		}
			$output=str_replace("{__Multiattach_notify}"," (".$Multiattach_notify.") ",$input);
		return $output;
		
	}
	public function adminTabs($show = null){
		$output=parent::adminTabs($show);
		$output=$this->_tags($output);
		return $output;
	}
	
}
