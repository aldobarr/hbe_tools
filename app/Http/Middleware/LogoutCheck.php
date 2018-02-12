<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use DB;
use URL;

class LogoutCheck
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		if(Auth::check()){
			$id = Auth::user()->id;
			$force_logout = DB::table('users')->where('id', $id)->pluck('force_logout');
			if(empty($force_logout) || $force_logout[0] == 1){
				Auth::logout();
				if(!empty($force_logout[0]))
					DB::table('users')->where('id', $id)->update(['force_logout' => 0]);
			}
		}
		return $next($request);
	}
}