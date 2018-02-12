<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ApplicantMembers extends Command
{
	use CurlFunctions;
	use \App\Http\Controllers\HBEFunctions;
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'applicants:add';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Move applicants to members.';

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
		$checks = $this->getPage('https://politicsandwar.com/alliance/id=2570');
		if(!stristr($checks, 'Change Permissions'))
			die('ERROR: Unable To Login To Alliance Page.');
		if(!$this->checkTurn($checks))
			return;
		if(preg_match('/11:[4-5][0-9]\s*pm/', $checks))
			return;
		
		$nations = json_decode($this->getPage('https://politicsandwar.com/api/nations/'))->nations;
		$membs = $this->getAllMemberIDs();
		foreach($nations as $nation){
			if($nation->allianceid != 2570 || in_array($nation->nationid, $membs) || stristr($nation->color, 'gr'))
				continue;
			
			$this->moveNation($nation, true);
		}
	}
}
