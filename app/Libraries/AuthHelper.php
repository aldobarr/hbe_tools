<?php
namespace App\Libraries;

use Auth;
use DB;

class AuthHelper{
	private static $required = false;
	
	public static function require_smf(){
		if(self::$required)
			return;
		
		$path_to_smf = dirname(dirname(dirname(dirname(__FILE__)))) . '/hbe_pnw/';
		require_once($path_to_smf . 'SSI.php');
		require_once($path_to_smf . 'Sources/Security.php');
		require_once($path_to_smf . 'Sources/Subs-Post.php');
		self::$required = true;
	}
	
	public static function isLoggedIn(){
		self::require_smf();
		$user = ssi_welcome('return');
		return !$user['is_guest'];
	}
	
	public static function authedUser($user_ids){
		if(!is_array($user_ids))
			$user_ids = array($user_ids);
		self::require_smf();
		$user = ssi_welcome('return');
		return !$user['is_guest'] && in_array($user['id'], $user_ids);
	}
	
	public static function canAccess($permission){
		self::require_smf();
		$permissions = array();
		if(is_array($permission)){
			foreach($permission as $p){
				$permissions[] = 'hbe_tools_' . $p;
			}
		}else
			$permissions = array('hbe_tools_' . $permission);
		return allowedTo($permissions);
	}
}