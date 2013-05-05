<?php
echo $this->Form->create('Multiattach', array('type' => 'file'));
?>
<label><?php echo __('Uploaded files'); ?>:</label>
<br />

<table class="table table-stripped">
<?php

	$tableHeaders = $this->Html->tableHeaders(array(
		//__('Id'),
		__('Filename'),
		__('Comment'),		
	));

foreach($Multiattach as $attachment){
			if(strpos($attachment['Multiattach']['mime'],'image')	!== FALSE ||
			   strpos($attachment['Multiattach']['mime'],'png')  	!== FALSE ||
			   strpos($attachment['Multiattach']['mime'],'jpg')  	!== FALSE ||
			   strpos($attachment['Multiattach']['mime'],'gif')  	!== FALSE ||
			   strpos($attachment['Multiattach']['mime'],'bmp')  	!== FALSE ||
			   strpos($attachment['Multiattach']['mime'],'bitmap')  !== FALSE
				){
				$link=$this->Html->url(array(
						'plugin'=>'Multiattach',
						'controller'=>'Multiattach',
						'action'=>'displayFile', 
						'admin'=>false,
						'dimension'=>'normal',
						'filename'=>$attachment['Multiattach']['filename']
						));
				$img=$this->Html->url(array(
						'plugin'=>'Multiattach',
						'controller'=>'Multiattach',
						'action'=>'displayFile', 
						'admin'=>false,
						'dimension'=>'thumb',
						'filename'=>$attachment['Multiattach']['filename'],
						));

				$archivo='<a href="'.$link.'"><img src="'.$img.'" alt="'.__('Uploaded image').'" style="width:150px;"></a>';
			} else{
			$archivo=$this->Html->link($attachment['Multiattach']['filename'],array(
				'plugin'=>'Multiattach',
				'controller'=>'Multiattach',
				'action'=>'displayFile', 
				'admin'=>false,
				'dimension'=>'normal',
				'filename'=>$attachment['Multiattach']['filename']
				));
			}
			
			$tableRows[]=array(
				//$attachment['Multiattach']['id'],
				$archivo,
				$this->Form->input('comment.'.$attachment['Multiattach']['id'],array('maxlength'=>140,'label'=>false,'type' => 'text','value'=>$attachment['Multiattach']['comment'])),
				);	
}
	echo $tableHeaders;
	echo $this->Html->tableCells($tableRows);
	echo $tableHeaders;
?>
</table>
<br />

<?php
echo $this->Form->input('step',array('value'=>'2','type'=>'hidden'));
echo $this->Form->button(__('Save'), array('type' => 'submit'));
echo $this->Form->end();
 ?>
