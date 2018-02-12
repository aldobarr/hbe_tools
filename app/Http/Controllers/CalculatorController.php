<?php

namespace App\Http\Controllers;

use Auth;
use AuthHelper;
use Cache;
use DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;

class CalculatorController extends Controller
{
	use HBEFunctions;
	use \App\Console\Commands\CurlFunctions;
	
	public function form(){
		return view('hbe.calc.form');
	}
	
	public function formSubmit(Request $request){
		Validator::extend('ve_nation', function($attribute, $value, $parameters, $validator) {
			$nation = json_decode($this->getPage('https://politicsandwar.com/api/nation/id=' . $value));
			return empty($nation->error) && !empty($nation->allianceid) && $nation->allianceid == 866 && !empty($nation->allianceposition) && $nation->allianceposition > 1;
        });
		
		$validator = Validator::make($request->all(), [
			'id' => 'required|integer|min:1|ve_nation',
			'infra' => 'required|integer|min:10'
		], ['id.ve_nation' => 'You must enter a valid nation id that belongs to the Viridian Entente']);
		if ($validator->fails())
            return redirect('ve/form')->withErrors($validator)->withInput();
		
		$id = $request->input('id');
		
		$infra = $request->input('infra');
		$ccep = $request->has('ccep') ? 1 : 0;
		$count = DB::table('ve_stats')->where('nation_id', $id)->count();
		if($count > 0)
			DB::table('ve_stats')->where('nation_id', $id)->update(['infra' => $infra, 'ccep' => $ccep, 'leader' => '']);
		else
			DB::table('ve_stats')->insert(['nation_id' => $id, 'infra' => $infra, 'ccep' => $ccep]);
		
		return view('hbe.calc.form', ['success' => true]);
	}
	
	public function rebuild(){
		if(Cache::has(md5('schedule:temp')))
			return view('hbe.calc.form', ['update' => true]);
		
		$users = array(1, 14, 201, 395);
		if(!AuthHelper::authedUser($users))
			return view('hbe.access_denied');
		
		$nations = DB::table('ve_stats')->get();
		$total_cost = $total_infra = $total_rcost = $total_rinfra = $total_roi = 0;
		$treasure_bonus = 1 + (json_decode($this->getPage('https://politicsandwar.com/api/alliance/id=866'))->treasures * 0.02);
		foreach($nations as $key => $nation){
			if(empty($nation['leader'])){
				$nations[$key]['cost'] = 0;
				$nations[$key]['new_infra'] = 0;
				$cities = array();
				$nation_data = json_decode($this->getPage('https://politicsandwar.com/api/nation/id=' . $nation['nation_id']));
				$avg_infra = 0;
				if(!empty($nation_data->error)){
					DB::table('ve_stats')->where('nation_id', $nation['nation_id'])->delete();
					continue;
				}
				foreach($nation_data->cityids as $city_id){
					$city = json_decode($this->getPage('https://politicsandwar.com/api/city/id=' . $city_id));
					$calc = $this->calc($city->infrastructure, $nation['infra'], $nation['ccep']);
					$nations[$key]['cost'] += $calc['cost'];
					$nations[$key]['new_infra'] += $calc['infra'];
					$cities[] = $city;
					$avg_infra += $city->infrastructure;
				}
				$avg_infra = (round((round($avg_infra) / count($cities)) / 100)) * 100;
				$temp_calc = $this->calc_roi($nations[$key], $nation_data, $cities, $treasure_bonus, $avg_infra < 900 ? 900 : $avg_infra);
				$nations[$key]['name'] = $nation_data->name;
				$nations[$key]['leader'] = $nation_data->leadername;
				$nations[$key]['old_infra'] = $nation_data->totalinfrastructure;
				$nations[$key]['rinfra'] = $temp_calc['infra'];
				$nations[$key]['rcost'] = $temp_calc['cost'];
				$nations[$key]['ravg'] = $temp_calc['avg'];
				$nations[$key]['roi'] = $temp_calc['roi'];
				DB::table('ve_stats')->where('nation_id', $nation['nation_id'])->update($nations[$key]);
			}
			$total_cost += $nations[$key]['cost'];
			$total_infra += $nations[$key]['new_infra'];
			$total_rcost += $nations[$key]['rcost'];
			$total_rinfra += $nations[$key]['rinfra'];
			$total_roi += $nations[$key]['roi'];
		}
		
		return view('hbe.calc.view', ['total_cost' => $total_cost, 'total_infra' => $total_infra, 'total_rcost' => $total_rcost, 'total_rinfra' => $total_rinfra, 'total_roi' => $total_roi, 'nations' => $nations, 'count' => 0]);
	}
	
