@extends('layouts.master')
@section('content')
<div class="container">
			<div class="panel panel-danger">
				<div class="panel-heading">
					<h3 class="panel-title">Error</h3>
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