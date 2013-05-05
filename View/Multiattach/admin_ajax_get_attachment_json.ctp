<?php
foreach ($multiattachments as $key => $attachment) {
	$attachment=$attachment["Multiattach"];
			if(strpos($attachment['mime'],'image')	!== FALSE ||
			   strpos($attachment['mime'],'png')  	!== FALSE ||
			   strpos($attachment['mime'],'jpg')  	!== FALSE ||
			   strpos($attachment['mime'],'gif')  	!== FALSE ||
			   strpos($attachment['mime'],'bmp')  	!== FALSE ||
			   strpos($attachment['mime'],'bitmap')  !== FALSE
				){
				$link=$this->Html->url(array(
						'plugin'=>'Multiattach',
						'controller'=>'Multiattach',
						'action'=>'displayFile', 
						'admin'=>false,
						'dimension'=>'normal',
						'filename'=>$attachment['filename']
						));
				$img=$this->Html->url(array(
						'plugin'=>'Multiattach',
						'controller'=>'Multiattach',
						'action'=>'displayFile', 
						'admin'=>false,
						'dimension'=>'thumb',
						'filename'=>$attachment['filename']
						));
				$archivo='<a href="'.$link.'"><img src="'.$img.'" alt="imagen subida" style="width:150px;"></a>';
			} elseif ($attachment['mime'] != "application/json") {
			$archivo=$this->Html->link($attachment['filename'],array(
				'plugin'=>'Multiattach',
				'controller'=>'Multiattach',
				'action'=>'displayFile', 
				'admin'=>false,
				'dimension'=>'normal',
				'filename'=>$attachment['filename']
				));
			} else {
				$archivo=str_replace('www.','',$attachment['filename']);
				$archivo=str_replace('http://','',$archivo);
			}
			$multiattachments[$key]["Multiattach"]["display"]=$archivo;
		
}
if (isset($multiattachments)) {
	echo json_encode($multiattachments);
}