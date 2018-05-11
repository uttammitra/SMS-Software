<?php
namespace App\Http\Controllers;

class mobileNotifController extends Controller {

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
		if($this->data['users']->role != "admin") exit;

		if(!$this->panelInit->hasThePerm('mobileNotif')){
			exit;
		}
	}

	public function listAll($page = 1)
	{
		$return = array();
		$mobNotifications = \mob_notifications::orderBy('id','desc');
		$return['totalItems'] = $mobNotifications->count();
		$return['subject_list'] = \subject::get();

		$mobNotifications = $mobNotifications->take('20')->skip(20* ($page - 1) )->get()->toArray();
		foreach ($mobNotifications as $value) {
			$value['notifData'] = htmlspecialchars_decode($value['notifData'],ENT_QUOTES);
			$value['notifDate'] = $this->panelInit->unix_to_date($value['notifDate']);
			$return['items'][] = $value;
		}
		return $return;
	}

	public function create(){
		$mobNotifications = new \mob_notifications();

		if(\Input::get('userType') == "users"){
			$mobNotifications->notifTo = "users";
			$mobNotifications->notifToIds = json_encode(\Input::get('selectedUsers'));
		}elseif(\Input::get('userType') == "students"){
			$mobNotifications->notifTo = "students";
			$mobNotifications->notifToIds = \Input::get('classId');
		}else{
			$mobNotifications->notifTo = \Input::get('userType');
			$mobNotifications->notifToIds = "";
		}

		$mobNotifications->notifData = htmlspecialchars(\Input::get('notifData'),ENT_QUOTES);

		$mobNotifications->notifDate = time();
		$mobNotifications->notifSender = $this->data['users']->fullName . " [ " . $this->data['users']->id . " ] ";
		$mobNotifications->save();


		if(isset($this->panelInit->settingsArray['firebase_apikey']) AND $this->panelInit->settingsArray['firebase_apikey'] != ""){
			//Send the PUSH Notifs.

			$users_list = \User::select('id','firebase_token');
			if(\Input::get('userType') == "users"){
				$usersList = array();
				$selectedUsers = \Input::get('selectedUsers');
				foreach ($selectedUsers as $user) {
					$usersList[] = $user['id'];
				}

				$users_list = $users_list->whereIn('id',$usersList);
			}elseif(\Input::get('userType') == "teachers"){
				$selectedUsersArray =  array();
				$subject = \subject::whereIn('id',\Input::get('subjectId'))->get()->toArray();
				while (list(, $value) = each($subject)) {
					$value['teacherId'] = json_decode($value['teacherId'],true);
					if(is_array($value['teacherId'])){
						while (list(, $value_) = each($value['teacherId'])) {
							$selectedUsersArray[] = $value_;
						}
					}
				}

				$users_list = $users_list->where('role','teacher')->whereIn('id',$selectedUsersArray);
			}elseif(\Input::get('userType') == "students"){

				$users_list = $users_list->where('role','student')->whereIn('studentClass',\Input::get('classId'));
				if(\Input::has('sectionId')){
					$users_list = $users_list->whereIn('studentSection',\Input::get('sectionId'));
				}

			}elseif(\Input::get('userType') == "parents"){
				$users_list = $users_list->where('role','parent');

				$stdInClassIds = \User::where('role','student')->whereIn('studentClass',\Input::get('classId'))->select('id');
				if($this->panelInit->settingsArray['enableSections'] == true){
					$stdInClassIds = $stdInClassIds->whereIn('studentClass',\Input::get('sectionId'));
				}
				$stdInClassIds = $stdInClassIds->get()->toArray();

				$users_list = $users_list->where('role','parent')->where(function ($query) use ($stdInClassIds) {
										while (list(, $value) = each($stdInClassIds)) {
											$query = $query->orWhere('parentOf', 'like', '%"'.$value['id'].'"%');
										}
									});
			}else{
				$users_list = $users_list;
			}
			$users_list = $users_list->get()->toArray();

			//Start of sending real-notifications
			$data = array();
			$data['data_title'] = 'Notification';
			$data['data_message'] = \Input::get('notifData');
			$data['data_url'] = \URL::to('#/');
			$data['payload_where'] = 'mob_notif';
			$data['payload_id'] = '';

			$data['firebase_token'] = array();

			while (list(, $value) = each($users_list)) {
				if($value['firebase_token'] != ""){
					$value['firebase_token'] = json_decode($value['firebase_token'],true);
					while (list(, $value_) = each($value['firebase_token'])) {
						$data['firebase_token'][] = $value_;
					}

				}
			}

			$this->panelInit->real_notifications( $data );

			//END of sending real-notifications

		}

		return $this->listAll();
	}

	public function delete($id){
		if ( $postDelete = \mob_notifications::where('id', $id)->first() )
		{
			$postDelete->delete();
			return $this->panelInit->apiOutput(true,"Delete Notification","Notification deleted");
		}else{
			return $this->panelInit->apiOutput(false,"Delete Notification","Notification isn't exist");
		}
	}

}
