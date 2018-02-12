<?php

namespace App\Http\Controllers;

use Auth;
use AuthHelper;
use DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ManagementController extends Controller
{
	public function user($all = 0){
		if(!AuthHelper::canAccess('view_user'))
			return view('hbe.access_denied');
		
		$users = array();
		if(empty($all))
			$users = DB::table('users')->where('activated', 1)->where('password', '!=', '')->where('auth_level', '>', 0)->get();
		else
			$users = DB::table('users')->where('activated', 1)->where('password', '!=', '')->get();
		$roles = DB::table('auth_levels')->get();
		return view('hbe.user_man', ['users' => $users, 'roles' => $roles]);
	}
	
	public function permissions(){
		if(!AuthHelper::canAccess('perm_man'))
			return view('hbe.access_denied');
		
		$permissions = DB::table('permissions')->get();
		$roles = DB::table('auth_levels')->get();
		return view('hbe.permissions_man', ['permissions' => $permissions, 'roles' => $roles]);
	}
	
	public function roles(){
		if(!AuthHelper::canAccess('role_man'))
			return view('hbe.access_denied');
		
		$roles = DB::table('auth_levels')->get();
		return view('hbe.roles_man', ['roles' => $roles]);
	}
}