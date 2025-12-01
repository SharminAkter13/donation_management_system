<?php
// Include your database configuration file
include('config.php');

if (isset($_POST["btnDelete"])) {
    $p_id = $_POST["txtId"] ?? null;

    if ($p_id) {
        // Use a prepared statement to prevent SQL Injection
        $stmt = $dms->prepare("DELETE FROM pledges WHERE id = ?");
        
        if ($stmt) {
            $stmt->bind_param("i", $p_id); // 'i' indicates the parameter is an integer

            if ($stmt->execute()) {
                echo "<div class='alert alert-success'>Pledge deleted successfully.</div>";
            } else {
                echo "<div class='alert alert-danger'>Error deleting pledge: " . $stmt->error . "</div>";
            }
            $stmt->close();
        } else {
            echo "<div class='alert alert-danger'>Error preparing statement: " . $dms->error . "</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>No pledge ID provided.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Pledges</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" xintegrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Font Awesome for Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <!-- Custom Styles -->
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
<!-- Content Wrapper. Contains page content -->
<div class="container-fluid p-5">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Manage Pledges</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="home.php">Home</a></li>
                        <li class="breadcrumb-item active">Manage Pledges</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <!-- Default box -->
        <div class="card">
            <div class="card-header bg-info text-white">
                <h3 class="card-title">Manage Pledges</h3>
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
                            <th>Pledge Name</th>
                            <th>Pledge Amount</th>
                            <th>Pledge Date</th>
                            <th>Status</th>
                            <th>Donor Name</th>
                            <th>Campaign Name</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Query to join with donors and campaigns tables to get names
                        $query = "SELECT 
                                    p.id, p.name, p.pledge_amount, p.pledge_date, p.status, 
                                    d.name AS donor_name, c.name AS campaign_name 
                                  FROM 
                                    pledges p
                                  LEFT JOIN 
                                    donors d ON p.donor_id = d.id
                                  LEFT JOIN
                                    campaigns c ON p.campaign_id = c.id";
                        
                        $pledges_result = $dms->query($query);
                        if ($pledges_result) {
                            while ($row = $pledges_result->fetch_assoc()) {
                                // Sanitize data for HTML output
                                $id = htmlspecialchars($row['id']);
                                $name = htmlspecialchars($row['name']);
                                $pledge_amount = htmlspecialchars($row['pledge_amount']);
                                $pledge_date = htmlspecialchars($row['pledge_date']);
                                $status = htmlspecialchars($row['status']);
                                $donor_name = htmlspecialchars($row['donor_name']);
                                $campaign_name = htmlspecialchars($row['campaign_name']);

                                echo "<tr>";
                                echo "<td>$id</td>";
                                echo "<td>$name</td>";
                                echo "<td>$pledge_amount</td>";
                                echo "<td>$pledge_date</td>";
                                echo "<td>$status</td>";
                                echo "<td>$donor_name</td>";
                                echo "<td>$campaign_name</td>";
                                echo "<td class='d-flex justify-content-center align-items-center'>";
                                
                                // View Button with Pledge details
                                echo "<button type='button' class='btn btn-info btn-sm me-2' data-bs-toggle='modal' data-bs-target='#pledgeViewModal'";
                                echo " data-id='$id' data-name='$name' data-amount='$pledge_amount' data-date='$pledge_date' data-status='$status' data-donorname='$donor_name' data-campaignname='$campaign_name' title='View Pledge'>";
                                echo "<i class='fas fa-eye'></i>";
                                echo "</button>";

                                // Delete Button (opens confirm modal)
                                echo "<button type='button' class='btn btn-danger btn-sm me-2' data-bs-toggle='modal' data-bs-target='#deleteConfirmModal' data-id='$id' title='Delete Pledge'>";
                                echo "<i class='fas fa-trash-alt'></i>";
                                echo "</button>";
                                
                                // Edit Button (unchanged from your original, for consistency)
                                echo "<form action='home.php?page=3' method='post' data-bs-toggle='tooltip' title='Edit Pledge'>";
                                echo "<input type='hidden' name='id' value='$id'>";
                                echo "<button type='submit' name='btnEdit' class='btn btn-warning btn-sm'>";
                                echo "<i class='fas fa-edit'></i>";
                                echo "</button>";
                                echo "</form>";
                                
                                // Hidden delete form (will be submitted by JS)
                                echo "<form id='deleteForm-$id' action='' method='post' style='display:none;'>";
                                echo "<input type='hidden' name='txtId' value='$id'>";
                                echo "<input type='hidden' name='btnDelete' value='1'>";
                                echo "</form>";
                                
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='8' class='text-center'>No pledges found.</td></tr>";
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

<!-- View Pledge Modal -->
<div class="modal fade" id="pledgeViewModal" tabindex="-1" aria-labelledby="pledgeViewModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pledgeViewModalLabel">Pledge Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p><strong>ID:</strong> <span id="view-id"></span></p>
                <p><strong>Pledge Name:</strong> <span id="view-name"></span></p>
                <p><strong>Pledge Amount:</strong> <span id="view-amount"></span></p>
                <p><strong>Pledge Date:</strong> <span id="view-date"></span></p>
                <p><strong>Status:</strong> <span id="view-status"></span></p>
                <p><strong>Donor Name:</strong> <span id="view-donorname"></span></p>
                <p><strong>Campaign Name:</strong> <span id="view-campaignname"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this pledge? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

<!-- Initialize Tooltips & Modal -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function (tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // View Modal
    var pledgeViewModal = document.getElementById('pledgeViewModal');
    if (pledgeViewModal) {
        pledgeViewModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            document.getElementById('view-id').textContent = button.getAttribute('data-id');
            document.getElementById('view-name').textContent = button.getAttribute('data-name');
            document.getElementById('view-amount').textContent = button.getAttribute('data-amount');
            document.getElementById('view-date').textContent = button.getAttribute('data-date');
            document.getElementById('view-status').textContent = button.getAttribute('data-status');
            document.getElementById('view-donorname').textContent = button.getAttribute('data-donorname');
            document.getElementById('view-campaignname').textContent = button.getAttribute('data-campaignname');
        });
    }

    // Delete Confirmation Modal
    var deleteConfirmModal = document.getElementById('deleteConfirmModal');
    if (deleteConfirmModal) {
        let pledgeIdToDelete = null;

        deleteConfirmModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            pledgeIdToDelete = button.getAttribute('data-id');
        });

        document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
            if (pledgeIdToDelete) {
                // Find the hidden form with the corresponding ID
                const form = document.getElementById(`deleteForm-${pledgeIdToDelete}`);
                if (form) {
                    // Submit the form to trigger the PHP delete logic
                    form.submit();
                }
            }
        });
    }
});
</script>
</body>
</html>
