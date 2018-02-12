@extends('layouts.master')
@section('content')
@if(!empty($success))
<div class="container">
			<div class="panel panel-success success-box">
				<div class="panel-heading">
					<h3 class="panel-title">Success!</h3>
				</div>
				<div class="panel-body">
					You have successfully added your nation to the rebuild calculation.
				</div>
			</div>
		</div>
@elseif(!empty($update))
<div class="container">
			<div class="panel panel-danger error-box">
				<div class="panel-heading">
					<h3 class="panel-title">Error!</h3>
				</div>
				<div class="panel-body">
					The rebuild page is currently undergoing an update. Please return later.
				</div>
			</div>
		</div>
@elseif(!empty($infra))
<div id="recordNationDataModal" class="modal staticModal" tabindex="-1" role="dialog" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h1 class="text-center">Check Infra Purchase Cost</h1>
					</div>
					<div class="modal-body">
						<form action="/calc/infra" method="POST" class="form col-md-12 center-block">
							{!! csrf_field() !!}
							<div class="form-group">
								<input type="text" name="id" class="form-control input-lg" placeholder="Nation ID" value="{{ old('id') }}" required>
							</div>
							<div class="form-group">
								<input type="text" name="infra" class="form-control input-lg" placeholder="Infra Amount Per City" value="{{ old('infra') }}" required>
							</div>
							<div class="form-group"><label><input type="checkbox" name="urb" value="1"> Urbanization</label></div>
							<div class="form-group"><label><input type="checkbox" name="ccep" value="1"> Center for Civil Engineering Project</label></div>
							<div class="form-group">
								<input type="submit" name="submit" class="btn btn-primary btn-lg btn-block" value="Submit">
							</div>
						</form>
					</div>
					<div class="modal-footer"></div>
				</div>
			</div>
		</div>
@elseif(!empty($land))
<div id="recordNationDataModal" class="modal staticModal" tabindex="-1" role="dialog" aria-hidden="true">
			<div class="modal-dialog">
				<div style="height: 317px;" class="modal-content">
					<div class="modal-header">
						<h1 class="text-center">Check Land Purchase Cost</h1>
					</div>
					<div class="modal-body">
						 <form action="/calc/land" method="POST" class="form col-md-12 center-block">
							{!! csrf_field() !!}
							<div class="form-group">
								<input type="text" name="id" class="form-control input-lg" placeholder="Nation ID" value="{{ old('id') }}" required>
							</div>
							<div class="form-group">
								<input type="text" name="land" class="form-control input-lg" placeholder="Land Amount Per City" value="{{ old('land') }}" required>
							</div>
							<div class="form-group">
								<input type="submit" name="submit" class="btn btn-primary btn-lg btn-block" value="Submit">
							</div>
						</form>
					</div>
				</div>
			</div>
@elseif(!empty($warchest))
<div id="recordNationDataModal" class="modal staticModal" tabindex="-1" role="dialog" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h1 class="text-center">Calculate Warchest</h1>
					</div>
					<div class="modal-body">
						<form action="/calc/warchest" method="POST" class="form col-md-12 center-block">
							{!! csrf_field() !!}
							<div class="form-group">
								<input type="text" name="id" class="form-control input-lg" placeholder="Nation ID" value="{{ old('id') }}" required>
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
@else
<div id="recordNationDataModal" class="modal staticModal" tabindex="-1" role="dialog" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h1 class="text-center">Record Nation Data</h1>
					</div>
					<div class="modal-body">
						<form action="/ve/form" method="POST" class="form col-md-12 center-block">
							{!! csrf_field() !!}
							<div class="form-group">
								<input type="text" name="id" class="form-control input-lg" placeholder="Nation ID" value="{{ old('id') }}" required>
							</div>
							<div class="form-group">
								<input type="text" name="infra" class="form-control input-lg" placeholder="Old Infra Amount Per City" value="{{ old('infra') }}" required>
							</div>
							<div class="form-group"><label><input type="checkbox" name="ccep" value="1"> Center for Civil Engineering Project</label></div>
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
