<?php
echo $this->Html->css('Multiattach.tab.css');

echo $this->Form->create('Multiattach');
?>
    <label for="url2parse"><?php echo __('Enter URL to parse'); ?></label>
	<?php
	echo $this->Form->textarea('url2parse',array('rows'=>'2'));
	?>
    <div></div>
<?php 
echo $this->Form->input('step',array('value'=>'1','type'=>'hidden'));
echo $this->Form->button(__('Parse'), array('type' => 'submit','class'=>'btn btn-success'));
echo $this->Form->end();
 ?>