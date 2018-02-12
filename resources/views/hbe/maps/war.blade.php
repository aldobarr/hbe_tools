@extends('layouts.master')
@section('content')
<div class="container">
			<div class="panel panel-default">
				<div class="panel-body" id="war_map" style="height: 600px;"></div>
			</div>
		</div>
		<script>
		@if(!empty($edges))
			var container = document.getElementById("war_map");
			var nodes = {!! json_encode($nodes) !!};
			var edges = {!! json_encode($edges) !!};
			var data = {
				nodes: nodes,
				edges: edges
			};
			var options = {
				nodes: {
					shape: "image",
					size: 30,
					font: {
						size: 32,
						color: "#000000"
					},
					borderWidth: 4
				},
				edges: {
					width: 2,
					arrows: 'to'
				},
				physics: {
					solver: "forceAtlas2Based"
				}
			};
			var network = new vis.Network(container, data, options);
		@else
			$(document).ready(function(){
				var parent = $("#war_map").parent();
				$("#war_map").remove();
				parent.removeClass("panel-default");
				parent.addClass("panel-success");
				parent.append("<div class=\"panel-heading\"><h3 class=\"panel-title\">Success!</h3></div><div class=\"panel-body\">Peace in our time!</div>");
			});
		@endif
		</script>
@stop