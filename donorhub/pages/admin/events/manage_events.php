<?php
// Start the session to access session variables
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include('config.php');

// Define allowed roles for this page
$allowedRoles = ['admin', 'campaign_manager', 'volunteer'];
$userRole = $_SESSION['user_role'] ?? '';

// Check if the user's role is in the allowed roles list
if (!in_array($userRole, $allowedRoles)) {
    // If not authorized, display an error message and exit
    echo "<div class='alert alert-danger'>Access Denied. You do not have permission to manage events.</div>";
    exit(); // Stop script execution
}

// Handle DELETION using a prepared statement for security
if (isset($_POST["btnDelete"])) {
    $u_id = $_POST["txtId"] ?? null;

    if ($u_id) {
        $stmt = $dms->prepare("DELETE FROM events WHERE id = ?");
        
        if ($stmt) {
            $stmt->bind_param("i", $u_id);

            if ($stmt->execute()) {
                echo "<div class='alert alert-success'>Event deleted successfully.</div>";
            } else {
                echo "<div class='alert alert-danger'>Error deleting event: " . $stmt->error . "</div>";
            }
            $stmt->close();
        } else {
            echo "<div class='alert alert-danger'>Error preparing statement: " . $dms->error . "</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>No event ID provided.</div>";
    }
}

// Handle EDITING/UPDATING using a prepared statement for security
if (isset($_POST["btnEdit"])) {
    $u_id = $_POST['edit_id'] ?? null;
    $u_name = trim($_POST['edit_name'] ?? '');
    $u_location = trim($_POST['edit_location'] ?? '');
    $u_date = $_POST['edit_date'] ?? '';

    // Simple validation
    if (!empty($u_id) && !empty($u_name) && !empty($u_location) && !empty($u_date)) {
        $stmt = $dms->prepare("UPDATE events SET name = ?, location = ?, date = ? WHERE id = ?");
        
        if ($stmt) {
            $stmt->bind_param("sssi", $u_name, $u_location, $u_date, $u_id);
            
            if ($stmt->execute()) {
                echo "<div class='alert alert-success'>Event updated successfully.</div>";
            } else {
                echo "<div class='alert alert-danger'>Error updating event: " . $stmt->error . "</div>";
            }
            $stmt->close();
        } else {
            echo "<div class='alert alert-danger'>Error preparing update statement: " . $dms->error . "</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>All fields are required for update.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage events</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" xintegrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .content-wrapper { padding: 20px; }
        .card-header .card-title { float: none; }
        .card-tools { float: right; }
    </style>
</head>
<body>
<div class="container-fluid p-5">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Manage Events</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="home.php">Home</a></li>
                        <li class="breadcrumb-item active">Manage Events</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h3 class="card-title">Manage Events</h3>
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
                <table class="table table-hover table-light table-striped">
                    <thead class="table-secondary-subtle text-center fw-bold">
                        <tr>
                            <th>#ID</th>
                            <th>Name</th>
                            <th>Location</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $events_result = $dms->query("SELECT * FROM events");
                        if ($events_result) {
                            while ($row = $events_result->fetch_assoc()) {
                                // Sanitize data for HTML output
                                $id = htmlspecialchars($row['id']);
                                $name = htmlspecialchars($row['name']);
                                $location = htmlspecialchars($row['location']);
                                $date = htmlspecialchars($row['date']);
                                // Format date for datetime-local input
                                $formatted_date = str_replace(' ', 'T', $row['date']);

                                echo "<tr>";
                                echo "<td>$id</td>";
                                echo "<td>$name</td>";
                                echo "<td>$location</td>";
                                echo "<td>$date</td>";
                                echo "<td class='d-flex justify-content-center align-items-center'>";

                                // View button
                                echo "<button type='button' class='btn btn-info btn-sm me-2' data-bs-toggle='modal' data-bs-target='#eventViewModal' ";
                                echo " data-id='$id' data-ename='$name' data-location='$location' data-date='$date' title='View Events'>";
                                echo "<i class='fas fa-eye'></i>";
                                echo "</button>";
                                
                                // Delete button (opens confirm modal)
                                echo "<button type='button' class='btn btn-danger btn-sm me-2' data-bs-toggle='modal' data-bs-target='#deleteConfirmModal' data-id='$id' title='Delete event'>";
                                echo "<i class='fas fa-trash-alt'></i>";
                                echo "</button>";

                                // Edit button
                                echo "<button type='button' class='btn btn-warning btn-sm' data-bs-toggle='modal' data-bs-target='#eventEditModal' ";
                                echo " data-id='$id' data-ename='$name' data-location='$location' data-date='$formatted_date' title='Edit Events'>";
                                echo "<i class='fas fa-edit'></i>";
                                echo "</button>";
                                
                                // Hidden delete form (now posts btnDelete as hidden input so POST matches PHP)
                                echo "<form id='deleteForm-$id' action='' method='post' class='me-2' style='display:none;'>";
                                echo "<input type='hidden' name='txtId' value='$id'>";
                                echo "<input type='hidden' name='btnDelete' value='1'>"; // <-- critical fix
                                echo "</form>";

                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5' class='text-center'>No events found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <div class="card-footer clearfix">
                <ul class="pagination pagination-sm m-0 float-right">
                    <li class="page-item"><a class="page-link" href="#">«</a></li>
                    <li class="page-item"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item"><a class="page-link" href="#">»</a></li>
                </ul>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="eventViewModal" tabindex="-1" aria-labelledby="eventViewModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eventViewModalLabel">Events Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p><strong>ID:</strong> <span id="view-id"></span></p>
                <p><strong>Event Name:</strong> <span id="view-ename"></span></p>
                <p><strong>Location:</strong> <span id="view-location"></span></p>
                <p><strong>Date:</strong> <span id="view-date"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="eventEditModal" tabindex="-1" aria-labelledby="eventEditModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="eventEditModalLabel">Edit Event</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" action="" method="post">
                <div class="modal-body">
                    <input type="hidden" name="edit_id" id="edit-id">
                    <div class="mb-3">
                        <label for="edit-name" class="form-label">Event Name</label>
                        <input type="text" class="form-control" id="edit-name" name="edit_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-location" class="form-label">Location</label>
                        <input type="text" class="form-control" id="edit-location" name="edit_location" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-date" class="form-label">Date and Time</label>
                        <input type="datetime-local" class="form-control" id="edit-date" name="edit_date" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="btnEdit" class="btn btn-warning">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this event? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function (tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // View Modal
    var eventViewModal = document.getElementById('eventViewModal');
    if (eventViewModal) {
        eventViewModal.addEventListener('show.bs.modal', function (event) {
            var trigger = event.relatedTarget;
            var button = trigger.closest ? trigger.closest('button') : trigger; // handle <i> click
            document.getElementById('view-id').textContent = button.getAttribute('data-id');
            document.getElementById('view-ename').textContent = button.getAttribute('data-ename');
            document.getElementById('view-location').textContent = button.getAttribute('data-location');
            document.getElementById('view-date').textContent = button.getAttribute('data-date');
        });
    }

    // Edit Modal
    var eventEditModal = document.getElementById('eventEditModal');
    if (eventEditModal) {
        eventEditModal.addEventListener('show.bs.modal', function (event) {
            var trigger = event.relatedTarget;
            var button = trigger.closest ? trigger.closest('button') : trigger;
            document.getElementById('edit-id').value = button.getAttribute('data-id');
            document.getElementById('edit-name').value = button.getAttribute('data-ename');
            document.getElementById('edit-location').value = button.getAttribute('data-location');
            document.getElementById('edit-date').value = button.getAttribute('data-date');
        });
    }

    // Delete Confirmation Modal
    var deleteConfirmModal = document.getElementById('deleteConfirmModal');
    if (deleteConfirmModal) {
        let eventIdToDelete = null;

        deleteConfirmModal.addEventListener('show.bs.modal', function (event) {
            const trigger = event.relatedTarget;
            const button = trigger.closest ? trigger.closest('button') : trigger; // handle <i> click
            eventIdToDelete = button.getAttribute('data-id');
        });

        document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
            if (eventIdToDelete) {
                const form = document.getElementById(`deleteForm-${eventIdToDelete}`);
                if (form) {
                    form.submit(); // posts txtId + btnDelete=1
                }
            }
        });
    }
});
</script>

</body>
</html>