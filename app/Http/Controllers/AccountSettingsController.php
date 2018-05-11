<?php
namespace App\Http\Controllers;

class AccountSettingsController extends Controller {

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
	}

	public function listAll()
	{
		$data = \User::where('id',\Auth::user()->id)->first()->toArray();
		$data['birthday'] = $this->panelInit->unix_to_date($data['birthday']);
		return $data;
	}

	public function langs()
	{
		$settingsArray = array();

		$languages = \languages::get();
		foreach ($languages as $language) {
			$settingsArray['languages'][$language->id] = $language->languageTitle;
		}

		$settingsArray['languageAllow'] = $this->panelInit->settingsArray['languageAllow'];
		$settingsArray['layoutColorUserChange'] = $this->panelInit->settingsArray['layoutColorUserChange'];

		return $settingsArray;
	}

	function saveProfile(){

		if(\Input::has('spec')){
			if( in_array(\Input::get('spec'),array('defTheme','defLang')) ){

				if(\Input::get('spec') == "defTheme"){
					$User = \User::where('id',\Auth::user()->id)->first();
					$User->defTheme = \Input::get('value');
					$User->save();
				}

				if(\Input::get('spec') == "defLang"){
					$User = \User::where('id',\Auth::user()->id)->first();
					$User->defLang = \Input::get('value');
					$User->save();
				}

				return $this->panelInit->apiOutput(true,$this->panelInit->language['ChgProfileData'],$this->panelInit->language['profileUpdated']);
			}
		}

		$User = \User::where('id',\Auth::user()->id)->first();
		$User->fullName = \Input::get('fullName');
		$User->gender = \Input::get('gender');
		$User->address = \Input::get('address');
		$User->phoneNo = \Input::get('phoneNo');
		$User->mobileNo = \Input::get('mobileNo');
		if(\Input::get('birthday') != ""){
			$User->birthday = $this->panelInit->date_to_unix(\Input::get('birthday'));
		}
		if (\Input::hasFile('photo')) {
			$fileInstance = \Input::file('photo');
			$newFileName = "profile_".$User->id.".jpg";
			$file = $fileInstance->move('uploads/profile/',$newFileName);

			$User->photo = "profile_".$User->id.".jpg";
		}
		$User->save();

		$data = \User::where('id',\Auth::user()->id)->first()->toArray();
		$data['birthday'] = $this->panelInit->unix_to_date($data['birthday']);

		return $this->panelInit->apiOutput(true,$this->panelInit->language['ChgProfileData'],$this->panelInit->language['profileUpdated'],$data);
	}

	function saveEmail(){
		if(\User::where('email',\Input::get('email'))->count() > 0){
			return $this->panelInit->apiOutput(false,"Update profile",$this->panelInit->language['mailAlreadyUsed']);
		}
		if (!\Hash::check(\Input::get('password'), $this->data['users']->password)) {
			return $this->panelInit->apiOutput(false,$this->panelInit->language['editPassword'],$this->panelInit->language['oldPwdDontMatch']);
		}
		$User = \User::where('id',\Auth::user()->id)->first();
		$User->email = \Input::get('email');
		$User->save();

		return $this->panelInit->apiOutput(true,$this->panelInit->language['ChgProfileData'],$this->panelInit->language['profileUpdated']);
	}

	function savePassword(){
		if (\Hash::check(\Input::get('password'), $this->data['users']->password)) {
			$User = \User::where('id',\Auth::user()->id)->first();
			$User->password = \Hash::make(\Input::get('newPassword'));
			$User->save();

			return $this->panelInit->apiOutput(true,$this->panelInit->language['editPassword'],$this->panelInit->language['pwdChangedSuccess']);
		}else{
			return $this->panelInit->apiOutput(false,$this->panelInit->language['editPassword'],$this->panelInit->language['oldPwdDontMatch']);
		}
	}

	function invoices(){
		$toReturn = array();

		if($this->data['users']->role == "student"){

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
						'users.fullName as fullName')->orderBy('id','DESC')->get();

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
						'users.fullName as fullName')->orderBy('id','DESC')->get();
		}

		if(isset($toReturn['invoices'])){
			foreach ($toReturn['invoices'] as $key => $value) {
				$toReturn['invoices'][$key]->paymentDate = $this->panelInit->unix_to_date($toReturn['invoices'][$key]->paymentDate);
				$toReturn['invoices'][$key]->dueDate = $this->panelInit->unix_to_date($toReturn['invoices'][$key]->dueDate);
			}
		}

		return $toReturn;
	}

	function invoicesDetails($id){
		$studentId = array();
		$studentId[] = \Auth::user()->id;
		if($this->data['users']->role == "parent"){
			$parentOf = json_decode($this->data['users']->parentOf,true);
			if(is_array($parentOf)){
				while (list($key, $value) = each($parentOf)) {
					$studentId[] = $value['id'];
				}
			}
		}
		$return = array();
		$return['payment'] = \payments::where('id',$id)->whereIn('paymentStudent',$studentId);
		if($return['payment']->count() > 0){
			$return['payment'] = $return['payment']->first()->toArray();
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
			$return['user'] = \User::where('users.id',$return['payment']['paymentStudent'])->leftJoin('classes','users.studentClass','=','classes.id')->leftJoin('sections','users.studentSection','=','sections.id')->select('users.*','classes.className','sections.sectionName','sections.sectionTitle')->first()->toArray();
			return $return;
		}
	}

}
