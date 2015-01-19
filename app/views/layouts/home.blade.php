<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="">
	<meta name="author" content="">
	<link rel="icon" href="../../favicon.ico">
	<title>Students</title>
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
	<link href="{{ URL::asset('res/css/tagbar.css') }}" rel="stylesheet">
	<link href="{{ URL::asset('res/css/bootstrap.css') }}" rel="stylesheet">
	<link href="{{ URL::asset('res/css/bootstrap-theme.css') }}" rel="stylesheet">
	<script type="text/javascript" src="{{ URL::asset('res/js/jquery.js') }}"></script>
	<script src="{{ URL::asset('res/js/bootstrap.min.js') }}"></script>
	<script src="{{ URL::asset('res/js/backtotop.js') }}"></script>
	<script src="{{ URL::asset('res/js/users/addphone.js') }}"></script>
	<script src="{{ URL::asset('res/js/jscroll/jquery.jscroll.min.js') }}"></script>
</head>
<body>
	<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
		<div class="container">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="{{ URL::route('home') }}">Project name</a>
			</div>
			<div id="navbar" class="collapse navbar-collapse">
				
				<ul class="nav navbar-nav home_navbar">
				@if(Auth::check())
					<li class="user_prof_link"><a href="{{ URL::route('user-profile', Auth::user()->username) }}">{{ Auth::user()->username }}</a></li>
					<li class=""><a href="{{ URL::route('account-sign-out') }}">Log Out</a></li>
				@endif
				</ul>
				@if(!Auth::check())
				<div class="login_inline_form pull-right">
				
					{{ Form::open(array('route' => 'account-sign-in-post', 'id' => 'inline-loginform', 'class' => 'form-inline', 'role' => 'form')) }}
					  <div class="form-group">
					  	<?php $emailError =  null ; $error_border_class = null;?>
						@if($errors->has('email_login'))
							<?php $emailError =  $errors->first('email_login') ; $error_border_class = 'error_border';?>
						@endif
						<div class="home-error_message_small">
							{{ $emailError }}
						</div>
					    <label class="sr-only" for="exampleInputEmail2">Email address</label>
					    {{ Form::email('email_login','', array('id' => 'login-email', 'class' => 'form-control input-sm '.$error_border_class, 'placeholder' => 'Email')) }}
					    
					  </div>
					  <div class="form-group">
					  	<?php $passwordError =  null ; $error_border_class = null;?>
						@if($errors->has('password_login'))
							<?php $passwordError =  $errors->first('password_login') ; $error_border_class = 'error_border';?>
						@endif
						<div class="home-error_message_small">
							{{ $passwordError }}
						</div>
					    <label class="sr-only" for="exampleInputPassword2">Password</label>
					    {{ Form::password('password_login', array('id' => 'login-password exampleInputPassword2', 'class' => 'form-control input-sm '.$error_border_class, 'placeholder' => 'Password')) }}
						<div class="forgot_password_small">
							<a href="{{ URL::route('recover-password') }}">Forgot password?</a>
						</div>
					  </div>
					  <div class="checkbox">
					    <div class="checkbox">
	                        <label>
	                          {{ Form::checkbox('remember', 0, 0, ['id' => 'login-remember', ]) }} <p class="rememberMe_label">Remember me</p>
	                        </label>
                     	</div>
					  </div>
					  <button type="submit" class="btn btn-sm btn-default">Sign in</button>
					{{Form::close()}} 
					<a href="{{ URL::route('account-create') }}" class="btn btn-sm btn-success"><span class="glyphicon glyphicon-certificate"></span> Register</a>
					
				</div>
				@endif
				
			</div><!--/.nav-collapse -->
		</div>
	</nav>

	<div class="container">
		@yield('ragac')
	</div><!-- /.container -->
</body>
</html>