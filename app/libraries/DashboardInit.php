<?php
class DashboardInit {

	public $panelItems;
	public $settingsArray = array();
	public $language;
	
	public $version = "3.2";
	public $nversion = "320";
	
	public $lowAndVersion = "2.1";
	public $nLowAndVersion = "210";
	
	public $lowiOsVersion = "1.0";
	public $nLowiOsVersion = "100";

	public $teacherClasses = array();
	public $isRTL;
	public $languageUniversal;
	public $selectAcYear;
	public $defTheme;
	public $baseURL;
	public $customPermissionsDecoded;
	public $calendarsLocale = array("ethiopic"=>"am","gregorian"=>"en_US","islamic"=>"en_US","persian"=>"fa");


	public function __construct(){
		$this->panelItems = array(
									"dashboard"=>array("title"=>"dashboard","icon"=>"mdi mdi-gauge","url"=> URL::to('#/'),"permissions"=>array('admin','teacher','student','parent') ),
									"staticContent"=>array("title"=>"staticPages","icon"=>"mdi mdi-book-multiple","activated"=>"staticpagesAct","cusPerm"=>"staticPages","permissions"=>array('admin','teacher','student','parent'),
													"children"=>array(
															"controlStatic"=>array("title"=>"controlPages","url"=>URL::to('#/static'),"permissions"=>array('admin') )
														)
									),

									"messages"=>array("title"=>"Messages","url"=>URL::to('#/messages'),"icon"=>"mdi mdi-message-text-outline","activated"=>"messagesAct","permissions"=>array('admin','teacher','student','parent') ),

									"mailsms"=>array("title"=>"mailsms","url"=>URL::to('#/mailsms'),"icon"=>"mdi mdi-cellphone-iphone","activated"=>"mailSmsAct","cusPerm"=>"mailsms","permissions"=>array('admin','teacher') ),
									"mobNotif"=>array("title"=>"Mobile Notifications","url"=>URL::to('#/mobileNotif'),"icon"=>"mdi mdi-telegram","activated"=>"sendNotifAct","cusPerm"=>"mobileNotif","permissions"=>array('admin') ),

									"calender"=>array("title"=>"Calender","url"=>URL::to('#/calender'),"icon"=>"mdi mdi-calendar-text","activated"=>"calendarAct","permissions"=>array('admin','teacher','student','parent') ),
									"classSchedule"=>array("title"=>"classSch","url"=>URL::to('#/classschedule'),"icon"=>"mdi mdi-timelapse","activated"=>"classSchAct","cusPerm"=>"classSch","permissions"=>array('admin','teacher','student','parent') ),

									"attendance"=>array("title"=>"Attendance","icon"=>"mdi mdi-check-all","activated"=>"attendanceAct","cusPerm"=>"Attendance","url"=>URL::to('#/attendance'),"permissions"=>array('admin','teacher') ),
									"vacation"=>array("title"=>"Vacation","url"=>URL::to('#/vacation'),"icon"=>"mdi mdi-airplane","activated"=>"vacationAct","permissions"=>array('teacher','student') ),
									"myAttendance"=>array("title"=>"Attendance","url"=>URL::to('#/attendanceStats'),"icon"=>"mdi mdi-check","activated"=>"attendanceAct","permissions"=>array('student','parent') ),
									"staffAttendance"=>array("title"=>"staffAttendance","url"=>URL::to('#/staffAttendance'),"icon"=>"mdi mdi-check","activated"=>"staffAttendanceAct","cusPerm"=>"staffAttendance","permissions"=>array('admin') ),

									"hostel"=>array("title"=>"HostelManage","icon"=>"mdi mdi-home-map-marker","activated"=>"hostelAct","cusPerm"=>"HostelManage","permissions"=>array('admin'),
														"children"=>array(
															"controlHostel"=>array("title"=>"Hostel","url"=>URL::to('#/hostel'),"permissions"=>array('admin') ),
															"hostelCat"=>array("title"=>"HostelCat","url"=>URL::to('#/hostelCat'),"permissions"=>array('admin') ),
														)
									),

									"library"=>array("title"=>"Library","url"=>URL::to('#/library'),"icon"=>"mdi mdi-library","activated"=>"bookslibraryAct","cusPerm"=>"Library","permissions"=>array('admin','teacher','student','parent') ),
									"media"=>array("title"=>"mediaCenter","url"=>URL::to('#/media'),"icon"=>"mdi mdi-folder-multiple-image","activated"=>"mediaAct","cusPerm"=>"mediaCenter","permissions"=>array('admin','teacher','student','parent') ),

									"teachers"=>array("title"=>"teachers","url"=>URL::to('#/teachers'),"icon"=>"mdi mdi-briefcase","cusPerm"=>"teachers","permissions"=>array('admin') ),
									"students"=>array("title"=>"students","url"=>URL::to('#/students'),"icon"=>"mdi mdi-account-multiple-outline","cusPerm"=>"students","permissions"=>array('admin','teacher','parent') ),
									"parents"=>array("title"=>"parents","url"=>URL::to('#/parents'),"icon"=>"mdi mdi-account-multiple","cusPerm"=>"parents","permissions"=>array('admin') ),


									"materials"=>array("title"=>"studyMaterial","url"=>URL::to('#/materials'),"icon"=>"mdi mdi-cloud-download","activated"=>"materialsAct","cusPerm"=>"studyMaterial","permissions"=>array('admin','teacher','student') ),
									"assignments"=>array("title"=>"Assignments","url"=>URL::to('#/assignments'),"icon"=>"mdi mdi-file-pdf","activated"=>"assignmentsAct","cusPerm"=>"Assignments","permissions"=>array('admin','teacher','student','parent') ),
									"onlineexams"=>array("title"=>"onlineExams","url"=>URL::to('#/onlineExams'),"icon"=>"mdi mdi-account-network","activated"=>"onlineexamsAct","cusPerm"=>"onlineExams","permissions"=>array('admin','teacher','student') ),

									"newsboard"=>array("title"=>"newsboard","url"=>URL::to('#/newsboard'),"icon"=>"mdi mdi-bullhorn","activated"=>"newsboardAct","cusPerm"=>"newsboard","permissions"=>array('admin','teacher','student','parent') ),
									"events"=>array("title"=>"events","url"=>URL::to('#/events'),"icon"=>"mdi mdi-calendar-clock","activated"=>"eventsAct","cusPerm"=>"events","permissions"=>array('admin','teacher','student','parent') ),

									"accounting"=>array("title"=>"accounting","icon"=>"mdi mdi-currency-usd","activated"=>"paymentsAct","cusPerm"=>"accounting","permissions"=>array('admin','student','parent','account'),
														"children"=>array(
															"controlFeeGroups"=>array("title"=>"FeeGroups","url"=>URL::to('#/feeGroup'),"activated"=>"paymentsAct","permissions"=>array('admin','account') ),
															"controlFeeTypes"=>array("title"=>"FeeTypes","url"=>URL::to('#/feeType'),"activated"=>"paymentsAct","permissions"=>array('admin','account') ),
															"controlFeeAllocation"=>array("title"=>"FeeAllocation","url"=>URL::to('#/feeAllocation'),"activated"=>"paymentsAct","permissions"=>array('admin','account') ),
															"Invoices"=>array("title"=>"Invoices","url"=>URL::to('#/invoices'),"activated"=>"paymentsAct","permissions"=>array('admin','student','parent','account') ),
															"DueInvoices"=>array("title"=>"dueInvoices","url"=>URL::to('#/invoices/due'),"activated"=>"paymentsAct","permissions"=>array('admin','student','parent','account') ),
														)
									),

									"expensesList"=>array("title"=>"Expenses","icon"=>"mdi mdi-currency-usd","activated"=>"paymentsAct","cusPerm"=>"accounting","permissions"=>array('admin','account'),
														"children"=>array(
															"expenses"=>array("title"=>"Expenses","url"=>URL::to('#/expenses'),"permissions"=>array('admin','account') ),
															"expensesCat"=>array("title"=>"Expenses Categories","url"=>URL::to('#/expensesCat'),"permissions"=>array('admin','account') ),
														)
									),


									"transportations"=>array("title"=>"Transportation","url"=>URL::to('#/transports'),"icon"=>"mdi mdi-bus","activated"=>"transportAct","cusPerm"=>"Transportation","permissions"=>array('admin','teacher','student','parent') ),

									"classes"=>array("title"=>"classes","icon"=>"mdi mdi-sitemap","cusPerm"=>"classes","permissions"=>array('admin'),
														"children"=>array(
															"classes"=>array("title"=>"classes","url"=>URL::to('#/classes'),"permissions"=>array('admin') ),
															"sections"=>array("title"=>"sections","url"=>URL::to('#/sections'),"permissions"=>array('admin') ),
														)
									),

									"subjects"=>array("title"=>"Subjects","url"=>URL::to('#/subjects'),"icon"=>"mdi mdi-book-open-page-variant","cusPerm"=>"Subjects","permissions"=>array('admin') ),
									"reports"=>array("title"=>"Reports","url"=>URL::to('#/reports'),"icon"=>"mdi mdi-chart-areaspline","activated"=>"reportsAct","cusPerm"=>"Reports","permissions"=>array('admin','account') ),

									"adminTasks"=>array("title"=>"adminTasks","icon"=>"mdi mdi-settings","permissions"=>array('admin'),
																				"children"=>array(
																						"academicyear"=>array("title"=>"academicyears","url"=>URL::to('#/academicYear'),"cusPerm"=>"academicyears","permissions"=>array('admin') ),
																						"siteSettings" => array("title"=>"generalSettings","url"=>URL::to('#/settings'),"cusPerm"=>"generalSettings","permissions"=>array('admin') ),
																						"languages" => array("title"=>"Languages","url"=>URL::to('#/languages'),"cusPerm"=>"Languages","permissions"=>array('admin') ),
																						"admins"=>array("title"=>"Administrators","url"=>URL::to('#/admins'),"cusPerm"=>"Administrators","permissions"=>array('admin') ),
																						"terms"=>array("title"=>"schoolTerms","url"=>URL::to('#/terms'),"cusPerm"=>"generalSettings","permissions"=>array('admin') ),
																					)
																				)
					);


		global $settingsLC;
		$check = \Schema::hasTable('settings');
		if(!$check){
			$this->redirect('install');
		}
		$settings = settings::get();
		$this->settingsArray = call_user_func($settingsLC[0], $settings,$this->version);

		if($this->settingsArray['thisVersion'] != $this->version){
			$this->redirect('upgrade');
		}

		$staticPages = static_pages::where('pageActive','1')->get();
		foreach ($staticPages as $pages) {
			$this->panelItems['staticContent']['children'][md5(uniqid())] = array("title"=>$pages->pageTitle,"url"=>URL::to('#/static')."/".$pages->id,"icon"=>"fa fa-file-text","permissions"=>array('admin','teacher','student','parent') );
		}

		if($this->settingsArray['allowTeachersMailSMS'] == "none" AND !Auth::guest() AND \Auth::user()->role == "teacher"){
			unset($this->panelItems['mailsms']);
		}

		$this->authUser = $this->getAuthUser();

		//Languages
		$defLang = $defLang_ = $this->settingsArray['languageDef'];
		if(isset($this->settingsArray['languageAllow']) AND $this->settingsArray['languageAllow'] == "1" AND isset($this->authUser->defLang) AND $this->authUser->defLang != 0){
			$defLang = $this->authUser->defLang;
		}

		//Theme
		$this->defTheme = $this->settingsArray['layoutColor'];
		if(isset($this->settingsArray['layoutColorUserChange']) AND $this->settingsArray['layoutColorUserChange'] == "1" AND isset($this->authUser->defTheme) AND $this->authUser->defTheme != ""){
			$this->defTheme = $this->authUser->defTheme;
		}

		$language = languages::whereIn('id',array($defLang,1))->get();
		if(count($language) == 0){
			$language = languages::whereIn('id',array($defLang_,1))->get();
		}

		foreach ($language as $value) {
			if($value->id == 1){
				$this->language = json_decode($value->languagePhrases,true);
				$this->languageUniversal = "en";
			}else{
				$this->languageUniversal = $value->languageUniversal;
				$this->isRTL = $value->isRTL;
				$phrases = json_decode($value->languagePhrases,true);
				while (list($key, $value) = each($phrases)) {
					$this->language[$key] = $value;
				}
			}
		}

		$this->weekdays = array("ethiopic"=>array(1=>'እሑድ',2=>'ሰኞ',3=>'ማክሰኞ',4=>'ረቡዕ',5=>'ሓሙስ',6=>'ዓርብ',7=>'ቅዳሜ'),
								"gregorian"=>array(1=>$this->language['Sunday'],2=>$this->language['Monday'],3=>$this->language['Tuesday'],4=>$this->language['Wednesday'],5=>$this->language['Thurusday'],6=>$this->language['Friday'],7=>$this->language['Saturday']),
								"islamic"=>array(1=>'Yawm as-sabt',2=>'Yawm al-ahad',3=>'Yawm al-ithnayn',4=>"Yawm ath-thulaathaa'",5=>"Yawm al-arbi'aa'",6=>"Yawm al-khamīs",7=>"Yawm al-jum'a"),
								"persian"=>array(1=>'Shambe',2=>'Yekshambe',3=>'Doshambe',4=>'Seshambe',5=>'Chæharshambe',6=>'Panjshambe',7=>"Jom'e"),
							);

		//Selected academicYear
		if (Session::has('selectAcYear')){
			$this->selectAcYear = Session::get('selectAcYear');
		}else{
			$currentAcademicYear = academic_year::where('isDefault','1')->first();
			$this->selectAcYear = $currentAcademicYear->id;
			Session::put('selectAcYear', $this->selectAcYear);
		}

		//Process Scheduled Payments
		$this->collectFees();
		$this->dueInvoicesNotif();

		$this->baseURL = URL::to('/');
		if (strpos($this->baseURL, 'index.php') == false) {
			$this->baseURL .= "/";
		}

	}

