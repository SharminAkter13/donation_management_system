<?php
include 'config.php';

// --- GET LOGGED IN USER INFO ---
$user_id = $_SESSION['user_id'] ?? null;
$user_role = $_SESSION['user_role'] ?? null;
$logged_in_donor_id = null;

if ($user_role === 'donor') {
    $stmt = $dms->prepare("SELECT id FROM donors WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $donor_row = $result->fetch_assoc();
    $logged_in_donor_id = $donor_row['id'] ?? null;
    $stmt->close();
}

// --- HANDLE ACTIONS (UPDATE AND DELETE) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $id = intval($_POST['id']);
    
    // Authorization Check: Admins, Campaign Managers, Volunteers, and Beneficiaries can act on any transaction.
    // Donors can only act on their own.
    $can_act = false;
    if (in_array($user_role, ['admin', 'campaign_manager', 'volunteer', 'beneficiary'])) {
        $can_act = true;
    } elseif ($user_role === 'donor') {
        // For donors, we need to verify ownership
        $stmt_owner = $dms->prepare("SELECT donor_id FROM transactions WHERE id = ?");
        $stmt_owner->bind_param("i", $id);
        $stmt_owner->execute();
        $result_owner = $stmt_owner->get_result();
        $transaction_owner = $result_owner->fetch_assoc();
        $stmt_owner->close();

        if ($transaction_owner && $transaction_owner['donor_id'] === $logged_in_donor_id) {
            $can_act = true;
        } else {
            $error = "You do not have permission to perform this action on this transaction.";
        }
    }

    if ($can_act) {
        if ($action === 'update') {
            $new_amount = floatval($_POST['amount']);
            $new_status = $_POST['status'];
            $new_payment_id = intval($_POST['payment_id']);

            // NEW LOGIC: Only an admin can approve a transaction (change status to 'complete')
            if ($new_status === 'complete' && $user_role !== 'admin') {
                $error = "Only an admin can approve a transaction.";
            } else {
                $dms->begin_transaction();
                try {
                    // Fetch old transaction data to calculate the amount change
                    $stmt_old_data = $dms->prepare("SELECT amount, campaign_id FROM transactions WHERE id = ?");
                    $stmt_old_data->bind_param("i", $id);
                    $stmt_old_data->execute();
                    $result = $stmt_old_data->get_result();
                    $old_data = $result->fetch_assoc();
                    $stmt_old_data->close();

                    if ($old_data) {
                        $amount_difference = $new_amount - $old_data['amount'];
                        $campaign_id = $old_data['campaign_id'];

                        // Update the transactions table
                        $stmt_trans = $dms->prepare("UPDATE transactions SET amount = ?, status = ?, payment_id = ? WHERE id = ?");
                        $stmt_trans->bind_param("dsii", $new_amount, $new_status, $new_payment_id, $id);
                        if (!$stmt_trans->execute()) {
                            throw new Exception("Failed to update transaction.");
                        }
                        $stmt_trans->close();

                        // Update the donations table
                        $donation_status = ($new_status === 'complete') ? 'Verified' : 'Pending';
                        $stmt_don = $dms->prepare("UPDATE donations SET amount = ?, status = ?, payment_id = ? WHERE id = ?");
                        $stmt_don->bind_param("dsii", $new_amount, $donation_status, $new_payment_id, $id);
                        if (!$stmt_don->execute()) {
                            throw new Exception("Failed to update donation.");
                        }
                        $stmt_don->close();
                        
                        // Update the campaigns table
                        $stmt_camp = $dms->prepare("UPDATE campaigns SET total_raised = total_raised + ? WHERE id = ?");
                        $stmt_camp->bind_param("di", $amount_difference, $campaign_id);
                        if (!$stmt_camp->execute()) {
                            throw new Exception("Failed to update campaign total.");
                        }
                        $stmt_camp->close();
                    }

                    $dms->commit();
                    $message = "Transaction updated successfully!";
                } catch (Exception $e) {
                    $dms->rollback();
                    $error = $e->getMessage();
                }
            }

        } elseif ($action === 'delete') {
            $dms->begin_transaction();
            try {
                // Get data before deleting
                $stmt_get = $dms->prepare("SELECT amount, campaign_id FROM donations WHERE id = ?");
                $stmt_get->bind_param("i", $id);
                $stmt_get->execute();
                $result = $stmt_get->get_result();
                $donation_data = $result->fetch_assoc();
                $stmt_get->close();

                // Delete from transactions
                $stmt_del_trans = $dms->prepare("DELETE FROM transactions WHERE id = ?");
                $stmt_del_trans->bind_param("i", $id);
                if (!$stmt_del_trans->execute()) {
                    throw new Exception("Failed to delete transaction.");
                }
                $stmt_del_trans->close();
                
                // Delete from donations (this will trigger the campaign update due to the existing trigger)
                $stmt_del_don = $dms->prepare("DELETE FROM donations WHERE id = ?");
                $stmt_del_don->bind_param("i", $id);

                if (!$stmt_del_don->execute()) {
                    throw new Exception("Failed to delete donation record.");
                }
                $stmt_del_don->close();
                
                $dms->commit();
                $message = "Transaction and corresponding donation deleted successfully!";
            } catch (Exception $e) {
                $dms->rollback();
                $error = $e->getMessage();
            }
        }
    }
}

