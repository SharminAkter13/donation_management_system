<?php
include 'config.php'; // Database connection

// Handle Add Fund
if (isset($_POST['addFund'])) {
    $name = $_POST['name'];
    $status = $_POST['status'];
    $collected = $_POST['collected_amount'];

    $stmt = $dms->prepare("INSERT INTO funds (name, status, collected_amount) VALUES (?, ?, ?)");
    $stmt->bind_param("ssd", $name, $status, $collected);
    $stmt->execute();
    $stmt->close();
}

// Handle Edit Fund
if (isset($_POST['editFund'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $status = $_POST['status'];
    $collected = $_POST['collected_amount'];

    $stmt = $dms->prepare("UPDATE funds SET name=?, status=?, collected_amount=? WHERE id=?");
    $stmt->bind_param("ssdi", $name, $status, $collected, $id);
    $stmt->execute();
    $stmt->close();
}

// Handle Delete Fund
if (isset($_POST['deleteFund'])) {
    $id = $_POST['id'];
    $dms->query("DELETE FROM funds WHERE id='$id'");
}

// Fetch all funds
$result = $dms->query("SELECT * FROM funds");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Funds</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container my-5">
    <h2 class="mb-4 text-center">Manage Funds</h2>

    <!-- Add Fund Form -->
    <div class="card mb-4">
        <div class="card-header">Add New Fund</div>
        <div class="card-body">
            <form method="POST">
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="name" class="form-control" placeholder="Fund Name" required>
                    </div>
                    <div class="col-md-4">
                        <select name="status" class="form-select" required>
                            <option value="">Select Status</option>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="number" name="collected_amount" class="form-control" placeholder="Collected Amount" step="0.01" required>
                    </div>
                </div>
                <div class="mt-3 text-end">
                    <button type="submit" name="addFund" class="btn btn-success">Add Fund</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Funds Table -->
    <div class="card">
        <div class="card-header">Funds List</div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Collected Amount</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['name'] ?></td>
                        <td><?= $row['status'] ?></td>
                        <td><?= number_format($row['collected_amount'], 2) ?></td>
                        <td>
                            <!-- Edit button triggers modal -->
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id'] ?>">Edit</button>

                            <!-- Delete form -->
                            <form method="POST" style="display:inline-block">
                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                <button type="submit" name="deleteFund" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </td>
                    </tr>

                    <!-- Edit Modal -->
                    <div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1" aria-hidden="true">
                      <div class="modal-dialog">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title">Edit Fund</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                          </div>
                          <form method="POST">
                          <div class="modal-body">
                              <input type="hidden" name="id" value="<?= $row['id'] ?>">
                              <div class="mb-3">
                                  <label>Fund Name</label>
                                  <input type="text" name="name" class="form-control" value="<?= $row['name'] ?>" required>
                              </div>
                              <div class="mb-3">
                                  <label>Status</label>
                                  <select name="status" class="form-select" required>
                                      <option value="Active" <?= $row['status']=='Active'?'selected':'' ?>>Active</option>
                                      <option value="Inactive" <?= $row['status']=='Inactive'?'selected':'' ?>>Inactive</option>
                                  </select>
                              </div>
                              <div class="mb-3">
                                  <label>Collected Amount</label>
                                  <input type="number" name="collected_amount" class="form-control" value="<?= $row['collected_amount'] ?>" step="0.01" required>
                              </div>
                          </div>
                          <div class="modal-footer">
                              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                              <button type="submit" name="editFund" class="btn btn-primary">Save Changes</button>
                          </div>
                          </form>
                        </div>
                      </div>
                    </div>

                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
