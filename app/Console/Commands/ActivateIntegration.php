<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;

class ActivateIntegration extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'integration:activate {user}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Activates the account of a user.';

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
		$name = $this->argument('user');
		$user = DB::table('users')->where('name', $name)->first();
		if(empty($user) || !empty($user['activated']))
			return;
		
		DB::table('users')->where('id', $user['id'])->update(['activated' => 1]);
	}
}