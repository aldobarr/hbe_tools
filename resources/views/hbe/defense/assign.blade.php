@extends('layouts.master')
@section('content')
<div class="modal fade" id="warchestModal" tabindex="-1" role="dialog" aria-labelledby="warchestModalLabel">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title" id="warchestModalLabel">Warchest of <span id="leader_name"></span></h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-md-12">
								<ul class="list-group">
									<li class="list-group-item">
										Credits<span id="credits" class="badge"></span>
									</li>
								</ul>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<ul class="list-group">
									<li class="list-group-item">
										Coal<span id="coal" class="badge"></span>
									</li>
									<li class="list-group-item">
										Oil<span id="oil" class="badge"></span>
									</li>
									<li class="list-group-item">
										Lead<span id="lead" class="badge"></span>
									</li>
									<li class="list-group-item">
										Iron<span id="iron" class="badge"></span>
									</li>
									<li class="list-group-item">
										Bauxite<span id="bauxite" class="badge"></span>
									</li>
									<li class="list-group-item">
										Food<span id="food" class="badge"></span>
									</li>
								</ul>
							</div>
							<div class="col-md-6">
								<ul class="list-group">
									<li class="list-group-item">
										Uranium<span id="uranium" class="badge"></span>
									</li>
									<li class="list-group-item">
										Gasoline<span id="gasoline" class="badge"></span>
									</li>
									<li class="list-group-item">
										Munitions<span id="munitions" class="badge"></span>
									</li>
									<li class="list-group-item">
										Steel<span id="steel" class="badge"></span>
									</li>
									<li class="list-group-item">
										Aluminum<span id="aluminum" class="badge"></span>
									</li>
									<li class="list-group-item">
										Cash<span class="badge">$<span id="cash"></span></span>
									</li>
								</ul>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					</div>
				</div>
			</div>
		</div>
		<div class="container-fluid">
			<div class="panel panel-default">
				<div class="panel-body table-responsive" id="target_table">
					<div class="text-center"><h1>Target</h1></div>
					<table class="table table-hover">
						<thead>
							<tr>
								<th>Nation Name</th>
								<th>Leader Name</th>
								<th>Soldiers</th>
								<th>Tanks</th>
								<th>Planes</th>
								<th>Ships</th>
								<th>Missiles</th>
								<th>Nukes</th>
								<th>Score</th>
								<th>Cities</th>
								<th>Infrastructure</th>
								<th>Days Inactive</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>{!! HTML::link('https://politicsandwar.com/nation/id=' . $target->nationid, $target->name, array('target' => '_blank')) !!}</td>
								<td>{!! HTML::link('https://politicsandwar.com/inbox/message/receiver=' . urlencode($target->leadername), $target->leadername, array('target' => '_blank')) !!}</td>
								<td>{{ $target->soldiers }}</td>
								<td>{{ $target->tanks }}</td>
								<td>{{ $target->aircraft }}</td>
								<td>{{ $target->ships }}</td>
								<td>{{ $target->missiles }}</td>
								<td>{{ $target->nukes }}</td>
								<td>{{ $target->score }}</td>
								<td>{{ $target->cities }}</td>
								<td>{{ $target->totalinfrastructure }}</td>
								<td><span{!! ($target->minutessinceactive >= 7 && $target->minutessinceactive < 14 ? ' style="background-color: rgba(255, 255, 0, 0.5); width: 100%; height: 100%; display: block;"' : ($target->minutessinceactive >= 14 ? ' style="background-color: rgba(255, 0, 0, 0.5); width: 100%; height: 100%; display: block;"' : '')) !!}>{{ $target->minutessinceactive }}</span></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<div class="container-fluid">
			<form action="/defense/target/assign" method="POST" class="form col-md-12 center-block">
				{!! csrf_field() !!}
				<div class="panel panel-default">
					<div class="panel-body{{ !$empty ? ' table-responsive' : '' }}" id="members_table">
						@if($empty)
						<div style="text-align: center;">We have no members in range.</div>
						@else
						<table class="table table-hover">
							<thead>
								<tr>
									<th>Nation Name</th>
									<th>Leader Name</th>
									<th>Soldiers</th>
									<th>Tanks</th>
									<th>Planes</th>
									<th>Ships</th>
									<th>Missiles</th>
									<th>Nukes</th>
									<th>Score</th>
									<th>Cities</th>
									<th>Infrastructure</th>
									<th>Warchests</th>
									<th>Days Inactive</th>
									<th>Last Signed In</th>
									<th></th>
								</tr>
							</thead>
							<tbody>
							@foreach($nations as $nation)
								<tr>
									<td>{!! HTML::link('https://politicsandwar.com/nation/id=' . $nation['nation_id'], $nation['nation'], array('target' => '_blank')) !!}</td>
									<td>{!! HTML::link('https://politicsandwar.com/inbox/message/receiver=' . urlencode($nation['leader']), $nation['leader'], array('target' => '_blank')) !!}</td>
									<td>{{ $nation['soldiers'] }}</td>
									<td>{{ $nation['tanks'] }}</td>
									<td>{{ $nation['planes'] }}</td>
									<td>{{ $nation['ships'] }}</td>
									<td>{{ $nation['missiles'] }}</td>
									<td>{{ $nation['nukes'] }}</td>
									<td>{{ $nation['score'] }}</td>
									<td>{{ $nation['cities'] }}</td>
									<td>{{ $nation['infra'] }}</td>
									<td><a href="#" nation-id="{{ $nation['nation_id'] }}" class="warchest-link" data-toggle="modal" data-target="#warchestModal"><span class="glyphicon glyphicon-list-alt"></span></a></td>
									<td><span{!! ($nation['inactive'] >= 7 && $nation['inactive'] < 14 ? ' style="background-color: rgba(255, 255, 0, 0.5); width: 100%; height: 100%; display: block;"' : ($nation['inactive'] >= 14 ? ' style="background-color: rgba(255, 0, 0, 0.5); width: 100%; height: 100%; display: block;"' : '')) !!}>{{ $nation['inactive'] }}</span></td>
									<td>{!! empty($nation['time']) ? '<strong>Never</strong>' : $nation['time'] !!}</td>
									<td><input type="checkbox" name="nation_ids[]" class="selected_members" value="{{ $nation['nation_id'] }}"></td>
								</tr>
							@endforeach
							</tbody>
						</table>
						@endif
					</div>
				</div>
				<div class="pull-right">
					<input id="submit-planning" name="submit_planning" type="submit" class="btn btn-primary" value="Assign Planning">
					<input id="submit-live" name="submit_live" type="submit" class="btn btn-primary" value="Assign Live">
				</div>
				<input name="target" type="hidden" value="{{ $target_id }}">
			</form>
		</div>
		<script>
			function addCommas(nStr)
			{
				nStr += '';
				x = nStr.split('.');
				x1 = x[0];
				x2 = x.length > 1 ? '.' + x[1] : '';
				var rgx = /(\d+)(\d{3})/;
				while (rgx.test(x1)) {
					x1 = x1.replace(rgx, '$1' + ',' + '$2');
				}
				return x1 + x2;
			}
			$(document).ready(function(){
				var nation_data = {!! json_encode($nations) !!};
				$(".warchest-link").click(function(){
					var id = $(this).attr("nation-id");
					var nation = nation_data[id];
					$("#leader_name").text(nation["leader"]);
					$("#credits").text(nation["credits"]);
					$("#coal").text(addCommas(nation["coal"]));
					$("#oil").text(addCommas(nation["oil"]));
					$("#uranium").text(addCommas(nation["uranium"]));
					$("#lead").text(addCommas(nation["lead"]));
					$("#iron").text(addCommas(nation["iron"]));
					$("#bauxite").text(addCommas(nation["bauxite"]));
					$("#gasoline").text(addCommas(nation["gasoline"]));
					$("#munitions").text(addCommas(nation["munitions"]));
					$("#steel").text(addCommas(nation["steel"]));
					$("#aluminum").text(addCommas(nation["aluminum"]));
					$("#food").text(addCommas(nation["food"]));
					$("#cash").text(addCommas(nation["cash"]));
				});
			});
		</script>
@stop