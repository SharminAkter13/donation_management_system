<?php
// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include('config.php'); // DB connection

// --- FETCH DROPDOWN OPTIONS FOR FORMS ---
$campaigns = $dms->query("SELECT id, name FROM campaigns WHERE status='active'")->fetch_all(MYSQLI_ASSOC);
$payments = $dms->query("SELECT id, type FROM payment_methods")->fetch_all(MYSQLI_ASSOC);
$funds = $dms->query("SELECT id, name FROM funds")->fetch_all(MYSQLI_ASSOC);
$pledges = $dms->query("SELECT id, name FROM pledges")->fetch_all(MYSQLI_ASSOC);

// --- HANDLE FORM SUBMISSIONS ---

// Handle UPDATE
if (isset($_POST["btnUpdate"])) {
    $donationId = intval($_POST["txtId"] ?? 0);
    $campaign_id = intval($_POST['campaign_id'] ?? 0);
    $payment_id = intval($_POST['payment_id'] ?? 0);
    $fund_id = intval($_POST['fund_id'] ?? 0);
    $pledge_id = !empty($_POST['pledge_id']) ? intval($_POST['pledge_id']) : null;
    $amount = floatval($_POST['amount'] ?? 0);

    // Get the user_id of the donor who owns this donation for permission check
    $stmt = $dms->prepare("SELECT donors.user_id FROM donations 
                           LEFT JOIN donors ON donations.donor_id = donors.id 
                           WHERE donations.id = ?");
    $stmt->bind_param("i", $donationId);
    $stmt->execute();
    $stmt->bind_result($donorUserId);
    $stmt->fetch();
    $stmt->close();

    $isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    $isOwner = isset($_SESSION['user_id']) && $_SESSION['user_id'] == $donorUserId;

    if ($isAdmin || $isOwner) {
        if ($campaign_id && $payment_id && $fund_id && $amount > 0) {
            // Check if pledge is set, then use the correct number of placeholders
            if ($pledge_id === null) {
                $stmt = $dms->prepare("UPDATE donations SET campaign_id=?, payment_id=?, fund_id=?, pledge_id=NULL, amount=? WHERE id=?");
                $stmt->bind_param("iiidi", $campaign_id, $payment_id, $fund_id, $amount, $donationId);
            } else {
                $stmt = $dms->prepare("UPDATE donations SET campaign_id=?, payment_id=?, fund_id=?, pledge_id=?, amount=? WHERE id=?");
                $stmt->bind_param("iiiidi", $campaign_id, $payment_id, $fund_id, $pledge_id, $amount, $donationId);
            }

            if ($stmt->execute()) {
                echo "<div class='alert alert-success'>Donation updated successfully!</div>";
            } else {
                echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
            }
            $stmt->close();
        } else {
            echo "<div class='alert alert-warning'>Please fill all required fields correctly.</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>You do not have permission to edit this donation.</div>";
    }
}

// Handle DELETE
if (isset($_POST["btnDelete"])) {
    $donationId = intval($_POST["txtId"] ?? 0);

    if ($donationId) {
        // Get the user_id of the donor who owns this donation
        $stmt = $dms->prepare("SELECT donors.user_id FROM donations 
                               LEFT JOIN donors ON donations.donor_id = donors.id 
                               WHERE donations.id = ?");
        $stmt->bind_param("i", $donationId);
        $stmt->execute();
        $stmt->bind_result($donorUserId);
        $stmt->fetch();
        $stmt->close();

        $isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
        $isOwner = isset($_SESSION['user_id']) && $_SESSION['user_id'] == $donorUserId;

        if (!$isAdmin && !$isOwner) {
            echo "<div class='alert alert-danger'>You do not have permission to delete this donation.</div>";
            exit;
        }

        // Delete donation
        $stmt = $dms->prepare("DELETE FROM donations WHERE id = ?");
        $stmt->bind_param("i", $donationId);
        $stmt->execute();
        $stmt->close();

        echo "<div class='alert alert-success'>Donation deleted successfully</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Donations</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>
<div class="container-fluid p-5">
    <h1>Manage Donations</h1>
    <table class="table table-hover table-striped">
        <thead class="bg-info text-white">
            <tr>
                <th>#ID</th>
                <th>Pledge / Campaign Name</th>
                <th>Donor Name</th>
                <th>Fund Name</th>
                <th>Payment Method</th>
                <th>Amount</th>
                <th>Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $sql = "SELECT d.id, d.amount, d.date, d.donor_id, d.campaign_id, d.pledge_id, d.fund_id, d.payment_id,
                       donors.name AS donor_name, donors.user_id,
                       COALESCE(p.name, c.name) AS pledge_campaign_name,
                       f.name AS fund_name,
                       pm.type AS payment_method
                FROM donations d
                LEFT JOIN donors ON d.donor_id = donors.id
                LEFT JOIN pledges p ON d.pledge_id = p.id
                LEFT JOIN campaigns c ON d.campaign_id = c.id
                LEFT JOIN funds f ON d.fund_id = f.id
                LEFT JOIN payment_methods pm ON d.payment_id = pm.id";

        $donations = $dms->query($sql);

        if ($donations && $donations->num_rows > 0) {
            while ($row = $donations->fetch_assoc()) {
                $isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
                $isOwner = isset($_SESSION['user_id']) && $_SESSION['user_id'] == ($row['user_id'] ?? 0);

                echo "<tr>
                        <td>{$row['id']}</td>
                        <td>" . htmlspecialchars($row['pledge_campaign_name'] ?? 'N/A') . "</td>
                        <td>" . htmlspecialchars($row['donor_name'] ?? 'N/A') . "</td>
                        <td>" . htmlspecialchars($row['fund_name'] ?? 'N/A') . "</td>
                        <td>" . htmlspecialchars($row['payment_method'] ?? 'N/A') . "</td>
                        <td>" . htmlspecialchars($row['amount'] ?? 'N/A') . "</td>
                        <td>" . htmlspecialchars($row['date'] ?? 'N/A') . "</td>
                        <td class='d-flex justify-content-center'>";

                // View button (everyone)
                echo "<button class='btn btn-info btn-sm me-2' data-bs-toggle='modal' data-bs-target='#donationViewModal'
                                data-id='{$row['id']}' 
                                data-pname='" . htmlspecialchars($row['pledge_campaign_name'] ?? '') . "'
                                data-donor='" . htmlspecialchars($row['donor_name'] ?? '') . "'
                                data-fund='" . htmlspecialchars($row['fund_name'] ?? '') . "'
                                data-payment='" . htmlspecialchars($row['payment_method'] ?? '') . "'
                                data-amount='{$row['amount']}'
                                data-date='{$row['date']}'>
                                <i class='fas fa-eye'></i>
                              </button>";

                // Edit/Delete if admin OR owner
                if ($isAdmin || $isOwner) {
                    // Edit button
                    echo "<button type='button' class='btn btn-warning btn-sm me-2' data-bs-toggle='modal' data-bs-target='#donationEditModal'
                                    data-id='{$row['id']}'
                                    data-campaign-id='{$row['campaign_id']}'
                                    data-payment-id='{$row['payment_id']}'
                                    data-fund-id='{$row['fund_id']}'
                                    data-pledge-id='{$row['pledge_id']}'
                                    data-amount='{$row['amount']}'>
                                    <i class='fas fa-edit'></i>
                                </button>";
                    // Delete button
                    echo "<button type='button' class='btn btn-danger btn-sm' data-bs-toggle='modal' data-bs-target='#deleteConfirmModal' data-id='{$row['id']}'>
                                <i class='fas fa-trash-alt'></i>
                              </button>";
                }
                echo "</td></tr>";
            }
        } else {
            echo "<tr><td colspan='8' class='text-center'>No donations found.</td></tr>";
        }
        ?>
        </tbody>
    </table>
</div>

<div class="modal fade" id="donationViewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Donation Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p><strong>ID:</strong> <span id="view-id"></span></p>
                <p><strong>Pledge/Campaign Name:</strong> <span id="view-pname"></span></p>
                <p><strong>Donor Name:</strong> <span id="view-donor"></span></p>
                <p><strong>Fund Name:</strong> <span id="view-fund"></span></p>
                <p><strong>Payment Method:</strong> <span id="view-payment"></span></p>
                <p><strong>Amount:</strong> <span id="view-amount"></span></p>
                <p><strong>Date:</strong> <span id="view-date"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="donationEditModal" tabindex="-1" aria-labelledby="donationEditModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="donationEditModalLabel">Edit Donation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="txtId" id="edit-id">
                    <div class="mb-3">
                        <label class="form-label">Campaign</label>
                        <select class="form-select" name="campaign_id" id="edit-campaign" required>
                            <option value="">-- Select Campaign --</option>
                            <?php foreach ($campaigns as $row): ?>
                                <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Method</label>
                        <select class="form-select" name="payment_id" id="edit-payment" required>
                            <option value="">-- Select Payment --</option>
                            <?php foreach ($payments as $row): ?>
                                <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['type']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Fund</label>
                        <select class="form-select" name="fund_id" id="edit-fund" required>
                            <option value="">-- Select Fund --</option>
                            <?php foreach ($funds as $row): ?>
                                <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pledge (optional)</label>
                        <select class="form-select" name="pledge_id" id="edit-pledge">
                            <option value="">-- No Pledge --</option>
                            <?php foreach ($pledges as $row): ?>
                                <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount</label>
                        <input type="number" step="0.01" class="form-control" name="amount" id="edit-amount" min="0.01" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="btnUpdate" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteConfirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this donation? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="post" style="display:inline;">
                    <input type="hidden" name="txtId" id="delete-id">
                    <button type="submit" name="btnDelete" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // View Modal
    const donationViewModal = document.getElementById('donationViewModal');
    donationViewModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        document.getElementById('view-id').textContent = button.getAttribute('data-id');
        document.getElementById('view-pname').textContent = button.getAttribute('data-pname');
        document.getElementById('view-donor').textContent = button.getAttribute('data-donor');
        document.getElementById('view-fund').textContent = button.getAttribute('data-fund');
        document.getElementById('view-payment').textContent = button.getAttribute('data-payment');
        document.getElementById('view-amount').textContent = button.getAttribute('data-amount');
        document.getElementById('view-date').textContent = button.getAttribute('data-date');
    });

    // Edit Modal
    const donationEditModal = document.getElementById('donationEditModal');
    donationEditModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        document.getElementById('edit-id').value = button.getAttribute('data-id');
        document.getElementById('edit-campaign').value = button.getAttribute('data-campaign-id');
        document.getElementById('edit-payment').value = button.getAttribute('data-payment-id');
        document.getElementById('edit-fund').value = button.getAttribute('data-fund-id');
        document.getElementById('edit-pledge').value = button.getAttribute('data-pledge-id');
        document.getElementById('edit-amount').value = button.getAttribute('data-amount');
    });

    // Delete Modal
    const deleteConfirmModal = document.getElementById('deleteConfirmModal');
    deleteConfirmModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const donationId = button.getAttribute('data-id');
        document.getElementById('delete-id').value = donationId;
    });
});
</script>
</body>
</html>