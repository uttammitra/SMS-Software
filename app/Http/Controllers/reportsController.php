<?php
namespace App\Http\Controllers;

class reportsController extends Controller {

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

		if(!$this->panelInit->hasThePerm('Reports')){
			exit;
		}
	}

	public function report(){
		if(\Input::get('stats') == 'usersStats' AND $this->data['users']->role == "admin"){
            return $this->usersStats();
        }
        if(\Input::get('stats') == 'stdAttendance' AND $this->data['users']->role == "admin"){
            return $this->stdAttendance(\Input::get('data'));
        }
        if(\Input::get('stats') == 'stfAttendance' AND $this->data['users']->role == "admin"){
            return $this->stfAttendance(\Input::get('data'));
        }
		if(\Input::get('stats') == 'stdVacation' AND $this->data['users']->role == "admin"){
            return $this->stdVacation(\Input::get('data'));
        }
		if(\Input::get('stats') == 'stfVacation' AND $this->data['users']->role == "admin"){
            return $this->stfVacation(\Input::get('data'));
        }
		if(\Input::get('stats') == 'payments' AND ( $this->data['users']->role == "admin" || $this->data['users']->role == "account" ) ){
            return $this->reports(\Input::get('data'));
        }
        if(\Input::get('stats') == 'expenses' AND ( $this->data['users']->role == "admin" || $this->data['users']->role == "account" ) ){
            return $this->expenses(\Input::get('data'));
        }
		if(\Input::get('stats') == 'marksheetGenerationPrepare' AND $this->data['users']->role == "admin"){
            return $this->marksheetGenerationPrepare();
        }

	}

    public function usersStats(){
        $toReturn = array();
        $toReturn['admins'] = array();
        $toReturn['admins']['activated'] = \User::where('role','admin')->where('activated','1')->count();
        $toReturn['admins']['inactivated'] = \User::where('role','admin')->where('activated','0')->count();
        $toReturn['admins']['total'] = $toReturn['admins']['activated'] + $toReturn['admins']['inactivated'];

        $toReturn['teachers'] = array();
        $toReturn['teachers']['activated'] = \User::where('role','teacher')->where('activated','1')->count();
        $toReturn['teachers']['inactivated'] = \User::where('role','teacher')->where('activated','0')->count();
        $toReturn['teachers']['total'] = $toReturn['teachers']['activated'] + $toReturn['teachers']['inactivated'];

        $toReturn['students'] = array();
        $toReturn['students']['activated'] = \User::where('role','student')->where('activated','1')->count();
        $toReturn['students']['inactivated'] = \User::where('role','student')->where('activated','0')->count();
        $toReturn['students']['total'] = $toReturn['students']['activated'] + $toReturn['students']['inactivated'];

        $toReturn['parents'] = array();
        $toReturn['parents']['activated'] = \User::where('role','parent')->where('activated','1')->count();
        $toReturn['parents']['inactivated'] = \User::where('role','parent')->where('activated','0')->count();
        $toReturn['parents']['total'] = $toReturn['parents']['activated'] + $toReturn['parents']['inactivated'];

        return $toReturn;
    }

    public function preAttendaceStats(){
        $toReturn = array();
		$classes = \classes::where('classAcademicYear',$this->panelInit->selectAcYear)->get();
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

        return $toReturn;
    }

    public function stdAttendance($data){
        $sql = "select * from attendance where ";
		$sqlArray = array();
		$toReturn = array();

		$students = array();
		$studentArray = \User::where('role','student');
		if(isset($data['classId']) AND $data['classId'] != "" ){
			$studentArray = $studentArray->where('studentClass',$data['classId']);
		}
		if(isset($data['sectionId']) AND $data['sectionId'] != "" ){
			$studentArray = $studentArray->where('studentSection',$data['sectionId']);
		}
		if($this->data['panelInit']->settingsArray['studentsSort'] != ""){
			$studentArray = $studentArray->orderByRaw($this->data['panelInit']->settingsArray['studentsSort']);
		}
		$studentArray = $studentArray->get();

		$subjectsArray = \subject::get();
		$subjects = array();
		foreach ($subjectsArray as $subject) {
			$subjects[$subject->id] = $subject->subjectTitle ;
		}

		if(isset($data['classId']) AND $data['classId'] != "" ){
			$sqlArray[] = "classId='".$data['classId']."'";
		}
		if($this->data['panelInit']->settingsArray['attendanceModel'] == "subject" AND isset($data['subjectId']) AND $data['subjectId'] != ""){
			$sqlArray[] = "subjectId='".$data['subjectId']."'";
		}
		if(isset($data['status']) AND $data['status'] != "All"){
			$sqlArray[] = "status='".$data['status']."'";
		}

		if(isset($data['attendanceDayFrom']) AND $data['attendanceDayFrom'] != "" AND isset($data['attendanceDayTo']) AND $data['attendanceDayTo'] != ""){
			$data['attendanceDayFrom'] = $this->panelInit->date_to_unix($data['attendanceDayFrom']);
			$data['attendanceDayTo'] = $this->panelInit->date_to_unix($data['attendanceDayTo']);
			$sqlArray[] = "date >= '".$data['attendanceDayFrom']."'";
			$sqlArray[] = "date <= '".$data['attendanceDayTo']."'";
		}

		$sql = $sql . implode(" AND ", $sqlArray);
		$sql = $sql . " order by date";
		$attendanceArray = \DB::select( \DB::raw($sql) );
		$attendanceList = array();

		foreach ($attendanceArray as $stAttendance) {
			$attendanceList[$stAttendance->studentId][] = $stAttendance;
		}

		$i = 0;
		foreach ($studentArray as $stOne) {
			if(isset($attendanceList[ $stOne->id ])){
				while (list(, $value) = each($attendanceList[ $stOne->id ])) {
					$toReturn[$i] = $value;
					$toReturn[$i]->studentName = $stOne->fullName;
					if($value->subjectId != ""){
						$toReturn[$i]->studentSubject = $subjects[$value->subjectId];
					}
					$toReturn[$i]->date = $this->panelInit->unix_to_date($value->date);
					$toReturn[$i]->studentRollId = $stOne->studentRollId;
					$i ++;
				}
			}
		}

		if(isset($data['exportType']) AND $data['exportType'] == "excel"){
			$data = array(1 => array ('Date','Roll Id', 'Full Name','Subject','Status'));

			foreach ($toReturn as $value) {
				if($value->status == 0){
					$value->status = $this->panelInit->language['Absent'];
				}elseif ($value->status == 1) {
					$value->status = $this->panelInit->language['Present'];
				}elseif ($value->status == 2) {
					$value->status = $this->panelInit->language['Late'];
				}elseif ($value->status == 3) {
					$value->status = $this->panelInit->language['LateExecuse'];
				}elseif ($value->status == 4) {
					$value->status = $this->panelInit->language['earlyDismissal'];
				}
				$data[] = array ($value->date, (isset($value->studentRollId)?$value->studentRollId:""),(isset($value->studentName)?$value->studentName:""),(isset($value->studentSubject)?$value->studentSubject:""),$value->status);
			}

			\Excel::create('Students-Atendance', function($excel) use($data) {

			    // Set the title
			    $excel->setTitle('Students Atendance Report');

			    // Chain the setters
			    $excel->setCreator('Schoex')->setCompany('SolutionsBricks');

				$excel->sheet('Students Atendance', function($sheet) use($data) {
					$sheet->freezeFirstRow();
					$sheet->fromArray($data, null, 'A1', true,false);
				});

			})->download('xls');
		}

		if(isset($data['exportType']) AND $data['exportType'] == "pdf"){
			$header = array ('Date','Roll Id', 'Full Name','Subject','Status');
			$data = array();
			foreach ($toReturn as $value) {
				if($value->status == 0){
					$value->status = $this->panelInit->language['Absent'];
				}elseif ($value->status == 1) {
					$value->status = $this->panelInit->language['Present'];
				}elseif ($value->status == 2) {
					$value->status = $this->panelInit->language['Late'];
				}elseif ($value->status == 3) {
					$value->status = $this->panelInit->language['LateExecuse'];
				}elseif ($value->status == 4) {
					$value->status = $this->panelInit->language['earlyDismissal'];
				}
				$data[] = array ( $value->date, (isset($value->studentRollId)?$value->studentRollId:""),(isset($value->studentName)?$value->studentName:""),(isset($value->studentSubject)?$value->studentSubject:""),$value->status);
			}

			$doc_details = array(
								"title" => "Attendance",
								"author" => $this->data['panelInit']->settingsArray['siteTitle'],
								"topMarginValue" => 10
								);

			$pdfbuilder = new \PdfBuilder($doc_details);

			$content = "<table cellspacing=\"0\" cellpadding=\"4\" border=\"1\">
		        <thead><tr>";
				foreach ($header as $value) {
					$content .="<th style='width:15%;border: solid 1px #000000; padding:2px;'>".$value."</th>";
				}
			$content .="</tr></thead><tbody>";

			foreach($data as $row)
			{
				$content .= "<tr>";
				foreach($row as $col){
					$content .="<td>".$col."</td>";
				}
				$content .= "</tr>";
			}

	        $content .= "</tbody></table>";

			$pdfbuilder->table($content, array('border' => '0','align'=>'') );
			$pdfbuilder->output('Attendance.pdf');

			exit;
		}

		return $toReturn;
    }

    public function stfAttendance($data){
        $sql = "select * from attendance where ";
		$sqlArray = array();
		$toReturn = array();

		$teachers = array();
		$teachersArray = \User::where('role','teacher');

		if($this->data['panelInit']->settingsArray['teachersSort'] != ""){
			$teachersArray = $teachersArray->orderByRaw($this->data['panelInit']->settingsArray['teachersSort']);
		}

		$teachersArray = $teachersArray->get();

		if(isset($data['status']) AND $data['status'] != "All"){
			$sqlArray[] = "status='".$data['status']."'";
		}

		if(isset($data['attendanceDayFrom']) AND $data['attendanceDayFrom'] != "" AND isset($data['attendanceDayTo']) AND $data['attendanceDayTo'] != ""){
			$data['attendanceDayFrom'] = $this->panelInit->date_to_unix($data['attendanceDayFrom']);
			$data['attendanceDayTo'] = $this->panelInit->date_to_unix($data['attendanceDayTo']);
			$sqlArray[] = "date >= (".$data['attendanceDayFrom'].") AND date <= (".$data['attendanceDayTo'].") ";
		}

        $sqlArray[] = "classId = '0'";

		$sql = $sql . implode(" AND ", $sqlArray);
		$sql = $sql ." order by date asc";
		$attendanceArray = \DB::select( \DB::raw($sql) );
		$attendanceList = array();

		foreach ($attendanceArray as $stAttendance) {
			$attendanceList[$stAttendance->studentId][] = $stAttendance;
		}

		$i = 0;
		foreach ($teachersArray as $stOne) {
			if(isset($attendanceList[$stOne->id])){
				while (list(, $value) = each($attendanceList[$stOne->id])) {
					$toReturn[$i] = $value;
					$toReturn[$i]->date = $this->panelInit->unix_to_date($value->date);
					$toReturn[$i]->studentName = $stOne->fullName;
					$i ++;
				}
			}
		}

		if(isset($data['exportType']) AND $data['exportType'] == "excel"){
			$data = array(1 => array ('Date', 'Full Name','Status'));
			foreach ($toReturn as $value) {
				if($value->status == 0){
					$value->status = $this->panelInit->language['Absent'];
				}elseif ($value->status == 1) {
					$value->status = $this->panelInit->language['Present'];
				}elseif ($value->status == 2) {
					$value->status = $this->panelInit->language['Late'];
				}elseif ($value->status == 3) {
					$value->status = $this->panelInit->language['LateExecuse'];
				}
				$data[] = array ( $value->date , $value->studentName,$value->status);
			}

			\Excel::create('Staff-Atendance', function($excel) use($data) {

			    // Set the title
			    $excel->setTitle('Staff Atendance Report');

			    // Chain the setters
			    $excel->setCreator('Schoex')->setCompany('SolutionsBricks');

				$excel->sheet('Staff Atendance', function($sheet) use($data) {
					$sheet->freezeFirstRow();
					$sheet->fromArray($data, null, 'A1', true,false);
				});

			})->download('xls');
		}

		if(isset($data['exportType']) AND $data['exportType'] == "pdf"){
			$header = array ('Date', 'Full Name','Status');
			$data = array();
			foreach ($toReturn as $value) {
				if($value->status == 0){
					$value->status = $this->panelInit->language['Absent'];
				}elseif ($value->status == 1) {
					$value->status = $this->panelInit->language['Present'];
				}elseif ($value->status == 2) {
					$value->status = $this->panelInit->language['Late'];
				}elseif ($value->status == 3) {
					$value->status = $this->panelInit->language['LateExecuse'];
				}
				$data[] = array ( $value->date , $value->studentName,$value->status);
			}

			$doc_details = array(
								"title" => "Attendance",
								"author" => $this->data['panelInit']->settingsArray['siteTitle'],
								"topMarginValue" => 10
								);

			$pdfbuilder = new \PdfBuilder($doc_details);

			$content = "<table cellspacing=\"0\" cellpadding=\"4\" border=\"1\">
				<thead><tr>";
				foreach ($header as $value) {
					$content .="<th style='width:15%;border: solid 1px #000000; padding:2px;'>".$value."</th>";
				}
			$content .="</tr></thead><tbody>";

			foreach($data as $row)
			{
				$content .= "<tr>";
				foreach($row as $col){
					$content .="<td>".$col."</td>";
				}
				$content .= "</tr>";
			}

			$content .= "</tbody></table>";

			$pdfbuilder->table($content, array('border' => '0','align'=>'') );
			$pdfbuilder->output('Attendance.pdf');

			exit;
		}

		return $toReturn;
    }

	public function stdVacation($data){
		$data['fromDate'] = $this->panelInit->date_to_unix($data['fromDate']);
		$data['toDate'] = $this->panelInit->date_to_unix($data['toDate']);

		$vacationList = \DB::table('vacation')
					->leftJoin('users', 'users.id', '=', 'vacation.userid')
					->select('vacation.id as id',
					'vacation.userid as userid',
					'vacation.vacDate as vacDate',
					'vacation.acceptedVacation as acceptedVacation',
					'users.fullName as fullName')
					->where('vacation.acYear',$this->panelInit->selectAcYear)
					->where('vacation.role','student')
					->where('vacation.vacDate','>=',$data['fromDate'])
					->where('vacation.vacDate','<=',$data['toDate'])
					->get();

		foreach ($vacationList as $key=>$value) {
			$vacationList[$key]->vacDate = $this->panelInit->unix_to_date($vacationList[$key]->vacDate);
		}

		return $vacationList;
	}

	public function stfVacation($data){
		$data['fromDate'] = $this->panelInit->date_to_unix($data['fromDate']);
		$data['toDate'] = $this->panelInit->date_to_unix($data['toDate']);

		$vacationList = \DB::table('vacation')
					->leftJoin('users', 'users.id', '=', 'vacation.userid')
					->select('vacation.id as id',
					'vacation.userid as userid',
					'vacation.vacDate as vacDate',
					'vacation.acceptedVacation as acceptedVacation',
					'users.fullName as fullName')
					->where('vacation.acYear',$this->panelInit->selectAcYear)
					->where('vacation.role','teacher')
					->where('vacation.vacDate','>=',$data['fromDate'])
					->where('vacation.vacDate','<=',$data['toDate'])
					->get();

		foreach ($vacationList as $key=>$value) {
			$vacationList[$key]->vacDate = $this->panelInit->unix_to_date($vacationList[$key]->vacDate);
		}

		return $vacationList;

	}

	public function reports($data){

		$data['fromDate'] = $this->panelInit->date_to_unix($data['fromDate']);
		$data['toDate'] = $this->panelInit->date_to_unix($data['toDate']);

		$payments = \DB::table('payments')
					->leftJoin('users', 'users.id', '=', 'payments.paymentStudent')
					->where('payments.paymentDate','>=',$data['fromDate'])
					->where('payments.paymentDate','<=',$data['toDate'])
					->select('payments.id as id',
					'payments.paymentTitle as paymentTitle',
					'payments.paymentDescription as paymentDescription',
					'payments.paymentAmount as paymentAmount',
					'payments.paidAmount as paidAmount',
					'payments.paymentStatus as paymentStatus',
					'payments.paymentDate as paymentDate',
					'payments.dueDate as dueDate',
					'payments.paymentStudent as studentId',
					'users.fullName as fullName');

		if($data['status'] != "All"){
			$payments = $payments->where('paymentStatus',$data['status']);
		}
		if(isset($data['dueInv']) AND $data['dueInv'] == true){
			$payments = $payments->where('dueDate','<',time())->where('paymentStatus','!=','1');
		}
		$payments = $payments->orderBy('id','DESC')->get();

		foreach ($payments as $key=>$value) {
			$payments[$key]->paymentDate = $this->panelInit->unix_to_date($payments[$key]->paymentDate);
			$payments[$key]->dueDate = $this->panelInit->unix_to_date($payments[$key]->dueDate);
			$payments[$key]->paymentAmount = $payments[$key]->paymentAmount + ($this->panelInit->settingsArray['paymentTax']*$payments[$key]->paymentAmount) /100;
		}

		return $payments;
	}

	public function expenses($data){

		$data['fromDate'] = $this->panelInit->date_to_unix($data['fromDate']);
		$data['toDate'] = $this->panelInit->date_to_unix($data['toDate']);

		$expenses = \DB::table('expenses')
					->leftJoin('expenses_cat','expenses_cat.id','=','expenses.expenseCategory')
					->where('expenses.expenseDate','>=',$data['fromDate'])
					->where('expenses.expenseDate','<=',$data['toDate'])
					->select('expenses.id as id',
					'expenses.expenseTitle as expenseTitle',
					'expenses.expenseAmount as expenseAmount',
					'expenses.expenseDate as expenseDate',
					'expenses.expenseCategory as expenseCategory',
					'expenses.expenseNotes as expenseNotes',
					'expenses_cat.cat_title as expenses_cat_name');

		$expenses = $expenses->orderBy('id','DESC')->get();

		foreach ($expenses as $key=>$value) {
			$expenses[$key]->expenseDate = $this->panelInit->unix_to_date($expenses[$key]->expenseDate);
		}

		return $expenses;
	}

	public function marksheetGenerationPrepare(){
		$toReturn = array();
		$toReturn['classes'] = \classes::where('classAcademicYear',$this->panelInit->selectAcYear)->get()->toArray();
		$toReturn['exams'] = \exams_list::where('examAcYear',$this->panelInit->selectAcYear)->get()->toArray();
		return $toReturn;
	}

}
