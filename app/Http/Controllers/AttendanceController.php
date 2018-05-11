<?php
namespace App\Http\Controllers;

class AttendanceController extends Controller {

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

		if(!$this->panelInit->hasThePerm('Attendance')){
			exit;
		}
	}

	public function listAll()
	{
		if($this->data['users']->role != "admin" AND $this->data['users']->role != "teacher") exit;
		$toReturn = array();
		$toReturn['attendanceModel'] = $this->data['panelInit']->settingsArray['attendanceModel'];

		if($this->data['users']->role == "teacher"){
			$toReturn['classes'] = \classes::where('classAcademicYear',$this->panelInit->selectAcYear)->where('classTeacher','LIKE','%"'.$this->data['users']->id.'"%')->get()->toArray();
		}else{
			$toReturn['classes'] = \classes::where('classAcademicYear',$this->panelInit->selectAcYear)->get()->toArray();
		}

		$toReturn['userRole'] = $this->data['users']->role;
		return $toReturn;
	}

	public function listAttendance(){
		if($this->data['users']->role != "admin" AND $this->data['users']->role != "teacher") exit;
		$toReturn = array();
		
		$toReturn['class'] = \classes::where('id',\Input::get('classId'));
		if($toReturn['class']->count() == 0){
			return $toReturn;
		}
		$toReturn['class'] = $toReturn['class']->first()->toArray();

		if(\Input::get('subjectId')){
			$toReturn['subject'] = \subject::where('id',\Input::get('subjectId'))->first()->toArray();
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

		$attendanceList = array();
		$vacationList = array();

		$vacationArray = \vacation::where('vacDate',$this->panelInit->date_to_unix(\Input::get('attendanceDay')))->where('acYear',$this->panelInit->selectAcYear)->where('role','student')->get();

		if(\Input::get('subjectId')){
			$attendanceArray = \attendance::where('classId',\Input::get('classId'))->where('subjectId',\Input::get('subjectId'))->where('date',$this->panelInit->date_to_unix(\Input::get('attendanceDay')))->get();
		}else{
			$attendanceArray = \attendance::where('classId',\Input::get('classId'))->where('date',$this->panelInit->date_to_unix(\Input::get('attendanceDay')))->get();
		}
		foreach ($attendanceArray as $stAttendance) {
			$attendanceList[$stAttendance->studentId] = $stAttendance->status;
		}
		foreach ($vacationArray as $vacation) {
			$vacationList[$vacation->userid] = $vacation->acceptedVacation;
		}

		$i = 0;
		foreach ($studentArray as $stOne) {
			$toReturn['students'][$i] = array('name'=>$stOne->fullName,'id'=>$stOne->id,'studentRollId'=>$stOne->studentRollId,'attendance'=> '', );

			if(isset($attendanceList[$stOne->id])){
				$toReturn['students'][$i]['attendance'] = $attendanceList[$stOne->id];
			}

			if(isset($vacationList[$stOne->id])){
				$toReturn['students'][$i]['vacation'] = true;
				$toReturn['students'][$i]['vacationStat'] = $vacationList[$stOne->id];
			}

			$i ++ ;
		}

		return json_encode($toReturn);
	}

	public function saveAttendance(){
		if($this->data['users']->role != "admin" AND $this->data['users']->role != "teacher") exit;

		$attendanceList = array();
		if($this->data['panelInit']->settingsArray['attendanceModel'] == "subject"){
			$attendanceArray = \attendance::where('classId',\Input::get('classId'))->where('subjectId',\Input::get('subject'))->where('date',$this->panelInit->date_to_unix(\Input::get('attendanceDay')))->get();
		}else{
			$attendanceArray = \attendance::where('classId',\Input::get('classId'))->where('date',$this->panelInit->date_to_unix(\Input::get('attendanceDay')))->get();
		}
		foreach ($attendanceArray as $stAttendance) {
			$attendanceList[$stAttendance->studentId] = $stAttendance->status;
		}

		$vacationArray = array();
		$vacationList = \vacation::where('vacDate',$this->panelInit->date_to_unix(\Input::get('attendanceDay')))->where('acYear',$this->panelInit->selectAcYear)->where('role','student')->get();
		foreach ($vacationList as $vacation) {
			$vacationArray[$vacation->userid] = $vacation->id;
		}

		if($this->panelInit->settingsArray['absentNotif'] == "mail" || $this->panelInit->settingsArray['absentNotif'] == "mailsms"){
			$mail = true;
		}
		if($this->panelInit->settingsArray['absentNotif'] == "sms" || $this->panelInit->settingsArray['absentNotif'] == "mailsms"){
			$sms = true;
		}
		if(isset($mail) || isset($sms)){
			$mailTemplate = \mailsms_templates::where('templateTitle','Student Absent')->first();
		}

		$stAttendance = \Input::get('stAttendance');
		while (list($key, $value) = each($stAttendance)) {
			if(isset($vacationArray[$value['id']])){
				$vacationEdit = \vacation::where('id',$vacationArray[$value['id']])->first();
				$vacationEdit->acceptedVacation = $value['vacationStat'];
				$vacationEdit->save();
				if($value['vacationStat'] == 1){
					$value['attendance'] = "9";
				}
			}
			if(isset($value['attendance']) AND strlen($value['attendance']) > 0){
				if(!isset($attendanceList[$value['id']])){
					$attendanceN = new \attendance();
					$attendanceN->classId = \Input::get('classId');
					$attendanceN->date = $this->panelInit->date_to_unix(\Input::get('attendanceDay'));
					$attendanceN->studentId = $value['id'];
					$attendanceN->status = $value['attendance'];
					if($this->data['panelInit']->settingsArray['attendanceModel'] == "subject"){
						$attendanceN->subjectId = \Input::get('subject');
					}
					$attendanceN->save();

					if($value['attendance'] != "1" AND $this->panelInit->settingsArray['absentNotif'] != "0"){
						$parents = \User::where('parentOf','like','%"'.$value['id'].'"%')->orWhere('parentOf','like','%:'.$value['id'].'}%')->get();
						$student = \User::where('id',$value['id'])->first();

						$absentStatus = "";
						switch ($value['attendance']) {
							case '0':
								$absentStatus = $this->panelInit->language['Absent'];
								break;
							case '2':
								$absentStatus = $this->panelInit->language['Late'];
								break;
							case '3':
								$absentStatus = $this->panelInit->language['LateExecuse'];
								break;
							case '4':
								$absentStatus = $this->panelInit->language['earlyDismissal'];
								break;
							case '9':
								$absentStatus = $this->panelInit->language['acceptedVacation'];
								break;
						}
						$MailSmsHandler = new \MailSmsHandler();
						foreach ($parents as $parent) {
							if(isset($mail) AND strpos($parent->comVia, 'mail') !== false){
								$studentTemplate = $mailTemplate->templateMail;
								$examGradesTable = "";
								$searchArray = array("{studentName}","{studentRoll}","{studentEmail}","{studentUsername}","{parentName}","{parentEmail}","{absentDate}","{absentStatus}","{schoolTitle}");
								$replaceArray = array($student->fullName,$student->studentRollId,$student->email,$student->username,$parent->fullName,$parent->email,\Input::get('attendanceDay'),$absentStatus,$this->panelInit->settingsArray['siteTitle']);
								$studentTemplate = str_replace($searchArray, $replaceArray, $studentTemplate);
								$MailSmsHandler->mail($parent->email,$this->panelInit->language['absentReport'],$studentTemplate);
							}
							if(isset($sms) AND $parent->mobileNo != "" AND strpos($parent->comVia, 'sms') !== false){
								$studentTemplate = $mailTemplate->templateSMS;
								$examGradesTable = "";
								$searchArray = array("{studentName}","{studentRoll}","{studentEmail}","{studentUsername}","{parentName}","{parentEmail}","{absentDate}","{absentStatus}","{schoolTitle}");
								$replaceArray = array($student->fullName,$student->studentRollId,$student->email,$student->username,$parent->fullName,$parent->email,\Input::get('attendanceDay'),$absentStatus,$this->panelInit->settingsArray['siteTitle']);
								$studentTemplate = str_replace($searchArray, $replaceArray, $studentTemplate);
								$MailSmsHandler->sms($parent->mobileNo,$studentTemplate);
							}
							$this->panelInit->mobNotifyUser('users',$parent->id,$this->panelInit->language['student'] . " " . $student->fullName . ":" . $absentStatus . " " . \Input::get('attendanceDay'));
						}
					}

				}else{
					if($attendanceList[$value['id']] != $value['attendance']){
						$attendanceN = \attendance::where('studentId',$value['id'])->where('date',$this->panelInit->date_to_unix(\Input::get('attendanceDay')))->where('classId',\Input::get('classId'));
						if($this->data['panelInit']->settingsArray['attendanceModel'] == "subject"){
							$attendanceN = $attendanceN->where('subjectId', \Input::get('subject') );
						}
						$attendanceN = $attendanceN->first();
						$attendanceN->status = $value['attendance'];
						$attendanceN->save();

						if($value['attendance'] != "1" AND $this->panelInit->settingsArray['absentNotif'] != "0"){
							$parents = \User::where('parentOf','like','%"'.$value['id'].'"%')->orWhere('parentOf','like','%:'.$value['id'].'}%')->get();
							$student = \User::where('id',$value['id'])->first();

							$absentStatus = "";
							switch ($value['attendance']) {
								case '0':
									$absentStatus = $this->panelInit->language['Absent'];
									break;
								case '2':
									$absentStatus = $this->panelInit->language['Late'];
									break;
								case '3':
									$absentStatus = $this->panelInit->language['LateExecuse'];
									break;
								case '4':
									$absentStatus = $this->panelInit->language['earlyDismissal'];
									break;
								case '9':
									$absentStatus = $this->panelInit->language['acceptedVacation'];
									break;
							}

							$MailSmsHandler = new \MailSmsHandler();
							foreach ($parents as $parent) {
								if(isset($mail) AND strpos($parent->comVia, 'mail') !== false){
									$studentTemplate = $mailTemplate->templateMail;
									$examGradesTable = "";
									$searchArray = array("{studentName}","{studentRoll}","{studentEmail}","{studentUsername}","{parentName}","{parentEmail}","{absentDate}","{absentStatus}","{schoolTitle}");
									$replaceArray = array($student->fullName,$student->studentRollId,$student->email,$student->username,$parent->fullName,$parent->email,\Input::get('attendanceDay'),$absentStatus,$this->panelInit->settingsArray['siteTitle']);
									$studentTemplate = str_replace($searchArray, $replaceArray, $studentTemplate);
									$MailSmsHandler->mail($parent->email,$this->panelInit->language['absentReport'],$studentTemplate);
								}
								if(isset($sms) AND $parent->mobileNo != "" AND strpos($parent->comVia, 'sms') !== false){
									$studentTemplate = $mailTemplate->templateSMS;
									$examGradesTable = "";
									$searchArray = array("{studentName}","{studentRoll}","{studentEmail}","{studentUsername}","{parentName}","{parentEmail}","{absentDate}","{absentStatus}","{schoolTitle}");
									$replaceArray = array($student->fullName,$student->studentRollId,$student->email,$student->username,$parent->fullName,$parent->email,\Input::get('attendanceDay'),$absentStatus,$this->panelInit->settingsArray['siteTitle']);
									$studentTemplate = str_replace($searchArray, $replaceArray, $studentTemplate);
									$MailSmsHandler->sms($parent->mobileNo,$studentTemplate);
								}
								$this->panelInit->mobNotifyUser('users',$parent->id,$this->panelInit->language['student'] . " " . $student->fullName . ":" . $absentStatus . " " . \Input::get('attendanceDay'));
							}
						}
					}
				}
			}
		}

		return $this->panelInit->apiOutput(true,"Attendance",$this->panelInit->language['attendanceSaved'] );
	}

	public function getStats($date = ""){
		if($date == ""){
			$date = date('m/Y');
		}

		$startTime = time() - (30*60*60*24);
		$endTime = time() + (60*60*24);

		$toReturn = array();
		$classes = \classes::where('classAcademicYear',$this->panelInit->selectAcYear)->get();

		if($this->data['users']->role == "teacher"){
			$classes = \classes::where('classAcademicYear',$this->panelInit->selectAcYear)->where('classTeacher','LIKE','%"'.$this->data['users']->id.'"%')->get();
		}else{
			$classes = \classes::where('classAcademicYear',$this->panelInit->selectAcYear)->get();
		}

		$toReturn['classes'] = array();
		$subjList = array();
		foreach ($classes as $class) {
			$class['classSubjects'] = json_decode($class['classSubjects'],true);
			if(is_array($class['classSubjects'])){
				foreach ($class['classSubjects'] as $subject) {
					$subjList[] = $subject;
				}
			}
			$toReturn['classes'][$class->id] = $class->className ;
		}

		$subjList = array_unique($subjList);
		if($this->data['panelInit']->settingsArray['attendanceModel'] == "subject"){
			$toReturn['subjects'] = array();
			if(count($subjList) > 0){
				$subjects = \subject::whereIN('id',$subjList)->get();
				foreach ($subjects as $subject) {
					$toReturn['subjects'][$subject->id] = $subject->subjectTitle ;
				}
			}
		}

		$toReturn['role'] = $this->data['users']->role;
		$toReturn['attendanceModel'] = $this->data['panelInit']->settingsArray['attendanceModel'];

		if($this->data['users']->role == "admin" || $this->data['users']->role == "teacher"){
			$attendanceArray = \attendance::where('date','>=',$startTime)->where('date','<=',$endTime)->orderBy('date','asc')->get();

		}elseif($this->data['users']->role == "student"){
			$attendanceArray = \attendance::where('studentId',$this->data['users']->id)->where('date','>=',$startTime)->where('date','<=',$endTime)->orderBy('date','asc')->get();
			foreach ($attendanceArray as $value) {
				$toReturn['studentAttendance'][] = array("date"=>$this->panelInit->unix_to_date($value->date),"status"=>$value->status,"subject"=>isset($toReturn['subjects'][$value->subjectId])?$toReturn['subjects'][$value->subjectId]:"" ) ;
			}
		}elseif($this->data['users']->role == "parent"){
			if($this->data['users']->parentOf != ""){
				$parentOf = json_decode($this->data['users']->parentOf,true);
				if(!is_array($parentOf)){
					$parentOf = array();
				}
				$ids = array();
				while (list(, $value) = each($parentOf)) {
					$ids[] = $value['id'];
				}

				$studentArray = \User::where('role','student')->whereIn('id',$ids)->get();
				foreach ($studentArray as $stOne) {
					$students[$stOne->id] = array('name'=>$stOne->fullName,'studentRollId'=>$stOne->studentRollId);
				}

				if(count($ids) > 0){
					$attendanceArray = \attendance::whereIn('studentId',$ids)->where('date','>=',$startTime)->where('date','<=',$endTime)->orderBy('date','asc')->get();
					foreach ($attendanceArray as $value) {
						if(isset($students[$value->studentId]) AND !isset($toReturn['studentAttendance'][$value->studentId])){
							$toReturn['studentAttendance'][$value->studentId]['n'] = $students[$value->studentId];
							$toReturn['studentAttendance'][$value->studentId]['d'] = array();
						}
						if(isset($toReturn['studentAttendance'][$value->studentId]['d'])){
							$toReturn['studentAttendance'][$value->studentId]['d'][] = array("date"=>$this->panelInit->unix_to_date($value->date),"status"=>$value->status,"subject"=>$value->subjectId);
						}
					}
				}
			}
		}
		return $toReturn;
	}
}
