<?php
include("config.php");

if (!isset($_SESSION['user_role'])) {
    header("Location: login.php");
    exit();
}

$userRole = $_SESSION['user_role'];

$page = isset($_GET['page']) ? (int)$_GET['page'] : 0;

function safe_include($path) {
    if (file_exists($path)) {
        include($path);
    } else {
        echo '<div class="alert alert-danger">Error: Page not found.</div>';
    }
}

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
        33 => "pages/admin/transactions/generate_receipt.php",
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

    // Check if the page exists for the user's role
    if (isset($rolePages[$userRole][$page])) {
        safe_include($rolePages[$userRole][$page]);
    } else {
        echo '<div class="alert alert-danger">You do not have permission to view this page.</div>';
    }

?>
