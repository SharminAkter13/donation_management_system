<?php
// Start the session if it hasn't been started yet
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Retrieve the user's role and username from the session
$userRole = $_SESSION['user_role'] ?? '';
$username = $_SESSION['user_name'] ?? 'Guest';

// Replicate the role-based page mapping from placeholder.php
$rolePages = [
    'admin' => [
        0 => "pages/admin/admin_dashboard.php",
        1 => "pages/admin/users/add_users.php",
        2 => "pages/admin/users/manage_users.php",
        3 => "pages/admin/users/edit_users.php",
        4 => "pages/admin/users/view_users.php",
        5 => "pages/admin/events/add_events.php",
        6 => "pages/admin/events/manage_events.php",
        7 => "pages/admin/events/events_calendar.php",
        8 => "pages/admin/events/events_history.php",
        9 => "pages/admin/campaigns/add_campaigns.php",
        10 => "pages/admin/campaigns/manage_campaigns.php",
        11 => "pages/admin/campaigns/campaigns_report.php",
        12 => "pages/admin/campaigns/campaigns_history.php",
        13 => "pages/admin/beneficiary/add_beneficiary.php",
        14 => "pages/admin/beneficiary/manage_beneficiary.php",
        15 => "pages/admin/beneficiary/beneficiary_report.php",
        16 => "pages/admin/beneficiary/beneficiary_history.php",
        17 => "pages/admin/donations/add_donations.php",
        18 => "pages/admin/donations/manage_donations.php",
        19 => "pages/admin/donations/donations_report.php",
        20 => "pages/admin/donations/donations_history.php",
        21 => "pages/admin/pledges/add_pledges.php",
        22 => "pages/admin/pledges/manage_pledges.php",
        23 => "pages/admin/pledges/pledges_summery_analyticts.php",
        24 => "pages/admin/volunteers/add_volunteers.php",
        25 => "pages/admin/volunteers/manage_volunteers.php",
        26 => "pages/admin/volunteers/volunteers_summery_analyticts.php",
        27 => "pages/admin/logout/logout.php",
        28 => "pages/admin/profile/user_profile.php",
        29 => "pages/admin/funds/manage_funds.php",
        30 => "pages/admin/funds/funds_reports.php",
        31 => "pages/admin/reports/reports.php",
        32 => "pages/admin/transactions/manage_transactions.php",
        33 => "pages/admin/transactions/transaction_reports.php",
        34 => "pages/admin/role/role_approve.php",
                35 => "pages/admin/volunteers/dashboard.php",

    ],
    'beneficiary' => [
        7 => "pages/admin/events/events_calendar.php",
        8 => "pages/admin/events/events_history.php",
        13 => "pages/admin/beneficiary/add_beneficiary.php",
        14 => "pages/admin/beneficiary/manage_beneficiary.php",
        15 => "pages/admin/beneficiary/beneficiary_report.php",
        16 => "pages/admin/beneficiary/beneficiary_history.php",
        28 => "pages/admin/profile/user_profile.php",
        27 => "pages/admin/logout/logout.php",
        31 => "pages/admin/reports/reports.php",
        12 => "pages/admin/campaigns/campaigns_history.php",
    ],
    'campaign_manager' => [
        5 => "pages/admin/events/add_events.php",
        6 => "pages/admin/events/manage_events.php",
        7 => "pages/admin/events/events_calendar.php",
        8 => "pages/admin/events/events_history.php",
        9 => "pages/admin/campaigns/add_campaigns.php",
        10 => "pages/admin/campaigns/manage_campaigns.php",
        11 => "pages/admin/campaigns/campaigns_report.php",
        12 => "pages/admin/campaigns/campaigns_history.php",
        28 => "pages/admin/profile/user_profile.php",
        27 => "pages/admin/logout/logout.php",
        33 => "pages/admin/transactions/transaction_reports.php",
        31 => "pages/admin/reports/reports.php",
    ],
    'donor' => [
        7 => "pages/admin/events/events_calendar.php",
        8 => "pages/admin/events/events_history.php",
        17 => "pages/admin/donations/add_donations.php",
        18 => "pages/admin/donations/manage_donations.php",
        19 => "pages/admin/donations/donations_report.php",
        20 => "pages/admin/donations/donations_history.php",
        22 => "pages/admin/pledges/manage_pledges.php",
        23 => "pages/admin/pledges/pledges_summery_analyticts.php",
        28 => "pages/admin/profile/user_profile.php",
        27 => "pages/admin/logout/logout.php",
        32 => "pages/admin/transactions/manage_transactions.php",
        33 => "pages/admin/transactions/transaction_reports.php",
        35 => "pages/admin/transactions/generate_receipt.php",
        31 => "pages/admin/reports/reports.php",
    ],
    'volunteer' => [
        5 => "pages/admin/events/add_events.php",
        6 => "pages/admin/events/manage_events.php",
        7 => "pages/admin/events/events_calendar.php",
        8 => "pages/admin/events/events_history.php",
        24 => "pages/admin/volunteers/add_volunteers.php",
        25 => "pages/admin/volunteers/manage_volunteers.php",
        26 => "pages/admin/volunteers/volunteers_summery_analyticts.php",
        28 => "pages/admin/profile/user_profile.php",
        27 => "pages/admin/logout/logout.php",
        33 => "pages/admin/transactions/transaction_reports.php",
        31 => "pages/admin/reports/reports.php",
                35 => "pages/admin/volunteers/dashboard.php",

    ],
];

