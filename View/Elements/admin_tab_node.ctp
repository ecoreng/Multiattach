<?php
echo $this->Html->css('Multiattach.tab.css', null, array('inline' => false));
echo $this->Html->css('//cdnjs.cloudflare.com/ajax/libs/x-editable/1.4.4/bootstrap-editable/css/bootstrap-editable.css', null, array('inline' => false));

$this->Html->script('/multiattach/js/getAttachments.js', false);
$this->Html->script('/multiattach/js/sortable.js', false);
$this->Html->script('//cdnjs.cloudflare.com/ajax/libs/x-editable/1.4.4/bootstrap-editable/js/bootstrap-editable.min.js', false);
?>
<script>
	Croogo.params.node_id=<?php echo $node_id; ?>;
	var params={
		multiattach:{
			proCoUrl:'<?php echo $this->Html->url(array('admin' => true, 'plugin' => 'Multiattach', 'controller' => 'Multiattach', 'action' => 'PostFieldAttachmentJson')); ?>',
			tabName:'<?php echo __('Attachments'); ?>',
			noMetaLbl:'<?php echo __('empty meta'); ?>',
			noCommentLbl:'<span class="label label-inverse"><?php echo __('empty comment'); ?></span>',
			deleteSt:{
				s00:'<?php echo __("Couldn\'t delete file or database entry"); ?>',
				s01:'<?php echo __('Attachment deleted with errors'); ?>',
				s10:'<?php echo __("Couldn\'t delete database entry"); ?>',
				s11:'<?php echo __('Succesfully deleted attachment'); ?>',
			},
		}
	};
	
	$.extend(Croogo.params,params);
</script>


<div id="multiattachstatus" class="alert"></div>

<div class="btn-group" id="attachCommand">
	<?php
	echo $this->Html->link(__('Upload'), '#', array('class' => 'btn btn-primary', 'onclick' => "var openWin = window.open('" . $this->Html->url(array('plugin' => 'Multiattach', 'controller' => 'Multiattach', 'action' => 'add', 'node' => $node_id,)) . "', '_blank', 'toolbar=0,scrollbars=1,location=0,status=1,menubar=0,resizable=1,width=640,height=480');  return false;"));
	?>
	<?php
	echo $this->Html->link(__('Attach from URL'), '#', array('class' => 'btn', 'onclick' => "var openWin = window.open('" . $this->Html->url(array('plugin' => 'Multiattach', 'controller' => 'Multiattach', 'action' => 'add_web', 'node' => $node_id,)) . "', '_blank', 'toolbar=0,scrollbars=1,location=0,status=1,menubar=0,resizable=1,width=640,height=480');  return false;"));
	?>
</div>

<label class="multiattach_title"><?php echo __('Attached files to this node'); ?></label>

<div id="multiattachments"></div>
