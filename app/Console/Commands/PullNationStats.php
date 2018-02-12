<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PullNationStats extends Command
{
	use CurlFunctions;
	use \App\Http\Controllers\HBEFunctions;
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'schedule:pullnationstats';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Import the alliance stats.';
	
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
		DB::transaction(function(){
			$stored_nations = DB::table('nation_stats')->pluck('next_audit', 'nation_id');
			$temp_ch = $this->getNewCurl();
			curl_setopt($temp_ch, CURLOPT_TIMEOUT, 35);
			$nations = json_decode($this->getPageWithResource($temp_ch, 'http://politicsandwar.com/api/alliance-members/?key=' . $this->api_key . '&allianceid=' . $this->alliance_id));
			if(empty($nations->success))
				return false;
			
			$nations = $nations->nations;
			$this->closeCurlResource($temp_ch);
			$membs = $this->getAllMemberIDs();
			$nation_ids = DB::table('warchests')->pluck('id');
			
			$inserts = array();
			$vals = array();
			$i = 0;
			foreach($nations as $nation){
				if($nation->allianceposition <= 1)
					continue;
				
				$next_audit = 0;
				if(array_key_exists($nation->nationid, $stored_nations)){
					$next_audit = $stored_nations[$nation->nationid];
					unset($stored_nations[$nation->nationid]);
				}
				
				$nation_id = (int)$nation->nationid;
				$array_vals = array(
					'nation_id_' . $i => $nation->nationid,
					'nation_' . $i => $nation->nation,
					'leader_' . $i => $nation->leader,
					'soldiers_' . $i => (int)$nation->soldiers,
					'tanks_' . $i => (int)$nation->tanks,
					'planes_' . $i => (int)$nation->aircraft,
					'ships_' . $i => (int)$nation->ships,
					'missiles_' . $i => (int)$nation->missiles,
					'nukes_' . $i => (int)$nation->nukes,
					'spies_' . $i => (int)$nation->spies,
					'score_' . $i => (double)$nation->score,
					'cities_' . $i => (int)$nation->cities,
					'infra_' . $i => (double)$nation->infrastructure,
					'cce_' . $i => (bool)$nation->cenciveng,
					'pb_' . $i => (bool)$nation->propbureau,
					'citytimer_' . $i => (int)$nation->cityprojecttimerturns,
					'inactive_' . $i => (int)$nation->minutessinceactive,
					'next_audit_' . $i => $next_audit,
				);
				$vals[] = '(:nation_id_' . $i . ', :nation_' . $i . ', :leader_' . $i . ', :soldiers_' . $i . ', :tanks_' . $i . ', :planes_' . $i . ', :ships_' . $i . ', :missiles_' . $i . ', :nukes_' . $i . ', :spies_' . $i . ', :score_' . $i . ', :cities_' . $i . ', :infra_' . $i . ', :cce_' . $i . ', :pb_' . $i . ', :citytimer_' . $i . ', :inactive_' . $i . ', :next_audit_' . $i++ . ')';
				
				$inserts = empty($inserts) ? $array_vals : array_merge($inserts, $array_vals);
				
				$update = array(
					'cash' => (double)$nation->money,
					'credits' => (int)$nation->credits,
					'steel' => (double)$nation->steel,
					'aluminum' => (double)$nation->aluminum,
					'gasoline' => (double)$nation->gasoline,
					'munitions' => (double)$nation->munitions,
					'uranium' => (double)$nation->uranium,
					'food' => (double)$nation->food,
					'coal' => (double)$nation->coal,
					'oil' => (double)$nation->oil,
					'lead' => (double)$nation->lead,
					'iron' => (double)$nation->iron,
					'bauxite' => (double)$nation->bauxite,
				);
				if(!in_array($nation_id, $nation_ids)){
					$update['id'] = $nation_id;
					$update['time'] = time();
					DB::table('warchests')->insert($update);
				}else
					DB::table('warchests')->where('id', $nation_id)->update($update);
			}
			if(!empty($stored_nations))
				DB::table('nation_stats')->whereIn('nation_id', array_keys($stored_nations))->delete();
			if(!empty($inserts))
				DB::insert('REPLACE INTO `nation_stats` (`' . implode('`, `', $this->getColumns()) . '`) VALUES ' . implode(', ', $vals), $inserts);
			
			$exists = DB::table('cron_last_run')->where('id', 'nation_stats')->count() > 0;
			if($exists)
				DB::table('cron_last_run')->where('id', 'nation_stats')->update(['time' => time()]);
			else
				DB::table('cron_last_run')->insert(['id' => 'nation_stats', 'time' => time()]);
		}, 2);
	}
	
	private function save_content($data){
		$dom = new \DOMDocument;
		@$dom->loadHTML($data);
		$tables = $dom->getElementsByTagName('table');
		$dataTable = null;
		$tables_found = 0;
		foreach($tables as $table){
			if($table->getAttribute('class') == 'nationtable'){
				if(++$tables_found <= 2)
					continue;
				$dataTable = $table;
				break;
			}
		}
		if($dataTable == null)
			return false;
		
		$trs = @$dataTable->getElementsByTagName('tr');
		$nation_ids = DB::table('warchests')->pluck('id');
		for($i = 1; $i<$trs->length; $i++){
			$tr = $trs->item($i);
			if(!$tr->hasChildNodes())
				continue;
			//if(stripos($tr->childNodes->item(0)->textContent, 'no tax records') !== false)
				//continue;
			
			$temp = explode('=', $tr->childNodes->item(0)->childNodes->item(1)->getAttribute('href'));
			if(count($temp) < 2)
				continue;
			
			$spies = (int)trim($tr->childNodes->item(26)->firstChild->nodeValue);
			$nation_id = (int)trim($temp[1]);
			$time = time();
			$update = array(
				'cash' => (double)str_replace(array('$', ','), array('', ''), trim($tr->childNodes->item(5)->firstChild->nodeValue)),
				'steel' => (double)str_replace(',', '', trim($tr->childNodes->item(7)->firstChild->nodeValue)),
				'aluminum' => (double)str_replace(',', '', trim($tr->childNodes->item(9)->firstChild->nodeValue)),
				'gasoline' => (double)str_replace(',', '', trim($tr->childNodes->item(11)->firstChild->nodeValue)),
				'munitions' => (double)str_replace(',', '', trim($tr->childNodes->item(13)->firstChild->nodeValue)),
				'uranium' => (double)str_replace(',', '', trim($tr->childNodes->item(15)->firstChild->nodeValue)),
				'food' => (double)str_replace(',', '', trim($tr->childNodes->item(17)->firstChild->nodeValue)),
			);
			if(!in_array($nation_id, $nation_ids)){
				$update['id'] = $nation_id;
				$update['credits'] = 0;
				$update['coal'] = 0;
				$update['oil'] = 0;
				$update['lead'] = 0;
				$update['iron'] = 0;
				$update['bauxite'] = 0;
				$update['time'] = time();
				DB::table('warchests')->insert($update);
			}else
				DB::table('warchests')->where('id', $nation_id)->update($update);
			DB::table('nation_stats')->where('nation_id', $nation_id)->update(array('spies' => $spies));
		}
		return true;
	}
	
	private function getColumns(){
		$columns = array();
		$table_fields = DB::select('DESCRIBE `nation_stats`');
		foreach($table_fields as $column)
			$columns[] = $column['Field'];
		return $columns;
	}
}
