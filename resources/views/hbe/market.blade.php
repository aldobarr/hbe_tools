@extends('layouts.master')
@section('content')
<div class="container">
			<div class="panel panel-default">
				<div class="panel-body">
					<ul id="stats-nav" class="nav nav-tabs">
						<li role="presentation" class="active" id="food"><a href="#">Food</a></li>
						@foreach($output as $key => $value)
							@if($key != 'food')
						<li role="presentation" id="{{ $key }}"><a href="#">{{ ucfirst($key) }}</a></li>
							@endif
						@endforeach
					</ul><br>
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
					</script>
					@foreach($output as $key => $value)
					<div id="{{ $key }}-div"{!! $key == 'food' ? '' : 'style="display: none;"' !!}>
						<div id="{{ $key }}-tab-week-div">
							<canvas id="{{ $key }}-chart" style="width: 100%; height: 100%;"></canvas>
							<script>
								var {{ $key }}Data = {
									labels: {!! json_encode($days) !!},
									datasets: [
										{
											label: "{{ ucfirst($key) }}",
											fill: false,
											lineTension: 0.1,
											backgroundColor: "rgba({{ implode(", ", array(75, 192, 192)) }}, 0.4)",
											borderColor: "rgba({{ implode(", ", array(75, 192, 192)) }}, 1)",
											borderCapStyle: 'butt',
											borderDash: [],
											borderDashOffset: 0.0,
											borderJoinStyle: 'miter',
											pointBorderColor: "rgba({{ implode(", ", array(75, 192, 192)) }}, 1)",
											pointBackgroundColor: "#fff",
											pointBorderWidth: 1,
											pointHoverRadius: 5,
											pointHoverBackgroundColor: "rgba({{ implode(", ", array(75, 192, 192)) }}, 1)",
											pointHoverBorderColor: "rgba(220, 220, 220, 1)",
											pointHoverBorderWidth: 2,
											pointRadius: 1,
											pointHitRadius: 10,
											data: {!! json_encode($value) !!}
										}
									]
								};
								var {{ $key }}Options = {
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
								var {{ $key }}_chart = document.getElementById("{{ $key }}-chart").getContext('2d');
								var {{ $key }}Chart = Chart.Line({{ $key }}_chart, {
									data: {{ $key }}Data,
									options: {{ $key }}Options
								});
							</script>
						</div>
					</div>
					@endforeach
				</div>
			</div>
		</div>
		<script>
			@foreach($output as $key => $value)
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
			@endforeach
		</script>
@stop