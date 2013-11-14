<?php

/*
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 * 
 * @author 	ecoreng <me@ecor.me>
 * @link 	http://github.com/ecoreng
 * @license http://www.opensource.org/licenses/mit-license.php The MIT
 * @created May 05, 2013
 *
 */

App::uses('HttpSocket', 'Network/Http');

class Vimeo extends DataSource {

	// Bug: https://cakephp.lighthouseapp.com/projects/42648/tickets/3681-HttpSocket-doesnt-look-at-SSL-certificate-CN-alternatives
	public $defaultSocketSettings = array('ssl_verify_host' => false);

	public $config = array('datasource' => 'Youtube');

	public function __construct($config = null) {
		if ($config === null) {
			$config = $this->config;
		}
		parent::__construct($config);
	}

/**
 * Get the information of a website given in $term
 *
 * @access public
 * @param string $url - The url of the website
 * @return mixed - return false if there is an error, return an array if valid
 */
	public function find($url) {
		$HttpSocket = new HttpSocket($this->defaultSocketSettings);
		$source = $HttpSocket->get($url);
		// 1st Priority: Opengraph tags (Easier for us, stricter to implement which turns out cleaner)
		// 2nd Pr: Meta tags of twitter
		// 3rd Pr: Meta + Title
		// 4th Pr: Meta + Title and fill blanks with content
		$return = array();
		$content['meta'] = $this->_dom($source, "meta", array("content", "name", "property"));

		// This is as far as i am willing to go to suggest info
		// suggest title
		$content['title'] = $this->_dom($source, "title");
		$content['h1'] = $this->_dom($source, "h1", null, 0);
		if (isset($content['meta']['og:title'])) {
			$return['title'] = $content['meta']['og:title']["content"];
		} elseif (isset($content['meta']['twitter:title'])) {
			$return['title'] = $content['meta']['twitter:title']["content"];
		} elseif (isset($content['title'])) {
			$return['title'] = $content['title'];
		} elseif (isset($content['h1'])) {
			$return['title'] = $content['h1'];
		} else {
			$return['title'] = null;
		}

		// suggest description
		$content['p'] = $this->_dom($source, "p", null, 0);
		if (isset($content['meta']['og:description'])) {
			$return['description'] = $content['meta']['og:description']["content"];
		} elseif (isset($content['meta']['twitter:description'])) {
			$return['description'] = $content['meta']['twitter:description']["content"];
		} elseif (isset($content['meta']['description']) && $content['meta']['description'] != "") {
			$return['description'] = $content['meta']['description']["content"];
		} elseif (isset($content['p'])) {
			$return['description'] = $content['p'];
		} else {
			$return['description'] = null;
		}

		// suggest an image
		$content['img'] = $this->_dom($source, "img", array("src"), 0);
		if (isset($content['meta']['og:image'])) {
			$return['image'] = $content['meta']['og:image']["content"];
		} elseif (isset($content['meta']['twitter:image'])) {
			$return['image'] = $content['meta']['twitter:image']["content"];
		} elseif (isset($content['img'])) {
			if (substr($content['img'][0]['src'], 0, 4) !== "http") {
				$genURL = parse_url($url);
				$path = null;
				$path = $genURL["path"];
				$content['img'][0]['src'] = $genURL["scheme"] . "://" . $genURL["host"] . $path . $content['img'][0]['src'];
			}
			$return['image'] = $content['img'][0]['src'];
		} else {
			$return['image'] = null;
		}

		// suggest a video
		if (isset($content['meta']['og:video'])) {
			$return['player'] = $content['meta']['og:video'];
			if (key_exists('content', $return['player'])) {
			    $return['player'] = $return['player']['content'];
			}
		} elseif (isset($content['meta']['twitter:player'])) {
			$return['player'] = $content['meta']['twitter:player'];
			if (key_exists('content', $return['player'])) {
			    $return['player'] = $return['player']['content'];
			}
		}
		// set a unique ID
        $return['id'] = explode('?clip_id=', $return['player']);
        $return['id'] = 'vimeo-' . $return['id'][1];
        
		return $return;
	}

	protected function _dom($source, $tagname, $attributes = array(), $index = null) {
		$return = null;
		$retprev = null;
		libxml_use_internal_errors(true);
		$d = new DOMDocument();
		$d->loadHTML($source);
		$items = $d->getElementsByTagName($tagname);
		if ($index !== null) {
			$items = $items->item(0);
			$items = array($items);
		}

		foreach ($items as $key => $item) {
			if (count($attributes) > 0) {
				foreach ($attributes as $attribute) {
					if (is_object($item)) {
						if (is_object($item->attributes)) {
							if (is_object($item->attributes->getNamedItem($attribute))) {
								$retprev[$key][$attribute] = $item->attributes->getNamedItem($attribute)->nodeValue;
							}
						}
					}
				}
			} else {
				if (isset($item->textContent)) {
					$return = $item->textContent;
				}
			}
			if ($tagname == "meta" && isset($retprev)) {
				$newkey = isset($retprev[$key]["property"]) ? $retprev[$key]["property"] : $key;
				if (isset($retprev[$key])) {
					$return[isset($retprev[$key]["name"]) ? strtolower($retprev[$key]["name"]) : strtolower($newkey)] = $retprev[$key];
				}
			} elseif ($return === null) {
				$return = $retprev;
			}
		}
		return $return;
	}

}
