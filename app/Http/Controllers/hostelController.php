<?php
namespace App\Http\Controllers;

class hostelController extends Controller {

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
		$this->data['users'] = $this->panelInit->getAuthUser();
		if(!isset($this->data['users']->id)){
			return \Redirect::to('/');
		}
		if($this->data['users']->role != "admin") exit;

		if(!$this->panelInit->hasThePerm('HostelManage')){
			exit;
		}
	}

	public function listAll()
	{
		return \hostel::get();
	}

	public function delete($id){
		if ( $postDelete = \hostel::where('id', $id)->first() )
        {
            $postDelete->delete();
            return $this->panelInit->apiOutput(true,$this->panelInit->language['delHostel'] ,$this->panelInit->language['hostelDeleted']);
        }else{
            return $this->panelInit->apiOutput(false,$this->panelInit->language['delHostel'] ,$this->panelInit->language['hostelNotExist']);
        }
	}

	public function create(){
		$hostel = new \hostel();
        $hostel->hostelTitle = \Input::get('hostelTitle');
		$hostel->hostelType = \Input::get('hostelType');
		if(\Input::has('hostelAddress')){
			$hostel->hostelAddress = \Input::get('hostelAddress');
		}
		if(\Input::has('hostelManager')){
			$hostel->hostelManager = \Input::get('hostelManager');
		}
		if(\Input::has('hostelNotes')){
			$hostel->hostelNotes = \Input::get('hostelNotes');
		}
		$hostel->save();

		return $this->panelInit->apiOutput(true,$this->panelInit->language['delHostel'],$this->panelInit->language['HostelCreated'] ,$hostel->toArray() );
	}

	function fetch($id){
		return \hostel::where('id',$id)->first();
	}

	function listSubs($id){
		$catListId = array();
		$categoriesList = \hostel_cat::where('catTypeId',$id)->get();
		foreach ($categoriesList as $value) {
			$catListId[] = $value->id;
			$catListNames[$value->id] = $value->catTitle;
		}

		$subscribers = array();

		if(count($catListId) > 0){
			$subscribers = \User::whereIn('hostel',$catListId)->get()->toArray();
		}

		return $subscribers;
	}

	function edit($id){
		$hostel = \hostel::find($id);
		$hostel->hostelTitle = \Input::get('hostelTitle');
		$hostel->hostelType = \Input::get('hostelType');
		if(\Input::has('hostelAddress')){
			$hostel->hostelAddress = \Input::get('hostelAddress');
		}
		if(\Input::has('hostelManager')){
			$hostel->hostelManager = \Input::get('hostelManager');
		}
		if(\Input::has('hostelNotes')){
			$hostel->hostelNotes = \Input::get('hostelNotes');
		}
		$hostel->save();

		return $this->panelInit->apiOutput(true,$this->panelInit->language['EditHostel'],$this->panelInit->language['HostelModified'],$hostel->toArray() );
	}
}
