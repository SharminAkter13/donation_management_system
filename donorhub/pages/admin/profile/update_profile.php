<?php
// Include the config file
include("config.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $email = $_POST['email'];
    $newPassword = $_POST['password'];

    // Check if a new password was provided
    if (!empty($newPassword)) {
        // Hash the new password securely
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        // Prepare the update statement with password
        $stmt = $dms->prepare("UPDATE users SET first_name=?, last_name=?, email=?, password=? WHERE id=?");
        // "ssssi" means 4 strings and 1 integer
        $stmt->bind_param("ssssi", $firstName, $lastName, $email, $hashedPassword, $user_id);
    } else {
        // Prepare the update statement without password
        $stmt = $dms->prepare("UPDATE users SET first_name=?, last_name=?, email=? WHERE id=?");
        // "sssi" means 3 strings and 1 integer
        $stmt->bind_param("sssi", $firstName, $lastName, $email, $user_id);
    }
    
    // Execute the statement
    if ($stmt->execute()) {
        echo '<div class="alert alert-success">Profile updated successfully!</div>';
    } else {
        echo '<div class="alert alert-danger">Error: ' . $stmt->error . '</div>';
    }
    
    // Close the statement
    $stmt->close();
} else {
    // If the request method is not POST, or required data is missing, show an error
    echo '<div class="alert alert-danger">Invalid request.</div>';
}
?>
