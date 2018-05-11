<?php
namespace App\Http\Controllers;

class MediaController extends Controller {

	var $data = array();
	var $panelInit ;
	var $layout = 'dashboard';
	var $width = "260";
	var $height = "260";

	public function __construct(){
		if(app('request')->header('Authorization') != ""){
			$this->middleware('jwt.auth');
		}else{
			$this->middleware('authApplication');
		}

		$this->panelInit = new \DashboardInit();
		$this->data['panelInit'] = $this->panelInit;
		$this->data['breadcrumb']['Settings'] = \URL::to('/dashboard/languages');
		$this->data['users'] = $this->panelInit->getAuthUser();
		if(!isset($this->data['users']->id)){
			return \Redirect::to('/');
		}

		if(!$this->panelInit->hasThePerm('mediaCenter')){
			exit;
		}
	}

	public function listAlbum()
	{
		return $this->listAlbumById();
	}

	public function listAlbumById($dir = 0)
	{

		$toReturn = array();
		if($dir != 0){
			$toReturn['current'] = \media_albums::where('id',$dir)->get()->first()->toArray();
		}
		$toReturn['albums'] = \media_albums::where('albumParent',$dir)->orderBy('id','DESC')->get()->toArray();
		while (list($key, $value) = each($toReturn['albums'])) {
			$toReturn['albums'][$key]['thumb'] = $this->generateThumb($value['albumImage'],$this->width,$this->height);
		}


		$toReturn['media'] = \media_items::where('albumId',$dir)->orderBy('id','DESC')->get()->toArray();
		while (list($key, $value) = each($toReturn['media'])) {

			if($value['mediaType'] == "0"){
				$toReturn['media'][$key]['thumb'] = $this->generateThumb($value['mediaURL'],$this->width,$this->height);
			}else{
				$toReturn['media'][$key]['v'] = $this->video_v($value['mediaURL']);
				$toReturn['media'][$key]['thumb'] = $this->generateThumb($value['mediaURLThumb'],$this->width,$this->height);
			}

		}

		return $toReturn;
	}

	public function newAlbum(){
		if($this->data['users']->role != "admin") exit;
		$newFileName = "";

		if (\Input::hasFile('albumImage')) {
			$fileInstance = \Input::file('albumImage');
			$newFileName = "album_".uniqid().".".$fileInstance->getClientOriginalExtension();


			$info = getimagesize( $fileInstance->getPathName() );
			if($info === false) {
				return $this->panelInit->apiOutput(false,$this->panelInit->language['addAlbum'],"Not supported media type");
			}

			$file = $fileInstance->move('uploads/media/',$newFileName);
		}

		$mediaAlbums = new \media_albums();
		$mediaAlbums->albumTitle = \Input::get('albumTitle');
		$mediaAlbums->albumDescription = \Input::get('albumDescription');
		$mediaAlbums->albumImage = $newFileName;
		$mediaAlbums->albumParent = \Input::get('albumParent');
		$mediaAlbums->save();


		return $this->panelInit->apiOutput(true,$this->panelInit->language['addAlbum'],$this->panelInit->language['albumCreated'],$mediaAlbums->toArray() );
	}

	public function image($image){
		$media_items = \media_items::where('id',$image);
		if($media_items->count() == 0){ exit; }
		$media_items = $media_items->first();
		header('Content-Type: image/jpeg');
		if(file_exists('uploads/media/'.$media_items->mediaURL)){
			echo file_get_contents('uploads/media/'.$media_items->mediaURL);
		}
		exit;
	}

