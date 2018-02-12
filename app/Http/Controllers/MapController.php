<?php

namespace App\Http\Controllers;

use Auth;
use AuthHelper;
use DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MapController extends Controller
{
	public function map(){
		if(!AuthHelper::canAccess('view_map'))
			return view('hbe.access_denied');
		
		$wars = DB::select('select wm.aggressor, wm.defender, wm.aside, wm.dside, a.name as aname, a.label as alabel, a.size as asize, a.flag as aimage, d.name as dname, d.label as dlabel, d.size as dsize, d.flag as dimage from war_map as wm inner join alliances as a on a.id = wm.aggressor inner join alliances as d on d.id = wm.defender');
		$ids = array();
		$nodes = array();
		$edges = array();
		$count = 0;
		foreach($wars as $war){
			if(!array_key_exists($war['aggressor'], $ids)){
				$ids[$war['aggressor']] = $count;
				$size = round($war['asize'] / 5000);
				if($size == 0)
					$size = 1;
				$nodes[] = array('id' => $count++, 'label' => $war['alabel'], 'size' => $size, 'title' => $war['aname'], 'group' => $war['aside'], 'image' => $war['aimage'], 'shadow' => array('enabled' => true, 'color' => ($war['aside'] == 1 ? '#FF0000' : '#0000FF')));
			}
			if(!array_key_exists($war['defender'], $ids)){
				$ids[$war['defender']] = $count;
				$size = round($war['dsize'] / 5000);
				if($size == 0)
					$size = 1;
				$nodes[] = array('id' => $count++, 'label' => $war['dlabel'], 'size' => $size, 'title' => $war['dname'], 'group' => $war['dside'], 'image' => $war['dimage'], 'shadow' => array('enabled' => true, 'color' => ($war['dside'] == 1 ? '#FF0000' : '#0000FF')));
			}
			$edges[] = array('from' => $ids[$war['aggressor']], 'to' => $ids[$war['defender']], 'color' => ($war['aside'] == 1 ? 'red' : 'blue'), 'length' => 300);
		}
		
		return view('hbe.maps.war', ['network' => true, 'nodes' => $nodes, 'edges' => $edges]);
	}
	
	public function web(){
		if(!AuthHelper::canAccess('view_web'))
			return view('hbe.access_denied');
		
		return view('hbe.maps.treaties');
	}
	
	public function web_raw(){
		if(!AuthHelper::canAccess('view_web'))
			return view('hbe.access_denied');
		
		return view('hbe.maps.treaties_raw', $this->calc_web());
	}
	
	private function calc_web(){
		$treaties = DB::select('select tw.one, tw.two, tw.type, a.name as aname, a.label as alabel, a.size as asize, a.flag as aimage, d.name as dname, d.label as dlabel, d.size as dsize, d.flag as dimage from treaty_web as tw inner join alliances as a on a.id = tw.one inner join alliances as d on d.id = tw.two');
		$colors = array(
			1 => '#bf27e0', // MDAP
            2 => '#c10007', // MDOAP
            3 => '#ced518', // MDP
            4 => '#0d2572', // ODOAP
            5 => '#00aeef', // ODP
            6 => '#65c765', // Protectorate
            7 => '#ffb6c1', // PIAT/NAP
	    8 => '#ffa500', // ODMAP
		);
		$types = array(
			1 => 'MDAP',
            2 => 'MDoAP',
            3 => 'MDP',
            4 => 'ODoAP',
            5 => 'ODP',
            6 => 'Protectorate',
	    7 => 'NAP/PIAT',
	    8 => 'ODMAP',
		);
		$ids = array();
		$nodes = array();
		$edges = array();
		$count = 0;
		foreach($treaties as $treaty){
			if(!array_key_exists($treaty['one'], $ids)){
				$ids[$treaty['one']] = $count;
				$nodes[] = array('id' => $count++, 'label' => $treaty['alabel'], 'size' => ($treaty['asize'] / 5000), 'title' => $treaty['aname'], 'image' => $treaty['aimage']);
			}
			if(!array_key_exists($treaty['two'], $ids)){
				$ids[$treaty['two']] = $count;
				$nodes[] = array('id' => $count++, 'label' => $treaty['dlabel'], 'size' => ($treaty['dsize'] / 5000), 'title' => $treaty['dname'], 'image' => $treaty['dimage']);
			}
			if($treaty['one'] != $treaty['two'])
				$edges[] = array_merge(array('from' => $ids[$treaty['one']], 'to' => $ids[$treaty['two']], 'color' => $colors[$treaty['type']], 'length' => 100, 'title' => $types[$treaty['type']]), ($treaty['type'] == 6 ? array('arrows' => 'to') : array()));
		}
		
		return array('network' => true, 'nodes' => $nodes, 'edges' => $edges);
	}
	
	public function alliances(){
		if(!AuthHelper::canAccess('view_alliances'))
			return view('hbe.access_denied');
		
		$alliances = DB::table('alliances')->get();
		return view('hbe.alliances', ['alliances' => $alliances]);
	}
	
	public function manage(){
		if(!AuthHelper::canAccess('manage_map'))
			return view('hbe.access_denied');
		
		$alliances = DB::table('alliances')->pluck('name', 'id');
		$wars = DB::select('select wm.aggressor, wm.defender, a.name as aname, d.name as dname from war_map as wm inner join alliances as a on a.id = wm.aggressor inner join alliances as d on d.id = wm.defender');
		return view('hbe.maps.manage_map', ['chosen' => true, 'wars' => $wars, 'alliances' => $alliances]);
	}
	
	public function manage_web(){
		if(!AuthHelper::canAccess('manage_web'))
			return view('hbe.access_denied');
		
		$types = array(
			1 => 'MDAP',
            2 => 'MDoAP',
            3 => 'MDP',
            4 => 'ODoAP',
            5 => 'ODP',
            6 => 'Protectorate',
	    7 => 'NAP/PIAT',
	    8 => 'ODMAP',
		);
		$alliances = DB::table('alliances')->pluck('name', 'id');
		$treaties = DB::select('select tw.one, tw.two, tw.type, a.name as aname, a.label as alabel, a.size as asize, a.flag as aimage, d.name as dname, d.label as dlabel, d.size as dsize, d.flag as dimage from treaty_web as tw inner join alliances as a on a.id = tw.one inner join alliances as d on d.id = tw.two');
		return view('hbe.maps.manage_web', ['chosen' => true, 'treaties' => $treaties, 'alliances' => $alliances, 'types' => $types]);
	}
}
