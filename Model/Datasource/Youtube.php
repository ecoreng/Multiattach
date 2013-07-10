<?php

/*
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 * 
 * @author edap <lastexxit@gmail.com>
 * @link http://github.com/edap/cakePHP-youtube-datasource
 * @license http://www.opensource.org/licenses/mit-license.php The MIT
 * @package       datasources
 * @subpackage    datasources.models.datasources*
 * @created Marz 11, 2012
 * @version 0.1
 *
 *
 * Create a datasource in your config/database.php
 *  public $Youtube = array(
 *
 *       'datasource' => 'Youtube',
 *       'api_url' => 'https://gdata.youtube.com/feeds/api/',
 *       'api_version' => '2'
 *   );
 */

App::uses('HttpSocket', 'Network/Http');

/**
 * Youtube Datasource
 *
 * @package datasources
 * @subpackage datasources.models.datasources
 */
class Youtube extends DataSource {

	public $description = 'Youtube';

	public $videoFeed = 'videos/';

	public $nationFeed = 'nations/';

	public $standardFeed = 'standardfeeds/';

	// Bug: https://cakephp.lighthouseapp.com/projects/42648/tickets/3681-HttpSocket-doesnt-look-at-SSL-certificate-CN-alternatives
	public $defaultSocketSettings = array('ssl_verify_host' => false);

	public $config = array(
		'datasource' => 'Youtube',
		'api_url' => 'https://gdata.youtube.com/feeds/api/',
		'api_version' => '2'
	);

