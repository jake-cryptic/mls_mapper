<div class="container">
	<div class="jumbotron">
		<h1 class="display-4">Access is restricted.</h1>
		<div class="alert alert-info" id="server_resp" role="alert">Login to continue.</div>

		<ul class="nav nav-pills" id="tabs_list" role="tablist">
			<li class="nav-item" role="presentation">
				<a class="nav-link active" id="login_tab" data-toggle="tab" href="#login_pane" role="tab" aria-controls="login_pane" aria-selected="true">Login</a>
			</li>
			<li class="nav-item" role="presentation">
				<a class="nav-link" id="create_tab" data-toggle="tab" href="#create_pane" role="tab" aria-controls="create_pane" aria-selected="false">Create</a>
			</li>
		</ul>

		<div class="tab-content" id="content">
			<div class="tab-pane fade show active" id="login_pane" role="tabpanel" aria-labelledby="login_tab">
				<form id="login_form" action="api/account-handler.php" method="POST">
					<input type="hidden" name="form_type" value="login" />
					<input type="hidden" name="csrf" value="<?php echo $_SESSION["token"]; ?>" />
					<div class="form-group">
						<label for="email">Email address</label>
						<input type="email" class="form-control" name="email" aria-describedby="emailHelp">
						<small id="emailHelp" class="form-text text-muted">Please enter your registered email.</small>
					</div>
					<div class="form-group">
						<label for="password">Password</label>
						<input type="password" class="form-control" name="password">
					</div>
					<button type="submit" class="btn btn-info">Login</button>
				</form>
			</div>
			<div class="tab-pane fade" id="create_pane" role="tabpanel" aria-labelledby="create_tab">
				<form id="create_form" action="api/account-handler.php" method="POST">
					<input type="hidden" name="form_type" value="create" />
					<input type="hidden" name="csrf" value="<?php echo $_SESSION["token"]; ?>" />
					<div class="form-group">
						<label for="name">First Name</label>
						<input type="text" class="form-control" name="name" aria-describedby="name_help" />
						<small id="name_help" class="form-text text-muted">X Æ A-12 will not pass my validation checks so don't try.</small>
					</div>
					<div class="form-group">
						<label for="email">Email address</label>
						<input type="email" class="form-control" name="email" aria-describedby="email_help" />
						<small id="email_help" class="form-text text-muted">Please note that accounts must be approved by the administrator.</small>
					</div>
					<div class="form-group">
						<label for="password">Password</label>
						<input type="password" class="form-control" name="password" aria-describedby="password_help" />
						<small id="password_help" class="form-text text-muted">Passwords must be 7 characters or longer.</small>
					</div>
					<button type="submit" class="btn btn-info">Request Access</button>
				</form>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript" src="assets/js/login.js"></script>