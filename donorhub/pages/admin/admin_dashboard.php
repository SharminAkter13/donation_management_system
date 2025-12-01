 <?php
    include('config.php'); // your db connection

    // --- Data Fetching for Dashboard Totals and Charts ---
    function getTotal($dms, $sql)
    {
        $res = $dms->query($sql);
        if ($res) {
            $row = $res->fetch_assoc();
            return $row['c'] ?? 0;
        } else {
            // Log or display the SQL error for debugging
            error_log("SQL Error: " . $dms->error);
            return 0;
        }
    }

    // Totals for the stats cards
    $totalUsers = getTotal($dms, "SELECT COUNT(*) as c FROM users");
    $totalVolunteers = getTotal($dms, "SELECT COUNT(*) as c FROM users u JOIN roles r ON u.role_id = r.id WHERE r.name = 'volunteer'");
    $totalBeneficiaries = getTotal($dms, "SELECT COUNT(*) as c FROM beneficiaries");
    $totalCampaigns = getTotal($dms, "SELECT COUNT(*) as c FROM campaigns");
    $totalEvents = getTotal($dms, "SELECT COUNT(*) as c FROM events");
    $totalDonations = $dms->query("SELECT SUM(amount) as s FROM donations")->fetch_assoc()['s'] ?? 0;
    $totalPledges = $dms->query("SELECT SUM(pledge_amount) as s FROM pledges")->fetch_assoc()['s'] ?? 0;

    // --- Data for Charts ---

    // 1. Donations Overview Chart: Total donations per month
    $donationData = array_fill(0, 12, 0); // Initialize with 12 months of zero data
    $sqlDonations = "SELECT SUM(amount) as total_donations, MONTH(date) as month FROM donations GROUP BY month ORDER BY month";
    $resultDonations = $dms->query($sqlDonations);
    if ($resultDonations) {
        while ($row = $resultDonations->fetch_assoc()) {
            // Note: `MONTH()` returns 1-12, so we subtract 1 for array index
            $donationData[$row['month'] - 1] = $row['total_donations'];
        }
    }

    // 2. Campaign Status Chart: Count of campaigns by status
    $campaignStatus = ['active' => 0, 'completed' => 0, 'upcoming' => 0];
    $sqlStatus = "SELECT status, COUNT(*) as count FROM campaigns GROUP BY status";
    $resultStatus = $dms->query($sqlStatus);
    if ($resultStatus) {
        while ($row = $resultStatus->fetch_assoc()) {
            $statusKey = strtolower($row['status']);
            if (array_key_exists($statusKey, $campaignStatus)) {
                $campaignStatus[$statusKey] = $row['count'];
            }
        }
    }

    // 3. Events Participation Chart: Count of donations per event
    // We will get event names and the number of donations tied to them.
    $eventLabels = [];
    $eventParticipation = [];
    $sqlEvents = "
        SELECT e.name, COUNT(d.id) AS donations_count
        FROM events e
        LEFT JOIN campaigns c ON c.event_id = e.id
        LEFT JOIN donations d ON d.campaign_id = c.id
        GROUP BY e.id
        ORDER BY donations_count DESC
        LIMIT 4
    ";
    $resultEvents = $dms->query($sqlEvents);
    if ($resultEvents) {
        while ($row = $resultEvents->fetch_assoc()) {
            $eventLabels[] = $row['name'];
            $eventParticipation[] = $row['donations_count'];
        }
    }

    // 4. Beneficiaries Served Chart: Count of beneficiaries per campaign
    // Assuming 'beneficiaries' table has a 'campaign_id' foreign key.
    $beneficiaryLabels = [];
    $beneficiariesServed = [];
    $sqlBeneficiaries = "
        SELECT c.name, COUNT(b.id) AS beneficiaries_count
    FROM campaigns c
    LEFT JOIN beneficiaries b ON c.id = b.id
    GROUP BY c.id
    ORDER BY beneficiaries_count DESC
    LIMIT 4
    ";
    $resultBeneficiaries = $dms->query($sqlBeneficiaries);
    if ($resultBeneficiaries) {
        while ($row = $resultBeneficiaries->fetch_assoc()) {
            $beneficiaryLabels[] = $row['name'];
            $beneficiariesServed[] = $row['beneficiaries_count'];
        }
    }

    // 5. Pledges vs Donations Chart: Totals are already calculated above
    $pledgeVsDonation = [
        'pledges' => $totalPledges,
        'donations' => $totalDonations
    ];

    // Close the database connection
    $dms->close();
    ?>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
 <div class="container-fluid p-5">
     <!-- Content Header (Page header) -->
     <section class="content-header">
         <div class="container-fluid">
             <div class="row mb-2">
                 <div class="col-sm-6">
                     <h1>Admin Dashboard</h1>
                 </div>
                 <div class="col-sm-6">
                     <ol class="breadcrumb float-sm-right">
                         <li class="breadcrumb-item"><a href="home.php">Home</a></li>
                         <li class="breadcrumb-item active">Admin Dashboard</li>
                     </ol>
                 </div>
             </div>
         </div><!-- /.container-fluid -->
     </section>

     <!-- Main content -->
     <section class="content">


         <!-- Default box -->
         <div class="card">
             <div class="card-header ">
                 <h3 class="card-title">Admin Dashboard</h3>

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
                 <div class="container-fluid p-4">

                     <div class="dashboard-header text-center mb-4">
                         <h2 class="fw-bold">Donation Management - Admin Dashboard</h2>
                         <p class="text-muted">Overview of Users, Volunteers, Beneficiaries, Campaigns, Events, Donations & Pledges</p>
                     </div>

                     <!-- Stats Cards -->
                     <div class="row g-4">
                         <div class="col-md-2 col-6">
                             <div class="card text-center p-3 bg-light"><i class="bi bi-people fs-2 text-primary"></i>
                                 <h6>Users</h6>
                                 <h4><?= $totalUsers ?></h4>
                             </div>
                         </div>
                         <div class="col-md-2 col-6">
                             <div class="card text-center p-3 bg-light"><i class="bi bi-person-heart fs-2 text-success"></i>
                                 <h6>Volunteers</h6>
                                 <h4><?= $totalVolunteers ?></h4>
                             </div>
                         </div>
                         <div class="col-md-2 col-6">
                             <div class="card text-center p-3 bg-light"><i class="bi bi-hand-thumbs-up fs-2 text-warning"></i>
                                 <h6>Beneficiaries</h6>
                                 <h4><?= $totalBeneficiaries ?></h4>
                             </div>
                         </div>
                         <div class="col-md-2 col-6">
                             <div class="card text-center p-3 bg-light"><i class="bi bi-megaphone fs-2 text-info"></i>
                                 <h6>Campaigns</h6>
                                 <h4><?= $totalCampaigns ?></h4>
                             </div>
                         </div>
                         <div class="col-md-2 col-6">
                             <div class="card text-center p-3 bg-light"><i class="bi bi-calendar-event fs-2 text-danger"></i>
                                 <h6>Events</h6>
                                 <h4><?= $totalEvents ?></h4>
                             </div>
                         </div>
                         <div class="col-md-2 col-6">
                             <div class="card text-center p-3 bg-light"><i class="bi bi-cash-coin fs-2 text-success"></i>
                                 <h6>Donations</h6>
                                 <h4>$<?= number_format($totalDonations, 2) ?></h4>
                             </div>
                         </div>
                         <div class="col-md-2 col-6">
                             <div class="card text-center p-3 bg-light"><i class="bi bi-journal-check fs-2 text-dark"></i>
                                 <h6>Pledges</h6>
                                 <h4>$<?= number_format($totalPledges, 2) ?></h4>
                             </div>
                         </div>
                     </div>

                     <!-- Charts -->
                     <div class="row mt-4 g-4">
                         <div class="col-md-6">
                             <div class="card p-3">
                                 <h6>Donations Overview</h6><canvas id="donationChart"></canvas>
                             </div>
                         </div>
                         <div class="col-md-6">
                             <div class="card p-3">
                                 <h6>Campaign Status</h6><canvas id="campaignChart"></canvas>
                             </div>
                         </div>
                         <div class="col-md-6">
                             <div class="card p-3">
                                 <h6>Events Participation</h6><canvas id="eventChart"></canvas>
                             </div>
                         </div>
                         <div class="col-md-6">
                             <div class="card p-3">
                                 <h6>Beneficiaries Served</h6><canvas id="beneficiaryChart"></canvas>
                             </div>
                         </div>
                         <div class="col-md-6">
                             <div class="card p-3">
                                 <h6>Pledges vs Donations</h6><canvas id="pledgeChart"></canvas>
                             </div>
                         </div>
                     </div>

                     <!-- Overview Table -->
                     <div class="row mt-5">
                         <div class="col-12">
                             <div class="card p-3">
                                 <h5 class="mb-3">System Overview</h5>
                                 <table class="table table-bordered table-striped text-center">
                                     <thead class="table-dark">
                                         <tr>
                                             <th>Users</th>
                                             <th>Volunteers</th>
                                             <th>Beneficiaries</th>
                                             <th>Campaigns</th>
                                             <th>Events</th>
                                             <th>Donations ($)</th>
                                             <th>Pledges ($)</th>
                                         </tr>
                                     </thead>
                                     <tbody>
                                         <tr>
                                             <td><?= $totalUsers ?></td>
                                             <td><?= $totalVolunteers ?></td>
                                             <td><?= $totalBeneficiaries ?></td>
                                             <td><?= $totalCampaigns ?></td>
                                             <td><?= $totalEvents ?></td>
                                             <td><?= number_format($totalDonations, 2) ?></td>
                                             <td><?= number_format($totalPledges, 2) ?></td>
                                         </tr>
                                     </tbody>
                                 </table>
                             </div>
                         </div>
                     </div>

                 </div>

                 <script>
                     // Use a PHP array to create the month labels for the donations chart
                     const monthLabels = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

                     // Donations line chart
                     new Chart(document.getElementById('donationChart'), {
                         type: 'line',
                         data: {
                             labels: monthLabels,
                             datasets: [{
                                 label: 'Donations ($)',
                                 data: <?= json_encode(array_values($donationData)) ?>,
                                 borderColor: 'blue',
                                 backgroundColor: 'rgba(54,162,235,0.2)',
                                 fill: true,
                                 tension: 0.3
                             }]
                         }
                     });

                     // Campaign status
                     new Chart(document.getElementById('campaignChart'), {
                         type: 'doughnut',
                         data: {
                             labels: ['Active', 'Completed', 'Upcoming'],
                             datasets: [{
                                 data: [<?= $campaignStatus['active'] ?>, <?= $campaignStatus['completed'] ?>, <?= $campaignStatus['upcoming'] ?>],
                                 backgroundColor: ['#0d6efd', '#198754', '#ffc107']
                             }]
                         }
                     });

                     // Event participation
                     new Chart(document.getElementById('eventChart'), {
                         type: 'bar',
                         data: {
                             labels: <?= json_encode($eventLabels) ?>,
                             datasets: [{
                                 label: 'Donations',
                                 data: <?= json_encode($eventParticipation) ?>,
                                 backgroundColor: 'rgba(255,99,132,0.7)'
                             }]
                         }
                     });

                     // Beneficiaries served
                     new Chart(document.getElementById('beneficiaryChart'), {
                         type: 'bar',
                         data: {
                             labels: <?= json_encode($beneficiaryLabels) ?>,
                             datasets: [{
                                 label: 'Beneficiaries',
                                 data: <?= json_encode($beneficiariesServed) ?>,
                                 backgroundColor: 'rgba(75,192,192,0.7)'
                             }]
                         }
                     });

                     // Pledges vs Donations
                     new Chart(document.getElementById('pledgeChart'), {
                         type: 'pie',
                         data: {
                             labels: ['Pledges', 'Donations'],
                             datasets: [{
                                 data: [<?= $pledgeVsDonation['pledges'] ?>, <?= $pledgeVsDonation['donations'] ?>],
                                 backgroundColor: ['#6c757d', '#28a745']
                             }]
                         }
                     });
                 </script>
             </div>
             <!-- /.card-body -->

         </div>
         <!-- /.card -->

     </section>
     <!-- /.content -->
 </div>
 <!-- ./Add users -->