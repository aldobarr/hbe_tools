@extends('layouts.master')
@section('content')
<div class="container">
			@if(!empty($fixed))
			<div class="panel panel-success">
				<div class="panel-heading">
					<h3 class="panel-title">Success</h3>
				</div>
				<div class="panel-body">
					{{ $fixed }}
				</div>
			</div>
			@endif
			<div class="panel panel-default">
				<div class="panel-body">
					<ul id="stats-nav" class="nav nav-tabs">
						<li role="presentation" class="active" id="cash"><a href="#">Cash</a></li>
						<li role="presentation" id="resources"><a href="#">Resources</a></li>
						@foreach($resource_totals as $key => $value)
							@if($key != 'cash' && $key != 'increment_labels' && $key != 'increment_totals')
						<li role="presentation" id="{{ $key }}"><a href="#">{{ ucfirst($key) }}</a></li>
							@endif
						@endforeach
					</ul><br>
					<div id="cash-div">
						<div class="row">
							<div class="col-md-6"><strong>Last Run:</strong> {!! $last_update !!}</div>
							<div class="col-md-6"><strong>Last Turn Income:</strong> ${{ number_format($resource_totals['cash']['turn'], 2) }}</div>
						</div>
						<div class="row">
							<div class="col-md-6"><strong>Last Day Income:</strong> ${{ number_format($resource_totals['cash']['day'], 2) }}</div>
							<div class="col-md-6"><strong>Alliance Total Income:</strong> ${{ number_format($resource_totals['cash']['total'], 2) }}</div>
						</div><br>
						<ul id="cash-stats-nav" class="nav nav-tabs">
							<li role="presentation" class="active" id="cash-tab-week"><a href="#">Week Display</a></li>
							<li role="presentation" id="cash-tab-month"><a href="#">Month Display</a></li>
						</ul><br>
						<div id="cash-tab-week-div">
							<canvas id="cash-chart" style="width: 100%; height: 100%;"></canvas>
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
								var cashData = {
									labels: {!! json_encode($resource_totals['increment_labels']['week']) !!},
									datasets: [
										{
											label: "Cash",
											fill: false,
											lineTension: 0.1,
											backgroundColor: "rgba({{ implode(", ", $resource_totals['increment_totals']['week']['cash'][1]) }}, 0.4)",
											borderColor: "rgba({{ implode(", ", $resource_totals['increment_totals']['week']['cash'][1]) }}, 1)",
											borderCapStyle: 'butt',
											borderDash: [],
											borderDashOffset: 0.0,
											borderJoinStyle: 'miter',
											pointBorderColor: "rgba({{ implode(", ", $resource_totals['increment_totals']['week']['cash'][1]) }}, 1)",
											pointBackgroundColor: "#fff",
											pointBorderWidth: 1,
											pointHoverRadius: 5,
											pointHoverBackgroundColor: "rgba({{ implode(", ", $resource_totals['increment_totals']['week']['cash'][1]) }}, 1)",
											pointHoverBorderColor: "rgba(220, 220, 220, 1)",
											pointHoverBorderWidth: 2,
											pointRadius: 1,
											pointHitRadius: 10,
											data: {!! json_encode($resource_totals['increment_totals']['week']['cash'][0]) !!}
										}
									]
								};
								var cashOptions = {
									scales: {
										yAxes: [{
											ticks: {
												callback: function(value){
													return '$' + Number(value).toLocaleString("en");
												}
											}
										}]
									},
									tooltips: {
										callbacks: {
											label: function(tooltipItem, data){
												return data.datasets[tooltipItem.datasetIndex].label + ": $" + addCommas(tooltipItem.yLabel);
											}
										}
									}
								};
								var cash_chart = document.getElementById("cash-chart").getContext('2d');
								var cashChart = Chart.Line(cash_chart, {
									data: cashData,
									options: cashOptions
								});
							</script>
						</div>
						<div id="cash-tab-month-div" style="display: none;">
							<canvas id="cash-chart-month" style="width: 100%; height: 100%;"></canvas>
							<script>
								var cashDataMonth = {
									labels: {!! json_encode($resource_totals['increment_labels']['month']) !!},
									datasets: [
										{
											label: "Cash",
											fill: false,
											lineTension: 0.1,
											backgroundColor: "rgba({{ implode(", ", $resource_totals['increment_totals']['month']['cash'][1]) }}, 0.4)",
											borderColor: "rgba({{ implode(", ", $resource_totals['increment_totals']['month']['cash'][1]) }}, 1)",
											borderCapStyle: 'butt',
											borderDash: [],
											borderDashOffset: 0.0,
											borderJoinStyle: 'miter',
											pointBorderColor: "rgba({{ implode(", ", $resource_totals['increment_totals']['month']['cash'][1]) }}, 1)",
											pointBackgroundColor: "#fff",
											pointBorderWidth: 1,
											pointHoverRadius: 5,
											pointHoverBackgroundColor: "rgba({{ implode(", ", $resource_totals['increment_totals']['month']['cash'][1]) }}, 1)",
											pointHoverBorderColor: "rgba(220, 220, 220, 1)",
											pointHoverBorderWidth: 2,
											pointRadius: 1,
											pointHitRadius: 10,
											data: {!! json_encode($resource_totals['increment_totals']['month']['cash'][0]) !!}
										}
									]
								};
								var cashOptionsMonth = {
									scales: {
										yAxes: [{
											ticks: {
												callback: function(value){
													return '$' + Number(value).toLocaleString("en");
												}
											}
										}]
									},
									tooltips: {
										callbacks: {
											label: function(tooltipItem, data){
												return data.datasets[tooltipItem.datasetIndex].label + ": $" + addCommas(tooltipItem.yLabel);
											}
										}
									}
								};
								var cash_chart_month = document.getElementById("cash-chart-month").getContext('2d');
								var cashChartMonth = Chart.Line(cash_chart_month, {
									data: cashDataMonth,
									options: cashOptionsMonth
								});
							</script>
						</div>
					</div>
					<div id="resources-div" style="display: none;">
						<div class="row">
							<div class="col-md-6"><strong>Last Run:</strong> {!! $last_update !!}</div>
							<div class="col-md-6"><strong>Last Turn Income:</strong> ${{ number_format($resource_totals['cash']['turn'], 2) }}</div>
						</div>
						<div class="row">
							<div class="col-md-6"><strong>Last Day Income:</strong> ${{ number_format($resource_totals['cash']['day'], 2) }}</div>
							<div class="col-md-6"><strong>Alliance Total Income:</strong> ${{ number_format($resource_totals['cash']['total'], 2) }}</div>
						</div><br>
						<ul id="resource-stats-nav" class="nav nav-tabs">
							<li role="presentation" class="active" id="resource-tab-week"><a href="#">Week Display</a></li>
							<li role="presentation" id="resource-tab-month"><a href="#">Month Display</a></li>
						</ul><br>
						<div id="resource-tab-week-div">
							<canvas id="resource-chart" style="width: 100%; height: 100%;"></canvas>
							<script>
								var resourceData = {
									labels: {!! json_encode($resource_totals['increment_labels']['week']) !!},
									datasets: [
									@foreach($resource_totals['increment_totals']['week'] as $key => $value)
										@if($key != 'cash')
										{
											label: "{{ ucfirst($key) }}",
											fill: false,
											lineTension: 0.1,
											backgroundColor: "rgba({{ implode(", ", $value[1]) }}, 0.4)",
											borderColor: "rgba({{ implode(", ", $value[1]) }}, 1)",
											borderCapStyle: 'butt',
											borderDash: [],
											borderDashOffset: 0.0,
											borderJoinStyle: 'miter',
											pointBorderColor: "rgba({{ implode(", ", $value[1]) }}, 1)",
											pointBackgroundColor: "#fff",
											pointBorderWidth: 1,
											pointHoverRadius: 5,
											pointHoverBackgroundColor: "rgba({{ implode(", ", $value[1]) }}, 1)",
											pointHoverBorderColor: "rgba(220, 220, 220, 1)",
											pointHoverBorderWidth: 2,
											pointRadius: 1,
											pointHitRadius: 10,
											data: {!! json_encode($value[0]) !!}
										},
										@endif
									@endforeach
									]
								};
								var resourceOptions = {
									scales: {
										yAxes: [{
											ticks: {
												callback: function(value){
													return Number(value).toLocaleString("en");
												}
											}
										}]
									},
									tooltips: {
										callbacks: {
											label: function(tooltipItem, data){
												return data.datasets[tooltipItem.datasetIndex].label + ": " + addCommas(tooltipItem.yLabel);
											}
										}
									}
								};
								var resource_chart = document.getElementById("resource-chart").getContext('2d');
								var resourceChart = Chart.Line(resource_chart, {
									data: resourceData,
									options: resourceOptions
								});
							</script>
						</div>
						<div id="resource-tab-month-div" style="display: none;">
							<canvas id="resource-chart-month" style="width: 100%; height: 100%;"></canvas>
							<script>
								var resourceDataMonth = {
									labels: {!! json_encode($resource_totals['increment_labels']['month']) !!},
									datasets: [
									@foreach($resource_totals['increment_totals']['month'] as $key => $value)
										@if($key != 'cash')
										{
											label: "{{ ucfirst($key) }}",
											fill: false,
											lineTension: 0.1,
											backgroundColor: "rgba({{ implode(", ", $value[1]) }}, 0.4)",
											borderColor: "rgba({{ implode(", ", $value[1]) }}, 1)",
											borderCapStyle: 'butt',
											borderDash: [],
											borderDashOffset: 0.0,
											borderJoinStyle: 'miter',
											pointBorderColor: "rgba({{ implode(", ", $value[1]) }}, 1)",
											pointBackgroundColor: "#fff",
											pointBorderWidth: 1,
											pointHoverRadius: 5,
											pointHoverBackgroundColor: "rgba({{ implode(", ", $value[1]) }}, 1)",
											pointHoverBorderColor: "rgba(220, 220, 220, 1)",
											pointHoverBorderWidth: 2,
											pointRadius: 1,
											pointHitRadius: 10,
											data: {!! json_encode($value[0]) !!}
										},
										@endif
									@endforeach
									]
								};
								var resourceOptionsMonth = {
									scales: {
										yAxes: [{
											ticks: {
												callback: function(value){
													return Number(value).toLocaleString("en");
												}
											}
										}]
									},
									tooltips: {
										callbacks: {
											label: function(tooltipItem, data){
												return data.datasets[tooltipItem.datasetIndex].label + ": " + addCommas(tooltipItem.yLabel);
											}
										}
									}
								};
								var resource_chart_month = document.getElementById("resource-chart-month").getContext('2d');
								var resourceChartMonth = Chart.Line(resource_chart_month, {
									data: resourceDataMonth,
									options: resourceOptionsMonth
								});
							</script>
						</div>
					</div>
					@foreach($resource_totals as $key => $value)
						@if($key != 'cash' && $key != 'increment_labels' && $key != 'increment_totals')
					<div id="{{ $key }}-div" style="display: none;">
						<div class="row">
							<div class="col-md-6"><strong>Last Run:</strong> {!! $last_update !!}</div>
							<div class="col-md-6"><strong>Last Turn Income:</strong> {{ number_format($resource_totals[$key]['turn'], 2) }}</div>
						</div>
						<div class="row">
							<div class="col-md-6"><strong>Last Day Income:</strong> {{ number_format($resource_totals[$key]['day'], 2) }}</div>
							<div class="col-md-6"><strong>Alliance Total Income:</strong> {{ number_format($resource_totals[$key]['total'], 2) }}</div>
						</div><br>
						<ul id="{{ $key }}-stats-nav" class="nav nav-tabs">
							<li role="presentation" class="active" id="{{ $key }}-tab-week"><a href="#">Week Display</a></li>
							<li role="presentation" id="{{ $key }}-tab-month"><a href="#">Month Display</a></li>
						</ul><br>
						<div id="{{ $key }}-tab-week-div">
							<canvas id="{{ $key }}-chart" style="width: 100%; height: 100%;"></canvas>
							<script>
								var {{ $key }}Data = {
									labels: {!! json_encode($resource_totals['increment_labels']['week']) !!},
									datasets: [
										{
											label: "{{ ucfirst($key) }}",
											fill: false,
											lineTension: 0.1,
											backgroundColor: "rgba({{ implode(", ", $resource_totals['increment_totals']['week'][$key][1]) }}, 0.4)",
											borderColor: "rgba({{ implode(", ", $resource_totals['increment_totals']['week'][$key][1]) }}, 1)",
											borderCapStyle: 'butt',
											borderDash: [],
											borderDashOffset: 0.0,
											borderJoinStyle: 'miter',
											pointBorderColor: "rgba({{ implode(", ", $resource_totals['increment_totals']['week'][$key][1]) }}, 1)",
											pointBackgroundColor: "#fff",
											pointBorderWidth: 1,
											pointHoverRadius: 5,
											pointHoverBackgroundColor: "rgba({{ implode(", ", $resource_totals['increment_totals']['week'][$key][1]) }}, 1)",
											pointHoverBorderColor: "rgba(220, 220, 220, 1)",
											pointHoverBorderWidth: 2,
											pointRadius: 1,
											pointHitRadius: 10,
											data: {!! json_encode($resource_totals['increment_totals']['week'][$key][0]) !!}
										}
									]
								};
								var {{ $key }}Options = {
									scales: {
										yAxes: [{
											ticks: {
												callback: function(value){
													return Number(value).toLocaleString("en");
												}
											}
										}]
									},
									tooltips: {
										callbacks: {
											label: function(tooltipItem, data){
												return data.datasets[tooltipItem.datasetIndex].label + ": " + addCommas(tooltipItem.yLabel);
											}
										}
									}
								};
								var {{ $key }}_chart = document.getElementById("{{ $key }}-chart").getContext('2d');
								var {{ $key }}Chart = Chart.Line({{ $key }}_chart, {
									data: {{ $key }}Data,
									options: {{ $key }}Options
								});
							</script>
						</div>
						<div id="{{ $key }}-tab-month-div" style="display: none;">
							<canvas id="{{ $key }}-chart-month" style="width: 100%; height: 100%;"></canvas>
							<script>
								var {{ $key }}DataMonth = {
									labels: {!! json_encode($resource_totals['increment_labels']['month']) !!},
									datasets: [
										{
											label: "{{ ucfirst($key) }}",
											fill: false,
											lineTension: 0.1,
											backgroundColor: "rgba({{ implode(", ", $resource_totals['increment_totals']['month'][$key][1]) }}, 0.4)",
											borderColor: "rgba({{ implode(", ", $resource_totals['increment_totals']['month'][$key][1]) }}, 1)",
											borderCapStyle: 'butt',
											borderDash: [],
											borderDashOffset: 0.0,
											borderJoinStyle: 'miter',
											pointBorderColor: "rgba({{ implode(", ", $resource_totals['increment_totals']['month'][$key][1]) }}, 1)",
											pointBackgroundColor: "#fff",
											pointBorderWidth: 1,
											pointHoverRadius: 5,
											pointHoverBackgroundColor: "rgba({{ implode(", ", $resource_totals['increment_totals']['month'][$key][1]) }}, 1)",
											pointHoverBorderColor: "rgba(220, 220, 220, 1)",
											pointHoverBorderWidth: 2,
											pointRadius: 1,
											pointHitRadius: 10,
											data: {!! json_encode($resource_totals['increment_totals']['month'][$key][0]) !!}
										}
									]
								};
								var {{ $key }}OptionsMonth = {
									scales: {
										yAxes: [{
											ticks: {
												callback: function(value){
													return Number(value).toLocaleString("en");
												}
											}
										}]
									},
									tooltips: {
										callbacks: {
											label: function(tooltipItem, data){
												return data.datasets[tooltipItem.datasetIndex].label + ": " + addCommas(tooltipItem.yLabel);
											}
										}
									}
								};
								var {{ $key }}_chart_month = document.getElementById("{{ $key }}-chart-month").getContext('2d');
								var {{ $key }}ChartMonth = Chart.Line({{ $key }}_chart_month, {
									data: {{ $key }}DataMonth,
									options: {{ $key }}OptionsMonth
								});
							</script>
						</div>
					</div>
						@endif
					@endforeach
				</div>
			</div>
			@if(AuthHelper::canAccess('fix_tax_income'))
			<div class="pull-right">
				{!! Form::open(['url' => 'taxes']) !!}
					{!! csrf_field() !!}
					<input type="submit" name="fix_taxes" value="Fix Taxes" class="btn btn-primary">
				{!! Form::close() !!}
			</div>
			@endif
		</div>
		<script>
			@foreach($resource_totals['increment_totals']['week'] as $key => $value)
			$("#{{ $key }}").click(function(){
				if($(this).hasClass("active"))
					return;
				$("#stats-nav").children().each(function(){
					if($(this).hasClass("active")){
						$(this).removeClass("active");
						$("#{{ $key }}").addClass("active");
						var id = $(this).attr("id");
						$("#" + id + "-div").slideUp(function(){
							$("#{{ $key }}-div").slideDown();
						});
						return false;
					}
				});
			});
			$("#{{ $key }}-tab-week").click(function(){
				if($(this).hasClass("active"))
					return;
				$("#{{ $key }}-stats-nav").children().each(function(){
					if($(this).hasClass("active")){
						$(this).removeClass("active");
						$("#{{ $key }}-tab-week").addClass("active");
						var id = $(this).attr("id");
						$("#" + id + "-div").slideUp(function(){
							$("#{{ $key }}-tab-week-div").slideDown();
						});
						return false;
					}
				});
			});
			$("#{{ $key }}-tab-month").click(function(){
				if($(this).hasClass("active"))
					return;
				$("#{{ $key }}-stats-nav").children().each(function(){
					if($(this).hasClass("active")){
						$(this).removeClass("active");
						$("#{{ $key }}-tab-month").addClass("active");
						var id = $(this).attr("id");
						$("#" + id + "-div").slideUp(function(){
							$("#{{ $key }}-tab-month-div").slideDown();
						});
						return false;
					}
				});
			});
			@endforeach
			
			$("#resources").click(function(){
				if($(this).hasClass("active"))
					return;
				$("#stats-nav").children().each(function(){
					if($(this).hasClass("active")){
						$(this).removeClass("active");
						$("#resources").addClass("active");
						var id = $(this).attr("id");
						$("#" + id + "-div").slideUp(function(){
							$("#resources-div").slideDown();
						});
						return false;
					}
				});
			});
			$("#resource-tab-week").click(function(){
				if($(this).hasClass("active"))
					return;
				$("#resource-stats-nav").children().each(function(){
					if($(this).hasClass("active")){
						$(this).removeClass("active");
						$("#resource-tab-week").addClass("active");
						var id = $(this).attr("id");
						$("#" + id + "-div").slideUp(function(){
							$("#resource-tab-week-div").slideDown();
						});
						return false;
					}
				});
			});
			$("#resource-tab-month").click(function(){
				if($(this).hasClass("active"))
					return;
				$("#resource-stats-nav").children().each(function(){
					if($(this).hasClass("active")){
						$(this).removeClass("active");
						$("#resource-tab-month").addClass("active");
						var id = $(this).attr("id");
						$("#" + id + "-div").slideUp(function(){
							$("#resource-tab-month-div").slideDown();
						});
						return false;
					}
				});
			});
		</script>
@stop