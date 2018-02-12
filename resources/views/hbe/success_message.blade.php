@extends('layouts.master')
@section('content')
<div class="container">
			<div class="panel panel-success">
				<div class="panel-heading">
					<h3 class="panel-title">Success</h3>
				</div>
				<div class="panel-body">
				@if(isset($safe))
					{!! $message or 'Message left blank' !!}
				@else
					{{ $message or 'Message left blank' }}
				@endif
				</div>
			</div>
		</div>
@stop