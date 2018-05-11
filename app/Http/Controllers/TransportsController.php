<?php
namespace App\Http\Controllers;

class TransportsController extends Controller {

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

		if(!$this->panelInit->hasThePerm('Transportation')){
			exit;
		}
	}

	public function listAll()
	{
		return \transportation::get();
	}

	public function delete($id){
		if($this->data['users']->role != "admin") exit;
		if ( $postDelete = \transportation::where('id', $id)->first() )
        {
            $postDelete->delete();
            return $this->panelInit->apiOutput(true,$this->panelInit->language['delTrans'],$this->panelInit->language['transDel']);
        }else{
            return $this->panelInit->apiOutput(false,$this->panelInit->language['delTrans'],$this->panelInit->language['transNotExist']);
        }
	}

	public function create(){
		if($this->data['users']->role != "admin") exit;
		$transportation = new \transportation();
		$transportation->transportTitle = \Input::get('transportTitle');
		$transportation->transportDescription = \Input::get('transportDescription');
		$transportation->transportDriverContact = \Input::get('transportDriverContact');
		$transportation->transportFare = \Input::get('transportFare');
		$transportation->save();

		return $this->panelInit->apiOutput(true,$this->panelInit->language['addTransport'],$this->panelInit->language['transportCreated'],$transportation->toArray() );
	}

	function fetch($id){
		return \transportation::where('id',$id)->first();
	}

	function edit($id){
		if($this->data['users']->role != "admin") exit;
		$transportation = \transportation::find($id);
		$transportation->transportTitle = \Input::get('transportTitle');
		$transportation->transportDescription = \Input::get('transportDescription');
		$transportation->transportDriverContact = \Input::get('transportDriverContact');
		$transportation->transportFare = \Input::get('transportFare');
		$transportation->save();

		return $this->panelInit->apiOutput(true,$this->panelInit->language['editTransport'],$this->panelInit->language['transportUpdated'],$transportation->toArray() );
	}

	function fetchSubs($id){
		return \User::where('activated','1')->where('transport',$id)->get()->toArray();
	}
}
