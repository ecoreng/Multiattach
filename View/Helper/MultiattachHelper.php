<?php

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
class MultiattachHelper extends AppHelper {
	var $attachments=array();

	public function set($attachments = NULL){
		$this->attachments=$attachments;
	}
	
	public function filterWebContent($filters = array()){
		$output=array();
		foreach($this->attachments as $key => $attachment) {
			$attachment=$attachment["Multiattach"];
			if ( !isset($attachment["content"]) || $attachment["content"] == "" )
				continue;
				
			$attachment["content"]=json_decode($attachment["content"],true);
			foreach($filters as $keyf => $filter) {
				if(!isset($attachment["content"][$keyf]))
					continue;
				if( preg_match($filter,$attachment["content"][$keyf]) ) {
					$output[]["Multiattach"]=$attachment;
				}
			}
		}
		if(count($output)>0)
			return $output;
		else
			return FALSE;
	}
	
	public function filter($filters = array()){
		$output=array();
		foreach($this->attachments as $key => $attachment) {
			$attachment=$attachment["Multiattach"];
				
			foreach($filters as $keyf => $filter) {
				if(!isset($attachment[$keyf]))
					continue;
				if( preg_match($filter,$attachment[$keyf]) ) {
					$output[]["Multiattach"]=$attachment;
				}
			}
		}
		if(count($output)>0)
			return $output;
		else
			return FALSE;
	}
	
}
