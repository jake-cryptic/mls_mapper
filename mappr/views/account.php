<script>
	document.title = "You must login to continue.";
</script>
<div class="container">
	<div class="jumbotron">
		<h1 class="display-4">Access is restricted.</h1>
		<p class="lead">Login to continue.</p>
		<hr class="my-4">
		<form action="api/account-handler.php" method="POST">
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
			<button type="submit" class="btn btn-primary">Login</button>
		</form>
	</div>

	<h2>Alternatively, create an account.</h2>
	<form action="api/account-handler.php" method="POST">
		<input type="hidden" name="form_type" value="create" />
		<input type="hidden" name="csrf" value="<?php echo $_SESSION["token"]; ?>" />
		<div class="form-group">
			<label for="email">Email address</label>
			<input type="email" class="form-control" name="email" aria-describedby="emailHelp">
			<small id="emailHelp" class="form-text text-muted">Please note that accounts must be approved by the administrator.</small>
		</div>
		<div class="form-group">
			<label for="password">Password</label>
			<input type="password" class="form-control" name="password">
		</div>
		<button type="submit" class="btn btn-primary">Request Access</button>
	</form>
</div>