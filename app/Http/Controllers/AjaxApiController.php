<?php

namespace App\Http\Controllers;

use Auth;
use AuthHelper;
use DB;
use App\User;
use App\Http\Controllers\Controller;

class AjaxApiController extends Controller
{	
	public function submitTarget(){
		$result = array('code' => 0, 'status' => 'Action Failed', 'error' => 'You are not authorized to use this API function.');
		if(!AuthHelper::canAccess('assign_targets'))
			die(json_encode($result));
		
		
	}
	
	public function userMan(){
		if(!AuthHelper::canAccess('edit_user_role')){
			http_response_code(401);
			die('You are not authorized to use this function.');
		}
		
		if(empty($_POST['name']) || empty($_POST['pk']) || !isset($_POST['value'])){
			http_response_code(400);
			die('The post values were blank.');
		}
		
		$name = $_POST['name'];
		$pk = (int)$_POST['pk'];
		$value = (int)$_POST['value'];
		
		$user = Auth::user();
		if($pk < 1 || $pk != $_POST['pk']){
			http_response_code(400);
			die('The primary key must be a positive integer.');
		}
		if($value < 0 || $value != $_POST['value'] || $value > DB::table('auth_levels')->max('id')){
			http_response_code(400);
			die('You must select a rank from the drop down list.');
		}
		
		$row = DB::table('users')->where('id', $pk)->first();
		if(empty($row)){
			http_response_code(412);
			die('That primary key does not exist.');
		}
		if($user->auth_level < $row['auth_level']){
			http_response_code(412);
			die('You are not allowed to change the rank of someone superior to you.');
		}
		if($user->auth_level < $value){
			http_response_code(412);
			die('You are not allowed to grant a rank superior to your own.');
		}
		
		DB::table('users')->where('id', $pk)->update([$name => $value]);
		http_response_code(200);
	}
	
	public function userManDel($id){
		$result = array('code' => 0, 'status' => 'Action Failed', 'error' => 'You are not authorized to use this API function.');
		if(!AuthHelper::canAccess('delete_user'))
			die(json_encode($result));
		
		$id = (int)$id;
		$row = DB::table('users')->where('id', $id)->first();
		$user = Auth::user();
		if(empty($row)){
			$result['error'] = 'There is no such user in the db.';
			die(json_encode($result));
		}
		if($row['auth_level'] >= $user->auth_level){
			$result['error'] = 'You may not delete someone of greater or equal rank.';
			die(json_encode($result));
		}
		if($row['auth_level'] == 5){
			$result['error'] = 'A super admin may not be deleted.';
			die(json_encode($result));
		}
		
		DB::table('users')->where('id', $id)->delete();
		$result['code'] = 1;
		$result['status'] = 'That user was deleted successfully.';
		
		$result['error'] = '';
		echo json_encode($result);
	}
	
	public function permsMan(){
		if(!AuthHelper::canAccess('perm_man')){
			http_response_code(401);
			die('You are not authorized to use this function.');
		}
		
		$user = Auth::user();
		if(empty($_POST['name']) || empty($_POST['pk']) || !isset($_POST['value'])){
			http_response_code(400);
			die('The post values were blank.');
		}
		
		if($_POST['value'] != 0 && empty($_POST['value'])){
			http_response_code(400);
			die('The post values were blank.');
		}
		
		$name = $_POST['name'];
		$pk = (int)$_POST['pk'];
		$value = $_POST['value'];
		
		if($pk < 1 || $pk != $_POST['pk']){
			http_response_code(400);
			die('The primary key may not be negative.');
		}
		
		$row = DB::table('permissions')->where('id', $pk)->first();
		if(empty($row)){
			http_response_code(412);
			die('That primary key does not exist.');
		}
		
		if($name == 'auth_level'){
			if($value > $user->auth_level){
				http_response_code(412);
				die('You are not allowed to assign a role superior to your own.');
			}
			if($row['auth_level'] > $user->auth_level){
				http_response_code(412);
				die('You are not allowed to modify the role of a permission where a role superior to yours is assigned.');
			}
		}else if(!AuthHelper::canAccess('perm_edit')){
			http_response_code(401);
			die('You are not authorized to use this function.');
		}
		
		DB::table('permissions')->where('id', $pk)->update([$name => $value]);
		http_response_code(200);
	}
	
