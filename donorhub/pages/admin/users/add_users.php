<?php
// Include database connection
include('config.php');

$message = "";

// --- Handle form submission ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize inputs
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role_id = intval($_POST['role_id'] ?? 0);

    // Validate required fields
    if (empty($first_name) || empty($email) || empty($password) || $role_id <= 0) {
        $message = "<div class='alert alert-danger'>Please fill in all required fields.</div>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "<div class='alert alert-danger'>Invalid email address.</div>";
    } else {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert user using prepared statement
        $sql = "INSERT INTO users (first_name, last_name, email, password, role_id) VALUES (?, ?, ?, ?, ?)";
        if ($stmt = $dms->prepare($sql)) {
            $stmt->bind_param("ssssi", $first_name, $last_name, $email, $hashed_password, $role_id);
            if ($stmt->execute()) {
                $message = "<div class='alert alert-success'>User added successfully!</div>";
            } else {
                $message = "<div class='alert alert-danger'>Database error: " . htmlspecialchars($stmt->error) . "</div>";
            }
            $stmt->close();
        } else {
            $message = "<div class='alert alert-danger'>Database prepare error: " . htmlspecialchars($dms->error) . "</div>";
        }
    }
}

// --- Fetch roles for dropdown ---
$roles = [];
$sql_roles = "SELECT id, name FROM roles ORDER BY name";
if ($result_roles = $dms->query($sql_roles)) {
    while ($row = $result_roles->fetch_assoc()) {
        $roles[] = $row;
    }
    $result_roles->close();
}

$dms->close();
?>


    <div class="container-fluid p-5">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6"><h1>Add User</h1></div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="home.php">Home</a></li>
                            <li class="breadcrumb-item active">Add User</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="row justify-content-center">
                    <div class="col-lg-12">
                        <div class="card  ">
                            <div class="card-header">
                                <h3 class="card-title">Add New User</h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <?= $message ?>
                                <form method="post" action="">
                                    <div class="mb-3">
                                        <label for="first_name" class="form-label">First Name *</label>
                                        <input type="text" name="first_name" id="first_name" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="last_name" class="form-label">Last Name</label>
                                        <input type="text" name="last_name" id="last_name" class="form-control">
                                    </div>
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email *</label>
                                        <input type="email" name="email" id="email" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Password *</label>
                                        <input type="password" name="password" id="password" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="role_id" class="form-label">Role *</label>
                                        <select name="role_id" id="role_id" class="form-control" required>
                                            <option value="">Select Role</option>
                                            <?php foreach($roles as $role): ?>
                                                <option value="<?= htmlspecialchars($role['id']); ?>">
                                                    <?= htmlspecialchars(ucwords($role['name'])); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100">Add User</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
