<?php
namespace App\Http\Controllers;

class ExamsListController extends Controller {

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

		if(!$this->panelInit->hasThePerm('examsList')){
			exit;
		}
	}

	public function listAll()
	{
		$toReturn['exams'] = array();
		if($this->data['users']->role == "student"){
			$toReturn['exams'] = \exams_list::where('examAcYear',$this->panelInit->selectAcYear)->where('examClasses','LIKE','%"'.$this->data['users']->studentClass.'"%')->get()->toArray();
		}elseif($this->data['users']->role == "parent"){

			$studentId = array();
			$parentOf = json_decode($this->data['users']->parentOf,true);
			if(is_array($parentOf)){
				while (list($key, $value) = each($parentOf)) {
					$studentId[] = $value['id'];
				}
			}

			if( count($studentId) > 0){
				$studentDetails = \User::where('role','student')->whereIn('id',$studentId)->select('studentClass');
				if($studentDetails->count() > 0){
					$studentDetails = $studentDetails->get()->toArray();

					$toReturn['exams'] = \exams_list::where('examAcYear',$this->panelInit->selectAcYear)->where(function($query) use ($studentDetails){ 
																												while (list(, $value) = each($studentDetails)) {
																													$query->orWhere('examClasses','LIKE','%"'.$value['studentClass'].'"%');
																												}
																											})->get()->toArray();
				}
			}

		}else{
			$toReturn['exams'] = \exams_list::where('examAcYear',$this->panelInit->selectAcYear)->get()->toArray();
		}

		if($this->data['users']->role == "teacher"){
			$toReturn['classes'] = \classes::where('classAcademicYear',$this->panelInit->selectAcYear)->where('classTeacher','LIKE','%"'.$this->data['users']->id.'"%')->get()->toArray();
		}else{
			$toReturn['classes'] = \classes::where('classAcademicYear',$this->panelInit->selectAcYear)->get()->toArray();
		}

		while (list($key, $value) = each($toReturn['exams'])) {
			$toReturn['exams'][$key]['examDate'] = $this->panelInit->unix_to_date($toReturn['exams'][$key]['examDate']);
			$toReturn['exams'][$key]['examEndDate'] = $this->panelInit->unix_to_date($toReturn['exams'][$key]['examEndDate']);
		}

		$toReturn['subjects'] = array();
		$subjects = \subject::select('id','subjectTitle')->get()->toArray();
		while (list($key, $value) = each($subjects)) {
			$toReturn['subjects'][$value['id']] = $value['subjectTitle'];
		}

		$toReturn['userRole'] = $this->data['users']->role;
		return $toReturn;
	}

	public function delete($id){
		if($this->data['users']->role != "admin") exit;
		if ( $postDelete = \exams_list::where('id', $id)->first() )
        {
            $postDelete->delete();
            return $this->panelInit->apiOutput(true,$this->panelInit->language['delExam'],$this->panelInit->language['exDeleted']);
        }else{
            return $this->panelInit->apiOutput(false,$this->panelInit->language['delExam'],$this->panelInit->language['exNotExist']);
        }
	}

	public function create(){
		if($this->data['users']->role != "admin") exit;
		$examsList = new \exams_list();
		$examsList->examTitle = \Input::get('examTitle');
		$examsList->examDescription = \Input::get('examDescription');
		$examsList->examDate = $this->panelInit->date_to_unix(\Input::get('examDate'));
		$examsList->examEndDate = $this->panelInit->date_to_unix(\Input::get('examEndDate'));
		$examsList->examAcYear = $this->panelInit->selectAcYear;
		if(\Input::has('examClasses')){
			$examsList->examClasses = json_encode(\Input::get('examClasses'));
		}
		if(\Input::has('examMarksheetColumns')){
			$examsList->examMarksheetColumns = json_encode(\Input::get('examMarksheetColumns'));
		}
		if(\Input::has('examSchedule')){
			$examSchedule = \Input::get('examSchedule');
			while (list($key, $value) = each($examSchedule)) {
				$examSchedule[$key]['stDate'] = $this->panelInit->date_to_unix($examSchedule[$key]['stDate'] );
			}
			$examsList->examSchedule = json_encode( $examSchedule );
		}
		$examsList->save();

		$examsList->examDate = \Input::get('examDate');
		$examsList->examEndDate = \Input::get('examEndDate');

		return $this->panelInit->apiOutput(true,$this->panelInit->language['addExam'],$this->panelInit->language['examCreated'],$examsList->toArray() );
	}

	function fetch($id){
		$exams_list = \exams_list::where('id',$id)->first()->toArray();
		$exams_list['examDate'] = $this->panelInit->unix_to_date($exams_list['examDate']);
		$exams_list['examEndDate'] = $this->panelInit->unix_to_date($exams_list['examEndDate']);
		$exams_list['examClasses'] = json_decode($exams_list['examClasses'],true);
		if(is_array($exams_list['examClasses'])){
			$exams_list['examClassesNames'] = \classes::whereIn('id',$exams_list['examClasses'])->select('className')->get()->toArray();
		}
		$exams_list['examMarksheetColumns'] = json_decode($exams_list['examMarksheetColumns'],true);
		if(!is_array($exams_list['examMarksheetColumns'])){
			$exams_list['examMarksheetColumns'] = array();
		}
		$exams_list['examSchedule'] = json_decode($exams_list['examSchedule'],true);
		if(is_array($exams_list['examSchedule'])){
			while (list($key, $value) = each($exams_list['examSchedule'])) {
				$exams_list['examSchedule'][$key]['stDate'] = $this->panelInit->unix_to_date($exams_list['examSchedule'][$key]['stDate'] );
			}
		}else{
			$exams_list['examSchedule'] = array();
		}
		return $exams_list;
	}

	function edit($id){
		if($this->data['users']->role != "admin") exit;
		$examsList = \exams_list::find($id);
		$examsList->examTitle = \Input::get('examTitle');
		$examsList->examDescription = \Input::get('examDescription');
		$examsList->examDate = $this->panelInit->date_to_unix(\Input::get('examDate'));
		$examsList->examEndDate = $this->panelInit->date_to_unix(\Input::get('examEndDate'));
		if(\Input::has('examClasses')){
			$examsList->examClasses = json_encode(\Input::get('examClasses'));
		}
		if(\Input::has('examMarksheetColumns')){
			$examsList->examMarksheetColumns = json_encode(\Input::get('examMarksheetColumns'));
		}
		if(\Input::has('examSchedule')){
			$examSchedule = \Input::get('examSchedule');
			while (list($key, $value) = each($examSchedule)) {
				$examSchedule[$key]['stDate'] = $this->panelInit->date_to_unix($examSchedule[$key]['stDate'] );
			}
			$examsList->examSchedule = json_encode( $examSchedule );
		}
		$examsList->save();

		$examsList->examDate = \Input::get('examDate');
		$examsList->examEndDate = \Input::get('examEndDate');

		return $this->panelInit->apiOutput(true,$this->panelInit->language['editExam'],$this->panelInit->language['examModified'],$examsList->toArray() );
	}

	function fetchMarks(){
		if($this->data['users']->role == "student" || $this->data['users']->role == "parent") exit;
		$toReturn = array();

		$toReturn['exam'] = \exams_list::where('id',\Input::get('exam'))->first()->toArray();
		$toReturn['subject'] = \subject::where('id',\Input::get('subjectId'))->first()->toArray();
		$toReturn['class'] = \classes::where('id',\Input::get('classId'))->first()->toArray();

		$toReturn['exam']['examClasses'] = json_decode($toReturn['exam']['examClasses'],true);
		$toReturn['exam']['examMarksheetColumns'] = json_decode($toReturn['exam']['examMarksheetColumns'],true);
		if(!is_array($toReturn['exam']['examMarksheetColumns'])){
			$toReturn['exam']['examMarksheetColumns'] = array();
		}

		$toReturn['students'] = array();
		$studentArray = \User::where('role','student')->where('studentClass',\Input::get('classId'));
		if($this->panelInit->settingsArray['enableSections'] == true){
			$studentArray = $studentArray->where('studentSection',\Input::get('sectionId'));
		}
		if($this->data['panelInit']->settingsArray['studentsSort'] != ""){
			$studentArray = $studentArray->orderByRaw($this->data['panelInit']->settingsArray['studentsSort']);
		}
		$studentArray = $studentArray->get();

		$examMarksArray = array();
		$examMarks = \exam_marks::where('examId',\Input::get('exam'))->where('classId',\Input::get('classId'))->where('subjectId',\Input::get('subjectId'))->get();
		foreach ($examMarks as $stMark) {
			$examMarksArray[$stMark->studentId] = $stMark;
		}

		$i = 0;
		foreach ($studentArray as $stOne) {
			$toReturn['students'][$i] = array('id'=>$stOne->id,'name'=>$stOne->fullName,'studentRollId'=>$stOne->studentRollId,'examMark'=>'','attendanceMark'=>'','markComments'=>'');
			if(isset($examMarksArray[$stOne->id])){
				$toReturn['students'][$i]['examMark'] = json_decode($examMarksArray[$stOne->id]->examMark,true);
				$toReturn['students'][$i]['totalMarks'] = $examMarksArray[$stOne->id]->totalMarks;
				$toReturn['students'][$i]['markComments'] = $examMarksArray[$stOne->id]->markComments;
			}
			$i ++;
		}

		echo json_encode($toReturn);
		exit;
	}

	function saveMarks($exam,$class,$subject){
		if($this->data['users']->role == "student" || $this->data['users']->role == "parent") exit;

		$studentList = array();
		$studentArray = \User::where('role','student')->where('studentClass',$class)->get();
		foreach ($studentArray as $stOne) {
			$studentList[] = $stOne->id;
		}

		$examMarksList = array();
		$examMarks = \exam_marks::where('examId',$exam)->where('classId',$class)->where('subjectId',$subject)->get();
		foreach ($examMarks as $stMark) {
			$examMarksList[$stMark->studentId] = array("examMark"=>$stMark->examMark,"attendanceMark"=>$stMark->attendanceMark,"markComments"=>$stMark->markComments);
		}

		$stMarks = \Input::get('respStudents');
		while (list($key, $value) = each($stMarks)) {
			if(!isset($examMarksList[$value['id']])){
				$examMarks = new \exam_marks();
				$examMarks->examId = $exam;
				$examMarks->classId = $class;
				$examMarks->subjectId = $subject;
				$examMarks->studentId = $value['id'];
				if(isset($value['examMark'])){
					$examMarks->examMark = json_encode($value['examMark']);
				}
				if(isset($value['totalMarks'])){
					$examMarks->totalMarks = $value['totalMarks'];
				}
				if(isset($value['markComments'])){
					$examMarks->markComments = $value['markComments'];
				}
				$examMarks->save();
			}else{
				$examMarks = \exam_marks::where('examId',$exam)->where('classId',$class)->where('subjectId',$subject)->where('studentId',$value['id'])->first();
				if(isset($value['examMark'])){
					$examMarks->examMark = json_encode($value['examMark']);
				}
				if(isset($value['totalMarks'])){
					$examMarks->totalMarks = $value['totalMarks'];
				}
				if(isset($value['markComments'])){
					$examMarks->markComments = $value['markComments'];
				}
				$examMarks->save();
			}
		}

		return $this->panelInit->apiOutput(true,$this->panelInit->language['editExam'],$this->panelInit->language['examModified'] );
	}

	function notifications($id){
		if($this->data['users']->role != "admin") exit;
		if($this->panelInit->settingsArray['examDetailsNotif'] == "0"){
			return json_encode(array("jsTitle"=>$this->panelInit->language['examDetailsNot'],"jsMessage"=>$this->panelInit->language['adjustExamNot'] ));
		}

		$examsList = \exams_list::where('id',$id)->first();

		$subjectArray = array();
		$subject = \subject::get();
		foreach ($subject as $value) {
			$subjectArray[$value->id] = $value->subjectTitle;
		}

		$usersArray = array();
		if($this->data['panelInit']->settingsArray['examDetailsNotifTo'] == "parent" || $this->data['panelInit']->settingsArray['examDetailsNotifTo'] == "both"){
			$users = \User::where('role','student')->orWhere('role','parent')->get();
		}else{
			$users = \User::where('role','student')->get();
		}
		foreach ($users as $value) {
			if($value->parentOf == "" AND $value->role == "parent") continue;
			if(!isset($usersArray[$value->id])){
				$usersArray[$value->id] = array();
			}
			if($value->parentOf != ""){
				$value->parentOf = json_decode($value->parentOf);
				if(!is_array($value->parentOf)){
					continue;
				}
				if(count($value->parentOf) > 0){
					$usersArray[$value->id]['parents'] = array();
				}
				foreach ($value->parentOf as $parentOf) {
					$usersArray[$parentOf->id]['parents'][$value->id] = array('username'=>$value->username,"email"=>$value->email,"fullName"=>$value->fullName,"mobileNo"=>$value->mobileNo,"comVia"=>$value->comVia);
				}
			}
			$usersArray[$value->id]['student'] = array('username'=>$value->username,"studentRollId"=>$value->studentRollId,"mobileNo"=>$value->mobileNo,"email"=>$value->email,"fullName"=>$value->fullName,"comVia"=>$value->comVia);
		}

		$return['marks'] = array();
		$examMarks = \exam_marks::where('examId',$id)->get();
		foreach ($examMarks as $value) {
			if(!isset($return['marks'][$value->studentId])){
				$return['marks'][$value->studentId] = array();
			}
			if(isset($subjectArray[$value->subjectId])){
				$return['marks'][$value->studentId][ $subjectArray[$value->subjectId] ] = array("examMark"=>$value->examMark,"attendanceMark"=>$value->attendanceMark,"markComments"=>$value->markComments);
			}
		}

		$mailTemplate = \mailsms_templates::where('templateTitle','Exam Details')->first();

		if($this->panelInit->settingsArray['examDetailsNotif'] == "mail" || $this->panelInit->settingsArray['examDetailsNotif'] == "mailsms"){
			$mail = true;
		}
		if($this->panelInit->settingsArray['examDetailsNotif'] == "sms" || $this->panelInit->settingsArray['examDetailsNotif'] == "mailsms"){
			$sms = true;
		}
		$sms = true;

		$MailSmsHandler = new \MailSmsHandler();
		while (list($key, $value) = each($return['marks'])) {
			if(!isset($usersArray[$key])) continue;
			if(isset($mail)){
				$studentTemplate = $mailTemplate->templateMail;
				$examGradesTable = "";
				while (list($keyG, $valueG) = each($value)) {
					if($valueG['examMark'] == "" AND $valueG['attendanceMark'] == ""){
						continue;
					}
					$examGradesTable .= $keyG. " Grade : ".$valueG['examMark']." - Attendance : ".$valueG['attendanceMark']." - Comments : ".$valueG['markComments']."<br/>";
				}
				if($examGradesTable == ""){
					continue;
				}
				$searchArray = array("{studentName}","{studentRoll}","{studentEmail}","{studentUsername}","{examTitle}","{examDescription}","{examDate}","{schoolTitle}","{examGradesTable}");
				$replaceArray = array($usersArray[$key]['student']['fullName'],$usersArray[$key]['student']['studentRollId'],$usersArray[$key]['student']['email'],$usersArray[$key]['student']['username'],$examsList->examTitle,$examsList->examDescription,$this->panelInit->unix_to_date($examsList->examDate),$this->panelInit->settingsArray['siteTitle'],$examGradesTable);
				$studentTemplate = str_replace($searchArray, $replaceArray, $studentTemplate);
				if (strpos($usersArray[$key]['student']['comVia'], 'mail') !== false) {
					$MailSmsHandler->mail($usersArray[$key]['student']['email'],"Exam grade details",$studentTemplate,$usersArray[$key]['student']['fullName']);
				}
				if(isset($usersArray[$key]['parents'])){
					while (list($keyP, $valueP) = each($usersArray[$key]['parents'])) {
						if (strpos($valueP['comVia'], 'mail') !== false) {
							$MailSmsHandler->mail($valueP['email'],"Exam grade details",$studentTemplate,$usersArray[$key]['student']['fullName']);
						}
					}
				}
			}
			if(isset($sms)){
				$studentTemplate = $mailTemplate->templateSMS;
				$examGradesTable = "";
				reset($value);
				while (list($keyG, $valueG) = each($value)) {
					if($valueG['examMark'] == "" AND $valueG['attendanceMark'] == ""){
						continue;
					}
					$examGradesTable .= $keyG. " Grade : ".$valueG['examMark']." - Attendance : ".$valueG['attendanceMark']." ";
				}
				if($examGradesTable == ""){
					continue;
				}
				$searchArray = array("{studentName}","{studentRoll}","{studentEmail}","{studentUsername}","{examTitle}","{examDescription}","{examDate}","{schoolTitle}","{examGradesTable}");
				$replaceArray = array($usersArray[$key]['student']['fullName'],$usersArray[$key]['student']['studentRollId'],$usersArray[$key]['student']['email'],$usersArray[$key]['student']['username'],$examsList->examTitle,$examsList->examDescription,$this->panelInit->unix_to_date($examsList->examDate),$this->panelInit->settingsArray['siteTitle'],$examGradesTable);
				$studentTemplate = str_replace($searchArray, $replaceArray, $studentTemplate);
				if($usersArray[$key]['student']['mobileNo'] != "" AND strpos($usersArray[$key]['student']['comVia'], 'sms') !== false){
					$MailSmsHandler->sms($usersArray[$key]['student']['mobileNo'],$studentTemplate);
				}
				if(isset($usersArray[$key]['parents'])){
					reset($usersArray[$key]['parents']);
					while (list($keyP, $valueP) = each($usersArray[$key]['parents'])) {
						if(trim($valueP['mobileNo']) != "" AND strpos($valueP['comVia'], 'sms') !== false){
							$MailSmsHandler->sms($valueP['mobileNo'],$studentTemplate);
						}
					}
				}
			}
		}

		return $this->panelInit->apiOutput(true,$this->panelInit->language['examDetailsNot'],$this->panelInit->language['examNotSent'] );
	}
}
