<?php
include('config.php');

// Get ID from URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;


$result = mysqli_query($dms, "SELECT * FROM beneficiaries WHERE id=$id");
$beneficiary = mysqli_fetch_assoc($result);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['beneficiary_name'];
    $email = $_POST['beneficiary_email'];
    $phone = $_POST['beneficiary_phone'];
    $address = $_POST['beneficiary_address'];
    $needs = $_POST['beneficiary_needs'];

    // Example update query
    mysqli_query($dms, "UPDATE beneficiaries SET name='$name', email='$email', phone='$phone', address='$address', required_support='$needs' WHERE id=$id");

    echo "<div class='alert alert-success text-center'>Beneficiary updated successfully!</div>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Beneficiary - DonorHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .card {
            border-radius: 12px;
            box-shadow: 0px 4px 15px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-warning text-white">
                    <h4 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Beneficiary</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="beneficiary_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="beneficiary_name" name="beneficiary_name" value="<?= htmlspecialchars($beneficiary['name']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="beneficiary_email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="beneficiary_email" name="beneficiary_email" value="<?= htmlspecialchars($beneficiary['email']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="beneficiary_phone" class="form-label">Phone Number</label>
                            <input type="text" class="form-control" id="beneficiary_phone" name="beneficiary_phone" value="<?= htmlspecialchars($beneficiary['phone']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="beneficiary_address" class="form-label">Address</label>
                            <textarea class="form-control" id="beneficiary_address" name="beneficiary_address" rows="3" required><?= htmlspecialchars($beneficiary['address']) ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="beneficiary_needs" class="form-label">Needs / Support Required</label>
                            <textarea class="form-control" id="beneficiary_needs" name="beneficiary_needs" rows="3" required><?= htmlspecialchars($beneficiary['needs']) ?></textarea>
                        </div>

                        <div class="text-end">
                            <a href="manage_beneficiaries.php" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-warning">Update Beneficiary</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap & FontAwesome -->
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