	public function warchestCheck(){
		if(!AuthHelper::canAccess('warchest_calc'))
			return view('hbe.access_denied');
		
		return view('hbe.calc.form', ['warchest' => true]);
	}
	
	public function warchestCheckSubmit(Request $request){
		if(!AuthHelper::canAccess('warchest_calc'))
			return view('hbe.access_denied');
		
		$this->validate($request, [
            'id' => 'required|integer|min:1'
        ]);
		
		$id = $request->input('id');
		$nation_data = json_decode($this->getPage('https://politicsandwar.com/api/nation/id=' . $id));
		$tries = 5;
		while(empty($nation_data) && $tries-- > 0)
			$nation_data = json_decode($this->getPage('https://politicsandwar.com/api/nation/id=' . $id));
		if($tries < 0)
			return view('hbe.error_message', ['safe' => true, 'message' => 'Unable to pull nation for nation_id = {' . $id . '}']);

		$cities = array();
		foreach($nation_data->cityids as $city_id){
			$city_data = json_decode($this->getPage('https://politicsandwar.com/api/city/id=' . $city_id));
			$tries = 5;
			while(empty($city_data) && $tries-- > 0){
				$city_data = json_decode($this->getPage('https://politicsandwar.com/api/city/id=' . $city_id));
			}
			if($tries < 0)
				return view('hbe.error_message', ['safe' => true, 'message' => 'Unable to pull city for city_id = {' . $city_id . '}']);
			
			$cities[] = $city_data;
		}
		
		return view('hbe.calc.warchest', $this->calculateWarchest($nation_data, $cities));
	}
	
	public function aaWarchestCheck(){
		if(!AuthHelper::canAccess('aa_warchest_calc'))
			return view('hbe.access_denied');
		
		$wc_data = array(
			'with_noob' => array(
				'food' => 0.0,
				'uranium' => 0.0,
				'gas' => 0.0,
				'mun' => 0.0,
				'steel' => 0.0,
				'aluminum' => 0.0,
				'cash' => 0.0,
			),
			'food' => 0.0,
			'uranium' => 0.0,
			'gas' => 0.0,
			'mun' => 0.0,
			'steel' => 0.0,
			'aluminum' => 0.0,
			'cash' => 0.0
		);
		$mem_warchests = collect(DB::table('warchests')->get())->keyBy('id')->all();
		$mems = $this->getAllMemberIDs();
		set_time_limit(240);
		foreach($mems as $memID){
			$nation_data = json_decode($this->getPage('https://politicsandwar.com/api/nation/id=' . $memID));
			$tries = 5;
			while(empty($nation_data) && $tries-- > 0){
				$nation_data = json_decode($this->getPage('https://politicsandwar.com/api/nation/id=' . $memID));
			}
			if($tries < 0)
				return view('hbe.error_message', ['safe' => true, 'message' => 'Unable to pull nation for nation_id = {' . $memID . '}']);

			if((!empty($nation_data->error) && stristr($nation_data->error, 'nation doesn\'t exist')) || $nation_data->allianceid != $this->alliance_id)
				continue;

			$cities = array();
			foreach($nation_data->cityids as $city_id){
				$city_data = json_decode($this->getPage('https://politicsandwar.com/api/city/id=' . $city_id));
				$tries = 5;
				while(empty($city_data) && $tries-- > 0){
					$city_data = json_decode($this->getPage('https://politicsandwar.com/api/city/id=' . $city_id));
				}
				if($tries < 0)
					return view('hbe.error_message', ['safe' => true, 'message' => 'Unable to pull city for city_id = {' . $city_id . '}']);
				
				$cities[] = $city_data;
			}
			
			$wc = $this->calculateWarchest($nation_data, $cities);
			if(!$wc['is_noob']){
				$wc_data['food'] += ($wc['food'] - $mem_warchests[$memID]['food']);
				$wc_data['uranium'] += ($wc['uranium'] - $mem_warchests[$memID]['uranium']);
				$wc_data['gas'] += ($wc['gas_mun'] - $mem_warchests[$memID]['gasoline']);
				$wc_data['mun'] += ($wc['gas_mun'] - $mem_warchests[$memID]['munitions']);
				$wc_data['steel'] += ($wc['steel'] - $mem_warchests[$memID]['steel']);
				$wc_data['aluminum'] += ($wc['aluminum'] - $mem_warchests[$memID]['aluminum']);
				$wc_data['cash'] += ($wc['cash'] - $mem_warchests[$memID]['cash']);
			}
			$wc_data['with_noob']['food'] += ($wc['food'] - $mem_warchests[$memID]['food']);
			$wc_data['with_noob']['uranium'] += ($wc['uranium'] - $mem_warchests[$memID]['uranium']);
			$wc_data['with_noob']['gas'] += ($wc['gas_mun'] - $mem_warchests[$memID]['gasoline']);
			$wc_data['with_noob']['mun'] += ($wc['gas_mun'] - $mem_warchests[$memID]['munitions']);
			$wc_data['with_noob']['steel'] += ($wc['steel'] - $mem_warchests[$memID]['steel']);
			$wc_data['with_noob']['aluminum'] += ($wc['aluminum'] - $mem_warchests[$memID]['aluminum']);
			$wc_data['with_noob']['cash'] += ($wc['cash'] - $mem_warchests[$memID]['cash']);
		}
		
		return view('hbe.calc.aa_warchest', $wc_data);
	}

