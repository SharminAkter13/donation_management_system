<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include DB config
include('config.php');

// Ensure only admins can access this page
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../../../index.php');
    exit();
}

// Handle approval
if (isset($_POST['approve'])) {
    $user_id = (int)$_POST['user_id'];
    $new_role_id = (int)$_POST['new_role_id'];

    $stmt = $dms->prepare("UPDATE users SET role_id = ?, requested_role_id = NULL WHERE id = ?");
    $stmt->bind_param("ii", $new_role_id, $user_id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['message'] = "Role for user ID {$user_id} has been approved.";
    header("Location:home.php?page=34");
    exit();
}

// Handle rejection
if (isset($_POST['reject'])) {
    $user_id = (int)$_POST['user_id'];

    $stmt = $dms->prepare("UPDATE users SET requested_role_id = NULL WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['message'] = "Role request for user ID {$user_id} has been rejected.";
    header("Location:home.php?page=34");
    exit();
}

// Fetch users with pending role requests
$query = "
    SELECT 
        u.id, 
        u.first_name, 
        u.last_name, 
        u.email, 
        r_current.name AS current_role_name, 
        r_requested.name AS requested_role_name,
        u.requested_role_id
    FROM users u
    JOIN roles r_current ON u.role_id = r_current.id
    JOIN roles r_requested ON u.requested_role_id = r_requested.id
    WHERE u.requested_role_id IS NOT NULL
";

$result = $dms->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Approve Roles</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../assets/dist/css/adminlte.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="card shadow-lg p-4 rounded-3">
            <h2 class="mb-4 text-center">Manage Role Requests</h2>

            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-success"><?= htmlspecialchars($_SESSION['message']) ?></div>
                <?php unset($_SESSION['message']); ?>
            <?php endif; ?>

            <?php if ($result && $result->num_rows > 0): ?>
                <table class="table table-bordered table-hover">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th>User Name</th>
                            <th>Email</th>
                            <th>Current Role</th>
                            <th>Requested Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                                <td><?= htmlspecialchars($row['email']) ?></td>
                                <td><?= htmlspecialchars($row['current_role_name']) ?></td>
                                <td><?= htmlspecialchars($row['requested_role_name']) ?></td>
                                <td>
                                    <form method="post" action="home.php?page=34">

                                        <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                                        <input type="hidden" name="new_role_id" value="<?= $row['requested_role_id'] ?>">
                                        <button type="submit" name="approve" class="btn btn-success btn-sm me-2">Approve</button>
                                        <button type="submit" name="reject" class="btn btn-danger btn-sm">Reject</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-info text-center">No pending role requests.</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
