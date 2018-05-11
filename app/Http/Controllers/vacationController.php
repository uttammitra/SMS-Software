<?php
namespace App\Http\Controllers;

class vacationController extends Controller {

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

	public function getVacation(){
		if($this->data['users']->role == "admin" || $this->data['users']->role == "parent") exit;

        $currentUserVacations = \vacation::where('userid',$this->data['users']->id)->where('acYear',$this->panelInit->selectAcYear)->count();

		if(\Input::has('toDate')){
			$daysList = $this->panelInit->date_ranges(\Input::get('fromDate'),\Input::get('toDate'));
		}else{
			$daysList = $this->panelInit->date_ranges(\Input::get('fromDate'));
		}

		$daysList = $this->remove_off_days($daysList);

		$daysList_ = array();
		while (list($key, $value) = each($daysList)) {
			if(isset($value['status'])){
				continue;
			}
			$daysList_[] = $value;
		}

        if($this->data['users']->role == "teacher" AND (count($daysList_) + $currentUserVacations) > $this->panelInit->settingsArray['teacherVacationDays'] ){
            return $this->panelInit->apiOutput(false,"Request Vacation","You Don't have enough balance for vacation");
        }

        if($this->data['users']->role == "student" AND (count($daysList_) + $currentUserVacations) > $this->panelInit->settingsArray['studentVacationDays'] ){
            return $this->panelInit->apiOutput(false,"Request Vacation","You Don't have enough balance for vacation");
        }

        return $this->panelInit->apiOutput(true,$this->panelInit->language['getVacation'],$this->panelInit->language['confirmVacation'],$daysList);
	}

    public function saveVacation(){
        if($this->data['users']->role == "admin" || $this->data['users']->role == "parent") exit;

        $daysList = \Input::get('days');
        $currentUserVacations = \vacation::where('userid',$this->data['users']->id)->where('acYear',$this->panelInit->selectAcYear)->count();

		$daysList_ = array();
		while (list($key, $value) = each($daysList)) {
			if(isset($value['status'])){
				continue;
			}
			$daysList_[] = $value;
		}

        if($this->data['users']->role == "teacher" AND (count($daysList_) + $currentUserVacations) > $this->panelInit->settingsArray['teacherVacationDays'] ){
            return $this->panelInit->apiOutput(false,"Request Vacation","You Don't have enough balance for vacation");
        }

        if($this->data['users']->role == "student" AND (count($daysList_) + $currentUserVacations) > $this->panelInit->settingsArray['studentVacationDays'] ){
            return $this->panelInit->apiOutput(false,"Request Vacation","You Don't have enough balance for vacation");
        }

        while (list(, $value) = each($daysList_)) {
            $vacation = new \vacation();
            $vacation->userid = $this->data['users']->id;
            $vacation->vacDate = $value['timestamp'];
            $vacation->acYear = $this->panelInit->selectAcYear;
			$vacation->role = $this->data['users']->role;
            $vacation->save();
        }

        return $this->panelInit->apiOutput(true,$this->panelInit->language['getVacation'],$this->panelInit->language['vacSubmitted']);
    }

	public function delete($id){
		if($this->data['users']->role != "admin") exit;
		if ( $postDelete = \vacation::where('id', $id)->first() )
        {
            $postDelete->delete();
            return $this->panelInit->apiOutput(true,$this->panelInit->language['delVacation'],$this->panelInit->language['vacDel']);
        }else{
            return $this->panelInit->apiOutput(false,$this->panelInit->language['delVacation'],$this->panelInit->language['vacNotExist']);
        }
	}

	public function remove_off_days($daysList){
		$weekDaysOff = json_decode($this->panelInit->settingsArray['daysWeekOff']);
		$officialVacation = json_decode($this->panelInit->settingsArray['officialVacationDay']);

		foreach ($daysList as $key => $day) {
			if(in_array($day['dow'], $weekDaysOff)){
				$daysList[$key]['status'] = "dow";
			}
			if(in_array($day['timestamp'], $officialVacation)){
				$daysList[$key]['status'] = "offv";
			}
		}

		return $daysList;
	}

}
