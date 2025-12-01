<?php
// Include your database configuration file
include('config.php');

// Replace with your actual credentials if they are not in config.php
$dms = new mysqli('localhost', 'root', '', 'donation_management_system');

if ($dms->connect_error) {
    die("Connection failed: " . $dms->connect_error);
}

// ------------------------------------------------------------------------------------------------
// PHP Logic to fetch data for summary cards
// ------------------------------------------------------------------------------------------------
// Total Volunteers
$total_volunteers = 0;
$result = $dms->query("SELECT COUNT(*) AS total FROM volunteer");
if ($result) {
    $row = $result->fetch_assoc();
    $total_volunteers = $row['total'];
}

// Active Volunteers (assuming 'Available' and 'Assigned' are active statuses)
$active_volunteers = 0;
$result = $dms->query("SELECT COUNT(*) AS total FROM volunteer WHERE availability_status IN ('Available', 'Assigned')");
if ($result) {
    $row = $result->fetch_assoc();
    $active_volunteers = $row['total'];
}

// Completed Tasks (this metric is not in your previous table, so a placeholder query is used)
$completed_tasks = 0;
// Example if you add a 'Completed' status: $result = $dms->query("SELECT COUNT(*) AS total FROM volunteer WHERE availability_status = 'Completed'");

// Events with Volunteers
$events_with_volunteers = 0;
$result = $dms->query("SELECT COUNT(DISTINCT event_id) AS total FROM volunteer WHERE event_id IS NOT NULL");
if ($result) {
    $row = $result->fetch_assoc();
    $events_with_volunteers = $row['total'];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteer Tracking Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body {
            background-color: #f4f6f9;
        }
        .card-header {
            font-weight: bold;
        }
        .status-badge {
            font-weight: bold;
            padding: 0.35em 0.65em;
            border-radius: 0.25rem;
            display: inline-block;
        }
        .status-available { background-color: #28a745; color: white; }
        .status-assigned { background-color: #ffc107; color: black; }
        .status-unavailable { background-color: #dc3545; color: white; }
    </style>
</head>
<body>
    <div class="container-fluid p-5" style="min-height: 2838.44px;">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Volunteers Interface</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="home.php">Home</a></li>
              <li class="breadcrumb-item active">Manage Volunteer</li>
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
          <h3 class="card-title">Manage Volunteer</h3>

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
    <h1 class="mb-4">Volunteer Tracking Dashboard</h1>

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary h-100">
                <div class="card-body">
                    <h5 class="card-title">Total Volunteers</h5>
                    <h2 class="card-text"><?php echo $total_volunteers; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success h-100">
                <div class="card-body">
                    <h5 class="card-title">Active Volunteers</h5>
                    <h2 class="card-text"><?php echo $active_volunteers; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning h-100">
                <div class="card-body">
                    <h5 class="card-title">Completed Tasks</h5>
                    <h2 class="card-text"><?php echo $completed_tasks; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info h-100">
                <div class="card-body">
                    <h5 class="card-title">Events with Volunteers</h5>
                    <h2 class="card-text"><?php echo $events_with_volunteers; ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Volunteer List</span>
            <input type="text" class="form-control w-25" placeholder="Search volunteers..." id="volunteerSearch">
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="volunteerTable">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Volunteer Name</th>
                            <th scope="col">Availability Status</th>
                            <th scope="col">Assigned Event</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Query to fetch all volunteers with their assigned event and user names
                        $query = "SELECT 
                                    vs.id, vs.name, vs.contact, vs.task, vs.availability_status, 
                                    e.name AS event_name, 
                                    CONCAT(u.first_name, ' ', u.last_name) AS user_name 
                                  FROM 
                                    volunteer vs
                                  LEFT JOIN 
                                    events e ON vs.event_id = e.id
                                  LEFT JOIN
                                    users u ON vs.user_id = u.id
                                  ORDER BY vs.id DESC";

                        $volunteers_result = $dms->query($query);
                        if ($volunteers_result) {
                            while ($row = $volunteers_result->fetch_assoc()) {
                                // Sanitize data for HTML output
                                $id = htmlspecialchars($row['id']);
                                $name = htmlspecialchars($row['name']);
                                $contact = htmlspecialchars($row['contact']);
                                $task = htmlspecialchars($row['task']);
                                $availability_status = htmlspecialchars($row['availability_status']);
                                $event_name = htmlspecialchars($row['event_name'] ?? 'N/A');
                                $user_name = htmlspecialchars($row['user_name'] ?? 'N/A');

                                // Determine badge class based on status
                                $status_class = '';
                                switch ($availability_status) {
                                    case 'Available':
                                        $status_class = 'status-available';
                                        break;
                                    case 'Assigned':
                                        $status_class = 'status-assigned';
                                        break;
                                    case 'Unavailable':
                                        $status_class = 'status-unavailable';
                                        break;
                                }

                                echo "<tr>";
                                echo "<td>#V{$id}</td>";
                                echo "<td>{$name}</td>";
                                echo "<td><span class='status-badge {$status_class}'>{$availability_status}</span></td>";
                                echo "<td>{$event_name}</td>";
                                echo "<td>
                                        <button class='btn btn-sm btn-primary view-details' data-bs-toggle='modal' data-bs-target='#volunteerDetailModal'
                                                data-id='{$id}'
                                                data-name='{$name}'
                                                data-contact='{$contact}'
                                                data-task='{$task}'
                                                data-availability='{$availability_status}'
                                                data-eventname='{$event_name}'
                                                data-username='{$user_name}'>
                                            View Details
                                        </button>
                                      </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5' class='text-center'>No volunteers found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="volunteerDetailModal" tabindex="-1" aria-labelledby="volunteerDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="volunteerDetailModalLabel">Volunteer Details: <span id="volunteerId"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Volunteer Information</h6>
                            <p><strong>Name:</strong> <span id="volunteerName"></span></p>
                            <p><strong>Contact:</strong> <span id="volunteerContact"></span></p>
                            <p><strong>Task:</strong> <span id="volunteerTask"></span></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Assignment Details</h6>
                            <p><strong>Availability Status:</strong> <span class="badge" id="volunteerStatus"></span></p>
                            <p><strong>Assigned Event:</strong> <span id="volunteerEvent"></span></p>
                            <p><strong>User (Who Assigned):</strong> <span id="volunteerUser"></span></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
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
  <!-- ./Add users -->


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const volunteerDetailModal = document.getElementById('volunteerDetailModal');
        volunteerDetailModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            
            // Get data from data-* attributes on the button
            const id = button.getAttribute('data-id');
            const name = button.getAttribute('data-name');
            const contact = button.getAttribute('data-contact');
            const task = button.getAttribute('data-task');
            const availability = button.getAttribute('data-availability');
            const eventName = button.getAttribute('data-eventname');
            const userName = button.getAttribute('data-username');

            // Populate the modal's content
            document.getElementById('volunteerId').textContent = '#' + id;
            document.getElementById('volunteerName').textContent = name;
            document.getElementById('volunteerContact').textContent = contact;
            document.getElementById('volunteerTask').textContent = task;
            
            const statusBadge = document.getElementById('volunteerStatus');
            statusBadge.textContent = availability;
            
            // Set status class dynamically
            let statusClass = '';
            switch (availability) {
                case 'Available':
                    statusClass = 'status-available';
                    break;
                case 'Assigned':
                    statusClass = 'status-assigned';
                    break;
                case 'Unavailable':
                    statusClass = 'status-unavailable';
                    break;
            }
            statusBadge.className = `status-badge ${statusClass}`;
            
            document.getElementById('volunteerEvent').textContent = eventName;
            document.getElementById('volunteerUser').textContent = userName;
        });
    });
</script>

</body>
</html>

<?php
// Close the database connection at the end of the script
mysqli_close($dms);
?>