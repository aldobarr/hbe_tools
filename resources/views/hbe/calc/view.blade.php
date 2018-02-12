@extends('layouts.master')
@section('content')
<div class="container-fluid">
			<div class="panel panel-default">
				<div class="panel-body{{ !empty($nations) ? ' table-responsive' : '' }}">
					@if(empty($nations))
					<div style="text-align: center;">There is no data to display.</div>
					@else
					<div class="row">
						<div class="col-md-6"><strong>Total Rebuild Cost:</strong> ${{ number_format($total_cost, 2) }}</div>
						<div class="col-md-6"><strong>Total Infra Purchase:</strong> {{ number_format($total_infra, 3) }}</div>
					</div>
					<div class="row">
						<div class="col-md-6"><strong>Total Recommended Rebuild Cost:</strong> ${{ number_format($total_rcost, 2) }}</div>
						<div class="col-md-6"><strong>Total Recommended Infra Purchase:</strong> {{ number_format($total_rinfra, 3) }}</div>
					</div>
					<div class="row">
						<div class="col-md-6"><strong>Total Potential ROI:</strong> {{ $total_roi < 0 ? '-' : '' }}${{ number_format($total_roi < 0 ? ($total_roi * -1) : $total_roi, 2) }}</div>
						<div class="col-md-6"><strong>Recommendations based on a 30 day income calculation</strong></div>
					</div>
					<table class="table table-hover">
						<thead>
							<tr>
								<th></th>
								<th>Nation Name</th>
								<th>Leader Name</th>
								<th>Current Total Infra</th>
								<th>Total Amount</th>
								<th>Total Cost</th>
								<th>Purchase Total</th>
								<th>Recommended City</th>
								<th>Purchase Cost</th>
								<th>ROI</th>
								<th>CCEP</th>
							</tr>
						</thead>
						<tbody>
						@foreach($nations as $nation)
							<tr>
								<td>{{ ++$count }}</td>
								<td>{!! HTML::link('https://politicsandwar.com/nation/id=' . $nation['nation_id'], $nation['name'], array('target' => '_blank')) !!}</td>
								<td>{!! HTML::link('https://politicsandwar.com/inbox/message/receiver=' . urlencode($nation['leader']), $nation['leader'], array('target' => '_blank')) !!}</td>
								<td>{{ number_format($nation['old_infra'], 3) }}</td>
								<td>{{ number_format($nation['new_infra'], 3) }}</td>
								<td>${{ number_format($nation['cost'], 2) }}</td>
								<td>{{ number_format($nation['rinfra'], 3) }}</td>
								<td>{{ number_format($nation['ravg']) }}</td>
								<td>${{ number_format($nation['rcost'], 2) }}</td>
								<td><span style="color: {{ $nation['roi'] < 0 ? 'red' : 'green' }};">{{ $nation['roi'] < 0 ? '-' : '' }}${{ number_format($nation['roi'] < 0 ? ($nation['roi'] * -1) : $nation['roi'], 2) }}</span></td>
								<td><span class="glyphicon glyphicon-{{ $nation['ccep'] ? 'ok' : 'remove' }}" style="color: {{ $nation['ccep'] ? 'green' : 'red' }};" aria-hidden="true"></span></td>
							</tr>
						@endforeach
						</tbody>
					</table>
					@endif
				</div>
			</div>
		</div>
@stop