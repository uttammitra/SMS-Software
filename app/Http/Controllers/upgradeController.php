<?php
namespace App\Http\Controllers;

class upgradeController extends Controller {

	var $data = array();
	var $version = "3.1";
	var $panelInit ;

	public function __construct(){

	}

	public function index($method = "main")
	{
		try{
			if(!\Schema::hasTable('settings')){
				return \Redirect::to('/install');
			}

			if(\Schema::hasTable('settings')){
				$testInstalled = \settings::where('fieldName','thisVersion')->get();
				if($testInstalled->count() > 0){
					$testInstalled = $testInstalled->first();
					if($testInstalled->fieldValue >= 3.2){
						echo "Already upgraded or at higher version";
						exit;
					}
				}
			}
		}catch(Exception $e){  }

		$this->data['currStep'] = "welcome";
		return \View::make('upgrade', $this->data);
	}

	public function proceed()
	{
		if(\Input::get('nextStep') == "1"){
			if (filter_var(\Input::get('email'), FILTER_VALIDATE_EMAIL)) {
				if (!\Auth::attempt(array('email' => \Input::get('email'), 'password' => \Input::get('password'),'activated'=>1,'role'=>'admin')))
				{
					$loginError = false;
					$this->data['loginError'] = "loginError";
				}
			}else{
				if (!\Auth::attempt(array('username' => \Input::get('email'), 'password' => \Input::get('password'),'activated'=>1,'role'=>'admin')))
				{
					$loginError = false;
					$this->data['loginError'] = "loginError";
				}
			}
			if(!isset($loginError)) {
				file_put_contents('storage/app/lc',\Input::get('cpc'));
				if($this->sbApi() == "err"){
					@unlink('storage/app/lc');
					$this->data['installErrors'][] = "Purchase code is missing";
					$loginError = false;
					$this->data['loginError'] = "loginError";
				}
			}
			$this->data['currStep'] = "welcome";
			if(!isset($loginError)) {
				$this->data['currStep'] = "1";
				$this->data['nextStep'] = "2";

				$testData = uniqid();

				@file_put_contents("uploads/assignments/test", $testData);
				@file_put_contents("uploads/books/test", $testData);
				@file_put_contents("uploads/cache/test", $testData);
				@file_put_contents("uploads/media/test", $testData);
				@file_put_contents("uploads/profile/test", $testData);
				@file_put_contents("uploads/studyMaterial/test", $testData);
				@file_put_contents("uploads/assignmentsAnswers/test", $testData);
				@file_put_contents("uploads/onlineExams/test", $testData);
				@file_put_contents("uploads/expenses/test", $testData);

				@file_put_contents("storage/app/test", $testData);
				@file_put_contents("storage/framework/test", $testData);
				@file_put_contents("storage/logs/test", $testData);

				if(@file_get_contents("uploads/assignments/test") != $testData){
					$this->data['perrors'][] = "uploads/assignments";
					$this->data['nextStep'] = "1";
				}else{
					$this->data['success'][] = "uploads/assignments";
				}

				if(@file_get_contents("uploads/books/test") != $testData){
					$this->data['perrors'][] = "uploads/books";
					$this->data['nextStep'] = "1";
				}else{
					$this->data['success'][] = "uploads/books";
				}

				if(@file_get_contents("uploads/cache/test") != $testData){
					$this->data['perrors'][] = "uploads/cache";
					$this->data['nextStep'] = "1";
				}else{
					$this->data['success'][] = "uploads/cache";
				}

				if(@file_get_contents("uploads/media/test") != $testData){
					$this->data['perrors'][] = "uploads/media";
					$this->data['nextStep'] = "1";
				}else{
					$this->data['success'][] = "uploads/media";
				}

				if(@file_get_contents("uploads/profile/test") != $testData){
					$this->data['perrors'][] = "uploads/profile";
					$this->data['nextStep'] = "1";
				}else{
					$this->data['success'][] = "uploads/profile";
				}

				if(@file_get_contents("uploads/studyMaterial/test") != $testData){
					$this->data['perrors'][] = "uploads/studyMaterial";
					$this->data['nextStep'] = "1";
				}else{
					$this->data['success'][] = "uploads/studyMaterial";
				}

				if(@file_get_contents("uploads/assignmentsAnswers/test") != $testData){
					$this->data['perrors'][] = "uploads/assignmentsAnswers";
					$this->data['nextStep'] = "1";
				}else{
					$this->data['success'][] = "uploads/assignmentsAnswers";
				}

				if(@file_get_contents("uploads/onlineExams/test") != $testData){
					$this->data['perrors'][] = "uploads/onlineExams";
					$this->data['nextStep'] = "1";
				}else{
					$this->data['success'][] = "uploads/onlineExams";
				}

				if(@file_get_contents("uploads/expenses/test") != $testData){
					$this->data['perrors'][] = "uploads/expenses";
					$this->data['nextStep'] = "1";
				}else{
					$this->data['success'][] = "uploads/expenses";
				}


				if(@file_get_contents("storage/app/test") != $testData){
					$this->data['perrors'][] = "storage/app";
					$this->data['nextStep'] = "1";
				}else{
					$this->data['success'][] = "storage/app";
				}

				if(@file_get_contents("storage/framework/test") != $testData){
					$this->data['perrors'][] = "storage/framework";
					$this->data['nextStep'] = "1";
				}else{
					$this->data['success'][] = "storage/framework";
				}

				if(@file_get_contents("storage/logs/test") != $testData){
					$this->data['perrors'][] = "storage/logs";
					$this->data['nextStep'] = "1";
				}else{
					$this->data['success'][] = "storage/logs";
				}
			}
		}

		if(\Input::get('nextStep') == "2"){
			$this->data['currStep'] = "2";
			$this->data['nextStep'] = "3";

			$testInstalled = \settings::where('fieldName','thisVersion')->first();

			if($testInstalled->fieldValue == "2.5"){
				//Upgrade from first version to 2.4
				\DB::unprepared(file_get_contents('storage/app/dbsqlUp26'));

				$settings = \settings::where('fieldName','thisVersion')->first();
				$settings->fieldValue = '2.6';
				$settings->save();

				//Change the exam list structure
				$classesIds = array();
				$examClasses = \classes::select('id')->get();
				foreach ($examClasses as $value) {
					$classesIds[] = ''.$value->id.'';
				}
				$examMarksheetColumns = array(array("id"=>1,"title"=>"Attendance Marks"));
				\DB::table('exams_list')->update( array('examClasses'=> json_encode($classesIds) ,'examMarksheetColumns'=> json_encode($examMarksheetColumns) ) );

				//Change the exam marks structure
				$updateArray = array();
				$examMarks = \exam_marks::where('totalMarks','');
				if($examMarks->count() > 0){
					$examMarks = $examMarks->get();
					$updateSql = "";

					foreach ($examMarks as $mark) {
						$updateSql .= "UPDATE exam_marks SET examMark='".json_encode( array('1'=>$mark->attendanceMark) )."',totalMarks='".$mark->examMark."' where id='".$mark->id."'; ";
					}

					if($updateSql != ""){
						\DB::unprepared($updateSql);
					}
				}

				//Change the Fee Allocation
				\DB::table('fee_allocation')->update( array('allocationWhen'=> 'registered' ) );

				if (!\File::exists('uploads/onlineExams')){
					\File::makeDirectory('uploads/onlineExams');
				}

				if (!\File::exists('uploads/expenses')){
					\File::makeDirectory('uploads/expenses');
				}

				$testInstalled->fieldValue = "2.6";
			}

			if($testInstalled->fieldValue == "2.6"){
				\DB::unprepared(file_get_contents('storage/app/dbsqlUp30'));

				$settings = \settings::where('fieldName','thisVersion')->first();
				$settings->fieldValue = '3.0';
				$settings->save();


				$testInstalled->fieldValue = "3.0";
			}

			if($testInstalled->fieldValue == "3.0"){
				\DB::unprepared(file_get_contents('storage/app/dbsqlUp31'));

				$settings = \settings::where('fieldName','thisVersion')->first();
				$settings->fieldValue = '3.1';
				$settings->save();


				$testInstalled->fieldValue = "3.1";
			}

			if($testInstalled->fieldValue == "3.1"){
				\DB::unprepared(file_get_contents('storage/app/dbsqlUp32'));

				$settings = \settings::where('fieldName','thisVersion')->first();
				$settings->fieldValue = '3.2';
				$settings->save();


				$testInstalled->fieldValue = "3.2";
			}

			\DB::unprepared(file_get_contents('storage/app/dbsqlUpLang'));
		}

		if(\Input::get('nextStep') == "3"){
			$this->data['currStep'] = "3";
		}

		return \View::make('upgrade', $this->data);
	}

