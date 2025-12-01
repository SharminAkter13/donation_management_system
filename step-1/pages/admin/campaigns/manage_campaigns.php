<?php
// Start the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
// Include the database configuration file
include('config.php');

// Define the table names
$campaignsTable = "campaigns";
$eventsTable = "events";

// --- PERMISSION CHECK ---
$userRole = $_SESSION['user_role'] ?? '';
$isAdmin = ($userRole === 'admin');
$isCampaignManager = ($userRole === 'campaign_manager');

// Redirect if not authorized
if (!$isAdmin && !$isCampaignManager) {
    echo "<div class='alert alert-danger'>Access Denied. You do not have permission to view this page.</div>";
    exit;
}

// --- CRUD Operations ---

// Add a new campaign
if (isset($_POST['btnSave'])) {
    $name = $_POST['campaignName'];
    $startDate = $_POST['startDate'];
    $endDate = $_POST['endDate'];
    $status = $_POST['status'];
    $eventId = $_POST['eventId'];
    $filePath = $_POST['filePath'];

    // Use prepared statements to prevent SQL injection
    $stmt = $dms->prepare("INSERT INTO $campaignsTable (name, start_date, end_date, status, event_id, file_path) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssis", $name, $startDate, $endDate, $status, $eventId, $filePath);

    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Campaign added successfully.</div>";
    } else {
        echo "<div class='alert alert-danger'>Error adding campaign: " . $stmt->error . "</div>";
    }
    $stmt->close();
}

// Update an existing campaign
if (isset($_POST['btnEdit'])) {
    $id = $_POST['id'];
    $name = $_POST['campaignName'];
    $startDate = $_POST['startDate'];
    $endDate = $_POST['endDate'];
    $status = $_POST['status'];
    $eventId = $_POST['eventId'];
    $filePath = $_POST['filePath'];

    // Use prepared statements to prevent SQL injection
    $stmt = $dms->prepare("UPDATE $campaignsTable SET name=?, start_date=?, end_date=?, status=?, event_id=?, file_path=? WHERE id=?");
    $stmt->bind_param("ssssisi", $name, $startDate, $endDate, $status, $eventId, $filePath, $id);

    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Campaign updated successfully.</div>";
    } else {
        echo "<div class='alert alert-danger'>Error updating campaign: " . $stmt->error . "</div>";
    }
    $stmt->close();
}

// Delete a campaign
if (isset($_POST['btnDelete'])) {
    $id = $_POST['txtId'];

    // Use prepared statements for safe deletion
    $stmt = $dms->prepare("DELETE FROM $campaignsTable WHERE id=?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Campaign deleted successfully.</div>";
    } else {
        echo "<div class='alert alert-danger'>Error deleting campaign: " . $stmt->error . "</div>";
    }
    $stmt->close();
}

