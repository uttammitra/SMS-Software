<?php
namespace App\Http\Controllers;

class LicenseController extends Controller {

	var $data = array();
	var $panelInit ;

	public function __construct(){

	}

	public function index()
	{
		return \View::make('license', $this->data);
	}

	public function proceed()
	{
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
			\settings::where('fieldName','lastUpdateCheck')->update(array('fieldValue' => 0));
			$sbApi = $this->sbApi();
			if($sbApi == "err"){
				@unlink('storage/app/lc');
				$this->data['installErrors'][] = "Purchase code is missing";
				$loginError = false;
				$this->data['loginError'] = "loginError";
			}else{
				file_put_contents('storage/app/mlc',$sbApi);
			}
			$this->data['success'] = true;
		}

		return \View::make('license', $this->data);
	}

	public function sbApi(){
		$url = "http://solutionsbricks.com/license";
		$pco = @file_get_contents('storage/app/lc');
		if($pco == false){
			return "err";
		}
		$data = array("p"=>1,"n"=>$pco,"u"=>\Request::url());
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
