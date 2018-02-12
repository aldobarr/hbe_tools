@extends('layouts.master')
@section('content')
<div class="modal fade" id="addModal" role="dialog" aria-labelledby="myModalLabel">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title" id="addModalLabel">Add War</h4>
					</div>
					<div class="modal-body">
						<div class="input-group">
							<label>Attacker ID:</label>
							<select id="aggressor_id-field" class="chosen" aria-describedby="aggressor_id">
								<option value=""></option>
								@foreach($alliances as $id => $name)
								<option value="{{ $id }}">{{ $name }}</option>
								@endforeach
							</select>
						</div>
						<br>
						<div>
							<label>Aggressor Side:</label><br>
							<label><input type="radio" id="aggressor_side1-field" name="aggressor_side" value="1"> Aggressors</label>
							<label><input type="radio" id="aggressor_side2-field" name="aggressor_side" value="2"> Defenders</label>
						</div>
						<br>
						<div class="input-group">
							<label>Defender ID:</label>
							<select id="defender_id-field" class="chosen" aria-describedby="defender_id">
								<option value=""></option>
								@foreach($alliances as $id => $name)
								<option value="{{ $id }}">{{ $name }}</option>
								@endforeach
							</select>
						</div>
					</div>
					<div class="modal-footer">
						<button id="add-war" type="button" class="btn btn-primary">Submit</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</div>
			</div>
		</div>
		<div class="container">
			<div class="panel panel-default">
				<div class="panel-body table-responsive" id="members_table">
					<table class="table table-hover">
						<thead>
							<tr>
								<th>Aggressor ID</th>
								<th>Aggressor</th>
								<th>Defender ID</th>
								<th>Defender</th>
								<th>Actions</th>
							</tr>
						</thead>
						<tbody id="edit_body">
						@foreach($wars as $war)
							<tr id="{{ $war['aggressor'] + $war['defender'] }}">
								<td>{{ $war['aggressor'] }}</td>
								<td>{{ $war['aname'] }}</td>
								<td>{{ $war['defender'] }}</td>
								<td>{{ $war['dname'] }}</td>
								<td>
									<button type="button" class="btn btn-default del_button" data-aid="{{ $war['aggressor'] }}" data-did="{{ $war['defender'] }}">
										<span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
									</button>
								</td>
							</tr>
						@endforeach
						</tbody>
					</table>
				</div>
			</div>
			<div class="pull-right">
				<input data-toggle="modal" data-target="#addModal" type="button" class="btn btn-primary" value="Add War">
				<input id="truncate" type="button" class="btn btn-danger" value="Truncate Wars">
			</div>
		</div>
		<script type="application/javascript">
			var token = "{{ csrf_token() }}";
			$("#addModal").on("hide.bs.modal", function(){
				$("#aggressor_id-field").chosen().val("").trigger('chosen:updated');
				$("#defender_id-field").chosen().val("").trigger('chosen:updated');
				$("input[type=radio][name=aggressor_side]").prop("checked", false);
			});
			$("#truncate").click(function(){
				var conf = confirm("Are you sure you wish to truncate the war map?");
				if(conf){
					$.get("{!! url('ajax_api/war_map_man/del') !!}/all").done(function(data){
						var result = JSON.parse(data);
						var display = result.status;
						if(result.code == 0){
							display += "\nError: " + result.error;
							alert(display);
						}
						if(result.code == 1){
							$("#edit_body").fadeOut(function(){
								$(this).empty().fadeIn();
							});
						}
					}).fail(function(){
						alert("Some error occurred.");
					});
				}
			});
			$("#add-war").click(function(){
				var attacker = $("#aggressor_id-field").chosen().val();
				var side = $("input[name=aggressor_side]:checked").val();
				var defender = $("#defender_id-field").chosen().val();
				if(attacker == "" || defender == "" || side == "" || side == 0)
					alert("You left something blank or didn't select a side.");
				else{
					$.post("{!! url('ajax_api/war_map_man/add') !!}", {'aggressor': attacker, 'side': side, 'defender': defender, '_token': token}).done(function(data){
						var result = JSON.parse(data);
						var display = result.status;
						if(result.code == 0){
							display += "\nError: " + result.error;
							alert(display);
						}
						if(result.code == 1){
							var app = '<tr id="' + result.war.id + '"><td>' + result.war.aggressor + '</td><td>' + result.war.aname + '</td>';
							app += '<td>' + result.war.defender + '</td><td>' + result.war.dname + '</td>';
							app += '<td><button type="button" class="btn btn-default del_button" data-aid="' + result.war.aggressor + '" data-did="' + result.war.defender + '"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span></button></td></tr>';
							$("#edit_body").append(app);
							$("#addModal").modal("hide");
						}
					}).fail(function(){
						alert("Some error occurred.");
					});
				}
			});
			$("#edit_body").on("click", "button", function(){
				if($(this).hasClass("del_button")){
					var aid = $(this).attr("data-aid");
					var did = $(this).attr("data-did");
					if(aid > 0 && did > 0){
						var id = parseInt(aid) + parseInt(did);
						var conf = confirm("Are you sure you wish to delete this war?");
						if(conf){
							$.get("{!! url('ajax_api/war_map_man/del') !!}/" + aid + "_" + did).done(function(data){
								var result = JSON.parse(data);
								var display = result.status;
								if(result.code == 0){
									display += "\nError: " + result.error;
									alert(display);
								}
								if(result.code == 1){
									$("#" + id).fadeOut(function(){
										$(this).remove();
									});
								}
							}).fail(function(){
								alert("Some error occurred.");
							});
						}
					}
				}
			});
			$(".chosen").chosen({
				width: "100%"
			});
		</script>
@stop