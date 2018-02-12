@extends('layouts.master')
@section('content')

<div id="calcRebuildCostModal" class="modal staticModal" tabindex="-1" role="dialog" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h1 class="text-center">Warchest Requirements</h1>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-md-6">
								<ul class="list-group">
									<li class="list-group-item">
										Food<span class="badge">{{ number_format($food, 2) }}</span>
									</li>
									<li class="list-group-item">
										Gasoline<span class="badge">{{ number_format($gas, 2) }}</span>
									</li>
									<li class="list-group-item">
										Steel<span class="badge">{{ number_format($steel, 2) }}</span>
									</li>
								</ul>
							</div>
							<div class="col-md-6">
								<ul class="list-group">
									<li class="list-group-item">
										Uranium<span class="badge">{{ number_format($uranium, 2) }}</span>
									</li>
									<li class="list-group-item">
										Munitions<span class="badge">{{ number_format($mun, 2) }}</span>
									</li>
									<li class="list-group-item">
										Aluminum<span class="badge">{{ number_format($aluminum, 2) }}</span>
									</li>
								</ul>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<ul class="list-group">
									<li class="list-group-item">
										Cash<span class="badge">${{ number_format($cash, 2) }}</span>
									</li>
								</ul>
							</div>
						</div>
					</div>
					<div class="modal-footer"></div>
				</div>
			</div>
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h1 class="text-center">Warchest Requirements With Noobs</h1>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-md-6">
								<ul class="list-group">
									<li class="list-group-item">
										Food<span class="badge">{{ number_format($with_noob['food'], 2) }}</span>
									</li>
									<li class="list-group-item">
										Gasoline<span class="badge">{{ number_format($with_noob['gas'], 2) }}</span>
									</li>
									<li class="list-group-item">
										Steel<span class="badge">{{ number_format($steel, 2) }}</span>
									</li>
								</ul>
							</div>
							<div class="col-md-6">
								<ul class="list-group">
									<li class="list-group-item">
										Uranium<span class="badge">{{ number_format($with_noob['uranium'], 2) }}</span>
									</li>
									<li class="list-group-item">
										Munitions<span class="badge">{{ number_format($with_noob['mun'], 2) }}</span>
									</li>
									<li class="list-group-item">
										Aluminum<span class="badge">{{ number_format($with_noob['aluminum'], 2) }}</span>
									</li>
								</ul>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<ul class="list-group">
									<li class="list-group-item">
										Cash<span class="badge">${{ number_format($with_noob['cash'], 2) }}</span>
									</li>
								</ul>
							</div>
						</div>
					</div>
					<div class="modal-footer"></div>
				</div>
			</div>
		</div>
@stop