	public function permsManAdd(){
		$result = array('code' => 0, 'status' => 'Action Failed', 'error' => 'You are not authorized to use this API function.');
		if(!AuthHelper::canAccess('perm_add'))
			die(json_encode($result));
		
		if(empty($_POST['name']) || empty($_POST['desc'])){
			$result['error'] = 'The name and description fields can not be blank.';
			die(json_encode($result));
		}
		
		$id = DB::table('auth_levels')->max('id');
		DB::table('permissions')->insert(['name' => $_POST['name'], 'description' => $_POST['desc'], 'auth_level' => $id]);
		
		$new_perm = DB::table('permissions')->where('name', $_POST['name'])->get();
		if(empty($new_perm)){
			$result['error'] = 'There was an error adding the new role to the db.';
			die(json_encode($result));
		}
		
		$result['code'] = 1;
		$result['status'] = 'The role was successfully created.';
		$result['error'] = '';
		$result['perm'] = $new_perm[0];
		$result['perm']['role'] = DB::table('auth_levels')->where('id', $id)->first();
		echo json_encode($result);
	}
	
	public function permsManDel($id){
		$result = array('code' => 0, 'status' => 'Action Failed', 'error' => 'You are not authorized to use this API function.');
		if(!AuthHelper::canAccess('perm_del'))
			die(json_encode($result));
		
		$id = (int)$id;
		$row = DB::table('permissions')->where('id', $id)->first();
		if(empty($row)){
			$result['error'] = 'There is no such permission in the db.';
			die(json_encode($result));
		}
		
		DB::table('permissions')->where('id', $id)->delete();
		
		$result['code'] = 1;
		$result['status'] = 'The permission was deleted successfully.';
		$result['error'] = '';
		echo json_encode($result);
	}
	
	public function rolesMan(){
		if(!AuthHelper::canAccess('role_man')){
			http_response_code(401);
			die('You are not authorized to use this function.');
		}
		
		if(empty($_POST['name']) || !isset($_POST['pk']) || empty($_POST['value'])){
			http_response_code(400);
			die('The post values were blank.');
		}
		
		$name = $_POST['name'];
		$pk = (int)$_POST['pk'];
		$value = $_POST['value'];
		
		if($pk < 0 || $pk != $_POST['pk']){
			http_response_code(400);
			die('The primary key may not be negative.');
		}
		
		$row = DB::table('auth_levels')->where('id', $pk)->first();
		if(empty($row)){
			http_response_code(412);
			die('That primary key does not exist.');
		}
		
		DB::table('auth_levels')->where('id', $pk)->update([$name => $value]);
		http_response_code(200);
	}
	
	public function rolesManAdd(){
		$result = array('code' => 0, 'status' => 'Action Failed', 'error' => 'You are not authorized to use this API function.');
		if(!AuthHelper::canAccess('role_man'))
			die(json_encode($result));
		
		if(empty($_POST['name']) || empty($_POST['desc'])){
			$result['error'] = 'The name and description fields can not be blank.';
			die(json_encode($result));
		}
		
		$id = DB::table('auth_levels')->max('id') + 1;
		DB::table('auth_levels')->insert(['id' => $id, 'name' => $_POST['name'], 'description' => $_POST['desc']]);
		
		$new_role = DB::table('auth_levels')->where('id', $id)->get();
		if(empty($new_role)){
			$result['error'] = 'There was an error adding the new role to the db.';
			die(json_encode($result));
		}
		
		$result['code'] = 1;
		$result['status'] = 'The role was successfully created.';
		$result['error'] = '';
		$result['role'] = $new_role[0];
		echo json_encode($result);
	}
	
