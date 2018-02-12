@extends('layouts.master')
@section('content')
<div class="container">
			<div class="panel panel-default">
				<div class="panel-body row" id="members_table">
					<div class="col-md-7 table-responsive">
						<table class="table table-hover">
							<thead>
								<tr>
									<th>ID</th>
									<th>Username</th>
									<th>Role</th>
									<th>Actions</th>
								</tr>
							</thead>
							<tbody id="edit_body">
							@foreach($users as $user)
								<tr id="{{ $user['id'] }}">
									<td>{{ $user['id'] }}</td>
									<td>{{ $user['display_name'] }}</td>
									@if(Auth::user()->auth_level >= $user['auth_level'] && AuthHelper::canAccess('edit_user_role'))
									<td><a href="#" data-name="auth_level" data-type="select" data-pk="{{ $user['id'] }}" data-value="{{ $roles[$user['auth_level']]['id'] }}" data-title="Select User's Role">{{ $roles[$user['auth_level']]['name'] }}</a></td>
										@if(Auth::user()->auth_level > $user['auth_level'] && AuthHelper::canAccess('delete_user'))
									<td>
										<button type="button" class="btn btn-default del_button" data-id="{{ $user['id'] }}">
											<span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
										</button>
									</td>
										@else
									<td></td>
										@endif
									@else
									<td>{{ $roles[$user['auth_level']]['name'] }}</td>
									<td></td>
									@endif
								</tr>
							@endforeach
							</tbody>
						</table>
					</div>
					<div class="col-md-5 table-responsive">
						<table class="table table-hover">
							<thead>
								<tr>
									<th>Role Name</th>
									<th>Description</th>
								</tr>
							</thead>
							<tbody>
							@foreach($roles as $role)
								<tr>
									<td>{{ $role['name'] }}</td>
									<td>{{ $role['description'] }}</td>
								</tr>
							@endforeach
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
		<script type="application/javascript">
			@if(AuthHelper::canAccess('edit_user_role'))
			$.fn.editable.defaults.mode = 'inline';
			var token = "{{ csrf_token() }}";
			$(document).ready(function() {
				$("#edit_body").editable({
					selector: "a",
					source: [
						@foreach($roles as $role)
						{value: {{ $role['id'] }}, text: "{{ $role['name'] }}"},
						@endforeach
					],
					url: "{!! url('ajax_api/user_man') !!}",
					params: {
						'_token': token
					}
				});
			});
			@endif
			@if(AuthHelper::canAccess('delete_user'))
			$(".del_button").click(function(){
				var id = $(this).attr("data-id");
				if(id > 1){
					var conf = confirm("Are you sure you wish to delete this user?");
					if(conf){
						$.get("{!! url('ajax_api/user_man/del') !!}/" + id).done(function(data){
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
			});
			@endif
		</script>
@stop