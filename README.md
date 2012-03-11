## CakePHP YouTube datasource

CakePHP datasource that retrieves infos regarding a video throught the youtube's API

## Installation
Put the youtube.php file in your Model/Datasource folder. Add the following
lines to the database.php file:<br />
<p>
public $Youtube = array(
	'datasource' => 'Youtube',
	'api_url' => 'https://gdata.youtube.com/feeds/api/videos/',
	'api_version' => '2'
);
</p>
Use the datasource wherever you want, controller, component, model, behaviour
ecc.. ex:
<p>
public function getVideo($id){
	$this->Youtube = ConnectionManager::getDataSource('Youtube');	
	$video = $this->Youtube->findById($id);
	if ($video) {
		return $video;
	}else{
		return false;
	}
}
</p>
usage:
<p>
print_r($this->getVideo('http://www.youtube.com/watch?v=PBWhzz_Gn10'));
</p>
or
<p>
print_r($this->getVideo('PBWhzz_Gn10'));
</p>

## Author

[Edap](http://itora.net/blog)

## License

This source code is released under an MIT license.