	public function rolesManDel($id){
		$result = array('code' => 0, 'status' => 'Action Failed', 'error' => 'You are not authorized to use this API function.');
		if(!AuthHelper::canAccess('role_man'))
			die(json_encode($result));
		
		$id = (int)$id;
		if($id == 0){
			$result['error'] = 'The basic role may not be deleted.';
			die(json_encode($result));
		}
		
		$row = DB::table('auth_levels')->where('id', $id)->first();
		if(empty($row)){
			$result['error'] = 'There is no such role in the db.';
			die(json_encode($result));
		}
		$users = DB::table('users')->where('auth_level', $id)->get();
		if(!empty($users)){
			$result['error'] = 'You may not delete a role while there is any user attached to that role.';
			die(json_encode($result));
		}
		$permissions = DB::table('permissions')->where('auth_level', $id)->get();
		if(!empty($permissions)){
			$result['error'] = 'You may not delete a role while there is any permission attached to that role.';
			die(json_encode($result));
		}
			
		$max_id = DB::table('auth_levels')->max('id');
		DB::table('auth_levels')->where('id', $id)->delete();
		$result['reload'] = false;
		if($id < $max_id){
			$result['reload'] = true;
			$result['reload_id'] = $id;
			$result['reload_max'] = $max_id;
			$this->fixIds($id);
		}
		$result['code'] = 1;
		$result['status'] = 'The role was deleted successfully.';
		
		$result['error'] = '';
		echo json_encode($result);
	}
	
	public function messageMan(){
		if(!AuthHelper::canAccess('edit_recruitment_message')){
			http_response_code(401);
			die('You are not authorized to use this function.');
		}
		
		if(empty($_POST['name']) || empty($_POST['pk']) || !isset($_POST['value'])){
			http_response_code(400);
			die('The post values were blank.');
		}
		
		if(empty($_POST['value'])){
			http_response_code(400);
			die('The post values were blank.');
		}
		
		$name = $_POST['name'];
		$pk = (int)$_POST['pk'];
		$value = $_POST['value'];
		
		if($pk < 1 || $pk != $_POST['pk']){
			http_response_code(400);
			die('The primary key may not be negative.');
		}
		if($name == 'subject' && strlen($value) > 50){
			http_response_code(400);
			die('The subject may not have more than 50 characters.');
		}
		
		$row = DB::table('recruitment_messages')->where('id', $pk)->first();
		if(empty($row)){
			http_response_code(412);
			die('That primary key does not exist.');
		}
		
		DB::table('recruitment_messages')->where('id', $pk)->update([$name => $value]);
		http_response_code(200);
	}
	
	public function messageManAdd(){
		$result = array('code' => 0, 'status' => 'Action Failed', 'error' => 'You are not authorized to use this API function.');
		if(!AuthHelper::canAccess('add_recruitment_message'))
			die(json_encode($result));
		
		if(empty($_POST['subject']) || empty($_POST['body']) || empty($_POST['type'])){
			$result['error'] = 'The subject and body fields can not be blank.';
			die(json_encode($result));
		}
		if(strlen($_POST['subject']) > 50){
			$result['error'] = 'The subject may not have more than 50 characters.';
			die(json_encode($result));
		}
		$_POST['type'] = (int)$_POST['type'];
		
		DB::table('recruitment_messages')->insert(['subject' => $_POST['subject'], 'body' => $_POST['body'], 'type' => $_POST['type'], 'active' => 1]);
		
		$new_message = DB::table('recruitment_messages')->where('id', DB::getPdo()->lastInsertId())->first();
		if(empty($new_message)){
			$result['error'] = 'There was an error adding the new message to the db.';
			die(json_encode($result));
		}
		
		$result['code'] = 1;
		$result['status'] = 'The message was successfully created.';
		$result['error'] = '';
		$result['message'] = $new_message;
		echo json_encode($result);
	}
	
	public function messageManDel($id){
		$result = array('code' => 0, 'status' => 'Action Failed', 'error' => 'You are not authorized to use this API function.');
		if(!AuthHelper::canAccess('del_recruitment_messages'))
			die(json_encode($result));
		
		$id = (int)$id;
		$row = DB::table('recruitment_messages')->where('id', $id)->first();
		if(empty($row)){
			$result['error'] = 'There is no such message in the db.';
			die(json_encode($result));
		}
		
		DB::table('recruitment_messages')->where('id', $id)->delete();
		
		$result['code'] = 1;
		$result['status'] = 'The message was deleted successfully.';
		$result['error'] = '';
		echo json_encode($result);
	}
	
