<?php
include("config.php");
?>

<style>
.causes-item .progress {
    height: 10px;
    border-radius: 0;
    margin-bottom: 20px;
}
.progress-bar {
    color: white;
    font-weight: bold;
    font-size: 12px;
}
.progress-bar.active {
    background-color: #6c5ce7; /* Active color */
}
.progress-bar.completed {
    background-color: #b0b0b0; /* Gray for completed/inactive */
}
.btn-custom.active {
    background-color: #6c5ce7;
    pointer-events: auto;
}
.btn-custom.completed {
    background-color: #b0b0b0;
    pointer-events: none;
    cursor: default;
}
</style>

<div class="container-fluid causes">
    <div class="container py-5">
        <div class="text-center mx-auto pb-5" style="max-width: 800px;">
            <h5 class="text-uppercase text-custom" style="color: #06cfd3ff!important;font-weight:bold;font-size:20pt;">Recent Causes</h5>
            <h1 class="mb-4">The environment needs our protection</h1>
            <p class="mb-0">Lorem Ipsum is simply dummy text of the printing and typesetting industry...</p>
        </div>
        <div class="row g-2">
            <?php
            $sql = "SELECT * FROM campaigns WHERE status = 'Active'";
            $result = $dms->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $goal = $row["goal_amount"];
                    $raised = $row["total_raised"];
                    $progress_percent = ($goal > 0) ? ($raised / $goal) * 100 : 0;
                    $progress_percent = min($progress_percent, 100); // Cap at 100%

                    // Determine if completed
                    $is_completed = ($progress_percent >= 100);
            ?>
            <div class="col-lg-3 col-md-6">
                <div class="causes-item">
                    <div class="causes-img">
                        <img src="<?= htmlspecialchars($row["file_path"]) ?>" class="img-fluid w-100" alt="Cause Image">
                        <div class="causes-link pb-2 px-3">
                            <small class="text-dark"><i class="fas fa-chart-bar text-custom me-2"></i>Goal: $<?= number_format($goal) ?></small>
                            <small class="text-dark"><i class="fa fa-thumbs-up text-custom me-2"></i>Raised: $<?= number_format($raised) ?></small>
                        </div>
                        <div class="causes-dination p-2">
                            <a class="btn-hover-bg btn btn-custom <?= $is_completed ? 'completed' : 'active' ?>" 
                               href="<?= $is_completed ? '#' : 'donate.php?id=' . $row['id'] ?>">
                               <?= $is_completed ? 'Completed' : 'Donate Now' ?>
                            </a>
                        </div>
                    </div>
                    <div class="progress">
                        <div class="progress-bar <?= $is_completed ? 'completed' : 'active' ?>" role="progressbar" 
                             style="width: <?= $progress_percent ?>%" 
                             aria-valuenow="<?= round($progress_percent) ?>" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                            <span><?= round($progress_percent) ?>%</span>
                        </div>
                    </div>
                    <div class="causes-content p-4">
                        <h4 class="mb-3"><?= htmlspecialchars($row["name"]) ?></h4>
                        <p class="mb-4"><?= htmlspecialchars($row["descriptions"]) ?></p>
                        <a class="btn-hover-bg btn btn-custom <?= $is_completed ? 'completed' : 'active' ?>" 
                           href="<?= $is_completed ? '#' : '#' ?>">
                           <?= $is_completed ? 'Completed' : 'Read More' ?>
                        </a>
                    </div>
                </div>
            </div>
            <?php
                }
            } else {
                echo "<p>No active campaigns found.</p>";
            }

            $dms->close();
            ?>
        </div>
    </div>
</div>
