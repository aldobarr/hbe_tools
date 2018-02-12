@extends('layouts.master')
@section('content')
@if (session('status'))
<div class="container">
			<div class="alert alert-success">
				{{ session('status') }}
			</div>
		</div>
@endif
<div id="loginModal" class="modal staticModal" tabindex="-1" role="dialog" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h1 class="text-center">Password Recovery</h1>
					</div>
					<div class="modal-body">
						<form action="/password/email" method="POST" class="form col-md-12 center-block">
							{!! csrf_field() !!}
							<div class="form-group">
								<input type="email" name="email" class="form-control input-lg" placeholder="Email" required>
							</div>
							<div class="form-group">
								<input type="submit" name="submit" class="btn btn-primary btn-lg btn-block" value="Submit">
							</div>
						</form>
					</div>
					<div class="modal-footer"></div>
				</div>
			</div>
		</div>
@stop