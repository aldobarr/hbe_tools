<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Artisan;

class TestFunction extends Command
{
	use CurlFunctions;
	use \App\Http\Controllers\HBEFunctions;
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'testing:testf';

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
		//echo $this->checkTurn($checks) ? 'Change Turn' : 'Not Time';
		Artisan::call('applicants:remove', []);
	}
}
