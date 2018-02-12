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
<div class="container-fluid">
			<div class="panel panel-default">
				<div class="panel-body" style="text-align: center;">
					<h1>Britannian Warchest Form</h1><br>
					<small>Please paste <strong>HTML source code:<strong></small>
					<form action="/" method="POST">
						{!! csrf_field() !!}
						<textarea name="nation_data" rows="20" class="form-control" id="paste_source"></textarea><br><br>
						{!! HTML::link('signin/mobile', 'Mobile', array('class' => 'btn btn-primary', 'style' => 'float: none;')) !!}
						<input name="submit" type="submit" class="btn btn-primary" value="Submit" style="float: none;">
					</form>
					<br><br>
					<h1>How to input your data</h1>
					<p>
						Sign in to your nation. Then, right click on an empty part of the page, and select "Page Source" or "View Page Source".  The picture depicts what you might see if you are using Chrome, but this applies to Firefox and Internet Explorer as well.
					</p>
					<img src="{!! asset('images/howto-signin-viewsrc.png') !!}" class="center-block img-responsive" border="2" />
					
					<p>
						You should then have a new window open up.  Copy everything that you see in this window (an easy way is to right click, and hit "Select all").
					</p>
					<img src="{!! asset('images/howto-signin-copysrc.png') !!}" class="center-block img-responsive" border="2" />
					
					<p>
						Then paste everything into the empty space. If you receive any errors please report it to a member of Internal Affairs!
					</p>
					<img src="{!! asset('images/howto-signin-pastesrc.png') !!}" class="center-block img-responsive" border="2" />
				</div>
			</div>
		</div>
@endif
@stop