<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;

class PreventRaids extends Command
{
	use CurlFunctions;
	
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'schedule:preventraids';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Prevent raids by withdrawing the bank.';
	
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
		
		$ch = $this->getNewCurl();
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
		
		$bank_page = 'https://politicsandwar.com/alliance/id=' . $this->alliance_id . '&display=bank';
		$bank_page_data = $this->getPageWithResource($ch, $bank_page);
		
		$withdraw = $this->getFunds($bank_page_data);
		$token = $this->getToken($bank_page_data);
		if(empty($withdraw) || count($withdraw) != 12 || empty($token))
			return;
		
		$params = array(
			'withmoney' => $withdraw['cash'],
			'withfood' => $withdraw['food'],
			'withcoal' => $withdraw['coal'],
			'withoil' => $withdraw['oil'],
			'withuranium' => $withdraw['uranium'],
			'withlead' => $withdraw['lead'],
			'withiron' => $withdraw['iron'],
			'withbauxite' => $withdraw['bauxite'],
			'withgasoline' => $withdraw['gasoline'],
			'withmunitions' => $withdraw['munitions'],
			'withsteel' => $withdraw['steel'],
			'withaluminum' => $withdraw['aluminum'],
			'withtype' => 'Alliance',
			'withrecipient' => 'British Overseas Territories',
			'withnote' => '',
			'withsubmit' => 'Withdraw',
			'token' => $token,
		);
		
		$results = $this->getPageWithResource($ch, $bank_page, true, $params);
		$this->closeCurlResource($ch);
		if(stristr($results, 'You successfully transferred funds from the alliance bank')){
			DB::table('anti_raid_transactions')->insert($withdraw);
		}
	}
	
	private function getFunds($data){
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

		$retData = array();
		$trs = @$dataTable->getElementsByTagName('tr');
		for($i = 1; $i<$trs->length; $i++){
			$tr = $trs->item($i);
			if(!$tr->hasChildNodes())
				continue;
			
			if(stristr($tr->firstChild->nodeValue, 'Money'))
				$retData['cash'] = (double)str_replace(array('$', ','), array('', ''), trim($tr->childNodes->item(1)->firstChild->nodeValue));
			else if(stristr($tr->firstChild->nodeValue, 'Food'))
				$retData['food'] = (double)str_replace(',', '', trim($tr->childNodes->item(1)->firstChild->nodeValue));
			else if(stristr($tr->firstChild->nodeValue, 'Coal'))
				$retData['coal'] = (double)str_replace(',', '', trim($tr->childNodes->item(1)->firstChild->nodeValue));
			else if(stristr($tr->firstChild->nodeValue, 'Oil'))
				$retData['oil'] = (double)str_replace(',', '', trim($tr->childNodes->item(1)->firstChild->nodeValue));
			else if(stristr($tr->firstChild->nodeValue, 'Uranium'))
				$retData['uranium'] = (double)str_replace(',', '', trim($tr->childNodes->item(1)->firstChild->nodeValue));
			else if(stristr($tr->firstChild->nodeValue, 'Lead'))
				$retData['lead'] = (double)str_replace(',', '', trim($tr->childNodes->item(1)->firstChild->nodeValue));
			else if(stristr($tr->firstChild->nodeValue, 'Iron'))
				$retData['iron'] = (double)str_replace(',', '', trim($tr->childNodes->item(1)->firstChild->nodeValue));
			else if(stristr($tr->firstChild->nodeValue, 'Bauxite'))
				$retData['bauxite'] = (double)str_replace(',', '', trim($tr->childNodes->item(1)->firstChild->nodeValue));
			else if(stristr($tr->firstChild->nodeValue, 'Gasoline'))
				$retData['gasoline'] = (double)str_replace(',', '', trim($tr->childNodes->item(1)->firstChild->nodeValue));
			else if(stristr($tr->firstChild->nodeValue, 'Munitions'))
				$retData['munitions'] = (double)str_replace(',', '', trim($tr->childNodes->item(1)->firstChild->nodeValue));
			else if(stristr($tr->firstChild->nodeValue, 'Steel'))
				$retData['steel'] = (double)str_replace(',', '', trim($tr->childNodes->item(1)->firstChild->nodeValue));
			else if(stristr($tr->firstChild->nodeValue, 'Aluminum'))
				$retData['aluminum'] = (double)str_replace(',', '', trim($tr->childNodes->item(1)->firstChild->nodeValue));
		}
		return $retData;
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
