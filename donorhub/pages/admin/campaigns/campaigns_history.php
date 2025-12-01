<?php
// Ensure this file is included via the placeholder.php to enforce access control
if (!isset($_SESSION['user_role'])) {
    header("Location: login.php");
    exit();
}

// Assume config.php and placeholder.php are in the same directory
include('config.php');
// Fetch data from the campaigns table
$sql = "SELECT `name`, `start_date`, `end_date`, `status`, `total_raised` FROM `campaigns` ORDER BY `end_date` DESC";
$result = $dms->query($sql);
$campaigns = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $campaigns[] = $row;
    }
}
$dms->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Campaign History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
    <div class="container-fluid p-5 my-4">
        <div class="row">
            <div class="col-md-9 mx-auto">
                <h1 class="mb-4">Campaign History</h1>

                <div class="row mb-3 g-3">
                    <div class="col-md-6">
                        <input type="text" id="searchInput" class="form-control" placeholder="Search by campaign name..." />
                    </div>
                    <div class="col-md-4">
                        <input type="month" id="filterMonth" class="form-control" />
                    </div>
                    <div class="col-md-2">
                        <button id="clearFilters" class="btn btn-outline-secondary w-100">Clear</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Campaign Name</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Status</th>
                                <th>Funds Raised</th>
                                <th>Donors</th>
                            </tr>
                        </thead>
                        <tbody id="campaignHistoryBody">
                            <?php if (!empty($campaigns)): ?>
                                <?php foreach ($campaigns as $campaign): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($campaign['name']); ?></td>
                                        <td><?php echo htmlspecialchars($campaign['start_date']); ?></td>
                                        <td><?php echo htmlspecialchars($campaign['end_date']); ?></td>
                                        <td>
                                            <?php
                                                $status = htmlspecialchars($campaign['status']);
                                                $badge_class = ($status == 'Active') ? 'bg-success' : 'bg-secondary';
                                            ?>
                                            <span class="badge <?php echo $badge_class; ?>"><?php echo $status; ?></span>
                                        </td>
                                        <td>$<?php echo number_format($campaign['total_raised']); ?></td>
                                        <td>
                                            <?php
                                            // You'll need to run a subquery or join to get the donor count for each campaign
                                            // As a placeholder, we'll use a static value or a value from the database if available
                                            // Assuming a 'donors_count' field is not available in your provided table structure
                                            // You would need to link donations to campaigns to get this data
                                            echo 'N/A';
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">No campaigns found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script>
        const searchInput = document.getElementById('searchInput');
        const filterMonth = document.getElementById('filterMonth');
        const clearFilters = document.getElementById('clearFilters');
        const tbody = document.getElementById('campaignHistoryBody');

        function filterTable() {
            const searchText = searchInput.value.toLowerCase();
            const monthFilter = filterMonth.value; // "YYYY-MM"

            for (let row of tbody.rows) {
                const campaignName = row.cells[0].textContent.toLowerCase();
                const startDate = row.cells[1].textContent;

                let show = true;

                if (searchText && !campaignName.includes(searchText)) {
                    show = false;
                }

                if (monthFilter && !startDate.startsWith(monthFilter)) {
                    show = false;
                }

                row.style.display = show ? '' : 'none';
            }
        }

        searchInput.addEventListener('input', filterTable);
        filterMonth.addEventListener('change', filterTable);
        clearFilters.addEventListener('click', () => {
            searchInput.value = '';
            filterMonth.value = '';
            filterTable();
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>