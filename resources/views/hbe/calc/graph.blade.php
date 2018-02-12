@extends('layouts.master')
@section('content')
<div class="container">
			<div class="panel panel-default">
				<div class="panel-body">
					<div id="resources-div">
						<div id="resource-tab-week-div">
							<canvas id="resource-chart" style="width: 100%; height: 100%;"></canvas>
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
								var resourceData = {
									labels: {!! json_encode($infras) !!},
									datasets: [
										{
											label: "Cost",
											fill: false,
											lineTension: 0.1,
											backgroundColor: "rgba(255, 0, 0, 0.4)",
											borderColor: "rgba(255, 0, 0, 1)",
											borderCapStyle: 'butt',
											borderDash: [],
											borderDashOffset: 0.0,
											borderJoinStyle: 'miter',
											pointBorderColor: "rgba(255, 0, 0, 1)",
											pointBackgroundColor: "#fff",
											pointBorderWidth: 1,
											pointHoverRadius: 5,
											pointHoverBackgroundColor: "rgba(255, 0, 0, 1)",
											pointHoverBorderColor: "rgba(220, 220, 220, 1)",
											pointHoverBorderWidth: 2,
											pointRadius: 1,
											pointHitRadius: 10,
											data: {!! json_encode($costs) !!}
										},
										{
											label: "Income",
											fill: false,
											lineTension: 0.1,
											backgroundColor: "rgba(0, 255, 0, 0.4)",
											borderColor: "rgba(0, 255, 0, 1)",
											borderCapStyle: 'butt',
											borderDash: [],
											borderDashOffset: 0.0,
											borderJoinStyle: 'miter',
											pointBorderColor: "rgba(0, 255, 0, 1)",
											pointBackgroundColor: "#fff",
											pointBorderWidth: 1,
											pointHoverRadius: 5,
											pointHoverBackgroundColor: "rgba(0, 255, 0, 1)",
											pointHoverBorderColor: "rgba(220, 220, 220, 1)",
											pointHoverBorderWidth: 2,
											pointRadius: 1,
											pointHitRadius: 10,
											data: {!! json_encode($income) !!}
										}
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
					</div>
				</div>
			</div>
		</div>
@stop