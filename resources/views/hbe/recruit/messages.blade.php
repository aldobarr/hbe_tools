@extends('layouts.master')
@section('content')
<div class="modal fade" id="addModal" role="dialog" aria-labelledby="myModalLabel">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title" id="addModalLabel">Add Recruitment Message</h4>
					</div>
					<div class="modal-body">
						<div class="input-group">
							<span class="input-group-addon" id="subject">Subject</span>
							<input id="subject-field" type="text" maxlength="50" class="form-control" aria-describedby="subject">
						</div>
						<br>
						<div>
							<label>Body:</label>
							<textarea id="body-field" class="form-control" style="max-width: 100%;"></textarea>
						</div>
						<div>
							<label>Type:</label>
							<select id="type-field" class="form-control">
							@foreach($types as $key => $type)
								<option value="{{ $key }}">{{ $type }}</option>
							@endforeach
							</select>
						</div>
						<br>
						<div class="table-responsive">
							<table class="table table-hover">
								<thead>
									<tr>
										<th>Variable</th>
										<th>Description</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td>{ID}</td>
										<td>The user's nation id.</td>
									</tr>
									<tr>
										<td>{LEADER}</td>
										<td>The user's leader name.</td>
									</tr>
									<tr>
										<td>{NATION}</td>
										<td>The user's nation name.</td>
									</tr>
									<tr>
										<td>{COLOR}</td>
										<td>The user's color.</td>
									</tr>
									<tr>
										<td>{CONTINENT}</td>
										<td>The user's continent.</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
					<div class="modal-footer">
						<button id="add-message" type="button" class="btn btn-primary">Submit</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					</div>
				</div>
			</div>
		</div>
		<div class="container">
			<div class="panel panel-default">
				<div class="panel-body table-responsive">
					<table class="table table-hover">
						<thead>
							<tr>
								<th>ID</th>
								<th>Subject</th>
								<th>Body</th>
								<th>Type</th>
								<th>Active</th>
								<th>Actions</th>
							</tr>
						</thead>
						<tbody id="edit_body">
						@foreach($messages as $message)
							<tr id="{{ $message['id'] }}">
								<td>{{ $message['id'] }}</td>
								<td><a href="#" data-name="subject" data-type="text" data-pk="{{ $message['id'] }}" data-title="Enter Message Subject">{{ $message['subject'] }}</a></td>
								<td><a href="#" data-name="body" data-type="textarea" data-pk="{{ $message['id'] }}" data-title="Enter Message Body">{{ $message['body'] }}</a></td>
								<td><a href="#" data-name="type" data-type="select" data-pk="{{ $message['id'] }}" data-title="Select Message Type" data-value="{{ $message['type'] }}">{{ $types[$message['type']] }}</a></td>
								<td><span data-id="{{ $message['id'] }}" class="active glyphicon glyphicon-{{ empty($message['active']) ? 'remove' : 'ok' }}" style="color: {{ empty($message['active']) ? 'red' : 'green' }};"></span></td>
								<td>
									<button type="button" class="btn btn-default del_button" data-id="{{ $message['id'] }}">
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
				<input data-toggle="modal" data-target="#addModal" type="button" class="btn btn-primary" value="Add Message">
			</div>
		</div>
		<script type="application/javascript">
			$.fn.editable.defaults.mode = 'inline';
			var token = "{{ csrf_token() }}";
			var types = {!! json_encode($types) !!};
			$(document).ready(function() {
				$("#edit_body").editable({
					selector: "a",
					source: [
						@foreach($types as $key => $type)
						{value: {{ $key }}, text: "{{ $type }}"},
						@endforeach
					],
					url: "{!! url('ajax_api/message_man') !!}",
					params: {
						'_token': token
					}
				});
			});
			$("#addModal").on("hide.bs.modal", function(){
				$("#subject-field").val("");
				$("#body-field").val("");
			});
			$("#add-message").click(function(){
				var subject = $("#subject-field").val();
				var body = $("#body-field").val();
				var type = $("#type-field").val();
				if(subject == "" || body == "")
					alert("The subject and body fields can not be left blank.");
				else{
					$.post("{!! url('ajax_api/message_man/add') !!}", {'subject': subject, 'body': body, 'type': type, '_token': token}).done(function(data){
						var result = JSON.parse(data);
						var display = result.status;
						if(result.code == 0){
							display += "\nError: " + result.error;
							alert(display);
						}
						if(result.code == 1){
							var app = '<tr id="' + result.message.id + '"><td>' + result.message.id + '</td><td><a href="#" data-name="subject" data-type="text" data-pk="' + result.message.id + '" data-title="Enter Message Subject">' + result.message.subject + '</a></td>';
							app += '<td><a href="#" data-name="body" data-type="textarea" data-pk="' + result.message.id + '" data-title="Enter Message Body">' + result.message.body + '</a></td>';
							app += '<td><a href="#" data-name="type" data-type="select" data-pk="' + result.message.id + '" data-title="Select Message Type" data-value="' + result.message.type + '">' + types[result.message.type] + '</a></td>';
							app += '<td><span data-id="' + result.message.id + '" class="active glyphicon glyphicon-' + (result.message.active == 0 ? 'remove' : 'ok') + '" style="color: ' + (result.message.active == 0 ? 'red' : 'green') + ';"></span></td>';
							app += '<td><button type="button" class="btn btn-default del_button" data-id="' + result.message.id + '"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span></button></td></tr>';
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
						var conf = confirm("Are you sure you wish to delete this message?");
						if(conf){
							$.get("{!! url('ajax_api/message_man/del') !!}/" + id).done(function(data){
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
			$("#edit_body").on("click", "span", function(){
				if($(this).hasClass("active")){
					var id = $(this).attr("data-id");
					var span = $(this);
					$.get("{!! url('ajax_api/message_man/toggle') !!}/" + id).done(function(data){
						var result = JSON.parse(data);
						var display = result.status;
						if(result.code == 0){
							display += "\nError: " + result.error;
							alert(display);
						}
						if(result.code == 1){
							span.fadeOut("fast", function(){
								$(this).removeClass(result.old == 1 ? "glyphicon-ok" : "glyphicon-remove").addClass(result.old == 1 ? "glyphicon-remove" : "glyphicon-ok");
								$(this).css("color", (result.old == 1 ? "red" : "green")).fadeIn("fast");
							});
						}
					}).fail(function(){
						alert("Some error occurred.");
					});
				}
			});
		</script>
@stop
