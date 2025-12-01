<?php
// Include your database connection
include('config.php');

// --- Total Users ---
$sql_total = "SELECT COUNT(*) FROM users";
$result_total = $dms->query($sql_total);
$total_users = 0;
if ($result_total) {
    list($total_users) = $result_total->fetch_row();
    $result_total->close();
}

// --- Users by Role ---
$sql_roles = "SELECT r.name AS role, COUNT(u.id) AS count
              FROM roles AS r
              LEFT JOIN users AS u ON r.id = u.role_id
              GROUP BY r.name";

$result_roles = $dms->query($sql_roles);
$role_data = [];
$labels = [];
$counts = [];

// Chart Colors (extendable)
$background_colors = [
    'rgba(255, 99, 132, 0.7)',
    'rgba(54, 162, 235, 0.7)',
    'rgba(255, 206, 86, 0.7)',
    'rgba(75, 192, 192, 0.7)',
    'rgba(153, 102, 255, 0.7)',
    'rgba(255, 159, 64, 0.7)'
];
$border_colors = [
    'rgba(255, 99, 132, 1)',
    'rgba(54, 162, 235, 1)',
    'rgba(255, 206, 86, 1)',
    'rgba(75, 192, 192, 1)',
    'rgba(153, 102, 255, 1)',
    'rgba(255, 159, 64, 1)'
];

if ($result_roles) {
    while ($row = $result_roles->fetch_assoc()) {
        $role_data[] = $row;
        $labels[] = ucwords($row['role']);
        $counts[] = $row['count'];
    }
    $result_roles->close();
}

// Close connection
$dms->close();

// JSON for Chart.js
$js_labels = json_encode($labels);
$js_counts = json_encode($counts);
$js_bg_colors = json_encode(array_slice($background_colors, 0, count($labels)));
$js_border_colors = json_encode(array_slice($border_colors, 0, count($labels)));
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Dashboard</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/css/adminlte.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<style>
    body { background-color: #f4f6f9; }
    .content-wrapper { padding: 20px; }
</style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">

    <div class="container-fluid p-5">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">User Dashboard</h1>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <!-- Total Users Box -->
                <div class="row">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3><?= htmlspecialchars($total_users) ?></h3>
                                <p>Total Users</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Chart Card -->
                    <div class="col-lg-6">
                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h3 class="card-title">User Distribution by Role</h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="chart-container" style="position: relative; height:350px;">
                                    <canvas id="userRoleChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Table Card -->
                    <div class="col-lg-6">
                        <div class="card card-info card-outline">
                            <div class="card-header">
                                <h3 class="card-title">User Counts by Role</h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Role</th>
                                            <th>Total Users</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($role_data as $data) : ?>
                                            <tr>
                                                <td><?= htmlspecialchars($data['role']) ?></td>
                                                <td><?= htmlspecialchars($data['count']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>
    </div>


<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/js/adminlte.min.js"></script>

<script>
const roleLabels = <?= $js_labels ?>;
const roleCounts = <?= $js_counts ?>;
const backgroundColors = <?= $js_bg_colors ?>;
const borderColors = <?= $js_border_colors ?>;

const ctx = document.getElementById('userRoleChart').getContext('2d');

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: roleLabels,
        datasets: [{
            label: 'Number of Users',
            data: roleCounts,
            backgroundColor: backgroundColors,
            borderColor: borderColors,
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});
</script>
</body>
</html>
