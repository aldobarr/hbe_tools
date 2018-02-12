@extends('layouts.master')
@section('content')
<div class="container-fluid">
			<form action="/defense/target/activate" method="POST" class="form col-md-12 center-block">
				{!! csrf_field() !!}
				<div class="panel panel-default">
					<div class="panel-body{{ !$empty ? ' table-responsive' : '' }}" id="members_table">
						@if($empty)
						<div style="text-align: center;">There are no targets in planning.</div>
						@else
						<table class="table table-hover">
							<thead>
								<tr>
									<th>Target Nation</th>
									<th>Target Name</th>
									<th>Target Alliance</th>
									<th>Target Thread</th>
									<th><input type="checkbox" onClick="toggle(this)"> Activate</th>
								</tr>
							</thead>
							<tbody>
							@foreach($plannings as $planning)
								<tr>
									<td>{!! HTML::link('https://politicsandwar.com/nation/id=' . $planning['target_id'], $planning['target_name'], array('target' => '_blank')) !!}</td>
									<td>{!! HTML::link('https://politicsandwar.com/nation/id=' . $planning['target_id'], $planning['target_leader'], array('target' => '_blank')) !!}</td>
									<td>{!! HTML::link('https://politicsandwar.com/alliance/id=' . $planning['target_aa_id'], $planning['target_aa'], array('target' => '_blank')) !!}</td>
									<td>{!! HTML::link('https://pnw.britannianempire.net/index.php?topic=' . $planning['thread_id'] . '.0', 'Target Thread', array('target' => '_blank')) !!}</td>
									<td><input type="checkbox" name="planning_ids[]" class="selected_targets" value="{{ $planning['id'] }}"></td>
								</tr>
							@endforeach
							</tbody>
						</table>
						@endif
					</div>
				</div>
				@if(!$empty)
				<div class="pull-right">
					<input id="submit" name="submit" type="submit" class="btn btn-primary" value="Activate">
				</div>
				@endif
			</form>
		</div>
		<script>
		function toggle(source){
			var checkboxes = document.getElementsByName("planning_ids[]");
			for(var i = 0; i<checkboxes.length; i++){
				checkboxes[i].checked = source.checked;
			}
		}
		</script>
@stop