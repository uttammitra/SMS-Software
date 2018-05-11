<?php
namespace App\Http\Controllers;

class expensesCatController extends Controller {

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

		if($this->data['users']->role != "admin" AND $this->data['users']->role != "account") exit;

		if(!$this->panelInit->hasThePerm('accounting')){
			exit;
		}
	}

	public function listAll()
	{
		return \expenses_cat::get();
	}

	public function delete($id){
		if ( $postDelete = \expenses_cat::where('id', $id)->first() )
        {
            $postDelete->delete();
            return $this->panelInit->apiOutput(true,$this->panelInit->language['delExpenseCat'],$this->panelInit->language['CategoryDeleted']);
        }else{
            return $this->panelInit->apiOutput(false,$this->panelInit->language['delExpenseCat'],$this->panelInit->language['CategoryNotExist']);
        }
	}

	public function create(){
		$expenses_cat = new \expenses_cat();
		$expenses_cat->cat_title = \Input::get('cat_title');
		if(\Input::has('cat_desc')){
			$expenses_cat->cat_desc = \Input::get('cat_desc');
		}
		$expenses_cat->save();

		return $this->panelInit->apiOutput(true,$this->panelInit->language['addExpCat'],$this->panelInit->language['CategoryAdded'],$expenses_cat->toArray() );
	}

	function fetch($id){
		return \expenses_cat::where('id',$id)->first();
	}

	function edit($id){
		$expenses_cat = \expenses_cat::find($id);
		$expenses_cat->cat_title = \Input::get('cat_title');
		if(\Input::has('cat_desc')){
			$expenses_cat->cat_desc = \Input::get('cat_desc');
		}
		$expenses_cat->save();

		return $this->panelInit->apiOutput(true,$this->panelInit->language['editExpCat'],$this->panelInit->language['CategoryUpdated'],$expenses_cat->toArray() );
	}
}
