<?php

App::uses('ModelBehavior', 'Model');

/**
 * Multiattach Behavior
 *
 * PHP version 5
 *
 * @category Behavior
 * @author   Elias Coronado <coso.del.cosito@gmail.com>
 * @license  http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link     http://github.com/ecoreng
 */
class MultiattachBehavior extends ModelBehavior {

/**
 * Setup
 *
 * @param Model $model
 * @param array $config
 * @return void
 */
	public function setup(Model $model, $config = array()) {
		if (is_string($config)) {
			$config = array($config);
		}

		$this->settings[$model->alias] = $config;
	}

/**
 * afterFind callback
 *
 * @param Model $model
 * @param array $results
 * @param boolean $primary
 * @return array
 */
	public function afterFind(Model $model, $results = array(), $primary = false) {
		if ($primary && isset($results[0][$model->alias])) {
			foreach ($results as $i => $result) {
				$model->bindModel(
						array('hasMany' => array(
								'Multiattach' => array(
									'className' => 'Multiattach.Multiattach'
								)
							)
						)
				);
				$model->Multiattach->recursive = -1;
				if (isset($results[$i][$model->alias]['id'])) {
					$results[$i]['Multiattach'] = $model->Multiattach->find('all', array('order' => array('Multiattach.order ASC, Multiattach.id DESC'), 'conditions' => array('Multiattach.node_id' => $results[$i][$model->alias]['id'])));
				}
			}
		}
		return $results;
	}
}
