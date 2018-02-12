@extends('layouts.master')
@section('content')
<div id="loginModal" class="modal staticModal" tabindex="-1" role="dialog" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h1 class="text-center">Reset Password</h1>
					</div>
					<div class="modal-body">
						<form action="/password/reset" method="POST" class="form col-md-12 center-block">
							{!! csrf_field() !!}
							<input type="hidden" name="token" value="{{ $token }}">
							<div class="form-group">
								<input type="email" name="email" class="form-control input-lg" placeholder="Email" value="{{ old('email') }}" required>
							</div>
							<div class="form-group">
								<input type="password" name="password" class="form-control input-lg" placeholder="Password" required>
							</div>
							<div class="form-group">
								<input type="password" name="password_confirmation" class="form-control input-lg" placeholder="Password" required>
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