	public function deleteAlbum($id){
		if($this->data['users']->role != "admin") exit;
		if ( $postDelete = \media_albums::where('id', $id)->first() )
        {
			$mediaItems = \media_items::where('albumId',$id)->get();
			foreach ($mediaItems as $item) {
				@unlink('uploads/media/'.$item->mediaURL);
				if($item->mediaURLThumb != ""){
					@unlink('uploads/media/'.$item->mediaURLThumb);
				}
			}
			\media_items::where('albumId',$id)->delete();

			$mediaAlbums = \media_albums::where('albumParent',$id)->get();
			foreach ($mediaAlbums as $item) {
			  @unlink('uploads/media/'.$item->albumImage);
			}
			\media_albums::where('albumParent',$id)->delete();

			@unlink('uploads/media/'.$postDelete->albumImage);
			$postDelete->delete();
            return $this->panelInit->apiOutput(true,$this->panelInit->language['delAlbum'],$this->panelInit->language['albumDel']);
        }else{
            return $this->panelInit->apiOutput(false,$this->panelInit->language['delAlbum'],$this->panelInit->language['albumNotExist']);
        }
	}

	public function fetchAlbum($id){
		return \media_albums::where('id',$id)->first();
	}

	public function editAlbum($id){
		if($this->data['users']->role != "admin") exit;
		$album = \media_albums::where('id',$id)->first();

		$album->albumTitle = \Input::get('albumTitle');
		$album->albumDescription = \Input::get('albumDescription');

		$newFileName = "";
		if (\Input::hasFile('albumImage')) {
			if($album->albumImage != ""){
				@unlink('uploads/media/'.$album->albumImage);
			}

			$fileInstance = \Input::file('albumImage');

			$info = getimagesize( $fileInstance->getPathName() );
			if($info === false) {
				return $this->panelInit->apiOutput(false,$this->panelInit->language['editAlbum'],"Not supported media type");
			}

			$newFileName = "album_".uniqid().".".$fileInstance->getClientOriginalExtension();
			$album->albumImage = $newFileName;
			$file = $fileInstance->move('uploads/media/',$newFileName);
		}

		$album->save();
		return $this->panelInit->apiOutput(true,$this->panelInit->language['editAlbum'],$this->panelInit->language['albumModified'],$album->toArray() );
	}

	public function delete($id){
		if($this->data['users']->role != "admin") exit;
		if ( $postDelete = \media_items::where('id', $id)->first() )
        {
			@unlink('uploads/media/'.$postDelete->mediaURL);
			if($postDelete->mediaURLThumb != ""){
				@unlink('uploads/media/'.$postDelete->mediaURLThumb);
			}
            $postDelete->delete();
            return $this->panelInit->apiOutput(true,$this->panelInit->language['delMedia'],$this->panelInit->language['mediaDel']);
        }else{
            return $this->panelInit->apiOutput(false,$this->panelInit->language['delMedia'],$this->panelInit->language['mediaNotExist']);
        }
	}

	public function create(){
		if($this->data['users']->role != "admin") exit;
		$newFileName = "";

		$mediaItems = new \media_items();
		$mediaItems->albumId = \Input::get('albumId');
		if(\Input::get('mediaType') == 0){

			if (\Input::hasFile('mediaURL')) {
				$fileInstance = \Input::file('mediaURL');
			
				$info = getimagesize( $fileInstance->getPathName() );
				if($info === false) {
					return $this->panelInit->apiOutput(false,$this->panelInit->language['addMedia'],"Not supported media type");
				}

				$newFileName = "media_".uniqid().".".$fileInstance->getClientOriginalExtension();
				$file = $fileInstance->move('uploads/media/',$newFileName);
			}

			$mediaItems->mediaURL = $newFileName;

		}else{
			$mediaItems->mediaURL = \Input::get('mediaURL');

			$thumbImage = "video_".uniqid().".jpg";
			file_put_contents('uploads/media/'.$thumbImage,file_get_contents($this->video_image(\Input::get('mediaURL'))));
			$mediaItems->mediaURLThumb = $thumbImage;
		}
		$mediaItems->mediaType = \Input::get('mediaType');
		$mediaItems->mediaTitle = \Input::get('mediaTitle');
		$mediaItems->mediaDescription = \Input::get('mediaDescription');
		$mediaItems->mediaDate = time();
		$mediaItems->save();

		if(\Input::get('mediaType') != 0){
			$mediaItems->v = $this->video_v(\Input::get('mediaURL'));
		}

		return $this->panelInit->apiOutput(true,$this->panelInit->language['addMedia'],$this->panelInit->language['mediaCreated'],$mediaItems->toArray() );
	}

