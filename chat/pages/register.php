<div class="register">
    <!-- BEGIN register-content -->
    <div class="register-content">
        <form action="index.php?page=register" method="POST" name="register_form">
            <h1 class="text-center">Sign Up</h1>
            <p class="text-muted text-center">Create your account to start chatting.</p>
            <div class="mb-3">
                <label class="form-label">Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control form-control-lg fs-15px" name="name" placeholder="e.g John Smith" value="">
            </div>
            <div class="mb-3">
                <label class="form-label">Email Address <span class="text-danger">*</span></label>
                <input type="email" class="form-control form-control-lg fs-15px" name="email" placeholder="username@address.com" value="">
            </div>
            <div class="mb-3">
                <label class="form-label">Password <span class="text-danger">*</span></label>
                <input type="password" class="form-control form-control-lg fs-15px" name="password" value="">
            </div>
            <div class="mb-3">
                <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                <input type="password" class="form-control form-control-lg fs-15px" name="confirm_password" value="">
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
                Already have an account? <a href="index.php?page=login">Sign In</a>
            </div>
        </form>
    </div>
    <!-- END register-content -->
</div>