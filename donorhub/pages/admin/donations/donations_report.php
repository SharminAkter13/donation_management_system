<?php
include 'config.php';

// Fetch all donations with JOINs
$sql = "SELECT d.id, d.name AS donation_name, d.amount, d.date,
        donors.name AS donor_name,
        funds.name AS fund_name,
        payment_methods.type AS payment_method,
        pledges.name AS pledge_name,
        campaigns.name AS campaign_name
        FROM donations d
        LEFT JOIN donors ON d.donor_id = donors.id
        LEFT JOIN funds ON d.fund_id = funds.id
        LEFT JOIN payment_methods ON d.payment_id = payment_methods.id
        LEFT JOIN pledges ON d.pledge_id = pledges.id
        LEFT JOIN campaigns ON d.campaign_id = campaigns.id
        ORDER BY d.date DESC";

$result = $dms->query($sql);

$donations = [];
$campaign_totals = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $donations[] = $row;
        $campaign_name = $row['campaign_name'] ?? 'No Campaign';
        $campaign_totals[$campaign_name] = ($campaign_totals[$campaign_name] ?? 0) + $row['amount'];
    }
    $result->close();
}

$dms->close();

// Prepare data for Chart.js
$chart_labels = json_encode(array_keys($campaign_totals));
$chart_data = json_encode(array_values($campaign_totals));
$chart_bg_colors = json_encode(array_map(fn($i)=>"rgba(54, 162, 235, 0.7)", $campaign_totals));
$chart_border_colors = json_encode(array_map(fn($i)=>"rgba(54, 162, 235, 1)", $campaign_totals));
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Donation Reports</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/css/adminlte.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
</head>
<body class="hold-transition sidebar-mini layout-fixed">


    <div class="container-fluid p-5">
        <section class="content-header">
            <div class="container-fluid mb-3">
                <div class="row">
                    <div class="col-sm-6"><h1>Donation Reports</h1></div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="home.php">Home</a></li>
                            <li class="breadcrumb-item active">Donation Reports</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">

                <!-- Chart Card -->
                <div class="card card-primary card-outline mb-4">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-chart-bar me-2"></i>Donation Amounts by Campaign</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="campaignChart" style="height: 300px;"></canvas>
                    </div>
                </div>

                <!-- Donations Table -->
                <div class="card card-info card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-table me-2"></i>All Donations</h3>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Donation Name</th>
                                    <th>Amount ($)</th>
                                    <th>Date</th>
                                    <th>Donor</th>
                                    <th>Fund</th>
                                    <th>Payment Method</th>
                                    <th>Pledge</th>
                                    <th>Campaign</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($donations)): ?>
                                    <?php foreach ($donations as $donation): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($donation['id']) ?></td>
                                            <td><?= htmlspecialchars($donation['donation_name']) ?></td>
                                            <td><?= number_format($donation['amount'], 2) ?></td>
                                            <td><?= htmlspecialchars($donation['date']) ?></td>
                                            <td><?= htmlspecialchars($donation['donor_name'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($donation['fund_name'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($donation['payment_method'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($donation['pledge_name'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($donation['campaign_name'] ?? '-') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="9" class="text-center">No donations found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </section>
    </div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/js/adminlte.min.js"></script>

<script>
const ctx = document.getElementById('campaignChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= $chart_labels ?>,
        datasets: [{
            label: 'Total Donations ($)',
            data: <?= $chart_data ?>,
            backgroundColor: <?= $chart_bg_colors ?>,
            borderColor: <?= $chart_border_colors ?>,
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>
</body>
</html>
