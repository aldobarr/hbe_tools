<?php

namespace App\Http\Controllers;

trait HBEFunctions{
	public function hbe_time_format($time){
		if($time < 0)
			return '<strong>Never</strong>';
		$isToday = $time >= strtotime(date('m/d/Y'));
		$todayStr = $isToday ? '<strong>Today</strong> at ' : '';
		$regularStr = $isToday ? '' : 'F d, Y, ';
		return $todayStr . date($regularStr . 'h:i:s A', $time);
	}
	
	public function calculateWarchest($nation, $cities){
		$uranium = 0;
		$current_tanks = $nation->tanks;
		$current_planes = $nation->aircraft;
		$current_ships = $nation->ships;
		foreach($cities as $city){
			$uranium += ceil($city->infrastructure / 1000) * 1.2;
		}
		$cities = $nation->cities;
		
		$food = $cities * 4500;
		$uranium *= 30;
		$gas_mun = 2000 * $cities; // 400 * 5 enemies * cities
		$steel = (($cities * 1250) - $current_tanks) + ($cities * 750) + (($cities * 25 * 15) - ($current_ships * 25)) + ($cities * 375); // 100% of tanks - current amount of tanks + 3 days worth of rebuying full tanks. (50 per factory * 5 factories per city * 3 days = 750)
																																		  // Also 100% of ships - current ships + 5 days worth of rebuying full ships. (1 per drydock * 3 drydocks per city * 25 steel per ship * 5 days = 375)
		$aluminum = (($cities * 270) - $current_planes) + ($cities * 630); // (90 * 3 = 270) 100% of planes - current amount of planes + 14 days of rebuying max planes. (3 per factory * 3 aluminum per plane * 5 factories per city * 14 days = 630)
		$cash = ($cities * 30000) + ((($cities * 1250) - $current_tanks) * 60) + ((($cities * 90) - $current_planes) * 4000) + ((($cities * 15) - $current_ships) * 50000) + ($cities * 500000); // Amount to buy max soldiers + max tanks + max planes + max_ships + moving improvements around and some spending money.
		
		return array('nation' => $nation->prename . ' ' . $nation->name, 'food' => $food, 'uranium' => $uranium, 'gas_mun' => $gas_mun, 'steel' => $steel, 'aluminum' => $aluminum, 'cash' => $cash, 'is_noob' => ($cities <= 6));
	}
	
	public function remove_outliers($dataset, $magnitude = 1) {
		$fn = function($x, $mean) {
			return pow($x - $mean, 2);
		};
		$count = count($dataset);
		$mean = array_sum($dataset) / $count; // Calculate the mean
		$deviation = sqrt(array_sum(array_map($fn, $dataset, array_fill(0, $count, $mean))) / $count) * $magnitude; // Calculate standard deviation and times by magnitude
		
		return array_filter($dataset, function($x) use ($mean, $deviation) { return ($x <= $mean + $deviation && $x >= $mean - $deviation); }); // Return filtered array of values that lie within $mean +- $deviation.
	}

	public function calculateWarchestLess($tanks, $planes, $ships, $cities){
		$uranium = 0;
		$current_tanks = $tanks;
		$current_planes = $planes;
		$current_ships = $ships;
		
		$uranium = $cities * 72;
		$food = $cities * 4500;
		$gas_mun = 2000 * $cities; // 400 * 5 enemies * cities
		$steel = (($cities * 1250) - $current_tanks) + ($cities * 750) + (($cities * 25 * 15) - ($current_ships * 25)) + ($cities * 375); // 100% of tanks - current amount of tanks + 3 days worth of rebuying full tanks. (50 per factory * 5 factories per city * 3 days = 750)
																																		  // Also 100% of ships - current ships + 5 days worth of rebuying full ships. (1 per drydock * 3 drydocks per city * 25 steel per ship * 5 days = 375)
		$aluminum = (($cities * 270) - $current_planes) + ($cities * 630); // (90 * 3 = 270) 100% of planes - current amount of planes + 14 days of rebuying max planes. (3 per factory * 3 aluminum per plane * 5 factories per city * 14 days = 630)
		$cash = ($cities * 30000) + ((($cities * 1250) - $current_tanks) * 60) + ((($cities * 90) - $current_planes) * 4000) + ((($cities * 15) - $current_ships) * 50000) + ($cities * 500000); // Amount to buy max soldiers + max tanks + max planes + max_ships + moving improvements around and some spending money.
		
		return array('uranium' => $uranium, 'food' => $food, 'gasoline' => $gas_mun, 'munitions' => $gas_mun, 'steel' => $steel, 'aluminum' => $aluminum, 'cash' => $cash);
	}
	
	public function getForumName($forum_id, $alternate){
		global $smcFunc;
		
		if(!is_int($forum_id) || $forum_id < 1)
			return $alternate;
		
		$query = $smcFunc['db_query']('', '
			SELECT real_name
			FROM {db_prefix}members
			WHERE id_member = {int:id}
			LIMIT 1',
			array(
				'id' => $forum_id
			)
		);
		$temp = $smcFunc['db_fetch_assoc']($query);
		$smcFunc['db_free_result']($query);
		if(empty($temp) || empty($temp['real_name']))
			return $alternate;
		return $temp['real_name'];
	}
	
	public function findRecipient($nation_id, $id_only = false){
		global $smcFunc;
		
		$recipient = array('to' => array(), 'bcc' => array());
		$query = $smcFunc['db_query']('', '
			SELECT id_member
			FROM {db_prefix}themes
			WHERE variable = {string:var}
			AND id_theme = 1
			AND value LIKE {string:nation}
			LIMIT 1',
			array(
				'var' => 'cust_nation',
				'nation' => '%' . $nation_id
			)
		);
		$temp = $smcFunc['db_fetch_assoc']($query);
		$smcFunc['db_free_result']($query);
		if(!empty($temp) && !empty($temp['id_member']))
			$recipient['to'][] = $temp['id_member'];
		if($id_only)
			return ((!empty($temp) && !empty($temp['id_member'])) ? $temp['id_member'] : 0);
		
		return $recipient;
	}
	
	public function getAllMemberIDs(){
		global $smcFunc;
		
		$mems = array();
		$query = $smcFunc['db_query']('', '
			SELECT id_member
			FROM {db_prefix}members
			WHERE id_group = {int:brit}
			OR additional_groups LIKE {string:sbrit}',
			array(
				'brit' => 11,
				'sbrit' => '%11%'
			)
		);
		while($row = $smcFunc['db_fetch_assoc']($query))
			$mems[] = $row['id_member'];
		$smcFunc['db_free_result']($query);
		
		$query = $smcFunc['db_query']('', '
			SELECT value
			FROM {db_prefix}themes
			WHERE variable = {string:var}
			AND id_theme = 1
			AND id_member IN ({array_int:mems})',
			array(
				'var' => 'cust_nation',
				'mems' => $mems
			)
		);
		
		$ret = array();
		while($row = $smcFunc['db_fetch_assoc']($query)){
			$val = explode('=', $row['value']);
			if(count($val) != 2)
				continue;
			
			$ret[] = $val[1];
		}
		$smcFunc['db_free_result']($query);
		
		return $ret;
	}
	
	private function checkTurn($str){
		$get = stristr($str, 'Next turn in');
		if(empty($get))
			return false;
		
		$test = explode(':', $get);
		return substr($test[0], -1) == '0';
	}
}