	public function infraCheck(){
		if(!AuthHelper::canAccess('infra_calc'))
			return view('hbe.access_denied');
		
		return view('hbe.calc.form', ['infra' => true]);
	}
	
	public function infraCheckSubmit(Request $request){
		if(!AuthHelper::canAccess('infra_calc'))
			return view('hbe.access_denied');
		
		$validator = Validator::make($request->all(), [
			'id' => 'required|integer|min:1',
			'infra' => 'required|integer|min:10'
		], []);
		if ($validator->fails())
            return redirect('calc/infra')->withErrors($validator)->withInput();
		
		$id = $request->input('id');
		
		$infra = $request->input('infra');
		$urbanization = $request->has('urb') ? 1 : 0;
		$ccep = $request->has('ccep') ? 1 : 0;
		
		$cost = 0;
		$infra_buy = 0;
		$sum_infra = $avg_infra = 0;
		$cities = array();
		if($request->has('cities')){
			$cities = $request->input('cities');
			foreach($cities as $city){
				$calc = $this->calc($city, $infra, $ccep, $urbanization);
				$cost += $calc['cost'];
				$infra_buy += $calc['infra'];
				$sum_infra += $city;
			}
		}else{
			$nation_data = json_decode($this->getPage('https://politicsandwar.com/api/nation/id=' . $id));
			$tries = 5;
			while(empty($nation_data) && $tries-- > 0)
				$nation_data = json_decode($this->getPage('https://politicsandwar.com/api/nation/id=' . $id));
			if($tries < 0)
				return view('hbe.error_message', ['safe' => true, 'message' => 'Unable to pull nation for nation_id = {' . $id . '}']);

			foreach($nation_data->cityids as $city_id){
				$city = json_decode($this->getPage('https://politicsandwar.com/api/city/id=' . $city_id));
				$tries = 5;
				while(empty($city) && $tries-- > 0)
					$city = json_decode($this->getPage('https://politicsandwar.com/api/city/id=' . $city_id));
				if($tries < 0)
					return view('hbe.error_message', ['safe' => true, 'message' => 'Unable to pull city for city_id = {' . $city_id . '}']);

				$calc = $this->calc($city->infrastructure, $infra, $ccep, $urbanization);
				$cost += $calc['cost'];
				$infra_buy += $calc['infra'];
				$cities[] = $city->infrastructure;
				$sum_infra += $city->infrastructure;
			}
		}
		$avg_infra = $sum_infra / count($cities);
		return view('hbe.calc.calc', ['cities' => $cities, 'cost' => $cost, 'infra_buy' => $infra_buy, 'id' => $id, 'infra' => $infra, 'ccep' => $ccep, 'urb' => $urbanization, 'total_infra' => $sum_infra, 'avg_infra' => $avg_infra]);
	}
	
