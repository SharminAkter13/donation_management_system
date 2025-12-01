<?php
session_start();

// Unset all session variables
$_SESSION = [];

// Destroy the session
session_destroy();

// Get the redirect target from the URL, defaulting to 'root' if not set.
$redirect_to = $_GET['redirect'] ?? 'root';

// Redirect to the appropriate login page based on the 'redirect' parameter.
switch ($redirect_to) {
    case 'admin':
        // Redirects to the admin index page
        header("Location: admin/index.php");
        break;
    case 'campaign_manager':
        // Redirects to the campaign manager index page
        header("Location: manager/index.php");
        break;
    default:
        // Redirects to the main login page for donors, volunteers, and beneficiaries
        header("Location: login.php");
        break;
}
exit;
?>
