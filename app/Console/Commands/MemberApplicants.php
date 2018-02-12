<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class MemberApplicants extends Command
{
	use CurlFunctions;
	use \App\Http\Controllers\HBEFunctions;
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'applicants:remove';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Move members to applicants.';

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
		$logged_in = $this->checkLoggedIn();
		if($logged_in === 2)
			return;
		if(!$logged_in)
			$this->login();
		if(!stristr($this->getPage('https://politicsandwar.com/alliance/id=2570'), 'Change Permissions'))
			die('ERROR: Unable To Login To Alliance Page.');
		
		$nations = json_decode($this->getPage('https://politicsandwar.com/api/nations/'))->nations;
		$membs = $this->getAllMemberIDs();
		foreach($nations as $nation){
			if($nation->allianceid != 2570 || in_array($nation->nationid, $membs))
				continue;
			
			$this->moveNation($nation, false);
			$tries = 5;
			//while(!$this->checkIsApp($nation->nationid) && $tries-- > 0)
				//$this->moveNation($nation, false);
		}

		$aa = json_decode($this->getPage('https://politicsandwar.com/api/alliance/id=2570'));
		if($aa->members > count($membs))
			Artisan::call('applicants:remove', []);
	}

	protected function checkIsApp($id){
		$nation = json_decode($this->getPage('https://politicsandwar.com/api/nation/id=' . $id));
		if(isset($nation->error))
			return true;

		return ($nation->allianceposition <= 1);
	}
}
