<?php

namespace App\Http\Controllers;

use AuthHelper;
use DB;
use App\Http\Controllers\Controller;

class KeySecurity extends Controller
{
	public function checkKey($key){
		$keyData = DB::table('key_security')->where('key', $key)->first();
		$result = array('success' => false, 'clientName' => '', 'features' => array());
		if(empty($keyData) || empty($keyData['valid'])){
			echo json_encode($result);
			die();
		}
		
		$result['success'] = true;
		$result['clientName'] = $keyData['client'];
		$result[$keyData['product']] = true;
		$result['features'] = json_decode($keyData['features'], true);
		$result['authorized_user'] = $keyData['authorized_user'];
		echo json_encode($result);
		die();
	}

	public function setUser($key, $nation_id){
		$keyData = DB::table('key_security')->where('key', $key)->first();
		if(empty($keyData))
			die('0');
		if($keyData['authorized_user'] > 0)
			die('0');

		DB::table('key_security')->where('id', $keyData['id'])->update(['authorized_user' => $nation_id]);
		die('1');
	}
}