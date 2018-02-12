<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;

class MiniAudits extends Command
{
	use CurlFunctions;
	use \App\Http\Controllers\HBEFunctions;
	
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'schedule:miniaudit';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Perform an alliance-wide mini audit.';
	
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
	public function handle(){
		$sender = array(
			'id' => 503,
			'name' => 'Finance Bot',
			'username' => 'Finance Bot',
		);
		$subject = 'Ministry of Finance: Automated Nation Audit for %s';
		$message_template = 'This is an [u]automated[/u] audit. If you have any questions, please contact a member of the Ministry of Finance. Please do not reply to this message.

During our automated audit of your nation, we found the following issues:

[list]%s
[/list]

We strongly recommend you get a full audit from the Ministry of Finance for a more in depth analysis as well as guidance on how to properly fix your nation.

Regards,
-Ministry of Finance, Audit Division';
		$enter_char = '
';
		$checks = array(
			'warchest_signin',
			'military',
			'city_health',
			'color',
		);
		
		$now = time();
		$week = 604800;
		$num_weeks = 2;
		$next = $now + ($week * $num_weeks);
		$nations = DB::table('nation_stats')->pluck('nation_id'); //->where('next_audit', '<=', $now)
		foreach($nations as $nation_id){
			$nation = json_decode($this->getPage('https://politicsandwar.com/api/nation/id=' . $nation_id));
			if(empty($nation) || !empty($nation->error))
				continue;
			$nation->warchest = DB::table('warchests')->where('id', $nation_id)->first();
			$cities = array();
			$messages = '';
			$send_message = false;
			foreach($nation->cityids as $city_id){
				$city = json_decode($this->getPage('https://politicsandwar.com/api/city/id=' . $city_id));
				$cities[$city_id] = $city;
			}			
			foreach($checks as $function){
				$message = $this->$function($nation, $cities);
				if(!empty($message)){
					$send_message = true;
					
					$messages .= $enter_char . $message;
				}
			}
			if($send_message){
				$recipient = $this->findRecipient($nation_id);
				sendpm($recipient, sprintf($subject, $nation->prename . ' ' . $nation->name), sprintf($message_template, $messages), true, $sender);
			}
		}
		//DB::table('nation_stats')->whereIn('nation_id', $nations)->update(['next_audit' => $next]);
	}
	
	private function warchest_signin($nation, $cities){
		$message = '';
		if(empty($nation->warchest)){
			$message = '[li]Warchest ERROR![list][li]If you have received this message, please inform a member of government ASAP![/li][/list][/li]';
			return $message;
		}
		
		$warchest_reqs = $this->calculateWarchest($nation, $cities);
		$start_msg = '[li]Your current warchest does not meet our minimum standards.[list]';
		$message = '';
		$empty = true;
		if($nation->warchest['food'] < $warchest_reqs['food']){
			if($empty){
				$message = $start_msg;
				$empty = false;
			}
			$message .= '[li]You have less than your minimum required amount of food - ' . number_format($warchest_reqs['food'], 2) . '[/li]';
		}
		if($nation->warchest['uranium'] < $warchest_reqs['uranium']){
			if($empty){
				$message = $start_msg;
				$empty = false;
			}
			$message .= '[li]You have less than your minimum required amount of uranium - ' . number_format($warchest_reqs['uranium'], 2) . '[/li]';
		}
		if($nation->warchest['gasoline'] < $warchest_reqs['gas_mun']){
			if($empty){
				$message = $start_msg;
				$empty = false;
			}
			$message .= '[li]You have less than your minimum required amount of gasoline - ' . number_format($warchest_reqs['gas_mun'], 2) . '[/li]';
		}
		if($nation->warchest['munitions'] < $warchest_reqs['gas_mun']){
			if($empty){
				$message = $start_msg;
				$empty = false;
			}
			$message .= '[li]You have less than your minimum required amount of munitions - ' . number_format($warchest_reqs['gas_mun'], 2) . '[/li]';
		}
		if($nation->warchest['steel'] < $warchest_reqs['steel']){
			if($empty){
				$message = $start_msg;
				$empty = false;
			}
			$message .= '[li]You have less than your minimum required amount of steel - ' . number_format($warchest_reqs['steel'], 2) . '[/li]';
		}
		if($nation->warchest['aluminum'] < $warchest_reqs['aluminum']){
			if($empty){
				$message = $start_msg;
				$empty = false;
			}
			$message .= '[li]You have less than your minimum required amount of aluminum - ' . number_format($warchest_reqs['aluminum'], 2) . '[/li]';
		}
		if($nation->warchest['cash'] < $warchest_reqs['cash']){
			if($empty){
				$message = $start_msg;
				$empty = false;
			}
			$message .= '[li]You have less than your minimum required amount of cash - $' . number_format($warchest_reqs['cash'], 2) . '[/li]';
		}
		if(!$empty)
			$message .= '[li]If you are already aware of this and working to correct the issue, please disregard.[/li][/list][/li]';
		
		return $message;
	}
	
