<?php
include('config.php');

// Fetch ongoing/upcoming events
$today = date('Y-m-d');
$sql = "SELECT * FROM events WHERE date >= '$today' ORDER BY date ASC";
$result = $dms->query($sql);
$events = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
}
$dms->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Events Calendar</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
<style>
body {
    padding-top: 60px;
}
.sidebar {
    width: 220px;
    position: fixed;
    top: 0;
    left: 0;
    height: 100%;
    background-color: #343a40;
    color: white;
    padding-top: 1rem;
}
.content {
    margin-left: 150px;
    padding: 2rem;
}
.card-calendar {
    height: 400px;
    overflow-y: auto;
}
</style>
</head>
<body>

<!-- Main content -->
<div class="container-fluid p-5">
    <h1 class="mb-4">Ongoing & Upcoming Events</h1>

    <div class="row">
        <div class="col-lg-8">
            <!-- Calendar Grid -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    Calendar View
                </div>
                <div class="card-body card-calendar">
                    <table class="table table-bordered text-center">
                        <thead>
                            <tr>
                                <th>Event</th>
                                <th>Location</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($events)) : ?>
                            <?php foreach ($events as $event) : ?>
                                <tr>
                                    <td><?= htmlspecialchars($event['name']) ?></td>
                                    <td><?= htmlspecialchars($event['location']) ?></td>
                                    <td><?= date('d M Y', strtotime($event['date'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="3">No ongoing events.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Sidebar Event Summary -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">Event Summary</div>
                <div class="card-body">
                    <p>Total Ongoing Events: <strong><?= count($events) ?></strong></p>
                    <?php if (!empty($events)) : ?>
                        <ul class="list-group">
                        <?php foreach ($events as $event) : ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?= htmlspecialchars($event['name']) ?>
                                <span class="badge bg-primary rounded-pill"><?= date('d M', strtotime($event['date'])) ?></span>
                            </li>
                        <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>No events scheduled.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
