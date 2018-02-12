<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PullWorldMilitaries extends Command
{
	use CurlFunctions;
	use \App\Http\Controllers\HBEFunctions;

	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'schedule:pullworldmilitaries';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Import the world military stats.';
	
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
			$temp_ch = $this->getNewCurl();
			curl_setopt($temp_ch, CURLOPT_TIMEOUT, 55);
			$nations = json_decode($this->getPageWithResource($temp_ch, 'https://politicsandwar.com/api/nations/'));
			if(empty($nations->success))
				return false;

			$this->closeCurlResource($temp_ch);
			$nations = $nations->nations;
			$membs = $this->getAllMemberIDs();
			
			DB::table('world_military_stats')->truncate();
			$inserts = array();
			$count = 0;
			foreach($nations as $nation_raw){
				if($nation_raw->allianceid == 0)
					continue;
				
				$nation_data = json_decode($this->getPage('https://politicsandwar.com/api/nation/id=' . $nation_raw->nationid));
				
				if(empty($nation_data) || !empty($nation_data->error) || $nation_data->allianceposition <= 1)
					continue;
				
				if($nation_data->allianceid == 2570 && !in_array($nation_data->nationid, $membs))
					continue;

				$inserts[] = array(
					'nation_id' => $nation_raw->nationid,
					'nation' => $nation_raw->nation,
					'leader' => $nation_raw->leader,
					'alliance_id' => $nation_raw->allianceid,
					'alliance_pos' => (int)$nation_data->allianceposition,
					'soldiers' => (int)$nation_data->soldiers,
					'tanks' => (int)$nation_data->tanks,
					'planes' => (int)$nation_data->aircraft,
					'ships' => (int)$nation_data->ships,
					'missiles' => (int)$nation_data->missiles,
					'nukes' => (int)$nation_data->nukes,
					'score' => (double)$nation_data->score,
					'cities' => (int)$nation_data->cities,
					'projects' => $this->calcProjects($nation_data),
					'infra' => (double)$nation_data->totalinfrastructure,
					'city_timer' => (int)$nation_data->cityprojecttimerturns,
					'inactive' => (int)$nation_data->minutessinceactive,
				);
				
				if(++$count == 200){
					$count = 0;
					DB::table('world_military_stats')->insert($inserts);
					$inserts = array();
				}
			}
			
			if(!empty($inserts))
				DB::table('world_military_stats')->insert($inserts);
			
			$exists = DB::table('cron_last_run')->where('id', 'world_military_stats')->count() > 0;
			if($exists)
				DB::table('cron_last_run')->where('id', 'world_military_stats')->update(['time' => time()]);
			else
				DB::table('cron_last_run')->insert(['id' => 'world_military_stats', 'time' => time()]);
		}, 2);
	}
	
	private function calcProjects($nation){
		$count = 0;
		$projects = array('ironworks', 'bauxiteworks', 'armsstockpile', 'emgasreserve', 'massirrigation', 'inttradecenter', 'missilelpad', 'nuclearresfac', 'irondome', 'vitaldefsys', 'intagncy', 'uraniumenrich', 'propbureau', 'cenciveng');
		foreach($projects as $project)
			if(!empty($nation->$project))
				$count++;
		return $count;
	}
	
	private function getColumns(){
		$columns = array();
		$table_fields = DB::select('DESCRIBE `world_military_stats`');
		foreach($table_fields as $column)
			$columns[] = $column['Field'];
		return $columns;
	}
}
