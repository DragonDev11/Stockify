<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create New Account</title>
  <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body>
  <body>
    <div class="container" id="container">
        <div class="form-container sign-up">
            <form method="POST" action="../../Back-end/signup.php">
                <h1>Create Account</h1>
                <span>or use your email for registeration</span>
                <input type="text" placeholder="First Name" name="firstname">
                <input type="text" placeholder="Last Name" name="lastname">
                <input type="text" placeholder="Username" name="username">
                <input type="email" placeholder="Email" name="email">
                <input type="text" placeholder="Phone Number" name="phone">
                <input type="password" placeholder="Password" name="password">
                <input type="password" placeholder="Confirm Password" name="confirm-password">
                <button type="submit">Sign Up</button>
            </form>
        </div>
        <div class="toggle-container">
            <div class="toggle">
                <div class="toggle-panel toggle-right">
                    <h1>Welcome</h1>
                    <p>Register with your personal informations to make an account</p>
                    <button id="register"><a href="../login/index.php">Already have an account? Login!</a></button>
                </div>
            </div>
        </div>
    </div>
    <br>
    <div id="error-message" style="color: red"></div>
    <!-- The error handling script could not work seperately from the html script, so I put them here together -->
    <script defer>
        const urlParams = new URLSearchParams(window.location.search);
        const error = urlParams.get('error');

        if (error) {
            let message = '';
            switch (error) {
                case 'username_not_found':
                    message = 'Username not found. Please try again.';
                    break;
                case 'wrong_password':
                    message = 'Incorrect password. Please try again.';
                    break;
                case 'password_not_match':
                    message = 'Passwords do not match.';
                case 'not_logged_in':
                    message = "You're not logged in, please log in.";
                case 'super_admin_already_exists':
                    message = 'Super admin already exists';
                    break;
                default:
                    message = 'An unknown error occurred.';
            }
            document.getElementById('error-message').textContent = message;
        }
    </script>
  </body>
</html>
