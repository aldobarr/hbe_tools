<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Cache;
use Artisan;

class PullTaxes extends Command
{
	use CurlFunctions;
	
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'schedule:pulltaxes';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Import the alliance taxes.';
	
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
		if(Cache::has(md5('fixing_taxes')))
			return;
		
		$aa = json_decode($this->getPage('https://politicsandwar.com/api/alliance/id=2570'));
		$members = (((int)($aa->members)) - ((int)($aa->vmodemembers))) + 1;
		
		$logged_in = $this->checkLoggedIn();
		if($logged_in === 2)
			return;
		if(!$logged_in)
			$this->login();
		
		$taxes_page = 'https://politicsandwar.com/alliance/id=2570&display=banktaxes';
		$this->getPage($taxes_page);
		
		$params = array(
			'maximum' => $members,
			'minimum' => 0,
			'search' => 'Go'
		);
		
		if($this->save_content($this->getPage($taxes_page, true, $params))){
			$exists = DB::table('cron_last_run')->where('id', 'taxes')->count() > 0;
			if($exists)
				DB::table('cron_last_run')->where('id', 'taxes')->update(['time' => time()]);
			else
				DB::table('cron_last_run')->insert(['id' => 'taxes', 'time' => time()]);
			
			//Artisan::call('applicants:remove', []);
		}
	}
	
	private function save_content($data){
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
		$latest_time = strtotime(trim($trs->item(1)->childNodes->item(1)->firstChild->nodeValue));
		if(DB::table('taxes')->where('time', $latest_time)->count() > 0)
			return false;
		
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
			if($time != $latest_time)
				continue;
			
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
}
