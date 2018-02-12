@extends('layouts.master')
@section('content')
<div class="modal fade" id="addModal" role="dialog" aria-labelledby="myModalLabel">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title" id="addModalLabel"></h4>
					</div>
					<div class="modal-body">
						<div class="input-group">
							<span class="input-group-addon" id="role-name">Role Name</span>
							<input id="role-name-field" type="text" class="form-control" aria-describedby="role-name">
						</div>
						<br>
						<div class="input-group">
							<span class="input-group-addon" id="role-desc">Role Description</span>
							<input id="role-desc-field" type="text" class="form-control" aria-describedby="role-desc">
						</div>
					</div>
					<div class="modal-footer">
						<button id="add-role" type="button" class="btn btn-primary">Submit</button>
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
								<th>ID</th>
								<th>Role</th>
								<th>Description</th>
								<th>Actions</th>
							</tr>
						</thead>
						<tbody id="edit_body">
						@foreach($roles as $role)
							<tr id="{{ $role['id'] }}">
								<td>{{ $role['id'] }}</td>
								<td><a href="#" data-name="name" data-type="text" data-pk="{{ $role['id'] }}" data-title="Enter Role Name">{{ $role['name'] }}</a></td>
								<td><a href="#" data-name="description" data-type="text" data-pk="{{ $role['id'] }}" data-title="Enter Role Description">{{ $role['description'] }}</a></td>
								@if($role['id'] != 0)
								<td>
									<button type="button" class="btn btn-default del_button" data-id="{{ $role['id'] }}">
										<span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
									</button>
								</td>
								@endif
							</tr>
						@endforeach
						</tbody>
					</table>
				</div>
			</div>
			<div class="pull-right">
				<input data-toggle="modal" data-target="#addModal" type="button" class="btn btn-primary" value="Add Role">
			</div>
		</div>
		<script type="application/javascript">
			$.fn.editable.defaults.mode = 'inline';
			var token = "{{ csrf_token() }}";
			$(document).ready(function() {
				$("#edit_body").editable({
					selector: "a",
					url: "{!! url('ajax_api/roles_man') !!}",
					params: {
						'_token': token
					}
				});
			});
			$("#addModal").on("hide.bs.modal", function(){
				$("#role-name-field").val("");
				$("#role-desc-field").val("");
			});
			$("#add-role").click(function(){
				var name = $("#role-name-field").val();
				var desc = $("#role-desc-field").val();
				if(name == "" || desc == "")
					alert("The name and description fields can not be left blank.");
				else{
					$.post("{!! url('ajax_api/roles_man/add') !!}", {'name': name, 'desc': desc, '_token': token}).done(function(data){
						var result = JSON.parse(data);
						var display = result.status;
						if(result.code == 0){
							display += "\nError: " + result.error;
							alert(display);
						}
						if(result.code == 1){
							var app = '<tr id="' + result.role.id + '"><td>' + result.role.id + '</td><td><a href="#" data-name="name" data-type="text" data-pk="' + result.role.id + '" data-title="Enter Role Name">' + result.role.name + '</a></td>';
							app += '<td><a href="#" data-name="description" data-type="text" data-pk="' + result.role.id + '" data-title="Enter Role Description">' + result.role.description + '</a></td>';
							app += '<td><button type="button" class="btn btn-default del_button" data-id="' + result.role.id + '"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span></button></td></tr>';
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
					var id = $(this).attr("data-id");
					if(id != 0){
						var conf = confirm("Are you sure you wish to delete this role?");
						if(conf){
							$.get("{!! url('ajax_api/roles_man/del') !!}/" + id).done(function(data){
								var result = JSON.parse(data);
								var display = result.status;
								if(result.code == 0){
									display += "\nError: " + result.error;
									alert(display);
								}
								if(result.code == 1){
									$("#" + id).fadeOut(function(){
										if(result.reload){
											do{
												var count = 1;
												$("#" + result.reload_id).children().each(function(){
													if(count == 1)
														$(this).text(result.reload_id - 1);
													else if(count == 2 || count == 3)
														$(this).children().first().attr("data-pk", result.reload_id - 1);
													else if(count == 4)
														$(this).children().first().attr("data-id", result.reload_id - 1);
													count++;
												});
												$("#" + result.reload_id).attr("id", result.reload_id - 1);
											}while(result.reload_id++ <= result.reload_max);
										}
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
		</script>
@stop