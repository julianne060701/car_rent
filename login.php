<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Booking System - Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 100%;
            max-width: 420px;
            transform: translateY(0);
            transition: all 0.3s ease;
        }

        .login-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .car-icon {
            color: white;
            font-size: 24px;
        }

        .logo h1 {
            color: #333;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .logo p {
            color: #666;
            font-size: 14px;
        }

        .role-selector {
            display: none;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: white;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            transform: translateY(-1px);
        }

        .form-group input:hover {
            border-color: #667eea;
        }

        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #666;
            user-select: none;
            margin-top: 16px;
        }

        .password-toggle:hover {
            color: #667eea;
        }

        .forgot-password {
            text-align: right;
            margin-bottom: 25px;
        }

        .forgot-password a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s ease;
        }

        .forgot-password a:hover {
            color: #764ba2;
        }

        .login-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .login-btn.loading {
            pointer-events: none;
        }

        .login-btn.loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-left: 2px solid white;
            border-radius: 50%;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: translate(-50%, -50%) rotate(360deg); }
        }

        .features {
            margin-top: 30px;
            padding-top: 25px;
            border-top: 1px solid #e1e5e9;
        }

        .feature {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            color: #666;
            font-size: 14px;
        }

        .feature-icon {
            width: 16px;
            height: 16px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            margin-right: 10px;
            position: relative;
        }

        .feature-icon::after {
            content: '✓';
            position: absolute;
            color: white;
            font-size: 10px;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            display: none;
        }

        .alert.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 30px 20px;
                margin: 10px;
            }
            
            .role-option {
                padding: 10px 15px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <div class="logo-icon">
                <span class="car-icon">🚗</span>
            </div>
            <h1>CarBook Pro</h1>
            <p>Fleet Management System</p>
        </div>

        <div class="alert" id="alert"></div>

        <form id="loginForm">

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required placeholder="Enter your work email">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="Enter your password">
                <span class="password-toggle" onclick="togglePassword()">👁️</span>
            </div>

            <div class="forgot-password">
                <a href="#" onclick="showForgotPassword()">Forgot Password?</a>
            </div>

            <button type="submit" class="login-btn" id="loginBtn">
                Sign In
            </button>
        </form>

        <div class="features">
            <div class="feature">
                <div class="feature-icon"></div>
                <span>Secure Authentication</span>
            </div>
            <div class="feature">
                <div class="feature-icon"></div>
                <span>Real-time Fleet Tracking</span>
            </div>
            <div class="feature">
                <div class="feature-icon"></div>
                <span>24/7 Support Available</span>
            </div>
        </div>
    </div>

    <script>
        // Password toggle functionality
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggle = document.querySelector('.password-toggle');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggle.textContent = '🙈';
            } else {
                passwordInput.type = 'password';
                toggle.textContent = '👁️';
            }
        }

        // Form submission
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const loginBtn = document.getElementById('loginBtn');
            
            // Validate inputs
            if (!email || !password) {
                showAlert('Please fill in all fields', 'error');
                return;
            }
            
            // Simulate loading
            loginBtn.classList.add('loading');
            loginBtn.textContent = '';
            
            // Simulate API call
            setTimeout(() => {
                // Demo authentication logic - automatically detects role based on credentials
                const users = {
                    'staff@carbook.com': { password: 'staff123', role: 'staff', name: 'Staff User' },
                    'admin@carbook.com': { password: 'admin123', role: 'admin', name: 'Admin User' },
                    'manager@carbook.com': { password: 'manager123', role: 'admin', name: 'Fleet Manager' },
                    'supervisor@carbook.com': { password: 'super123', role: 'staff', name: 'Supervisor' }
                };
                
                if (users[email] && users[email].password === password) {
                    const user = users[email];
                    showAlert(`Welcome ${user.name}! Redirecting to ${user.role} dashboard...`, 'success');
                    
                    setTimeout(() => {
                        // In a real app, redirect based on role
                        console.log(`Redirecting ${user.name} to ${user.role} dashboard`);
                        console.log(`User role: ${user.role}`);
                    }, 1500);
                } else {
                    showAlert('Invalid email or password. Please try again.', 'error');
                }
                
                loginBtn.classList.remove('loading');
                loginBtn.textContent = 'Sign In';
            }, 2000);
        });

        // Alert system
        function showAlert(message, type) {
            const alert = document.getElementById('alert');
            alert.textContent = message;
            alert.className = `alert ${type}`;
            alert.style.display = 'block';
            
            setTimeout(() => {
                alert.style.display = 'none';
            }, 5000);
        }

        // Forgot password
        function showForgotPassword() {
            showAlert('Password reset instructions sent to your email!', 'success');
        }
    </script>
    </script>
</body>
</html>