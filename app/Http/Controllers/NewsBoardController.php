<?php
namespace App\Http\Controllers;

class NewsBoardController extends Controller {

	var $data = array();
	var $panelInit ;
	var $layout = 'dashboard';

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

		if(!$this->panelInit->hasThePerm('newsboard')){
			exit;
		}
	}

	public function listAll($page = 1)
	{
		$toReturn = array();
		if($this->data['users']->role == "admin" ){
			$toReturn['newsboard'] = \newsboard::take('20')->skip(20* ($page - 1) )->get()->toArray();
			$toReturn['totalItems'] = \newsboard::count();
		}else{
			 $toReturn['newsboard'] = \newsboard::where('newsFor',$this->data['users']->role)->orWhere('newsFor','all')->take('20')->skip(20* ($page - 1) )->get()->toArray();
			 $toReturn['totalItems'] = \newsboard::where('newsFor',$this->data['users']->role)->orWhere('newsFor','all')->count();
		}

		foreach ($toReturn['newsboard'] as $key => $item) {
			$toReturn['newsboard'][$key]['newsText'] = strip_tags(htmlspecialchars_decode($toReturn['newsboard'][$key]['newsText'],ENT_QUOTES));
			$toReturn['newsboard'][$key]['newsDate'] = $this->panelInit->unix_to_date($toReturn['newsboard'][$key]['newsDate']);
		}

		$toReturn['userRole'] = $this->data['users']->role;

		return $toReturn;
	}

	public function search($keyword,$page = 1)
	{
		$toReturn = array();
		if($this->data['users']->role == "admin" ){
			$toReturn['newsboard'] = \newsboard::where('newsTitle','like','%'.$keyword.'%')->orWhere('newsText','like','%'.$keyword.'%')->take('20')->skip(20* ($page - 1) )->get()->toArray();
			$toReturn['totalItems'] = \newsboard::where('newsTitle','like','%'.$keyword.'%')->orWhere('newsText','like','%'.$keyword.'%')->count();
		}else{
			 $toReturn['newsboard'] = \newsboard::where('newsFor',$this->data['users']->role)->orWhere('newsFor','all')->where('newsTitle','like','%'.$keyword.'%')->orWhere('newsText','like','%'.$keyword.'%')->take('20')->skip(20* ($page - 1) )->get()->toArray();
			 $toReturn['totalItems'] = \newsboard::where('newsFor',$this->data['users']->role)->orWhere('newsFor','all')->where('newsTitle','like','%'.$keyword.'%')->orWhere('newsText','like','%'.$keyword.'%')->count();
		}

		foreach ($toReturn['newsboard'] as $key => $item) {
			$toReturn['newsboard'][$key]['newsText'] = strip_tags(htmlspecialchars_decode($toReturn['newsboard'][$key]['newsText'],ENT_QUOTES));
			$toReturn['newsboard'][$key]['newsDate'] = $this->panelInit->unix_to_date($toReturn['newsboard'][$key]['newsDate']);
		}

		$toReturn['userRole'] = $this->data['users']->role;

		return $toReturn;
	}

	public function delete($id){
		if ( $postDelete = \newsboard::where('id', $id)->first() )
        {
            $postDelete->delete();
            return $this->panelInit->apiOutput(true,$this->panelInit->language['delNews'],$this->panelInit->language['newsDeleted']);
        }else{
            return $this->panelInit->apiOutput(false,$this->panelInit->language['delNews'],$this->panelInit->language['newsNotEist']);
        }
	}

	public function create(){
		if($this->data['users']->role != "admin") exit;
		$newsboard = new \newsboard();
		$newsboard->newsTitle = \Input::get('newsTitle');
		$newsboard->newsText = htmlspecialchars(\Input::get('newsText'),ENT_QUOTES);
		$newsboard->newsFor = \Input::get('newsFor');
		$newsboard->newsDate = $this->panelInit->date_to_unix(\Input::get('newsDate'));
		$newsboard->creationDate = time();
		$newsboard->save();

		$this->panelInit->mobNotifyUser('role',\Input::get('newsFor'),\Input::get('newsTitle') );

		$newsboard->newsText = strip_tags(htmlspecialchars_decode($newsboard->newsText));

		return $this->panelInit->apiOutput(true,$this->panelInit->language['addNews'],$this->panelInit->language['newsCreated'],$newsboard->toArray() );
	}

	function fetch($id){
		$data = \newsboard::where('id',$id)->first()->toArray();
		$data['newsText'] = htmlspecialchars_decode($data['newsText'],ENT_QUOTES);
		$data['newsDate'] = $this->panelInit->unix_to_date($data['newsDate']);
		return json_encode($data);
	}

	function edit($id){
		if($this->data['users']->role != "admin") exit;
		$newsboard = \newsboard::find($id);
		$newsboard->newsTitle = \Input::get('newsTitle');
		$newsboard->newsText = htmlspecialchars(\Input::get('newsText'),ENT_QUOTES);
		$newsboard->newsFor = \Input::get('newsFor');
		$newsboard->newsDate = $this->panelInit->date_to_unix(\Input::get('newsDate'));
		$newsboard->save();

		$newsboard->newsText = strip_tags(htmlspecialchars_decode($newsboard->newsText));

		return $this->panelInit->apiOutput(true,$this->panelInit->language['editNews'],$this->panelInit->language['newsModified'],$newsboard->toArray() );
	}
}
