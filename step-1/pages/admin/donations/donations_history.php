<?php
include 'config.php';

// === Fetch Donation Summary ===
$totalDonations = 0;
$totalDonationCount = 0;
$totalDonors = 0;
$donations = [];

$sqlSummary = "SELECT d.id, d.name, d.amount, d.date, 
                dn.name AS donor_name,
                f.name AS fund_name,
                p.type AS payment_type,
                c.name AS campaign_name
               FROM donations d
               LEFT JOIN donors dn ON d.donor_id = dn.id
               LEFT JOIN funds f ON d.fund_id = f.id
               LEFT JOIN payment_methods p ON d.payment_id = p.id
               LEFT JOIN campaigns c ON d.campaign_id = c.id
               ORDER BY d.date DESC";

$result = $dms->query($sqlSummary);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $donations[] = $row;
        $totalDonations += $row['amount'];
    }
    $totalDonationCount = count($donations);
    $totalDonors = count(array_unique(array_column($donations, 'donor_name')));
    $result->close();
}

// Prepare data for pie chart (top donors by total donation)
$donorTotals = [];
foreach ($donations as $d) {
    $donorTotals[$d['donor_name']] = ($donorTotals[$d['donor_name']] ?? 0) + $d['amount'];
}

// Sort descending
arsort($donorTotals);

$labels = array_keys($donorTotals);
$amounts = array_values($donorTotals);

// Generate colors
$colors = [
    'rgba(255, 99, 132, 0.7)',
    'rgba(54, 162, 235, 0.7)',
    'rgba(255, 206, 86, 0.7)',
    'rgba(75, 192, 192, 0.7)',
    'rgba(153, 102, 255, 0.7)',
    'rgba(255, 159, 64, 0.7)',
];
$bgColors = json_encode(array_slice($colors, 0, count($labels)));
$js_labels = json_encode($labels);
$js_amounts = json_encode($amounts);

$dms->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Donation History</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/css/adminlte.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
</head>
<body class="hold-transition sidebar-mini layout-fixed">


    <div class="container-fluid p-4">
        <!-- Status Boxes -->
        <div class="row mb-4">
            <div class="col-lg-4 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>$<?= number_format($totalDonations, 2) ?></h3>
                        <p>Total Donations</p>
                    </div>
                    <div class="icon"><i class="fas fa-dollar-sign"></i></div>
                </div>
            </div>
            <div class="col-lg-4 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?= $totalDonors ?></h3>
                        <p>Total Donors</p>
                    </div>
                    <div class="icon"><i class="fas fa-users"></i></div>
                </div>
            </div>
            <div class="col-lg-4 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3><?= $totalDonationCount ?></h3>
                        <p>Donation Entries</p>
                    </div>
                    <div class="icon"><i class="fas fa-hand-holding-heart"></i></div>
                </div>
            </div>
        </div>

        <!-- Pie Chart -->
        <div class="card card-primary card-outline mb-4">
            <div class="card-header">
                <h3 class="card-title">Top Donors by Amount</h3>
            </div>
            <div class="card-body">
                <canvas id="donorPieChart" style="max-height:300px;"></canvas>
            </div>
        </div>

        <!-- Donations Table -->
        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title">All Donations</h3>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Donation Name</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Donor</th>
                            <th>Fund</th>
                            <th>Payment</th>
                            <th>Campaign</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($donations as $d) : ?>
                            <tr>
                                <td><?= $d['id'] ?></td>
                                <td><?= htmlspecialchars($d['name']) ?></td>
                                <td>$<?= number_format($d['amount'], 2) ?></td>
                                <td><?= $d['date'] ?></td>
                                <td><?= htmlspecialchars($d['donor_name']) ?></td>
                                <td><?= htmlspecialchars($d['fund_name']) ?></td>
                                <td><?= htmlspecialchars($d['payment_type']) ?></td>
                                <td><?= htmlspecialchars($d['campaign_name']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>


<script>
const ctx = document.getElementById('donorPieChart').getContext('2d');
new Chart(ctx, {
    type: 'pie',
    data: {
        labels: <?= $js_labels ?>,
        datasets: [{
            data: <?= $js_amounts ?>,
            backgroundColor: <?= $bgColors ?>,
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom' }
        }
    }
});
</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/js/adminlte.min.js"></script>
</body>
</html>
