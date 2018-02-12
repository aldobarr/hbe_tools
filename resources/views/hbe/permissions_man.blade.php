@extends('layouts.master')
@section('content')
@if(AuthHelper::canAccess('perm_add'))
<div class="modal fade" id="addModal" role="dialog" aria-labelledby="myModalLabel">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title" id="addModalLabel"></h4>
					</div>
					<div class="modal-body">
						<div class="input-group">
							<span class="input-group-addon" id="perm-name">Role Name</span>
							<input id="perm-name-field" type="text" class="form-control" aria-describedby="perm-name">
						</div>
						<br>
						<div class="input-group">
							<span class="input-group-addon" id="perm-desc">Role Description</span>
							<input id="perm-desc-field" type="text" class="form-control" aria-describedby="perm-desc">
						</div>
					</div>
					<div class="modal-footer">
						<button id="add-permission" type="button" class="btn btn-primary">Submit</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</div>
			</div>
		</div>
@endif
		<div class="container">
			<div class="panel panel-default">
				<div class="panel-body table-responsive" id="members_table">
					<table class="table table-hover">
						<thead>
							<tr>
								<th>ID</th>
								<th>Name</th>
								<th>Description</th>
								<th>Role</th>
								<th>Actions</th>
							</tr>
						</thead>
						<tbody id="edit_body">
						@foreach($permissions as $permission)
							<tr id="{{ $permission['id'] }}">
								<td>{{ $permission['id'] }}</td>
								<td><{{ AuthHelper::canAccess('perm_edit') ? 'a' : 'span' }} href="#" data-name="name" data-type="text" data-pk="{{ $permission['id'] }}" data-title="Enter Permission Name">{{ $permission['name'] }}</a></td>
								<td><{{ AuthHelper::canAccess('perm_edit') ? 'a' : 'span' }} href="#" data-name="description" data-type="text" data-pk="{{ $permission['id'] }}" data-title="Enter Permission Description">{{ $permission['description'] }}</a></td>
								<td><a href="#" data-name="auth_level" data-type="select" data-pk="{{ $permission['id'] }}" data-title="Select Permission Role" data-value="{{ $roles[$permission['auth_level']]['id'] }}">{{ $roles[$permission['auth_level']]['name'] }}</a></td>
								<td>
									@if(AuthHelper::canAccess('perm_del'))
									<button type="button" class="btn btn-default del_button" data-id="{{ $permission['id'] }}">
										<span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
									</button>
									@endif
								</td>
							</tr>
						@endforeach
						</tbody>
					</table>
				</div>
			</div>
			@if(AuthHelper::canAccess('perm_add'))
			<div class="pull-right">
				<input data-toggle="modal" data-target="#addModal" type="button" class="btn btn-primary" value="Add Permission">
			</div>
			@endif
		</div>
		<script type="application/javascript">
			$.fn.editable.defaults.mode = 'inline';
			var token = "{{ csrf_token() }}";
			var perm_edit = {{ AuthHelper::canAccess('perm_edit') ? 'true' : 'false' }};
			var perm_del = {{ AuthHelper::canAccess('perm_del') ? 'true' : 'false' }};
			$(document).ready(function() {
				$("#edit_body").editable({
					selector: "a",
					source: [
						@foreach($roles as $role)
						{value: {{ $role['id'] }}, text: "{{ $role['name'] }}"},
						@endforeach
					],
					url: "{!! url('ajax_api/perms_man') !!}",
					params: {
						'_token': token
					}
				});
			});
			$("#addModal").on("hide.bs.modal", function(){
				$("#perm-name-field").val("");
				$("#perm-desc-field").val("");
			});
			$("#add-permission").click(function(){
				var name = $("#perm-name-field").val();
				var desc = $("#perm-desc-field").val();
				if(name == "" || desc == "")
					alert("The name and description fields can not be left blank.");
				else{
					$.post("{!! url('ajax_api/perms_man/add') !!}", {'name': name, 'desc': desc, '_token': token}).done(function(data){
						var result = JSON.parse(data);
						var display = result.status;
						if(result.code == 0){
							display += "\nError: " + result.error;
							alert(display);
						}
						if(result.code == 1){
							var app = '<tr id="' + result.perm.id + '"><td>' + result.perm.id + '</td><td><' + (perm_edit ? 'a' : 'span') + ' href="#" data-name="name" data-type="text" data-pk="' + result.perm.id + '" data-title="Enter Permission Name">' + result.perm.name + '</a></td>';
							app += '<td><' + (perm_edit ? 'a' : 'span') + ' href="#" data-name="description" data-type="text" data-pk="' + result.perm.id + '" data-title="Enter Permission Description">' + result.perm.description + '</a></td>';
							app += '<td><a href="#" data-name="auth_level" data-type="select" data-pk="' + result.perm.id + '" data-title="Select Permission Role" data-value="' + result.perm.role.id + '">' + result.perm.role.name + '</a></td>';
							app += '<td>' + (perm_del ? ('<button type="button" class="btn btn-default del_button" data-id="' + result.perm.id + '"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span></button>') : '') + '</td></tr>';
							$("#edit_body").append(app);
							$("#addModal").modal("hide");
						}
					}).fail(function(){
						alert("Some error occurred.");
					});
				}
			});
			@if(AuthHelper::canAccess('del_perms'))
			$("#edit_body").on("click", "button", function(){
				if($(this).hasClass("del_button")){
					var id = $(this).attr("data-id");
					if(id != 0){
						var conf = confirm("Are you sure you wish to delete this permission?");
						if(conf){
							$.get("{!! url('ajax_api/perms_man/del') !!}/" + id).done(function(data){
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
			@endif
		</script>
@stop