<?php
include('config.php');

// Fix: get edit_id from POST or GET
$edit_id = $_POST['id'] ?? $_GET['id'] ?? null;
$user = null;

// Fetch user data if id is passed
if ($edit_id) {
    $result = $dms->query("SELECT * FROM users WHERE id='$edit_id'");
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
    }
}

// Handle update form submission
if (isset($_POST["btnUpdate"])) {
    $u_id   = $_POST["txtId"];
    $fname  = $_POST["txtFname"];
    $lname  = $_POST["txtLname"];
    $email  = $_POST["txtEmail"];

    $update = $dms->query("UPDATE users SET first_name='$fname', last_name='$lname', email='$email' WHERE id='$u_id'");
    
    if ($update) {
        echo "<div class='alert alert-success'>User updated successfully</div>";
        echo "<script>setTimeout(function(){ window.location='home.php?page=2'; }, 1000);</script>";
    } else {
        echo "<div class='alert alert-danger'>Error updating user</div>";
    }
}
?>

<div class="container-fluid p-5">
    <div class="row">
        <!-- Sidebar placeholder -->
        <aside class="col-md-3">
            <!-- Sidebar content if needed -->
        </aside>

        <!-- Main content -->
        <main class="col-md-9">
            <div class="card shadow-lg">
                <div class="card-header bg-warning text-white">
                    <h3 class="mb-0">Edit User</h3>
                </div>
                <div class="card-body">
                    <?php if ($user): ?>
                    <form method="post" action="">
                        <input type="hidden" name="txtId" value="<?= $user['id'] ?>">

                        <div class="mb-3">
                            <label class="form-label">First Name</label>
                            <input type="text" name="txtFname" class="form-control" 
                                   value="<?= htmlspecialchars($user['first_name']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="txtLname" class="form-control" 
                                   value="<?= htmlspecialchars($user['last_name']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="txtEmail" class="form-control" 
                                   value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="home.php?page=2" class="btn btn-secondary">Cancel</a>
                            <button type="submit" name="btnUpdate" class="btn btn-success">Update User</button>
                        </div>
                    </form>
                    <?php else: ?>
                        <div class="alert alert-danger">No user found.</div>
                        <a href="home.php?page=2" class="btn btn-secondary">Back</a>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>
