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

App::uses('Xml', 'Utility');

/**
 * Youtube Datasource
 *
 * @package datasources
 * @subpackage datasources.models.datasources
 */
class Youtube extends DataSource{
	var $description = 'Youtube';
    var $video_feed = 'videos/';
    var $nation_feed = 'nations/';
    var $standard_feed = 'standardfeeds/';

	public function __construct($config) {
		parent::__construct($config);
		$this->Xml = new Xml();
	}

    /**
     * build the url 
     *
     * @access private
     * @return string
     * @param string $key keyword to search
     * @param string $type search for nation, video or category
    **/
    private function __buildUrl($key, $type, $options = null){
        switch ($type) {
        case 'nation';
            if (!isset($options['feed_id'])) {
                $feed_id = 'top_rated';
            } else {
                $feed_id = $type;
            }
            $url = $this->config['api_url'].$this->video_feed;
            $url.= '?category='.$key.'&v='.$this->config['api_version'];
        break;

        case 'single_video';
            $id =  $this->__cleanYoutubeId($key);
            if (!$id) {
                return false;
            }
            $url = $this->config['api_url'].$this->video_feed;
            $url.= $key.'?v='.$this->config['api_version'];
        break;

        case 'category';
            $url = $this->config['api_url'].$this->video_feed;
            $url.= '?category='.$key.'&v='.$this->config['api_version'];
        break;

        case 'search':
            $url = $this->config['api_url'].$this->video_feed;
            $url.= '?q=' . urlencode($key) . '&v='.$this->config['api_version'];
            if(empty($options['category'])){
              $url.= '&category=' . $options['category'];
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
    public function findById($id = null){
        $video_url = $this->__buildUrl($id, 'single_video');
        if (!$video_url) {
            return false;
        }
        $video_feed = $this->Xml->toArray($this->Xml->build($video_url));
		if (!$video_feed) {
			return false;
		}
        return $video_feed;
    }

    /**
     * Retrieve info regarding a video for a given category
     *
     * @access public
     * @param string $cat - the youtube $cat to retrieve
     * @return mixed - return false if the videos do not exists, return a videos 
     * array if they exist
     */
    public function findByCategory($cat = null){
        $exists = $this->__availableCategory($cat);
        if (!$exists){
            return false;
        }
        $cat_video = $this->__buildUrl($cat, 'category');
        if (!$cat_video) {
            return false;
        }
        $video_feed = $this->Xml->toArray($this->Xml->build($cat_video));
		if (!$video_feed) {
			return false;
		}
		return $video_feed;
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
    public function find($term, $cat = null){
      $url = $this->__buildUrl(
        $term,
        'search',
        array(
          'category'=> $this->__availableCategory($cat) ? $cat : null
        )
      );
      $video_feed = $this->Xml->toArray($this->Xml->build($url));
      if(!$video_feed) return false;
      return $video_feed;
    }

    /**
     * Retrieve info regarding a video for the given nation
     *
     * @access public
     * @param string $nat - the youtube $nation that we search
     * available nations are:
     * 'JP', 'MX', 'NL', 'NZ', 'PL', 'RU', 'ZA', 'KR', 'ES', 'SE', 'TW',
     * 'US','AR','AU','BR','CA','CZ','FR','DE','GB','HK','IN','IE','IL','IT'
     * @param $options array - containing the feed_id type
     * the feed_id type available are:
     * 'top_rated', 'top_favorites','most_viewed', 'most_shared', 
     * 'most_populare', 'most_recent', 'most_discussed', 'most_responded', 
     * 'recently_featured', 'on_the_web'
     *
     * @return mixed - return false if the videos do not exists, return the 
     * array video feed if the nation is present 
     */
    public function findByNation($nat = null, $options = null){
        $exists = $this->__availableNation($nat);
        if (!$exists){
            return false;
        }
        $nat_video = $this->__buildUrl($nat, 'nation', $options);
        if (!$nat_video) {
            return false;
        }
        $video_feed = $this->Xml->toArray($this->Xml->build($nat_video));
		if (!$video_feed) {
			return false;
		}
        return $video_feed;
    }

    private function __availableCategory($cat) {
        $existing_categories = array(
            'Comedy', 'People', 'Entertainment', 'People', 'Music', 'Howto',
            'Sports', 'Autos', 'Education', 'Film', 'News', 'Animals','Tech', 
            'Travel','Games'
        );
        if (isset($cat) && in_array($cat, $existing_categories)) {
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
    **/
    private function __availableNation($nat) {
        $existing_nations = array(
            'JP', 'MX', 'NL', 'NZ', 'PL', 'RU', 'ZA', 'KR', 'ES', 'SE', 'TW',
            'US','AR','AU','BR','CA','CZ','FR','DE','GB','HK','IN','IE','IL','IT'
        );
        if (isset($nat) && in_array($nat, $existing_nations)) {
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
    **/
    private function __availableFeed($feed_id) {
        $existing_feeds = array(
            'top_rated', 'top_favorites','most_viewed', 'most_shared', 
            'most_populare', 'most_recent', 'most_discussed', 'most_responded', 
            'recently_featured', 'on_the_web'
        );
        if (isset($feed_id) && in_array($feed_id, $existing_feeds)) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Return an array with some basic informations, like thumbs, title, ecc..
     *
     * @access public
     * @param array $video_feed - the video feed with all the retrieved 
     * attributes
     * @return mixed - return false if does not work, return an array with basic 
     * info if everything is ok
     */
    function formatData($video_feed){
        if (empty($video_feed)) {
            return false;
        }
        $video_feed = array(
            'title' => $video_feed['entry']['title'],
            'id' => $video_feed['entry']['media:group']['yt:videoid'],
            'author' => $video_feed['entry'] ['author']['name'],
            'default_thumb' => $video_feed['entry']['media:group']['media:thumbnail'][0]['@url'],
            'hd_thumb' => $video_feed['entry']['media:group']['media:thumbnail'][1]['@url'],
            'start_thumb' => $video_feed['entry']['media:group']['media:thumbnail'][2]['@url'],
            'middle_thumb' => $video_feed['entry']['media:group']['media:thumbnail'][3]['@url'],
            'end_thumb' => $video_feed['entry']['media:group']['media:thumbnail'][4]['@url'],
        );
        return $video_feed;
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
    private function __cleanYoutubeId($subject){
        if(!strpos($subject,"www.youtube.com")){
            $subject = "http://www.youtube.com/watch?v=".$subject;
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
?>
