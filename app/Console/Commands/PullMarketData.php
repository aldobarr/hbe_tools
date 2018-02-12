<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;

class PullMarketData extends Command
{
	use CurlFunctions;
	
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'schedule:pullmarketdata';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Import market data.';
	
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
		$resources = array('credits', 'coal', 'oil', 'uranium', 'lead', 'iron', 'bauxite', 'gasoline', 'munitions', 'steel', 'aluminum', 'food');
		$inserts = array();
		$time = time();
		foreach($resources as $resource){
			$data = json_decode($this->getPage('https://politicsandwar.com/api/tradeprice/resource=' . $resource));
			$inserts[] = array('resource' => $resource, 'high_buy' => $data->highestbuy->price, 'low_buy' => $data->lowestbuy->price, 'avg' => $data->avgprice, 'time' => $time);
		}
		DB::table('market_data')->insert($inserts);
	}
}