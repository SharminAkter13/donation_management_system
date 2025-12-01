<?php
include('config.php');

// Initialize variables
$errors = [];
$success = false;
$mysqli = $dms; // Use the provided $dms variable for consistency

// Fetch donors and campaigns for dropdowns
$donors_result = $mysqli->query("SELECT id, name FROM donors ORDER BY name");
$campaigns_result = $mysqli->query("SELECT id, name FROM campaigns ORDER BY name");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data
    $donor_id = $_POST['donor_id'] ?? '';
    $campaign_id = $_POST['campaign_id'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $pledge_amount = floatval($_POST['pledge_amount'] ?? 0);
    $pledge_date = $_POST['pledge_date'] ?? '';
    $status = $_POST['status'] ?? 'Pending';

    // Simple validation
    if (!$donor_id) $errors[] = "Please select a donor.";
    if (!$campaign_id) $errors[] = "Please select a campaign.";
    if (empty($name)) $errors[] = "Please enter a pledge name.";
    if ($pledge_amount <= 0) $errors[] = "Please enter a valid pledge amount.";
    if (empty($pledge_date)) $errors[] = "Please select a pledge date.";

    // If no validation errors, proceed with database insertion
    if (empty($errors)) {
        // Use a prepared statement to prevent SQL injection
        $stmt = $mysqli->prepare("INSERT INTO pledges (name, pledge_amount, pledge_date, status, donor_id, campaign_id) VALUES (?, ?, ?, ?, ?, ?)");
        
        if (!$stmt) {
            die("Prepare failed: " . $mysqli->error);
        }

        // Bind parameters and execute
        // 'sdssii' corresponds to string, double, string, string, integer, integer
        $stmt->bind_param("sdssii", $name, $pledge_amount, $pledge_date, $status, $donor_id, $campaign_id);
        
        // Check for success and close the statement
        if ($stmt->execute()) {
            $success = true;
        } else {
            $errors[] = "Error adding pledge: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Add New Pledge</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <!-- AdminLTE Theme -->
  <link rel="stylesheet" href="assets/dist/css/adminlte.min.css">
</head>
<body class="hold-transition">
  <!-- Page Content Wrapper -->
  <div class="container-fluid p-5" style="min-height: 2838.44px;">
    <!-- Page Header -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Add New Pledge</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="home.php">Home</a></li>
              <li class="breadcrumb-item active">Add Pledge</li>
            </ol>
          </div>
        </div>
      </div>
    </section>

    <!-- Main Content -->
    <section class="content">
      <div class="container-fluid">
        <div class="card shadow-sm">
          <div class="card-header bg-primary text-white">
            <h3 class="card-title">Pledge Details</h3>
          </div>
          <div class="card-body">
            <!-- Success/Error Messages -->
            <?php if ($success): ?>
              <div class="alert alert-success">Pledge added successfully! <a href="manage_pledges.php">Go to Manage Pledges</a></div>
            <?php endif; ?>
            <?php if (!empty($errors)): ?>
              <div class="alert alert-danger">
                <ul class="mb-0">
                  <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            <?php endif; ?>

            <form method="post" action="add_pledges.php" novalidate>
              <!-- Donor Dropdown -->
              <div class="mb-3">
                <label for="donor_id" class="form-label">Donor</label>
                <select class="form-control" id="donor_id" name="donor_id" required>
                  <option value="" disabled selected>-- Choose Donor --</option>
                  <?php while($d = $donors_result->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($d['id']) ?>"><?= htmlspecialchars($d['name']) ?></option>
                  <?php endwhile; ?>
                </select>
              </div>

              <!-- Campaign Dropdown -->
              <div class="mb-3">
                <label for="campaign_id" class="form-label">Campaign</label>
                <select class="form-control" id="campaign_id" name="campaign_id" required>
                  <option value="" disabled selected>-- Choose Campaign --</option>
                  <?php while($c = $campaigns_result->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($c['id']) ?>"><?= htmlspecialchars($c['name']) ?></option>
                  <?php endwhile; ?>
                </select>
              </div>
            
              <!-- Pledge Name -->
              <div class="mb-3">
                <label for="name" class="form-label">Pledge Name</label>
                <input
                  type="text"
                  class="form-control"
                  id="name"
                  name="name"
                  required
                  value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                />
              </div>

              <!-- Pledge Amount -->
              <div class="mb-3">
                <label for="pledge_amount" class="form-label">Pledge Amount ($)</label>
                <input
                  type="number"
                  class="form-control"
                  id="pledge_amount"
                  name="pledge_amount"
                  step="0.01"
                  min="0"
                  required
                  value="<?= htmlspecialchars($_POST['pledge_amount'] ?? '') ?>"
                />
              </div>

              <!-- Pledge Date -->
              <div class="mb-3">
                <label for="pledge_date" class="form-label">Pledge Date</label>
                <input
                  type="datetime-local"
                  class="form-control"
                  id="pledge_date"
                  name="pledge_date"
                  required
                  value="<?= htmlspecialchars($_POST['pledge_date'] ?? '') ?>"
                />
              </div>

              <!-- Status Dropdown -->
              <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-control" id="status" name="status" required>
                  <?php
                    $statuses = ['Pending', 'Approved', 'Completed'];
                    $selectedStatus = $_POST['status'] ?? 'Pending';
                    foreach ($statuses as $st):
                  ?>
                    <option value="<?= $st ?>" <?= ($selectedStatus === $st) ? 'selected' : '' ?>><?= $st ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <button type="submit" class="btn btn-primary">Save Pledge</button>
              <a href="manage_pledges.php" class="btn btn-secondary ms-2">Cancel</a>
            </form>
          </div>
        </div>
      </div>
    </section>
  </div>

  <!-- Bootstrap & AdminLTE Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/dist/js/adminlte.min.js"></script>
</body>
</html>
