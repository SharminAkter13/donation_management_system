<?php
include('config.php');

if (isset($_POST["btnDelete"])) {
    $u_id = $_POST["txtId"] ?? null;

    if ($u_id) {
        $dms->query("DELETE FROM users WHERE id='$u_id'");
        echo "<div class='alert alert-success'>User deleted successfully</div>";
    } else {
        echo "<div class='alert alert-danger'>No user ID provided</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <!-- Font Awesome for Icons -->
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
                <div class="col-sm-6"><h1>Manage Users</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Manage Users</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Manage Users</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                    <button type="button" class="btn btn-tool" data-card-widget="remove"><i class="fas fa-times"></i></button>
                </div>
            </div>

            <div class="card-body">
                <table class="table table-hover table-light table-striped">
                  <thead class="bg-info text-white">
                        <tr>
                            <th>#ID</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Email</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $users = $dms->query("SELECT * FROM users");
                        while (list($id, $fname, $lname, $email) = $users->fetch_row()) {
                            echo "<tr>
                                <td>$id</td>
                                <td>$fname</td>
                                <td>$lname</td>
                                <td>$email</td>
                                <td class='d-flex justify-content-center align-items-center'>
                                    <button type='button' class='btn btn-info btn-sm me-2' data-bs-toggle='modal' data-bs-target='#userViewModal'
                                        data-id='$id'
                                        data-fname='$fname'
                                        data-lname='$lname'
                                        data-email='$email'
                                        title='View User'>
                                        <i class='fas fa-eye'></i>
                                    </button>

                                    <!-- Delete Button -->
                                    <button type='button' class='btn btn-danger btn-sm me-2 deleteBtn' 
                                        data-id='$id' title='Delete User'>
                                        <i class='fas fa-trash-alt'></i>
                                    </button>
                                    
                                    <!-- Hidden Delete Form -->
                                    <form id='deleteForm-$id' action='' method='post' style='display:none;'>
                                        <input type='hidden' name='txtId' value='$id'>
                                        <input type='hidden' name='btnDelete' value='1'>
                                    </form>

                                    <form action='home.php?page=3' method='post'>
                                        <input type='hidden' name='id' value='$id'>
                                        <button type='submit' name='btnEdit' class='btn btn-warning btn-sm'>
                                            <i class='fas fa-edit'></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>";
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

<!-- View User Modal -->
<div class="modal fade" id="userViewModal" tabindex="-1" aria-labelledby="userViewModalLabel" aria-hidden="true">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="userViewModalLabel">User Details</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <p><strong>ID:</strong> <span id="view-id"></span></p>
            <p><strong>First Name:</strong> <span id="view-fname"></span></p>
            <p><strong>Last Name:</strong> <span id="view-lname"></span></p>
            <p><strong>Email:</strong> <span id="view-email"></span></p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
    </div></div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Confirm Deletion</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">Are you sure you want to delete this user?</div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
        </div>
    </div></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // View User Modal Fill
    var userViewModal = document.getElementById('userViewModal');
    if (userViewModal) {
        userViewModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            document.getElementById('view-id').textContent = button.getAttribute('data-id');
            document.getElementById('view-fname').textContent = button.getAttribute('data-fname');
            document.getElementById('view-lname').textContent = button.getAttribute('data-lname');
            document.getElementById('view-email').textContent = button.getAttribute('data-email');
        });
    }

    // Delete Confirmation
    let userIdToDelete = null;
    document.querySelectorAll('.deleteBtn').forEach(btn => {
        btn.addEventListener('click', function () {
            userIdToDelete = this.getAttribute('data-id');
            var deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
            deleteModal.show();
        });
    });

    document.getElementById('confirmDeleteBtn').addEventListener('click', function () {
        if (userIdToDelete) {
            document.getElementById('deleteForm-' + userIdToDelete).submit();
        }
    });
});
</script>
</body>
</html>
