<?php
// Fix: The $dms variable is defined in config.php (included via placeholder.php)
// We need to ensure it's accessible within this script's scope and the getTotal function.
// If config.php includes the connection setup, make sure it's accessible globally.
// Assuming config.php is available in the parent scope where this file is included:

// --- Utility Function (Typically in a separate functions.php) ---
if (!function_exists('getTotal')) {
    /**
     * Executes an SQL query to get a single count value.
     * @param mysqli $dms The database connection object.
     * @param string $sql The SQL query to execute.
     * @return int The total count, or 0 on error.
     */
    function getTotal($dms, $sql) {
        // Line 22 was the error location, now using a corrected query
        $result = $dms->query($sql);
        if ($result && $row = $result->fetch_row()) {
            return (int)$row[0];
        }
        return 0;
    }
}
// -----------------------------------------------------------------

// Add 'global $dms;' here to explicitly bring the database connection
// defined in config.php (which is loaded before this file by placeholder.php) 
// into the local scope of this file.
global $dms;

// Check if $dms is still null after attempting to globalize it (safety check)
if ($dms === null) {
    // If $dms is still null, it means config.php was not included correctly
    // or did not define $dms. You might need to adjust the include path
    // if this error persists, but for now, we assume it's globally available.
    echo '<div class="alert alert-danger">Error: Database connection ($dms) is not available. Check config.php inclusion.</div>';
    // Prevent further execution that relies on the database
    return;
}

// 1. Get the ID for the 'volunteer' role to filter users
$role_sql = "SELECT id FROM roles WHERE name = 'volunteer'";
$role_result = $dms->query($role_sql);
$volunteer_role_id = 0;
if ($role_result && $row = $role_result->fetch_assoc()) {
    $volunteer_role_id = $row['id'];
}

// 2. Define SQL queries for the dashboard metrics using existing tables

// Metric 1: Total Number of Registered Volunteers (Users with 'volunteer' role)
$sql_total_volunteers = "
    SELECT COUNT(id) 
    FROM users 
    WHERE role_id = {$volunteer_role_id}
";

// Metric 2: Total Volunteer Event Signups (Entries in the existing 'volunteer' table)
// We are using the existing 'volunteer' table which tracks signups to 'events'.
$sql_event_signups = "
    SELECT COUNT(user_id) 
    FROM volunteer
";

// Metric 3: Total Number of Active Events
$sql_total_events = "
    SELECT COUNT(id) 
    FROM events
";

// Metric 4: Total Number of Active Campaigns (Used as general engagement metric)
$sql_total_campaigns = "
    SELECT COUNT(id) 
    FROM campaigns
";

// 5. Query for Volunteer Task/Event Table (New Query)
// FIX: Using columns explicitly provided by the user (name, task, availability_status, created_at)
// Removed JOIN to 'users' since 'volunteer.name' is available.
// Used 'e.name' for event name, as 'e.event_name' failed previously.
$sql_volunteer_tasks = "
    SELECT 
        v.name AS volunteer_name,
        v.task AS assigned_task,
        v.availability_status AS availability_status,
        e.name AS event_name,
        v.created_at AS signup_date
    FROM 
        volunteer v
    JOIN 
        events e ON v.event_id = e.id
    ORDER BY 
        v.created_at DESC
    LIMIT 10
";


// 3. Fetch the results
$total_volunteers = getTotal($dms, $sql_total_volunteers); // Total Users marked as Volunteers
$total_event_signups = getTotal($dms, $sql_event_signups);  // Total signups across all events
$total_events = getTotal($dms, $sql_total_events);          // Total Events
$total_campaigns = getTotal($dms, $sql_total_campaigns);    // Total Campaigns

$volunteer_tasks_result = $dms->query($sql_volunteer_tasks);
$volunteer_tasks = [];
if ($volunteer_tasks_result) {
    while ($row = $volunteer_tasks_result->fetch_assoc()) {
        $volunteer_tasks[] = $row;
    }
}


// 4. Dashboard HTML structure (using Bootstrap classes for cards)
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Volunteer Dashboard</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active">Volunteer Dashboard</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">

            <!-- Card 1: Total Registered Volunteers -->
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info rounded-lg shadow">
                    <div class="inner">
                        <h3><?php echo $total_volunteers; ?></h3>
                        <p>Total Volunteers</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <a href="?page=25" class="small-box-footer">
                        More info <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <!-- Card 2: Total Event Signups -->
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success rounded-lg shadow">
                    <div class="inner">
                        <h3><?php echo $total_event_signups; ?></h3>
                        <p>Total Event Signups</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-hand-holding-heart"></i>
                    </div>
                    <a href="?page=26" class="small-box-footer">
                        View Analytics <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <!-- Card 3: Total Active Events -->
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning rounded-lg shadow">
                    <div class="inner">
                        <h3><?php echo $total_events; ?></h3>
                        <p>Total Events Organized</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <a href="?page=6" class="small-box-footer">
                        Manage Events <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <!-- Card 4: Total Campaigns -->
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger rounded-lg shadow">
                    <div class="inner">
                        <h3><?php echo $total_campaigns; ?></h3>
                        <p>Total Campaigns</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-bullhorn"></i>
                    </div>
                    <a href="?page=10" class="small-box-footer">
                        Manage Campaigns <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            
        </div>
        <!-- End of Info Boxes Row -->

        <div class="row mt-4">
            <div class="col-12">
                <div class="card rounded-lg shadow-sm">
                    <div class="card-header border-0">
                        <h3 class="card-title">Recent Volunteer Activity</h3>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-striped table-valign-middle">
                            <thead>
                                <tr>
                                    <th>Volunteer</th>
                                    <th>Assigned Task</th>
                                    <th>Event/Project</th>
                                    <th>Availability Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($volunteer_tasks)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No volunteer signups found yet.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($volunteer_tasks as $task): 
                                        // Use the availability_status from the volunteer table
                                        $status_text = htmlspecialchars($task['availability_status']);
                                        
                                        // Determine badge color based on status text
                                        $status_class = 'badge-secondary';
                                        if (strtolower($status_text) === 'available') {
                                            $status_class = 'badge-success';
                                        } elseif (strtolower($status_text) === 'unavailable') {
                                            $status_class = 'badge-danger';
                                        } elseif (strtolower($status_text) === 'pending') {
                                            $status_class = 'badge-warning';
                                        }

                                        // Formatting signup date below status for context
                                        $signup_date_formatted = date('M d, Y', strtotime($task['signup_date']));
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($task['volunteer_name']); ?></td>
                                        <td><?php echo htmlspecialchars($task['assigned_task']); ?></td>
                                        <td><?php echo htmlspecialchars($task['event_name']); ?></td>
                                        <td>
                                            <span class="badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                            <br><small class="text-muted">Signed up: <?php echo $signup_date_formatted; ?></small>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</section>