<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'], $_SESSION['user_role'])) {
    header("Location: login.php");
    exit;
}

require 'config.php';
$userId = intval($_SESSION['user_id']);
$userRole = $_SESSION['user_role'];
$allowedRoles = ['admin', 'beneficiary'];

if (!in_array($userRole, $allowedRoles, true)) {
    echo "<div class='alert alert-danger text-center'>Access Denied.</div>";
    exit;
}

$success_message = $error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['beneficiary_id'] ?? 0);
    $name = trim($_POST['beneficiary_name'] ?? '');
    $email = trim($_POST['beneficiary_email'] ?? '');
    $phone = trim($_POST['beneficiary_phone'] ?? '');
    $address = trim($_POST['beneficiary_address'] ?? '');
    $needs = trim($_POST['beneficiary_needs'] ?? '');
    $status = trim($_POST['beneficiary_status'] ?? '');

    if ($userRole !== 'admin' && $id !== $userId) {
        $error_message = "Unauthorized action.";
    } elseif ($id > 0 && $name !== '' && $email !== '') {
        $stmt = $dms->prepare("
            UPDATE beneficiaries
            SET name = ?, email = ?, phone = ?, address = ?, required_support = ?, status = ?
            WHERE user_id = ?
        ");
        if ($stmt) {
            $stmt->bind_param("ssssssi", $name, $email, $phone, $address, $needs, $status, $id);
            $stmt->execute() ? $success_message = "Updated successfully!" : $error_message = $stmt->error;
            $stmt->close();
        } else {
            $error_message = $dms->error;
        }
    }
    $_SESSION['success_message'] = $success_message;
    $_SESSION['error_message'] = $error_message;
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

if (isset($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    if ($userRole === 'beneficiary' && $deleteId !== $userId) {
        $error_message = "Unauthorized action.";
    } else {
        $stmt = $dms->prepare("DELETE FROM beneficiaries WHERE user_id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $deleteId);
            $stmt->execute() ? $success_message = "Deleted successfully!" : $error_message = $stmt->error;
            $stmt->close();
        } else {
            $error_message = $dms->error;
        }
    }
    $_SESSION['success_message'] = $success_message;
    $_SESSION['error_message'] = $error_message;
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

$beneficiaries = [];
if ($userRole === 'admin') {
    $res = $dms->query("SELECT * FROM beneficiaries");
    while ($row = $res->fetch_assoc()) {
        $beneficiaries[] = $row;
    }
} else {
    $stmt = $dms->prepare("SELECT * FROM beneficiaries WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $beneficiaries[] = $row;
    } else {
        $error_message = "No profile found for your account.";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Beneficiaries - DonorHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/dist/css/adminlte.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .table-wrapper {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .content-wrapper {
            padding: 20px;
        }
        .card {
            border-radius: 12px;
            box-shadow: 0px 4px 15px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

<div class="container-fluid p-3" style="min-height: 2838.44px;">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Beneficiaries Management</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="home.php">Home</a></li>
                        <li class="breadcrumb-item active">Beneficiaries</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="card">
            <div class="card-header container-fluid">
                <h3 class="card-title">Manage Beneficiaries</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                    <button type="button" class="btn btn-tool" data-card-widget="remove" title="Remove">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="card-body container-fluid">
                <div class="container-fluid ">
                    <div class="table-responsive">
                        <?php if (!empty($success_message)): ?>
                            <div class="alert alert-success text-center"><?= htmlspecialchars($success_message) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($error_message)): ?>
                            <div class="alert alert-danger text-center"><?= htmlspecialchars($error_message) ?></div>
                        <?php endif; ?>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4><i class="fas fa-users me-2"></i>
                            <?php if ($userRole === 'admin'): ?>
                                All Beneficiaries
                            <?php else: ?>
                                Your Profile
                            <?php endif; ?>
                            </h4>
                            <?php if ($userRole === 'admin'): ?>
                                <input type="text" id="searchBox" class="form-control w-25" placeholder="Search...">
                            <?php endif; ?>
                        </div>

                        <table class="table table-hover" id="beneficiaryTable">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Full Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Address</th>
                                    <th>Needs</th>
                                    <th>Status</th> <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($beneficiaries as $b): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($b['id']) ?></td>
                                        <td><?= htmlspecialchars($b['name']) ?></td>
                                        <td><?= htmlspecialchars($b['email']) ?></td>
                                        <td><?= htmlspecialchars($b['phone']) ?></td>
                                        <td><?= htmlspecialchars($b['address']) ?></td>
                                        <td ><?= htmlspecialchars($b['required_support']) ?></td>
                                        <td>
                                            <?php
                                            $badge_class = ($b['status'] === 'Active') ? 'bg-success' : 'bg-danger';
                                            echo '<span class="badge ' . $badge_class . '">' . htmlspecialchars($b['status']) . '</span>';
                                            ?>
                                        </td> <td>
                                            <button type="button" class="btn btn-sm btn-info view-btn"
                                                data-bs-toggle="modal" data-bs-target="#viewBeneficiaryModal"
                                                data-id="<?= htmlspecialchars($b['id']) ?>"
                                                data-name="<?= htmlspecialchars($b['name']) ?>"
                                                data-email="<?= htmlspecialchars($b['email']) ?>"
                                                data-phone="<?= htmlspecialchars($b['phone']) ?>"
                                                data-address="<?= htmlspecialchars($b['address']) ?>"
                                                data-needs="<?= htmlspecialchars($b['required_support']) ?>"
                                                data-status="<?= htmlspecialchars($b['status']) ?>"> <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-warning edit-btn"
                                                data-bs-toggle="modal" data-bs-target="#editBeneficiaryModal"
                                                data-id="<?= htmlspecialchars($b['id']) ?>"
                                                data-name="<?= htmlspecialchars($b['name']) ?>"
                                                data-email="<?= htmlspecialchars($b['email']) ?>"
                                                data-phone="<?= htmlspecialchars($b['phone']) ?>"
                                                data-address="<?= htmlspecialchars($b['address']) ?>"
                                                data-needs="<?= htmlspecialchars($b['required_support']) ?>"
                                                data-status="<?= htmlspecialchars($b['status']) ?>"> <i class="fas fa-edit"></i>
                                            </button>
                                            <?php if ($userRole === 'admin' || ($userRole === 'beneficiary' && $b['id'] === $userId)): ?>
                                                <button type="button" class="btn btn-sm btn-danger delete-btn" data-bs-toggle="modal" data-bs-target="#deleteBeneficiaryModal" data-id="<?= htmlspecialchars($b['id']) ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="editBeneficiaryModal" tabindex="-1" aria-labelledby="editBeneficiaryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="editBeneficiaryModalLabel">Edit Beneficiary</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editBeneficiaryForm" method="POST" action="">
                    <input type="hidden" name="beneficiary_id" id="beneficiary_id">
                    <div class="mb-3">
                        <label for="beneficiary_name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="beneficiary_name" name="beneficiary_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="beneficiary_email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="beneficiary_email" name="beneficiary_email" required>
                    </div>
                    <div class="mb-3">
                        <label for="beneficiary_phone" class="form-label">Phone Number</label>
                        <input type="text" class="form-control" id="beneficiary_phone" name="beneficiary_phone" required>
                    </div>
                    <div class="mb-3">
                        <label for="beneficiary_address" class="form-label">Address</label>
                        <textarea class="form-control" id="beneficiary_address" name="beneficiary_address" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="beneficiary_needs" class="form-label">Needs / Support Required</label>
                        <textarea class="form-control" id="beneficiary_needs" name="beneficiary_needs" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="beneficiary_status" class="form-label">Status</label>
                        <select class="form-select" id="beneficiary_status" name="beneficiary_status" required>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Update Beneficiary</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="viewBeneficiaryModal" tabindex="-1" aria-labelledby="viewBeneficiaryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="viewBeneficiaryModalLabel">Beneficiary Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong>Full Name:</strong> <span id="view_beneficiary_name"></span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Email Address:</strong> <span id="view_beneficiary_email"></span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Phone Number:</strong> <span id="view_beneficiary_phone"></span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Status:</strong> <span id="view_beneficiary_status"></span>
                    </div>
                </div>
                <div class="mb-3">
                    <strong>Address:</strong> <p id="view_beneficiary_address" class="border p-2 rounded"></p>
                </div>
                <div class="mb-3">
                    <strong>Needs / Support Required:</strong> <p id="view_beneficiary_needs" class="border p-2 rounded"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteBeneficiaryModal" tabindex="-1" aria-labelledby="deleteBeneficiaryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteBeneficiaryModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this beneficiary? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a id="confirmDeleteLink" href="#" class="btn btn-danger">Delete</a>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/dist/js/adminlte.min.js"></script>

<script>
    // Search Filter Script (only for admins)
    <?php if ($userRole === 'admin'): ?>
    document.getElementById("searchBox").addEventListener("keyup", function() {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll("#beneficiaryTable tbody tr");
        rows.forEach(row => {
            let text = row.innerText.toLowerCase();
            row.style.display = text.includes(filter) ? "" : "none";
        });
    });
    <?php endif; ?>

    // Populate the Edit modal with data from the clicked row
    const editBeneficiaryModal = document.getElementById('editBeneficiaryModal');
    editBeneficiaryModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const id = button.getAttribute('data-id');
        const name = button.getAttribute('data-name');
        const email = button.getAttribute('data-email');
        const phone = button.getAttribute('data-phone');
        const address = button.getAttribute('data-address');
        const needs = button.getAttribute('data-needs');
        const status = button.getAttribute('data-status');
        
        const modalForm = editBeneficiaryModal.querySelector('#editBeneficiaryForm');
        modalForm.querySelector('#beneficiary_id').value = id;
        modalForm.querySelector('#beneficiary_name').value = name;
        modalForm.querySelector('#beneficiary_email').value = email;
        modalForm.querySelector('#beneficiary_phone').value = phone;
        modalForm.querySelector('#beneficiary_address').value = address;
        modalForm.querySelector('#beneficiary_needs').value = needs;
        modalForm.querySelector('#beneficiary_status').value = status;
    });

    // Populate the View modal with data from the clicked row
    const viewBeneficiaryModal = document.getElementById('viewBeneficiaryModal');
    viewBeneficiaryModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const name = button.getAttribute('data-name');
        const email = button.getAttribute('data-email');
        const phone = button.getAttribute('data-phone');
        const address = button.getAttribute('data-address');
        const needs = button.getAttribute('data-needs');
        const status = button.getAttribute('data-status');
        
        const modal = viewBeneficiaryModal;
        modal.querySelector('#view_beneficiary_name').textContent = name;
        modal.querySelector('#view_beneficiary_email').textContent = email;
        modal.querySelector('#view_beneficiary_phone').textContent = phone;
        modal.querySelector('#view_beneficiary_address').textContent = address;
        modal.querySelector('#view_beneficiary_needs').textContent = needs;
        modal.querySelector('#view_beneficiary_status').textContent = status;
    });

    // Set the href for the delete button inside the modal
    const deleteBeneficiaryModal = document.getElementById('deleteBeneficiaryModal');
    deleteBeneficiaryModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const id = button.getAttribute('data-id');
        const confirmDeleteLink = deleteBeneficiaryModal.querySelector('#confirmDeleteLink');
        confirmDeleteLink.href = '?delete=' + id;
    });
</script>

</body>
</html>