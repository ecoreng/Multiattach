<?php
echo $this->Html->css('Multiattach.tab.css');

echo $this->Form->create('Multiattach', array('type' => 'file'));
?>
<h4><?php echo __('Attached web data'); ?>:</h4>
<br />
<div id="webData" class="well">
	<?php if(isset($attachmentData['image'])) { ?>
    	<img src="<?php echo $attachmentData['image']; ?>" alt="<?php echo $attachmentData['title']; ?>" />
    <?php } ?>
    <div class="right">
		<?php
            echo $this->Form->input('title',array('value'=> $attachmentData['title'], 'label'=>false, 'div'=>false));
        ?>
        <?php
            echo $this->Form->textarea('description',array('value'=> $attachmentData['description']));
        ?>
    </div>
    <div class="clr">
		<?php echo $attachmentData['url']; ?><br />
        <?php if (isset($attachmentData['player']) && $attachmentData['player'] != "") { ?>
        <span class="label label-info">[ <?php echo __("Media included"); ?> ]</span>
        <?php } ?>
        <?php
            echo $this->Form->textarea('data',array('value'=> json_encode($attachmentData),'style'=>'display:none;'));
        ?>
    </div>
</div>

<?php
echo $this->Form->input('step',array('value'=>'2','type'=>'hidden'));
echo $this->Form->button(__('Save'), array('type' => 'submit','class'=>'btn btn-success'));
echo $this->Form->end();
 ?>
