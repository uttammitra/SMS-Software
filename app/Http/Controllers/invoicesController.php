<?php
namespace App\Http\Controllers;

class invoicesController extends Controller {

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

		if(!$this->panelInit->hasThePerm('accounting')){
			exit;
		}
	}

	public function listAll($page = 1)
	{
		$toReturn = array();

		if($this->data['users']->role == "admin" || $this->data['users']->role == "account"){

			$toReturn['invoices'] = \DB::table('payments')
						->leftJoin('users', 'users.id', '=', 'payments.paymentStudent')
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

		}elseif($this->data['users']->role == "student" || $this->data['users']->role == "teacher"){

			$toReturn['invoices'] = \DB::table('payments')
						->where('paymentStudent',$this->data['users']->id)
						->leftJoin('users', 'users.id', '=', 'payments.paymentStudent')
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

		}elseif($this->data['users']->role == "parent"){

			$studentId = array();
			$parentOf = json_decode($this->data['users']->parentOf,true);
			if(is_array($parentOf)){
				while (list($key, $value) = each($parentOf)) {
					$studentId[] = $value['id'];
				}
			}
			$toReturn['invoices'] = \DB::table('payments')
						->whereIn('paymentStudent',$studentId)
						->leftJoin('users', 'users.id', '=', 'payments.paymentStudent')
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
		}

		if(\Input::has('searchInput')){
			$searchInput = \Input::get('searchInput');
			if(is_array($searchInput)){

				if(isset($searchInput['dueInv']) AND $searchInput['dueInv'] == true){
					$toReturn['invoices'] = $toReturn['invoices']->where('dueDate','<',time())->where('paymentStatus','!=','1');
				}

				if(isset($searchInput['text']) AND strlen($searchInput['text']) > 0 ){
					$keyword = $searchInput['text'];
					$toReturn['invoices'] = $toReturn['invoices']->where(function($query) use ($keyword){
						$query->where('payments.paymentTitle','LIKE','%'.$keyword.'%');
						$query->orWhere('payments.paymentDescription','LIKE','%'.$keyword.'%');
						$query->orWhere('fullName','LIKE','%'.$keyword.'%');
					});
				}

				if(isset($searchInput['paymentStatus']) AND $searchInput['paymentStatus'] != ""){
					$toReturn['invoices'] = $toReturn['invoices']->where('paymentStatus',$searchInput['paymentStatus']);
				}

				if(isset($searchInput['fromDate']) AND $searchInput['fromDate'] != ""){
					$searchInput['fromDate'] = $this->panelInit->date_to_unix($searchInput['fromDate']);
					$toReturn['invoices'] = $toReturn['invoices']->where('paymentDate','>=',$searchInput['fromDate']);
				}

				if(isset($searchInput['toDate']) AND $searchInput['toDate'] != ""){
					$searchInput['toDate'] = $this->panelInit->date_to_unix($searchInput['toDate']);
					$toReturn['invoices'] = $toReturn['invoices']->where('paymentDate','<=',$searchInput['toDate']);
				}

			}
		}

		$toReturn['totalItems'] = $toReturn['invoices']->count();
		$toReturn['invoices'] = $toReturn['invoices']->orderBy('id','DESC')->take('20')->skip(20* ($page - 1) )->get();

		foreach ($toReturn['invoices'] as $key => $value) {
			$toReturn['invoices'][$key]->paymentDate = $this->panelInit->unix_to_date($toReturn['invoices'][$key]->paymentDate);
			$toReturn['invoices'][$key]->dueDate = $this->panelInit->unix_to_date($toReturn['invoices'][$key]->dueDate);
			$toReturn['invoices'][$key]->paymentAmount = $toReturn['invoices'][$key]->paymentAmount + ($this->panelInit->settingsArray['paymentTax']*$toReturn['invoices'][$key]->paymentAmount) /100;
		}

		$toReturn['currency_symbol'] = $this->panelInit->settingsArray['currency_symbol'];

		$classes = \classes::where('classAcademicYear',$this->panelInit->selectAcYear)->get();
		$toReturn['classes'] = array();
		foreach ($classes as $class) {
			$toReturn['classes'][$class->id] = $class->className ;
		}

		return $toReturn;
	}

	public function delete($id){
		if($this->data['users']->role != "admin" && $this->data['users']->role != "account") exit;
		if ( $postDelete = \payments::where('id', $id)->first() )
        {
            $postDelete->delete();
            return $this->panelInit->apiOutput(true,$this->panelInit->language['delPayment'],$this->panelInit->language['paymentDel']);
        }else{
            return $this->panelInit->apiOutput(false,$this->panelInit->language['delPayment'],$this->panelInit->language['paymentNotExist']);
        }
	}

	public function create(){
		if($this->data['users']->role != "admin" && $this->data['users']->role != "account") exit;
		$craetedPayments = array();
		$studentClass = \Input::get('paymentStudent');
		if(!is_array($studentClass)){
			return $this->panelInit->apiOutput(false,$this->panelInit->language['addPayment'],"No students are selected");
		}
		while (list($key, $value) = each($studentClass)) {
			if($value['id'] == "" || $value['id'] == "0"){
				continue;
			}
			$payments = new \payments();
			$payments->paymentTitle = \Input::get('paymentTitle');
			if(\Input::has('paymentDescription')){
				$payments->paymentDescription = \Input::get('paymentDescription');
			}
			$payments->paymentStudent = $value['id'];

			if(\Input::has('paymentRows')){
				$payments->paymentRows = json_encode(\Input::get('paymentRows'));

				$paymentAmount = 0;
				$paymentRows = \Input::get('paymentRows');
				while (list($key, $value) = each($paymentRows)) {
					$paymentAmount += $value['amount'];
				}
			}else{
				$paymentRows = array();
				$payments->paymentRows = json_encode($paymentRows);
				$paymentAmount = 0;
			}

			$payments->paymentAmount = $paymentAmount;
			$payments->paymentDate = $this->panelInit->date_to_unix(\Input::get('paymentDate'));
			$payments->dueDate = $this->panelInit->date_to_unix(\Input::get('dueDate'));

			$payments->paymentUniqid = uniqid();
			$payments->paymentStatus = \Input::get('paymentStatus');
			if(\Input::get('paymentStatus') == 1){
				$payments->paidAmount = $paymentAmount;
				if(\Input::has('paidMethod')){
					$payments->paidMethod = \Input::get('paidMethod');
				}
				if(\Input::has('paidTime')){
					$payments->paidTime = \Input::get('paidTime');
				}
			}
			$payments->save();

			$this->panelInit->mobNotifyUser('users',$value['id'], $this->panelInit->language['newPaymentNotif']);

			$payments->paymentDate = \Input::get('paymentDate');
			$payments->dueDate = \Input::get('dueDate');

			$craetedPayments[] = $payments->toArray();
		}

		return $this->panelInit->apiOutput(true,$this->panelInit->language['addPayment'],$this->panelInit->language['paymentCreated'],$craetedPayments );
	}

	function invoice($id){
		$return = array();
		$return['payment'] = \payments::where('id',$id)->first()->toArray();
		$return['payment']['paymentDate'] = $this->panelInit->unix_to_date($return['payment']['paymentDate']);
		if($return['payment']['dueDate'] < time()){
			$return['payment']['isDueDate'] = true;
		}
		$return['payment']['dueDate'] = $this->panelInit->unix_to_date($return['payment']['dueDate']);
		if($return['payment']['paymentStatus'] == "1"){
			$return['payment']['paidTime'] = $this->panelInit->unix_to_date($return['payment']['paidTime']);
		}
		$return['payment']['paymentRows'] = json_decode($return['payment']['paymentRows'],true);
		$return['siteTitle'] = $this->panelInit->settingsArray['siteTitle'];
		$return['baseUrl'] = \URL::to('/');
		$return['address'] = $this->panelInit->settingsArray['address'];
		$return['address2'] = $this->panelInit->settingsArray['address2'];
		$return['systemEmail'] = $this->panelInit->settingsArray['systemEmail'];
		$return['phoneNo'] = $this->panelInit->settingsArray['phoneNo'];
		$return['paypalPayment'] = $this->panelInit->settingsArray['paypalPayment'];
		$return['currency_code'] = $this->panelInit->settingsArray['currency_code'];
		$return['currency_symbol'] = $this->panelInit->settingsArray['currency_symbol'];
		$return['paymentTax'] = $this->panelInit->settingsArray['paymentTax'];
		$return['amountTax'] = ($this->panelInit->settingsArray['paymentTax']*$return['payment']['paymentAmount']) /100;
		$return['totalWithTax'] = $return['payment']['paymentAmount'] + $return['amountTax'];
		$return['pendingAmount'] = $return['totalWithTax'] - $return['payment']['paidAmount'];
		$return['user'] = \User::where('users.id',$return['payment']['paymentStudent'])->leftJoin('classes','users.studentClass','=','classes.id')->leftJoin('sections','users.studentSection','=','sections.id')->select('users.*','classes.className','sections.sectionName','sections.sectionTitle')->first()->toArray();

		$return['paypalEnabled'] = $this->panelInit->settingsArray['paypalEnabled'];
		$return['2coEnabled'] = $this->panelInit->settingsArray['2coEnabled'];
		$return['payumoneyEnabled'] = $this->panelInit->settingsArray['payumoneyEnabled'];

		$return['collection'] = \paymentsCollection::where('invoiceId',$id)->get()->toArray();
		while (list($key, $value) = each($return['collection'])) {
			$return['collection'][$key]['collectionDate'] = $this->panelInit->unix_to_date($return['collection'][$key]['collectionDate']);
		}

		return $return;
	}

	function fetch($id){
		$payments = \payments::where('id',$id)->first()->toArray();
		$payments['paymentDate'] = $this->panelInit->unix_to_date($payments['paymentDate']);
		$payments['dueDate'] = $this->panelInit->unix_to_date($payments['dueDate']);
		$payments['paymentRows'] = json_decode($payments['paymentRows'],true);
		if(!is_array($payments['paymentRows'])){
			$payments['paymentRows'] = array();
			$payments['paymentRows'][] = array('title'=>$payments['paymentDescription'],'amount'=>$payments['paymentAmount']);
		}
		return $payments;
	}

	function edit($id){
		if($this->data['users']->role != "admin" && $this->data['users']->role != "account") exit;
		$payments = \payments::find($id);
		$payments->paymentTitle = \Input::get('paymentTitle');
		if(\Input::has('paymentDescription')){
			$payments->paymentDescription = \Input::get('paymentDescription');
		}

		if(\Input::has('paymentRows')){
			$payments->paymentRows = json_encode(\Input::get('paymentRows'));

			$paymentAmount = 0;
			$paymentRows = \Input::get('paymentRows');
			while (list($key, $value) = each($paymentRows)) {
				$paymentAmount += $value['amount'];
			}
		}else{
			$paymentRows = array();
			$payments->paymentRows = json_encode($paymentRows);
			$paymentAmount = 0;
		}

		$payments->paymentAmount = $paymentAmount;
		$payments->paymentDate = $this->panelInit->date_to_unix(\Input::get('paymentDate'));
		$payments->dueDate = $this->panelInit->date_to_unix(\Input::get('dueDate'));
		$payments->save();

		$payments->paymentDate = \Input::get('paymentDate');
		$payments->dueDate = \Input::get('dueDate');

		return $this->panelInit->apiOutput(true,$this->panelInit->language['editPayment'],$this->panelInit->language['paymentModified'],$payments->toArray() );
	}

	function collect($id){
		if($this->data['users']->role != "admin" && $this->data['users']->role != "account") exit;
		$payments = \payments::where('id',$id);
		if($payments->count() == 0){
			return;
		}
		$payments = $payments->first();

		$amountTax = ($this->panelInit->settingsArray['paymentTax']*$payments->paymentAmount) /100;
		$totalWithTax = $payments->paymentAmount + $amountTax;
		$pendingAmount = $totalWithTax - $payments->paidAmount;

		if(bccomp(\Input::get('collectionAmount'), $pendingAmount,10) == 1){
			return $this->panelInit->apiOutput(false,"Invoice Collection","Collection amount is greater that invoice pending amount");
		}

		$paymentsCollection = new \paymentsCollection();
		$paymentsCollection->invoiceId = $id;
		$paymentsCollection->collectionAmount = \Input::get('collectionAmount');
		$paymentsCollection->collectionDate = $this->panelInit->date_to_unix(\Input::get('collectionDate'));
		$paymentsCollection->collectionMethod = \Input::get('collectionMethod');
		if(\Input::has('collectionNote')){
			$paymentsCollection->collectionNote = \Input::get('collectionNote');
		}
		$paymentsCollection->collectedBy = $this->data['users']->id;
		$paymentsCollection->save();

		$payments->paidAmount = $payments->paidAmount+$paymentsCollection->collectionAmount;
		if($payments->paidAmount >= $totalWithTax){
			$payments->paymentStatus = 1;
		}else{
			$payments->paymentStatus = 2;
		}
		$payments->paidMethod = \Input::get('collectionMethod');
		$payments->paidTime = $this->panelInit->date_to_unix(\Input::get('collectionDate'));
		$payments->save();

		$payments->paymentAmount = $totalWithTax;

		return $this->panelInit->apiOutput(true,"Invoice Collection","Collection completed successfully",$payments->toArray());
	}

	function revert($id){
		if($this->data['users']->role != "admin" && $this->data['users']->role != "account") exit;
		$paymentsCollection = \paymentsCollection::where('id',$id);
		if($paymentsCollection->count() == 0){
			return;
		}
		$paymentsGet = $paymentsCollection->first();
		$invoice = $paymentsGet->invoiceId;
		$paymentsCollection = $paymentsCollection->delete();

		//recalculate
		$totalPaid = 0;
		$paymentsCollection = \paymentsCollection::where('invoiceId',$invoice)->get();
		foreach ($paymentsCollection as $key => $value) {
			$totalPaid += $value['collectionAmount'];
		}

		$payments = \payments::where('id',$invoice);
		if($payments->count() == 0){
			return;
		}
		$payments = $payments->first();

		$amountTax = ($this->panelInit->settingsArray['paymentTax']*$payments->paymentAmount) /100;
		$totalWithTax = $payments->paymentAmount + $amountTax;

		if($totalPaid >= $totalWithTax){
			$payments->paymentStatus = 1;
		}elseif ($totalPaid == 0) {
			$payments->paymentStatus = 0;
		}else{
			$payments->paymentStatus = 2;
		}
		$payments->paidAmount = $totalPaid;
		$payments->save();

		return $this->panelInit->apiOutput(true,"Revert Invoice Collection","Collection reverted successfully",$payments->toArray());
	}

	function paymentSuccess($uniqid){
		$payments = \payments::where('paymentUniqid',$uniqid)->first();
		if(\Input::get('verify_sign')){
			$payments->paymentStatus = 1;
			$payments->paymentSuccessDetails = json_encode(\Input::all());
			$payments->save();
		}
		return \Redirect::to('/#/payments');
	}

	function PaymentData($id){
		if($this->data['users']->role != "admin" && $this->data['users']->role != "account") exit;
		$payments = \payments::where('id',$id)->first();
		if($payments->paymentSuccessDetails == ""){
			return $this->panelInit->apiOutput(false,$this->panelInit->language['paymentDetails'],$this->panelInit->language['noPaymentDetails'] );
		}else{
			return $this->panelInit->apiOutput(true,null,null,json_decode($payments->paymentSuccessDetails,true) );
		}
	}

	function paymentFailed(){
		return \Redirect::to('/#/payments');
	}

	public function searchStudents($student){
		$students = \User::where('role','student')->where('fullName','like','%'.$student.'%')->orWhere('username','like','%'.$student.'%')->orWhere('email','like','%'.$student.'%')->get();
		$retArray = array();
		foreach ($students as $student) {
			$retArray[$student->id] = array("id"=>$student->id,"name"=>$student->fullName,"email"=>$student->email);
		}
		return json_encode($retArray);
	}

	function export($type){
		if($this->data['users']->role != "admin" && $this->data['users']->role != "account") exit;
		if($type == "excel"){

			$return['currency_symbol'] = $this->panelInit->settingsArray['currency_symbol'];

			$data = array(1 => array ('Invoice ID','Title','Student','Amount','Paid Amount','Date','Due Date','Status'));

			$toReturn['invoices'] = \DB::table('payments')
						->leftJoin('users', 'users.id', '=', 'payments.paymentStudent')
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
			$toReturn['totalItems'] = $toReturn['invoices']->count();
			$toReturn['invoices'] = $toReturn['invoices']->orderBy('id','DESC')->get();

			foreach ($toReturn['invoices'] as $key => $value) {
				$value->paymentDate = $this->panelInit->unix_to_date($toReturn['invoices'][$key]->paymentDate);
				$value->dueDate = $this->panelInit->unix_to_date($toReturn['invoices'][$key]->dueDate);
				$value->paymentAmount = $toReturn['invoices'][$key]->paymentAmount + ($this->panelInit->settingsArray['paymentTax']*$toReturn['invoices'][$key]->paymentAmount) /100;
				if($value->paymentStatus == 1){
					$paymentStatus = "PAID";
				}elseif($value->paymentStatus == 2){
					$paymentStatus = "PARTIALLY PAID";
				}else{
					$paymentStatus = "UNPAID";
				}
				$data[] = array($value->paymentTitle,$value->paymentDescription,$value->fullName,$return['currency_symbol']." ".$value->paymentAmount,$return['currency_symbol']." ".$value->paidAmount,$value->paymentDate,$value->dueDate,$paymentStatus);
			}

			\Excel::create('Payments-Sheet', function($excel) use($data) {

			    // Set the title
			    $excel->setTitle('Payments Sheet');

			    // Chain the setters
			    $excel->setCreator('Schoex')->setCompany('SolutionsBricks');

				$excel->sheet('Payments', function($sheet) use($data) {
					$sheet->freezeFirstRow();
					$sheet->fromArray($data, null, 'A1', true,false);
				});

			})->download('xls');

		}elseif ($type == "pdf") {
			$return['currency_symbol'] = $this->panelInit->settingsArray['currency_symbol'];

			$header = array ('Invoice ID','Title','Student','Amount','Paid Amount','Date','Due Date','Status');
			$data = array();

			$toReturn['invoices'] = \DB::table('payments')
						->leftJoin('users', 'users.id', '=', 'payments.paymentStudent')
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
			$toReturn['totalItems'] = $toReturn['invoices']->count();
			$toReturn['invoices'] = $toReturn['invoices']->orderBy('id','DESC')->limit('100')->get();

			foreach ($toReturn['invoices'] as $key => $value) {
				$value->paymentDate = $this->panelInit->unix_to_date($toReturn['invoices'][$key]->paymentDate);
				$value->dueDate = $this->panelInit->unix_to_date($toReturn['invoices'][$key]->dueDate);
				$value->paymentAmount = $toReturn['invoices'][$key]->paymentAmount + ($this->panelInit->settingsArray['paymentTax']*$toReturn['invoices'][$key]->paymentAmount) /100;
				if($value->paymentStatus == 1){
					$paymentStatus = "PAID";
				}elseif($value->paymentStatus == 2){
					$paymentStatus = "PARTIALLY PAID";
				}else{
					$paymentStatus = "UNPAID";
				}
				$data[] = array($value->paymentTitle,$value->paymentDescription,$value->fullName,$return['currency_symbol']." ".$value->paymentAmount,$return['currency_symbol']." ".$value->paidAmount,$value->paymentDate,$value->dueDate,$paymentStatus);
			}

			$doc_details = array(
								"title" => "Payments",
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
			$pdfbuilder->output('Payments.pdf');

		}
	}

	public static function generateInvoice($user,$case){
		if($user->studentClass == "" || $user->studentClass == "0"){
			return;
		}

		$feeAllocationUser = \fee_allocation::where('allocationType','student')->where('allocationWhen',$case)->where('allocationId',$user->id)->get()->toArray();
		$feeAllocationClass = \fee_allocation::where('allocationType','class')->where('allocationWhen',$case)->where('allocationId',$user->studentClass)->get()->toArray();

		$feeTypesArray = array();
		$feeTypes = \fee_type::get();
		foreach($feeTypes as $type){
			$feeTypesArray[$type->id] = $type->feeTitle;
		}

		if(count($feeAllocationUser) > 0){
			foreach ($feeAllocationUser as $allocatedUser) {

				$paymentDescription = array();
				$paymentAmount = 0;
				$allocationValues = json_decode($allocatedUser->allocationValues,true);
				while (list($key, $value) = each($allocationValues)) {
					if(isset($feeTypesArray[$key])){
						$paymentDescription[] = $feeTypesArray[$key];
						$paymentAmount += $value;
					}
				}

				$payments = new \payments();
				$payments->paymentTitle = $allocatedUser->allocationTitle;
				$payments->paymentDescription = implode(", ",$paymentDescription);
				$payments->paymentStudent = $user->id;
				$payments->paymentAmount = $paymentAmount;
				$payments->paymentStatus = "0";
				$payments->paymentDate = time();
				$payments->paymentUniqid = uniqid();
				$payments->save();

			}
		}

		if(count($feeAllocationClass) > 0){
			foreach ($feeAllocationClass as $allocatedUser) {

				$paymentDescription = array();
				$paymentAmount = 0;
				$allocationValues = json_decode($allocatedUser['allocationValues'],true);
				while (list($key, $value) = each($allocationValues)) {
					if(isset($feeTypesArray[$key])){
						$paymentDescription[] = $feeTypesArray[$key];
						$paymentAmount += $value;
					}
				}

				$payments = new \payments();
				$payments->paymentTitle = $allocatedUser['allocationTitle'];
				$payments->paymentDescription = implode(", ",$paymentDescription);
				$payments->paymentStudent = $user->id;
				$payments->paymentAmount = $paymentAmount;
				$payments->paymentStatus = "0";
				$payments->paymentDate = time();
				$payments->paymentUniqid = uniqid();
				$payments->save();

			}
		}

	}
}
