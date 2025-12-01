<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>DonorHub | Forgot Password</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="assets/plugins/fontawesome-free/css/all.min.css">
  <!-- icheck bootstrap -->
  <link rel="stylesheet" href="assets/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- AdminLTE -->
  <link rel="stylesheet" href="assets/dist/css/adminlte.min.css">

  <style>
    body {
      background: linear-gradient(135deg, #045a5d, #cf983e);
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
    }
    .login-box {
      width: 400px;
    }
    .card {
      border-radius: 15px;
      box-shadow: 0 0 20px rgba(0,0,0,0.1);
    }
    .login-box-msg {
      font-size: 1rem;
      font-weight: 500;
      color: #555;
      margin-bottom: 20px;
    }
    .input-group-text {
      background: #fff;
      border: 1px solid #045a5d;
    }
    .input-group-text i {
      color: #cf983e;
    }
    .form-control {
      border-radius: 10px;
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
    .back-link {
      display: block;
      text-align: center;
      margin-top: 20px;
      font-weight: 500;
      color: #045a5d;
    }
    .back-link:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
<div class="login-box">
  <div class="card shadow-lg border-0">
    <div class="card-header text-center bg-white" style="border-bottom:0;">
      <a href="index.php" class="h1" style="font-weight:700; color:#045a5d;">
        <img src="assets/img/img/main-logo.png" alt="Logo" style="width:170px;">
      </a>
    </div>
    <div class="card-body">
      <p class="login-box-msg">Forgot your password? Enter your email to reset it.</p>

      <form action="recover-password.php" method="post">
        <div class="input-group mb-3">
          <input type="email" class="form-control" placeholder="Enter your email" name="email" required>
          <span class="input-group-text"><i class="fas fa-envelope"></i></span>
        </div>
        <div class="row">
          <div class="col-12">
            <button type="submit" class="btn btn-primary btn-block">Request New Password</button>
          </div>
        </div>
      </form>

      <a href="login.php" class="back-link">← Back to Login</a>
    </div>
  </div>
</div>

<script src="assets/plugins/jquery/jquery.min.js"></script>
<script src="assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/dist/js/adminlte.min.js"></script>
</body>
</html>
