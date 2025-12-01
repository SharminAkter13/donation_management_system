<?php
// === FILE: event_donation_dashboard_bs5_stacked.php ===
// This file requires a 'config.php' file that establishes a database connection
// and stores the connection object in a variable named $dms.
//
// This version of the code has been updated to use a stacked area chart,
// which requires at least two data series. For demonstration, we've added
// a second, simulated "Other Donations" series.
//
// === Database Connection ===
include('config.php');

// Initialize variables
$total_events = 0;
$total_donations_sum = 0;
$top_event_name = 'N/A';
$top_donation_amount = 0;

// Fetch all events and their total donations
$sql = "
SELECT e.name AS event_name, IFNULL(SUM(d.amount), 0) AS total_donations
FROM events e
LEFT JOIN donations d ON d.campaign_id = e.id
GROUP BY e.id
ORDER BY total_donations DESC
";
$result = $dms->query($sql);

$event_names = [];
$donation_totals = [];
$other_donations = []; // Second data series for the stacked chart

if ($result) {
    // Determine the number of events
    $total_events = $result->num_rows;

    // Loop through results to populate arrays for the chart and calculate totals
    while ($row = $result->fetch_assoc()) {
        $event_names[] = $row['event_name'];
        $donation_totals[] = $row['total_donations'];
        
        // Sum all donations for the overall total
        $total_donations_sum += $row['total_donations'];
        
        // Find the top event by donation amount
        if ($top_donation_amount < $row['total_donations']) {
            $top_donation_amount = $row['total_donations'];
            $top_event_name = $row['event_name'];
        }

        // Generate a simulated value for the second series
        $other_donations[] = round($row['total_donations'] * (rand(1, 5) / 10)); // Example: 10%-50% of the main donation
    }
}

// Re-order the arrays for the chart to be alphabetical on the x-axis
if (!empty($event_names)) {
    array_multisort($event_names, SORT_ASC, $donation_totals, $other_donations);
}

// Close the database connection
$dms->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Donation Dashboard</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #f8f9fa; /* A light gray background color */
        }
    </style>
</head>
<body class="p-4">

<div class="container-fluid p-5">
    <div class="mb-5 text-center text-md-start">
        <h1 class="fw-bold text-dark">Donation Dashboard</h1>
        <p class="lead text-muted mt-2">A visual overview of event donation performance.</p>
    </div>

    <!-- Key Metrics Section -->
    <div class="row row-cols-1 row-cols-md-3 g-4 mb-5">
        <!-- Total Events Card -->
        <div class="col">
            <div class="card shadow-sm border-1 rounded-4">
                <div class="card-body d-flex align-items-center justify-content-between p-4">
                    <div>
                        <p class="text-muted fw-bold mb-1">Total Events</p>
                        <p class="h3 fw-bold mb-0 text-dark"><?= htmlspecialchars($total_events) ?></p>
                    </div>
                    <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                        <i class="fas fa-calendar-check fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Donations Card -->
        <div class="col">
            <div class="card shadow-sm border-1 rounded-4">
                <div class="card-body d-flex align-items-center justify-content-between p-4">
                    <div>
                        <p class="text-muted fw-bold mb-1">Total Donations</p>
                        <p class="h3 fw-bold mb-0 text-dark">$<?= number_format($total_donations_sum, 2) ?></p>
                    </div>
                    <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle">
                        <i class="fas fa-hand-holding-heart fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Event Card -->
        <div class="col">
            <div class="card shadow-sm border-1 rounded-4">
                <div class="card-body d-flex align-items-center justify-content-between p-4">
                    <div>
                        <p class="text-muted fw-bold mb-1">Top Event</p>
                        <p class="h4 fw-bold mb-0 text-dark"><?= htmlspecialchars($top_event_name) ?></p>
                        <p class="text-muted small mt-1 fw-bold">Amount: $<?= number_format($top_donation_amount, 2) ?></p>
                    </div>
                    <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-circle">
                        <i class="fas fa-trophy fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Section -->
    <div class="card shadow-sm border-1 rounded-4">
        <div class="card-header bg-white rounded-4 p-4 pb-0">
            <h3 class="card-title fw-bold text-dark mb-0">Donations per Event</h3>
        </div>
        <div class="card-body p-4">
            <canvas id="donationChart" style="height: 320px;"></canvas>
        </div>
    </div>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const ctx = document.getElementById('donationChart').getContext('2d');
    const donationChart = new Chart(ctx, {
        type: 'line', // Changed to 'line'
        data: {
            labels: <?= json_encode($event_names) ?>,
            datasets: [{
                label: 'Event Donations',
                data: <?= json_encode($donation_totals) ?>,
                backgroundColor: 'rgba(13, 110, 253, 0.5)', // Bootstrap primary color with transparency
                borderColor: 'rgba(13, 110, 253, 1)',
                fill: true, // Enable fill for area chart
                tension: 0.4, // Smooth curves
            }, {
                label: 'Other Donations',
                data: <?= json_encode($other_donations) ?>,
                backgroundColor: 'rgba(108, 117, 125, 0.5)', // Bootstrap secondary color with transparency
                borderColor: 'rgba(108, 117, 125, 1)',
                fill: true, // Enable fill for area chart
                tension: 0.4, // Smooth curves
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: true },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(context.parsed.y);
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    stacked: true, // Key property for stacked chart
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value;
                        }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
</script>
</body>
</html>
