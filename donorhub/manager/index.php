<?php
session_start();
require '../config.php';
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
                // Check if the user has the 'campaign_manager' role.
                if ($user['role_name'] === 'campaign_manager') {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_email'] = $email;
                    $_SESSION['user_role'] = $user['role_name'];
                    $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                    
                    header("Location: ../home.php?page=11");
                    exit;
                } else {
                    // Access is denied for all other roles.
                    $errors[] = "Access denied. Only Campaign Managers are permitted to log in here.";
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
    <title>DonorHub | Campaign Manager Log in</title>

    <!-- AdminLTE 3 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.1.0/dist/css/adminlte.min.css">
    <!-- Font Awesome icons (already included in AdminLTE) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body class="hold-transition login-page">
    <div class="login-box">
        <div class="card card-outline card-primary">
            <div class="card-header text-center">
                <a href="index.php" class="h1"><b>Donor</b>Hub</a>
            </div>
            <div class="card-body">
                <p class="login-box-msg fw-bold"> <b style="font-weight: bolder;font-size:16pt;color:#0c6b6c;">Campaign Manager Panel</b></p>

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
                    <div class="input-group mb-3">
                        <input type="email" class="form-control" placeholder="Email" name="email" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-envelope"></span>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="password" class="form-control" placeholder="Password" name="password" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-8">
                            <div class="icheck-primary">
                                <input type="checkbox" id="remember">
                                <label for="remember">
                                    Remember Me
                                </label>
                            </div>
                        </div>
                        <div class="col-4">
                            <button type="submit" name="submit" class="btn btn-primary btn-block">Sign In</button>
                        </div>
                    </div>
                </form>

                <p class="mb-1" >
                    Forgot Password <a href="../forgot-password.php">Click Here</a>
                </p>
                <p class="mb-0">
                    <a href="../index.php" class="text-center">Register a new membership</a>
                </p>
            </div>
        </div>
    </div>

    <!-- AdminLTE 3 JS -->
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.1.0/dist/js/adminlte.min.js"></script>
    <!-- jQuery and Bootstrap JS (AdminLTE requires them) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