	public function collectFees(){
		$feeTypeList = array();
		$updateAllocationArray = array();
		$updateGroupArray = array();

		$fee_allocation = \fee_allocation::where('feeTypeNextTS','<',time())->where('feeTypeNextTS','!=',0);
		if( $fee_allocation->count() > 0 ){
			$fee_allocation = $fee_allocation->get()->toArray();

			while (list(, $value) = each($fee_allocation)) {
				$updateAllocationArray[$value['id']] = array();

				if(!isset($feeTypeList[$value['feeType']])){
					$feeType = \fee_type::leftJoin('fee_group','fee_group.id','=','fee_type.feeGroup')->where('fee_type.id',$value['feeType']);
					if($feeType->count() > 0){
						$feeTypeList[$value['feeType']] = $feeType->select('fee_type.id','fee_type.feeTitle','fee_type.feeCode','fee_type.feeDescription','fee_type.feeGroup','fee_type.feeAmount','fee_type.feeSchDetails','fee_group.invoice_prefix as invoice_prefix','fee_group.invoice_count as invoice_count')->first()->toArray();
						$feeTypeList[$value['feeType']]['feeSchDetails'] = json_decode($feeTypeList[$value['feeType']]['feeSchDetails'],true);

						$updateGroupArray[$feeTypeList[$value['feeType']]['id']] = array();
						$updateGroupArray[$feeTypeList[$value['feeType']]['id']]['group'] = $feeTypeList[$value['feeType']]['feeGroup'];
						$updateGroupArray[$feeTypeList[$value['feeType']]['id']]['count'] = $feeTypeList[$value['feeType']]['invoice_count'];
					}
				}

				if( !isset(	$feeTypeList[$value['feeType']] ) ){
					$updateAllocationArray[$value['id']]['feeTypeNextTS'] = 0;
				}else{
					$paymentUsers = $this->getPaymentUsers($value['feeFor'],$value['feeForInfo']);

					$paymentDate = time();
					$compareTimes = array();

					reset($feeTypeList[$value['feeType']]['feeSchDetails']);
					while (list($key_, $value_) = each($feeTypeList[$value['feeType']]['feeSchDetails'])) {

						if($value_['date'] >= time()){
							$compareTimes[] = $value_['date'];
						}

						if($value['feeTypeNextTS'] == $value_['date']){
							$paymentDate = $value_['date'];
							$dueDate = $value_['due'];
						}
					}

					if(count($compareTimes) > 0){
						$updateAllocationArray[$value['id']]['feeTypeNextTS'] = min($compareTimes);
					}else{
							$updateAllocationArray[$value['id']]['feeTypeNextTS'] = 0;
					}

					$paymentRows = array();
					$paymentRows[] = array("title"=>$feeTypeList[$value['feeType']]['feeTitle'],"amount"=>$feeTypeList[$value['feeType']]['feeAmount']);

					while (list(, $value_) = each($paymentUsers)) {

						$updateGroupArray[$value['feeType']]['count'] ++ ;

						$payments = new \payments();
						$payments->paymentTitle = $feeTypeList[$value['feeType']]['invoice_prefix'].$updateGroupArray[$value['feeType']]['count'];
						$payments->paymentDescription = $feeTypeList[$value['feeType']]['feeTitle'];
						$payments->paymentStudent = $value_['id'];
						$payments->paymentRows = json_encode($paymentRows);
						$payments->paymentAmount = $feeTypeList[$value['feeType']]['feeAmount'];
						$payments->paymentStatus = "0";
						$payments->paymentDate = $paymentDate;
						$payments->dueDate = $dueDate;
						$payments->paymentUniqid = uniqid();
						$payments->save();

					}

				}
			}

		}

		if(count($updateAllocationArray) > 0){
			while (list($key, $value) = each($updateAllocationArray)) {
				\fee_allocation::where('id',$key)->update($value);
			}
		}

		if(count($updateGroupArray) > 0){
			while (list($key, $value) = each($updateGroupArray)) {
				\fee_group::where('id',$value['group'])->update( array( 'invoice_count' => $value['count']) );
			}
		}

	}

