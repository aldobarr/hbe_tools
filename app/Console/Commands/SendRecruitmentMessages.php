<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Cache;
use DB;

class SendRecruitmentMessages extends Command
{
	use CurlFunctions;
	
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'schedule:sendrecruitmentmessages';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Import the CN stats.';
	
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
		if(Cache::has(md5($this->signature)))
			return;
		
		Cache::put(md5($this->signature), true, 20);
		$this->executeCommand();
		Cache::forget(md5($this->signature));
	}
	
	private function executeCommand(){
		$messages_recruit_noob = DB::table('recruitment_messages')->where('active', 1)->where('type', 1)->get();
		$messages_recruit_exp = DB::table('recruitment_messages')->where('active', 1)->where('type', 3)->get();
		$messages_remind = DB::table('recruitment_messages')->where('active', 1)->where('type', 2)->get();
		$messages_toaa = DB::table('send_pm')->where('sent', 0)->get();
		
		if(empty($messages_recruit_noob) && empty($messages_remind) && empty($messages_recruit_exp) && empty($messages_toaa))
			return;
		
		$day = 86400;
		$month = time() + ($day * 30);
		$week = time() + ($day * 7);
		$messaged_recruit = DB::table('recruitment_messaged')->where('time', '>', time())->whereIn('type', [1, 3])->pluck('id');
		$messaged_remind = DB::table('recruitment_messaged')->where('time', '>', time())->where('type', 2)->pluck('id');
		$to_message = array('recruit' => array(), 'remind' => array(), 'mass_pm' => array());
		$nations = json_decode($this->getPage('https://politicsandwar.com/api/nations/'))->nations;
		
		foreach($nations as $nation){
			if(empty($nation->allianceid)){
				if(in_array($nation->nationid, $messaged_recruit))
					continue;
				$to_message['recruit'][] = $nation;
			}else if($nation->allianceid == $this->alliance_id && $this->isApplicant($nation)){
				if(in_array($nation->nationid, $messaged_remind))
					continue;
				$to_message['remind'][] = $nation;
			}else if($nation->allianceid == $this->alliance_id)
				$to_message['mass_pm'][] = $nation;
		}
		if(empty($to_message['recruit']) && empty($to_message['remind']) && empty($to_message['mass_pm']))
			return;
		
		$logged_in = $this->checkLoggedIn();
		if($logged_in === 2)
			return;
		if(!$logged_in)
			$this->login();
		
		if(!empty($messages_recruit_noob) || !empty($messages_recruit_exp)){
			foreach($to_message['recruit'] as $nation){
				$is_noob = $this->isNoob($nation);
				if($is_noob && !empty($messages_recruit_noob)){
					if($this->sendMessage($nation, $messages_recruit_noob)){
						$exists = DB::table('recruitment_messaged')->where('id', $nation->nationid)->where('type', [1, 3])->first();
						if(empty($exists))
							DB::table('recruitment_messaged')->insert(['id' => $nation->nationid, 'time' => $month, 'type' => 1]);
						else
							DB::table('recruitment_messaged')->where('id', $nation->nationid)->where('type', 1)->update(['time' => $month, 'type' => 1]);
						
						$exists = DB::table('recruitment_messaged')->where('id', $nation->nationid)->where('type', 2)->first();
						if(empty($exists))
							DB::table('recruitment_messaged')->insert(['id' => $nation->nationid, 'time' => time() + $day, 'type' => 2]);
						else
							DB::table('recruitment_messaged')->where('id', $nation->nationid)->where('type', 2)->update(['time' => time() + $day]);
					}
				}else if(!$is_noob && !empty($messages_recruit_exp)){
					if($this->sendMessage($nation, $messages_recruit_exp)){
						$exists = DB::table('recruitment_messaged')->where('id', $nation->nationid)->where('type', [1, 3])->first();
						if(empty($exists))
							DB::table('recruitment_messaged')->insert(['id' => $nation->nationid, 'time' => $month, 'type' => 3]);
						else
							DB::table('recruitment_messaged')->where('id', $nation->nationid)->where('type', 1)->update(['time' => $month, 'type' => 3]);
						
						$exists = DB::table('recruitment_messaged')->where('id', $nation->nationid)->where('type', 2)->first();
						if(empty($exists))
							DB::table('recruitment_messaged')->insert(['id' => $nation->nationid, 'time' => time() + $day, 'type' => 2]);
						else
							DB::table('recruitment_messaged')->where('id', $nation->nationid)->where('type', 2)->update(['time' => time() + $day]);
					}
				}
			}
		}
		if(!empty($messages_remind)){
			foreach($to_message['remind'] as $nation){
				if($this->sendMessage($nation, $messages_remind)){
					$exists = DB::table('recruitment_messaged')->where('id', $nation->nationid)->where('type', 2)->first();
					if(empty($exists))
						DB::table('recruitment_messaged')->insert(['id' => $nation->nationid, 'time' => $week, 'type' => 2]);
					else
						DB::table('recruitment_messaged')->where('id', $nation->nationid)->where('type', 2)->update(['time' => $week]);
				}
			}
		}
		if(!empty($messages_toaa)){
			foreach($messages_toaa as $message){
				foreach($to_message['mass_pm'] as $nation){
					$result = $this->sendMessage($nation, array($message), true);
					if(!stristr($result, 'Message Sent'))
						DB::table('error_log')->insert(['error' => 'Mass PM to AA sending failed.' . PHP_EOL . PHP_EOL . $result, 'time' => time()]);
				}
				DB::table('send_pm')->where('id', $message['id'])->update(['sent' => time()]);
			}
		}
	}
	
	private function isApplicant($nation){
		return $nation->allianceposition == 1;
	}
	
	private function isNoob($nation){
		return $nation->cities <= 5;
	}
	
	private function sendMessage($nation, $messages, $return_result = false){
		$message = $messages[0];
		if(count($messages) > 1)
			$message = $messages[rand(0, count($messages) - 1)];
		
		$post_url = 'https://politicsandwar.com/inbox/message/receiver=' . urlencode($nation->leader);
		
		$variables = array('{ID}', '{LEADER}', '{NATION}', '{COLOR}', '{CONTINENT}');
		$values = array($nation->nationid, $nation->leader, $nation->nation, $nation->color, $nation->continent);
		
		$params = array(
			'newconversation' => 'true',
			'receiver' => $nation->leader,
			'carboncopy' => '',
			'subject' => str_ireplace($variables, $values, $message['subject']),
			'body' => str_ireplace($variables, $values, $message['body']),
			'sndmsg' => 'Send Message'
		);

		$result = $this->getPage($post_url, true, $params);
		if($result === FALSE)
			return ($return_result ? $result : false);
		
		return ($return_result ? $result : (stristr($result, 'Message Sent') ? true : false));
	}
}
