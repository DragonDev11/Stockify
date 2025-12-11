<?php
session_start();
session_destroy();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <title>Login Page</title>
</head>

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
        <div class="form-container sign-in">
            <form method="POST" action="../../Back-end/login.php">
                <h1>Sign In</h1>
                <span>use your username and password</span>
                <input id="username" type="text" placeholder="Username" name="username">
                <input id="password" type="password" placeholder="Password" name="password">
                <a href="#">Forget Your Password? contact your administrator</a>
                <button id="submit-btn" type="submit">Sign In</button>
            </form>
        </div>
    </div>
    <br>
    <div id="error-message" style="color: red"></div>
<!-- The error handling script could not work seperately from the html script, so I put them here together -->
    <script defer>
        const urlParams = new URLSearchParams(window.location.search);
        const error = urlParams.get('error');

        if (error) {
            let message = error.replaceAll("_", " ");
            document.getElementById('error-message').textContent = message;
        }
    </script>
</body>
</html>