	public function __construct($config = null) {
		if ($config === null) {
			$config = $this->config;
		}
		parent::__construct($config);
	}

/**
 * build the url 
 *
 * @access private
 * @return string
 * @param string $key keyword to search
 * @param string $type search for nation, video or category
 * */
	private function __buildUrl($key, $type, $options = null) {
		switch ($type) {
			case 'nation';
				$feed = isset($options['feedId']) ? $options['feedId'] : 'top_rated';
				$url = $this->config['api_url'] . $this->standardFeed;
				$url .= $key . '/' . $feed . '?v=' . $this->config['api_version'] . "&alt=json";
				break;
			case 'single_video';
				$id = $this->__cleanYoutubeId($key);
				if (!$id) {
					return false;
				}
				$url = $this->config['api_url'] . $this->videoFeed;
				$url .= $id . '?v=' . $this->config['api_version'] . "&alt=json";
				break;

			case 'category';
				$url = $this->config['api_url'] . $this->videoFeed;
				$url .= '?category=' . $key . '?v=' . $this->config['api_version'] . "&alt=json";
				break;

			case 'search':
				$url = $this->config['api_url'] . $this->videoFeed;
				$url .= '?q=' . urlencode($key) . '&v=' . $this->config['api_version'] . "&alt=json";
				if (!empty($options['category'])) {
					$url .= '&category=' . $options['category'];
				}
				break;

			default:
				$url = false;
		}
		return $url;
	}

/**
 * Retrieve info regarding a video by a given ID or youtube link
 *
 * @access public
 * @param string $id - the youtube $id to retrieve
 * @return mixed - return false if the video does not exists, return an 
 * array containing the video's info
 */
	public function findById($id = null) {
		$videoUrl = $this->__buildUrl($id, 'single_video');
		if (!$videoUrl) {
			return false;
		}
		$HttpSocket = new HttpSocket($this->defaultSocketSettings);
		$videoFeed = json_decode($HttpSocket->get($videoUrl), true);
		if (!$videoFeed) {
			return false;
		}
		return $videoFeed;
	}

/**
 * Retrieve info regarding a video for a given category
 *
 * @access public
 * @param string $cat - the youtube $cat to retrieve
 * @return mixed - return false if the videos do not exists, return a videos 
 * array if they exist
 */
	public function findByCategory($cat = null) {
		$exists = $this->__availableCategory($cat);
		if (!$exists) {
			return false;
		}
		$catVideo = $this->__buildUrl($cat, 'category');
		if (!$catVideo) {
			return false;
		}
		$HttpSocket = new HttpSocket($this->defaultSocketSettings);
		$videoFeed = json_decode($HttpSocket->get($catVideo), true);

		if (!$videoFeed) {
			return false;
		}
		return $videoFeed;
	}

/**
 * Open search with category capability
 *
 * @access public
 * @param string $term - The term to look for
 * @param string $cat - Category to be included
 * @return mixed - return false if the videos do not exists, return a videos
 * array if they exist
 */
	public function find($term, $cat = null) {
		$url = $this->__buildUrl(
				$term, 'search', array(
			'category' => $this->__availableCategory($cat) ? $cat : null
				)
		);
		$HttpSocket = new HttpSocket($this->defaultSocketSettings);
		$videoFeed = json_decode($HttpSocket->get($url), true);
		if (!$videoFeed) {
			return false;
		}
		return $videoFeed;
	}

/**
 * Retrieve info regarding a video for the given nation
 *
 * @access public
 * @param string $nat - the youtube $nation that we search
 * available nations are:
 * 'JP', 'MX', 'NL', 'NZ', 'PL', 'RU', 'ZA', 'KR', 'ES', 'SE', 'TW',
 * 'US','AR','AU','BR','CA','CZ','FR','DE','GB','HK','IN','IE','IL','IT'
 * @param $options array - containing the feedId type
 * the feedId type available are:
 * 'top_rated', 'top_favorites','most_viewed', 'most_shared', 
 * 'most_popular', 'most_recent', 'most_discussed', 'most_responded', 
 * 'recently_featured', 'on_the_web'
 *
 * @return mixed - return false if the videos do not exists, return the 
 * array video feed if the nation is present 
 */
	public function findByNation($nat = null, $options = null) {
		$exists = $this->__availableNation($nat);
		if (!$exists) {
			return false;
		}
		$natVideo = $this->__buildUrl($nat, 'nation', $options);
		if (!$natVideo) {
			return false;
		}
		$HttpSocket = new HttpSocket($this->defaultSocketSettings);
		$videoFeed = json_decode($HttpSocket->get($natVideo), true);

		if (!$videoFeed) {
			return false;
		}
		return $videoFeed;
	}

/**
 * check if a category is available on the yotube standard feed
 *
 * @return bool
 * @param string $cat category to be checked
 * */
	private function __availableCategory($cat) {
		$existingCategories = array(
			'Comedy', 'People', 'Entertainment', 'People', 'Music', 'Howto',
			'Sports', 'Autos', 'Education', 'Film', 'News', 'Animals', 'Tech',
			'Travel', 'Games'
		);
		if (isset($cat) && in_array($cat, $existingCategories)) {
			return true;
		} else {
			return false;
		}
	}

/**
 * check if a nation is available on the yotube standard feedavailable nation
 *
 * @return bool
 * @param string $nat nation to be proved
 * */
	private function __availableNation($nat) {
		$existingNations = array(
			'JP', 'MX', 'NL', 'NZ', 'PL', 'RU', 'ZA', 'KR', 'ES', 'SE', 'TW',
			'US', 'AR', 'AU', 'BR', 'CA', 'CZ', 'FR', 'DE', 'GB', 'HK', 'IN', 'IE', 'IL', 'IT'
		);
		if (isset($nat) && in_array($nat, $existingNations)) {
			return true;
		} else {
			return false;
		}
	}

/**
 * check if a nation is available on the yotube standard feedavailable nation
 *
 * @return bool
 * @param string $nat nation to be proved
 * */
	private function __availableFeed($feedId) {
		$existingFeeds = array(
			'top_rated', 'top_favorites', 'most_viewed', 'most_shared',
			'most_popular', 'most_recent', 'most_discussed', 'most_responded',
			'recently_featured', 'on_the_web'
		);
		if (isset($feedId) && in_array($feedId, $existingFeeds)) {
			return true;
		} else {
			return false;
		}
	}

/**
 * Return an array with some basic informations, like thumbs, title, ecc..
 *
 * @access public
 * @param array $videoFeed - the video feed with all the retrieved 
 * attributes
 * @return mixed - return false if does not work, return an array with basic 
 * info if everything is ok
 */
	public function formatData($videoFeed) {
		if (empty($videoFeed)) {
			return false;
		}
		$thumbsD = array();
		$cnt = count($videoFeed['entry']['media$group']['media$thumbnail']);
		foreach ($videoFeed['entry']['media$group']['media$thumbnail'] as $key => $thumb) {
			if ($key >= $cnt - 3) {
				continue;
			}
			$thumbsD[] = $thumb['url'];
		}

		$thumbsR = array(
			$videoFeed['entry']['media$group']['media$thumbnail'][$cnt - 3]['url'],
			$videoFeed['entry']['media$group']['media$thumbnail'][$cnt - 2]['url'],
			$videoFeed['entry']['media$group']['media$thumbnail'][$cnt - 1]['url'],
		);
		$videoFeed = array(
			'image' => $thumbsD[0],
			'title' => $videoFeed['entry']['title']['$t'],
			'id' => $videoFeed['entry']['media$group']['yt$videoid']['$t'],
			'author' => $videoFeed['entry']['author'][0]['name']['$t'],
			'thumbs' => array('default' => $thumbsD, 'video' => $thumbsR),
			'description' => $videoFeed['entry']['media$group']['media$description']['$t'],
			'player' => $videoFeed['entry']['content']['src'],
		);
		return $videoFeed;
	}

/**
 * Clean the video url from unnecessary parameters
 * access both formats:
 * http://www.youtube.com/watch?v=PBWhzz_Gn10
 * and
 * PBWhzz_Gn10
 *
 * @access public
 * @param string $subject - the url to be cleaned
 * @return mixed - return false if does not work, return the youtube id if 
 * everithing is ok
 */
	private function __cleanYoutubeId($subject) {
		if (!strpos($subject, "www.youtube.com")) {
			$subject = "http://www.youtube.com/watch?v=" . $subject;
		}
		$url = parse_url($subject);
		if (!isset($url['host'])) {
			return false;
		}
		if ($url['host'] != "www.youtube.com") {
			return false;
		}
		parse_str($url['query'], $query);
		if (!isset($query['v'])) {
			return false;
		}
		return $query['v'];
	}
}