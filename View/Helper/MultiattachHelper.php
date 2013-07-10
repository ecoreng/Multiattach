<?php

/**
 * Multiattach Helper
 *
 * Helper for Multiattach Plugin
 *
 * @category Helper
 * @author   Elias Coronado <coso.del.cosito@gmail.com>
 * @license  http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link     http://github.com/ecoreng
 */
class MultiattachHelper extends AppHelper {

	public $attachments = array();

	public function set($attachments = null) {
		$this->attachments = $attachments;
	}

	public function filterMeta($filters = array()) {
		return $this->deepFilter("meta",$filters);
	}

	public function filterWebContent($filters = array()) {
		return $this->deepFilter("content",$filters);
	}

	public function deepFilter($field = "content", $filters = array()) {
		$output = array();
		foreach ($this->attachments as $key => $attachment) {
			$attachment = $attachment["Multiattach"];
			if (!key_exists($field, $attachment)) {
				continue;
			}
			$attachment[$field] = json_decode($attachment[$field],true);
			foreach ($filters as $keyf => $filter) {
				if (!isset($attachment[$field][$keyf])) {
					continue;
				}
				if (preg_match($filter,$attachment[$field][$keyf])) {
					$output[]["Multiattach"] = $attachment;
				}
			}
		}
		if (count($output) > 0) {
			return $output;
		} else {
			return false;
		}
	}

	public function filter($filters = array()) {
		$output = array();
		foreach ($this->attachments as $key => $attachment) {
			$attachment = $attachment["Multiattach"];
			$numFilters = count($filters);
			$passed = 0;
			foreach ($filters as $keyf => $filter) {
				if (!isset($attachment[$keyf])) {
					continue;
				}
				if (preg_match($filter, $attachment[$keyf]) ) {
					$passed += 1;
				}
				if ($numFilters == $passed) {
					$output[]["Multiattach"] = $attachment;
				}
			}
		}
		if (count($output) > 0) {
			return $output;
		} else {
			return false;
		}
	}
}