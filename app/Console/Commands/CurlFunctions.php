<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\DB;

trait CurlFunctions
{
	private $cookie_file = '';
	private $backup_file = '';
	private $alliance_id = 2570;
	private $user_agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/59.0.3071.115 Safari/537.36';
	private $interface = 'eth0:0';
	private $api_key = '5bc66112ed1543';
	
	protected function checkLoggedIn($ch = null){
		$this->set_cookie_file();
		$closer = false;
		if($ch === null){
			$closer = true;
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_INTERFACE, $this->interface);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
			curl_setopt($ch, CURLOPT_TIMEOUT, 20);
			curl_setopt($ch, CURLOPT_AUTOREFERER, true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_COOKIESESSION, true);
			curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_file);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_file);
			curl_setopt($ch, CURLOPT_FAILONERROR, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
		}
		curl_setopt($ch, CURLOPT_URL, 'https://politicsandwar.com/');
		$result = curl_exec($ch);
		if($result === FALSE){
			$error = curl_error($ch);
			DB::table('error_log')->insert(['error' => $error, 'time' => time()]);
			if($closer)
				curl_close($ch);
			return 2;
		}
		if($closer)
			curl_close($ch);
		return stristr($result, 'logout') ? true : false;
	}
	
	protected function login($email = 'default@email.com', $pass = 'pass', $ch = null){
		$this->set_cookie_file();
		$post_url = 'https://politicsandwar.com/login/';
		$params = array(
			'email' => $email,
			'password' => $pass,
			'rememberme' => '1',
			'loginform' => 'Login'
		);
		
		$closer = false;
		if($ch === null){
			$closer = true;
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_INTERFACE, $this->interface);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
			curl_setopt($ch, CURLOPT_TIMEOUT, 20);
			curl_setopt($ch, CURLOPT_AUTOREFERER, true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_COOKIESESSION, true);
			curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_file);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_file);
			curl_setopt($ch, CURLOPT_FAILONERROR, true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
		}
		curl_setopt($ch, CURLOPT_URL, $post_url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params, '', '&'));
		$result = curl_exec($ch);
		if($result === FALSE){
			$error = curl_error($ch);
			DB::table('error_log')->insert(['error' => $error, 'time' => time()]);
		}
		if($closer)
			curl_close($ch);
	}
	
	public function logout(){
		$res = $this->getPage('https://politicsandwar.com/logout/');
		return (stristr($res, 'You will now be directed to the homepage.') !== FALSE);
	}
	
	public function getPage($post_url, $post = false, $params = array()){
		if($post !== true)
			$post = false;
		if(!is_array($params))
			$params = array();
		
		$this->set_cookie_file();
		$bin_trans = false;
		$referer = '';
		if(array_key_exists('{bin_trans}', $params)){
			$bin_trans = true;
			unset($params['{bin_trans}']);
		}
		if(array_key_exists('{set_referer}', $params)){
			$referer = $params['{set_referer}'];
			unset($params['{set_referer}']);
		}
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_INTERFACE, $this->interface);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, $bin_trans);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($ch, CURLOPT_TIMEOUT, 20);
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		if(!empty($referer))
			curl_setopt($ch, CURLOPT_REFERER, $referer);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_COOKIESESSION, true);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_file);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_file);
		curl_setopt($ch, CURLOPT_FAILONERROR, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
		curl_setopt($ch, CURLOPT_URL, $post_url);
		curl_setopt($ch, CURLOPT_POST, $post);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params, '', '&'));
		$result = curl_exec($ch);
		if($result === FALSE){
			$error = curl_error($ch);
			DB::table('error_log')->insert(['error' => $error, 'time' => time()]);
		}
		curl_close($ch);
		
		return $result;
	}
	
	public function getNewCurl($use_interface = true, $use_backup_cookie = false){
		$ch = curl_init();
		if($use_interface)
			curl_setopt($ch, CURLOPT_INTERFACE, $this->interface);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($ch, CURLOPT_TIMEOUT, 20);
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_COOKIESESSION, true);
		if(!$use_backup_cookie){
			curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_file);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_file);
		}else{
			curl_setopt($ch, CURLOPT_COOKIEJAR, $this->backup_file);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $this->backup_file);
		}
		curl_setopt($ch, CURLOPT_FAILONERROR, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
		return $ch;
	}
	
	public function getPageWithResource($ch, $post_url, $post = false, $params = array()){
		if($ch == null || !is_resource($ch))
			return false;
		if(get_resource_type($ch) != 'curl')
			return false;
		
		if($post !== true)
			$post = false;
		if(!is_array($params))
			$params = array();
		
		$this->set_cookie_file();
		$bin_trans = false;
		$referer = '';
		if(array_key_exists('{bin_trans}', $params)){
			$bin_trans = true;
			unset($params['{bin_trans}']);
		}
		if(array_key_exists('{set_referer}', $params)){
			$referer = $params['{set_referer}'];
			unset($params['{set_referer}']);
		}
		
		if(!empty($referer))
			curl_setopt($ch, CURLOPT_REFERER, $referer);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, $bin_trans);
		curl_setopt($ch, CURLOPT_URL, $post_url);
		curl_setopt($ch, CURLOPT_POST, $post);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params, '', '&'));
		$result = curl_exec($ch);
		if($result === FALSE){
			$error = curl_error($ch);
			DB::table('error_log')->insert(['error' => $error, 'time' => time()]);
		}
		
		return $result;
	}
	
	private function moveNation($nation, $applicant){
		if(!isset($nation->leader) && !isset($nation->leadername)){
			if(is_numeric($nation) && ((int)$nation) == $nation){
				$nation = json_decode($this->getPage('https://politicsandwar.com/api/nation/id=' . $nation));
				if(empty($nation) || !empty($nation->error))
					return false;
			}
		}
		
		$params = array(
			'nationperm' => isset($nation->leader) ? $nation->leader : $nation->leadername,
			'level' => $applicant ? '2' : '1',
			'permsubmit' => 'Go'
		);
		$this->getPage('https://politicsandwar.com/alliance/id=2570', true, $params);
	}
	
	public function closeCurlResource($ch){
		if($ch == null || !is_resource($ch))
			return;
		if(get_resource_type($ch) != 'curl')
			return;
		
		curl_close($ch);
	}
	
	private function set_cookie_file(){
		if(empty($this->cookie_file)){
			$this->cookie_file = base_path() . '/cookies/cookie_file';
			$this->backup_file = base_path() . '/cookies/backup_file';
		}
	}
}