	public function landCheck(){
		if(!AuthHelper::canAccess('land_calc'))
			return view('hbe.access_denied');

		return view('hbe.calc.form', ['land' => true]);
	}
	
	public function landCheckSubmit(Request $request){
		if(!AuthHelper::canAccess('land_calc'))
			return view('hbe.access_denied');

		$validator = Validator::make($request->all(), [
			'id' => 'required|integer|min:1',
			'land' => 'required|integer|min:10'
		], []);
		if($validator->fails())
			return redirect('calc/land')->withErrors($validator)->withInput();
		
		$id = $request->input('id');
		$land = $request->input('land');
		
		$cost = 0;
		$land_buy = 0;
		$sum_land = $avg_land = 0;
		$cities = array();
		if($request->has('cities')){
			$cities = $request->input('cities');
			foreach($cities as $city){
				$calc = $this->calcLand($city, $land);
				$cost += $calc['cost'];
				$land_buy += $calc['land'];
				$sum_land += $city;
			}
		}else{
			$nation_data = json_decode($this->getPage('https://politicsandwar.com/api/nation/id=' . $id));
			$tries = 5;
			while(empty($nation_data) && $tries-- > 0)
				$nation_data = json_decode($this->getPage('https://politicsandwar.com/api/nation/id=' . $id));
			if($tries < 0)
				return view('hbe.error_message', ['safe' => true, 'message' => 'Unable to pull nation for nation_id = {' . $id . '}']);
			
			foreach($nation_data->cityids as $city_id){
				$city = json_decode($this->getPage('https://politicsandwar.com/api/city/id=' . $city_id));
				$tries = 5;
				while(empty($city) && $tries-- > 0)
					$city = json_decode($this->getPage('https://politicsandwar.com/api/city/id=' . $city_id));
				if($tries < 0)
					return view('hbe.error_message', ['safe' => true, 'message' => 'Unable to pull city for city_id = {' . $city_id . '}']);
				
				$calc = $this->calcLand($city->land, $land);
				$cost += $calc['cost'];
				$land_buy += $calc['land'];
				$cities[] = $city->land;
				$sum_land += $city->land;
			}
		}
		$avg_land = $sum_land / count($cities);
		return view('hbe.calc.calc', ['land_form' => true, 'cities' => $cities, 'cost' => $cost, 'land_buy' => $land_buy, 'id' => $id, 'land' => $land, 'total_land' => $sum_land, 'avg_land' => $avg_land]);
	}
	
	public function graph(){
		$infra = 1100;
		$start_infra = 1000;
		$end_infra = 2000;
		$costs = array();
		$income = array();
		$infras = array();
		$city = json_decode($this->getPage('https://politicsandwar.com/api/city/id=60737'));
		while($infra <= $end_infra){
			$cost = $this->calc($start_infra, $infra, false);
			$costs[] = $cost['cost'];
			$income[] = 7.5 * $this->calc_pop($city, true, $infra);
			$infras[] = $infra;
			$infra += 100;
		}
		return view('hbe.calc.graph', ['graphs' => true, 'infras' => $infras, 'costs' => $costs, 'income' => $income]);
	}
	
	private function calc($current_infra, $max_infra, $ccep, $urbanization = true){
		$cost = 0;
		$total_infra = 0;
		$discount = $urbanization ? 0.95 : 1;
		if($ccep)
			$discount -= 0.05;
		
		while($current_infra < $max_infra){
			if($current_infra + 100 > $max_infra){
				$infra = $max_infra - $current_infra;
				$total_infra += $infra;
				$cost += $discount * ($infra * (300 + (pow(abs($current_infra - 10), 2.2) / 710)));
				break;
			}
			$cost += $discount * (100 * (300 + (pow(abs($current_infra - 10), 2.2) / 710)));
			$current_infra += 100;
			$total_infra += 100;
		}
		return array('cost' => $cost, 'infra' => $total_infra);
	}
	
	private function calcLand($current_land, $max_land){
		$cost = 0;
		$total_land = 0;
		while($current_land < $max_land){
			if($current_land + 500 > $max_land){
				$land = $max_land - $current_land;
				$total_land += $land;
				$cost += $land * ((0.002 * (pow(abs($current_land - 20), 2))) + 50);
				break;
			}
			$cost += 500 * ((0.002 * (pow(abs($current_land - 20), 2))) + 50);
			$current_land += 500;
			$total_land += 500;
		}
		return array('cost' => $cost, 'land' => $total_land);
	}
	