	public function dueInvoicesNotif(){
		$dueInvoices = \payments::where('dueDate','<', time() )->where('dueNotified','0')->where('paymentStatus','!=','1');
		if($dueInvoices->count() > 0){

			if($this->settingsArray['dueInvoicesNotif'] == "mail" || $this->settingsArray['dueInvoicesNotif'] == "mailsms"){
				$mail = true;
			}
			if($this->settingsArray['dueInvoicesNotif'] == "sms" || $this->settingsArray['dueInvoicesNotif'] == "mailsms"){
				$sms = true;
			}

			if($this->settingsArray['dueInvoicesNotifTo'] == "student" || $this->settingsArray['dueInvoicesNotifTo'] == "both"){
				$students = true;
			}
			if($this->settingsArray['dueInvoicesNotifTo'] == "parent" || $this->settingsArray['dueInvoicesNotifTo'] == "both"){
				$parents = true;
			}

			if(isset($mail) || isset($sms)){
				$mailsms_template = \mailsms_templates::where('templateTitle','Due Invoice')->first()->toArray();
			}

			$usersIds = array();
			$usersIdsFlat = array();
			$studentsArray = array();
			$parentsArray = array();
			$updateInvoices = array();

			//Get Due Invoices
			$dueInvoices = $dueInvoices->limit(5)->get()->toArray();
			while (list(, $value) = each($dueInvoices)) {
				$usersIds[ $value['id'] ] = $value['paymentStudent'];
				$usersIdsFlat[] = $value['paymentStudent'];
				$updateInvoices[] = $value['id'];
			}

			//Get users information
			$usersList = \User::whereIn('id',$usersIdsFlat);

			if(isset($parents)){
				$usersList = $usersList->orWhere(function ($query) use ($usersIdsFlat) {
							while (list(, $value) = each($usersIdsFlat)) {
								$query = $query->orWhere('parentOf', 'like', '%"'.$value.'"%');
							}
						});
			}

			$usersList = $usersList->select('id','role','fullName','email','mobileNo','comVia','parentOf')->get()->toArray();
			while (list(, $value) = each($usersList)) {

				if($value['role'] == "parent"){
					
					$value['parentOf'] = json_decode($value['parentOf'],true);
					if(is_array($value['parentOf'])){

						while (list(, $value2) = each($value['parentOf'])) {
							if(!isset($parentsArray[ $value2['id'] ])){
								$parentsArray[ $value2['id'] ] = array();
							}
							$parentsArray[ $value2['id'] ][] = array("id"=>$value['id'],"role"=>$value['role'],"email"=>$value['email'],"mobileNo"=>$value['mobileNo'],"comVia"=>$value['comVia'],"fullName"=>$value['fullName']);
						}

					}
						
				}else{
					$studentsArray[ $value['id'] ] = $value;
				}

			}

			//Start the sending operation
			reset($dueInvoices);
			$MailSmsHandler = new \MailSmsHandler();

			while (list(, $value) = each($dueInvoices)) {

				if(!isset($studentsArray[$value['paymentStudent']])){
					continue;
				}

				if(isset($students)){

					if(isset($mail) AND strpos($studentsArray[$value['paymentStudent']]['comVia'], 'mail') !== false){
						$temp_mailsms_template = $mailsms_template;
						$searchArray = array("{name}","{invoice_id}","{invoice_details}","{invoice_amount}","{invoice_date}");
						$replaceArray = array($studentsArray[$value['paymentStudent']]['fullName'],$value['paymentTitle'],$value['paymentDescription'],$this->settingsArray['currency_symbol'].$value['paymentAmount'], $this->unix_to_date($value['paymentDate']) );
						$sendTemplate = str_replace($searchArray, $replaceArray, $temp_mailsms_template['templateMail']);
						$MailSmsHandler->mail($studentsArray[$value['paymentStudent']]['email'],$this->language['Invoices'],$sendTemplate,"",$this->settingsArray);
					}

					if(isset($sms) AND strpos($studentsArray[$value['paymentStudent']]['comVia'], 'sms') !== false AND strlen($studentsArray[$value['paymentStudent']]['mobileNo']) > 5){
						$temp_mailsms_template = $mailsms_template;
						$searchArray = array("{name}","{invoice_id}","{invoice_details}","{invoice_amount}","{invoice_date}");
						$replaceArray = array($studentsArray[$value['paymentStudent']]['fullName'],$value['paymentTitle'],$value['paymentDescription'],$this->settingsArray['currency_symbol'].$value['paymentAmount'], $this->unix_to_date($value['paymentDate']) );
						$sendTemplate = str_replace($searchArray, $replaceArray, $temp_mailsms_template['templateSMS']);
						$MailSmsHandler->sms($studentsArray[$value['paymentStudent']]['mobileNo'],$sendTemplate,$this->settingsArray);
					}

				}
				if(isset($parents) AND isset($parentsArray[$value['paymentStudent']]) ){
					
					while (list(, $parent) = each($parentsArray[$value['paymentStudent']])) {
						if(isset($mail) AND strpos($parent['comVia'], 'mail') !== false){
							$temp_mailsms_template = $mailsms_template;
							$searchArray = array("{name}","{invoice_id}","{invoice_details}","{invoice_amount}","{invoice_date}");
							$replaceArray = array($parent['fullName'],$value['paymentTitle'],$value['paymentDescription'],$this->settingsArray['currency_symbol'].$value['paymentAmount'], $this->unix_to_date($value['paymentDate']) );
							$sendTemplate = str_replace($searchArray, $replaceArray, $temp_mailsms_template['templateMail']);
							$MailSmsHandler->mail($parent['email'],$this->language['Invoices'],$sendTemplate,"",$this->settingsArray);
						}

						if(isset($sms) AND strpos($parent['comVia'], 'sms') !== false AND strlen($parent['mobileNo']) > 5){
							$temp_mailsms_template = $mailsms_template;
							$searchArray = array("{name}","{invoice_id}","{invoice_details}","{invoice_amount}","{invoice_date}");
							$replaceArray = array($parent['fullName'],$value['paymentTitle'],$value['paymentDescription'],$this->settingsArray['currency_symbol'].$value['paymentAmount'], $this->unix_to_date($value['paymentDate']) );
							$sendTemplate = str_replace($searchArray, $replaceArray, $temp_mailsms_template['templateSMS']);
							$MailSmsHandler->sms($parent['mobileNo'],$sendTemplate,$this->settingsArray);
						}
					}

				}
			}
			if(count($updateInvoices) > 0){
				\payments::whereIn('id', $updateInvoices )->update( array('dueNotified'=>'1') );
			}
		}
		
	}

