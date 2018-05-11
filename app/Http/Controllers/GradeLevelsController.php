<?php
namespace App\Http\Controllers;

class GradeLevelsController extends Controller {

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
		if($this->data['users']->role != "admin") exit;

		if(!$this->panelInit->hasThePerm('gradeLevels')){
			exit;
		}
	}

	public function listAll()
	{
		return \grade_levels::get();
	}

	public function delete($id){
		if ( $postDelete = \grade_levels::where('id', $id)->first() )
        {
            $postDelete->delete();
            return $this->panelInit->apiOutput(true,$this->panelInit->language['delGradeLevel'],$this->panelInit->language['gradeDeleted']);
        }else{
            return $this->panelInit->apiOutput(false,$this->panelInit->language['delGradeLevel'],$this->panelInit->language['gradeNotExist']);
        }
	}

	public function create(){
		$gradeLevels = new \grade_levels();
		$gradeLevels->gradeName = \Input::get('gradeName');
		$gradeLevels->gradeDescription = \Input::get('gradeDescription');
		$gradeLevels->gradePoints = \Input::get('gradePoints');
		$gradeLevels->gradeFrom = \Input::get('gradeFrom');
		$gradeLevels->gradeTo = \Input::get('gradeTo');
		$gradeLevels->save();

		return $this->panelInit->apiOutput(true,$this->panelInit->language['addLevel'],$this->panelInit->language['gradeCreated'],$gradeLevels->toArray() );
	}

	function fetch($id){
		return \grade_levels::where('id',$id)->first();
	}

	function edit($id){
		$gradeLevels = \grade_levels::find($id);
		$gradeLevels->gradeName = \Input::get('gradeName');
		$gradeLevels->gradeDescription = \Input::get('gradeDescription');
		$gradeLevels->gradePoints = \Input::get('gradePoints');
		$gradeLevels->gradeFrom = \Input::get('gradeFrom');
		$gradeLevels->gradeTo = \Input::get('gradeTo');
		$gradeLevels->save();

		return $this->panelInit->apiOutput(true,$this->panelInit->language['editGrade'],$this->panelInit->language['gradeUpdated'],$gradeLevels->toArray() );
	}
}
