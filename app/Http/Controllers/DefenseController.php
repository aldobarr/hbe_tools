<?php

namespace App\Http\Controllers;

use Auth;
use AuthHelper;
use Artisan;
use DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DefenseController extends Controller
{
	use HBEFunctions;
	use \App\Console\Commands\CurlFunctions;
	
	public function targets(){
		if(!AuthHelper::canAccess('assign_targets'))
			return view('hbe.access_denied');
		
		return view('hbe.defense.targets');
	}
	
	public function targetMatch(Request $request){
		if(!AuthHelper::canAccess('assign_targets'))
			return view('hbe.access_denied');
		
		$this->validate($request, [
            'id' => 'required|integer|min:1'
        ]);
		
		$id = $request->input('id');
		$target_data = json_decode($this->getPage('https://politicsandwar.com/api/nation/id=' . $id));
		$target_data->minutessinceactive = round((((int)$target_data->minutessinceactive) / 1440), 2);
		$range = (double)$target_data->score;
		$range_min = $range / 1.75;
		$range_max = $range / 0.75;
		$fix = $this->fixColumns();
		$nations = DB::table('nation_stats')->whereBetween('score', [$range_min, $range_max])->orderBy('score', 'desc')->leftJoin('warchests', 'nation_stats.nation_id', '=', 'warchests.id')->get($fix);
		
		$fixed_nations = array();
		foreach($nations as $nation){
			$nation['inactive'] = round((((int)$nation['inactive']) / 1440), 2);
			if(!empty($nation['id']))
				$nation['time'] = $this->hbe_time_format($nation['time']);
			$fixed_nations[$nation['nation_id']] = $nation;
		}
		
		return view('hbe.defense.assign', ['nations' => $fixed_nations, 'target' => $target_data, 'target_id' => $id, 'empty' => empty($fixed_nations)]);
	}
	
	public function targetAssign(Request $request){
		global $context;
		
		if(!AuthHelper::canAccess('assign_targets'))
			return view('hbe.access_denied');
		
		$this->validate($request, [
			'target' => 'required|integer|min:1',
            'nation_ids' => 'required|array'
        ]);
		
		$live = $request->has('submit_live');
		$target = $request->input('target');
		$nation_ids = $request->input('nation_ids');
		$target_data = json_decode($this->getPage('https://politicsandwar.com/api/nation/id=' . $target));
		
		$subject = '[' . $target_data->alliance . '] - ' . $target_data->name . ' - ' . $target_data->score;
		$body = '[b]Name/Link:[/b] [url=https://politicsandwar.com/nation/id=' . $target . ']' . $target_data->leadername . '[/url]
[b]Alliance:[/b] ' . ($target_data->allianceid > 0 ? ('[url=https://politicsandwar.com/alliance/id=' . $target_data->allianceid . ']') : '') . $target_data->alliance . ($target_data->allianceid > 0 ? '[/url]' : '') . '
[b]Score:[/b] ' . $target_data->score . '
[color=green][b]Assigned Britannians:[/b][/color]
[list]';

		$recipient = array('to' => array(), 'bcc' => array());
		$assignee_nations = array();
		
		foreach($nation_ids as $id){
			$id = (int)$id;
			if($id < 1)
				return view('hbe.access_denied');
			
			$to_id = $this->findRecipient($id, true);
			$assignee = json_decode($this->getPage('https://politicsandwar.com/api/nation/id=' . $id), true);
			if(!$assignee['success'])
				continue;
			
			$assignee['to_id'] = $to_id;
			$assignee_nations[$id] = $assignee;
			
			if(!empty($to_id))
				$recipient['to'][] = $to_id;
			
			$body .= '
[li]' . $this->getForumName($to_id, $assignee['leadername']) . '[/li]';
		}
		$body .= '
[/list]';

		$topic_id = $this->createTargetPost($subject, $body, $live);
		$subject = 'Urgent Target Assignment';
		$body = 'Greetings {LEADER},

You\'ve just been assigned a new target in a current war. For more details, please view the target thread which can be found here:

[url=https://pnw.britannianempire.net/index.php?topic=' . $topic_id . '.0]https://pnw.britannianempire.net/index.php?topic=' . $topic_id . '.0[/url]

Also, it is of great importance that you come online at our Discord Channel. You can find it here: [url=https://discord.gg/NPGwZsC]https://discord.gg/NPGwZsC[/url]
Once there, make sure to contact myself or another officer of the Ministry of Defense, so that we can work for better coordination with the war effort.

Regards,
' . $context['user']['name'];

		$sender = array(
			'id' => $context['user']['id'],
			'name' => $context['user']['name'],
			'username' => $context['user']['username'],
		);
		
		if($live){
			sendpm($recipient, $subject, str_ireplace(' {LEADER}', '', $body), true, $sender);
			
			$logged_in = $this->checkLoggedIn();
			if($logged_in === 2)
				return view('hbe.success_message', ['message' => 'Britannians have been assigned, however we failed to login to the game to send the in game PMs.']);
			if(!$logged_in){
				$this->login();
				$logged_in = $this->checkLoggedIn();
				if($logged_in === 2 || !$logged_in)
					return view('hbe.success_message', ['message' => 'Britannians have been assigned, however we were unable to login to the game to send the in game PMs.']);
			}
			
			foreach($assignee_nations as $id => $nation)
				$this->sendMessage($nation, $subject, $body);
		}else{
			DB::table('war_planning_threads')->insert([
				'target_id' => $target,
				'target_name' => $target_data->name,
				'target_leader' => $target_data->leadername,
				'target_aa' => $target_data->alliance,
				'target_aa_id' => $target_data->allianceid,
				'data' => json_encode($assignee_nations),
				'thread_id' => $topic_id,
			]);
		}
		return view('hbe.success_message', ['message' => 'Britannians have been successfully assigned to the selected target, and the target thread has been created.' . ($live ? '<br>Assignees have been PM\'d their assignment.' : ''), 'safe' => true]);
	}
	
	public function targetActivateForm(){
		if(!AuthHelper::canAccess('assign_targets'))
			return view('hbe.access_denied');
		
		$plannings = DB::table('war_planning_threads')->where('moved', 0)->get();
		$update = array();
		foreach($plannings as $id => $planning){
			$thread = $planning['thread_id'];
			if(!$this->checkThread($thread)){
				$update[] = $planning['id'];
				unset($plannings[$id]);
			}
		}
		DB::table('war_planning_threads')->whereIn('id', $update)->update(['moved' => 1]);
		return view('hbe.defense.activate', ['plannings' => $plannings, 'empty' => empty($plannings)]);
	}
	
	private function checkThread($thread){
		global $smcFunc;
		
		$query = $smcFunc['db_query']('', '
			SELECT id_board
			FROM {db_prefix}topics
			WHERE id_topic = {int:thread}
			LIMIT 1',
			array(
				'thread' => $thread
			)
		);
		
		$temp = $smcFunc['db_fetch_assoc']($query);
		$smcFunc['db_free_result']($query);
		if(empty($temp) || empty($temp['id_board']))
			return false;
		return ($temp['id_board'] == 60);
	}
	
	public function targetActivate(Request $request){
		global $context;
		
		if(!AuthHelper::canAccess('assign_targets'))
			return view('hbe.access_denied');
		
		$this->validate($request, [
			'planning_ids' => 'required|array',
        ]);
		
		$planning_ids = $request->input('planning_ids');
		$plannings = DB::table('war_planning_threads')->whereIn('id', $planning_ids)->get();
		$subject = 'Urgent Target Assignment';
		$body = 'Greetings {LEADER},

You\'ve just been assigned a new target in a current war. For more details, please view the target thread which can be found here:

[url=https://pnw.britannianempire.net/index.php?topic=%d.0]https://pnw.britannianempire.net/index.php?topic=%d.0[/url]

Also, it is of great importance that you come online at our Discord Channel. You can find it here: [url=https://discord.gg/NPGwZsC]https://discord.gg/NPGwZsC[/url]
Once there, make sure to contact myself or another officer of the Ministry of Defense, so that we can work for better coordination with the war effort.

Regards,
' . $context['user']['name'];
		$sender = array(
			'id' => $context['user']['id'],
			'name' => $context['user']['name'],
			'username' => $context['user']['username'],
		);
		
		foreach($plannings as $planning){
			$recipient = array('to' => array(), 'bcc' => array());
			$data = json_decode($planning['data'], true);
			foreach($data as $assignee)
				$recipient['to'][] = $assignee['to_id'];
			$this->activateThread($planning['thread_id']);
			sendpm($recipient, $subject, str_ireplace(' {LEADER}', '', sprintf($body, $planning['thread_id'], $planning['thread_id'])), true, $sender);
		}
		
		DB::table('war_planning_threads')->whereIn('id', $planning_ids)->update(['moved' => 1]);
		
		$logged_in = $this->checkLoggedIn();
		if($logged_in === 2)
			return view('hbe.success_message', ['message' => 'Britannians have been messaged on the forum and target threads moved to live, however we failed to login to the game to send the in game PMs.']);
		if(!$logged_in){
			$this->login();
			$logged_in = $this->checkLoggedIn();
			if($logged_in === 2 || !$logged_in)
				return view('hbe.success_message', ['message' => 'Britannians have been messaged on the forum and target threads moved to live, however we were unable to login to the game to send the in game PMs.']);
		}
		foreach($plannings as $planning){
			$data = json_decode($planning['data'], true);
			$temp_body = sprintf($body, $planning['thread_id'], $planning['thread_id']);
			foreach($data as $assignee)
				$this->sendMessage($assignee, $subject, $temp_body);
		}
		
		return view('hbe.success_message', ['message' => 'Target threads have been moved to live and Britannians messaged.']);
	}
	
	private function activateThread($thread_id){
		global $smcFunc;
		
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}topics
			SET id_board = {int:fifteen}
			WHERE id_topic = {int:topic}',
			array(
				'fifteen' => 15,
				'topic' => $thread_id
			)
		);
	}
	
	private function sendMessage($nation, $subject, $body){
		$post_url = 'https://politicsandwar.com/inbox/message/receiver=' . urlencode($nation['leadername']);
		
		$variables = array('{LEADER}', '{NATION}', '{COLOR}', '{CONTINENT}');
		$values = array($nation['leadername'], $nation['name'], $nation['color'], $nation['continent']);
		
		$params = array(
			'newconversation' => 'true',
			'receiver' => $nation['leadername'],
			'carboncopy' => '',
			'subject' => str_ireplace($variables, $values, $subject),
			'body' => str_ireplace($variables, $values, $body),
			'sndmsg' => 'Send Message'
		);

		$result = $this->getPage($post_url, true, $params);
		if($result === FALSE)
			return false;
		
		return (stristr($result, 'Message Sent') ? true : false);
	}
	
	private function createTargetPost($subject, $body, $live){
		global $context, $smcFunc;
		
		$msg = $smcFunc['htmlspecialchars']($body, ENT_QUOTES);
		$msgOptions = array(
			'body' => $msg,
			'id' => 0,
			'subject' => $subject,
		);
		$topicOptions = array(
			'id' => 0,
			'board' => $live ? 15 : 60,
			'mark_as_read' => '0',
		);
		$posterOptions = array(
			'id' => $context['user']['id'],
			'name' => $context['user']['name'],
			'update_post_count' => true,
		);
		
		createPost($msgOptions, $topicOptions, $posterOptions);
		
		return $topicOptions['id'];
	}

	public function raidPrevention(){
		if(!AuthHelper::canAccess('raid_prevention'))
			return view('hbe.access_denied');
		
		$is_protected = DB::table('misc_data')->where('key', 'antiraid')->value('value') == '1';
		$available = array(
			'cash' => 0.0,
			'food' => 0.0,
			'coal' => 0.0,
			'oil' => 0.0,
			'uranium' => 0.0,
			'lead' => 0.0,
			'iron' => 0.0,
			'bauxite' => 0.0,
			'gasoline' => 0.0,
			'munitions' => 0.0,
			'steel' => 0.0,
			'aluminum' => 0.0,
		);

		if($is_protected){
			$anti_raid_data = DB::table('anti_raid_transactions')->where('returned', 0)->get();
			foreach($anti_raid_data as $data){
				foreach($data as $key => $val){
					if(array_key_exists($key, $available))
						$available[$key] += $val;
				}
			}
		}

		return view('hbe.defense.raid_prevention', ['is_protected' => $is_protected, 'available' => $available]);
	}

	public function raidPreventionToggle(){
		if(!AuthHelper::canAccess('raid_prevention'))
			return view('hbe.access_denied');

		$is_protected = DB::table('misc_data')->where('key', 'antiraid')->value('value') == '1';
		
		if($is_protected)
			Artisan::call('raidprevention:finish', []);
		else
			Artisan::call('raidprevention:start', []);
		
		return redirect('raid/prevention');
	}

	public function raidPreventionWithdraw(Request $request){
		if(!AuthHelper::canAccess('raid_prevention'))
			return view('hbe.access_denied');
		
		$is_protected = DB::table('misc_data')->where('key', 'antiraid')->value('value') == '1';
		if(!$is_protected)
			return view('hbe.error_message', ['safe' => true, 'message' => 'It is not possible to withdraw while raid prevention is turned off.']);

		$anti_raid_data = DB::table('anti_raid_transactions')->where('returned', 0)->get();
		$available = array(
			'cash' => 0.0,
			'food' => 0.0,
			'coal' => 0.0,
			'oil' => 0.0,
			'uranium' => 0.0,
			'lead' => 0.0,
			'iron' => 0.0,
			'bauxite' => 0.0,
			'gasoline' => 0.0,
			'munitions' => 0.0,
			'steel' => 0.0,
			'aluminum' => 0.0,
		);
		foreach($anti_raid_data as $data){
			foreach($data as $key => $val){
				if(array_key_exists($key, $available))
					$available[$key] += $val;
			}
		}

		$this->validate($request, [
			'cash' => 'required|numeric|min:0|max:' . $available['cash'],
			'food' => 'required|numeric|min:0|max:' . $available['food'],
			'coal' => 'required|numeric|min:0|max:' . $available['coal'],
			'oil' => 'required|numeric|min:0|max:' . $available['oil'],
			'uranium' => 'required|numeric|min:0|max:' . $available['uranium'],
			'lead' => 'required|numeric|min:0|max:' . $available['lead'],
			'iron' => 'required|numeric|min:0|max:' . $available['iron'],
			'bauxite' => 'required|numeric|min:0|max:' . $available['bauxite'],
			'gasoline' => 'required|numeric|min:0|max:' . $available['gasoline'],
			'munitions' => 'required|numeric|min:0|max:' . $available['munitions'],
			'steel' => 'required|numeric|min:0|max:' . $available['steel'],
			'aluminum' => 'required|numeric|min:0|max:' . $available['aluminum'],
		]);

		$deposit = array(
			'cash' => $request->input('cash'),
			'food' => $request->input('food'),
			'coal' => $request->input('coal'),
			'oil' => $request->input('oil'),
			'uranium' => $request->input('uranium'),
			'lead' => $request->input('lead'),
			'iron' => $request->input('iron'),
			'bauxite' => $request->input('bauxite'),
			'gasoline' => $request->input('gasoline'),
			'munitions' => $request->input('munitions'),
			'steel' => $request->input('steel'),
			'aluminum' => $request->input('aluminum'),
		);
		
		$ch = $this->getNewCurl(false, true);
		$logged_in = $this->checkLoggedIn($ch);
		if($logged_in === 2){
			$this->closeCurlResource($ch);
			return view('hbe.error_message', ['safe' => true, 'message' => 'Unable to check logged in state.']);
		}
		if(!$logged_in){
			$this->login('email@domain.com', 'pass', $ch);
			$logged_in = $this->checkLoggedIn($ch);
			if($logged_in === 2 || !$logged_in){
				$this->closeCurlResource($ch);
				return view('hbe.error_message', ['safe' => true, 'message' => 'Unable to login.']);
			}
		}

		$bank_page = 'https://politicsandwar.com/alliance/id=4348&display=bank';
		$bank_page_data = $this->getPageWithResource($ch, $bank_page);
		$token = $this->getToken($bank_page_data);
		if(empty($token))
			return view('hbe.error_message', ['safe' => true, 'message' => 'Unable to obtain bank token.']);
		
		$params = array(
			'withmoney' => $deposit['cash'],
			'withfood' => $deposit['food'],
			'withcoal' => $deposit['coal'],
			'withoil' => $deposit['oil'],
			'withuranium' => $deposit['uranium'],
			'withlead' => $deposit['lead'],
			'withiron' => $deposit['iron'],
			'withbauxite' => $deposit['bauxite'],
			'withgasoline' => $deposit['gasoline'],
			'withmunitions' => $deposit['munitions'],
			'withsteel' => $deposit['steel'],
			'withaluminum' => $deposit['aluminum'],
			'withtype' => 'Alliance',
			'withrecipient' => 'Holy Britannian Empire',
			'withnote' => '',
			'withsubmit' => 'Withdraw',
			'token' => $token,
		);

		$results = $this->getPageWithResource($ch, $bank_page, true, $params);
		$this->closeCurlResource($ch);
		if(!stristr($results, 'You successfully transferred funds from the alliance bank'))
			return view('hbe.error_message', ['safe' => true, 'message' => 'Bank transaction failed.']);

		foreach($deposit as $key => $val){
			$available[$key] -= $val;
			$deposit[$key] = ($val * -1);
		}

		DB::table('anti_raid_transactions')->insert($deposit);
		return view('hbe.defense.raid_prevention', ['is_protected' => $is_protected, 'available' => $available, 'deposit' => $deposit]);
	}
	
	private function getToken($data){
		$dom = new \DOMDocument;
		@$dom->loadHTML($data);
		$inputs = $dom->getElementsByTagName('input');
		foreach($inputs as $input){
			if($input->getAttribute('name') == 'token'){
				return $input->getAttribute('value');
			}
		}
		return false;
	}

	public function readiness(){
		if(!AuthHelper::canAccess('view_world_military_stats'))
			return view('hbe.access_denied');
		
		$nations = DB::table('world_military_stats')->get();
		$alliances = array();
		foreach($nations as $nation){
			if(!array_key_exists($nation['alliance_id'], $alliances)){
				$alliance = DB::table('alliances')->where('id', $nation['alliance_id'])->get();
				if(empty($alliance) || empty($alliance[0]))
					continue;
				
				$alliance = $alliance[0];
				$alliances[$nation['alliance_id']] = array(
					'id' => $nation['alliance_id'],
					'name' => $alliance['name'],
					'label' => $alliance['label'],
					'size' => $alliance['size'],
					'nations' => array(),
					'soldiers' => 0,
					'max_soldiers' => 0,
					'psoldiers' => '',
					'tanks' => 0,
					'max_tanks' => 0,
					'ptanks' => '',
					'planes' => 0,
					'max_planes' => 0,
					'pplanes' => '',
					'ships' => 0,
					'max_ships' => 0,
					'pships' => '',
					'nukes' => 0,
					'inactive' => 0,
					'cnations' => 0
				);
			}
			
			$nation['max_soldiers'] = $nation['cities'] * 15000;
			$nation['max_tanks'] = $nation['cities'] * 1250;
			$nation['max_planes'] = $nation['cities'] * 90;
			$nation['max_ships'] = $nation['cities'] * 15;
			$nation['psoldiers'] = number_format(($nation['soldiers'] / $nation['max_soldiers']) * 100, 2) . '%';
			$nation['ptanks'] = number_format(($nation['tanks'] / $nation['max_tanks']) * 100, 2) . '%';
			$nation['pplanes'] = number_format(($nation['planes'] / $nation['max_planes']) * 100, 2) . '%';
			$nation['pships'] = number_format(($nation['ships'] / $nation['max_ships']) * 100, 2) . '%';
			$nation['nukes'] = $nation['nukes'];//$this->calcNukes($nation);
			$nation['inactive'] = round((((int)$nation['inactive']) / 1440), 2);
			$nation['pos'] = $nation['alliance_pos'];
			$nation['alliance_pos_img'] = $nation['alliance_pos'] > 2 ? ($nation['alliance_pos'] == 3 ? 'officer_25.png' : ($nation['alliance_pos'] == 4 ? 'heir_25.png' : 'leader_25.png')) : '';
			$nation['alliance_pos'] = $nation['alliance_pos'] > 2 ? ($nation['alliance_pos'] == 3 ? 'Officer' : ($nation['alliance_pos'] == 4 ? 'Heir' : 'Leader')) : '';
			$alliances[$nation['alliance_id']]['nations'][] = $nation;
			$alliances[$nation['alliance_id']]['soldiers'] += $nation['soldiers'];
			$alliances[$nation['alliance_id']]['max_soldiers'] += $nation['max_soldiers'];
			$alliances[$nation['alliance_id']]['tanks'] += $nation['tanks'];
			$alliances[$nation['alliance_id']]['max_tanks'] += $nation['max_tanks'];
			$alliances[$nation['alliance_id']]['planes'] += $nation['planes'];
			$alliances[$nation['alliance_id']]['max_planes'] += $nation['max_planes'];
			$alliances[$nation['alliance_id']]['ships'] += $nation['ships'];
			$alliances[$nation['alliance_id']]['max_ships'] += $nation['max_ships'];
			$alliances[$nation['alliance_id']]['inactive'] += $nation['inactive'];
			$alliances[$nation['alliance_id']]['nukes'] += $nation['nukes'];
			$alliances[$nation['alliance_id']]['cnations']++;
		}
		
		foreach($alliances as $id => $alliance){
			$alliances[$id]['psoldiers'] = number_format(($alliance['soldiers'] / $alliance['max_soldiers']) * 100, 2) . '%';
			$alliances[$id]['ptanks'] = number_format(($alliance['tanks'] / $alliance['max_tanks']) * 100, 2) . '%';
			$alliances[$id]['pplanes'] = number_format(($alliance['planes'] / $alliance['max_planes']) * 100, 2) . '%';
			$alliances[$id]['pships'] = number_format(($alliance['ships'] / $alliance['max_ships']) * 100, 2) . '%';
			$alliances[$id]['inactive'] = round((((int)($alliance['inactive'] / $alliance['cnations'])) / 1440), 2);
			usort($alliances[$id]['nations'], function($a, $b){
				return $b['pos'] - $a['pos'];
			});
		}
		
		usort($alliances, function($a, $b){
			return $b['size'] - $a['size'];
		});
		
		$last_run = $this->hbe_time_format(DB::table('cron_last_run')->where('id', 'world_military_stats')->value('time'));
		return view('hbe.defense.readiness', ['alliances' => $alliances, 'last_run' => $last_run]);
	}
	
	private function calcNukes($nation){
		$infra_score = $nation['infra'] / 40; //514.675
		$city_score = ($nation['cities'] - 1) * 50; //650
		$project_score = $nation['projects'] * 20; //80
		//105 + 850 + 630 + 0 + 0
		$mil_score_no_nukes = ($nation['soldiers'] * 0.0005) + ($nation['tanks'] * 0.05) + ($nation['planes'] * 0.5) + ($nation['ships'] * 2) + ($nation['missiles'] * 5);
		$score_no_nukes = $infra_score + $city_score + $project_score + $mil_score_no_nukes;
		return ((int)(($nation['score'] - $score_no_nukes) / 15));
	}
	
	private function getColumn($sort, $default){
		$table_fields = DB::select('DESCRIBE `nation_stats`');
		foreach($table_fields as $column)
			if($sort == $column['Field'])
				return $column['Field'];
		return $default;
	}
	
	private function fixColumns(){
		$columns = array();
		$table_fields = DB::select('DESCRIBE `nation_stats`');
		foreach($table_fields as $column)
			$columns[] = 'nation_stats.' . $column['Field'] . ' as ' . $column['Field'];
		$table_fields = DB::select('DESCRIBE `warchests`');
		foreach($table_fields as $column)
			$columns[] = 'warchests.' . $column['Field'] . ' as ' . $column['Field'];
		return $columns;
	}
}
