<?php

foreach ($multiattachments as $key => $attachment) {
	$attachment = $attachment["Multiattach"];
	if (strpos($attachment['mime'], 'image') !== false || strpos($attachment['mime'], 'png') !== false || strpos($attachment['mime'], 'jpg') !== false || strpos($attachment['mime'], 'gif') !== false || strpos($attachment['mime'], 'bmp') !== false || strpos($attachment['mime'], 'bitmap') !== false) {
		$link = $this->Html->url(array(
			'plugin' => 'Multiattach',
			'controller' => 'Multiattach',
			'action' => 'displayFile',
			'admin' => false,
			'dimension' => 'normal',
			'filename' => $attachment['filename']
				));
		$img = $this->Html->url(array(
			'plugin' => 'Multiattach',
			'controller' => 'Multiattach',
			'action' => 'displayFile',
			'admin' => false,
			'dimension' => 'square-thumb',
			'filename' => $attachment['filename']
				));
		$archivo = '<a href="' . $link . '"><img src="' . $img . '" alt="imagen subida" class="attachmentImage"></a>';
	} elseif ($attachment['mime'] != "application/json") {
		$archivo = $this->Html->link($attachment['filename'], array(
			'plugin' => 'Multiattach',
			'controller' => 'Multiattach',
			'action' => 'displayFile',
			'admin' => false,
			'dimension' => 'normal',
			'filename' => $attachment['filename']
				));
	} else {
		$archivo = str_replace('www.', '', $attachment['filename']);
		$archivo = str_replace('http://', '', $archivo);
		$archivo = "<div class='fileHolder'>" . $archivo . "</div>";
	}
	$multiattachments[$key]["Multiattach"]["display"] = $archivo;
}
if (isset($multiattachments)) {
	echo json_encode($multiattachments);
}