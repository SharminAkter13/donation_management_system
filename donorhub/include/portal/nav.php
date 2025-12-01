<?php
// Start the session at the beginning of the file if not already started.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$dashboard_link = "dashboard.php"; // Default dashboard link

if (isset($_SESSION['user_role'])) {
    switch ($_SESSION['user_role']) {
        case 'admin':
            $dashboard_link = "home.php?page=0";
            break;
        case 'beneficiary':
            $dashboard_link = "home.php?page=6";
            break;
        case 'campaign_manager':
            $dashboard_link = "home.php?page=11";
            break;
        case 'donor':
            $dashboard_link = "home.php?page=20";
            break;
        case 'volunteer':
            $dashboard_link = "home.php?page=25";
            break;
        default:
            $dashboard_link = "home.php";
            break;
    }
}
?>
<div class="navbar navbar-expand-lg bg-dark navbar-dark">
    <div class="container-fluid">
        <a href="#home" class="navbar-brand">DonorHub</a> <button type="button" class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-between" id="navbarCollapse">
            <div class="navbar-nav ms-auto">
                <a href="#home" class="nav-item nav-link active">Home</a>
                <a href="#about" class="nav-item nav-link">About</a>
                <a href="#causes" class="nav-item nav-link">Causes</a>
                <a href="#events" class="nav-item nav-link">Events</a>
                <a href="#blog" class="nav-item nav-link">Blog</a>
                
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">Pages</a>
                    <div class="dropdown-menu">
                        <a href="pages/portal/single.php" class="dropdown-item">Detail Page</a>
                        <a href="#services" class="dropdown-item">What We Do</a> <a href="#team" class="dropdown-item">Meet The Team</a>   <a href="#donate" class="dropdown-item">Donate Now</a>   <a href="#volunteer" class="dropdown-item">Become A Volunteer</a> </div>
                </div>
                
                <a href="#contact" class="nav-item nav-link">Contact</a>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="<?= htmlspecialchars($dashboard_link) ?>" class="nav-item nav-link">Dashboard</a>
                    <a href="home.php?page=27" class="nav-item nav-link">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="nav-item nav-link">Sign In</a>
                    <a href="reg.php" class="nav-item nav-link">Sign Up</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>