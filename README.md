## CakePHP YouTube datasource

CakePHP YouTube datasource is used to search metatags in the youtube's API

## Installation
Put the youtube.php file in your Model/Datasource folder. Add the following
lines to the database.php file:

	public $Youtube = array(
        	'datasource' => 'Youtube',
        	'api_url' => 'https://gdata.youtube.com/feeds/api/',
        	'api_version' => '2'
	);

## Available Functions
findById = get infos for a single video, starting from a complete youtube URL, or
from a Video ID 

findByNation = find videos for a specific nation. See the comments for a list of
the available nations. You can choose which type of video to retrieve, "top
rated" is the default value.

findByCategory = find videos for a specific Category. See the comments for a list of
the available categories.

## Usage Examples
Use the datasource wherever you want, controller, component, model, behaviour

### Model

	public function getVideo($id){
		$this->Youtube = ConnectionManager::getDataSource('Youtube');	
		$video = $this->Youtube->findById($id);
		if ($video) {
			return $video;
		}else{
			return false;
		}
	}

usage:

	print_r($this->getVideo('http://www.youtube.com/watch?v=PBWhzz_Gn10'));
or

	print_r($this->getVideo('PBWhzz_Gn10'));
### Controller
	App::import('Model', 'ConnectionManager');

	public function video() {
		$this->autoRender = false;
		$videos = ConnectionManager::getDataSource('Youtube');
		//print_r($videos->findById('k0FWwh12-tk'));
		//print_r($videos->findByCategory('Sports'));
		print_r($videos->findByNation('JP', array('feed_id' => 'top_rated')));
	}

## Author

[Edap](http:edapx.com)

## License

This source code is released under an MIT license.
