<?php
App::uses('Component', 'Controller');

/**
 * Multiattach Component
 *
 * Hooked to the Nodes Controller
 *
 */
class MultiattachComponent extends Component {
	
	public $components=array('Session');
	
	public function initialize(Controller $controller) {
		
		if ($controller->request->params['controller']=="nodes") {
					
		switch($controller->request->params['action']){
			case 'admin_edit':
				$compare=true;
				$compare=is_numeric($controller->request->params['pass'][0]);
					if($compare){
						$rq=$controller->Node->findById($controller->request->params['pass'][0]);
						
				 		$controller->set(array('Multiattach'=>$rq['Multiattach']));
						$controller->set(array('node_id'=>$controller->request->params['pass'][0]));
					} 					
				break;	
			}
		}
	}


}
