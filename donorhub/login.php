<?php
session_start();
require 'config.php';
$errors = [];

if (isset($_POST['submit'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Check if both email and password are provided.
    if (empty($email) || empty($password)) {
        $errors[] = "Please enter both email and password.";
    }

    // If there are no errors, proceed with authentication.
    if (empty($errors)) {
        // Prepare the SQL statement to prevent SQL injection.
        // It selects the user's ID, password, and role name.
        $stmt = $dms->prepare("
            SELECT u.id, u.password, r.name as role_name, u.first_name, u.last_name
            FROM users u
            JOIN roles r ON u.role_id = r.id
            WHERE u.email = ?
        ");
        
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();

            // Verify if a user was found and the password matches.
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $email;
                $_SESSION['user_role'] = $user['role_name'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];

                // Use a switch statement to redirect to home.php with the correct page ID.
                switch ($user['role_name']) {
                    case 'admin':
                        header("Location: home.php?page=0");
                        break;
                        case 'campaign_manager':
                        header("Location: home.php?page=11");
                        break;
                        case 'beneficiary':
                        header("Location: index.php");
                        break;
                    case 'donor':
                        header("Location: index.php");
                        break;
                    case 'volunteer':
                        header("Location: home.php?page=35");
                        break;
                    default:
                        // Default redirection if the role is not one of the allowed roles.
                        $errors[] = "Your account role is not permitted to log in.";
                        break;
                }
                if (empty($errors)) {
                    exit;
                }
            } else {
                // Handle invalid credentials.
                $errors[] = "Invalid email or password.";
            }
        } else {
            $errors[] = "Database query failed.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DonorHub | Log in </title>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="assets/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="assets/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <link rel="stylesheet" href="assets/dist/css/adminlte.min.css">

    <style>
       
  body {
    /* Background image */
    background-image: url('/assets/img/bg.png'); /* Replace with your image path */
    background-size: cover;      /* Make the image cover the entire screen */
    background-position: center; /* Center the image */
    background-repeat: no-repeat;/* Prevent tiling */
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
}

/* Optional: add a dark overlay for readability */
body::before {
    content: '';
    position: absolute;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(4, 90, 93, 0.6); /* semi-transparent overlay */
    z-index: 0;
}
.login-box {
    position: relative;
    z-index: 1; /* make sure form is above overlay */
}

    .card {
        border-radius: 15px;
    }
    .input-group-text {
        background: #fff;
        border: 1px solid #045a5d;
    }
    .input-group-text i {
        color: #cf983e!important; /* Logo teal color for icons */
        font-size: 1.1rem;
    }
    .toggle-password i {
        color: #cf983e; /* Gold color for toggle icon */
    }
    .btn-primary {
        background-color: #045a5d;
        border-color: #cf983e;
        font-weight: 600;
        border-radius: 6px;
    }
    .btn-primary:hover {
        background-color: #cf983e;
        border-color: #045a5d;
        color: #fff;
    }
</style>

</head>
<body class="hold-transition login-page" style="background:#f4f6f9;">

<div class="login-box">
    <div class="card shadow-lg border-0" style="border-radius:15px;">
        <div class="card-header text-center bg-white" style="border-bottom:0;">
            <a href="index.php" class="h1" style="font-weight:700; color:#3c8dbc;">
                <img src="assets/img/img/main-logo.png" alt="Logo" style="width:170px;">
                <!-- <span>Donor<b>Hub</b></span> -->
            </a>
        </div>

        <div class="card-body">
            <p class="login-box-msg" style="font-size:17px; font-weight:500;">Welcome Back, Please Sign In To Donate</p>

            <?php
            if (!empty($errors)) {
                echo '<div class="alert alert-danger">';
                foreach ($errors as $error) {
                    echo "<div>$error</div>";
                }
                echo '</div>';
            }
            ?>

            <form action="" method="post">
                
                <!-- Email -->
                <div class="input-group mb-3">
                    <span class="input-group-text bg-white">
                        <i class="fas fa-envelope text-primary"></i>
                    </span>
                    <input type="email" class="form-control" placeholder="Email Address" name="email" required>
                </div>

                <!-- Password -->
                <div class="input-group mb-3">
                    <span class="input-group-text bg-white">
                        <i class="fas fa-lock text-primary"></i>
                    </span>
                    <input type="password" class="form-control" placeholder="Password" name="password" required>
                    <span class="input-group-text bg-white toggle-password" style="cursor:pointer;">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>

                <!-- Remember + Button -->
                <div class="row">
                    <div class="col-7">
                        <div class="icheck-primary">
                            <input type="checkbox" id="remember">
                            <label for="remember">Remember Me</label>
                        </div>
                    </div>

                    <div class="col-5">
                        <button type="submit" name="submit" class="btn btn-primary btn-block" 
                            style="font-weight:600; border-radius:6px;">
                            Sign In
                        </button>
                    </div>
                </div>
            </form>

            <hr>

            <div class="d-flex justify-content-between mt-3">
    <a href="forgot-password.php" style="color:#045a5d; font-weight:500;">Forgot password?</a>
     <a href="reg.php" style="color:#cf983e; font-weight:500;">Create an account</a>
</div>
        </div>
    </div>
</div>

<!-- Password Toggle Script -->
<script>
document.querySelector('.toggle-password').addEventListener('click', function () {
    const passwordField = document.querySelector('input[name="password"]');
    const icon = this.querySelector('i');
    
    if (passwordField.type === "password") {
        passwordField.type = "text";
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        passwordField.type = "password";
        icon.classList.add('fa-eye');
        icon.classList.remove('fa-eye-slash');
    }
});
</script>

<script src="assets/plugins/jquery/jquery.min.js"></script>
<script src="assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/dist/js/adminlte.min.js"></script>

</body>
</html>
