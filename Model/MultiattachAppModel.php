<?php
App::uses('AppModel', 'Model');
/**
 * Multiattach Model
 *
 * @property Node $NodeFolder
 */
class MultiattachAppModel extends AppModel {


/**
 * Use table
 *
 * @var mixed False or table name
 */
	public $useTable = 'multiattachments';

/**
 * Display field
 *
 * @var string
 */
	public $displayField = 'filename';

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'node_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
			),
		),
	);

/**
 * hasOne associations
 *
 * @var array
 */
	public $hasOne = array(
		'NodeFolder' => array(
			'className' => 'Node',
			'foreignKey' => 'id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
}
