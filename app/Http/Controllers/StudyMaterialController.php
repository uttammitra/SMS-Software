<?php
namespace App\Http\Controllers;

class StudyMaterialController extends Controller {

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

		if(!$this->panelInit->hasThePerm('studyMaterial')){
			exit;
		}
	}

	public function listAll()
	{
		$toReturn = array();
		$toReturn['classes'] = \classes::where('classAcademicYear',$this->panelInit->selectAcYear)->get()->toArray();
		$classesArray = array();
		while (list(, $class) = each($toReturn['classes'])) {
			$classesArray[$class['id']] = $class['className'];
		}

		$subjects = \subject::get()->toArray();
		$subjectArray = array();
		while (list(, $subject) = each($subjects)) {
			$subjectArray[$subject['id']] = $subject['subjectTitle'];
		}

		$toReturn['materials'] = array();
		$studyMaterial = new \study_material();

		if($this->data['users']->role == "student"){
			$studyMaterial = $studyMaterial->where('class_id','LIKE','%"'.$this->data['users']->studentClass.'"%');
			if($this->panelInit->settingsArray['enableSections'] == true){
				$studyMaterial = $studyMaterial->where('sectionId','LIKE','%"'.$this->data['users']->studentSection.'"%');
			}
		}

		if($this->data['users']->role == "teacher"){
			$studyMaterial = $studyMaterial->where('teacher_id',$this->data['users']->id);
		}

		$studyMaterial = $studyMaterial->get();

		foreach ($studyMaterial as $key => $material) {
			$classId = json_decode($material->class_id);
			if($this->data['users']->role == "student" AND !in_array($this->data['users']->studentClass, $classId)){
				continue;
			}
			$toReturn['materials'][$key]['id'] = $material->id;
			$toReturn['materials'][$key]['subjectId'] = $material->subject_id;
			if(isset($subjectArray[$material->subject_id])){
				$toReturn['materials'][$key]['subject'] = $subjectArray[$material->subject_id];
			}else{
				$toReturn['materials'][$key]['subject'] = "";
			}
			$toReturn['materials'][$key]['material_title'] = $material->material_title;
			$toReturn['materials'][$key]['material_description'] = $material->material_description;
			$toReturn['materials'][$key]['material_file'] = $material->material_file;
			$toReturn['materials'][$key]['classes'] = "";

            if(is_array($classId)){
    			while (list(, $value) = each($classId)) {
    				if(isset($classesArray[$value])) {
    					$toReturn['materials'][$key]['classes'] .= $classesArray[$value].", ";
    				}
    			}
            }
		}

		$toReturn['userRole'] = $this->data['users']->role;
		return $toReturn;
		exit;
	}

	public function delete($id){
		if($this->data['users']->role == "student" || $this->data['users']->role == "parent") exit;
		if ( $postDelete = \study_material::where('id', $id)->first() )
        {
			@unlink('uploads/studyMaterial/'.$postDelete->material_file);
            $postDelete->delete();
            return $this->panelInit->apiOutput(true,$this->panelInit->language['delMaterial'],$this->panelInit->language['materialDel']);
        }else{
            return $this->panelInit->apiOutput(false,$this->panelInit->language['delMaterial'],$this->panelInit->language['materialNotExist']);
        }
	}

	public function create(){
		if($this->data['users']->role == "student" || $this->data['users']->role == "parent") exit;
		$studyMaterial = new \study_material();
		$studyMaterial->class_id = json_encode(\Input::get('class_id'));
		if($this->panelInit->settingsArray['enableSections'] == true){
			$studyMaterial->sectionId = json_encode(\Input::get('sectionId'));
		}
		$studyMaterial->subject_id = \Input::get('subject_id');
		$studyMaterial->material_title = \Input::get('material_title');
		$studyMaterial->material_description = \Input::get('material_description');
		$studyMaterial->teacher_id = $this->data['users']->id;
		$studyMaterial->save();
		if (\Input::hasFile('material_file')) {
			$fileInstance = \Input::file('material_file');
			$newFileName = "material_".uniqid().".".$fileInstance->getClientOriginalExtension();
			$fileInstance->move('uploads/studyMaterial/',$newFileName);

			$studyMaterial->material_file = $newFileName;
			$studyMaterial->save();
		}

		return $this->panelInit->apiOutput(true,$this->panelInit->language['addMaterial'],$this->panelInit->language['materialAdded'],$studyMaterial->toArray() );
	}

	function fetch($id){
		$studyMaterial = \study_material::where('id',$id)->first()->toArray();
		$DashboardController = new DashboardController();
		$studyMaterial['sections'] = $DashboardController->sectionsList(json_decode($studyMaterial['class_id'],true));
		$studyMaterial['subject'] = $DashboardController->subjectList(json_decode($studyMaterial['class_id'],true));
		return $studyMaterial;
	}

	public function download($id){
		$toReturn = \study_material::where('id',$id)->first();
		if(file_exists('uploads/studyMaterial/'.$toReturn->material_file)){
			$fileName = preg_replace('/[^a-zA-Z0-9-_\.]/','',$toReturn->material_title). "." .pathinfo($toReturn->material_file, PATHINFO_EXTENSION);
			header("Content-Type: application/force-download");
			header("Content-Disposition: attachment; filename=" . $fileName);
			echo file_get_contents('uploads/studyMaterial/'.$toReturn->material_file);
		}
		exit;
	}

	function edit($id){
		if($this->data['users']->role == "student" || $this->data['users']->role == "parent") exit;
		$studyMaterial = \study_material::find($id);
		$studyMaterial->class_id = json_encode(\Input::get('class_id'));
		if($this->panelInit->settingsArray['enableSections'] == true){
			$studyMaterial->sectionId = json_encode(\Input::get('sectionId'));
		}
		$studyMaterial->subject_id = \Input::get('subject_id');
		$studyMaterial->material_title = \Input::get('material_title');
		$studyMaterial->material_description = \Input::get('material_description');
		if (\Input::hasFile('material_file')) {
			@unlink("uploads/studyMaterial/".$studyMaterial->material_file);
			$fileInstance = \Input::file('material_file');
			$newFileName = "material_".uniqid().".".$fileInstance->getClientOriginalExtension();
			$fileInstance->move('uploads/studyMaterial/',$newFileName);

			$studyMaterial->material_file = $newFileName;
		}
		$studyMaterial->save();

		return $this->panelInit->apiOutput(true,$this->panelInit->language['editMaterial'],$this->panelInit->language['materialEdited'],$studyMaterial->toArray() );
	}
}
