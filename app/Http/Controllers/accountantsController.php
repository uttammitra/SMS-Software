<?php
namespace App\Http\Controllers;

class accountantsController extends Controller {

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

		if(!$this->panelInit->hasThePerm('Administrators')){
			exit;
		}
	}

	public function listAll()
	{
		return \User::where('role','account')->get();
	}

	public function delete($id){
		if ( $postDelete = \User::where('role','account')->where('id',$id)->first() )
        {
            $postDelete->delete();
            return $this->panelInit->apiOutput(true,$this->panelInit->language['delAccountant'],$this->panelInit->language['accountantDelSucc']);
        }else{
            return $this->panelInit->apiOutput(false,$this->panelInit->language['delAccountant'],$this->panelInit->language['accountantNotExist']);
        }
	}

	public function create(){
		if(\User::where('username',trim(\Input::get('username')))->count() > 0){
			return $this->panelInit->apiOutput(false,$this->panelInit->language['addAccountant'],$this->panelInit->language['usernameAlreadyUsed']);
		}
		if(\User::where('email',\Input::get('email'))->count() > 0){
			return $this->panelInit->apiOutput(false,$this->panelInit->language['addAccountant'],$this->panelInit->language['emailAlreadyUsed']);
		}
		$User = new \User();
		$User->username = \Input::get('username');
		$User->email = \Input::get('email');
		$User->fullName = \Input::get('fullName');
		$User->password = \Hash::make(\Input::get('password'));
		$User->role = "account";
		if(\Input::has('comVia')){
			$User->comVia = json_encode(\Input::get('comVia'));
		}
		$User->save();

		if (\Input::hasFile('photo')) {
			$fileInstance = \Input::file('photo');
			$newFileName = "profile_".$User->id.".jpg";
			$file = $fileInstance->move('uploads/profile/',$newFileName);

			$User->photo = "profile_".$User->id.".jpg";
			$User->save();
		}

		return $this->panelInit->apiOutput(true,$this->panelInit->language['addAccountant'],$this->panelInit->language['accountantCreated'],$User->toArray());
	}

	function fetch($id){
		$user = \User::where('role','account')->where('id',$id)->first()->toArray();
		$user['comVia'] = json_decode($user['comVia'],true);
		if(!is_array($user['comVia'])){
			$user['comVia'] = array();
		}
		return $user;
	}

	function edit($id){
		if(\User::where('username',trim(\Input::get('username')))->where('id','<>',$id)->count() > 0){
			return $this->panelInit->apiOutput(false,$this->panelInit->language['editAccountant'],$this->panelInit->language['usernameAlreadyUsed']);
		}
		if(\User::where('email',\Input::get('email'))->where('id','!=',$id)->count() > 0){
			return $this->panelInit->apiOutput(false,$this->panelInit->language['editAccountant'],$this->panelInit->language['emailAlreadyUsed']);
		}
		$User = \User::find($id);
		$User->username = \Input::get('username');
		$User->email = \Input::get('email');
		$User->fullName = \Input::get('fullName');
		if(\Input::get('password') != ""){
			$User->password = \Hash::make(\Input::get('password'));
		}
		if (\Input::hasFile('photo')) {
			$fileInstance = \Input::file('photo');
			$newFileName = "profile_".$User->id.".jpg";
			$file = $fileInstance->move('uploads/profile/',$newFileName);

			$User->photo = "profile_".$User->id.".jpg";
		}
		if(\Input::has('comVia')){
			$User->comVia = json_encode(\Input::get('comVia'));
		}
		$User->save();

		return $this->panelInit->apiOutput(true,$this->panelInit->language['editAccountant'],$this->panelInit->language['accountantUpdated'],$User->toArray());
	}
}
