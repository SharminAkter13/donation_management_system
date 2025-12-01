<?php
// Ensure this file is included via the placeholder.php to enforce access control
if (!isset($_SESSION['user_role'])) {
    header("Location: login.php");
    exit();
}

// Assume config.php and placeholder.php are in the same directory
include('config.php');

// Fetch summary data for the dashboard cards
$totalCampaigns = 0;
$activeCampaigns = 0;
$totalFunds = 0;
$totalDonors = 0;
$campaignsData = [];
$statusCounts = [];

if ($dms) {
    // Query for summary data
    $summary_sql = "SELECT COUNT(*) AS total, SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END) AS active, SUM(total_raised) AS funds FROM `campaigns`";
    $summary_result = $dms->query($summary_sql);
    if ($summary_result && $summary_result->num_rows > 0) {
        $summary = $summary_result->fetch_assoc();
        $totalCampaigns = $summary['total'];
        $activeCampaigns = $summary['active'];
        $totalFunds = $summary['funds'] ?? 0;
    }

    // Note: The total donors count requires a `donations` table that links donors to campaigns.
    // The following query is an example of how you would get that count assuming such a table exists.
    $donors_sql = "SELECT COUNT(DISTINCT donor_id) AS total_donors FROM `donations`";
    $donors_result = $dms->query($donors_sql);
    if ($donors_result && $donors_result->num_rows > 0) {
        $donors_row = $donors_result->fetch_assoc();
        $totalDonors = $donors_row['total_donors'] ?? 0;
    }

    // Query for chart data
    $chart_sql = "SELECT `name`, `total_raised`, `status` FROM `campaigns` ORDER BY `total_raised` DESC";
    $chart_result = $dms->query($chart_sql);
    if ($chart_result && $chart_result->num_rows > 0) {
        while ($row = $chart_result->fetch_assoc()) {
            $campaignsData[] = $row;
            // Count status for the pie chart
            $status = htmlspecialchars($row['status']);
            $statusCounts[$status] = isset($statusCounts[$status]) ? $statusCounts[$status] + 1 : 1;
        }
    }

    $dms->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Campaign Reports Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container-fluid mt-3 p-1">
        <div class="row">
            <div class="col-md-9 mx-auto">
                <h1 class="mb-4">Campaign Reports Dashboard</h1>

                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card text-bg-primary shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">Total Campaigns</h5>
                                <h2 class="card-text" id="totalCampaigns"><?php echo $totalCampaigns; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-bg-success shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">Active Campaigns</h5>
                                <h2 class="card-text" id="activeCampaigns"><?php echo $activeCampaigns; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-bg-warning shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">Funds Raised</h5>
                                <h2 class="card-text" id="fundsRaised">$<?php echo number_format($totalFunds); ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-bg-info shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">Total Donors</h5>
                                <h2 class="card-text" id="totalDonors"><?php echo $totalDonors; ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Funds Raised per Campaign</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="fundsChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Campaign Status Distribution</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="statusChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // PHP data to JavaScript
        const campaignsData = <?php echo json_encode($campaignsData); ?>;
        const statusCounts = <?php echo json_encode($statusCounts); ?>;

        // Funds Chart
        const campaignNames = campaignsData.map(c => c.name);
        const fundsRaised = campaignsData.map(c => c.total_raised);
        const fundsCtx = document.getElementById('fundsChart').getContext('2d');
        const fundsChart = new Chart(fundsCtx, {
            type: 'bar',
            data: {
                labels: campaignNames,
                datasets: [{
                    label: 'Funds Raised ($)',
                    data: fundsRaised,
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Funds Raised ($)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Campaigns'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += '$' + context.parsed.y.toLocaleString();
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });

        // Status Chart
        const statusLabels = Object.keys(statusCounts);
        const statusData = Object.values(statusCounts);
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        const statusChart = new Chart(statusCtx, {
            type: 'pie',
            data: {
                labels: statusLabels,
                datasets: [{
                    label: 'Campaign Status',
                    data: statusData,
                    backgroundColor: [
                        'rgba(40, 167, 69, 0.7)', // Active (green)
                        'rgba(108, 117, 125, 0.7)', // Completed (grey)
                        'rgba(255, 193, 7, 0.7)' // Pending (yellow)
                    ],
                    borderColor: '#fff',
                    borderWidth: 2
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
                                const total = context.dataset.data.reduce((acc, curr) => acc + curr, 0);
                                const percentage = ((value / total) * 100).toFixed(1) + '%';
                                return `${label}: ${value} (${percentage})`;
                            }
                        }
                    }
                }
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>