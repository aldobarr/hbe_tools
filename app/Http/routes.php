<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', 'MapController@web');
Route::get('home', 'MapController@web');

// TEMP
Route::get('ve/form', 'CalculatorController@form');
Route::post('ve/form', 'CalculatorController@formSubmit');
Route::get('ve/rebuild', 'CalculatorController@rebuild');
Route::get('ve/graph', 'CalculatorController@graph');
// TEMP
Route::get('raid/start', function(){
	if(!AuthHelper::canAccess('admin_forum'))
		return view('hbe.access_denied');
	
	Artisan::call('raidprevention:start', []);
	return 'Raid Prevention Start Command Sent.';
});
Route::get('raid/stop', function(){
	if(!AuthHelper::canAccess('admin_forum'))
		return view('hbe.access_denied');
	
	Artisan::call('raidprevention:finish', []);
	return 'Raid Prevention Stop Command Sent.';
});

Route::get('market', 'StatsController@marketAvg');
Route::get('calc/infra', 'CalculatorController@infraCheck');
Route::post('calc/infra', 'CalculatorController@infraCheckSubmit');
Route::get('calc/land', 'CalculatorController@landCheck');
Route::post('calc/land', 'CalculatorController@landCheckSubmit');
Route::get('calc/warchest', 'CalculatorController@warchestCheck');
Route::post('calc/warchest', 'CalculatorController@warchestCheckSubmit');
Route::get('calc/aa/warchest', 'CalculatorController@aaWarchestCheck');

Route::get('defense/target', 'DefenseController@targets');
Route::post('defense/target', 'DefenseController@targetMatch');
Route::post('defense/target/assign', 'DefenseController@targetAssign');
Route::get('defense/target/activate', 'DefenseController@targetActivateForm');
Route::post('defense/target/activate', 'DefenseController@targetActivate');
Route::get('defense/readiness', 'DefenseController@readiness');
Route::get('raid/prevention', 'DefenseController@raidPrevention');
Route::post('raid/prevention', 'DefenseController@raidPreventionToggle');
Route::get('raid/prevention/withdraw', function(){
	return redirect('raid/prevention');
});
Route::post('raid/prevention/withdraw', 'DefenseController@raidPreventionWithdraw');

// Plugin
Route::get('plugin', function(){
	if(!AuthHelper::canAccess('dl_plugin'))
		return view('hbe.access_denied');
	
	return view('hbe.warchest.plugin', ['icon' => url('/images/hbe.png'), 'hash' => ('sha1:' . sha1_file(dirname(dirname(dirname(__FILE__))) . '/britannian_nation_parser-an+fx.xpi'))]);
});
Route::get('firefox', function(){
	$filepath = dirname(dirname(dirname(__FILE__))) . '/britannian_nation_parser-an+fx.xpi';
	$filename = sprintf('"%s"', addcslashes(basename($filepath), '"\\'));
	$filesize = filesize($filepath);
	
	header('Content-Description: File Transfer');
	header('Content-Type: application/x-xpinstall');
	header('Content-Disposition: attachment; filename=' . $filename);
	header('Content-Transfer-Encoding: binary');
	header('Connection: Keep-Alive');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	header('Content-Length: ' . $filesize);
	readfile($filepath);
	return;
});
Route::get('ffupdate', function(){
	header('Content-Type: application/rdf+xml');
	$filepath = dirname(dirname(dirname(__FILE__))) . '/britannian_nation_parser-an+fx.update.rdf';
	readfile($filepath);
	return;
});
Route::group(['middleware' => 'cors'], function(){
	Route::post('/auto', 'SignInController@signInAuto');
});
// End Plugin

// Authentication routes...
Route::get('auth/login', 'Auth\AuthController@getLogin');
Route::post('auth/login', 'Auth\AuthController@postLogin');
Route::get('auth/logout', function(){
	require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/hbe_pnw/Sources/LogInOut.php');
	
	Logout(true, false);
	return redirect('home');
});

// Registration routes...
/*Route::get('auth/register', 'Auth\AuthController@getRegister');
Route::post('auth/register', 'Auth\AuthController@postRegister');

// Password reset link request routes...
Route::get('password/email', 'Auth\PasswordController@getEmail');
Route::post('password/email', 'Auth\PasswordController@postEmail');

// Password reset routes...
Route::get('password/reset/{token}', 'Auth\PasswordController@getReset');
Route::post('password/reset', 'Auth\PasswordController@postReset');*/

Route::get('user/management/{all?}', 'ManagementController@user');
Route::get('user/permissions', 'ManagementController@permissions');
Route::get('user/roles', 'ManagementController@roles');

Route::get('stats/dl', 'StatsController@download');
Route::get('stats/{page?}/{sort?}/{asc?}/{search?}', 'StatsController@stats');
Route::get('taxes', 'StatsController@taxes');
Route::post('taxes', 'StatsController@fixTaxes');

Route::get('map', 'MapController@map');
Route::get('map/alliances', 'MapController@alliances');
Route::get('map/manage', 'MapController@manage');

Route::get('web', 'MapController@web');
Route::get('web/raw', 'MapController@web_raw');
Route::get('web/alliances', 'MapController@alliances');
Route::get('web/manage', 'MapController@manage_web');

Route::get('messages', 'RecruitmentController@messages');
Route::get('pm', 'RecruitmentController@sendPM');
Route::post('pm', 'RecruitmentController@postPM');

Route::get('license/check/{key}', 'KeySecurity@checkKey');
Route::get('license/set/{key}/{nation_id}', 'KeySecurity@setUser');

Route::group(['prefix' => 'mass_pm'], function(){
	Route::get('version', 'MassPMController@version');
	Route::get('get_app', 'MassPMController@getApp');
	Route::get('get_runner', 'MassPMController@getRunner');
});

Route::get('ajax_api/defense/submit', 'AjaxApiController@submitTarget');

Route::post('ajax_api/user_man', 'AjaxApiController@userMan');
Route::get('ajax_api/user_man/del/{id}', 'AjaxApiController@userManDel');

Route::post('ajax_api/war_map_man/add', 'AjaxApiController@warMapAdd');
Route::get('ajax_api/war_map_man/del/{id}', 'AjaxApiController@warMapDel');

Route::post('ajax_api/treaty_web_man/add', 'AjaxApiController@treatyWebAdd');
Route::get('ajax_api/treaty_web_man/del/{id}', 'AjaxApiController@treatyWebDel');

Route::post('ajax_api/roles_man', 'AjaxApiController@rolesMan');
Route::post('ajax_api/roles_man/add', 'AjaxApiController@rolesManAdd');
Route::get('ajax_api/roles_man/del/{id}', 'AjaxApiController@rolesManDel');

Route::post('ajax_api/perms_man', 'AjaxApiController@permsMan');
Route::post('ajax_api/perms_man/add', 'AjaxApiController@permsManAdd');
Route::get('ajax_api/perms_man/del/{id}', 'AjaxApiController@permsManDel');

Route::post('ajax_api/message_man', 'AjaxApiController@messageMan');
Route::post('ajax_api/message_man/add', 'AjaxApiController@messageManAdd');
Route::get('ajax_api/message_man/del/{id}', 'AjaxApiController@messageManDel');
Route::get('ajax_api/message_man/toggle/{id}', 'AjaxApiController@messageManToggle');
