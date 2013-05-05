<?php
echo $this->Form->create('Multiattach', array('type' => 'file'));
?>
<label for="uploads[]"><?php echo __('Select files to upload as attachments'); ?></label>
	 <input name='uploads[]' type="file" multiple>
     <div></div>
<?php 
echo $this->Form->input('step',array('value'=>'1','type'=>'hidden'));
echo $this->Form->button(__('Upload'), array('type' => 'submit','class'=>'btn btn-success'));
echo $this->Form->end();
 ?>