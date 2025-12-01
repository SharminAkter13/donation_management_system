<?php
include('config.php');

$message = "";

// Check database connection
if (!$dms) {
    die("Connection failed: " . mysqli_connect_error());
}

// --- Handle form submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $user_id = intval($_POST['user_id'] ?? 0);
    $availability_status = trim($_POST['volunteer_availability_status'] ?? '');
    $task = trim($_POST['volunteer_task'] ?? '');
    $event_id = intval($_POST['event_id'] ?? 0);
    $contact = trim($_POST['volunteer_contact'] ?? '');
    $name = trim($_POST['volunteer_name'] ?? '');

    // Validate inputs
    if (empty($name) || empty($contact) || empty($task) || empty($availability_status) || $event_id <= 0 || $user_id <= 0) {
        $message = "<div class='alert alert-danger text-center'>Please fill in all required fields.</div>";
    } else {
        // Use a prepared statement to prevent SQL injection
        $query = "INSERT INTO volunteer_system (name, contact, task, availability_status, event_id, user_id) 
                  VALUES (?, ?, ?, ?, ?, ?)";
        
        if ($stmt = $dms->prepare($query)) {
            $stmt->bind_param("ssssii", $name, $contact, $task, $availability_status, $event_id, $user_id);

            if ($stmt->execute()) {
                $message = "<div class='alert alert-success text-center'>Volunteer added successfully!</div>";
            } else {
                $message = "<div class='alert alert-danger text-center'>Error adding volunteer: " . htmlspecialchars($stmt->error) . "</div>";
            }
            $stmt->close();
        } else {
            $message = "<div class='alert alert-danger text-center'>Database prepare error: " . htmlspecialchars($dms->error) . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Volunteer - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .card { border-radius: 12px; box-shadow: 0px 4px 15px rgba(0,0,0,0.1); }
    </style>
</head>
<body>

<div class="container my-5 p-5">
    <div class="row">
        <div class="col-md-12 mx-auto">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h4 class="mb-0"><i class="fas fa-handshake-angle me-2"></i>Add Volunteer</h4>
                        </div>
                        <div class="card-body">
                            <?= $message ?>
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="volunteer_name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="volunteer_name" name="volunteer_name" placeholder="Enter volunteer's full name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="volunteer_contact" class="form-label">Contact</label>
                                    <input type="text" class="form-control" id="volunteer_contact" name="volunteer_contact" placeholder="Enter contact" required>
                                </div>
                                <div class="mb-3">
                                    <label for="volunteer_task" class="form-label">Task</label>
                                    <input type="text" class="form-control" id="volunteer_task" name="volunteer_task" placeholder="Enter task" required>
                                </div>
                                <div class="mb-3">
                                    <label for="volunteer_availability_status" class="form-label">Availability Status</label>
                                    <select class="form-control" id="volunteer_availability_status" name="volunteer_availability_status" required>
                                        <option value="">Select status</option>
                                        <option value="Available">Available</option>
                                        <option value="Unavailable">Unavailable</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="event_id" class="form-label">Select Event</label>
                                    <select class="form-control" id="event_id" name="event_id" required>
                                        <option value="">Select an event</option>
                                        <?php
                                        $result = mysqli_query($dms, "SELECT id, name FROM events ORDER BY name");
                                        if (!$result) {
                                            echo "<option value=''>Error fetching events</option>";
                                        } else {
                                            while ($event = mysqli_fetch_assoc($result)) {
                                                echo "<option value='{$event['id']}'>" . htmlspecialchars($event['name']) . "</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="user_id" class="form-label">Select User</label>
                                    <select class="form-control" id="user_id" name="user_id" required>
                                        <option value="">Select a user</option>
                                        <?php
                                        // This is the corrected query to show only volunteers
                                        $query = "SELECT id, CONCAT(first_name, ' ', last_name) AS full_name FROM users WHERE role_id = 3 ORDER BY full_name";
                                        $result = mysqli_query($dms, $query);

                                        if (!$result) {
                                            echo "<option value=''>Error fetching users</option>";
                                        } else {
                                            while ($user = mysqli_fetch_assoc($result)) {
                                                echo "<option value='{$user['id']}'>" . htmlspecialchars($user['full_name']) . "</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="text-end">
                                    <button type="reset" class="btn btn-secondary me-2">Reset</button>
                                    <button type="submit" class="btn btn-primary">Add Volunteer</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

</body>
</html>
<?php
// Close the database connection at the end of the script
mysqli_close($dms);
?>