<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;

class ReturnAntiRaid extends Command
{
	use CurlFunctions;
	
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'raidprevention:finish';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Return raid prevention funds.';
	
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
		if(DB::table('misc_data')->where('key', 'antiraid')->value('value') != '1')
			return;
		
		$ch = $this->getNewCurl(false, true);
		$logged_in = $this->checkLoggedIn($ch);
		if($logged_in === 2){
			$this->closeCurlResource($ch);
			return;
		}
		if(!$logged_in){
			$this->login('email@domain.com', 'pass', $ch);
			$logged_in = $this->checkLoggedIn($ch);
			if($logged_in === 2 || !$logged_in){
				$this->closeCurlResource($ch);
				return;
			}
		}
		
		$bank_page = 'https://politicsandwar.com/alliance/id=4348&display=bank';
		$bank_page_data = $this->getPageWithResource($ch, $bank_page);
		
		$returned_ids = array();
		$anti_raid_data = DB::table('anti_raid_transactions')->where('returned', 0)->get();
		
		$token = $this->getToken($bank_page_data);
		if(empty($anti_raid_data) || empty($token))
			return;
		
		$deposit = array(
			'cash' => 0.0,
			'food' => 0.0,
			'coal' => 0.0,
			'oil' => 0.0,
			'uranium' => 0.0,
			'lead' => 0.0,
			'iron' => 0.0,
			'bauxite' => 0.0,
			'gasoline' => 0.0,
			'munitions' => 0.0,
			'steel' => 0.0,
			'aluminum' => 0.0,
		);
		foreach($anti_raid_data as $data){
			foreach($data as $key => $val){
				if(array_key_exists($key, $deposit))
					$deposit[$key] += $val;
			}
			$returned_ids[] = $data['id'];
		}
		
		$params = array(
			'withmoney' => $deposit['cash'],
			'withfood' => $deposit['food'],
			'withcoal' => $deposit['coal'],
			'withoil' => $deposit['oil'],
			'withuranium' => $deposit['uranium'],
			'withlead' => $deposit['lead'],
			'withiron' => $deposit['iron'],
			'withbauxite' => $deposit['bauxite'],
			'withgasoline' => $deposit['gasoline'],
			'withmunitions' => $deposit['munitions'],
			'withsteel' => $deposit['steel'],
			'withaluminum' => $deposit['aluminum'],
			'withtype' => 'Alliance',
			'withrecipient' => 'Holy Britannian Empire',
			'withnote' => '',
			'withsubmit' => 'Withdraw',
			'token' => $token,
		);
		
		$results = $this->getPageWithResource($ch, $bank_page, true, $params);
		$this->closeCurlResource($ch);
		if(stristr($results, 'You successfully transferred funds from the alliance bank')){
			DB::table('anti_raid_transactions')->whereIn('id', $returned_ids)->update(['returned' => 1]);
			DB::table('misc_data')->where('key', 'antiraid')->update(['value' => 0]);
		}
	}
	
	private function getToken($data){
		$dom = new \DOMDocument;
		@$dom->loadHTML($data);
		$inputs = $dom->getElementsByTagName('input');
		foreach($inputs as $input){
			if($input->getAttribute('name') == 'token'){
				return $input->getAttribute('value');
			}
		}
		return false;
	}
}
