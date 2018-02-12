@extends('layouts.master')
@section('content')
<div class="container-fluid">
			<div class="panel panel-default">
				<div class="panel-body{{ !empty($alliances) ? ' table-responsive' : '' }}" id="alliances_table">
					Last Updated: {!! $last_run !!}
					@if(empty($alliances))
					<div style="text-align: center;">There is no data to display.</div>
					@else
					<table class="table table-hover">
						<thead>
							<tr>
								<th></th>
								<th>Alliance</th>
								<th>Soldiers</th>
								<th>Tanks</th>
								<th>Planes</th>
								<th>Ships</th>
								<th>Nukes</th>
								<th>Nations</th>
								<th>Activity</th>
							</tr>
						</thead>
						@foreach($alliances as $id => $alliance)
						<tbody>
							<tr>
								<td><button type="button" data-id="{{ $id }}" class="btn btn-default alliance_button_plus"><span id="{{ $id }}_span" class="glyphicon glyphicon-plus"></span></button></td>
								<td>{!! HTML::link('https://politicsandwar.com/alliance/id=' . $alliance['id'], $alliance['name'] . ' (' . $alliance['label'] . ')', array('target' => '_blank')) !!}</td>
								<td>{{ $alliance['psoldiers'] }}</td>
								<td>{{ $alliance['ptanks'] }}</td>
								<td>{{ $alliance['pplanes'] }}</td>
								<td>{{ $alliance['pships'] }}</td>
								<td>{{ $alliance['nukes'] }}</td>
								<td>{{ $alliance['cnations'] }}</td>
								<td>{{ $alliance['inactive'] }}</td>
							</tr>
						</tbody>
						<tbody id="{{ $id }}" style="display: none;">
							@foreach($alliance['nations'] as $nation)
							<tr>
								<td></td>
								<td>{!! (!empty($nation['alliance_pos']) ? (HTML::image('images/defense/readiness/' . $nation['alliance_pos_img'], $nation['alliance_pos']) . ' ') : '') . HTML::link('https://politicsandwar.com/nation/id=' . $nation['nation_id'], $nation['leader'] . ' of ' . $nation['nation'] . (!empty($nation['alliance_pos']) ? (' - ' . $nation['alliance_pos']) : ''), array('target' => '_blank')) !!}</td>
								<td>{{ $nation['soldiers'] . ' (' . $nation['psoldiers'] . ')' }}</td>
								<td>{{ $nation['tanks'] . ' (' . $nation['ptanks'] . ')' }}</td>
								<td>{{ $nation['planes'] . ' (' . $nation['pplanes'] . ')' }}</td>
								<td>{{ $nation['ships'] . ' (' . $nation['pships'] . ')' }}</td>
								<td colspan="3">{{ $nation['nukes'] }}</td>
							</tr>
							@endforeach
						</tbody>
						@endforeach
					</table>
					@endif
				</div>
			</div>
		</div>
		<script>
			$(document).ready(function(){
				$("button").on("click", function(){
					if($(this).hasClass("alliance_button_plus")){
						var id = $(this).attr("data-id");
						$("#" + id).show();
						$("#" + id + "_span").removeClass("glyphicon-plus").addClass("glyphicon-minus");
						$(this).removeClass("alliance_button_plus").addClass("alliance_button_minus");
					}else if($(this).hasClass("alliance_button_minus")){
						var id = $(this).attr("data-id");
						$("#" + id).hide();
						$("#" + id + "_span").removeClass("glyphicon-minus").addClass("glyphicon-plus");
						$(this).removeClass("alliance_button_minus").addClass("alliance_button_plus");
					}
				});
			});
		</script>
@stop