	function dateToUnix($date,$format=""){
		$d = DateTime::createFromFormat($format, $date);
		$d->setTime(0,0,0);
		return $d->getTimestamp();
	}

	public function sbApi(){
		$url = "http://solutionsbricks.com/license";
		$pco = @file_get_contents('storage/app/lc');
		if($pco == false){
			return "err";
		}
		$data = array("p"=>1,"n"=>$pco,"u"=>\Request::url(),"v"=>$this->version);
		if(function_exists('curl_init')){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			$output = curl_exec($ch);
			curl_close($ch);
		}elseif(function_exists('file_get_contents')){
			$postdata = http_build_query($data);

			$opts = array('http' =>
			    array(
			        'method'  => 'POST',
			        'header'  => 'Content-type: application/x-www-form-urlencoded',
			        'content' => $postdata
			    )
			);

			$context  = stream_context_create($opts);

			$output = file_get_contents($url, false, $context);
		}else{
			$stream = fopen($url, 'r', false, stream_context_create(array(
		          'http' => array(
		              'method' => 'POST',
		              'header' => 'Content-type: application/x-www-form-urlencoded',
		              'content' => http_build_query($data)
		          )
		      )));

		      $output = stream_get_contents($stream);
		      fclose($stream);
		}
		if($output == "err"){
			@unlink('storage/app/lc');
		}
		return $output;
	}

}
