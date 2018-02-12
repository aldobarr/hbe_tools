<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
		<meta http-equiv="content-type" content="text/html; charset=UTF-8">
		
		<title>Holy Britannian Empire</title>
		
		<!-- Latest compiled and minified jQuery -->
		<script src="/js/jquery-2.2.3.min.js"></script>
		<script src="/js/vis.min.js"></script>
		<link href="/css/vis.min.css" rel="stylesheet"/>
		<style>
			#web{
				position: fixed;
				height: 100%;
				width: 100%;
			}
		</style>
	</head>
	<body>
		<div id="web"></div>
		<script>
		@if(!empty($edges))
			var container = document.getElementById("web");
			var nodes = {!! json_encode($nodes) !!};
			var edges = {!! json_encode($edges) !!};
			var data = {
				nodes: nodes,
				edges: edges
			};
			var options = {
				height: '100%',
				width: '100%',
				nodes: {
					shape: "image"
				},
				edges: {
					width: 2
				},
				physics: {
					solver: "forceAtlas2Based"
				}
			};
			var network = new vis.Network(container, data, options);
		@else
			$(document).ready(function(){
				var parent = $("#web").parent();
				$("#web").remove();
				parent.removeClass("panel-default");
				parent.addClass("panel-success");
				parent.append("<div class=\"panel-heading\"><h3 class=\"panel-title\">Weird</h3></div><div class=\"panel-body\">There are no treaties...</div>");
			});
		@endif
		</script>
	</body>
</html>