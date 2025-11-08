<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Inventory Management System</title>
    
    <!-- Bootstrap 5.3.2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Google Fonts - Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <!-- Custom Login CSS -->
    <link rel="stylesheet" href="../public/css/login_form.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <i class="bi bi-box-seam"></i>
                <h1>Welcome Back!</h1>
                <p>Inventory Management System</p>
            </div>
            
            <div class="login-body">
                <form action="<?php $config = require __DIR__ . '/../config/app.php'; echo $config['app_url']; ?>/backend/auth/login.php" method="POST" id="loginForm">
                    <div class="mb-3">
                        <label for="username" class="form-label">
                            <i class="bi bi-person-fill"></i> Username or Email
                        </label>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="username" 
                            name="username" 
                            placeholder="Enter your username or email"
                            required 
                            autofocus
                        >
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">
                            <i class="bi bi-lock-fill"></i> Password
                        </label>
                        <div class="input-group">
                            <input 
                                type="password" 
                                class="form-control" 
                                id="password" 
                                name="password" 
                                placeholder="Enter your password"
                                required
                            >
                            <button 
                                type="button" 
                                class="password-toggle" 
                                id="togglePassword"
                                aria-label="Toggle password visibility"
                            >
                                <i class="bi bi-eye-fill" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="remember-forgot-row">
                        <div class="form-check">
                            <input 
                                class="form-check-input" 
                                type="checkbox" 
                                id="rememberMe" 
                                name="remember_me"
                            >
                            <label class="form-check-label" for="rememberMe">
                                Remember me
                            </label>
                        </div>
                        
                        <div class="forgot-password">
                            <a href="#"><i class="bi bi-question-circle"></i> Forgot Password?</a>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-login" name="login">
                        <i class="bi bi-box-arrow-in-right"></i> Sign In
                    </button>
                </form>
            </div>
            
            <div class="login-footer">
                <p>&copy; 2025 Inventory Management System. All rights reserved.</p>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap 5.3.2 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Custom Login JS -->
    <script src="../public/js/login_form.js"></script>
    
    <?php if (isset($_SESSION['error_message'])): ?>
    <script>
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true,
            background: '#ffffff',
            iconColor: '#dc3545',
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });
        
        Toast.fire({
            icon: 'error',
            title: '<?php echo addslashes($_SESSION['error_message']); ?>'
        });
    </script>
    <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['success_message'])): ?>
    <script>
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true,
            background: '#ffffff',
            iconColor: '#28a745',
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });
        
        Toast.fire({
            icon: 'success',
            title: '<?php echo addslashes($_SESSION['success_message']); ?>'
        });
    </script>
    <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
</body>
</html>