	public function real_notifications($data){

		//Send to firebase
		$Firebase = new \Firebase();

		if(isset($this->settingsArray['firebase_apikey']) AND $this->settingsArray['firebase_apikey'] != ""){
			$Firebase->api_key($this->settingsArray['firebase_apikey']);
		}else{
			return;
		}

		$Firebase->title = $data['data_title'] ;
		$Firebase->body = strip_tags($data['data_message']);

		$addData = array();
		if(isset($data['notifUrl'])){
			$addData['url'] = $data['data_url'];
		}
		if(isset($data['payload_where'])){
			$addData['where'] = $data['payload_where'];
		}
		if(isset($data['payload_id'])){
			$addData['id'] = $data['payload_id'];
		}
		$addData['sound'] = 'default';
		if(count($addData) > 0){
			$Firebase->data = $addData;
		}
		
		while (list($key, $token) = each($data['firebase_token'])) {
			$info = $Firebase->send($token);
			\Log::info($info);
		}
	}

	public function redirect($to){
		if($to == "install"){
			$toTitle = "Installation";
		}
		if($to == "upgrade"){
			$toTitle = "Upgrade";
		}
		echo "<html><head>
			<title>$toTitle</title>
			<meta http-equiv='refresh' content='2; URL=".\URL::to('/'.$to)."'>
			<meta name='keywords' content='automatic redirection'>
		</head>
		<body> If your browser doesn't automatically go to the $toTitle within a few seconds,
		you may want to go to <a href='".\URL::to('/'.$to)."'>the destination</a> manually.
		</body></html>";
		die();
	}

