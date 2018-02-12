<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Auth\User;
use DB;
use Hash;

class RegisterIntegration extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'integration:register {user} {email} {pass}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Register a new user when registered on the forum.';

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
		$email = $this->argument('email');
		$pass = Hash::make($this->argument('pass'));
		
		$users = DB::table('users')->where('name', $name)->orWhere('email', $email)->get();
		if(!empty($users))
			return;
		
		$user = new User;
		$user->name = $name;
		$user->email = $email;
		$user->display_name = $name;
		$user->password = $pass;
		$user->save();
	}
}