<?php
include('config.php');

$result = mysqli_query($dms, "SELECT * FROM beneficiaries ORDER BY id DESC");
$beneficiaries = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $beneficiaries[] = $row;
    }
}
$total_beneficiaries = count($beneficiaries);
?>



    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            border: none;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.05);
            border-radius: 0.75rem;
        }
        .fw-bold {
            font-weight: 700 !important;
        }
    </style>
<!-- Beneficiariers Report Start -->

<div class="container-fluid p-5" style="min-height: 2838.44px;">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Benefciaries Interface</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="home.php">Home</a></li>
              <li class="breadcrumb-item active">Benefciaries</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">

      <!-- Default box -->
      <div class="card">
        <div class="card-header ">
          <h3 class="card-title">Benefciaries Reports</h3>

          <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
              <i class="fas fa-minus"></i>
            </button>
            <button type="button" class="btn btn-tool" data-card-widget="remove" title="Remove">
              <i class="fas fa-times"></i>
            </button>
          </div>
        </div>
        <div class="card-body">

<div class="container-fluid">
    <div class="row">
        <div class="col-md-10 offset-md-1">

            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4 mt-4">
                <h3 class="fw-bold text-primary">Beneficiary Reports</h3>
                <button class="btn btn-success"><i class="bi bi-download"></i> Export Report</button>
            </div>

            <!-- Summary Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted">Total Beneficiaries</h6>
                                <h4 class="fw-bold"><?= $total_beneficiaries ?></h4>
                            </div>
                            <i class="bi bi-people text-primary fs-3"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted">Total Donations Given</h6>
                                <h4 class="fw-bold">$0.00</h4> <!-- Placeholder -->
                            </div>
                            <i class="bi bi-cash-stack text-success fs-3"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted">Active Programs</h6>
                                <h4 class="fw-bold">N/A</h4> <!-- Placeholder -->
                            </div>
                            <i class="bi bi-clipboard-data text-warning fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Form -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <form class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Beneficiary Name</label>
                            <input type="text" class="form-control" placeholder="Enter name">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Program</label>
                            <select class="form-control">
                                <option value="">All</option>
                                <option>Education Support</option>
                                <option>Medical Aid</option>
                                <option>Food Distribution</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Date Range</label>
                            <input type="date" class="form-control">
                        </div>
                        <div class="col-12 text-end">
                            <button class="btn btn-primary"><i class="bi bi-search"></i> Search</button>
                            <button type="reset" class="btn btn-secondary">Reset</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Table -->
            <div class="card border-0 shadow-sm">
                <div class="card-body table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-primary">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Needs</th>
                                <th>Donation Amount</th>
                                <th>Date Provided</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($beneficiaries)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">No beneficiaries found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($beneficiaries as $index => $b): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($b['id']) ?></td>
                                        <td><?= htmlspecialchars($b['name']) ?></td>
                                        <td><?= htmlspecialchars($b['required_support']) ?></td>
                                        <td>$0.00</td> <!-- Placeholder -->
                                        <td>N/A</td> <!-- Placeholder -->
                                        <td><span class="badge bg-secondary">Pending</span></td> <!-- Placeholder -->
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
        </div>
        <!-- /.card-body -->
       
      </div>
      <!-- /.card -->

    </section>
    <!-- /.content -->
  </div>
<!-- Beneficiariers Report End -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
