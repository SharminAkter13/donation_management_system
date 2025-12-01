<?php
// Include your database configuration file
include('config.php');

// Replace with your actual credentials if they are not in config.php
$dms = new mysqli('localhost', 'root', '', 'donation_management_system');

if ($dms->connect_error) {
    die("Connection failed: " . $dms->connect_error);
}

// ------------------------------------------------------------------------------------------------
// PHP Logic to handle DELETION
// ------------------------------------------------------------------------------------------------
if (isset($_POST["btnDelete"])) {
    $v_id = $_POST["txtId"] ?? null;

    if ($v_id) {
        // Use a prepared statement to prevent SQL Injection
        $stmt = $dms->prepare("DELETE FROM volunteer WHERE id = ?");
        
        if ($stmt) {
            $stmt->bind_param("i", $v_id); // 'i' indicates the parameter is an integer

            if ($stmt->execute()) {
                echo "<div class='alert alert-success'>Volunteer deleted successfully.</div>";
            } else {
                echo "<div class='alert alert-danger'>Error deleting volunteer: " . $stmt->error . "</div>";
            }
            $stmt->close();
        } else {
            echo "<div class='alert alert-danger'>Error preparing statement: " . $dms->error . "</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>No volunteer ID provided.</div>";
    }
}


// ------------------------------------------------------------------------------------------------
// PHP Logic to handle UPDATE (New)
// ------------------------------------------------------------------------------------------------
if (isset($_POST["btnUpdate"])) {
    // Sanitize and validate inputs
    $id = intval($_POST['edit_id'] ?? 0);
    $name = trim($_POST['edit_name'] ?? '');
    $contact = trim($_POST['edit_contact'] ?? '');
    $task = trim($_POST['edit_task'] ?? '');
    $availability_status = trim($_POST['edit_availability_status'] ?? '');
    $event_id = intval($_POST['edit_event_id'] ?? 0);
    $user_id = intval($_POST['edit_user_id'] ?? 0);

    if ($id <= 0 || empty($name) || empty($contact) || empty($task) || empty($availability_status) || $event_id <= 0 || $user_id <= 0) {
        echo "<div class='alert alert-danger'>Please fill in all required fields for update.</div>";
    } else {
        // Use a prepared statement to prevent SQL injection
        $query = "UPDATE volunteer SET name=?, contact=?, task=?, availability_status=?, event_id=?, user_id=? WHERE id=?";
        
        if ($stmt = $dms->prepare($query)) {
            $stmt->bind_param("ssssiii", $name, $contact, $task, $availability_status, $event_id, $user_id, $id);

            if ($stmt->execute()) {
                echo "<div class='alert alert-success'>Volunteer updated successfully!</div>";
            } else {
                echo "<div class='alert alert-danger'>Error updating volunteer: " . htmlspecialchars($stmt->error) . "</div>";
            }
            $stmt->close();
        } else {
            echo "<div class='alert alert-danger'>Database prepare error: " . htmlspecialchars($dms->error) . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Volunteers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
        }
        .content-wrapper {
            padding: 20px;
        }
        .card-header .card-title {
            float: none;
        }
        .card-tools {
            float: right;
        }
        .action-buttons {
            display: flex;
            justify-content: center;
        }
    </style>
</head>
<body>
<div class="container-fluid p-5">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Manage Volunteers</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="home.php">Home</a></li>
                        <li class="breadcrumb-item active">Manage Volunteers</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title">Manage Volunteers</h3>
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
                            <th>Volunteer Name</th>
                            <th>Contact</th>
                            <th>Task</th>
                            <th>Availability Status</th>
                            <th>Event Name</th>
                            <th>User Name</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Query to join with events and users tables to get names
                        $query = "SELECT 
                                    vs.id, vs.name, vs.contact, vs.task, vs.availability_status, 
                                    e.name AS event_name, 
                                    vs.event_id,
                                    u.id AS user_id,
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
                                $event_name = htmlspecialchars($row['event_name']);
                                $event_id = htmlspecialchars($row['event_id']);
                                $user_name = htmlspecialchars($row['user_name']);
                                $user_id = htmlspecialchars($row['user_id']);

                                echo "<tr>";
                                echo "<td>$id</td>";
                                echo "<td>$name</td>";
                                echo "<td>$contact</td>";
                                echo "<td>$task</td>";
                                echo "<td>$availability_status</td>";
                                echo "<td>$event_name</td>";
                                echo "<td>$user_name</td>";
                                echo "<td class='d-flex justify-content-center align-items-center'>";
                                
                                // View Button with volunteer details
                                echo "<button type='button' class='btn btn-info btn-sm me-2' data-bs-toggle='modal' data-bs-target='#volunteerViewModal'";
                                echo " data-id='$id' data-name='$name' data-contact='$contact' data-task='$task' data-availability='$availability_status' data-eventname='$event_name' data-username='$user_name' title='View Volunteer'>";
                                echo "<i class='fas fa-eye'></i>";
                                echo "</button>";

                                // Delete Button (opens confirm modal)
                                echo "<button type='button' class='btn btn-danger btn-sm me-2' data-bs-toggle='modal' data-bs-target='#deleteConfirmModal' data-id='$id' title='Delete Volunteer'>";
                                echo "<i class='fas fa-trash-alt'></i>";
                                echo "</button>";
                                
                                // NEW: Edit Button (opens edit modal)
                                echo "<button type='button' class='btn btn-warning btn-sm' data-bs-toggle='modal' data-bs-target='#volunteerEditModal'";
                                echo " data-id='$id' data-name='$name' data-contact='$contact' data-task='$task' data-availability='$availability_status' data-eventid='$event_id' data-userid='$user_id' title='Edit Volunteer'>";
                                echo "<i class='fas fa-edit'></i>";
                                echo "</button>";
                                
                                // Hidden delete form (will be submitted by JS)
                                echo "<form id='deleteForm-$id' action='' method='post' style='display:none;'>";
                                echo "<input type='hidden' name='txtId' value='$id'>";
                                echo "<input type='hidden' name='btnDelete' value='1'>";
                                echo "</form>";
                                
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='8' class='text-center'>No volunteers found.</td></tr>";
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

<div class="modal fade" id="volunteerViewModal" tabindex="-1" aria-labelledby="volunteerViewModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="volunteerViewModalLabel">Volunteer Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p><strong>ID:</strong> <span id="view-id"></span></p>
                <p><strong>Volunteer Name:</strong> <span id="view-name"></span></p>
                <p><strong>Contact:</strong> <span id="view-contact"></span></p>
                <p><strong>Task:</strong> <span id="view-task"></span></p>
                <p><strong>Availability Status:</strong> <span id="view-availability"></span></p>
                <p><strong>Event Name:</strong> <span id="view-eventname"></span></p>
                <p><strong>User Name:</strong> <span id="view-username"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
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
                Are you sure you want to delete this volunteer? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="volunteerEditModal" tabindex="-1" aria-labelledby="volunteerEditModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="volunteerEditModalLabel">Edit Volunteer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editForm" method="POST" action="">
                    <input type="hidden" name="edit_id" id="edit_id">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="edit_name" name="edit_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_contact" class="form-label">Contact</label>
                        <input type="text" class="form-control" id="edit_contact" name="edit_contact" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_task" class="form-label">Task</label>
                        <input type="text" class="form-control" id="edit_task" name="edit_task" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_availability_status" class="form-label">Availability Status</label>
                        <select class="form-control" id="edit_availability_status" name="edit_availability_status" required>
                            <option value="Available">Available</option>
                            <option value="Unavailable">Unavailable</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_event_id" class="form-label">Select Event</label>
                        <select class="form-control" id="edit_event_id" name="edit_event_id" required>
                            <?php
                            $result_events = mysqli_query($dms, "SELECT id, name FROM events ORDER BY name");
                            if ($result_events) {
                                while ($event = mysqli_fetch_assoc($result_events)) {
                                    echo "<option value='{$event['id']}'>" . htmlspecialchars($event['name']) . "</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_user_id" class="form-label">Select User (Volunteer)</label>
                        <select class="form-control" id="edit_user_id" name="edit_user_id" required>
                            <?php
                            $result_users = mysqli_query($dms, "SELECT id, CONCAT(first_name, ' ', last_name) AS full_name FROM users WHERE role_id = 3 ORDER BY full_name");
                            if ($result_users) {
                                while ($user = mysqli_fetch_assoc($result_users)) {
                                    echo "<option value='{$user['id']}'>" . htmlspecialchars($user['full_name']) . "</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="btnUpdate" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function (tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // View Modal
    var volunteerViewModal = document.getElementById('volunteerViewModal');
    if (volunteerViewModal) {
        volunteerViewModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            document.getElementById('view-id').textContent = button.getAttribute('data-id');
            document.getElementById('view-name').textContent = button.getAttribute('data-name');
            document.getElementById('view-contact').textContent = button.getAttribute('data-contact');
            document.getElementById('view-task').textContent = button.getAttribute('data-task');
            document.getElementById('view-availability').textContent = button.getAttribute('data-availability');
            document.getElementById('view-eventname').textContent = button.getAttribute('data-eventname');
            document.getElementById('view-username').textContent = button.getAttribute('data-username');
        });
    }

    // Delete Confirmation Modal
    var deleteConfirmModal = document.getElementById('deleteConfirmModal');
    if (deleteConfirmModal) {
        let volunteerIdToDelete = null;

        deleteConfirmModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            volunteerIdToDelete = button.getAttribute('data-id');
        });

        document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
            if (volunteerIdToDelete) {
                // Find the hidden form with the corresponding ID
                const form = document.getElementById(`deleteForm-${volunteerIdToDelete}`);
                if (form) {
                    // Submit the form to trigger the PHP delete logic
                    form.submit();
                }
            }
        });
    }

    // NEW: Edit Volunteer Modal
    var volunteerEditModal = document.getElementById('volunteerEditModal');
    if (volunteerEditModal) {
        volunteerEditModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            
            // Get data from data-* attributes
            var id = button.getAttribute('data-id');
            var name = button.getAttribute('data-name');
            var contact = button.getAttribute('data-contact');
            var task = button.getAttribute('data-task');
            var availability = button.getAttribute('data-availability');
            var eventId = button.getAttribute('data-eventid');
            var userId = button.getAttribute('data-userid');

            // Populate the modal's form fields
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_contact').value = contact;
            document.getElementById('edit_task').value = task;
            document.getElementById('edit_availability_status').value = availability;
            document.getElementById('edit_event_id').value = eventId;
            document.getElementById('edit_user_id').value = userId;
        });
    }
});
</script>
</body>
</html>
<?php
// Close the database connection at the end of the script
mysqli_close($dms);
?>