	public function getPaymentUsers($feeFor,$feeForInfo){
		$students = array();

		if($feeFor == "all" AND isset($this->selectAcYear)){
			$classesList = array();
			$classes = classes::where('classAcademicYear',$this->selectAcYear)->get()->toArray();
			while (list(, $value) = each($classes)) {
				$classesList[] = $value['id'];
			}

			$students = array();
			if(count($classesList) > 0){
				$students = User::where('role','student')->whereIn('studentClass',$classesList)->where('activated','1')->select('id')->get()->toArray();
			}
		}

		if($feeFor == "class"){
			$feeForInfo = json_decode($feeForInfo,true);

			if(is_array($feeForInfo) AND isset($feeForInfo['class'])){
				$students = User::where('role','student')->where('activated','1')->where('studentClass',$feeForInfo['class']);
				if( isset($feeForInfo['section']) AND is_array($feeForInfo['section']) ){
					$students = $students->whereIn('studentSection',$feeForInfo['section']);
				}
				$students = $students->select('id')->get()->toArray();

			}
		}

		if($feeFor == "student"){
			$feeForInfo = json_decode($feeForInfo,true);

			if(is_array($feeForInfo)){
				$ids = array();
				while (list($key, $value) = each($feeForInfo)) {
					$ids[] = $value['id'];
				}

				$students = User::where('role','student')->where('activated','1')->whereIn('id',$ids)->select('id')->get()->toArray();

			}
		}

		return $students;
	}

