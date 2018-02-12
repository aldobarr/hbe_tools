@extends('layouts.master')
@section('content')
@if(!empty($success))
<div class="container">
			<div class="panel panel-success success-box">
				<div class="panel-heading">
					<h3 class="panel-title">Success!</h3>
				</div>
				<div class="panel-body">
					You have successfully scheduled a mass pm to be sent to the alliance in game.
				</div>
			</div>
		</div>
@else
<div id="sendPMModal" class="modal staticModal" tabindex="-1" role="dialog" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h1 class="text-center">Send PM</h1>
					</div>
					<div class="modal-body">
						<form action="/pm" method="POST" class="form col-md-12 center-block">
							{!! csrf_field() !!}
							<div class="form-group">
								<input type="text" name="subject" maxlength="50" class="form-control input-lg" placeholder="Subject" value="{{ old('subject') }}" required>
							</div>
							<div class="form-group">
								<textarea name="body" class="form-control input-lg" placeholder="Body" style="max-width: 100%;" required>{{ old('body') }}</textarea>
							</div>
							<div class="form-group">
								<input type="submit" name="send" class="btn btn-primary btn-lg btn-block" value="Send">
							</div>
						</form>
					</div>
					<div class="modal-footer"></div>
				</div>
			</div>
		</div>
		<div class="container">
			<div class="panel panel-default">
				<div class="panel-body">
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
			</div>
		</div>
@endif
@stop
