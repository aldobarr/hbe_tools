<div id="loginModal" class="modal staticModal" tabindex="-1" role="dialog" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h1 class="text-center">Login</h1>
					</div>
					<div class="modal-body">
						<?php global $context, $scripturl, $user_info;
						$_SESSION['login_url'] = stristr(Request::url(), 'auth/login') ? secure_url('/home') : Request::url();
						?>
						<form action="{!! str_replace('http:', 'https:', $scripturl) !!}?action=login2" method="POST" class="form col-md-12 center-block" accept-charset="{!! $context['character_set'] !!}">
							<div class="form-group">
								<input type="text" name="user" class="form-control input-lg" placeholder="Username" value="{{ $user_info['username'] }}" required>
							</div>
							<div class="form-group">
								<input type="password" name="passwrd" class="form-control input-lg" placeholder="Password" required>
							</div>
							<div class="form-group">
								<input type="hidden" name="cookielength" value="-1">
								<input type="hidden" name="sc" value="{{ $context['session_id'] }}">
								<input type="submit" class="btn btn-primary btn-lg btn-block" value="Login">
								<span class="pull-right"><a href="{{ str_replace('tools.', '', url('/index.php?action=reminder')) }}">Forget your password?</a></span><span><label><input type="checkbox" name="remember" style="vertical-align: top;">Keep me logged in</label></span>
							</div>
						</form>
					</div>
					<div class="modal-footer"></div>
				</div>
			</div>
		</div>
