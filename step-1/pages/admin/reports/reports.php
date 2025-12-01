<?php
// Start the session to access session variables and ensure a user is logged in
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Include the database configuration file
include('config.php');

// Define the title of the page for the browser and for the report header
$pageTitle = "Comprehensive Donation Reports";

// Initialize data arrays and variables
$error_message = '';
$totalDonations = 0;
$totalDonors = 0;
$totalCampaigns = 0;
$totalCollectedAmount = 0;
$totalGoalAmount = 0;
$totalVolunteers = 0;
$totalEvents = 0;
$totalPledges = 0;
$highestDonorName = "N/A";
$highestDonorAmount = 0;

$campaignData = [];
$topDonors = [];

// === SQL Queries to Fetch All Report Data ===

// 1. Total Donations
$totalDonationsQuery = "SELECT COUNT(id) AS total_donations, SUM(amount) AS total_amount FROM donations";
$result = mysqli_query($dms, $totalDonationsQuery);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $totalDonations = $row['total_donations'] ?? 0;
    $totalCollectedAmount = $row['total_amount'] ?? 0;
} else {
    $error_message .= "Error fetching total donations: " . mysqli_error($dms) . "<br>";
}

// 2. Total Donors
$totalDonorsQuery = "SELECT COUNT(id) AS total_donors FROM donors";
$result = mysqli_query($dms, $totalDonorsQuery);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $totalDonors = $row['total_donors'] ?? 0;
} else {
    $error_message .= "Error fetching total donors: " . mysqli_error($dms) . "<br>";
}

// 3. Total Campaigns and Total Goal Amount
$totalCampaignsQuery = "SELECT COUNT(id) AS total_campaigns, SUM(goal_amount) AS total_goal FROM campaigns";
$result = mysqli_query($dms, $totalCampaignsQuery);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $totalCampaigns = $row['total_campaigns'] ?? 0;
    $totalGoalAmount = $row['total_goal'] ?? 0;
} else {
    $error_message .= "Error fetching total campaigns: " . mysqli_error($dms) . "<br>";
}

// 4. Total Volunteers
$totalVolunteersQuery = "SELECT COUNT(id) AS total_volunteers FROM volunteer";
$result = mysqli_query($dms, $totalVolunteersQuery);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $totalVolunteers = $row['total_volunteers'] ?? 0;
} else {
    $error_message .= "Error fetching total volunteers: " . mysqli_error($dms) . "<br>";
}

// 5. Total Events
$totalEventsQuery = "SELECT COUNT(id) AS total_events FROM events";
$result = mysqli_query($dms, $totalEventsQuery);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $totalEvents = $row['total_events'] ?? 0;
} else {
    $error_message .= "Error fetching total events: " . mysqli_error($dms) . "<br>";
}

// 6. Total Pledges
$totalPledgesQuery = "SELECT COUNT(id) AS total_pledges, SUM(pledge_amount) AS total_pledges_amount FROM pledges";
$result = mysqli_query($dms, $totalPledgesQuery);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $totalPledges = $row['total_pledges'] ?? 0;
} else {
    $error_message .= "Error fetching total pledges: " . mysqli_error($dms) . "<br>";
}

// 7. Data for Campaign Chart and Table (Total collected vs. goal)
$campaignDataQuery = "SELECT
    c.name,
    c.goal_amount,
    COALESCE(SUM(d.amount), 0) AS total_amount_raised
FROM campaigns c
LEFT JOIN donations d ON c.id = d.campaign_id
GROUP BY c.id
ORDER BY total_amount_raised DESC";

$campaignDataResult = mysqli_query($dms, $campaignDataQuery);
if ($campaignDataResult) {
    while ($row = mysqli_fetch_assoc($campaignDataResult)) {
        $campaignData[] = $row;
    }
} else {
    $error_message .= "Error fetching campaign data: " . mysqli_error($dms) . "<br>";
}

// 8. Highest Donor
$highestDonorQuery = "SELECT
    d.name,
    SUM(don.amount) AS total_donated
FROM donors d
JOIN donations don ON d.id = don.donor_id
GROUP BY d.id
ORDER BY total_donated DESC
LIMIT 1";

