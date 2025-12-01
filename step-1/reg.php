<?php
session_start();
require 'config.php';
$errors = [];

if (isset($_POST['submit'])) {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (!$first_name || !$last_name || !$email || !$password || !$confirm_password) {
        $errors[] = "All fields are required.";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    // Check if email already exists
    if (empty($errors)) {
        $stmt = $dms->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = "Email already registered.";
        }
        $stmt->close();
    }

    // Register user
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $role_id = 2; // Example: 1=admin, 2=donor/beneficiary etc.
        $stmt = $dms->prepare("INSERT INTO users (first_name, last_name, email, password, role_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $first_name, $last_name, $email, $hashed_password, $role_id);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Registration successful! Please log in.";
            header("Location: login.php");
            exit;
        } else {
            $errors[] = "Registration failed. Try again.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DonorHub | Register</title>
 <link href="assets/img/img/main-logo.png" rel="icon">
    <!-- Google Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="assets/plugins/fontawesome-free/css/all.min.css">
    <!-- icheck bootstrap -->
    <link rel="stylesheet" href="assets/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <!-- AdminLTE -->
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
    .register-box {
        width: 400px;
        z-index: 1;

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
        color: #cf983e; /* Gold color for toggle icon for emphasis */
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
<body>

<div class="register-box">
    <div class="card shadow-lg border-0">
        <div class="card-header text-center bg-white" style="border-bottom:0;">
            <a href="index.php" class="h1" style="font-weight:700; color:#3c8dbc;">
                <img src="assets/img/img/main-logo.png" alt="Logo" style="width:170px;">
            </a>
        </div>
        <div class="card-body">
            <p class="login-box-msg" style="font-size:17px; font-weight:500;">Create your account</p>

            <?php
            if (!empty($errors)) {
                echo '<div class="alert alert-danger">';
                foreach ($errors as $error) {
                    echo "<div>$error</div>";
                }
                echo '</div>';
            }
            if (!empty($_SESSION['success'])) {
                echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
                unset($_SESSION['success']);
            }
            ?>

            <form action="" method="post">
                <!-- First Name -->
                <div class="input-group mb-3">
                    <span class="input-group-text bg-white"><i class="fas fa-user text-primary"></i></span>
                    <input type="text" class="form-control" placeholder="First Name" name="first_name" required>
                </div>

                <!-- Last Name -->
                <div class="input-group mb-3">
                    <span class="input-group-text bg-white"><i class="fas fa-user text-primary"></i></span>
                    <input type="text" class="form-control" placeholder="Last Name" name="last_name" required>
                </div>

                <!-- Email -->
                <div class="input-group mb-3">
                    <span class="input-group-text bg-white"><i class="fas fa-envelope text-primary"></i></span>
                    <input type="email" class="form-control" placeholder="Email Address" name="email" required>
                </div>

                <!-- Password -->
                <div class="input-group mb-3">
                    <span class="input-group-text bg-white"><i class="fas fa-lock text-primary"></i></span>
                    <input type="password" class="form-control" placeholder="Password" name="password" required>
                    <span class="input-group-text bg-white toggle-password"><i class="fas fa-eye"></i></span>
                </div>

                <!-- Confirm Password -->
                <div class="input-group mb-3">
                    <span class="input-group-text bg-white"><i class="fas fa-lock text-primary"></i></span>
                    <input type="password" class="form-control" placeholder="Confirm Password" name="confirm_password" required>
                    <span class="input-group-text bg-white toggle-password"><i class="fas fa-eye"></i></span>
                </div>

                <!-- Submit Button -->
                <div class="row">
                    <div class="col-12">
                        <button type="submit" name="submit" class="btn btn-primary btn-block" style="font-weight:600; border-radius:6px;">
                            Register
                        </button>
                    </div>
                </div>
            </form>

            <hr>
            <p class="mb-0 text-center">
                Already have an account? <a href="login.php"><b>Sign In</b></a>
            </p>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="assets/plugins/jquery/jquery.min.js"></script>
<script src="assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/dist/js/adminlte.min.js"></script>

<script>
    document.querySelectorAll('.toggle-password').forEach(el => {
        el.addEventListener('click', function () {
            const input = this.closest('.input-group').querySelector('input');
            const icon = this.querySelector('i');
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = "password";
                icon.classList.add('fa-eye');
                icon.classList.remove('fa-eye-slash');
            }
        });
    });
</script>

</body>
</html>
