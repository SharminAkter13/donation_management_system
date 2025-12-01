<?php
// Ensure this file is included via the placeholder.php to enforce access control
if (!isset($_SESSION['user_role'])) {
    header("Location: login.php");
    exit();
}

// Assume config.php and placeholder.php are in the same directory
include 'config.php';

// Check if a transaction ID is provided
if (!isset($_GET['transaction_id']) || !is_numeric($_GET['transaction_id'])) {
    echo '<div class="alert alert-danger">Invalid transaction ID provided.</div>';
    exit();
}

$transaction_id = intval($_GET['transaction_id']);

// Use the session variable directly
$userRole = $_SESSION['user_role'] ?? '';

// --- SECURITY CHECK: VERIFY DONOR OWNERSHIP ---
$is_donor_owner = false;
if ($userRole === 'donor') {
    $stmt_owner = $dms->prepare("SELECT donor_id FROM transactions WHERE id = ?");
    $stmt_owner->bind_param("i", $transaction_id);
    $stmt_owner->execute();
    $result_owner = $stmt_owner->get_result();
    $transaction_owner = $result_owner->fetch_assoc();
    $stmt_owner->close();
    
    // Fetch logged-in donor ID
    $user_id = $_SESSION['user_id'] ?? null;
    $stmt_donor_id = $dms->prepare("SELECT id FROM donors WHERE user_id = ?");
    $stmt_donor_id->bind_param("i", $user_id);
    $stmt_donor_id->execute();
    $result_donor_id = $stmt_donor_id->get_result();
    $logged_in_donor_id = $result_donor_id->fetch_assoc()['id'] ?? null;
    $stmt_donor_id->close();

    if ($transaction_owner && $transaction_owner['donor_id'] === $logged_in_donor_id) {
        $is_donor_owner = true;
    }
}

// Admins and privileged users can view any receipt
$is_privileged_user = in_array($userRole, ['admin', 'campaign_manager', 'volunteer', 'beneficiary']);

if (!$is_donor_owner && !$is_privileged_user) {
    echo '<div class="alert alert-danger">You do not have permission to view this receipt.</div>';
    exit();
}


// Fetch all necessary transaction details
$sql = "SELECT
    t.id, t.date, t.amount, t.status,
    d.name AS donor_name,
    c.name AS campaign_name,
    p.type AS payment_method
    FROM transactions t
    JOIN donors d ON t.donor_id = d.id
    JOIN campaigns c ON t.campaign_id = c.id
    JOIN payment_methods p ON t.payment_id = p.id
    WHERE t.id = ?";

$stmt = $dms->prepare($sql);
$stmt->bind_param("i", $transaction_id);
$stmt->execute();
$result = $stmt->get_result();
$transaction = $result->fetch_assoc();
$stmt->close();
$dms->close();

if (!$transaction) {
    echo '<div class="alert alert-danger">Transaction not found.</div>';
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Donation Receipt #<?= htmlspecialchars($transaction['id']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .receipt-container {
            width: 80%;
            max-width: 700px;
            margin: 50px auto;
            background-color: #fff;
            padding: 40px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border-radius: 10px;
        }
        .receipt-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #343a40;
            padding-bottom: 20px;
        }
        .receipt-header h1 {
            color: #007bff;
            font-weight: bold;
        }
        .receipt-details {
            margin-bottom: 30px;
        }
        .receipt-details .row {
            margin-bottom: 10px;
        }
        .receipt-details .col-md-6 strong {
            color: #343a40;
        }
        .receipt-footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #343a40;
        }
        .print-button {
            text-align: center;
            margin-bottom: 20px;
        }
        @media print {
            body {
                background-color: #fff;
            }
            .receipt-container {
                box-shadow: none;
                margin: 0;
                padding: 0;
                width: 100%;
            }
            .print-button {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="receipt-container container-fluid">
        <div class="receipt-header">
            <h1>Donation Receipt</h1>
            <p class="lead">Thank you for your generous contribution.</p>
        </div>
        
        <div class="receipt-details">
            <div class="row">
                <div class="col-md-6"><strong>Receipt ID:</strong> <?= htmlspecialchars($transaction['id']) ?></div>
                <div class="col-md-6 text-md-end"><strong>Date:</strong> <?= htmlspecialchars(date('F j, Y', strtotime($transaction['date']))) ?></div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-6"><strong>Donor Name:</strong> <?= htmlspecialchars($transaction['donor_name']) ?></div>
                <div class="col-md-6 text-md-end"><strong>Campaign:</strong> <?= htmlspecialchars($transaction['campaign_name']) ?></div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-6"><strong>Amount:</strong> $<?= number_format($transaction['amount'], 2) ?></div>
                <div class="col-md-6 text-md-end"><strong>Payment Method:</strong> <?= htmlspecialchars($transaction['payment_method']) ?></div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-12"><strong>Status:</strong> <span class="badge bg-success">Completed</span></div>
            </div>
        </div>
        
        <div class="receipt-footer">
            <p>Your support makes a significant impact on our mission. For more details, please visit your transaction history.</p>
        </div>
    </div>
    
    <div class="print-button">
        <button class="btn btn-primary" onclick="window.print()">Print this Receipt</button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>