	private function calc_roi($nation, $nation_data, $cities, $treasure_bonus, $avg_infra){
		$roi = array();
		$test_infra = $avg_infra;
		while($test_infra <= $nation['infra'] + 700){
			$test_infra += 100;
			$total_cost = 0;
			$total_infra = 0;
			$gross_income = 0;
			$expenses = 0;
			foreach($cities as $city){
				$data = $this->calc($city->infrastructure, $test_infra, $nation['ccep']);
				$total_cost += $data['cost'];
				$total_infra += $data['infra'];
				$gross_income += (2.5 + ($city->commerce / 20)) * $this->calc_pop($city, $nation_data, $test_infra);
				$expenses += $this->getExpense($nation_data, $city);
			}
			$temp_gross = $gross_income * 0.29;
			$gross_income = $temp_gross * (1 + ($nation_data->color == 'lime' ? 0.05 : 0)) * ($nation_data->color == 'lime' ? $treasure_bonus : 1);
			$gross_income += ($nation_data->domestic_policy == 'Open Markets' ? 0.01 : 0) * $temp_gross;
			$net_income = $gross_income - $expenses;
			$troi = (($net_income * 30) - $total_cost);
			if(empty($roi))
				$roi = array('roi' => $troi, 'cost' => $total_cost, 'infra' => $total_infra, 'avg' => $test_infra);
			else if($roi['roi'] < $troi)
				$roi = array('roi' => $troi, 'cost' => $total_cost, 'infra' => $total_infra, 'avg' => $test_infra);
		}
		return $roi;
	}
	
	private function calc_pop($city, $nation_data, $infra){
		$base_pop = ($infra * 100);
		$base_dense = $base_pop / $city->land;
		$disease = (((pow($base_dense, 2) * 0.01) - 25) / 100) + ($base_pop / 100000) + (($city->pollution + $city->nuclearpollution) * 0.05) - ($city->imp_hospital * 2.5);
		$disease_lost = (($disease / 10) * ($base_pop / 10));
		$crime = ((pow(103 - $city->commerce, 2) + $base_pop) / 111111) - (2.5 * $city->imp_policestation);
		$crime_lost = max($crime / 100, 0);
		return ($base_pop - $disease_lost - $crime_lost) * (1 + ($city->age / 3000));
	}
	
	private function getExpense($nation_data, $city){
		$costs = array(
			'imp_coalpower' => 1200,
			'imp_oilpower' => 1800,
			'imp_nuclearpower' => 10500,
			'imp_windpower' => 500,
			'imp_coalmine' => 400,
			'imp_oilwell' => 600,
			'imp_ironmine' => 1600,
			'imp_bauxitemine' => 1600,
			'imp_leadmine' => 1500,
			'imp_uramine' => 5000,
			'imp_farm' => 300,
			'imp_gasrefinery' => 4000,
			'imp_steelmill' => 4000,
			'imp_aluminumrefinery' => 2500,
			'imp_munitionsfactory' => 3500,
			'imp_policestation' => 750,
			'imp_hospital' => 1000,
			'imp_recyclingcenter' => 2500,
			'imp_subway' => 3250,
			'imp_supermarket' => 600,
			'imp_bank' => 1800,
			'imp_mall' => 5400,
			'imp_stadium' => 12150,
			'imp_barracks' => 0,
			'imp_factory' => 0,
			'imp_airforcebase' => 0,
			'imp_drydock' => 0,
		);
		$military = array(
			'soldiers' => 1.25,
			'tanks' => 50,
			'aircraft' => 500,
			'ships' => 3750,
			//'spies' => 2400,
			'missiles' => 21000,
			'nukes' => 35000,
		);
		$expenses = 0;
		$military_expenses = 0;
		foreach($costs as $imp => $cost)
			$expenses += (property_exists($city, $imp) ? $city->$imp : 0) * $cost;
		foreach($military as $key => $cost)
			$military_expenses += $nation_data->$key * $cost;
		
		return ($expenses + ($military_expenses * ($nation_data->domestic_policy == 'Imperialism' ? 0.95 : 1)));
	}
}
