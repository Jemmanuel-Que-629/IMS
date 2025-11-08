// Password toggle functionality
const togglePassword = document.getElementById('togglePassword');
const passwordInput = document.getElementById('password');
const toggleIcon = document.getElementById('toggleIcon');

if (togglePassword && passwordInput && toggleIcon) {
    togglePassword.addEventListener('click', function() {
        // Toggle the password input type
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        
        // Toggle the icon
        if (type === 'password') {
            toggleIcon.classList.remove('bi-eye-slash-fill');
            toggleIcon.classList.add('bi-eye-fill');
        } else {
            toggleIcon.classList.remove('bi-eye-fill');
            toggleIcon.classList.add('bi-eye-slash-fill');
        }
    });
}

// Form validation and AJAX submission
const loginForm = document.getElementById('loginForm');
if (loginForm) {
    loginForm.addEventListener('submit', function(e) {
        e.preventDefault(); // Prevent default form submission
        
        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value;
        const rememberMe = document.getElementById('rememberMe').checked;
        
        if (username === '' || password === '') {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                background: '#ffffff',
                iconColor: '#ffc107',
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });
            
            Toast.fire({
                icon: 'warning',
                title: 'Please fill in all fields'
            });
            return;
        }
        
        // Prepare form data
        const formData = new FormData();
        formData.append('username', username);
        formData.append('password', password);
        formData.append('login', '1');
        if (rememberMe) {
            formData.append('remember_me', '1');
        }
        
        // Show loading state
        const submitBtn = loginForm.querySelector('.btn-login');
        const originalBtnText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Signing in...';
        
        // Submit via AJAX
        fetch(loginForm.action, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            console.log('Login Response:', data);
            
            if (data.success) {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000,
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
                    title: data.message
                }).then(() => {
                    window.location.href = data.redirect;
                });
            } else {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
                
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
                    title: data.message
                });
            }
        })
        .catch(error => {
            console.error('Login Error:', error);
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
            
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
                title: 'An error occurred. Please try again.'
            });
        });
    });
}