	public function messageManToggle($id){
		$result = array('code' => 0, 'status' => 'Action Failed', 'error' => 'You are not authorized to use this API function.');
		if(!AuthHelper::canAccess('toggle_recruitment_messages'))
			die(json_encode($result));
		
		$id = (int)$id;
		$row = DB::table('recruitment_messages')->where('id', $id)->first();
		if(empty($row)){
			$result['error'] = 'There is no such message in the db.';
			die(json_encode($result));
		}
		
		DB::table('recruitment_messages')->where('id', $id)->update(['active' => (1 - $row['active'])]);
		$result['code'] = 1;
		$result['status'] = 'The message was toggled successfully.';
		$result['error'] = '';
		$result['old'] = $row['active'];
		echo json_encode($result);
	}
	
	public function warMapAdd(){
		$result = array('code' => 0, 'status' => 'Action Failed', 'error' => 'You are not authorized to use this API function.');
		if(!AuthHelper::canAccess('manage_map'))
			die(json_encode($result));
		
		if(empty($_POST['aggressor']) || empty($_POST['side']) || empty($_POST['defender'])){
			$result['error'] = 'Something was left blank.';
			die(json_encode($result));
		}
		if($_POST['side'] != 1 && $_POST['side'] != 2){
			$result['error'] = 'Inalid side selection.';
			die(json_encode($result));
		}
		$aggressor = (int)$_POST['aggressor'];
		$defender = (int)$_POST['defender'];
		$side = (int)$_POST['side'];
		
		if($aggressor == $defender){
			$result['error'] = 'The defender can\'t be the same as the attacker.';
			die(json_encode($result));
		}
		if(DB::table('alliances')->where('id', $aggressor)->orWhere('id', $defender)->count() != 2){
			$result['error'] = 'One or both of the alliances inserted do not exist.';
			die(json_encode($result));
		}
		if(DB::table('war_map')->where('aggressor', $aggressor)->where('defender', $defender)->count() != 0){
			$result['error'] = 'That war already exists.';
			die(json_encode($result));
		}
		if(DB::table('war_map')->where('aggressor', $defender)->where('defender', $aggressor)->count() != 0){
			$result['error'] = 'That war already exists.';
			die(json_encode($result));
		}
		
		DB::table('war_map')->insert(['aggressor' => $aggressor, 'aside' => $side, 'defender' => $defender, 'dside' => ($side == 1 ? 2 : 1)]);
		
		$new_war = DB::select('select wm.aggressor, wm.defender, a.name as aname, d.name as dname from war_map as wm inner join alliances as a on a.id = wm.aggressor inner join alliances as d on d.id = wm.defender and wm.aggressor = :aggressor and wm.defender = :defender', ['aggressor' => $aggressor, 'defender' => $defender]);
		if(empty($new_war) || empty($new_war[0])){
			$result['error'] = 'There was an error adding the new war to the db.';
			die(json_encode($result));
		}
		
		$new_war = $new_war[0];
		$result['code'] = 1;
		$result['status'] = 'The war was successfully created.';
		$result['error'] = '';
		$result['war'] = $new_war;
		$result['war']['id'] = $new_war['aggressor'] + $new_war['defender'];
		echo json_encode($result);
	}
	
	public function warMapDel($id){
		$result = array('code' => 0, 'status' => 'Action Failed', 'error' => 'You are not authorized to use this API function.');
		if(!AuthHelper::canAccess('manage_map'))
			die(json_encode($result));
		
		if($id == 'all'){
			DB::table('war_map')->truncate();
			$result['code'] = 1;
			$result['status'] = 'The war map was successfully truncated.';
			$result['error'] = '';
			die(json_encode($result));
		}
		$ids = explode('_', $id);
		if(count($ids) != 2){
			$result['error'] = 'Malformed id.';
			die(json_encode($result));
		}
		$aid = (int)$ids[0];
		$did = (int)$ids[1];
		if(DB::table('war_map')->where('aggressor', $aid)->where('defender', $did)->count() == 0){
			$result['error'] = 'There is no such war in the db.';
			die(json_encode($result));
		}
		
		DB::table('war_map')->where('aggressor', $aid)->where('defender', $did)->delete();
		
		$result['code'] = 1;
		$result['status'] = 'The war was deleted successfully.';
		$result['error'] = '';
		echo json_encode($result);
	}
	
