<?php
/*
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 * 
 * @author edap <lastexxit@gmail.com>
 * @link http://github.com/edap/cakePHP-youtube-datasource
 * @license http://www.opensource.org/licenses/mit-license.php The MIT 
 * License
 * @created Marz 11, 2012
 * @version 0.1
 */

class Youtube extends DataSource{
	var $description = 'Youtube';

	public function __construct($config) {
		parent::__construct($config);
        App::uses('Xml', 'Utility');
		$this->Xml = new Xml();
	}

    /**
     * Read the youtube API feed to retrieve info regarding a video
     *
     * @access public
     * @param string $id - the youtube $id to retrieve
     * @return mixed - return false if the video does not exists, return an 
     * array containing
     */
    function findById($id = null){
        $id =  $this->cleanYoutubeId($id);
        if (!$id) {
            return false;
        }
        $yt_video = $this->config['api_url'].$id.'?v='.$this->config['api_version'];
        $video_feed = $this->Xml->toArray($this->Xml->build($yt_video));
        if ($video_feed) {
            return $video_feed;
        }
        else {
            return false;
        }
    }

    /**
     * Return an array with some basic informationa, like thumbs, title, ecc..
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
	function cleanYoutubeId($subject){
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
