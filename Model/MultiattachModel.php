<?php
App::uses('AppModel', 'Model');
/**
 * Multiattach Model
 *
 * @property Node $NodeFolder
 */
class MultiattachModel extends MultiattachAppModel {

	public function beforeDelete(){
		$this->Multiattach->recursive=-1;
		$deleteAttach=$this->Multiattach->findById($this->id);
		if(file_exists(APP.$deleteAttach["Multiattach"]["real_filename"])){
			if(unlink(APP.$deleteAttach["Multiattach"]["real_filename"])){
				return true;
			} else {
				return false;	
			}
		}

	}


}
