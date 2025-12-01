<?php
// Ensure ob_start() is the very first thing in the file
ob_start();
session_start();

// Include the config file
include("config.php");

$user = null;
if (isset($_SESSION['user_id'])) {
  $user_id = $_SESSION['user_id'];

  // Use prepared statements to prevent SQL injection
  $stmt = $dms->prepare("SELECT * FROM users WHERE id=?");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
  }
  $stmt->close();
}
ob_end_clean();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>User Profile</title>
  <!-- Bootstrap CSS from CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>

  <!-- Add Users -->
  <div class="container-fluid p-5" style="min-height: 2838.44px;">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Users Profile</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="home.php">Home</a></li>
              <li class="breadcrumb-item active">Profile</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Profile Section -->
    <div class="container-fluid my-5">
      <div class="row justify-content-center">
        <div class="col-md-12 col-lg-12">
          <div class="card shadow-lg border-0 rounded-3">
            <div class="card-body text-center p-4">
              <img src="assets/dist/img/user2-160x160.jpg"
                class="rounded-circle mb-3"
                alt="User profile picture" width="120" height="120">
              <h3 class="card-title mb-0">
                <?= htmlspecialchars($user['first_name'] ?? 'Guest') ?>
                <?= htmlspecialchars($user['last_name'] ?? '') ?>
              </h3>
              <p class="text-muted"><?= htmlspecialchars($user['role'] ?? 'DonorHub User') ?></p> <!-- Display Role here -->

              <ul class="list-group list-group-flush text-start mb-3">
                <li class="list-group-item">
                  <i class="fas fa-envelope me-2 text-primary"></i>
                  <strong>Email:</strong> <?= htmlspecialchars($user['email'] ?? 'N/A') ?>
                </li>
                <li class="list-group-item">
                  <i class="fas fa-calendar-alt me-2 text-warning"></i>
                  <strong>Joined:</strong> <?= htmlspecialchars($user['created_at'] ?? 'N/A') ?>
                </li>
              </ul>

              <!-- Edit Profile Button that triggers the modal -->
              <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                <i class="fas fa-user-edit me-2"></i> Edit Profile
              </button>
            </div>
          </div>
        </div>
      </div>

    </div>
    <!-- /.content-wrapper -->

    <!-- Edit Profile Modal -->
    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="editProfileModalLabel">Edit Profile</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form id="editProfileForm">
              <input type="hidden" name="user_id" id="edit_user_id">
              <div class="mb-3">
                <label for="edit_first_name" class="form-label">First Name</label>
                <input type="text" class="form-control" id="edit_first_name" name="first_name">
              </div>
              <div class="mb-3">
                <label for="edit_last_name" class="form-label">Last Name</label>
                <input type="text" class="form-control" id="edit_last_name" name="last_name">
              </div>
              <div class="mb-3">
                <label for="edit_email" class="form-label">Email address</label>
                <input type="email" class="form-control" id="edit_email" name="email">
              </div>
              <div class="mb-3">
                <label for="edit_password" class="form-label">New Password</label>
                <input type="password" class="form-control" id="edit_password" name="password">
                <div class="form-text">Leave blank to keep your current password.</div>
              </div>
              <div id="update_message" class="mt-3"></div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary" id="saveProfileChanges">Save changes</button>
          </div>
        </div>
      </div>
    </div>

    <!-- jQuery library from CDN -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <!-- Bootstrap JS from CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
      $(document).ready(function() {
        // When the modal is about to be shown, populate the form fields with current data
        $('#editProfileModal').on('show.bs.modal', function() {
          // Get the current user data from the PHP variables
          var userId = <?= json_encode($user['id'] ?? null); ?>;
          var firstName = <?= json_encode($user['first_name'] ?? ''); ?>;
          var lastName = <?= json_encode($user['last_name'] ?? ''); ?>;
          var email = <?= json_encode($user['email'] ?? ''); ?>;
          // Do not populate the password field for security reasons

          // Populate the modal form fields
          $('#edit_user_id').val(userId);
          $('#edit_first_name').val(firstName);
          $('#edit_last_name').val(lastName);
          $('#edit_email').val(email);
          $('#edit_password').val('');
        });

        // Handle form submission via AJAX
        $('#saveProfileChanges').click(function() {
          var formData = $('#editProfileForm').serialize();

          $.ajax({
            url: 'update_profile.php', // The file to handle the update
            type: 'POST',
            data: formData,
            success: function(response) {
              $('#update_message').html(response);
              // Reload the page to show the updated data after a short delay
              setTimeout(function() {
                location.reload();
              }, 1000);
            },
            error: function() {
              $('#update_message').html('<div class="alert alert-danger">An error occurred. Please try again.</div>');
            }
          });
        });
      });
    </script>

</body>

</html>