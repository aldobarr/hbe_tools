<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Cache;
use DB;

class Temp extends Command
{
	use CurlFunctions;
	
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'schedule:temp';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Temp task.';
	
	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		Cache::put(md5($this->signature), true, 20);
		DB::table('ve_stats')->update(['leader' => '']);
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
		Cache::forget(md5($this->signature));
	}
	
	private function calc($current_infra, $max_infra, $ccep){
		$cost = 0;
		$total_infra = 0;
		$discount = $ccep ? 0.9 : 0.95;
		while($current_infra < $max_infra){
			if($current_infra + 100 > $max_infra){
				$infra = $max_infra - $current_infra;
				$total_infra += $infra;
				$cost += $discount * ($infra * (300 + (0.01 * pow(($current_infra - 10), 1.95))));
				break;
			}
			$cost += $discount * (100 * (300 + (0.01 * pow(($current_infra - 10), 1.95))));
			$current_infra += 100;
			$total_infra += 100;
		}
		return array('cost' => $cost, 'infra' => $total_infra);
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
			'imp_oilrefinery' => 4000,
			'imp_steelmill' => 4000,
			'imp_aluminumrefinery' => 2500,
			'imp_munitionsfactory' => 3500,
			'imp_policestation' => 750,
			'imp_hospital' => 1000,
			'imp_recyclingcenter' => 2500,
			'imp_subway' => 3250,
			'imp_supermarket' => 600,
			'imp_bank' => 1800,
			'imp_shoppingmall' => 5400,
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
