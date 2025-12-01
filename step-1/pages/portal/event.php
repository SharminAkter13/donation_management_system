<?php
include 'config.php'; // your DB connection

// Fetch only active events ordered by date
$query = "SELECT id, name, location, descriptions, date, image_url, status 
          FROM events 
          WHERE status = 'Active' 
          ORDER BY date ASC";
$result = $dms->query($query);

// Error check
if (!$result) {
    die("Query Failed: " . $dms->error);
}
?>

<div class="event">
    <div class="container">
        <div class="section-header text-center">
            <p style="color: #06cfd3ff!important;font-weight:bold;font-size:20pt;">Upcoming Events</p>
            <h2>Be ready for our upcoming charity events</h2>
        </div>
        <div class="row">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="col-lg-6 mb-4">
                    <div class="event-item">
                        <img src="<?php echo htmlspecialchars($row['image_url']); ?>" 
                             alt="<?php echo htmlspecialchars($row['name']); ?>" 
                             style="width:100%; height:250px; object-fit:cover;">
                        <div class="event-content">
                            <div class="event-meta">
                                <p><i class="fa fa-calendar-alt"></i>
                                    <?php echo date("d-M-Y", strtotime($row['date'])); ?>
                                </p>
                                <p><i class="far fa-clock"></i>
                                    <?php echo date("h:i A", strtotime($row['date'])); ?>
                                </p>
                                <p><i class="fa fa-map-marker-alt"></i>
                                    <?php echo htmlspecialchars($row['location']); ?>
                                </p>
                            </div>
                            <div class="event-text">
                                <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                                <p><?php echo nl2br(htmlspecialchars($row['descriptions'])); ?></p>
                                <a class="btn btn-custom" href="event_details.php?id=<?php echo $row['id']; ?>">Join Now</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>
