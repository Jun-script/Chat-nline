<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="../assets/css/vendor.min.css" rel="stylesheet" />
    <link href="../assets/css/app.min.css" rel="stylesheet" />
    <link href="../assets/css/custom.css" rel="stylesheet" />
</head>
<body>
	<!-- BEGIN #app -->
	<div id="app" class="app app-full-height app-without-header">
		<!-- BEGIN login -->
		<div class="login">
			<!-- BEGIN login-content -->
			<div class="login-content">
				<form action="index.php" method="POST" name="login_form">
					<h1 class="text-center">Sign In</h1>
					<div class="text-muted text-center mb-4">
						For your protection, please verify your identity.
					</div>
					<div class="mb-3">
						<label class="form-label">Email Address</label>
						<input type="text" class="form-control form-control-lg fs-15px" value="" placeholder="username@address.com">
					</div>
					<div class="mb-3">
						<div class="d-flex">
							<label class="form-label">Password</label>
							<a href="#" class="ms-auto text-muted">Forgot password?</a>
						</div>
						<input type="password" class="form-control form-control-lg fs-15px" value="" placeholder="Enter your password">
					</div>
					<div class="mb-3">
						<div class="form-check">
							<input class="form-check-input" type="checkbox" value="" id="customCheck1">
							<label class="form-check-label fw-500" for="customCheck1">Remember me</label>
						</div>
					</div>
					<button type="submit" class="btn btn-theme btn-lg d-block w-100 fw-500 mb-3">Sign In</button>
					<div class="text-center text-muted">
						Don't have an account yet? <a href="index.php?page=register">Sign up</a>.
					</div>
				</form>
			</div>
			<!-- END login-content -->
		</div>
		<!-- END login -->
	</div>
	<!-- END #app -->
    <script src="../assets/js/vendor.min.js"></script>
    <script src="../assets/js/app.min.js"></script>
</body>
</html>