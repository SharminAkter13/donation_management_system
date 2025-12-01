<?php
// Include your database configuration file
include('config.php');

// Replace with your actual credentials if they are not in config.php
$dms = new mysqli('localhost', 'root', '', 'donation_management_system');

if ($dms->connect_error) {
    die("Connection failed: " . $dms->connect_error);
}

// ------------------------------------------------------------------------------------------------
// PHP Logic to fetch data for summary cards and chart
// ------------------------------------------------------------------------------------------------
// Total Pledged Amount
$total_pledged = 0;
$result = $dms->query("SELECT SUM(pledge_amount) AS total FROM pledges");
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $total_pledged = $row['total'] ?? 0;
}

// Total Fulfilled Amount
// Corrected to use the 'amount' column from the 'donations' table
$total_fulfilled = 0;
$result = $dms->query("SELECT SUM(amount) AS total FROM donations");
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $total_fulfilled = $row['total'] ?? 0;
}

// Outstanding Pledges
$outstanding_pledges = $total_pledged - $total_fulfilled;

// Number of Active Pledges
$active_pledges = 0;
$result = $dms->query("SELECT COUNT(*) AS total FROM pledges WHERE status IN ('Partially Fulfilled', 'Overdue')");
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $active_pledges = $row['total'] ?? 0;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pledge Tracking Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        .card-header {
            font-weight: bold;
        }

        .status-badge {
            font-weight: bold;
        }

        .status-fulfilled {
            background-color: #28a745;
            color: white;
        }

        .status-partially {
            background-color: #ffc107;
            color: black;
        }

        .status-overdue {
            background-color: #dc3545;
            color: white;
        }

        /* Adjust the chart container to limit its height and make it responsive */
        .chart-container {
            width: 100%;
            height: 300px;
            position: relative;
        }

        canvas {
            width: 100% !important;
            height: 100% !important;
        }
    </style>
</head>

<body>
    <div class="container-fluid p-5" style="min-height: 2838.44px;">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Pledges Interface</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="home.php">Home</a></li>
                            <li class="breadcrumb-item active">Pledges</li>
                        </ol>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>

        <!-- Main content -->
        <section class="content">
            <!-- Default box -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Pledges Summary</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                        <button type="button" class="btn btn-tool" data-card-widget="remove" title="Remove">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="container-fluid py-4">
                        <h1 class="mb-4">Pledge Tracking Dashboard</h1>
                        <div class="row g-4 mb-4">
                            <div class="col-md-3">
                                <div class="card text-white bg-primary h-100">
                                    <div class="card-body">
                                        <h5 class="card-title">Total Pledged Amount</h5>
                                        <h2 class="card-text">$<?php echo number_format($total_pledged, 2); ?></h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card text-white bg-success h-100">
                                    <div class="card-body">
                                        <h5 class="card-title">Total Fulfilled Amount</h5>
                                        <h2 class="card-text">$<?php echo number_format($total_fulfilled, 2); ?></h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card text-white bg-warning h-100">
                                    <div class="card-body">
                                        <h5 class="card-title">Outstanding Pledges</h5>
                                        <h2 class="card-text">$<?php echo number_format($outstanding_pledges, 2); ?></h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card text-white bg-info h-100">
                                    <div class="card-body">
                                        <h5 class="card-title">Number of Active Pledges</h5>
                                        <h2 class="card-text"><?php echo $active_pledges; ?></h2>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card shadow-sm">
                            <div class="card-header">
                                <span>Pledge Analytics Chart</span>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="pledgeChart"></canvas>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->
        </section>
        <!-- /.content -->
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('pledgeChart').getContext('2d');
            const pledgeChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Total Pledged', 'Total Fulfilled', 'Outstanding'],
                    datasets: [{
                        label: 'Amount (USD)',
                        data: [
                            <?php echo json_encode($total_pledged); ?>,
                            <?php echo json_encode($total_fulfilled); ?>,
                            <?php echo json_encode($outstanding_pledges); ?>
                        ],
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.5)', // Blue for Pledged
                            'rgba(75, 192, 192, 0.5)', // Green for Fulfilled
                            'rgba(255, 206, 86, 0.5)'  // Yellow for Outstanding
                        ],
                        borderColor: [
                            'rgba(54, 162, 235, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(255, 206, 86, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false, // Ensure chart scales properly
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '$' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
</body>

</html>

<?php
// Close the database connection at the end of the script
mysqli_close($dms);
?>
