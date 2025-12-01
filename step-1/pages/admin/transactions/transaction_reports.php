<?php
include 'config.php';

// --- FETCH TRANSACTION DATA FOR THE CHART ---
$query_transactions = "
    SELECT 
        c.name AS campaign_name,
        SUM(t.amount) AS total_amount
    FROM transactions t 
    JOIN campaigns c ON t.campaign_id = c.id
    GROUP BY c.name
    ORDER BY total_amount DESC
";
$result = $dms->query($query_transactions);
$transactions_data = $result->fetch_all(MYSQLI_ASSOC);

// --- FETCH SUMMARY DATA ---
$query_summary = "
    SELECT 
        COUNT(id) AS total_transactions,
        SUM(amount) AS total_amount,
        AVG(amount) AS average_amount
    FROM transactions
";
$summary_result = $dms->query($query_summary);
$summary = $summary_result->fetch_assoc();

// Prepare data for JavaScript
$chart_labels = json_encode(array_column($transactions_data, 'campaign_name'));
$chart_amounts = json_encode(array_column($transactions_data, 'total_amount'));

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Transaction Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card shadow-lg p-4 rounded-3">
            <h2 class="mb-4 text-center">Transaction Report 📈</h2>
            
            <div class="row text-center mb-4">
                <div class="col-md-4">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h5 class="card-title">Total Transactions</h5>
                            <p class="card-text fs-4"><?= htmlspecialchars($summary['total_transactions']) ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h5 class="card-title">Total Amount Raised</h5>
                            <p class="card-text fs-4">$<?= number_format($summary['total_amount'], 2) ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <h5 class="card-title">Average Transaction</h5>
                            <p class="card-text fs-4">$<?= number_format($summary['average_amount'], 2) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 text-center">
                <h5>Donations by Campaign</h5>
                <canvas id="transactionsChart" style="max-width: 600px; margin: auto;"></canvas>
            </div>
        </div>
    </div>

    <script>
    const labels = <?= $chart_labels ?>;
    const data = <?= $chart_amounts ?>;

    const ctx = document.getElementById('transactionsChart').getContext('2d');
    
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{
                label: 'Total Donation Amount ($)',
                data: data,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 206, 86, 0.8)',
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(153, 102, 255, 0.8)',
                    'rgba(255, 159, 64, 0.8)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((sum, current) => sum + current, 0);
                            const percentage = ((value / total) * 100).toFixed(2);
                            return `${label}: $${value.toFixed(2)} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
    </script>
</body>
</html>