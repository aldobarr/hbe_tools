@extends('layouts.master')
@section('content')
@if(!empty($error))
<div class="container">
			<div class="panel panel-danger error-box">
				<div class="panel-heading">
					<h3 class="panel-title">Sign In Error</h3>
				</div>
				<div class="panel-body">
					{{ $error }}
				</div>
			</div>
		</div>
@elseif(!empty($success))
<div class="container">
			<div class="panel panel-success success-box">
				<div class="panel-heading">
					<h3 class="panel-title">Success!</h3>
				</div>
				<div class="panel-body">
					You have signed in successfully
				</div>
			</div>
		</div>
@else
<div id="signinMobileModal" class="modal staticModal" tabindex="-1" role="dialog" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h1 class="text-center">Mobile Signin</h1>
					</div>
					<div class="modal-body">
						<form action="/signin/mobile" method="POST" class="form col-md-12 center-block">
							{!! csrf_field() !!}
							<div class="form-group">
								<input type="text" name="id" class="form-control input-lg" placeholder="Nation ID" value="{{ old('id') }}" required>
							</div>
							<div class="form-group row">
								<div class="col-md-3"><input type="text" name="credits" class="form-control input-lg" placeholder="Credits" value="{{ old('credits') }}" required></div>
								<div class="col-md-3"><input type="text" name="coal" class="form-control input-lg" placeholder="Coal" value="{{ old('coal') }}" required></div>
								<div class="col-md-3"><input type="text" name="oil" class="form-control input-lg" placeholder="Oil" value="{{ old('oil') }}" required></div>
								<div class="col-md-3"><input type="text" name="uranium" class="form-control input-lg" placeholder="Uranium" value="{{ old('uranium') }}" required></div>
							</div>
							<div class="form-group row">
								<div class="col-md-3"><input type="text" name="lead" class="form-control input-lg" placeholder="Lead" value="{{ old('lead') }}" required></div>
								<div class="col-md-3"><input type="text" name="iron" class="form-control input-lg" placeholder="Iron" value="{{ old('iron') }}" required></div>
								<div class="col-md-3"><input type="text" name="bauxite" class="form-control input-lg" placeholder="Bauxite" value="{{ old('bauxite') }}" required></div>
								<div class="col-md-3"><input type="text" name="gasoline" class="form-control input-lg" placeholder="Gasoline" value="{{ old('gasoline') }}" required></div>
							</div>
							<div class="form-group row">
								<div class="col-md-3"><input type="text" name="munitions" class="form-control input-lg" placeholder="Munitions" value="{{ old('munitions') }}" required></div>
								<div class="col-md-3"><input type="text" name="steel" class="form-control input-lg" placeholder="Steel" value="{{ old('steel') }}" required></div>
								<div class="col-md-3"><input type="text" name="aluminum" class="form-control input-lg" placeholder="Aluminum" value="{{ old('aluminum') }}" required></div>
								<div class="col-md-3"><input type="text" name="food" class="form-control input-lg" placeholder="Food" value="{{ old('food') }}" required></div>
							</div>
							<div class="form-group">
								<input type="text" name="cash" class="form-control input-lg" placeholder="Cash" value="{{ old('cash') }}" required>
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
@endif
@stop