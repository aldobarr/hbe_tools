<?php

namespace App\Http\Controllers;

use AuthHelper;
use DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SignInController extends Controller
{
	public function signInForm(){
		if(!AuthHelper::canAccess('warchest_form'))
			return view('hbe.access_denied');
		
		return view('hbe.warchest.signin');
	}
	
	public function signIn(Request $request){
		if(!AuthHelper::canAccess('warchest_form'))
			return view('hbe.access_denied');
		
		$this->validate($request, [
            'nation_data' => 'required'
        ]);
		
		$data = $request->input('nation_data');
		return $this->performSignIn($data);
	}
	
	public function signInFormMobile(){
		if(!AuthHelper::canAccess('warchest_form'))
			return view('hbe.access_denied');
		
		return view('hbe.warchest.signin_mobile');
	}
	
	public function signInMobile(Request $request){
		if(!AuthHelper::canAccess('warchest_form'))
			return view('hbe.access_denied');
		
		$this->validate($request, [
			'id' => 'required|integer',
            'credits' => 'required|integer',
			'coal' => 'required|numeric',
			'oil' => 'required|numeric',
			'uranium' => 'required|numeric',
			'lead' => 'required|numeric',
			'iron' => 'required|numeric',
			'bauxite' => 'required|numeric',
			'gasoline' => 'required|numeric',
			'munitions' => 'required|numeric',
			'steel' => 'required|numeric',
			'aluminum' => 'required|numeric',
			'food' => 'required|numeric',
			'cash' => 'required|numeric'
        ]);
		
		$nation_id = $request->input('id');
		$nation_data = array(
			'credits' => trim(str_replace(',', '', $request->input('credits'))),
			'coal' => trim(str_replace(',', '', $request->input('coal'))),
			'oil' => trim(str_replace(',', '', $request->input('oil'))),
			'uranium' => trim(str_replace(',', '', $request->input('uranium'))),
			'lead' => trim(str_replace(',', '', $request->input('lead'))),
			'iron' => trim(str_replace(',', '', $request->input('iron'))),
			'bauxite' => trim(str_replace(',', '', $request->input('bauxite'))),
			'gasoline' => trim(str_replace(',', '', $request->input('gasoline'))),
			'munitions' => trim(str_replace(',', '', $request->input('munitions'))),
			'steel' => trim(str_replace(',', '', $request->input('steel'))),
			'aluminum' => trim(str_replace(',', '', $request->input('aluminum'))),
			'food' => trim(str_replace(',', '', $request->input('food'))),
			'cash' => trim(str_replace(array('$', ','), array('', ''), $request->input('cash'))),
			'time' => time()
		);
		
		$pnw = json_decode(file_get_contents('https://politicsandwar.com/api/nation/id=' . $nation_id));
		if(!empty($pnw->error))
			return view('hbe.warchest.signin_mobile', ['error' => $pnw->error]);
		else if($pnw->allianceid != 2570 || $pnw->allianceposition <= 1)
			return view('hbe.warchest.signin_mobile', ['error' => 'The nation id you entered is not for a member of the Holy Britannian Empire!']);
		
		if(DB::table('warchests')->where('id', $nation_id)->count() > 0)
			DB::table('warchests')->where('id', $nation_id)->update($nation_data);
		else{
			$nation_data['id'] = $nation_id;
			DB::table('warchests')->insert($nation_data);
		}
		return view('hbe.warchest.signin_mobile', ['success' => 1]);
	}
	
	public function signInAuto(Request $request){
		$this->validate($request, [
            'nation_data' => 'required'
        ]);
		
		$data = urldecode($request->input('nation_data'));
		return $this->performSignIn($data);
	}
	
	private function getNationId($data){
		$matches = array();
		preg_match('/nation\/id=[0-9]+"><li>View<\/li>/', $data, $matches);
		$substr = $matches[0];
		$nation_id_str = substr($substr, 0, strpos($substr, '"'));
		$arr = explode('=', $nation_id_str);
		return $arr[1];
	}
	
	private function performSignIn($data){
		$dom = new \DOMDocument;
		@$dom->loadHTML($data);
		$divs = $dom->getElementsByTagName('div');
		$infoBar = null;
		foreach($divs as $div){
			if($div->getAttribute('class') == 'informationbar col-xs-12'){
				$infoBar = $div;
				break;
			}
		}
		if($infoBar == null)
			return view('hbe.warchest.signin', ['error' => 'Unable to parse nation source properly. Error Code: 1']);
		
		$nation_id = $this->getNationId($data);
		if(DB::table('nation_stats')->where('nation_id', $nation_id)->count() <= 0)
			return view('hbe.warchest.signin', ['error' => 'Unable to record nation data from non-members of the Holy Britannian Empire. Error Code: 2']);
		$span = @$infoBar->getElementsByTagName('span')[0];
		if(empty($span))
			return view('hbe.warchest.signin', ['error' => 'Unable to parse nation source properly. Error Code: 3']);
		
		$temp_arr = explode(PHP_EOL, $span->nodeValue);
		$nation_data = array(
			'credits' => trim(str_replace(',', '', $temp_arr[1])),
			'coal' => trim(str_replace(',', '', $temp_arr[2])),
			'oil' => trim(str_replace(',', '', $temp_arr[3])),
			'uranium' => trim(str_replace(',', '', $temp_arr[4])),
			'lead' => trim(str_replace(',', '', $temp_arr[5])),
			'iron' => trim(str_replace(',', '', $temp_arr[6])),
			'bauxite' => trim(str_replace(',', '', $temp_arr[7])),
			'gasoline' => trim(str_replace(',', '', $temp_arr[8])),
			'munitions' => trim(str_replace(',', '', $temp_arr[9])),
			'steel' => trim(str_replace(',', '', $temp_arr[10])),
			'aluminum' => trim(str_replace(',', '', $temp_arr[11])),
			'food' => trim(str_replace(',', '', $temp_arr[12])),
			'cash' => trim(str_replace(array('$', ','), array('', ''), $temp_arr[13])),
			'time' => time()
		);
		
		if(DB::table('warchests')->where('id', $nation_id)->count() > 0)
			DB::table('warchests')->where('id', $nation_id)->update($nation_data);
		else{
			$nation_data['id'] = $nation_id;
			DB::table('warchests')->insert($nation_data);
		}
		return view('hbe.warchest.signin', ['success' => 1]);
	}
}