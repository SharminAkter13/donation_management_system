<?php
// Database connection
include('config.php');

$message = "";

// Check database connection
if (!$dms) {
    die("Connection failed: " . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['beneficiary_name'] ?? '');
    $email = trim($_POST['beneficiary_email'] ?? '');
    $phone = trim($_POST['beneficiary_phone'] ?? '');
    $address = trim($_POST['beneficiary_address'] ?? '');
    $needs = trim($_POST['beneficiary_needs'] ?? '');
    $user_id = intval($_POST['user_id'] ?? 0);
    $status = trim($_POST['beneficiary_status'] ?? 'Inactive'); // Capture the new status field, with a default value

    // Validate inputs
    if (empty($name) || empty($email) || empty($phone) || empty($address) || empty($needs) || $user_id <= 0 || empty($status)) {
        $message = "<div class='alert alert-danger text-center'>Please fill in all required fields and select a user.</div>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "<div class='alert alert-danger text-center'>Invalid email address.</div>";
    } else {
        // Use a prepared statement to prevent SQL injection
        $query = "INSERT INTO beneficiaries (name, email, phone, address, required_support, user_id, status) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";

        if ($stmt = $dms->prepare($query)) {
            $stmt->bind_param("sssssis", $name, $email, $phone, $address, $needs, $user_id, $status);

            if ($stmt->execute()) {
                $message = "<div class='alert alert-success text-center'>Beneficiary added successfully!</div>";
            } else {
                $message = "<div class='alert alert-danger text-center'>Error adding beneficiary: " . htmlspecialchars($stmt->error) . "</div>";
            }
            $stmt->close();
        } else {
            $message = "<div class='alert alert-danger text-center'>Database prepare error: " . htmlspecialchars($dms->error) . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Beneficiary - DonorHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            border-radius: 12px;
            box-shadow: 0px 4px 15px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

<div class="container my-5 p-5">
    <div class="row">
        <div class="col-md-12 mx-auto">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h4 class="mb-0"><i class="fas fa-hand-holding-heart me-2"></i>Add Beneficiary</h4>
                        </div>
                        <div class="card-body">
                            <?= $message ?>
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="beneficiary_name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="beneficiary_name" name="beneficiary_name" placeholder="Enter beneficiary's full name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="beneficiary_email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="beneficiary_email" name="beneficiary_email" placeholder="Enter email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="beneficiary_phone" class="form-label">Phone Number</label>
                                    <input type="text" class="form-control" id="beneficiary_phone" name="beneficiary_phone" placeholder="Enter phone number" required>
                                </div>
                                <div class="mb-3">
                                    <label for="beneficiary_address" class="form-label">Address</label>
                                    <textarea class="form-control" id="beneficiary_address" name="beneficiary_address" rows="3" placeholder="Enter full address" required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="beneficiary_needs" class="form-label">Needs / Support Required</label>
                                    <textarea class="form-control" id="beneficiary_needs" name="beneficiary_needs" rows="3" placeholder="Describe the needs" required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="user_id" class="form-label">Select User</label>
                                    <select class="form-control" id="user_id" name="user_id" required>
                                        <option value="">Select a user</option>
                                        <?php
                                        // Corrected query to get only users with role_id = 5
                                        $result = mysqli_query($dms, "SELECT id, CONCAT(first_name, ' ', last_name) AS full_name FROM users WHERE role_id = 5 ORDER BY first_name");
                                        if (!$result) {
                                            echo "<option value=''>Error fetching users</option>";
                                        } else {
                                            while ($user = mysqli_fetch_assoc($result)) {
                                                echo "<option value='{$user['id']}'>" . htmlspecialchars($user['full_name']) . "</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="beneficiary_status" class="form-label">Status</label>
                                    <select class="form-control" id="beneficiary_status" name="beneficiary_status" required>
                                        <option value="Active">Active</option>
                                        <option value="Inactive">Inactive</option>
                                    </select>
                                </div>
                                <div class="text-end">
                                    <button type="reset" class="btn btn-secondary me-2">Reset</button>
                                    <button type="submit" class="btn btn-primary">Add Beneficiary</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

</body>
</html>