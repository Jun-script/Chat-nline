<!-- BEGIN login -->
		<div class="login">
		<div class="container-fluid h-100">
    <div class="row h-100">
        <!-- Login Form -->
        <div class="col-md-12 p-5">
            <div class="d-flex align-items-center justify-content-center h-100">
                <div class="card w-100" style="max-width: 400px;">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Sign In</h4>
                        <form name="login_form">
                            <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        
                        <div class="mb-3 d-flex justify-content-between">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="remember">
                                <label class="form-check-label" for="remember">Remember me</label>
                            </div>
                            <a href="#" class="text-decoration-none">Forgot Password?</a>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 mb-3">Sign In</button>
                        
                        <p class="text-center mb-0">
                            Don't have an account? 
                            <a href="index.php?page=register" class="text-decoration-none">Sign Up</a>
                        </p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>