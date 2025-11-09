<!-- BEGIN #app -->
<div id="app" class="app app-full-height app-without-header">
	<!-- BEGIN login -->
	<div class="login">
		<!-- BEGIN login-content -->
		<div class="login-content">
			<form id="loginForm" method="POST">
				<h1 class="text-center">Sign In</h1>
				<div class="text-muted text-center mb-4">
					For your protection, please verify your identity.
				</div>
				<div id="form-message" class="alert d-none"></div>
				<div class="mb-3">
					<label class="form-label">Email Address</label>
					<input type="email" id="email" name="email" class="form-control form-control-lg fs-15px" value="" placeholder="username@address.com">
				</div>
				<div class="mb-3">
					<div class="d-flex">
						<label class="form-label">Password</label>
						<a href="#" class="ms-auto text-muted">Forgot password?</a>
					</div>
					<input type="password" id="password" name="password" class="form-control form-control-lg fs-15px" value="" placeholder="Enter your password">
				</div>
				<div class="mb-3">
					<div class="form-check">
						<input class="form-check-input" type="checkbox" value="" id="customCheck1">
						<label class="form-check-label fw-500" for="customCheck1">Remember me</label>
					</div>
				</div>
				<button type="submit" class="btn btn-theme btn-lg d-block w-100 fw-500 mb-3">Sign In</button>
				<div class="text-center text-muted">
					Don't have an account yet? <a href="?page=register">Sign up</a>.
				</div>
			</form>
		</div>
		<!-- END login-content -->
	</div>
	<!-- END login -->
</div>
<!-- END #app -->

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('loginForm');
    const messageDiv = document.getElementById('form-message');

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(form);

        fetch('api/login.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            messageDiv.classList.remove('d-none', 'alert-success', 'alert-danger');
            if (data.status === 'success') {
                messageDiv.classList.add('alert-success');
                messageDiv.textContent = data.message;
                setTimeout(() => {
                    window.location.href = '?page=chat';
                }, 1500);
            } else {
                messageDiv.classList.add('alert-danger');
                messageDiv.textContent = data.message;
            }
        })
        .catch(error => {
            messageDiv.classList.remove('d-none', 'alert-success');
            messageDiv.classList.add('alert-danger');
            messageDiv.textContent = 'An error occurred while processing your request.';
            console.error('Error:', error);
        });
    });
});
</script>
