<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Artisan;

class ActivateAntiRaid extends Command
{
	use CurlFunctions;
	
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'raidprevention:start';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Activate raid prevention.';
	
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
		if(DB::table('misc_data')->where('key', 'antiraid')->value('value') == '1')
			return;
		
		
		DB::table('misc_data')->where('key', 'antiraid')->update(['value' => 1]);
		Artisan::call('schedule:preventraids', []);
	}
}