	private function military($nation, $cities){
		$message = '';
		$soldiers = array('amount' => count($cities) * 3000, 'percent' => 0);
		$tanks = array('amount' => 0, 'percent' => 0.2);
		$planes = array('amount' => 0, 'percent' => 1);
		$start_msg = '[li]Your current military does not meet our minimum standards.[list]';
		$empty = true;
		
		foreach($cities as $city){
			$infra = $city->infrastructure;
			$improvements = array(
				'barracks' => 1,
				'factories' => 1,
				'hangars' => 1
			);
			
			if($infra >= 1400){
				$improvements['factories'] = 2;
				$improvements['hangars'] = 5;
			}else if($infra >= 1300){
				$improvements['factories'] = 2;
				$improvements['hangars'] = 4;
			}else if($infra >= 1200){
				$improvements['factories'] = 2;
				$improvements['hangars'] = 2;
			}else if($infra >= 1100){
				$improvements['factories'] = 1;
				$improvements['hangars'] = 2;
			}
			$tanks['amount'] += 250 * $improvements['factories'];
			$planes['amount'] += 18 * $improvements['hangars'];
				
			if($city->imp_barracks < $improvements['barracks']){
				if($empty){
					$empty = false;
					$message = $start_msg;
				}
				$singular = $improvements['barracks'] == 1;
				$message .= '[li]You must have at least ' . $improvements['barracks'] . ' barrack' . ($singular ? '' : 's') . ' in your city [url=https://politicsandwar.com/city/id=' . $city->cityid . ']' . $city->name . '.[/url][/li]';
			}
			
			if($city->imp_factory < $improvements['factories']){
				if($empty){
					$empty = false;
					$message = $start_msg;
				}
				$singular = $improvements['factories'] == 1;
				$message .= '[li]You must have at least ' . $improvements['factories'] . ' factor' . ($singular ? 'y' : 'ies') . ' in your city [url=https://politicsandwar.com/city/id=' . $city->cityid . ']' . $city->name . '.[/url][/li]';
			}
			
			if($city->imp_hangar < $improvements['hangars']){
				if($empty){
					$empty = false;
					$message = $start_msg;
				}
				$singular = $improvements['hangars'] == 1;
				$message .= '[li]You must have at least ' . $improvements['hangars'] . ' hangar' . ($singular ? '' : 's') . ' in your city [url=https://politicsandwar.com/city/id=' . $city->cityid . ']' . $city->name . '.[/url][/li]';
			}
		}
		
		$soldiers['amount'] = (int)($soldiers['amount'] * $soldiers['percent']);
		$tanks['amount'] = (int)($tanks['amount'] * $tanks['percent']);
		$planes['amount'] = (int)($planes['amount'] * $planes['percent']);
		
		if($nation->soldiers < $soldiers['amount']){
			if($empty){
				$empty = false;
				$message = $start_msg;
			}
			$message .= '[li]You must have at least ' . $soldiers['amount'] . ' soldiers.[/li]';
		}
		if($nation->tanks < $tanks['amount']){
			if($empty){
				$empty = false;
				$message = $start_msg;
			}
			$message .= '[li]You must have at least ' . $tanks['amount'] . ' tanks.[/li]';
		}
		if($nation->aircraft < $planes['amount']){
			if($empty){
				$empty = false;
				$message = $start_msg;
			}
			$message .= '[li]You must have at least ' . $planes['amount'] . ' planes.[/li]';
		}
		
		if(!$empty)
			$message .= '[/list][/li]';
		
		return $message;
	}
	
	private function city_health($nation, $cities){
		$message = '';
		$start_msg = '[li]Your cities require improvement.[list]';
		$empty = true;
		
		foreach($cities as $city){
			if($city->infrastructure < 1000){
				if($empty){
					$empty = false;
					$message = $start_msg;
				}
				$message .= '[li]Your city [url=https://politicsandwar.com/city/id=' . $city->cityid . ']' . $city->name . '.[/url] should have at least 1,000 infrastructure.[/li]';
			}
			if($city->land < 1500){
				if($empty){
					$empty = false;
					$message = $start_msg;
				}
				$message .= '[li]Your city [url=https://politicsandwar.com/city/id=' . $city->cityid . ']' . $city->name . '.[/url] should have at least 1,500 land.[/li]';
			}
			if($city->powered != 'Yes'){
				if($empty){
					$empty = false;
					$message = $start_msg;
				}
				$message .= '[li]Your city [url=https://politicsandwar.com/city/id=' . $city->cityid . ']' . $city->name . '.[/url] is currently not power![/li]';
			}
			if($city->imp_nuclearpower < 1){
				if($empty){
					$empty = false;
					$message = $start_msg;
				}
				$message .= '[li]Your city [url=https://politicsandwar.com/city/id=' . $city->cityid . ']' . $city->name . '.[/url] is not 100% powered by nuclear power plants. All of your cities should be 100% powered by nucler power plants![/li]';
			}
			if($city->commerce < 100){
				if($empty){
					$empty = false;
					$message = $start_msg;
				}
				$message .= '[li]Your city [url=https://politicsandwar.com/city/id=' . $city->cityid . ']' . $city->name . '.[/url] should have 100% commerce.[/li]';
			}
			if($city->crime > 0){
				if($empty){
					$empty = false;
					$message = $start_msg;
				}
				$message .= '[li]Your city [url=https://politicsandwar.com/city/id=' . $city->cityid . ']' . $city->name . '.[/url] has a high level of crime. Your crime should be 0.[/li]';
			}
			if($city->disease > 1){
				if($empty){
					$empty = false;
					$message = $start_msg;
				}
				$message .= '[li]Your city [url=https://politicsandwar.com/city/id=' . $city->cityid . ']' . $city->name . '.[/url] has a high level of disease. Try to reduce it to 0 if possible.[/li]';
			}
		}
		
		if(!$empty)
			$message .= '[/list][/li]';
		
		return $message;
	}
	
	private function color($nation, $cities){
		return (($nation->color != 'white' && $nation->color != 'beige') ? '[li]Your nation should be in the "white" color unless you are in beige. Please switch to the "white" color as soon as possible.[/li]' : '');
	}
}