	public function treatyWebAdd(){
		$result = array('code' => 0, 'status' => 'Action Failed', 'error' => 'You are not authorized to use this API function.');
		if(!AuthHelper::canAccess('manage_web'))
			die(json_encode($result));
		
		if(empty($_POST['aggressor']) || empty($_POST['type']) || empty($_POST['defender'])){
			$result['error'] = 'Something was left blank.';
			die(json_encode($result));
		}
		$type = (int)$_POST['type'];
		if($type < 1 || $type > 8){
			$result['error'] = 'Inalid treaty type selection.';
			die(json_encode($result));
		}
		$aggressor = (int)$_POST['aggressor'];
		$defender = (int)$_POST['defender'];
		$count_expected = $aggressor == $defender ? 1 : 2;
		if(DB::table('alliances')->where('id', $aggressor)->orWhere('id', $defender)->count() != $count_expected){
			$result['error'] = 'One or both of the alliances inserted do not exist.';
			die(json_encode($result));
		}
		if(DB::table('treaty_web')->where('one', $aggressor)->where('two', $defender)->count() != 0){
			$result['error'] = 'There already exists a treaty between those two alliance.';
			die(json_encode($result));
		}
		if(DB::table('treaty_web')->where('one', $defender)->where('two', $aggressor)->count() != 0){
			$result['error'] = 'There already exists a treaty between those two alliance.';
			die(json_encode($result));
		}
		
		DB::table('treaty_web')->insert(['one' => $aggressor, 'two' => $defender, 'type' => $type]);
		
		$new_treaty = DB::select('select tw.one, tw.two, tw.type, a.name as aname, d.name as dname from treaty_web as tw inner join alliances as a on a.id = tw.one inner join alliances as d on d.id = tw.two and tw.one = :aggressor and tw.two = :defender', ['aggressor' => $aggressor, 'defender' => $defender]);
		if(empty($new_treaty) || empty($new_treaty[0])){
			$result['error'] = 'There was an error adding the new treaty to the db.';
			die(json_encode($result));
		}
		
		$new_treaty = $new_treaty[0];
		$result['code'] = 1;
		$result['status'] = 'The treaty was successfully created.';
		$result['error'] = '';
		$result['treaty'] = $new_treaty;
		$result['treaty']['id'] = $new_treaty['one'] + $new_treaty['two'];
		echo json_encode($result);
	}
	
	public function treatyWebDel($id){
		$result = array('code' => 0, 'status' => 'Action Failed', 'error' => 'You are not authorized to use this API function.');
		if(!AuthHelper::canAccess('manage_web'))
			die(json_encode($result));
		
		$ids = explode('_', $id);
		if(count($ids) != 2){
			$result['error'] = 'Malformed id.';
			die(json_encode($result));
		}
		$aid = (int)$ids[0];
		$did = (int)$ids[1];
		if(DB::table('treaty_web')->where('one', $aid)->where('two', $did)->count() == 0){
			$result['error'] = 'There is no such treaty in the db.';
			die(json_encode($result));
		}
		
		DB::table('treaty_web')->where('one', $aid)->where('two', $did)->delete();
		
		$result['code'] = 1;
		$result['status'] = 'The treaty was deleted successfully.';
		$result['error'] = '';
		echo json_encode($result);
	}
	
	private function fixIds($id){
		DB::table('auth_levels')->where('id', '>=', $id)->decrement('id');
		DB::table('users')->where('auth_level', '>=', $id)->decrement('auth_level');
		DB::table('permissions')->where('auth_level', '>=', $id)->decrement('auth_level');
	}
}