	function fetch($id){
		return \media_items::where('id',$id)->first();
	}

	function edit($id){
		if($this->data['users']->role != "admin") exit;
		$mediaItems = \media_items::where('id',$id)->first();
		$mediaItems->albumId = \Input::get('albumId');

		$newFileName = "";

		if(\Input::get('mediaType') != 0){
			$mediaItems->mediaURL = \Input::get('mediaURL');

			if($mediaItems->mediaURLThumb != ""){
				@unlink('uploads/media/'.$mediaItems->mediaURLThumb);
			}

			$thumbImage = "video_".uniqid().".jpg";
			file_put_contents('uploads/media/'.$thumbImage,file_get_contents($this->video_image(\Input::get('mediaURL'))));
			$mediaItems->mediaURLThumb = $thumbImage;
		}elseif (\Input::hasFile('mediaURL')) {
			if($mediaItems->mediaURL != ""){
				@unlink('uploads/media/'.$mediaItems->mediaURL);
			}
			if($mediaItems->mediaURLThumb != ""){
				@unlink('uploads/media/'.$mediaItems->mediaURLThumb);
			}

			$fileInstance = \Input::file('mediaURL');
			
			$info = getimagesize( $fileInstance->getPathName() );
			if($info === false) {
				return $this->panelInit->apiOutput(false,$this->panelInit->language['editMedia'],"Not supported media type");
			}

			$newFileName = "album_".uniqid().".".$fileInstance->getClientOriginalExtension();
			$mediaItems->mediaURL = $newFileName;
			$file = $fileInstance->move('uploads/media/',$newFileName);
		}

		$mediaItems->mediaTitle = \Input::get('mediaTitle');
		$mediaItems->mediaDescription = \Input::get('mediaDescription');
		$mediaItems->mediaDate = time();
		$mediaItems->save();

		if(\Input::get('mediaType') != 0){
			$mediaItems->v = $this->video_v(\Input::get('mediaURL'));
		}

		return $this->panelInit->apiOutput(true,$this->panelInit->language['editMedia'],$this->panelInit->language['mediaModified'],$mediaItems->toArray() );
	}

	function video_image($url,$size="large"){
		if($size=="thumb"){
			$size=1;
		}else{
			$size=0;
		}

		$image_url = parse_url($url);
		if($image_url['host'] == 'www.youtube.com' || $image_url['host'] == 'youtube.com'){
			$array = explode("&", $image_url['query']);
			return "http://img.youtube.com/vi/".substr($array[0], 2)."/" . $size . ".jpg";
		} elseif($image_url['host'] == 'www.vimeo.com' || $image_url['host'] == 'vimeo.com'){
			$hash = unserialize(file_get_contents("http://vimeo.com/api/v2/video/".substr($image_url['path'], 1).".php"));
			return $hash[0]["thumbnail_large"];
		}
	}

	function video_v($url,$size="large"){
		if($size=="thumb"){
			$size=1;
		}else{
			$size=0;
		}

		$image_url = parse_url($url);
		if($image_url['host'] == 'www.youtube.com' || $image_url['host'] == 'youtube.com'){
			parse_str( parse_url( $url, PHP_URL_QUERY ), $my_array_of_vars );
			return $my_array_of_vars['v'];
		} elseif($image_url['host'] == 'www.vimeo.com' || $image_url['host'] == 'vimeo.com'){
			$urlParts = explode("/", parse_url($url, PHP_URL_PATH));
			return (int)$urlParts[count($urlParts)-1];
		}
	}

	function generateThumb($origImage,$width,$height){
		if(\File::exists('uploads/media/'.$origImage) AND $origImage != ""){

			$cached_image = 'uploads/cache/'.$width."-".$height."-".$origImage;

			if(\File::exists($cached_image)){
				return $cached_image;
			}

			// Create a new SimpleImage object
			$image = new \claviska\SimpleImage();

			$image->fromFile( 'uploads/media/'.$origImage )->thumbnail($width, $height,'center')->toFile($cached_image);

			return $cached_image;
		}else{
			return "";
		}
	}

}