$highestDonorResult = mysqli_query($dms, $highestDonorQuery);
if ($highestDonorResult) {
    $row = mysqli_fetch_assoc($highestDonorResult);
    if ($row) {
        $highestDonorName = $row['name'];
        $highestDonorAmount = $row['total_donated'];
    }
} else {
    $error_message .= "Error fetching highest donor: " . mysqli_error($dms) . "<br>";
}

// Calculate percentage raised
$percentageRaised = ($totalGoalAmount > 0) ? round(($totalCollectedAmount / $totalGoalAmount) * 100, 2) : 0;

// 9. Donor Donation Details
$donorDetails = [];
$donorDetailsQuery = "SELECT 
        donors.name AS donor_name,
        donors.contact AS donor_contact,
        donations.amount,
        donations.date,
        pm.type AS payment_method,
        COALESCE(c.name, p.name) AS campaign_pledge
    FROM donations
    LEFT JOIN donors ON donations.donor_id = donors.id
    LEFT JOIN payment_methods pm ON donations.payment_id = pm.id
    LEFT JOIN campaigns c ON donations.campaign_id = c.id
    LEFT JOIN pledges p ON donations.pledge_id = p.id
    ORDER BY donations.date DESC";

$donorDetailsResult = mysqli_query($dms, $donorDetailsQuery);
if ($donorDetailsResult) {
    while ($row = mysqli_fetch_assoc($donorDetailsResult)) {
        $donorDetails[] = $row;
    }
} else {
    $error_message .= "Error fetching donor details: " . mysqli_error($dms) . "<br>";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Include Chart.js for charts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background-color: #f8f9fa; }
        .report-container {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .report-header {
            border-bottom: 2px solid #343a40;
            padding-bottom: 10px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .report-section {
            margin-bottom: 40px;
        }
        .report-section h4 {
            border-left: 4px solid #007bff;
            padding-left: 10px;
            margin-bottom: 20px;
        }
        @media print {
            body { background-color: #fff; }
            .no-print { display: none !important; }
            .report-container { box-shadow: none; padding: 0; }
        }
        .card-icon {
            font-size: 3rem;
        }
    </style>
</head>
<body>

<div class="container my-5 report-container">
    <div class="report-header no-print">
        <h2><i class="fas fa-chart-line me-2"></i><?= htmlspecialchars($pageTitle) ?></h2>
        <button class="btn btn-primary" onclick="window.print()"><i class="fas fa-print me-2"></i>Print Report</button>
    </div>

    <div class="report-header print-only" style="display:none;">
        <h1>Donation Management Report</h1>
        <p>Generated on: <?= date("F j, Y, g:i a") ?></p>
    </div>

    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger">
            <strong>Database Error:</strong> <?= $error_message ?>
        </div>
    <?php endif; ?>

    <!-- Summary Cards Section -->
    <div class="report-section">
        <h4><i class="fas fa-tachometer-alt me-2"></i>Key Metrics</h4>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Total Donations</h5>
                            <h1 class="card-text fw-bold">$<?= number_format($totalCollectedAmount, 2) ?></h1>
                        </div>
                        <i class="fas fa-hand-holding-usd card-icon opacity-50"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Total Donors</h5>
                            <h1 class="card-text fw-bold"><?= $totalDonors ?></h1>
                        </div>
                        <i class="fas fa-users card-icon opacity-50"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-warning text-white">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Total Campaigns</h5>
                            <h1 class="card-text fw-bold"><?= $totalCampaigns ?></h1>
                        </div>
                        <i class="fas fa-bullhorn card-icon opacity-50"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-info text-white">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Total Events</h5>
                            <h1 class="card-text fw-bold"><?= $totalEvents ?></h1>
                        </div>
                        <i class="fas fa-calendar-alt card-icon opacity-50"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-dark text-white">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Total Volunteers</h5>
                            <h1 class="card-text fw-bold"><?= $totalVolunteers ?></h1>
                        </div>
                        <i class="fas fa-handshake card-icon opacity-50"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-secondary text-white">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Total Pledges</h5>
                            <h1 class="card-text fw-bold"><?= $totalPledges ?></h1>
                        </div>
                        <i class="fas fa-file-invoice-dollar card-icon opacity-50"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-light">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Raised vs. Goal</h5>
                            <h1 class="card-text fw-bold"><?= $percentageRaised ?>%</h1>
                            <p class="text-muted">of total goal amount</p>
                        </div>
                        <i class="fas fa-percentage card-icon opacity-50"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-light">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Highest Donor</h5>
                            <h1 class="card-text fw-bold">$<?= number_format($highestDonorAmount, 2) ?></h1>
                            <p class="text-muted">By: <?= htmlspecialchars($highestDonorName) ?></p>
                        </div>
                        <i class="fas fa-crown card-icon opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="report-section">
        <h4><i class="fas fa-chart-pie me-2"></i>Data Visualization</h4>
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title text-center">Campaign Progress (Collected vs. Goal)</h5>
                        <canvas id="campaignBarChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title text-center">Donation Breakdown by Campaign</h5>
                        <canvas id="campaignPieChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Campaign Details Table -->
    <div class="report-section">
        <h4><i class="fas fa-table me-2"></i>Campaign Details</h4>
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Campaign Name</th>
                    <th>Goal Amount</th>
                    <th>Collected Amount</th>
                    <th>Progress</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($campaignData as $campaign): ?>
                    <tr>
                        <td><?= htmlspecialchars($campaign['name']) ?></td>
                        <td>$<?= number_format($campaign['goal_amount'], 2) ?></td>
                        <td>$<?= number_format($campaign['total_amount_raised'], 2) ?></td>
                        <td>
                            <?php
                            $progress = ($campaign['goal_amount'] > 0) ? round(($campaign['total_amount_raised'] / $campaign['goal_amount']) * 100) : 0;
                            echo '<div class="progress" style="height: 25px;">';
                            echo '<div class="progress-bar bg-success" role="progressbar" style="width: ' . $progress . '%;" aria-valuenow="' . $progress . '" aria-valuemin="0" aria-valuemax="100">' . $progress . '%</div>';
                            echo '</div>';
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<!-- Donor Donation Details Table -->
<div class="report-section p-4">
    <h4><i class="fas fa-hand-holding-usd me-2"></i>Donor Donation Details</h4>
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Donor Name</th>
                <th>Contact</th>
                <th>Campaign/Pledge</th>
                <th>Payment Method</th>
                <th>Donation Amount</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($donorDetails)): ?>
                <?php foreach ($donorDetails as $donor): ?>
                    <tr>
                        <td><?= htmlspecialchars($donor['donor_name']) ?></td>
                        <td><?= htmlspecialchars($donor['donor_contact'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($donor['campaign_pledge'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($donor['payment_method'] ?? 'N/A') ?></td>
                        <td>$<?= number_format($donor['amount'], 2) ?></td>
                        <td><?= date("F j, Y, g:i a", strtotime($donor['date'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6" class="text-center">No donations found</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // PHP data to be used in JavaScript charts
    const campaignLabels = <?= json_encode(array_column($campaignData, 'name')) ?>;
    const campaignRaised = <?= json_encode(array_column($campaignData, 'total_amount_raised')) ?>;
    const campaignGoals = <?= json_encode(array_column($campaignData, 'goal_amount')) ?>;

    // Bar Chart for Campaign Progress
    const barCtx = document.getElementById('campaignBarChart').getContext('2d');
    new Chart(barCtx, {
        type: 'bar',
        data: {
            labels: campaignLabels,
            datasets: [{
                label: 'Collected Amount ($)',
                data: campaignRaised,
                backgroundColor: 'rgba(75, 192, 192, 0.6)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }, {
                label: 'Goal Amount ($)',
                data: campaignGoals,
                backgroundColor: 'rgba(255, 99, 132, 0.6)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Pie Chart for Donation Breakdown
    const pieCtx = document.getElementById('campaignPieChart').getContext('2d');
    new Chart(pieCtx, {
        type: 'pie',
        data: {
            labels: campaignLabels,
            datasets: [{
                data: campaignRaised,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 206, 86, 0.8)',
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(153, 102, 255, 0.8)',
                    'rgba(255, 159, 64, 0.8)'
                ],
                hoverOffset: 4
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
                        label: function(tooltipItem) {
                            const total = tooltipItem.dataset.data.reduce((sum, val) => sum + val, 0);
                            const currentValue = tooltipItem.raw;
                            const percentage = Math.round((currentValue / total) * 100);
                            return `${tooltipItem.label}: $${currentValue.toLocaleString()} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
</script>

</body>
</html>
