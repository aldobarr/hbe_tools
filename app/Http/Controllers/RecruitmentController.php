<?php

namespace App\Http\Controllers;

use Auth;
use AuthHelper;
use DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RecruitmentController extends Controller
{
	public function messages(){
		if(!AuthHelper::canAccess('recruitment_messages'))
			return view('hbe.access_denied');
		
		$messages = DB::table('recruitment_messages')->get();
		$types = array(1 => 'Noob Recruitment', 2 => 'Application Reminder', 3 => 'Experienced Recruitment');
		return view('hbe.recruit.messages', ['messages' => $messages, 'types' => $types]);
	}
	
	public function sendPM(){
		if(!AuthHelper::canAccess('send_pm'))
			return view('hbe.access_denied');
		
		return view('hbe.recruit.sendpm');
	}
	
	public function postPM(Request $request){
		if(!AuthHelper::canAccess('send_pm'))
			return view('hbe.access_denied');
		
		$this->validate($request, [
			'subject' => 'required|max:50',
			'body' => 'required'
		]);
		$subject = $request->input('subject');
		$body = $request->input('body');
		$time = time();
		
		DB::table('send_pm')->insert(['subject' => $subject, 'body' => $body, 'time' => time()]);
		return view('hbe.recruit.sendpm', ['success' => true]);
	}
}