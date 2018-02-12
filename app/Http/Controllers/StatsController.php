<?php

namespace App\Http\Controllers;

use Auth;
use AuthHelper;
use DB;
use Cache;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StatsController extends Controller
{
	use HBEFunctions;
	use \App\Console\Commands\CurlFunctions;
	
	public function download(){
		if(!AuthHelper::canAccess('view_stats'))
			return view('hbe.access_denied');
		
		$filename = 'export.csv';
		$out = array();
		$first = true;
		$raw_data = DB::table('nation_stats')->get();
		foreach($raw_data as $row){
			$cols = array();
			$add = array();
			foreach($row as $col => $val){
				if($first)
					$cols[] = '"' . str_replace('"', '\\"', $col) . '"';
				$add[] = '"' . str_replace('"', '\\"', $val) . '"';
			}
			if(!empty($cols))
				$out[] = implode(',', $cols);
			$out[] = implode(',', $add);
			$first = false;
		}
		$out = implode(PHP_EOL, $out);
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Length: " . strlen($out));
		header("Content-type: text/x-csv");
		header("Content-Disposition: attachment; filename=$filename");
		echo $out;
		exit;
	}
	
	public function stats($page = 1, $sort = 'score', $asc = 'desc', $search = ''){
		if(!AuthHelper::canAccess('view_stats'))
			return view('hbe.access_denied');
		
		if($page < 0)
			return redirect()->action('StatsController@stats', [1, $sort, $asc, $search]);
		$page--;
		$num_rows = 0;
		$search = urldecode($search);
		$range_min = $range_max = -1;
		$search_items = explode(':', $search);
		$col_search = explode('|', $search_items[0]);
		if($search_items[0] == 'range'){
			$range = (double)$search_items[1];
			$range_min = $range / 1.75;
			$range_max = $range / 0.75;
		}
		
		if(empty($search) || count($search_items) != 2)
			$num_rows = DB::table('nation_stats')->count();
		else if($range_max == -1)
			$num_rows = DB::table('nation_stats')->where($col_search[0], (empty($col_search[1]) ? 'like' : ($col_search[1] == 'gt' ? '>' : '<')), empty($col_search[1]) ? ('%' . $search_items[1] . '%') : $search_items[1])->count();
		else
			$num_rows = DB::table('nation_stats')->whereBetween('score', [$range_min, $range_max])->count();
		
		$max_page = ceil($num_rows / 25);
		if(($page + 1) > $max_page)
			return redirect()->action('StatsController@stats', [$max_page, $sort, $asc, $search]);
		
		$wc_sort = $sort == 'wc';
		$food_ur_sort = $sort == 'food' || $sort == 'uranium';
		$nations = array();
		$fix = $this->fixColumns();
		if($wc_sort || $food_ur_sort){
			if(empty($search) || count($search_items) != 2)
				$nations = DB::table('nation_stats')->orderBy($this->getColumn($sort, 'score'), $asc == 'desc' ? 'desc' : 'asc')->leftJoin('warchests', 'nation_stats.nation_id', '=', 'warchests.id')->get($fix);
			else if($range_max == -1)
				$nations = DB::table('nation_stats')->where($col_search[0], (empty($col_search[1]) ? 'like' : ($col_search[1] == 'gt' ? '>' : '<')), empty($col_search[1]) ? ('%' . $search_items[1] . '%') : $search_items[1])->leftJoin('warchests', 'nation_stats.nation_id', '=', 'warchests.id')->orderBy($this->getColumn($sort, 'ruler'), $asc == 'desc' ? 'desc' : 'asc')->get($fix);
			else
				$nations = DB::table('nation_stats')->whereBetween('score', [$range_min, $range_max])->orderBy($this->getColumn($sort, 'ruler'), $asc == 'desc' ? 'desc' : 'asc')->leftJoin('warchests', 'nation_stats.nation_id', '=', 'warchests.id')->get($fix);
		}else{
			if(empty($search) || count($search_items) != 2)
				$nations = DB::table('nation_stats')->orderBy($this->getColumn($sort, 'score'), $asc == 'desc' ? 'desc' : 'asc')->leftJoin('warchests', 'nation_stats.nation_id', '=', 'warchests.id')->skip($page * 25)->take(25)->get($fix);
			else if($range_max == -1)
				$nations = DB::table('nation_stats')->where($col_search[0], (empty($col_search[1]) ? 'like' : ($col_search[1] == 'gt' ? '>' : '<')), empty($col_search[1]) ? ('%' . $search_items[1] . '%') : $search_items[1])->leftJoin('warchests', 'nation_stats.nation_id', '=', 'warchests.id')->orderBy($this->getColumn($sort, 'ruler'), $asc == 'desc' ? 'desc' : 'asc')->skip($page * 50)->take(50)->get($fix);
			else
				$nations = DB::table('nation_stats')->whereBetween('score', [$range_min, $range_max])->orderBy($this->getColumn($sort, 'ruler'), $asc == 'desc' ? 'desc' : 'asc')->leftJoin('warchests', 'nation_stats.nation_id', '=', 'warchests.id')->skip($page * 50)->take(50)->get($fix);
		}
		$fixed_nations = array();
		foreach($nations as $nation){
			$nation['inactive'] = round((((int)$nation['inactive']) / 1440), 2);
			if(!empty($nation['id']))
				$nation['time'] = $this->hbe_time_format($nation['time']);
			$nation['avg_infra'] = number_format($nation['infra'] / $nation['cities']);
			$nation['warchest_data'] = $this->handleWarchestData($nation);
			$fixed_nations[$nation['nation_id']] = $nation;
		}
		if($wc_sort){
			if($asc == 'desc'){
				uasort($fixed_nations, function($a, $b){
					return $b['warchest_data']['overall'] - $a['warchest_data']['overall'];
				});
			}else{
				uasort($fixed_nations, function($a, $b){
					return $a['warchest_data']['overall'] - $b['warchest_data']['overall'];
				});
			}
			$f2 = array();
			$count = 0;
			$start = $page * 25;
			$end = ($page + 1) * 25;
			foreach($fixed_nations as $key => $nation){
				if($count >= $start && $count < $end)
					$f2[$key] = $nation;
				else if($count >= $end)
					break;
				$count++;
			}
			$fixed_nations = $f2;
		}else if($food_ur_sort){
			if($asc == 'desc'){
				if($sort == 'food'){
					uasort($fixed_nations, function($a, $b){
						return $b['food'] - $a['food'];
					});
				}else{
					uasort($fixed_nations, function($a, $b){
						return $b['uranium'] - $a['uranium'];
					});
				}
			}else{
				if($sort == 'food'){
					uasort($fixed_nations, function($a, $b){
						return $a['food'] - $b['food'];
					});
				}else{
					uasort($fixed_nations, function($a, $b){
						return $a['uranium'] - $b['uranium'];
					});
				}
			}
			$f2 = array();
			$count = 0;
			$start = $page * 25;
			$end = ($page + 1) * 25;
			foreach($fixed_nations as $key => $nation){
				if($count >= $start && $count < $end)
					$f2[$key] = $nation;
				else if($count >= $end)
					break;
				$count++;
			}
			$fixed_nations = $f2;
		}
		
		$last_update = $this->hbe_time_format(DB::table('cron_last_run')->where('id', 'nation_stats')->first()['time']);
		
		return view('hbe.nation_stats', ['pagination' => true, 'nations' => $fixed_nations, 'max_page' => $max_page, 'page' => $page + 1, 'last_update' => $last_update, 'sort' => $sort, 'asc' => $asc, 'search' => $search]);
	}
	
	private function handleWarchestData($nation){
		$warchest = array('overall' => 0);
		$warchest_goals = $this->calculateWarchestLess($nation['tanks'], $nation['planes'], $nation['ships'], $nation['cities']);
		foreach($warchest_goals as $key => $value){
			$warchest[$key] = round(100 * ($nation[$key] / $value));
			if($warchest[$key] > 100)
				$warchest[$key] = 100;
			$warchest['overall'] += $warchest[$key];
		}
		$warchest['overall'] = round($warchest['overall'] / count($warchest_goals));
		if($warchest['overall'] > 100)
			$warchest['overall'] = 100;
		return $warchest;
	}
	
	public function taxes($fixed = ''){
		if(!AuthHelper::canAccess('view_tax_income'))
			return view('hbe.access_denied');
		
		$resource_totals = array(
			'cash' => array('turn' => 0, 'day' => 0, 'total' => 0, 'temp' => 0, 'temp_month' => 0),
			'food' => array('turn' => 0, 'day' => 0, 'total' => 0, 'temp' => 0, 'temp_month' => 0),
			'coal' => array('turn' => 0, 'day' => 0, 'total' => 0, 'temp' => 0, 'temp_month' => 0),
			'oil' => array('turn' => 0, 'day' => 0, 'total' => 0, 'temp' => 0, 'temp_month' => 0),
			'uranium' => array('turn' => 0, 'day' => 0, 'total' => 0, 'temp' => 0, 'temp_month' => 0),
			'lead' => array('turn' => 0, 'day' => 0, 'total' => 0, 'temp' => 0, 'temp_month' => 0),
			'iron' => array('turn' => 0, 'day' => 0, 'total' => 0, 'temp' => 0, 'temp_month' => 0),
			'bauxite' => array('turn' => 0, 'day' => 0, 'total' => 0, 'temp' => 0, 'temp_month' => 0),
			'gasoline' => array('turn' => 0, 'day' => 0, 'total' => 0, 'temp' => 0, 'temp_month' => 0),
			'munitions' => array('turn' => 0, 'day' => 0, 'total' => 0, 'temp' => 0, 'temp_month' => 0),
			'steel' => array('turn' => 0, 'day' => 0, 'total' => 0, 'temp' => 0, 'temp_month' => 0),
			'aluminum' => array('turn' => 0, 'day' => 0, 'total' => 0, 'temp' => 0, 'temp_month' => 0),
			'increment_labels' => array(
				'week' => array(),
				'month' => array(),
			),
			'increment_totals' => array(
				'week' => array(
					'cash' => array(array(), array(75, 192, 192)),
					'food' => array(array(), array(255, 51, 0)),
					'coal' => array(array(), array(0, 0, 0,)),
					'oil' => array(array(), array(180, 180, 180)),
					'uranium' => array(array(), array(161, 239, 207)),
					'lead' => array(array(), array(164, 189, 213)),
					'iron' => array(array(), array(67, 75, 77)),
					'bauxite' => array(array(), array(183, 65, 14)),
					'gasoline' => array(array(), array(255, 255, 153)),
					'munitions' => array(array(), array(128, 0, 128)),
					'steel' => array(array(), array(67, 70, 75)),
					'aluminum' => array(array(), array(0, 0, 255)),
				),
				'month' => array(
					'cash' => array(array(), array(75, 192, 192)),
					'food' => array(array(), array(255, 51, 0)),
					'coal' => array(array(), array(0, 0, 0,)),
					'oil' => array(array(), array(180, 180, 180)),
					'uranium' => array(array(), array(161, 239, 207)),
					'lead' => array(array(), array(164, 189, 213)),
					'iron' => array(array(), array(67, 75, 77)),
					'bauxite' => array(array(), array(183, 65, 14)),
					'gasoline' => array(array(), array(255, 255, 153)),
					'munitions' => array(array(), array(128, 0, 128)),
					'steel' => array(array(), array(67, 70, 75)),
					'aluminum' => array(array(), array(0, 0, 255)),
				)
			),
		);
		
		$select = 'SUM(cash) as cash, SUM(food) as food, SUM(coal) as coal, SUM(oil) as oil, SUM(uranium) as uranium, SUM(lead) as lead, SUM(iron) as iron, SUM(bauxite) as bauxite, SUM(gasoline) as gasoline, SUM(munitions) as munitions, SUM(steel) as steel, SUM(aluminum) as aluminum';
		$totals = DB::table('taxes')->select(DB::raw($select))->first();
	
		$resource_totals['cash']['total'] = $totals['cash'];
		$resource_totals['food']['total'] = $totals['food'];
		$resource_totals['coal']['total'] = $totals['coal'];
		$resource_totals['oil']['total'] = $totals['oil'];
		$resource_totals['uranium']['total'] = $totals['uranium'];
		$resource_totals['lead']['total'] = $totals['lead'];
		$resource_totals['iron']['total'] = $totals['iron'];
		$resource_totals['bauxite']['total'] = $totals['bauxite'];
		$resource_totals['gasoline']['total'] = $totals['gasoline'];
		$resource_totals['munitions']['total'] = $totals['munitions'];
		$resource_totals['steel']['total'] = $totals['steel'];
		$resource_totals['aluminum']['total'] = $totals['aluminum'];
	
		$count = 0;
		$tcount = 0;
		$ttcount = 0;
		$record_week = true;
		$record_month = true;
		$last_time = -1;
		$today = $time_count = $month_count = mktime(0, 0, 0, date('n'), date('j'), date('Y'));
		$full_month = mktime(0, 0, 0, date('n') - 1 == 0 ? 12 : date('n') - 1, date('j'), date('n') - 1 == 0 ? date('Y') - 1 : date('Y')) - 86400;
		$resources = DB::table('taxes')->where('time', '>=', $full_month)->orderBy('time', 'desc')->get();
		foreach($resources as $record){
			if($last_time != $record['time'] && $last_time > -1)
				$count++;
			if($record_week){
				if($record['time'] < $time_count){
					$var = date('l', $last_time);
					$resource_totals['increment_labels']['week'][] = $var;
					$resource_totals['increment_totals']['week']['cash'][0][] = $resource_totals['cash']['temp'];
					$resource_totals['increment_totals']['week']['food'][0][] = $resource_totals['food']['temp'];
					$resource_totals['increment_totals']['week']['coal'][0][] = $resource_totals['coal']['temp'];
					$resource_totals['increment_totals']['week']['oil'][0][] = $resource_totals['oil']['temp'];
					$resource_totals['increment_totals']['week']['uranium'][0][] = $resource_totals['uranium']['temp'];
					$resource_totals['increment_totals']['week']['lead'][0][] = $resource_totals['lead']['temp'];
					$resource_totals['increment_totals']['week']['iron'][0][] = $resource_totals['iron']['temp'];
					$resource_totals['increment_totals']['week']['bauxite'][0][] = $resource_totals['bauxite']['temp'];
					$resource_totals['increment_totals']['week']['gasoline'][0][] = $resource_totals['gasoline']['temp'];
					$resource_totals['increment_totals']['week']['munitions'][0][] = $resource_totals['munitions']['temp'];
					$resource_totals['increment_totals']['week']['steel'][0][] = $resource_totals['steel']['temp'];
					$resource_totals['increment_totals']['week']['aluminum'][0][] = $resource_totals['aluminum']['temp'];
					
					$resource_totals['cash']['temp'] = 0;
					$resource_totals['food']['temp'] = 0;
					$resource_totals['coal']['temp'] = 0;
					$resource_totals['oil']['temp'] = 0;
					$resource_totals['uranium']['temp'] = 0;
					$resource_totals['lead']['temp'] = 0;
					$resource_totals['iron']['temp'] = 0;
					$resource_totals['bauxite']['temp'] = 0;
					$resource_totals['gasoline']['temp'] = 0;
					$resource_totals['munitions']['temp'] = 0;
					$resource_totals['steel']['temp'] = 0;
					$resource_totals['aluminum']['temp'] = 0;
					$time_count -= 86400;
					if($time_count < ($today - 604800))
						$record_week = false;
				}
			}
			if($record_month){
				if($record['time'] < $month_count){
					$var = date('F j', $last_time);
					$resource_totals['increment_labels']['month'][] = $var;
					$resource_totals['increment_totals']['month']['cash'][0][] = $resource_totals['cash']['temp_month'];
					$resource_totals['increment_totals']['month']['food'][0][] = $resource_totals['food']['temp_month'];
					$resource_totals['increment_totals']['month']['coal'][0][] = $resource_totals['coal']['temp_month'];
					$resource_totals['increment_totals']['month']['oil'][0][] = $resource_totals['oil']['temp_month'];
					$resource_totals['increment_totals']['month']['uranium'][0][] = $resource_totals['uranium']['temp_month'];
					$resource_totals['increment_totals']['month']['lead'][0][] = $resource_totals['lead']['temp_month'];
					$resource_totals['increment_totals']['month']['iron'][0][] = $resource_totals['iron']['temp_month'];
					$resource_totals['increment_totals']['month']['bauxite'][0][] = $resource_totals['bauxite']['temp_month'];
					$resource_totals['increment_totals']['month']['gasoline'][0][] = $resource_totals['gasoline']['temp_month'];
					$resource_totals['increment_totals']['month']['munitions'][0][] = $resource_totals['munitions']['temp_month'];
					$resource_totals['increment_totals']['month']['steel'][0][] = $resource_totals['steel']['temp_month'];
					$resource_totals['increment_totals']['month']['aluminum'][0][] = $resource_totals['aluminum']['temp_month'];
					
					$resource_totals['cash']['temp_month'] = 0;
					$resource_totals['food']['temp_month'] = 0;
					$resource_totals['coal']['temp_month'] = 0;
					$resource_totals['oil']['temp_month'] = 0;
					$resource_totals['uranium']['temp_month'] = 0;
					$resource_totals['lead']['temp_month'] = 0;
					$resource_totals['iron']['temp_month'] = 0;
					$resource_totals['bauxite']['temp_month'] = 0;
					$resource_totals['gasoline']['temp_month'] = 0;
					$resource_totals['munitions']['temp_month'] = 0;
					$resource_totals['steel']['temp_month'] = 0;
					$resource_totals['aluminum']['temp_month'] = 0;
					$month_count -= 86400;
					if($month_count < ($today - 2764800))
						$record_month = false;
				}
			}
			if($count < 12){
				if($count == 0){
					$resource_totals['cash']['turn'] += $record['cash'];
					$resource_totals['food']['turn'] += $record['food'];
					$resource_totals['coal']['turn'] += $record['coal'];
					$resource_totals['oil']['turn'] += $record['oil'];
					$resource_totals['uranium']['turn'] += $record['uranium'];
					$resource_totals['lead']['turn'] += $record['lead'];
					$resource_totals['iron']['turn'] += $record['iron'];
					$resource_totals['bauxite']['turn'] += $record['bauxite'];
					$resource_totals['gasoline']['turn'] += $record['gasoline'];
					$resource_totals['munitions']['turn'] += $record['munitions'];
					$resource_totals['steel']['turn'] += $record['steel'];
					$resource_totals['aluminum']['turn'] += $record['aluminum'];
				}
				$resource_totals['cash']['day'] += $record['cash'];
				$resource_totals['food']['day'] += $record['food'];
				$resource_totals['coal']['day'] += $record['coal'];
				$resource_totals['oil']['day'] += $record['oil'];
				$resource_totals['uranium']['day'] += $record['uranium'];
				$resource_totals['lead']['day'] += $record['lead'];
				$resource_totals['iron']['day'] += $record['iron'];
				$resource_totals['bauxite']['day'] += $record['bauxite'];
				$resource_totals['gasoline']['day'] += $record['gasoline'];
				$resource_totals['munitions']['day'] += $record['munitions'];
				$resource_totals['steel']['day'] += $record['steel'];
				$resource_totals['aluminum']['day'] += $record['aluminum'];
			}			
			// Week gathering
			$resource_totals['cash']['temp'] += $record['cash'];
			$resource_totals['food']['temp'] += $record['food'];
			$resource_totals['coal']['temp'] += $record['coal'];
			$resource_totals['oil']['temp'] += $record['oil'];
			$resource_totals['uranium']['temp'] += $record['uranium'];
			$resource_totals['lead']['temp'] += $record['lead'];
			$resource_totals['iron']['temp'] += $record['iron'];
			$resource_totals['bauxite']['temp'] += $record['bauxite'];
			$resource_totals['gasoline']['temp'] += $record['gasoline'];
			$resource_totals['munitions']['temp'] += $record['munitions'];
			$resource_totals['steel']['temp'] += $record['steel'];
			$resource_totals['aluminum']['temp'] += $record['aluminum'];
			
			// Month Gathering
			$resource_totals['cash']['temp_month'] += $record['cash'];
			$resource_totals['food']['temp_month'] += $record['food'];
			$resource_totals['coal']['temp_month'] += $record['coal'];
			$resource_totals['oil']['temp_month'] += $record['oil'];
			$resource_totals['uranium']['temp_month'] += $record['uranium'];
			$resource_totals['lead']['temp_month'] += $record['lead'];
			$resource_totals['iron']['temp_month'] += $record['iron'];
			$resource_totals['bauxite']['temp_month'] += $record['bauxite'];
			$resource_totals['gasoline']['temp_month'] += $record['gasoline'];
			$resource_totals['munitions']['temp_month'] += $record['munitions'];
			$resource_totals['steel']['temp_month'] += $record['steel'];
			$resource_totals['aluminum']['temp_month'] += $record['aluminum'];
			$last_time = $record['time'];
		}
		
		$temp = array();
		foreach($resource_totals['increment_labels'] as $key => $val)
			$temp[$key] = array_reverse($val);
		$resource_totals['increment_labels'] = $temp;
		
		$temp = array();
		foreach($resource_totals['increment_totals'] as $time => $arr){
			$temp[$time] = array();
			foreach($resource_totals['increment_totals'][$time] as $key => $val){
				$temp[$time][$key] = $val;
				$temp[$time][$key][0] = array_reverse($val[0]);
			}
		}
		$resource_totals['increment_totals'] = $temp;
		
		return view('hbe.taxes', ['fixed' => $fixed, 'graphs' => true, 'resource_totals' => $resource_totals, 'resources' => $resources, 'last_update' => $this->hbe_time_format(DB::table('cron_last_run')->where('id', 'taxes')->value('time'))]);
	}
	
	public function fixTaxes(Request $request){
		if(!AuthHelper::canAccess('fix_tax_income'))
			return view('hbe.access_denied');
		
		$this->validate($request, [
            'fix_taxes' => 'required'
        ]);
		
		$logged_in = $this->checkLoggedIn();
		if($logged_in === 2)
			return view('hbe.error_message', ['message' => 'We failed to login to the game.']);
		if(!$logged_in){
			$this->login();
			$logged_in = $this->checkLoggedIn();
			if($logged_in === 2 || !$logged_in)
				return view('hbe.error_message', ['message' => 'We were unable to login to the game.']);
		}
		
		$taxes_page = 'https://politicsandwar.com/alliance/id=' . $this->alliance_id . '&display=banktaxes';
		$tax_num = $this->getTaxNum($this->getPage($taxes_page));
		if($tax_num <= 0)
			return view('hbe.error_message', ['message' => 'We were unable to get the proper tax number.']);
		
		$base = 500;
		$page_num = $base * (floor($tax_num / $base));
		$params = array(
			'maximum' => $base,
			'minimum' => $page_num,
			'search' => 'Go'
		);
		$time = $this->getTimeFromPage($this->getPage($taxes_page, true, $params));
		if($time < 0)
			return view('hbe.error_message', ['message' => 'We were unable to get the proper time from the taxes page.']);
		if(!$this->backupTaxesTable())
			return view('hbe.error_message', ['message' => 'We were unable to backup the taxes table before fixing.']);
		Cache::put(md5('fixing_taxes'), true, 20);
		$fix_taxes = $this->fixTaxesTable($time, $page_num, $taxes_page);
		Cache::forget(md5('fixing_taxes'));
		if(!$fix_taxes)
			return view('hbe.error_message', ['message' => 'We were unable to fix the taxes table.']);
		return $this->taxes('Taxes successfully re-imported.');
	}
	
	private function backupTaxesTable(){
		$dir = base_path('db_backups/');
		$filename = 'taxes_backup_' . date('G-i-s_m_d_Y') . '.sql';
		$user = env('DB_USERNAME');
		$pass = env('DB_PASSWORD');
		$command = 'mysqldump --user ' . $user . ' --password=' . $pass . ' --complete-insert --skip-extended-insert britannia_tools taxes > ' . $dir . $filename;
		$output = '';
		exec($command, $output);
		
		if(is_array($output))
			$output = implode('', $output);
		if(stristr($output, 'Got error'))
			return false;
		
		if(!file_exists($dir . $filename))
			return false;
		return filesize($dir . $filename) > 1;
	}
	
	private function fixTaxesTable($time, $page_num, $taxes_page){
		DB::table('taxes')->where('time', '>=', $time)->delete();
		
		while($page_num >= 0){
			$params = array(
				'maximum' => 500,
				'minimum' => $page_num,
				'search' => 'Go'
			);
			
			$tax_page_data = $this->getPage($taxes_page, true, $params);
			$logged_in = stristr($tax_page_data, 'logout') ? true : false;
			if(!$logged_in){
				$this->login();
				$logged_in = $this->checkLoggedIn();
				if($logged_in === 2 || !$logged_in){
					echo 'Login failed in the middle.';
					return false;
				}
				$this->getPage($taxes_page);
				$tax_page_data = $this->getPage($taxes_page, true, $params);
				$logged_in = stristr($tax_page_data, 'logout') ? true : false;
				if(!$logged_in){
					echo 'Unable to pull taxes. (login)';
					return false;
				}
			}
			if(!$this->save_taxes($tax_page_data))
				return false;
			
			$page_num -= 500;
		}
		$exists = DB::table('cron_last_run')->where('id', 'taxes')->count() > 0;
		if($exists)
			DB::table('cron_last_run')->where('id', 'taxes')->update(['time' => time()]);
		else
			DB::table('cron_last_run')->insert(['id' => 'taxes', 'time' => time()]);
		return true;
	}
	
	private function save_taxes($data){
		$inserts = array();
		$dom = new \DOMDocument;
		@$dom->loadHTML($data);
		$tables = $dom->getElementsByTagName('table');
		$dataTable = null;
		foreach($tables as $table){
			if($table->getAttribute('class') == 'nationtable'){
				$dataTable = $table;
				break;
			}
		}
		if($dataTable == null)
			return false;

		$trs = @$dataTable->getElementsByTagName('tr');		
		for($i = $trs->length - 1; $i>0; $i--){
			$tr = $trs->item($i);
			if(!$tr->hasChildNodes())
				continue;
			if(stripos($tr->childNodes->item(0)->textContent, 'no tax records') !== false)
				continue;
			
			$temp = explode('=', $tr->childNodes->item(2)->firstChild->getAttribute('href'));
			if(count($temp) < 2)
				continue;
			
			$time = strtotime(trim($tr->childNodes->item(1)->firstChild->nodeValue));			
			$inserts[] = array(
				'nation_id' => (int)trim($temp[1]),
				'name' => trim($tr->childNodes->item(2)->firstChild->nodeValue),
				'cash' => (double)str_replace(array('$', ','), array('', ''), trim($tr->childNodes->item(4)->firstChild->nodeValue)),
				'food' => (double)str_replace(',', '', trim($tr->childNodes->item(5)->firstChild->nodeValue)),
				'coal' => (double)str_replace(',', '', trim($tr->childNodes->item(6)->firstChild->nodeValue)),
				'oil' => (double)str_replace(',', '', trim($tr->childNodes->item(7)->firstChild->nodeValue)),
				'uranium' => (double)str_replace(',', '', trim($tr->childNodes->item(8)->firstChild->nodeValue)),
				'lead' => (double)str_replace(',', '', trim($tr->childNodes->item(9)->firstChild->nodeValue)),
				'iron' => (double)str_replace(',', '', trim($tr->childNodes->item(10)->firstChild->nodeValue)),
				'bauxite' => (double)str_replace(',', '', trim($tr->childNodes->item(11)->firstChild->nodeValue)),
				'gasoline' => (double)str_replace(',', '', trim($tr->childNodes->item(12)->firstChild->nodeValue)),
				'munitions' => (double)str_replace(',', '', trim($tr->childNodes->item(13)->firstChild->nodeValue)),
				'steel' => (double)str_replace(',', '', trim($tr->childNodes->item(14)->firstChild->nodeValue)),
				'aluminum' => (double)str_replace(',', '', trim($tr->childNodes->item(15)->firstChild->nodeValue)),
				'time' => $time,
			);
		}
		DB::table('taxes')->insert($inserts);
		return true;
	}
	
	private function getTaxNum($taxPage){
		$matches = array();
		preg_match('/Showing [0-9]+-[0-9]+ of [0-9,]+ Records<\/p>/', $taxPage, $matches);
		if(empty($matches))
			return -1;
		$str = $matches[0];
		$str_one = explode('of', $str);
		if(empty($str_one) || empty($str_one[0]) || empty($str_one[1]))
			return -2;
		$str_two = explode('Records', $str_one[1]);
		if(empty($str_two) || empty($str_two[0]))
			return -3;
		$int_ret = ((int)(trim(str_replace(',', '', $str_two[0]))));
		return $int_ret;
	}
	
	private function getTimeFromPage($data){
		$dom = new \DOMDocument;
		@$dom->loadHTML($data);
		$tables = $dom->getElementsByTagName('table');
		$dataTable = null;
		foreach($tables as $table){
			if($table->getAttribute('class') == 'nationtable'){
				$dataTable = $table;
				break;
			}
		}
		if($dataTable == null)
			return false;

		$trs = @$dataTable->getElementsByTagName('tr');
		$time = -1;
		for($i = $trs->length - 1; $i>0; $i--){
			$tr = $trs->item($i);
			if(!$tr->hasChildNodes())
				continue;
			if(stripos($tr->childNodes->item(0)->textContent, 'no tax records') !== false)
				continue;
			
			$temp = explode('=', $tr->childNodes->item(2)->firstChild->getAttribute('href'));
			if(count($temp) < 2)
				continue;
			
			$time = strtotime(trim($tr->childNodes->item(1)->firstChild->nodeValue));
			break;
		}
		return $time;
	}
	
	public function marketAvg(){
		if(!AuthHelper::canAccess('view_market_avg'))
			return view('hbe.access_denied');
		
		$pull_time = time() - 3888000;
		$data = DB::table('market_data')->where('time', '>=', $pull_time)->get();
		$sorted_data = array();
		foreach($data as $market_data){
			$time_str = date('dm', $market_data['time']);
			if(!array_key_exists($time_str, $sorted_data)){
				$sorted_data[$time_str] = array(
					'food' => array('high' => array(), 'low' => array(), 'time' => $market_data['time']),
					'steel' => array('high' => array(), 'low' => array(), 'time' => $market_data['time']),
					'aluminum' => array('high' => array(), 'low' => array(), 'time' => $market_data['time']),
					'gasoline' => array('high' => array(), 'low' => array(), 'time' => $market_data['time']),
					'munitions' => array('high' => array(), 'low' => array(), 'time' => $market_data['time']),
					'uranium' => array('high' => array(), 'low' => array(), 'time' => $market_data['time']),
					'coal' => array('high' => array(), 'low' => array(), 'time' => $market_data['time']),
					'oil' => array('high' => array(), 'low' => array(), 'time' => $market_data['time']),
					'lead' => array('high' => array(), 'low' => array(), 'time' => $market_data['time']),
					'iron' => array('high' => array(), 'low' => array(), 'time' => $market_data['time']),
					'bauxite' => array('high' => array(), 'low' => array(), 'time' => $market_data['time']),
					'credits' => array('high' => array(), 'low' => array(), 'time' => $market_data['time'])
				);
			}
			
			$sorted_data[$time_str][$market_data['resource']]['high'][] = $market_data['high_buy'];
			$sorted_data[$time_str][$market_data['resource']]['low'][] = $market_data['low_buy'];
		}
		$output_data = array(
			'food' => array(),
			'steel' => array(),
			'aluminum' => array(),
			'gasoline' => array(),
			'munitions' => array(),
			'uranium' => array(),
			'coal' => array(),
			'oil' => array(),
			'lead' => array(),
			'iron' => array(),
			'bauxite' => array(),
			'credits' => array()
		);
		$days_str = array();
		$counted = array();
		foreach($sorted_data as $days){
			$time = 0;
			foreach($days as $resource => $resource_data){
				$resource_data['high'] = $this->remove_outliers($resource_data['high']);
				$high_avg = ((double)(((double)array_sum($resource_data['high'])) / ((double)count($resource_data['high']))));
				$resource_data['low'] = $this->remove_outliers($resource_data['low']);
				$low_avg = ((double)(((double)array_sum($resource_data['low'])) / ((double)count($resource_data['low']))));
				$actual_avg = ((double)(($high_avg + $low_avg) / 2.0));
				$output_data[$resource][] = (double)number_format($actual_avg, 2, '.', '');
				if(!in_array($resource_data['time'], $counted)){
					$counted[] = $resource_data['time'];
					$days_str[] = date('m/d', $resource_data['time']);
				}
			}
		}
		return view('hbe.market', ['graphs' => true, 'output' => $output_data, 'days' => $days_str]);
	}
	
	private function getColumn($sort, $default){
		$table_fields = DB::select('DESCRIBE `nation_stats`');
		foreach($table_fields as $column)
			if($sort == $column['Field'])
				return $column['Field'];
		return $default;
	}
	
	private function fixColumns(){
		$columns = array();
		$table_fields = DB::select('DESCRIBE `nation_stats`');
		foreach($table_fields as $column)
			$columns[] = 'nation_stats.' . $column['Field'] . ' as ' . $column['Field'];
		$table_fields = DB::select('DESCRIBE `warchests`');
		foreach($table_fields as $column)
			$columns[] = 'warchests.' . $column['Field'] . ' as ' . $column['Field'];
		return $columns;
	}
}