// Helper function to check if a user role has permission to a specific page number
function canAccess($page, $role, $rolePages) {
    return isset($rolePages[$role][$page]);
}

?>

<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="index.php" class="brand-link">
        <img src="assets/dist/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light"> DonorHub</span>
    </a>

    <div class="sidebar">
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="assets/dist/img/avatar2.png" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                <a href="#" class="d-block fw-bold" id="username" style="text-decoration: none;">
                    <?php echo htmlspecialchars($username); ?>
                </a>
                <a href="#" class="d-block">
                    <i class="fa fa-circle text-success"></i>
                    <?php echo htmlspecialchars($userRole); ?>
                </a>
            </div>
        </div>

        <div class="form-inline">
            <div class="input-group" data-widget="sidebar-search">
                <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
                <div class="input-group-append">
                    <button class="btn btn-sidebar">
                        <i class="fas fa-search fa-fw"></i>
                    </button>
                </div>
            </div>
        </div>

        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

                <?php if (canAccess(0, $userRole, $rolePages)): ?>
                    <li class="nav-item">
                        <a href="home.php?page=0" class="nav-link active">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if (canAccess(1, $userRole, $rolePages) || canAccess(2, $userRole, $rolePages) || canAccess(4, $userRole, $rolePages)): ?>
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-users"></i>
                            <p>
                                Users
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <?php if (canAccess(1, $userRole, $rolePages)): ?>
                                <li class="nav-item">
                                    <a href="home.php?page=1" class="nav-link">
                                        <i class="fas fa-user-plus nav-icon"></i>
                                        <p>Add User</p>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <?php if (canAccess(2, $userRole, $rolePages)): ?>
                                <li class="nav-item">
                                    <a href="home.php?page=2" class="nav-link">
                                        <i class="fas fa-user-edit nav-icon"></i>
                                        <p>Manage Users</p>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <?php if (canAccess(4, $userRole, $rolePages)): ?>
                                <li class="nav-item">
                                    <a href="home.php?page=4" class="nav-link">
                                        <i class="fas fa-user-friends nav-icon"></i>
                                        <p>View Users</p>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>

                <?php if (canAccess(17, $userRole, $rolePages) || canAccess(18, $userRole, $rolePages) || canAccess(19, $userRole, $rolePages) || canAccess(20, $userRole, $rolePages)): ?>
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-donate"></i>
                            <p>
                                Donations
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <?php if (canAccess(17, $userRole, $rolePages)): ?>
                                <li class="nav-item">
                                    <a href="home.php?page=17" class="nav-link">
                                        <i class="fas fa-hand-holding-usd nav-icon"></i>
                                        <p>Add Donation</p>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <?php if (canAccess(18, $userRole, $rolePages)): ?>
                                <li class="nav-item">
                                    <a href="home.php?page=18" class="nav-link">
                                        <i class="fas fa-cogs nav-icon"></i>
                                        <p>Manage Donations</p>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <?php if (canAccess(19, $userRole, $rolePages)): ?>
                                <li class="nav-item">
                                    <a href="home.php?page=19" class="nav-link">
                                        <i class="fas fa-chart-line nav-icon"></i>
                                        <p>Donation Reports</p>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <?php if (canAccess(20, $userRole, $rolePages)): ?>
                                <li class="nav-item">
                                    <a href="home.php?page=20" class="nav-link">
                                        <i class="fas fa-history nav-icon"></i>
                                        <p>Donation History</p>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>

                <?php if (canAccess(13, $userRole, $rolePages) || canAccess(14, $userRole, $rolePages) || canAccess(15, $userRole, $rolePages) || canAccess(16, $userRole, $rolePages)): ?>
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-users-cog"></i>
                            <p>
                                Beneficiaries
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <?php if (canAccess(13, $userRole, $rolePages)): ?>
                                <li class="nav-item">
                                    <a href="home.php?page=13" class="nav-link">
                                        <i class="fas fa-user-plus nav-icon"></i>
                                        <p>Add Beneficiary</p>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <?php if (canAccess(14, $userRole, $rolePages)): ?>
                                <li class="nav-item">
                                    <a href="home.php?page=14" class="nav-link">
                                        <i class="fas fa-user-edit nav-icon"></i>
                                        <p>Manage Beneficiaries</p>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <?php if (canAccess(15, $userRole, $rolePages)): ?>
                                <li class="nav-item">
                                    <a href="home.php?page=15" class="nav-link">
                                        <i class="fas fa-chart-bar nav-icon"></i>
                                        <p>Beneficiary Reports</p>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <?php if (canAccess(16, $userRole, $rolePages)): ?>
                                <li class="nav-item">
                                    <a href="home.php?page=16" class="nav-link">
                                        <i class="fas fa-history nav-icon"></i>
                                        <p>Beneficiary History</p>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>

                <?php if (canAccess(29, $userRole, $rolePages) || canAccess(30, $userRole, $rolePages)): ?>
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-wallet"></i>
                            <p>
                                Funds
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <?php if (canAccess(29, $userRole, $rolePages)): ?>
                                <li class="nav-item">
                                    <a href="home.php?page=29" class="nav-link">
                                        <i class="fas fa-coins nav-icon"></i>
                                        <p>Manage Funds</p>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <?php if (canAccess(30, $userRole, $rolePages)): ?>
                                <li class="nav-item">
                                    <a href="home.php?page=30" class="nav-link">
                                        <i class="fas fa-file-invoice-dollar nav-icon"></i>
                                        <p>Funds Reports</p>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>

                <?php if (canAccess(21, $userRole, $rolePages) || canAccess(22, $userRole, $rolePages) || canAccess(23, $userRole, $rolePages)): ?>
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-handshake"></i>
                            <p>
                                Pledges
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <?php if (canAccess(21, $userRole, $rolePages)): ?>
                                <li class="nav-item">
                                    <a href="home.php?page=21" class="nav-link">
                                        <i class="fas fa-hand-holding-heart nav-icon"></i>
                                        <p>Add Pledge</p>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <?php if (canAccess(22, $userRole, $rolePages)): ?>
                                <li class="nav-item">
                                    <a href="home.php?page=22" class="nav-link">
                                        <i class="fas fa-tasks nav-icon"></i>
                                        <p>Manage Pledges</p>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <?php if (canAccess(23, $userRole, $rolePages)): ?>
                                <li class="nav-item">
                                    <a href="home.php?page=23" class="nav-link">
                                        <i class="fas fa-chart-pie nav-icon"></i>
                                        <p>Pledges Summary</p>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>

                <?php if (canAccess(9, $userRole, $rolePages) || canAccess(10, $userRole, $rolePages) || canAccess(11, $userRole, $rolePages) || canAccess(12, $userRole, $rolePages)): ?>
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-bullhorn"></i>
                            <p>
                                Campaigns
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <?php if (canAccess(9, $userRole, $rolePages)): ?>
                                <li class="nav-item">
                                    <a href="home.php?page=9" class="nav-link">
                                        <i class="fas fa-plus-square nav-icon"></i>
                                        <p>Add Campaign</p>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <?php if (canAccess(10, $userRole, $rolePages)): ?>
                                <li class="nav-item">
                                    <a href="home.php?page=10" class="nav-link">
                                        <i class="fas fa-tasks nav-icon"></i>
                                        <p>Manage Campaigns</p>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <?php if (canAccess(11, $userRole, $rolePages)): ?>
                                <li class="nav-item">
                                    <a href="home.php?page=11" class="nav-link">
                                        <i class="fas fa-chart-bar nav-icon"></i>
                                        <p>Campaign Reports</p>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <?php if (canAccess(12, $userRole, $rolePages)): ?>
                                <li class="nav-item">
                                    <a href="home.php?page=12" class="nav-link">
                                        <i class="fas fa-history nav-icon"></i>
                                        <p>Campaign History</p>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>

                <?php if (canAccess(24, $userRole, $rolePages) || canAccess(25, $userRole, $rolePages) || canAccess(26, $userRole, $rolePages)): ?>
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-hands-helping"></i>
                            <p>
                                Volunteers
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <?php if (canAccess(24, $userRole, $rolePages)): ?>
                                <li class="nav-item">
                                    <a href="home.php?page=35" class="nav-link">
                                        <i class="fas fa-user-plus nav-icon"></i>
                                        <p> Volunteer Dashboard</p>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <?php if (canAccess(24, $userRole, $rolePages)): ?>
                                <li class="nav-item">
                                    <a href="home.php?page=24" class="nav-link">
                                        <i class="fas fa-user-plus nav-icon"></i>
                                        <p>Add Volunteer</p>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <?php if (canAccess(25, $userRole, $rolePages)): ?>
                                <li class="nav-item">
                                    <a href="home.php?page=25" class="nav-link">
                                        <i class="fas fa-user-edit nav-icon"></i>
                                        <p>Manage Volunteers</p>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <?php if (canAccess(26, $userRole, $rolePages)): ?>
                                <li class="nav-item">
                                    <a href="home.php?page=26" class="nav-link">
                                        <i class="fas fa-chart-line nav-icon"></i>
                                        <p>Volunteers Summary</p>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>

                <?php if (canAccess(31, $userRole, $rolePages)): ?>
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-chart-area"></i>
                            <p>
                                Overall Reports
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="home.php?page=31" class="nav-link">
                                    <i class="fas fa-file-alt nav-icon"></i>
                                    <p>Reports</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>
                
                <?php if (canAccess(32, $userRole, $rolePages) || canAccess(33, $userRole, $rolePages)): ?>
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-exchange-alt"></i>
                            <p>
                                Transactions
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <?php if (canAccess(32, $userRole, $rolePages)): ?>
                                <li class="nav-item">
                                    <a href="home.php?page=32" class="nav-link">
                                        <i class="fas fa-credit-card nav-icon"></i>
                                        <p>Transaction Management</p>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <?php if (canAccess(33, $userRole, $rolePages)): ?>
                                <li class="nav-item">
                                    <a href="home.php?page=33" class="nav-link">
                                        <i class="fas fa-file-invoice nav-icon"></i>
                                        <p>Transaction Reports</p>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>

                <?php if (canAccess(34, $userRole, $rolePages)): ?>
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-user-tag"></i>
                            <p>
                                Role Management
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="home.php?page=34" class="nav-link">
                                    <i class="fas fa-user-check nav-icon"></i>
                                    <p>Role Approve</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>

                <?php if (canAccess(5, $userRole, $rolePages) || canAccess(6, $userRole, $rolePages) || canAccess(7, $userRole, $rolePages) || canAccess(8, $userRole, $rolePages)): ?>
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-calendar-alt"></i>
                            <p>
                                Events
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <?php if (canAccess(5, $userRole, $rolePages)): ?>
                                <li class="nav-item">
                                    <a href="home.php?page=5" class="nav-link">
                                        <i class="fas fa-plus-square nav-icon"></i>
                                        <p>Add Event</p>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <?php if (canAccess(6, $userRole, $rolePages)): ?>
                                <li class="nav-item">
                                    <a href="home.php?page=6" class="nav-link">
                                        <i class="fas fa-tasks nav-icon"></i>
                                        <p>Manage Events</p>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <?php if (canAccess(7, $userRole, $rolePages)): ?>
                                <li class="nav-item">
                                    <a href="home.php?page=7" class="nav-link">
                                        <i class="fas fa-calendar-check nav-icon"></i>
                                        <p>Events Calendar</p>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <?php if (canAccess(8, $userRole, $rolePages)): ?>
                                <li class="nav-item">
                                    <a href="home.php?page=8" class="nav-link">
                                        <i class="fas fa-history nav-icon"></i>
                                        <p>Events History</p>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>
                
                <?php if (canAccess(28, $userRole, $rolePages)): ?>
                    <li class="nav-item">
                        <a href="home.php?page=28" class="nav-link">
                            <i class="nav-icon fas fa-user-circle"></i>
                            <p>Profile</p>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if (canAccess(27, $userRole, $rolePages)): ?>
                    <li class="nav-item">
                        <a href="home.php?page=27" class="nav-link">
                            <i class="nav-icon fas fa-sign-out-alt"></i>
                            <p>Logout</p>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</aside>