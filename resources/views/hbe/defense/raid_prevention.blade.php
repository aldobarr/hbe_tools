@extends('layouts.master')
@section('content')
<div class="container">
	@if(!$is_protected)
		<div class="panel panel-info">
			<div class="panel-heading">
				<h3 class="panel-title">Status</h3>
			</div>
			<div class="panel-body">Raid prevention is currently <span style="color: red;"><strong>disabled.</strong></span></div>
		</div>
		<div class="pull-right">
			{!! Form::open(['url' => 'raid/prevention']) !!}
				<input type="submit" name="enable" value="Enable Raid Prevention" class="btn btn-primary">
			{!! Form::close() !!}
		</div>
	@else
		@if(!empty($deposit))
		<div class="alert alert-success">
			<strong>Success!</strong> We successfully deposited the following:<br><br>
			<ul>
			@foreach($deposit as $key => $val)
				<li>{{ (ucfirst($key) . ($key == 'cash' ? ' - $' : ' - ') . number_format(($val * -1), 2)) }}</li>
			@endforeach
			</ul>
		</div>
		@endif
		<div class="row">
			<div class="col-md-6">
				<div class="panel panel-info">
					<div class="panel-heading">
						<h3 class="panel-title">Status</h3>
					</div>
					<div class="panel-body">
						<span>Raid prevention is currently <span style="color: green;"><strong>enabled.</strong></span></span><br><br>
						<div class="pull-right">
							{!! Form::open(['url' => 'raid/prevention']) !!}
								<input type="submit" name="disable" value="Disable Raid Prevention" class="btn btn-primary">
							{!! Form::close() !!}
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-6">
				<div class="panel panel-default">
					<div class="panel-heading">
						<h3 class="panel-title">Withdraw from the Cayman Isles</h3>
					</div>
					<div class="panel-body">
						{!! Form::open(['url' => 'raid/prevention/withdraw']) !!}
							<div class="input-group">
								<span class="input-group-addon">Cash</span>
								<input type="text" name="cash" value="{{ old('cash', 0) }}" class="form-control">
								<span class="input-group-addon">${{ number_format($available['cash'], 2) }}</span>
							</div>
							<div class="input-group">
								<span class="input-group-addon">Food</span>
								<input type="text" name="food" value="{{ old('food', 0) }}" class="form-control">
								<span class="input-group-addon">{{ number_format($available['food'], 2) }}</span>
							</div>
							<div class="input-group">
								<span class="input-group-addon">Coal</span>
								<input type="text" name="coal" value="{{ old('coal', 0) }}" class="form-control">
								<span class="input-group-addon">{{ number_format($available['coal'], 2) }}</span>
							</div>
							<div class="input-group">
								<span class="input-group-addon">Oil</span>
								<input type="text" name="oil" value="{{ old('oil', 0) }}" class="form-control">
								<span class="input-group-addon">{{ number_format($available['oil'], 2) }}</span>
							</div>
							<div class="input-group">
								<span class="input-group-addon">Uranium</span>
								<input type="text" name="uranium" value="{{ old('uranium', 0) }}" class="form-control">
								<span class="input-group-addon">{{ number_format($available['uranium'], 2) }}</span>
							</div>
							<div class="input-group">
								<span class="input-group-addon">Lead</span>
								<input type="text" name="lead" value="{{ old('lead', 0) }}" class="form-control">
								<span class="input-group-addon">{{ number_format($available['lead'], 2) }}</span>
							</div>
							<div class="input-group">
								<span class="input-group-addon">Iron</span>
								<input type="text" name="iron" value="{{ old('iron', 0) }}" class="form-control">
								<span class="input-group-addon">{{ number_format($available['iron'], 2) }}</span>
							</div>
							<div class="input-group">
								<span class="input-group-addon">Bauxite</span>
								<input type="text" name="bauxite" value="{{ old('bauxite', 0) }}" class="form-control">
								<span class="input-group-addon">{{ number_format($available['bauxite'], 2) }}</span>
							</div>
							<div class="input-group">
								<span class="input-group-addon">Gasoline</span>
								<input type="text" name="gasoline" value="{{ old('gasoline', 0) }}" class="form-control">
								<span class="input-group-addon">{{ number_format($available['gasoline'], 2) }}</span>
							</div>
							<div class="input-group">
								<span class="input-group-addon">Munitions</span>
								<input type="text" name="munitions" value="{{ old('munitions', 0) }}" class="form-control">
								<span class="input-group-addon">{{ number_format($available['munitions'], 2) }}</span>
							</div>
							<div class="input-group">
								<span class="input-group-addon">Steel</span>
								<input type="text" name="steel" value="{{ old('steel', 0) }}" class="form-control">
								<span class="input-group-addon">{{ number_format($available['steel'], 2) }}</span>
							</div>
							<div class="input-group">
								<span class="input-group-addon">Aluminum</span>
								<input type="text" name="aluminum" value="{{ old('aluminum', 0) }}" class="form-control">
								<span class="input-group-addon">{{ number_format($available['aluminum'], 2) }}</span>
							</div><br>
							<div class="pull-right"><input type="submit" name="submit" value="Withdraw" class="btn btn-success"></div>
						{!! Form::close() !!}
					</div>
				</div>
			</div>
		</div>
	@endif
	</div>
@stop
