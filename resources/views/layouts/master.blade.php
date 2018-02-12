<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
		<meta http-equiv="content-type" content="text/html; charset=UTF-8">
		
		<title>Holy Britannian Empire</title>
		
		<!-- Latest compiled and minified jQuery -->
		<script src="//code.jquery.com/jquery-2.2.3.min.js"></script>
		
		<!-- Latest compiled and minified CSS -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
		
		<!-- Optional theme -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">
		
		<!-- Latest compiled and minified JavaScript -->
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
		
		@if(isset($buyers))
		{!! HTML::script('js/clipboard.min.js') !!}
		@endif
		@if(isset($roles) || isset($messages) || isset($inline_edit))
		<link href="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.1/bootstrap3-editable/css/bootstrap-editable.css" rel="stylesheet"/>
		<script src="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.1/bootstrap3-editable/js/bootstrap-editable.min.js"></script>
		@endif
		@if(isset($pagination))
		{!! HTML::script('js/jquery.jqpagination.min.js') !!}
		{!! HTML::style('css/jqpagination.css') !!}
		@endif
		@if(isset($graphs))
		<script src="//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.1.3/Chart.min.js"></script>
		@endif
		@if(isset($network))
		<script src="/js/vis.min.js"></script>
		<link href="/css/vis.min.css" rel="stylesheet"/>
		@endif
		@if(isset($chosen))
		{!! HTML::script('js/chosen.jquery.min.js') !!}
		{!! HTML::style('css/chosen.min.css') !!}
		@endif
		{!! HTML::style('css/style.css') !!}
	</head>
	<body>
		<nav class="navbar navbar-default navbar-static-top" role="navigation">
			<div class="container">
				<!-- Brand and toggle get grouped for better mobile display -->
				<div class="navbar-header">
					<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					<a class="navbar-brand" href="#" data-target=".navbar-collapse">Holy Britannian Empire</a>
				</div>
				<!-- Collect the nav links, forms, and other content for toggling -->
				<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
					<ul class="nav navbar-nav">
						@if(AuthHelper::canAccess(array('warchest_form', 'view_web', 'view_stats', 'view_tax_income', 'view_map', 'view_market_avg')))
						<li>
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Britannian Statistics <span class="caret"></span></a>
							<ul class="dropdown-menu">
								@if(AuthHelper::canAccess('view_web'))
								<li>{!! HTML::link('/web', 'Treaty Web') !!}</li>
								@endif
								@if(AuthHelper::canAccess('view_stats'))
								<li>{!! HTML::link('/stats', 'Nation Data') !!}</li>
								@endif
								@if(AuthHelper::canAccess('view_tax_income'))
								<li>{!! HTML::link('/taxes', 'Alliance Tax Income') !!}</li>
								@endif
								@if(AuthHelper::canAccess('view_map'))
								<li>{!! HTML::link('/map', 'War Map') !!}</li>
								@endif
								@if(AuthHelper::canAccess('view_market_avg'))
								<li>{!! HTML::link('/market', 'Market Tracker') !!}</li>
								@endif
							</ul>
						</li>
						@endif
						@if(AuthHelper::canAccess(array('infra_calc', 'land_calc', 'warchest_calc', 'aa_warchest_calc')))
						<li>
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Britannian Calculators <span class="caret"></span></a>
							<ul class="dropdown-menu">
								@if(AuthHelper::canAccess('infra_calc'))
								<li>{!! HTML::link('/calc/infra', 'Infra Calculator') !!}</li>
								@endif
								@if(AuthHelper::canAccess('land_calc'))
								<li>{!! HTML::link('/calc/land', 'Land Calculator') !!}</li>
								@endif
								@if(AuthHelper::canAccess('warchest_calc'))
								<li>{!! HTML::link('/calc/warchest', 'Warchest Calculator') !!}</li>
								@endif
								@if(AuthHelper::canAccess('aa_warchest_calc'))
								<li>{!! HTML::link('/calc/aa/warchest', 'Alliance Warchest Calculator') !!}</li>
								@endif
							</ul>
						</li>
						@endif
						@if(AuthHelper::canAccess(array('assign_targets', 'view_world_military_stats', 'view_alliances', 'raid_prevention')))
						<li>
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Britannian Military <span class="caret"></span></a>
							<ul class="dropdown-menu">
								@if(AuthHelper::canAccess('assign_targets'))
								<li>{!! HTML::link('/defense/target', 'Target Acquisition') !!}</li>
								<li>{!! HTML::link('/defense/target/activate', 'Target Activation') !!}</li>
								@endif
								@if(AuthHelper::canAccess('view_world_military_stats'))
								<li>{!! HTML::link('/defense/readiness', 'World Military Readiness') !!}</li>
								@endif
								@if(AuthHelper::canAccess('view_alliances'))
								<li>{!! HTML::link('/map/alliances', 'View Alliances') !!}</li>
								@endif
								@if(AuthHelper::canAccess('raid_prevention'))
								<li>{!! HTML::link('/raid/prevention', 'Raid Prevention') !!}</li>
								@endif
							</ul>
						</li>
						@endif
						@if(AuthHelper::canAccess(array('manage_map', 'manage_web')))
						<li>
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Britannian Maps <span class="caret"></span></a>
							<ul class="dropdown-menu">
								@if(AuthHelper::canAccess('manage_map'))
								<li>{!! HTML::link('/map/manage', 'Manage War Map') !!}</li>
								@endif
								@if(AuthHelper::canAccess('manage_web'))
								<li>{!! HTML::link('/web/manage', 'Manage Treaty Web') !!}</li>
								@endif
							</ul>
						</li>
						@endif
						@if(AuthHelper::canAccess(array('recruitment_messages', 'send_pm')))
						<li>
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Alliance Messaging <span class="caret"></span></a>
							<ul class="dropdown-menu">
								@if(AuthHelper::canAccess('recruitment_messages'))
								<li>{!! HTML::link('/messages', 'Recruitment Messages') !!}</li>
								@endif
								@if(AuthHelper::canAccess('send_pm'))
								<li>{!! HTML::link('/pm', 'Send Mass PM') !!}</li>
								@endif
							</ul>
						</li>
						@endif
						<li>
							{!! HTML::link('/auth/' . (AuthHelper::isLoggedIn() ? 'logout' : 'login'), AuthHelper::isLoggedIn() ? 'Logout' : 'Login') !!}
						</li>
						@if(!AuthHelper::isLoggedIn())
						<li>
							{!! str_replace('tools.', 'www.', HTML::link('/index.php?action=register', 'Register')) !!}
						</li>
						@endif
					</ul>
				</div>
			</div>
		</nav>
		@if (count($errors) > 0)
		<div class="container">
			<div class="alert alert-danger">
				<strong>Whoops!</strong> We encountered 1 or more errors.<br><br>
				<ul>
					@foreach ($errors->all() as $error)
					<li>{{ $error }}</li>
					@endforeach
				</ul>
			</div>
		</div>
		@endif
		@yield('content')
	</body>
</html>
