<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Auth\User;
use DB;
use Hash;

class ModifyUserIntegration extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'integration:modify {user} {email} {name} {pass}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Modify a user when changed in SMF.';

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
		$user = $this->argument('user');
		$email = $this->argument('email');
		$name = $this->argument('name');
		$pass = $this->argument('pass');
		if(!empty($pass))
			$pass = Hash::make($pass);
		
		$id = DB::table('users')->where('name', $user)->pluck('id')[0];
		$user = User::find($id);
		
		if(!empty($user)){
			$save = false;
			if((!empty($email) || !empty($pass))){
				if(!empty($pass))
					$user->password = $pass;
				if(!empty($email)){
					$user->email = $email;
					$user->activated = 0;
				}
				$user->force_logout = 1;
				$save = true;
			}
			if(!empty($name)){
				$user->display_name = $name;
				$save = true;
			}
			if($save)
				$user->save();
		}
	}
}