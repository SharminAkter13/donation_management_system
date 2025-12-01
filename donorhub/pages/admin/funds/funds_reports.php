<?php
include 'config.php'; // DB connection

// Fetch fund status counts and collected amounts
$statusQuery = "SELECT status, COUNT(*) as count, SUM(collected_amount) as total_amount FROM funds GROUP BY status";
$result = $dms->query($statusQuery);

$statuses = [];
$counts = [];
$amounts = [];

while ($row = $result->fetch_assoc()) {
    $statuses[] = $row['status'];
    $counts[] = $row['count'];
    $amounts[] = $row['total_amount'];
}

$statuses_json = json_encode($statuses);
$counts_json = json_encode($counts);
$amounts_json = json_encode($amounts);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Funds Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .chart-card {
            max-width: 500px;
            margin: 20px auto;
            padding: 20px;
        }
        canvas {
            width: 100% !important;
            height: 300px !important;
        }
    </style>
</head>
<body>
<div class="container-fluid my-5 p-2">
    <h1 class="text-center mb-4">Funds Report Dashboard</h1>
<div class="row">
    <!-- Fund Status Pie Chart -->
    <div class="card chart-card shadow-sm col-lg-5">
        <div class="card-body">
            <h5 class="card-title text-center">Active / Inactive Funds</h5>
            <canvas id="statusChart"></canvas>
        </div>
    </div>
    
    <!-- Collected Amount Bar Chart -->
    <div class="card chart-card shadow-sm col-lg-5">
        <div class="card-body">
            <h5 class="card-title text-center">Collected Amounts</h5>
            <canvas id="amountChart"></canvas>
        </div>
    </div>
</div>


</div>
<script>
const statuses = <?php echo $statuses_json; ?>;
const counts = <?php echo $counts_json; ?>;
const amounts = <?php echo $amounts_json; ?>;

// Pie chart: Active / Inactive funds
const ctxStatus = document.getElementById('statusChart').getContext('2d');
new Chart(ctxStatus, {
    type: 'pie',
    data: {
        labels: statuses,
        datasets: [{
            label: 'Number of Funds',
            data: counts,
            backgroundColor: ['#28a745', '#dc3545'],
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

// Bar chart: Collected amounts
const ctxAmount = document.getElementById('amountChart').getContext('2d');
new Chart(ctxAmount, {
    type: 'bar',
    data: {
        labels: statuses,
        datasets: [{
            label: 'Collected Amount',
            data: amounts,
            backgroundColor: ['#007bff', '#ffc107'],
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
