<?php
// === FILE: add_campaigns.php ===
// This file requires a 'config.php' file that establishes a database connection
// and stores the connection object in a variable named $dms.

include('config.php'); // Must have $dms = mysqli_connect(...) inside

$message = "";

// Fetch events for the dropdown
$events = [];
$eventQuery = "SELECT id, name FROM events";
$result = mysqli_query($dms, $eventQuery);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $events[] = $row;
    }
}

// Handle form submission
if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $goal_amount = (float)$_POST['goal_amount'];
    $start_date = $_POST['start_date'];
    $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
    $status = $_POST['status'];
    $event_id = $_POST['event_id'];

    // Handle file upload
    $file_path = null;
    if (!empty($_FILES['file']['name'])) {
        $target_dir = "uploads/";
        // Attempt to create the directory if it doesn't exist
        if (!is_dir($target_dir)) {
            if (!mkdir($target_dir, 0777, true)) {
                 $message = "❌ Error: Failed to create the 'uploads' directory. Please check folder permissions.";
            }
        }
        
        // If the directory exists, attempt to move the file
        if (empty($message)) {
            $file_path = $target_dir . basename($_FILES["file"]["name"]);
            if (!move_uploaded_file($_FILES["file"]["tmp_name"], $file_path)) {
                $message = "❌ Error: Failed to upload the file. Please check folder permissions for the 'uploads' directory.";
            }
        }
    }

    if (empty($message)) {
        // The INSERT query has 8 placeholders (`?`)
        $query = "INSERT INTO campaigns 
                  (name, descriptions, goal_amount, start_date, end_date, status, event_id, file_path) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($dms, $query);

        if ($stmt) {
            // CRITICAL FIX: The type definition string now has 8 characters
            // to match the 8 variables being bound.
            // s = string, d = double, i = integer
        mysqli_stmt_bind_param(
            $stmt,
            "ssdsssis", // 8 characters = 8 variables
            $name,
            $description,
            $goal_amount,
            $start_date,
            $end_date,
            $status,
            $event_id,
            $file_path
        );

            if (mysqli_stmt_execute($stmt)) {
                $message = "✅ Campaign added successfully!";
            } else {
                $message = "❌ Error executing statement: " . mysqli_stmt_error($stmt);
            }
            mysqli_stmt_close($stmt);
        } else {
            $message = "❌ SQL Prepare failed: " . mysqli_error($dms);
        }
    }
}

mysqli_close($dms);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Campaign</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            border: none;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.05);
            border-radius: 0.75rem;
        }
        .fw-bold {
            font-weight: 700 !important;
        }
       
    </style>
</head>
<body>

<div class="container-fluid camp mt-4 p-5">
     <div class="row">
        <div class="col-md-9 mx-auto ">

            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold"><i class="bi bi-megaphone"></i> Add New Campaign</h4>
            </div>

            <!-- Display Message -->
            <?php if (!empty($message)): ?>
                <div class="alert <?= strpos($message, 'Error') !== false ? 'alert-danger' : 'alert-success' ?>" role="alert">
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <!-- Add Campaign Form -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <form action="" method="post" enctype="multipart/form-data" class="row g-3">
                        <div class="col-md-12">
                            <label for="campaignName" class="form-label">Campaign Name</label>
                            <input type="text" class="form-control" id="campaignName" name="name" required>
                        </div>
                        <div class="col-md-12">
                            <label for="campaignDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="campaignDescription" name="description" rows="3" required></textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="goalAmount" class="form-label">Goal Amount ($)</label>
                            <input type="number" step="0.01" class="form-control" id="goalAmount" name="goal_amount" required>
                        </div>
                        <div class="col-md-6">
                            <label for="eventID" class="form-label">Event</label>
                            <select id="eventID" name="event_id" class="form-control">
                                <option value="">-- Select Event --</option>
                                <?php foreach ($events as $event): ?>
                                    <option value="<?= htmlspecialchars($event['id']) ?>"><?= htmlspecialchars($event['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="startDate" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="startDate" name="start_date" required>
                        </div>
                        <div class="col-md-6">
                            <label for="endDate" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="endDate" name="end_date">
                        </div>
                         <div class="col-md-12">
                            <label for="campaignStatus" class="form-label">Campaign Status</label>
                            <select class="form-control" id="campaignStatus" name="status" required>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label for="campaignfile" class="form-label">Campaign Image</label>
                            <input class="form-control" type="file" id="campaignfile" name="file">
                        </div>
                        <div class="col-12">
                            <button type="submit" name="submit" class="btn btn-primary w-100">Add Campaign</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
