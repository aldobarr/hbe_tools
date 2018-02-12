@extends('layouts.master')
@section('content')
<div class="modal fade" id="addModal" role="dialog" aria-labelledby="myModalLabel">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title" id="addModalLabel">Add Treaty</h4>
					</div>
					<div class="modal-body">
						<div class="input-group">
							<label>Alliance One ID:</label>
							<select id="aggressor_id-field" class="chosen" aria-describedby="aggressor_id">
								<option value=""></option>
								@foreach($alliances as $id => $name)
								<option value="{{ $id }}">{{ $name }}</option>
								@endforeach
							</select>
						</div>
						<br>
						<div class="input-group">
							<label>Alliance Two ID:</label>
							<select id="defender_id-field" class="chosen" aria-describedby="defender_id">
								<option value=""></option>
								@foreach($alliances as $id => $name)
								<option value="{{ $id }}">{{ $name }}</option>
								@endforeach
							</select>
						</div>
						<br>
						<div>
							<label>Treaty Type:</label>
							<select id="type_id-field" class="chosen" aria-describedby="type_id">
								<option value=""></option>
								@foreach($types as $id => $type)
								<option value="{{ $id }}">{{ $type }}</option>
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
								<th>One ID</th>
								<th>Alliance One</th>
								<th>Two ID</th>
								<th>Alliance Two</th>
								<th>Type</th>
								<th>Actions</th>
							</tr>
						</thead>
						<tbody id="edit_body">
						@foreach($treaties as $treaty)
							<tr id="{{ $treaty['one'] + $treaty['two'] }}">
								<td>{{ $treaty['one'] }}</td>
								<td>{{ $treaty['aname'] }}</td>
								<td>{{ $treaty['two'] }}</td>
								<td>{{ $treaty['dname'] }}</td>
								<td>{{ $types[$treaty['type']] }}</td>
								<td>
									<button type="button" class="btn btn-default del_button" data-aid="{{ $treaty['one'] }}" data-did="{{ $treaty['two'] }}">
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
				<input data-toggle="modal" data-target="#addModal" type="button" class="btn btn-primary" value="Add Treaty">
			</div>
		</div>
		<script type="application/javascript">
			var token = "{{ csrf_token() }}";
			var types = {!! json_encode($types) !!};
			$("#addModal").on("hide.bs.modal", function(){
				$("#aggressor_id-field").chosen().val("").trigger('chosen:updated');
				$("#defender_id-field").chosen().val("").trigger('chosen:updated');
				$("#type_id-field").chosen().val("").trigger('chosen:updated');
			});
			$("#add-war").click(function(){
				var attacker = $("#aggressor_id-field").chosen().val();
				var defender = $("#defender_id-field").chosen().val();
				var type = $("#type_id-field").chosen().val();
				if(attacker == "" || defender == "" || type == "" || type == 0)
					alert("You left something blank or didn't select a treaty type.");
				else{
					$.post("{!! url('ajax_api/treaty_web_man/add') !!}", {'aggressor': attacker, 'defender': defender, 'type': type, '_token': token}).done(function(data){
						var result = JSON.parse(data);
						var display = result.status;
						if(result.code == 0){
							display += "\nError: " + result.error;
							alert(display);
						}
						if(result.code == 1){
							var app = '<tr id="' + result.treaty.id + '"><td>' + result.treaty.one + '</td><td>' + result.treaty.aname + '</td>';
							app += '<td>' + result.treaty.two + '</td><td>' + result.treaty.dname + '</td>';
							app += '<td>' + types[result.treaty.type] + '</td>';
							app += '<td><button type="button" class="btn btn-default del_button" data-aid="' + result.treaty.one + '" data-did="' + result.treaty.two + '"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span></button></td></tr>';
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
						var conf = confirm("Are you sure you wish to delete this treaty?");
						if(conf){
							$.get("{!! url('ajax_api/treaty_web_man/del') !!}/" + aid + "_" + did).done(function(data){
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