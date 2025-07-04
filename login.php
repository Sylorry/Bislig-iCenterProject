<?php
session_start(); // Start the session

// Check if the user is already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: inventory.php"); // Redirect to the main page if already logged in
    exit();
}

// Initialize login attempts if not set
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}

// Check if the user is locked out
if (isset($_SESSION['lockout_time']) && time() < $_SESSION['lockout_time']) {
    $error = "Too many login attempts. Please try again later.";
} else {
    // Handle login form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Replace with your actual admin credentials (use password_hash() in a real-world scenario)
        $admin_username = "admin"; 
        $admin_password = password_hash("inventory", PASSWORD_BCRYPT); // Hash the password

        // Check credentials (in a real-world scenario, retrieve hashed password from a database)
        if ($username === $admin_username && password_verify($password, $admin_password)) {
            $_SESSION['admin_logged_in'] = true; // Set session variable
            $_SESSION['login_attempts'] = 0; // Reset attempts on successful login
            session_regenerate_id(true); // Prevent session fixation

            header("Location: admin.php"); // Redirect to the main page
            exit();
        } else {
            $_SESSION['login_attempts']++; // Increment login attempts
            if ($_SESSION['login_attempts'] >= 3) { // Example: Lockout after 3 attempts
                $_SESSION['lockout_time'] = time() + 300; // Lockout for 5 minutes
                $error = "Too many failed attempts. Please try again later.";
            } else {
                $error = "Invalid username or password.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"> <!-- For password toggle -->
    <style>
        p {
            color: white; /* golden yellow */
        }
        body {
            font-family: 'Poppins', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(-45deg, rgb(0, 0, 0), rgb(0, 0, 0), #16213e, #0f3460);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
        }

        .main-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 90%;
            max-width: 1200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .logo-container {
            flex: 1;
            text-align: center;
            padding: 40px;
            color: white;
        }

        .logo-container img {
            max-width: 100%;
            height: auto;
            max-height: 200px;
            margin-bottom: 20px;
            border: 2px solid white;
            border-radius: 30px;
        }

        .logo-container h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.7);
        }

        .logo-container p {
            font-size: 1.2rem;
            text-shadow: 0 1px 5px rgba(0, 0, 0, 0.5);
        }

        .login-container {
            flex: 1;
            background: transparent;
            padding: 40px;
            border-radius: 0 20px 20px 0;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            text-align: center;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
            margin-right: 20px;
        }

        .login-container h2 {
            font-size: 2rem;
            color: white;
            margin-bottom: 20px;
        }

        .input-container {
            position: relative;
            margin: 20px auto;
            width: 90%;
            max-width: 350px;
        }

        .input-container input {
            width: calc(100% - 60px);
            padding: 15px 40px 15px 45px;
            margin: 0 auto;
            border: 1px solid #ccc;
            border-radius: 20px;
            font-size: 1rem;
            background-color: #333333;
            color: #ffffff;
            transition: all 0.3s ease;
        }

        .input-container input:focus {
            outline: none;
            border-color: #0ad82a;
            background-color: #444444;
        }

        .input-container label {
            position: absolute;
            top: 50%;
            left: 45px;
            transform: translateY(-50%);
            font-size: 1rem;
            color: #aaaaaa;
            pointer-events: none;
            transition: all 0.3s ease;
        }

        .input-container input:focus + .input-label,
        .input-container input:not(:placeholder-shown) + .input-label {
            top: -10px;
            left: 45px;
            font-size: 0.85rem;
            color: #0ad82a;
            background-color: #121212;
            padding: 0 5px;
        }

        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.2em;
            color: #aaaaaa;
            pointer-events: none;
            transition: color 0.3s ease;
        }

        .input-container input:focus ~ .input-icon {
            color:rgb(148, 146, 146);
        }

        button {
            padding: 15px;
            background: linear-gradient(135deg,rgb(24, 162, 180), #07c926);
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-size: 1.2em;
            margin-top: 20px;
            color: white;
            font-weight: 600;
            transition: all 0.3s;
        }

        button:hover {
            background: white;
            box-shadow: 0 5px 15px rgba(10, 216, 42, 0.4);
        }

        button:active {
            transform: scale(0.98);
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .profile-section {
            text-align: center; /* Center the title */
            margin-bottom: 20px; /* Space below the title */
            animation: slideIn 1s; /* Slide in animation */
        }

        @keyframes slideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .profile-section h1 {
            font-size: 5em; /* Increase font size */
            margin-bottom: 20px; /* Reduced space below the title */
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.7); /* Add text shadow for visibility */
        }

        .profile-section p {
            font-size: 1.5em; /* Increase font size for paragraph */
            text-shadow: 0 1px 5px rgba(0, 0, 0 , 0.5); /* Add text shadow for visibility */
        }

        .login-container {
            background: transparent;
            padding: 40px;
            border-radius: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            text-align: center;
            width: 90%;
            max-width: 400px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transform: translateY(0);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .login-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.4);
        }

        .login-container::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                to bottom right,
                rgba(255, 255, 255, 0.1),
                rgba(255, 255, 255, 0)
            );
            transform: rotate(30deg);
            z-index: -1;
        }

        .login-container h2 {
            font-size: 2em; /* Increase font size for the login title */
            color: #ffffff; /* Ensure color is white */
            margin-top: 0; /* Remove default margin */
        }

        .input-container {
            position: relative;
            margin: 20px auto; /* Add vertical spacing and center horizontally */
            width: 90%; /* Ensure the container doesn't stretch to the edges */
            max-width: 350px; /* Limit the maximum width for better balance */
        }

        .input-container input {
            width: calc(100% - 60px); /* Reduce width to leave space for margins */
            padding: 15px 40px 15px 45px; /* Add padding for the icon */
            margin: 0 auto; /* Center the input field horizontally */
            border: 1px solid #ccc;
            border-radius: 20px;
            font-size: 1rem;
            background-color: #333333;
            color: #ffffff;
            transition: all 0.3s ease;
        }

        .input-container input:focus {
            outline: none;
            border-color:rgb(255, 255, 255);
            background-color: #444444;
        }

        .input-container label {
            position: absolute;
            top: 50%;
            left: 45px; /* Adjust for the icon */
            transform: translateY(-50%);
            font-size: 1rem;
            color: #aaaaaa;
            pointer-events: none;
            transition: all 0.3s ease;
        }

        .input-container input:focus + .input-label,
        .input-container input:not(:placeholder-shown) + .input-label {
            top: -10px;
            left: 45px; /* Adjust for the icon */
            font-size: 0.85rem;
            color:rgb(255, 255, 255);
            background-color: #121212;
            padding: 0 5px;
        }

        .input-icon {
            position: absolute;
            left: 15px; /* Position the icon inside the input field */
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.2em;
            color: #aaaaaa;
            pointer-events: none;
            transition: color 0.3s ease;
        }

        .input-container input:focus ~ .input-icon {
            color:rgb(255, 255, 255); /* Change icon color when input is focused */
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #aaaaaa;
            transition: all 0.3s;
        }

        .toggle-password:hover {
            color: #0ad82a;
        }

        button {
            padding: 15px;
            background: black;
            border: none;
            border-radius: 15px;
            cursor: pointer;
            width: 100%;
            font-size: 1.2em;
            margin-top: 20px;
            color: white;
            font-weight: 600;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        button:hover {
            background: gray;
            box-shadow: 0 5px 15px rgb(0, 0, 0);
        }

        button:active {
            transform: scale(0.98);
        }

        button::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                to right,
                transparent,
                rgba(255, 255, 255, 0.2),
                transparent
            );
            transform: translateX(-100%);
            transition: transform 0.6s ease;
        }

        button:hover::after {
            transform: translateX(100%);
        }

        .loading {
            position: relative;
            color: transparent;
        }

        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .remember-me {
            display: flex;
            align-items: center; /* Center the checkbox and label vertically */
            margin-top: 10px; /* Space above the checkbox */
            color: #ffffff; /* White text color */
        }

        input[type="checkbox"] {
            width: 20px; /* Increase checkbox size */
            height: 20px; /* Increase checkbox size */
            margin-right: 10px; /* Space between checkbox and label */
        }

        .error-message {
            background-color: rgba(255, 76, 76, 0.2);
            color: #ff4c4c;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            border-left: 4px solid #ff4c4c;
            animation: shake 0.5s;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-5px); }
            40%, 80% { transform: translateX(5px); }
        }

        .error-message p {
            margin: 0;
        }

        .success-message {
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }

        .password-strength {
            height: 4px;
            background: #333;
            border-radius: 2px;
            margin-top: 5px;
            overflow: hidden;
        }

        .strength-meter {
            height: 100%;
            width: 0;
            background: #ff4c4c;
            transition: all 0.3s;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 30px 20px;
                width: 90%;
            }
            
            .profile-section h1 {
                font-size: 2.5em;
            }
            
            .profile-section p {
                font-size: 1em;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Left Side: Logo -->
        <div class="logo-container">
            <img src="images/iCenter.png" alt="Bislig iCenter Logo">
            <h1>Bislig iCenter</h1>
            <p>"No. 1 Supplier of iPhones in Mindanao"</p>
        </div>

        <!-- Right Side: Login Form -->
        <div class="login-container">
            <h2>Admin Login</h2>
            <?php 
            if (isset($error)) {
                echo "<div class='error-message'><p>$error</p></div>";
            }
            ?>
            <form method="post" action="">
                <div class="input-container">
                    <input type="text" name="username" id="username" placeholder=" " required>
                    <label for="username" class="input-label">Username</label>
                    <i class="fas fa-user input-icon"></i>
                </div>
                <div class="input-container">
                    <input type="password" name="password" id="password" placeholder=" " required>
                    <label for="password" class="input-label">Password</label>
                    <i class="fas fa-lock input-icon"></i>
                    <i class="fas fa-eye toggle-password" onclick="togglePasswordVisibility()"></i>
                </div>
                <div class="remember-me">
                    <input type="checkbox" name="remember_me" id="remember_me" <?php echo isset($_COOKIE['username']) ? 'checked' : ''; ?>>
                    <label for="remember_me">Remember Me</label>
                </div>
                <button type="submit">LOG IN</button>
            </form>
        </div>
    </div>

    <script>
        // Function to toggle password visibility
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.querySelector('.toggle-password');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Password strength indicator
        document.getElementById('password').addEventListener('input', function(e) {
            const password = e.target.value;
            const strengthMeter = document.querySelector('.strength-meter');
            let strength = 0;
            
            if (password.length > 0) strength += 1;
            if (password.length >= 8) strength += 1;
            if (/[A-Z]/.test(password)) strength += 1;
            if (/[0-9]/.test(password)) strength += 1;
            if (/[^A-Za-z0-9]/.test(password)) strength += 1;
            
            // Update strength meter
            const width = strength * 20;
            strengthMeter.style.width = width + '%';
            
            // Update color
            if (strength <= 2) {
                strengthMeter.style.backgroundColor = '#ff4c4c';
            } else if (strength <= 4) {
                strengthMeter.style.backgroundColor = '#ffcc00';
            } else {
                strengthMeter.style.backgroundColor = '#0ad82a';
            }
        });

        // Form submission with loading state
        document.querySelector('form').addEventListener('submit', function(e) {
            const button = this.querySelector('button[type="submit"]');
            button.classList.add('loading');
            button.disabled = true;
        });

        // Input validation
        document.getElementById('username').addEventListener('blur', function() {
            if (!this.value.trim()) {
                this.style.borderColor = '#ff4c4c';
            } else {
                this.style.borderColor = '#0ad82a';
            }
        });

        document.getElementById('password').addEventListener('blur', function() {
            if (!this.value.trim()) {
                this.style.borderColor = '#ff4c4c';
            } else {
                this.style.borderColor = '#0ad82a';
            }
        });
    </script>
</body>
</html>
