@extends('layouts.master')
@section('content')
<div class="container">
			<div class="panel panel-default">
				<div class="panel-body table-responsive" id="members_table">
					<table class="table table-hover">
						<thead>
							<tr>
								<th>Alliance ID</th>
								<th>Name</th>
								<th>Acronym</th>
								<th>Score</th>
								<th>Flag</th>
							</tr>
						</thead>
						<tbody id="edit_body">
						@foreach($alliances as $alliance)
							<tr>
								<td>{{ $alliance['id'] }}</td>
								<td>{{ $alliance['name'] }}</td>
								<td>{{ $alliance['label'] }}</td>
								<td>{{ number_format($alliance['size']) }}</td>
								<td>{!! HTML::image($alliance['flag'], $alliance['name'], array('class' => 'img-responsive', 'style' => 'max-height: 100px;')) !!}</td>
							</tr>
						@endforeach
						</tbody>
					</table>
				</div>
			</div>
		</div>
@stop