	public function hasThePerm($perm){
		if(\Auth::user() AND \Auth::user()->role == "admin" AND \Auth::user()->customPermissionsType == "custom" AND is_array(\Auth::user()->customPermissionsAsJson()) AND !in_array($perm,\Auth::user()->customPermissionsAsJson())){
			return false;
		}else{
			return true;
		}
	}

	public function getAuthUser(){
		if(app('request')->header('Authorization') != ""){
			return \JWTAuth::parseToken()->authenticate();
		}else{
			return \Auth::guard('web')->user();
		}
	}

	public function isLoggedInUser(){

	}

	public function customPermissionsType(){
		if($this->customPermissionsDecoded == ""){
			$this->customPermissionsDecoded = json_decode($this->customPermissions);
		}
		return $this->customPermissionsDecoded;
	}

	public function mobNotifyUser($userType,$userIds,$notifData){
		$mobNotifications = new \mob_notifications();

		if($userType == "users"){
			$mobNotifications->notifTo = "users";
			if(!is_array($userIds)){
				$userIds = array($userIds);
			}
			$userIdsList = array();
			while (list(, $value) = each($userIds)) {
				$userIdsList[] = array('id'=>$value);
			}
			$mobNotifications->notifToIds = json_encode($userIdsList);
		}elseif($userType == "class"){
			$mobNotifications->notifTo = "students";
			$mobNotifications->notifToIds = $userIds;
		}elseif($userType == "role"){
			$mobNotifications->notifTo = $userIds;
			$mobNotifications->notifToIds = "";
		}

		$mobNotifications->notifData = htmlspecialchars($notifData,ENT_QUOTES);
		$mobNotifications->notifDate = time();
		$mobNotifications->notifSender = "Automated";
		$mobNotifications->save();
	}

	public static function globalXssClean()
	{
	  $sanitized = static::arrayStripTags(Input::get());
	  Input::merge($sanitized);
	}

	public static function arrayStripTags($array)
	{
	    $result = array();

	    foreach ($array as $key => $value) {
	        // Don't allow tags on key either, maybe useful for dynamic forms.
	        $key = strip_tags($key);

	        // If the value is an array, we will just recurse back into the
	        // function to keep stripping the tags out of the array,
	        // otherwise we will set the stripped value.
	        if (is_array($value)) {
	            $result[$key] = static::arrayStripTags($value);
	        } else {
	            // I am using strip_tags(), you may use htmlentities(),
	            // also I am doing trim() here, you may remove it, if you wish.
	            $result[$key] = trim(strip_tags($value));
	        }
	    }

	    return $result;
	}

	public function viewop($layout,$view,&$data,$div=""){
		$data['content'] = View::make($view, $data);
		return view($layout, $data);
	}

	function sanitize_output($buffer) {
		$search = array('/\>[^\S ]+/s','/[^\S ]+\</s','/(\s)+/s','/\s\s+/');
		$replace = array('>','<',' ',' ');
		$buffer = preg_replace($search, $replace, $buffer);

		return $buffer;
	}

	public static function breadcrumb($breadcrumb){
		echo "<ol class='breadcrumb'>
					<li><a class='aj' href='".URL::to('/dashboard')."'><i class='fa fa-dashboard'></i> Home</a></li>";
		$i = 0;
		while (list($key, $value) = each($breadcrumb)) {
			$i++;
			if($i == count($breadcrumb)){
				echo "<li class='active'>".$key."</li>";
			}else{
				echo "<li class='bcItem'><a class='aj' href='$value' title='$key'>$key</a></li>";
			}
		}
		echo "</ol>";
	}

