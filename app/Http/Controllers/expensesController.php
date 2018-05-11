<?php
namespace App\Http\Controllers;

class expensesController extends Controller {

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

	public function listAll($page = 1)
	{
		$toReturn = array();

		$toReturn['expenses'] = new \expenses();

		$toReturn['totalItems'] = $toReturn['expenses']->count();
		$toReturn['expenses'] = $toReturn['expenses']->orderBy('id','DESC')->take('20')->skip(20* ($page - 1) )->get()->toArray();

		while (list($key, $value) = each($toReturn['expenses'])) {
			$toReturn['expenses'][$key]['expenseDate'] = $this->panelInit->unix_to_date($toReturn['expenses'][$key]['expenseDate']);
		}

		$expenses_cat = \expenses_cat::select('id','cat_title')->get()->toArray();
		while (list($key, $value) = each($expenses_cat)) {
			$toReturn['expenses_cat'][$value['id']] = $value['cat_title'];
		}

		return $toReturn;
	}

	public function delete($id){
		if ( $postDelete = \expenses::where('id', $id)->first() )
        {
            $postDelete->delete();
            return $this->panelInit->apiOutput(true,$this->panelInit->language['delExpense'],$this->panelInit->language['expenseDeleted']);
        }else{
            return $this->panelInit->apiOutput(false,$this->panelInit->language['delExpense'],$this->panelInit->language['expenseNotExist']);
        }
	}

	public function create(){
		$expenses = new \expenses();
		$expenses->expenseDate = $this->panelInit->date_to_unix(\Input::get('expenseDate'));
		$expenses->expenseTitle = \Input::get('expenseTitle');
		$expenses->expenseAmount = \Input::get('expenseAmount');
		$expenses->expenseCategory = \Input::get('expenseCategory');
		if(\Input::has('expenseNotes')){
			$expenses->expenseNotes = \Input::get('expenseNotes');
		}
		$expenses->save();

		if (\Input::hasFile('expenseImage')) {
			$fileInstance = \Input::file('expenseImage');
			$newFileName = uniqid().".".$fileInstance->getClientOriginalExtension();
			$fileInstance->move('uploads/expenses/',$newFileName);

			$expenses->expenseImage = $newFileName;
			$expenses->save();
		}

		$expenses->expenseDate = \Input::get('expenseDate');

		return $this->panelInit->apiOutput(true,$this->panelInit->language['addExpense'],$this->panelInit->language['expenseAdded'],$expenses->toArray() );
	}

	function fetch($id){
		$expenses = \expenses::where('id',$id)->first();
		$expenses->expenseDate = $this->panelInit->unix_to_date($expenses->expenseDate);

		return $expenses;
	}

	public function download($id){
		$toReturn = \expenses::where('id',$id)->first();
		if(file_exists('uploads/expenses/'.$toReturn->bookFile)){
			$fileName = preg_replace('/[^a-zA-Z0-9-_\.]/','-',$toReturn->expenseTitle). "." .pathinfo($toReturn->expenseImage, PATHINFO_EXTENSION);
			header("Content-Type: application/force-download");
			header("Content-Disposition: attachment; filename=" . $fileName);
			echo file_get_contents('uploads/expenses/'.$toReturn->expenseImage);
		}
		exit;
	}

	function edit($id){
		$expenses = \expenses::find($id);
		$expenses->expenseDate = $this->panelInit->date_to_unix(\Input::get('expenseDate'));
		$expenses->expenseTitle = \Input::get('expenseTitle');
		$expenses->expenseAmount = \Input::get('expenseAmount');
		$expenses->expenseCategory = \Input::get('expenseCategory');
		if(\Input::has('expenseNotes')){
			$expenses->expenseNotes = \Input::get('expenseNotes');
		}

		if (\Input::hasFile('expenseImage')) {
			$fileInstance = \Input::file('expenseImage');
			$newFileName = uniqid().".".$fileInstance->getClientOriginalExtension();
			$fileInstance->move('uploads/expenses/',$newFileName);

			$expenses->expenseImage = $newFileName;
			$expenses->save();
		}

		$expenses->save();

		$expenses->expenseDate = \Input::get('expenseDate');

		return $this->panelInit->apiOutput(true,$this->panelInit->language['editExpense'],$this->panelInit->language['expenseUpdated'],$expenses->toArray() );
	}
}
