<?php

class MultiattachEventHandler extends Object implements CakeEventListener {


/**
 * implementedEvents
 *
 * @return array
 */
	public function implementedEvents() {
		return array(
			'Controller.Nodes.afterAdd'=>array(
				'callable' => 'onNodeAfterAdd'
			),
		);
	}
	public function onNodeAfterAdd($event){
		// Not really implemented yet
	}
}
