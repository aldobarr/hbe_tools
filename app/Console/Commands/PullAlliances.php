<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;

class PullAlliances extends Command
{
	use CurlFunctions;
	
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'schedule:pullalliances';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Import alliances into the db.';
	
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
	public function handle(){
		DB::transaction(function(){
			$alliances = json_decode($this->getPage('https://politicsandwar.com/api/alliances/'))->alliances;
			if(empty($alliances))
				return;
			
			$inserts = array();
			foreach($alliances as $alliance){
				$new_flag = $this->dlFlag((empty($alliance->flagurl) ? '' : $alliance->flagurl), $alliance->name, $alliance->id);
				$inserts[] = array('id' => $alliance->id, 'name' => $alliance->name, 'label' => (empty($alliance->acronym) ? $alliance->name : $alliance->acronym), 'size' => (empty($alliance->score) ? 0 : $alliance->score), 'flag' => $new_flag);
			}
			if(!empty($inserts)){
				DB::table('alliances')->truncate();
				DB::table('alliances')->insert($inserts);
			}
		}, 3);
	}
	
	private function dlFlag($flag_url, $name, $id){
		if(empty($flag_url))
			return '/images/flags/pnw_flag.png';
		
		$path_parts = $this->path_info($flag_url);
		if(empty($path_parts) || empty($path_parts['dirname']) || empty($path_parts['basename']))
			return '';
		$flag_url = $path_parts['dirname'] . '/' . urlencode($path_parts['basename']);
		$flag = preg_replace('/[^A-Za-z0-9\_]/', '', str_replace(' ', '_', $name)) . '_' . $id . '.' . $path_parts['extension'];
		$img = public_path() . '/images/flags/' . $flag;
		$flag_raw = @file_get_contents($flag_url);
		if($flag_raw === FALSE)
			return '/images/flags/pnw_flag.png';
		file_put_contents($img, $flag_raw);
		return '/images/flags/' . $flag;
	}
	
	private function path_info($path){
		$temp = pathinfo($path);
		if(isset($temp['extension']))
			return $temp;
		if(isset($temp['dirname'])){
			$temp = pathinfo($temp['dirname']);
			return isset($temp['extension']) ? $temp : '';
		}
		return '';
	}
}