	public function truncate($text, $length = 100, $ending = '...', $exact = false, $considerHtml = false) {
		if ($considerHtml) {
			// if the plain text is shorter than the maximum length, return the whole text
			if (strlen ( preg_replace ( '/<.*?>/', '', $text ) ) <= $length) {
				return $text;
			}
			// splits all html-tags to scanable lines
			preg_match_all ( '/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER );
			$total_length = strlen ( $ending );
			$open_tags = array ( );
			$truncate = '';
			foreach ( $lines as $line_matchings ) {
				// if there is any html-tag in this line, handle it and add it (uncounted) to the output
				if (! empty ( $line_matchings [1] )) {
					// if it's an "empty element" with or without xhtml-conform closing slash (f.e. <br/>)
					if (preg_match ( '/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $line_matchings [1] )) {
						// do nothing
					// if tag is a closing tag (f.e. </b>)
					} else if (preg_match ( '/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings [1], $tag_matchings )) {
						// delete tag from $open_tags list
						$pos = array_search ( $tag_matchings [1], $open_tags );
						if ($pos !== false) {
							unset ( $open_tags [$pos] );
						}
						// if tag is an opening tag (f.e. <b>)
					} else if (preg_match ( '/^<\s*([^\s>!]+).*?>$/s', $line_matchings [1], $tag_matchings )) {
						// add tag to the beginning of $open_tags list
						array_unshift ( $open_tags, strtolower ( $tag_matchings [1] ) );
					}
					// add html-tag to $truncate'd text
					$truncate .= $line_matchings [1];
				}
				// calculate the length of the plain text part of the line; handle entities as one character
				$content_length = strlen ( preg_replace ( '/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', ' ', $line_matchings [2] ) );
				if ($total_length + $content_length > $length) {
					// the number of characters which are left
					$left = $length - $total_length;
					$entities_length = 0;
					// search for html entities
					if (preg_match_all ( '/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', $line_matchings [2], $entities, PREG_OFFSET_CAPTURE )) {
						// calculate the real length of all entities in the legal range
						foreach ( $entities [0] as $entity ) {
							if ($entity [1] + 1 - $entities_length <= $left) {
								$left --;
								$entities_length += strlen ( $entity [0] );
							} else {
								// no more characters left
								break;
							}
						}
					}
					$truncate .= substr ( $line_matchings [2], 0, $left + $entities_length );
					// maximum lenght is reached, so get off the loop
					break;
				} else {
					$truncate .= $line_matchings [2];
					$total_length += $content_length;
				}
				// if the maximum length is reached, get off the loop
				if ($total_length >= $length) {
					break;
				}
			}
		} else {
			if (strlen ( $text ) <= $length) {
				return $text;
			} else {
				$truncate = substr ( $text, 0, $length - strlen ( $ending ) );
			}
		}
		// if the words shouldn't be cut in the middle...
		if (! $exact) {
			// ...search the last occurance of a space...
			$spacepos = strrpos ( $truncate, ' ' );
			if (isset ( $spacepos )) {
				// ...and cut the text in this position
				$truncate = substr ( $truncate, 0, $spacepos );
			}
		}
		// add the defined ending to the text
		$truncate .= $ending;
		if ($considerHtml) {
			// close all unclosed html-tags
			foreach ( $open_tags as $tag ) {
				$truncate .= '</' . $tag . '>';
			}
		}
		return $truncate;
	}

	public function apiOutput($success,$title=null,$messages = null,$data=null){
		$returnArray = array("status"=>"");

		if($title != null){
			$returnArray['title'] = $title;
		}

		if($messages != null){
			$returnArray['message'] = $messages;
		}

		if($data != null){
			$returnArray['data'] = $data;
		}

		if($success){
			$returnArray['status'] = 'success';
			return $returnArray;
		}else{
			$returnArray['status'] = 'failed';
			return $returnArray;
		}
	}

	public function date_to_unix($time,$format=""){
		if(!isset($this->settingsArray['timezone'])){
			$this->settingsArray['timezone'] = "Europe/London";
		}
		if($format == ""){
			$format = $this->settingsArray['dateformat'];
		}
		if(!isset($this->settingsArray['gcalendar']) || ( isset($this->settingsArray['gcalendar']) AND ( $this->settingsArray['gcalendar'] == "gregorian" || $this->settingsArray['gcalendar'] == "" ) ) ){
			//Regular Date manipulation
			$format = str_replace("hr","h",$format);
			$format = str_replace("mn","i",$format);
			return $this->greg_to_unix($time,$format);
		}else{
			//Intl Date manipulation
			$format = str_replace("hr","h",$format);
			$format = str_replace("mn","m",$format);
			return $this->intlToTimestamp($time,$format);
		}
	}

	public function unix_to_date($timestamp,$format=""){
		if($format == ""){
			$format = $this->settingsArray['dateformat'];
		}
		if(!isset($this->settingsArray['timezone'])){
			$this->settingsArray['timezone'] = "Europe/London";
		}

		//Adjust date offset
		if(isset($this->settingsArray['calendarOffset']) AND $this->settingsArray['calendarOffset'] != "" AND $this->settingsArray['calendarOffset'] != "0" ){
			$timestamp += ( intval($this->settingsArray['calendarOffset']) * 86400 );
		}

		if(!isset($this->settingsArray['gcalendar']) || ( isset($this->settingsArray['gcalendar']) AND ( $this->settingsArray['gcalendar'] == "gregorian" || $this->settingsArray['gcalendar'] == "" ) ) ){
			//Regular Date manipulation
			$format = str_replace("hr","h",$format);
			$format = str_replace("mn","i",$format);
			return $this->unix_to_greg($timestamp,$format);
		}else{
			//Intl Date manipulation
			$format = str_replace("hr","h",$format);
			$format = str_replace("mn","m",$format);
			return $this->timestampToIntl($timestamp,$format);
		}
	}

	public function date_ranges($start,$end=""){
		if(!isset($this->settingsArray['timezone'])){
			$this->settingsArray['timezone'] = "Europe/London";
		}

		//Adjust date offset
		if(isset($this->settingsArray['calendarOffset']) AND $this->settingsArray['calendarOffset'] != "" AND $this->settingsArray['calendarOffset'] != "0" ){
			$start += ( intval($this->settingsArray['calendarOffset']) * 86400 );
			$end += ( intval($this->settingsArray['calendarOffset']) * 86400 );
		}

		if(!isset($this->settingsArray['gcalendar']) || ( isset($this->settingsArray['gcalendar']) AND ( $this->settingsArray['gcalendar'] == "gregorian" || $this->settingsArray['gcalendar'] == "" ) ) ){
			return $this->gregTsDow($start,$end);
		}else{
			return $this->intlTsDow($start,$end);
		}
	}

	function todayDow(){
		$time = time();

		//Adjust date offset
		if(isset($this->settingsArray['calendarOffset']) AND $this->settingsArray['calendarOffset'] != "" AND $this->settingsArray['calendarOffset'] != "0" ){
			$time += ( intval($this->settingsArray['calendarOffset']) * 86400 );
		}

		if(!isset($this->settingsArray['gcalendar']) || ( isset($this->settingsArray['gcalendar']) AND ( $this->settingsArray['gcalendar'] == "gregorian" || $this->settingsArray['gcalendar'] == "" ) ) ){
			return $this->unix_to_date($time,'w') + 1;
		}else{
			return $this->unix_to_date($time,'e') + 1 ;
		}
	}

	//Work with Date & Time
	public function greg_to_unix($time,$format) {
		$dd = DateTime::createFromFormat($format, $time, new DateTimeZone($this->settingsArray['timezone']));
		if (strpos($format, 'h:i') == false) {
			$dd->setTime(0,0,0);
		}
		return $dd->getTimestamp();
	}

	public function unix_to_greg($timestamp, $format){
		if($timestamp == ""){
			$timestamp = time();
		}
		$date = new DateTime("@".$timestamp);
		$date->setTimezone(new DateTimeZone($this->settingsArray['timezone']));
		return $date->format($format);
	}

	//Work with Date & Time
	public function intlToTimestamp($date,$format=""){
		if($format == ""){
			$format = $this->settingsArray['dateformat'];
		}

		$format = str_replace('m','MM',$format);
		$format = str_replace('d','dd',$format);
		$format = str_replace('Y','yyyy',$format);

		if($this->settingsArray['gcalendar'] == "gregorian"){
			$intl_locale = 'en_Us';
			$intl_calendar = \IntlDateFormatter::GREGORIAN;
		}else{
			$intl_locale = 'en_Us@calendar='.$this->settingsArray['gcalendar'];
			$intl_calendar = \IntlDateFormatter::TRADITIONAL;
		}

		$intlDateFormatter = new \IntlDateFormatter(
			$intl_locale,
			\IntlDateFormatter::FULL,
			\IntlDateFormatter::FULL,
			$this->settingsArray['timezone'],
			$intl_calendar,
			$format
		);
		$intlDateFormatter->setLenient(false);

		return $intlDateFormatter->parse($date);
	}

	public function timestampToIntl($timestamp,$format=""){

		if($format == ""){
			$format = $this->settingsArray['dateformat'];
		}

		$format = str_replace('m','MM',$format);
		$format = str_replace('d','dd',$format);
		$format = str_replace('Y','yyyy',$format);

		if($this->settingsArray['gcalendar'] == "gregorian"){
			$intl_locale = 'en_Us';
			$intl_calendar = \IntlDateFormatter::GREGORIAN;
		}else{
			$intl_locale = 'en_Us@calendar='.$this->settingsArray['gcalendar'];
			$intl_calendar = \IntlDateFormatter::TRADITIONAL;
		}

		$DateTime = new \DateTime("@".$timestamp);
		$IntlDateFormatter = new \IntlDateFormatter(
			$intl_locale,
			\IntlDateFormatter::FULL,
			\IntlDateFormatter::FULL,
			$this->settingsArray['timezone'],
			$intl_calendar,
			$format
		);
		return $IntlDateFormatter->format($timestamp);
	}

	public function gregTsDow($start,$end=""){
		$return = array();

		$format = $this->settingsArray['dateformat'];

		if(!isset($this->settingsArray['timezone'])){
			$this->settingsArray['timezone'] = "Europe/London";
		}

		if($end == ""){
			$dd = DateTime::createFromFormat($format, $start, new DateTimeZone($this->settingsArray['timezone']));
			$return[] = array("dow"=>$dd->format('N'),"date"=>$start,"timestamp"=>$dd->getTimestamp() );
		}else{

			$tmpDate = DateTime::createFromFormat($format, $start, new DateTimeZone($this->settingsArray['timezone']));
			$tmpDate->setTime(0,0,0);

			$tmpEndDate = DateTime::createFromFormat($format, $end, new DateTimeZone($this->settingsArray['timezone']));
			$tmpEndDate->setTime(0,0,0);

			$outArray = array();
			do {
				$return[] = array("dow"=>$tmpDate->format('N'),"date"=>$tmpDate->format($format),"timestamp"=>$tmpDate->getTimestamp() );
			} while ($tmpDate->modify('+1 day') <= $tmpEndDate);

		}

		return $return;
	}

	public function intlTsDow($start,$end=""){
		$return = array();

		$format = $this->settingsArray['dateformat'];

		$format = str_replace('m','MM',$format);
		$format = str_replace('d','dd',$format);
		$format = str_replace('Y','yyyy',$format);

		if(!isset($this->settingsArray['timezone'])){
			$this->settingsArray['timezone'] = "Europe/London";
		}

		$intl_locale = 'en_Us@calendar='.$this->settingsArray['gcalendar'];
		$intl_calendar = \IntlDateFormatter::TRADITIONAL;

		$intlDateFormatter = new \IntlDateFormatter(
						$intl_locale,
						\IntlDateFormatter::FULL,
						\IntlDateFormatter::FULL,
						$this->settingsArray['timezone'],
						$intl_calendar,
						$format
					);
		$intlDateFormatter->setLenient(false);
		$timestamp = $intlDateFormatter->parse($start);
		$firstTime = true;

		if($end == ""){
			$DateTime = new \DateTime("@".$timestamp);
			$IntlDateFormatter = new \IntlDateFormatter(
				$intl_locale,
				\IntlDateFormatter::FULL,
				\IntlDateFormatter::FULL,
				$this->settingsArray['timezone'],
				$intl_calendar,
				"e"
			);
			$return[] = array("dow"=>$IntlDateFormatter->format($DateTime),"date"=>$start,"timestamp"=>$timestamp);
		}else{
			do{
				if(!isset($firstTime)){
					$start = $this->timestampToIntl($timestamp);
				}else{
					$end = $this->intlToTimestamp($end);
				}

				unset($firstTime);
				$DateTime = new \DateTime("@".$timestamp);
				$IntlDateFormatter = new \IntlDateFormatter(
					$intl_locale,
					\IntlDateFormatter::FULL,
					\IntlDateFormatter::FULL,
					$this->settingsArray['timezone'],
					$intl_calendar,
					"e"
				);
				$return[] = array("dow"=>$IntlDateFormatter->format($DateTime),"date"=>$start,"timestamp"=>$timestamp);

				//Next timestamp
				$timestamp = $timestamp + 86400;
			}while($timestamp <= $end);
		}

		return $return;
	}

}
