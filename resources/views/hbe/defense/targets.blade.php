@extends('layouts.master')
@section('content')
<div id="findTargetDataModal" class="modal staticModal" tabindex="-1" role="dialog" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h1 class="text-center">Find Members In Range</h1>
					</div>
					<div class="modal-body">
						<form action="/defense/target" method="POST" class="form col-md-12 center-block">
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
@stop