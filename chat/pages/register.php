<!-- BEGIN #app -->
<div id="app" class="app app-full-height app-without-header">
	<!-- BEGIN register -->
	<div class="register">
		<!-- BEGIN register-content -->
		<div class="register-content">
			<form id="registerForm" method="POST">
				<h1 class="text-center">Sign Up</h1>
				<p class="text-muted text-center">One Admin ID is all you need to access all the Admin services.</p>
				<div id="form-message" class="alert d-none"></div>
				<div class="mb-3">
					<label class="form-label">Name <span class="text-danger">*</span></label>
					<input type="text" id="username" name="username" class="form-control form-control-lg fs-15px" placeholder="e.g John Smith" value="">
				</div>
				<div class="mb-3">
					<label class="form-label">Email Address <span class="text-danger">*</span></label>
					<input type="email" id="email" name="email" class="form-control form-control-lg fs-15px" placeholder="username@address.com" value="">
				</div>
				<div class="mb-3">
					<label class="form-label">Password <span class="text-danger">*</span></label>
					<input type="password" id="password" name="password" class="form-control form-control-lg fs-15px" value="">
				</div>
				<div class="mb-3">
					<label class="form-label">Confirm Password <span class="text-danger">*</span></label>
					<input type="password" id="confirm_password" name="confirm_password" class="form-control form-control-lg fs-15px" value="">
				</div>
				<div class="mb-3">
					<div class="form-check">
						<input class="form-check-input" type="checkbox" value="" id="customCheck1">
						<label class="form-check-label fw-500" for="customCheck1">I have read and agree to the <a href="#">Terms of Use</a> and <a href="#">Privacy Policy</a>.</label>
					</div>
				</div>
				<div class="mb-3">
					<button type="submit" class="btn btn-theme btn-lg fs-15px fw-500 d-block w-100">Sign Up</button>
				</div>
				<div class="text-muted text-center">
					Already have an Admin ID? <a href="?page=login">Sign In</a>
				</div>
			</form>
		</div>
		<!-- END register-content -->
	</div>
	<!-- END register -->
</div>
<!-- END #app -->

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('registerForm');
    const messageDiv = document.getElementById('form-message');

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(form);

        fetch('api/register.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            messageDiv.classList.remove('d-none', 'alert-success', 'alert-danger');
            if (data.status === 'success') {
                messageDiv.classList.add('alert-success');
                messageDiv.textContent = data.message;
                form.reset();
                setTimeout(() => {
                    window.location.href = '?page=login';
                }, 2000);
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
