@extends('layouts.master')
@section('content')
<div class="modal fade" id="searchModal" tabindex="-1" role="dialog" aria-labelledby="searchModalLabel">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title" id="searchModalLabel">Filter</h4>
					</div>
					<div class="modal-body">
						<div class="input-group">
							<span class="input-group-addon" id="colSearch">Filter By</span>
							<select class="form-control" id="col" aria-describedby="colSearch">
								<option value="nation">Nation Name</option>
								<option value="leader">Leader Name</option>
								<option value="soldiers|gt">Soldiers Greater Than</option>
								<option value="soldiers|lt">Soldiers Less Than</option>
								<option value="tanks|gt">Tanks Greater Than</option>
								<option value="tanks|lt">Tanks Less Than</option>
								<option value="planes|gt">Planes Greater Than</option>
								<option value="planes|lt">Planes Less Than</option>
								<option value="ships|gt">Ships Greater Than</option>
								<option value="ships|lt">Ships Less Than</option>
								<option value="missiles|gt">Missiles Greater Than</option>
								<option value="missiles|lt">Missiles Less Than</option>
								<option value="nukes|gt">Nukes Greater Than</option>
								<option value="nukes|lt">Nukes Less Than</option>
								<option value="score|gt">Score Greater Than</option>
								<option value="score|lt">Score Less Than</option>
								<option value="cities|gt">Cities Greater Than</option>
								<option value="cities|lt">Cities Less Than</option>
								<option value="infra|gt">Infrastructure Greater Than</option>
								<option value="infra|lt">Infrastructure Less Than</option>
								<option value="city_timer|gt">City Timer Greater Than</option>
								<option value="city_timer|lt">City Timer Less Than</option>
								<option value="inactive|gt">Inactive Greater Than</option>
								<option value="inactive|lt">Inactive Less Than</option>
								<option value="range">War Range</option>
							</select>
						</div><br>
						<div class="input-group">
							<span class="input-group-addon" id="colSearchItem">Filter</span>
							<input type="text" id="item" class="form-control" aria-describedby="colSearchItem">
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
						<button id="submit" type="button" class="btn btn-primary">Submit</button>
					</div>
				</div>
			</div>
		</div>
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
										<div class="row">
											<div class="col-md-3">Coal</div>
											<div class="col-md-4"><div class="progress"><div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">N/A</div></div></div>
											<div class="col-md-5"><span id="coal" class="badge"></span></div>
										</div>
									</li>
									<li class="list-group-item">
										<div class="row">
											<div class="col-md-3">Oil</div>
											<div class="col-md-4"><div class="progress"><div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">N/A</div></div></div>
											<div class="col-md-5"><span id="oil" class="badge"></span></div>
										</div>
									</li>
									<li class="list-group-item">
										<div class="row">
											<div class="col-md-3">Lead</div>
											<div class="col-md-4"><div class="progress"><div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">N/A</div></div></div>
											<div class="col-md-5"><span id="lead" class="badge"></span></div>
										</div>
									</li>
									<li class="list-group-item">
										<div class="row">
											<div class="col-md-3">Iron</div>
											<div class="col-md-4"><div class="progress"><div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">N/A</div></div></div>
											<div class="col-md-5"><span id="iron" class="badge"></span></div>
										</div>
									</li>
									<li class="list-group-item">
										<div class="row">
											<div class="col-md-3">Bauxite</div>
											<div class="col-md-4"><div class="progress"><div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">N/A</div></div></div>
											<div class="col-md-5"><span id="bauxite" class="badge"></span></div>
										</div>
									</li>
									<li class="list-group-item">
										<div class="row">
											<div class="col-md-3">Food</div>
											<div class="col-md-4"><div class="progress"><div class="progress-bar" id="food_progress" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div></div></div>
											<div class="col-md-5"><span id="food" class="badge"></span></div>
										</div>
									</li>
								</ul>
							</div>
							<div class="col-md-6">
								<ul class="list-group">
									<li class="list-group-item">
										<div class="row">
											<div class="col-md-3">Uranium</div>
											<div class="col-md-4"><div class="progress"><div class="progress-bar" id="uranium_progress" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div></div></div>
											<div class="col-md-5"><span id="uranium" class="badge"></span></div>
										</div>
									</li>
									<li class="list-group-item">
										<div class="row">
											<div class="col-md-3">Gasoline</div>
											<div class="col-md-4"><div class="progress"><div class="progress-bar" id="gasoline_progress" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div></div></div>
											<div class="col-md-5"><span id="gasoline" class="badge"></span></div>
										</div>
									</li>
									<li class="list-group-item">
										<div class="row">
											<div class="col-md-3">Munitions</div>
											<div class="col-md-4"><div class="progress"><div class="progress-bar" id="munitions_progress" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div></div></div>
											<div class="col-md-5"><span id="munitions" class="badge"></span></div>
										</div>
									</li>
									<li class="list-group-item">
										<div class="row">
											<div class="col-md-3">Steel</div>
											<div class="col-md-4"><div class="progress"><div class="progress-bar" id="steel_progress" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div></div></div>
											<div class="col-md-5"><span id="steel" class="badge"></span></div>
										</div>
									</li>
									<li class="list-group-item">
										<div class="row">
											<div class="col-md-3">Aluminum</div>
											<div class="col-md-4"><div class="progress"><div class="progress-bar" id="aluminum_progress" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div></div></div>
											<div class="col-md-5"><span id="aluminum" class="badge"></span></div>
										</div>
									</li>
									<li class="list-group-item">
										<div class="row">
											<div class="col-md-3">Cash</div>
											<div class="col-md-4"><div class="progress"><div class="progress-bar" id="cash_progress" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div></div></div>
											<div class="col-md-5"><span class="badge">$<span id="cash"></span></span></div>
										</div>
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
				<div class="panel-body{{ $page > 0 ? ' table-responsive' : '' }}" id="members_table">
					<button type="button" class="btn btn-default" id="download"><span class="glyphicon glyphicon-download-alt" aria-hidden="true"></span></button> <button type="button" class="btn btn-default" data-toggle="modal" data-target="#searchModal"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></button> Last Updated: {!! $last_update !!}
					@if($page <= 0)
					<div style="text-align: center;">There is no data to display.</div>
					@else
					<table class="table table-hover">
						<thead>
							<tr>
								<th><a href="{!! URL::to('/stats/' . $page . '/nation' . ($asc == 'desc' && $sort == 'nation' ? '/asc' : '/desc')) . (empty($search) ? '' : '/' . $search) !!}">Nation Name{!! ($sort == 'nation' ? (' <span class="glyphicon glyphicon-chevron-' . ($asc == 'asc' ? 'up' : 'down') . '"></span>') : '') !!}</a></th>
								<th><a href="{!! URL::to('/stats/' . $page . '/leader' . ($asc == 'desc' && $sort == 'leader' ? '/asc' : '/desc')) . (empty($search) ? '' : '/' . $search) !!}">Leader Name{!! ($sort == 'leader' ? (' <span class="glyphicon glyphicon-chevron-' . ($asc == 'asc' ? 'up' : 'down') . '"></span>') : '') !!}</a></th>
								<th><a href="{!! URL::to('/stats/' . $page . '/soldiers' . ($asc == 'desc' && $sort == 'soldiers' ? '/asc' : '/desc')) . (empty($search) ? '' : '/' . $search) !!}">Soldiers{!! ($sort == 'soldiers' ? (' <span class="glyphicon glyphicon-chevron-' . ($asc == 'asc' ? 'up' : 'down') . '"></span>') : '') !!}</a></th>
								<th><a href="{!! URL::to('/stats/' . $page . '/tanks' . ($asc == 'desc' && $sort == 'tanks' ? '/asc' : '/desc')) . (empty($search) ? '' : '/' . $search) !!}">Tanks{!! ($sort == 'tanks' ? (' <span class="glyphicon glyphicon-chevron-' . ($asc == 'asc' ? 'up' : 'down') . '"></span>') : '') !!}</a></th>
								<th><a href="{!! URL::to('/stats/' . $page . '/planes' . ($asc == 'desc' && $sort == 'planes' ? '/asc' : '/desc')) . (empty($search) ? '' : '/' . $search) !!}">Planes{!! ($sort == 'planes' ? (' <span class="glyphicon glyphicon-chevron-' . ($asc == 'asc' ? 'up' : 'down') . '"></span>') : '') !!}</a></th>
								<th><a href="{!! URL::to('/stats/' . $page . '/ships' . ($asc == 'desc' && $sort == 'ships' ? '/asc' : '/desc')) . (empty($search) ? '' : '/' . $search) !!}">Ships{!! ($sort == 'ships' ? (' <span class="glyphicon glyphicon-chevron-' . ($asc == 'asc' ? 'up' : 'down') . '"></span>') : '') !!}</a></th>
								<th><a href="{!! URL::to('/stats/' . $page . '/nukes' . ($asc == 'desc' && $sort == 'nukes' ? '/asc' : '/desc')) . (empty($search) ? '' : '/' . $search) !!}">Nukes{!! ($sort == 'nukes' ? (' <span class="glyphicon glyphicon-chevron-' . ($asc == 'asc' ? 'up' : 'down') . '"></span>') : '') !!}</a></th>
								<th><a href="{!! URL::to('/stats/' . $page . '/spies' . ($asc == 'desc' && $sort == 'spies' ? '/asc' : '/desc')) . (empty($search) ? '' : '/' . $search) !!}">Spies{!! ($sort == 'spies' ? (' <span class="glyphicon glyphicon-chevron-' . ($asc == 'asc' ? 'up' : 'down') . '"></span>') : '') !!}</a></th>
								<th><a href="{!! URL::to('/stats/' . $page . '/score' . ($asc == 'desc' && $sort == 'score' ? '/asc' : '/desc')) . (empty($search) ? '' : '/' . $search) !!}">Score{!! ($sort == 'score' ? (' <span class="glyphicon glyphicon-chevron-' . ($asc == 'asc' ? 'up' : 'down') . '"></span>') : '') !!}</a></th>
								<th><a href="{!! URL::to('/stats/' . $page . '/cities' . ($asc == 'desc' && $sort == 'cities' ? '/asc' : '/desc')) . (empty($search) ? '' : '/' . $search) !!}">Cities{!! ($sort == 'cities' ? (' <span class="glyphicon glyphicon-chevron-' . ($asc == 'asc' ? 'up' : 'down') . '"></span>') : '') !!}</a></th>
								<th><a href="{!! URL::to('/stats/' . $page . '/infra' . ($asc == 'desc' && $sort == 'infra' ? '/asc' : '/desc')) . (empty($search) ? '' : '/' . $search) !!}">Infra / Avg / CCE / PB{!! ($sort == 'infra' ? (' <span class="glyphicon glyphicon-chevron-' . ($asc == 'asc' ? 'up' : 'down') . '"></span>') : '') !!}</a></th>
								<th><a href="{!! URL::to('/stats/' . $page . '/city_timer' . ($asc == 'desc' && $sort == 'city_timer' ? '/asc' : '/desc')) . (empty($search) ? '' : '/' . $search) !!}">City Timer{!! ($sort == 'city_timer' ? (' <span class="glyphicon glyphicon-chevron-' . ($asc == 'asc' ? 'up' : 'down') . '"></span>') : '') !!}</a></th>
								<th><a href="{!! URL::to('/stats/' . $page . '/wc' . ($asc == 'desc' && $sort == 'wc' ? '/asc' : '/desc')) . (empty($search) ? '' : '/' . $search) !!}">Warchests{!! ($sort == 'wc' ? (' <span class="glyphicon glyphicon-chevron-' . ($asc == 'asc' ? 'up' : 'down') . '"></span>') : '') !!}</a></th>
								<th><a href="{!! URL::to('/stats/' . $page . '/food' . ($asc == 'desc' && $sort == 'food' ? '/asc' : '/desc')) . (empty($search) ? '' : '/' . $search) !!}">Food{!! ($sort == 'food' ? (' <span class="glyphicon glyphicon-chevron-' . ($asc == 'asc' ? 'up' : 'down') . '"></span>') : '') !!}</a></th>
								<th><a href="{!! URL::to('/stats/' . $page . '/uranium' . ($asc == 'desc' && $sort == 'uranium' ? '/asc' : '/desc')) . (empty($search) ? '' : '/' . $search) !!}">Uranium{!! ($sort == 'uranium' ? (' <span class="glyphicon glyphicon-chevron-' . ($asc == 'asc' ? 'up' : 'down') . '"></span>') : '') !!}</a></th>
								<th><a href="{!! URL::to('/stats/' . $page . '/inactive' . ($asc == 'desc' && $sort == 'inactive' ? '/asc' : '/desc')) . (empty($search) ? '' : '/' . $search) !!}">Days Inactive{!! ($sort == 'inactive' ? (' <span class="glyphicon glyphicon-chevron-' . ($asc == 'asc' ? 'up' : 'down') . '"></span>') : '') !!}</a></th>
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
								<td>{{ $nation['nukes'] }}</td>
								<td>{{ $nation['spies'] }}</td>
								<td>{{ $nation['score'] }}</td>
								<td>{{ $nation['cities'] }}</td>
								<td>{{ $nation['infra'] }} / {{ $nation['avg_infra'] }} / <span style="color: {{ $nation['cce'] ? 'green' : 'red' }};" class="glyphicon glyphicon-{{ $nation['cce'] ? 'ok' : 'remove' }}"></span> / <span style="color: {{ $nation['pb'] ? 'green' : 'red' }};" class="glyphicon glyphicon-{{ $nation['pb'] ? 'ok' : 'remove' }}"></span></td>
								<td>{!! ($nation['city_timer'] <= 0 ? '<strong>Now</strong>' : $nation['city_timer']) !!}</td>
								<td>
									<div class="pull-left" style="width: 75%;">
										<div class="progress">
											<div class="progress-bar" role="progressbar" aria-valuenow="{{ $nation['warchest_data']['overall'] }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ $nation['warchest_data']['overall'] }}%;">
												{{ $nation['warchest_data']['overall'] }}%
											</div>
										</div>
									</div>
									<div class="pull-right">
										<a href="#" nation-id="{{ $nation['nation_id'] }}" class="warchest-link" data-toggle="modal" data-target="#warchestModal"><span class="glyphicon glyphicon-list-alt"></span></a>
									</div>
								</td>
								<td>{{ number_format($nation['food'], 2) }}</td>
								<td>{{ number_format($nation['uranium'], 2)}}</td>
								<td><span{!! ($nation['inactive'] >= 7 && $nation['inactive'] < 14 ? ' style="background-color: rgba(255, 255, 0, 0.5); width: 100%; height: 100%; display: block;"' : ($nation['inactive'] >= 14 ? ' style="background-color: rgba(255, 0, 0, 0.5); width: 100%; height: 100%; display: block;"' : '')) !!}>{{ $nation['inactive'] }}</span></td>
							</tr>
						@endforeach
						</tbody>
					</table>
					@endif
				</div>
			</div>
			<div style="text-align: center;">
				<div class="gigantic pagination">
					<a href="#" class="first" data-action="first">&laquo;</a>
					<a href="#" class="previous" data-action="previous">&lsaquo;</a>
					<input type="text" readonly="readonly" data-max-page="{{ $max_page }}" />
					<a href="#" class="next" data-action="next">&rsaquo;</a>
					<a href="#" class="last" data-action="last">&raquo;</a>
				</div>
			</div>
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
				$(".pagination").jqPagination({
					link_string: "{!! url('/stats/' . $page . '/{page_number}') !!}",
					current_page: {{ $page }},
					paged: function(page){
						window.location.href = "{{ url('/stats') }}/" + page{!! !empty($sort) ? (' + "/' . $sort . ($asc == 'asc' ? '/asc' : '/desc' ) . (empty($search) ? '"' : '/' . $search . '"')) : '/ruler/asc' . (empty($search) ? '"' : '/' . $search . '"') !!};
					}
				});
				$("#submit").click(function(){
					var col = $("#col option:selected").val();
					var item = $("#item").val();
					window.location.href = "{{ url('/stats') }}{{ '/' . ($page == 0 ? '1' : $page) . (!empty($sort) ? ('/' . $sort . ($asc == 'asc' ? '/asc' : '/desc' )) : '/ruler/asc') }}/" + col + ":" + item;
				});
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
					
					// progress
					$("#food_progress").attr("aria-valuenow", nation["warchest_data"]["food"]);
					$("#food_progress").text(nation["warchest_data"]["food"] + "%");
					$("#food_progress").css("width", nation["warchest_data"]["food"] + "%");
					$("#uranium_progress").attr("aria-valuenow", nation["warchest_data"]["uranium"]);
					$("#uranium_progress").text(nation["warchest_data"]["uranium"] + "%");
					$("#uranium_progress").css("width", nation["warchest_data"]["uranium"] + "%");
					$("#gasoline_progress").attr("aria-valuenow", nation["warchest_data"]["gasoline"]);
					$("#gasoline_progress").text(nation["warchest_data"]["gasoline"] + "%");
					$("#gasoline_progress").css("width", nation["warchest_data"]["gasoline"] + "%");
					$("#munitions_progress").attr("aria-valuenow", nation["warchest_data"]["munitions"]);
					$("#munitions_progress").text(nation["warchest_data"]["munitions"] + "%");
					$("#munitions_progress").css("width", nation["warchest_data"]["munitions"] + "%");
					$("#steel_progress").attr("aria-valuenow", nation["warchest_data"]["steel"]);
					$("#steel_progress").text(nation["warchest_data"]["steel"] + "%");
					$("#steel_progress").css("width", nation["warchest_data"]["steel"] + "%");
					$("#aluminum_progress").attr("aria-valuenow", nation["warchest_data"]["aluminum"]);
					$("#aluminum_progress").text(nation["warchest_data"]["aluminum"] + "%");
					$("#aluminum_progress").css("width", nation["warchest_data"]["aluminum"] + "%");
					$("#cash_progress").attr("aria-valuenow", nation["warchest_data"]["cash"]);
					$("#cash_progress").text(nation["warchest_data"]["cash"] + "%");
					$("#cash_progress").css("width", nation["warchest_data"]["cash"] + "%");
				});
				$("#download").click(function(){
					window.location.href = "{{ url('/stats/dl') }}";
				});
			});
		</script>
@stop
