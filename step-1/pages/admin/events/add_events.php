<?php
include('config.php');

// Initialize variables
$errors = [];
$success = false;
$mysqli = $dms; // DB connection

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $date = $_POST['date'] ?? '';
    $descriptions = trim($_POST['descriptions'] ?? '');
    $status = $_POST['status'] ?? 'Inactive';

    // Image upload handling
    $image_url = '';
    if (!empty($_FILES['image']['name'])) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $fileName = time() . "_" . basename($_FILES["image"]["name"]);
        $targetFilePath = $targetDir . $fileName;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
            $image_url = $targetFilePath;
        } else {
            $errors[] = "Failed to upload image.";
        }
    }

    // Validation
    if (empty($name)) $errors[] = "Please enter an event name.";
    if (empty($location)) $errors[] = "Please enter an event location.";
    if (empty($date)) $errors[] = "Please select an event date.";
    if (empty($descriptions)) $errors[] = "Please enter event descriptions.";

    // If valid, insert into DB
    if (empty($errors)) {
        $stmt = $mysqli->prepare("INSERT INTO events (name, location, descriptions, date, image_url, status) VALUES (?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            die("Prepare failed: " . $mysqli->error);
        }
        $stmt->bind_param("ssssss", $name, $location, $descriptions, $date, $image_url, $status);

        if ($stmt->execute()) {
            $success = true;
        } else {
            $errors[] = "Error adding event: " . $stmt->error;
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
  <title>Add New Event</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <!-- AdminLTE Theme -->
  <link rel="stylesheet" href="assets/dist/css/adminlte.min.css">
</head>
<body class="hold-transition">
  <div class="container-fluid p-5">
    <!-- Page Header -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Add New Event</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="home.php">Home</a></li>
              <li class="breadcrumb-item active">Add Event</li>
            </ol>
          </div>
        </div>
      </div>
    </section>

    <!-- Main Content -->
    <section class="content">
      <div class="container-fluid">
        <div class="card shadow-sm">
          <div class="card-header bg-success text-white">
            <h3 class="card-title">Event Details</h3>
          </div>
          <div class="card-body">
            <!-- Success/Error Messages -->
            <?php if ($success): ?>
              <div class="alert alert-success">✅ Event added successfully!</div>
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

            <form method="post" action="" enctype="multipart/form-data" novalidate>
              <!-- Event Name -->
              <div class="mb-3">
                <label for="name" class="form-label">Event Name</label>
                <input type="text" class="form-control" id="name" name="name" required
                  value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" />
              </div>

              <!-- Location -->
              <div class="mb-3">
                <label for="location" class="form-label">Location</label>
                <input type="text" class="form-control" id="location" name="location" required
                  value="<?= htmlspecialchars($_POST['location'] ?? '') ?>" />
              </div>

              <!-- Description -->
              <div class="mb-3">
                <label for="descriptions" class="form-label">Descriptions</label>
                <textarea class="form-control" id="descriptions" name="descriptions" rows="4" required><?= htmlspecialchars($_POST['descriptions'] ?? '') ?></textarea>
              </div>

              <!-- Date -->
              <div class="mb-3">
                <label for="date" class="form-label">Date and Time</label>
                <input type="datetime-local" class="form-control" id="date" name="date" required
                  value="<?= htmlspecialchars($_POST['date'] ?? '') ?>" />
              </div>

              <!-- Image -->
              <div class="mb-3">
                <label for="image" class="form-label">Event Image</label>
                <input type="file" class="form-control" id="image" name="image" accept="image/*" />
              </div>

              <!-- Status -->
              <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                  <option value="Active" <?= (($_POST['status'] ?? '') === 'Active') ? 'selected' : '' ?>>Active</option>
                  <option value="Inactive" <?= (($_POST['status'] ?? '') === 'Inactive') ? 'selected' : '' ?>>Inactive</option>
                </select>
              </div>

              <button type="submit" class="btn btn-success">Save Event</button>
              <a href="manage_events.php" class="btn btn-secondary ms-2">Cancel</a>
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
