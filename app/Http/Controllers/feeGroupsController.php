<?php
namespace App\Http\Controllers;

class feeGroupsController extends Controller {

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

		if($this->data['users']->role != "admin" && $this->data['users']->role != "account") exit;

		if(!$this->panelInit->hasThePerm('accounting')){
			exit;
		}
	}

	public function listAll()
	{
		return \fee_group::get();
	}

	public function delete($id){
		if ( $postDelete = \fee_group::where('id', $id)->first() )
        {
            $postDelete->delete();
            return $this->panelInit->apiOutput(true,$this->panelInit->language['delFeeGroup'],$this->panelInit->language['feeGroupDeleted']);
        }else{
            return $this->panelInit->apiOutput(false,$this->panelInit->language['delFeeGroup'],$this->panelInit->language['feeGroupNotExist']);
        }
	}

	public function create(){
		$feeGroup = new \fee_group();
		$feeGroup->group_title = \Input::get('group_title');
		if(\Input::has('group_description')){
			$feeGroup->group_description = \Input::get('group_description');
		}
		$feeGroup->invoice_prefix = \Input::get('invoice_prefix');
		$feeGroup->save();

		return $this->panelInit->apiOutput(true,$this->panelInit->language['addFeeGroup'],$this->panelInit->language['feeGroupAdded'],$feeGroup->toArray() );
	}

	function fetch($id){
		return \fee_group::where('id',$id)->first();
	}

	function edit($id){
		$feeGroup = \fee_group::find($id);
		$feeGroup->group_title = \Input::get('group_title');
		$feeGroup->group_description = \Input::get('group_description');
		$feeGroup->invoice_prefix = \Input::get('invoice_prefix');
		$feeGroup->save();

		return $this->panelInit->apiOutput(true,$this->panelInit->language['editFeeGroup'],$this->panelInit->language['feeGroupUpdated'],$feeGroup->toArray() );
	}
}
