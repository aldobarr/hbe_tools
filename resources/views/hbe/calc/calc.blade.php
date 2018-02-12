@extends('layouts.master')
@section('content')

@if(!empty($land_form))
<div id="calcRebuildCostModal" class="modal staticModal" tabindex="-1" role="dialog" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h1 class="text-center">Land Purchase Cost</h1>
					</div>
					<div class="modal-body">
						<form action="/calc/land" method="POST" class="form col-md-12 center-block">
							{!! csrf_field() !!}
							<div class="row">
								<div class="col-md-4"><strong>Average Land Per City:</strong></div>
								<div class="col-md-8">{{ number_format($avg_land, 2) }}</div>
							</div>
							<div class="row">
								<div class="col-md-4"><strong>Total Land:</strong></div>
								<div class="col-md-8">{{ number_format($total_land, 2) }}</div>
							</div>
							<div class="row">
								<div class="col-md-4"><strong>Total Buy:</strong></div>
								<div class="col-md-8">{{ number_format($land_buy, 2) }}</div>
							</div>
							<div class="row">
								<div class="col-md-4"><strong>Total Cost:</strong></div>
								<div class="col-md-8">${{ number_format($cost, 2) }}</div>
							</div><br>
							<div class="form-group">
								<input type="text" name="land" class="form-control input-lg" placeholder="Land Amount Per City" value="{{ $land }}" required>
							</div>
							<div class="form-group">
								<input type="submit" name="submit" class="btn btn-primary btn-lg btn-block" value="Recalculate">
							</div>
							<input type="hidden" name="id" value="{{ $id }}">
							@foreach($cities as $land_vals)
							<input type="hidden" name="cities[]" value="{{ $land_vals }}">
							@endforeach
						</form>
					</div>
					<div class="modal-footer"></div>
				</div>
			</div>
		</div>
@else
<div id="calcRebuildCostModal" class="modal staticModal" tabindex="-1" role="dialog" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h1 class="text-center">Infra Purchase Cost</h1>
					</div>
					<div class="modal-body">
						<form action="/calc/infra" method="POST" class="form col-md-12 center-block">
							{!! csrf_field() !!}
							<div class="row">
								<div class="col-md-4"><strong>Average Infra Per City:</strong></div>
								<div class="col-md-8">{{ number_format($avg_infra, 2) }}</div>
							</div>
							<div class="row">
								<div class="col-md-4"><strong>Total Infra:</strong></div>
								<div class="col-md-8">{{ number_format($total_infra, 2) }}</div>
							</div>
							<div class="row">
								<div class="col-md-4"><strong>Total Buy:</strong></div>
								<div class="col-md-8">{{ number_format($infra_buy, 2) }}</div>
							</div>
							<div class="row">
								<div class="col-md-4"><strong>Total Cost:</strong></div>
								<div class="col-md-8">${{ number_format($cost, 2) }}</div>
							</div><br>
							<div class="form-group">
								<input type="text" name="infra" class="form-control input-lg" placeholder="Infra Amount Per City" value="{{ $infra }}" required>
							</div>
							<div class="form-group">
								<input type="submit" name="submit" class="btn btn-primary btn-lg btn-block" value="Recalculate">
							</div>
							<input type="hidden" name="id" value="{{ $id }}">
							@if($urb)
							<input type="hidden" name="urb" value="1">
							@endif
							@if($ccep)
							<input type="hidden" name="ccep" value="1">
							@endif
							@foreach($cities as $infra_vals)
							<input type="hidden" name="cities[]" value="{{ $infra_vals }}">
							@endforeach
						</form>
					</div>
					<div class="modal-footer"></div>
				</div>
			</div>
		</div>
@endif
@stop