// Fetch all events for the dropdown menu
$events = [];
$event_sql = "SELECT id, name FROM $eventsTable";
$event_result = $dms->query($event_sql);
if ($event_result) {
    while ($row = $event_result->fetch_assoc()) {
        $events[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Campaigns</title>
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
                    <h1>Manage Campaigns</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Manage Campaigns</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Campaigns</h3>
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
                <?php if ($isAdmin || $isCampaignManager): ?>
                    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#campaignModal" id="addCampaignBtn">
                        + Add New Campaign
                    </button>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>#ID</th>
                                <th>Campaign Name</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Status</th>
                                <th>Event Name</th>
                                <th>File Path</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="campaignTableBody">
                            <?php
                            // Check for database connection before running the query
                            if ($dms->connect_error) {
                                echo "<tr><td colspan='8' class='text-center text-danger'>Connection failed: " . $dms->connect_error . "</td></tr>";
                            } else {
                                // Fetch all campaigns with their associated event names using a JOIN
                                $sql = "SELECT c.id, c.name, c.start_date, c.end_date, c.status, c.file_path, e.name AS event_name, c.event_id
                                        FROM $campaignsTable AS c
                                        LEFT JOIN $eventsTable AS e ON c.event_id = e.id";
                                $campaigns = $dms->query($sql);

                                // Added error handling to check if the query was successful
                                if (!$campaigns) {
                                    echo "<tr><td colspan='8' class='text-center text-danger'>Query failed: " . $dms->error . "</td></tr>";
                                } else if ($campaigns->num_rows > 0) {
                                    while ($row = $campaigns->fetch_assoc()) {
                                        // Helper: Create badge HTML based on status
                                        $statusMap = ['Active' => 'success', 'Inactive' => 'danger'];
                                        $badgeClass = $statusMap[$row['status']] ?? 'primary';
                                        $statusBadge = "<span class='badge bg-{$badgeClass}'>{$row['status']}</span>";

                                        $eventName = $row['event_name'] ?? 'N/A';
                                        $filePath = htmlspecialchars($row['file_path'] ?? 'N/A');
                                        $filePathLink = ($filePath !== 'N/A') ? "<a href='{$filePath}' target='_blank'>View File</a>" : "N/A";

                                        echo "<tr>
                                            <td>{$row['id']}</td>
                                            <td>{$row['name']}</td>
                                            <td>{$row['start_date']}</td>
                                            <td>{$row['end_date']}</td>
                                            <td>{$statusBadge}</td>
                                            <td>{$eventName}</td>
                                            <td>{$filePathLink}</td>
                                            <td class='d-flex justify-content-center align-items-center'>
                                                <button type='button' class='btn btn-info btn-sm view-btn me-2' data-bs-toggle='modal' data-bs-target='#viewCampaignModal'
                                                    data-id='{$row['id']}'
                                                    data-name='{$row['name']}'
                                                    data-start='{$row['start_date']}'
                                                    data-end='{$row['end_date']}'
                                                    data-status='{$row['status']}'
                                                    data-event-name='{$eventName}'
                                                    data-file-path='{$filePath}'
                                                    title='View Campaign'>
                                                    <i class='fas fa-eye'></i>
                                                </button>";

                                        // Only show Edit and Delete buttons for authorized users
                                        if ($isAdmin || $isCampaignManager) {
                                            echo "<button type='button' class='btn btn-warning btn-sm edit-btn me-2' data-bs-toggle='modal' data-bs-target='#campaignModal'
                                                data-id='{$row['id']}'
                                                data-name='{$row['name']}'
                                                data-start='{$row['start_date']}'
                                                data-end='{$row['end_date']}'
                                                data-status='{$row['status']}'
                                                data-event-id='{$row['event_id']}'
                                                data-file-path='{$filePath}'
                                                title='Edit Campaign'>
                                                <i class='fas fa-edit'></i>
                                            </button>
                                            <button type='button' class='btn btn-danger btn-sm delete-btn' data-bs-toggle='modal' data-bs-target='#deleteConfirmModal' data-id='{$row['id']}' title='Delete Campaign'>
                                                <i class='fas fa-trash-alt'></i>
                                            </button>";
                                        }

                                        echo "</td></tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='8' class='text-center'>No campaigns found.</td></tr>";
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
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

<div class="modal fade" id="campaignModal" tabindex="-1" aria-labelledby="campaignModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="campaignForm" class="modal-content" method="post">
            <div class="modal-header">
                <h5 class="modal-title" id="campaignModalLabel">Add Campaign</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="campaignId" name="id" />
                <div class="mb-3">
                    <label for="campaignName" class="form-label">Campaign Name</label>
                    <input type="text" class="form-control" id="campaignName" name="campaignName" required />
                </div>
                <div class="mb-3">
                    <label for="startDate" class="form-label">Start Date</label>
                    <input type="date" class="form-control" id="startDate" name="startDate" required />
                </div>
                <div class="mb-3">
                    <label for="endDate" class="form-label">End Date</label>
                    <input type="date" class="form-control" id="endDate" name="endDate" required />
                </div>
                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status" required>
                        <option value="" disabled selected>Select status</option>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="eventId" class="form-label">Event</label>
                    <select class="form-select" id="eventId" name="eventId" required>
                        <option value="" disabled selected>Select an event</option>
                        <?php foreach ($events as $event) : ?>
                            <option value="<?= htmlspecialchars($event['id']) ?>">
                                <?= htmlspecialchars($event['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="filePath" class="form-label">File Path (URL)</label>
                    <input type="url" class="form-control" id="filePath" name="filePath" placeholder="e.g., https://example.com/file.pdf" />
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" id="saveCampaignBtn" name="btnSave">Save Campaign</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="viewCampaignModal" tabindex="-1" aria-labelledby="viewCampaignModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewCampaignModalLabel">Campaign Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <strong>ID:</strong> <span id="viewId"></span>
                </div>
                <div class="mb-3">
                    <strong>Campaign Name:</strong> <span id="viewName"></span>
                </div>
                <div class="mb-3">
                    <strong>Start Date:</strong> <span id="viewStart"></span>
                </div>
                <div class="mb-3">
                    <strong>End Date:</strong> <span id="viewEnd"></span>
                </div>
                <div class="mb-3">
                    <strong>Status:</strong> <span id="viewStatus"></span>
                </div>
                <div class="mb-3">
                    <strong>Event Name:</strong> <span id="viewEventName"></span>
                </div>
                <div class="mb-3">
                    <strong>File Path:</strong> <span id="viewFilePath"></span>
                </div>
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
                Are you sure you want to delete this campaign? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="post" action="">
                    <input type="hidden" name="txtId" id="deleteCampaignId">
                    <button type="submit" class="btn btn-danger" name="btnDelete">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function (tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });

    const campaignModal = document.getElementById('campaignModal');
    if (campaignModal) {
        const modalTitle = document.getElementById('campaignModalLabel');
        const campaignForm = document.getElementById('campaignForm');
        const saveButton = document.getElementById('saveCampaignBtn');
        const campaignIdInput = document.getElementById('campaignId');

        campaignModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const isEdit = button.classList.contains('edit-btn');

            if (isEdit) {
                modalTitle.textContent = 'Edit Campaign';
                saveButton.textContent = 'Update Campaign';
                saveButton.name = 'btnEdit';

                // Populate form fields with data from the button's data attributes
                campaignIdInput.value = button.getAttribute('data-id');
                document.getElementById('campaignName').value = button.getAttribute('data-name');
                document.getElementById('startDate').value = button.getAttribute('data-start');
                document.getElementById('endDate').value = button.getAttribute('data-end');
                document.getElementById('status').value = button.getAttribute('data-status');
                document.getElementById('eventId').value = button.getAttribute('data-event-id');
                // New: Populate file path field
                document.getElementById('filePath').value = button.getAttribute('data-file-path');
            } else {
                modalTitle.textContent = 'Add Campaign';
                saveButton.textContent = 'Save Campaign';
                saveButton.name = 'btnSave';
                campaignForm.reset();
                campaignIdInput.value = '';
                document.getElementById('eventId').value = ''; // Reset the dropdown
                document.getElementById('filePath').value = ''; // New: Clear file path
            }
        });
    }

    // New JavaScript for the View Modal
    const viewCampaignModal = document.getElementById('viewCampaignModal');
    if (viewCampaignModal) {
        viewCampaignModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;

            // Populate view modal with data from the button's data attributes
            document.getElementById('viewId').textContent = button.getAttribute('data-id');
            document.getElementById('viewName').textContent = button.getAttribute('data-name');
            document.getElementById('viewStart').textContent = button.getAttribute('data-start');
            document.getElementById('viewEnd').textContent = button.getAttribute('data-end');

            // Recreate the status badge for the view modal
            const status = button.getAttribute('data-status');
            const statusBadgeSpan = document.createElement('span');
            statusBadgeSpan.textContent = status;
            statusBadgeSpan.classList.add('badge');
            if (status === 'Active') {
                statusBadgeSpan.classList.add('bg-success');
            } else if (status === 'Inactive') {
                statusBadgeSpan.classList.add('bg-danger');
            } else {
                statusBadgeSpan.classList.add('bg-primary');
            }
            document.getElementById('viewStatus').innerHTML = ''; // Clear previous content
            document.getElementById('viewStatus').appendChild(statusBadgeSpan);

            document.getElementById('viewEventName').textContent = button.getAttribute('data-event-name');

            // New: Populate the view file path field
            const filePath = button.getAttribute('data-file-path');
            const viewFilePathSpan = document.getElementById('viewFilePath');
            viewFilePathSpan.innerHTML = ''; // Clear previous content
            if (filePath && filePath !== 'N/A') {
                const link = document.createElement('a');
                link.href = filePath;
                link.textContent = filePath;
                link.target = '_blank';
                viewFilePathSpan.appendChild(link);
            } else {
                viewFilePathSpan.textContent = 'N/A';
            }
        });
    }

    const deleteConfirmModal = document.getElementById('deleteConfirmModal');
    if (deleteConfirmModal) {
        deleteConfirmModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            document.getElementById('deleteCampaignId').value = id;
        });
    }
});
</script>

</body>
</html>