// --- FETCH DATA FOR DISPLAY ---
// Changed the query to JOIN tables and fetch names
$query_transactions = "
    SELECT 
        t.*, 
        d.name AS donor_name, 
        c.name AS campaign_name,
        p.type AS payment_name
    FROM transactions t 
    JOIN donations d ON t.id = d.id 
    JOIN campaigns c ON t.campaign_id = c.id
    JOIN payment_methods p ON t.payment_id = p.id
";

if ($user_role === 'donor' && $logged_in_donor_id) {
    // A donor should only see their own transactions
    $query_transactions .= " WHERE d.donor_id = ? ORDER BY t.date DESC";
    $stmt = $dms->prepare($query_transactions);
    $stmt->bind_param("i", $logged_in_donor_id);
    $stmt->execute();
    $transactions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    // Admins and others see all transactions
    $query_transactions .= " ORDER BY t.date DESC";
    $transactions = $dms->query($query_transactions)->fetch_all(MYSQLI_ASSOC);
}

$payments = $dms->query("SELECT id, type FROM payment_methods")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Transactions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card shadow-lg p-4 rounded-3">
        <h2 class="mb-4 text-center">Manage Transactions</h2>
        <?php if (isset($message)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Payment Method</th>
                        <th>Donor Name</th>
                        <th>Campaign Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $transaction): ?>
                        <tr>
                            <td><?= htmlspecialchars($transaction['id']) ?></td>
                            <td><?= htmlspecialchars($transaction['status']) ?></td>
                            <td><?= htmlspecialchars($transaction['date']) ?></td>
                            <td>$<?= number_format($transaction['amount'], 2) ?></td>
                            <td><?= htmlspecialchars($transaction['payment_name']) ?></td>
                            <td><?= htmlspecialchars($transaction['donor_name']) ?></td>
                            <td><?= htmlspecialchars($transaction['campaign_name']) ?></td>
                            <td>
                                <?php
                                $is_donor_owner = ($user_role === 'donor' && $transaction['donor_id'] === $logged_in_donor_id);
                                $is_privileged_user = in_array($user_role, ['admin', 'campaign_manager', 'volunteer', 'beneficiary']);
                                ?>
                                <?php if ($is_donor_owner || $is_privileged_user): ?>
                                    <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#viewEditModal" data-transaction='<?= json_encode($transaction) ?>'>View/Edit</button>
                                    <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="<?= $transaction['id'] ?>">Delete</button>
                                    <?php if ($transaction['status'] === 'complete'): ?>
                                        <a href="home.php?page=35&transaction_id=<?= $transaction['id'] ?>" class="btn btn-primary btn-sm mt-1">View Receipt</a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <button type="button" class="btn btn-secondary btn-sm" disabled>No Action</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="viewEditModal" tabindex="-1" aria-labelledby="viewEditModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewEditModalLabel">Transaction Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" id="transaction_id">
                    
                    <div class="mb-3">
                        <label for="view_edit_status" class="form-label">Status</label>
                        <select class="form-select" id="view_edit_status" name="status">
                            <option value="complete">Complete</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="view_edit_amount" class="form-label">Amount</label>
                        <input type="number" step="0.01" class="form-control" id="view_edit_amount" name="amount" required>
                    </div>

                    <div class="mb-3">
                        <label for="view_edit_payment" class="form-label">Payment Method</label>
                        <select class="form-select" id="view_edit_payment" name="payment_id">
                            <?php foreach ($payments as $payment): ?>
                                <option value="<?= $payment['id'] ?>"><?= htmlspecialchars($payment['type']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="view_edit_date" class="form-label">Date</label>
                        <input type="text" class="form-control" id="view_edit_date" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="view_edit_donor_name" class="form-label">Donor Name</label>
                        <input type="text" class="form-control" id="view_edit_donor_name" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="view_edit_campaign_name" class="form-label">Campaign Name</label>
                        <input type="text" class="form-control" id="view_edit_campaign_name" readonly>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Delete Transaction</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this transaction? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <form method="POST">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="delete_transaction_id">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // JavaScript to populate the View/Edit modal
    var viewEditModal = document.getElementById('viewEditModal');
    viewEditModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var transactionData = JSON.parse(button.getAttribute('data-transaction'));
        
        var modalTitle = viewEditModal.querySelector('.modal-title');
        var idInput = viewEditModal.querySelector('#transaction_id');
        var statusSelect = viewEditModal.querySelector('#view_edit_status');
        var amountInput = viewEditModal.querySelector('#view_edit_amount');
        var paymentSelect = viewEditModal.querySelector('#view_edit_payment');
        var dateInput = viewEditModal.querySelector('#view_edit_date');
        var donorNameInput = viewEditModal.querySelector('#view_edit_donor_name');
        var campaignNameInput = viewEditModal.querySelector('#view_edit_campaign_name');

        modalTitle.textContent = 'Transaction #' + transactionData.id;
        idInput.value = transactionData.id;
        statusSelect.value = transactionData.status;
        amountInput.value = transactionData.amount;
        paymentSelect.value = transactionData.payment_id;
        dateInput.value = transactionData.date;
        donorNameInput.value = transactionData.donor_name;
        campaignNameInput.value = transactionData.campaign_name;
    });

    // JavaScript to populate the Delete modal
    var deleteModal = document.getElementById('deleteModal');
    deleteModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var transactionId = button.getAttribute('data-id');
        
        var deleteIdInput = deleteModal.querySelector('#delete_transaction_id');
        deleteIdInput.value = transactionId;
    });
</script>

</body>
</html>
