<?php
$this->Html
	->addCrumb('', '/admin', array('icon' => 'home'))
	->addCrumb(__d('croogo', 'Settings'), array(
		'admin' => true,
		'plugin' => 'settings',
		'controller' => 'settings',
		'action' => 'index',
	));
if (!empty($this->request->params['named']['p'])) {
	$this->Html->addCrumb($this->request->params['named']['p']);
}
?>
<h2 class="hidden-desktop"><?php echo __('Multiattach Settings') ?></h2>
<?php
echo $this->Form->create('Multiattach');

?>
<div class="row-fluid">
	<div class="span8">
		<ul class="nav nav-tabs">
		<?php
			echo $this->Croogo->adminTab(__d('croogo', 'General'), '#setting-basic');
			//echo $this->Croogo->adminTab(__d('croogo', 'Misc'), '#setting-misc');
		?>
		</ul>

		<div class="tab-content">
			<div id="setting-basic" class="tab-pane">
                          <?php
                          echo $this->Form->input('allowed_mime__json.id', array('type' => 'hidden', 'default' => $defaults['allowed_mime__json']['id'] ));
                          echo $this->Form->input('allowed_mime__json.value', array(
                                        'type'  => 'textarea',
                                        'value' => implode(PHP_EOL, json_decode($defaults['allowed_mime__json']["value"], true)),
					'label' => __('Allowed mime types for upload (one mime type per line)'),
                                        'after' => '<span class="help-block"><a target="_blank" href="http://www.iana.org/assignments/media-types">List of MIME types</a>.</span>' 
				));             
                          echo $this->Form->input('thumbnail_sizes__json.id', array('type' => 'hidden', 'default' => $defaults['thumbnail_sizes__json']['id'] ));
                          echo $this->Form->input('thumbnail_sizes__json.value', array(
                                        'type'  => 'textarea',
                                        'value' => implode(PHP_EOL, json_decode($defaults['thumbnail_sizes__json']["value"], true)),
					'label' => __('Filesizes available (alias: width, height) (use 0 if you dont want that dimension to be resized)'),
				));             
                          
                          echo $this->Form->input('remove_settings.value', array('label' => __('Remove settings on deactivate?'),'options' => array('1' => 'Yes', '0' => 'No'), 'default' => $defaults['remove_settings']['value']));
                          echo $this->Form->input('remove_settings.id', array('type' => 'hidden', 'default' => $defaults['remove_settings']['id'] ));
                          ?> 
                        </div>
                </div>
        </div>
	<div class="span4">
	<?php
		echo $this->Html->beginBox(__d('croogo', 'Settings')) .
			$this->Form->button(__d('croogo', 'Save'), array('button' => 'default')) .
			$this->Html->endBox();

		echo $this->Croogo->adminBoxes();
	?>
	</div>
</div>
<?php echo $this->Form->end(); ?>