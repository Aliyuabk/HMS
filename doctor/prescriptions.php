<?php
session_start();
require_once('include/config.php');

// Check if doctor is logged in
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor'){
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$sql = mysqli_query($con, "SELECT * FROM doctor WHERE id = '$user_id'");
$user_data = mysqli_fetch_array($sql);

$status = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

$query = "
    SELECT pr.*, p.first_name, p.last_name, p.ehr_no, a.appointment_date
    FROM prescriptions pr
    JOIN patients p ON pr.patient_id = p.id
    LEFT JOIN appointments a ON pr.appointment_id = a.id
    WHERE pr.doctor_id = '$user_id'
";

if(!empty($status)) {
    $query .= " AND pr.status = '$status'";
}

if(!empty($search)) {
    $query .= " AND (p.first_name LIKE '%$search%' OR p.last_name LIKE '%$search%' OR p.ehr_no LIKE '%$search%')";
}

$query .= " ORDER BY pr.created_at DESC";
$result = mysqli_query($con, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Doctor | Manage Prescriptions</title>
    <?php include 'include/css.php'; ?>
    <style>
        .status-badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-pending { background: #f0ad4e; color: white; }
        .status-completed { background: #5cb85c; color: white; }
        .status-cancelled { background: #d9534f; color: white; }
    </style>
</head>
<body>
<div id="app">
    <div class="sidebar app-aside" id="sidebar">
        <?php include('include/sidebar.php'); ?>
    </div>

    <div class="app-content">
        <?php include 'include/header.php'; ?>
        <div class="main-content">
            <div class="wrap-content container" id="container">
                <section id="page-title">
                    <div class="row">
                        <div class="col-sm-8">
                            <h2 class="mainTitle">Doctor | Manage Prescriptions</h2>
                        </div>
                        <ol class="breadcrumb">
                            <li><span>Doctor</span></li>
                            <li class="active"><span>Prescriptions</span></li>
                        </ol>
                    </div>
                </section>

                <div class="container-fluid container-fullw bg-white">
                    <div class="row">
                        <div class="col-md-12">
                            <!-- Filter Panel -->
                            <div class="panel panel-white">
                                <div class="panel-heading">
                                    <h4 class="panel-title">Filter Prescriptions</h4>
                                </div>
                                <div class="panel-body">
                                    <form method="get" class="form-inline">
                                        <div class="form-group">
                                            <label>Status:</label>
                                            <select name="status" class="form-control">
                                                <option value="">All Status</option>
                                                <option value="pending" <?= ($status=='pending')?'selected':'' ?>>Pending</option>
                                                <option value="completed" <?= ($status=='completed')?'selected':'' ?>>Completed</option>
                                                <option value="cancelled" <?= ($status=='cancelled')?'selected':'' ?>>Cancelled</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <input type="text" name="search" class="form-control" placeholder="Search patient..." value="<?= htmlspecialchars($search) ?>">
                                        </div>
                                        <button type="submit" class="btn btn-primary">Filter</button>
                                        <a href="prescriptions.php" class="btn btn-default">Show All</a>
                                        <a href="add-prescription.php" class="btn btn-success pull-right">
                                            <i class="fa fa-plus"></i> Add New Prescription
                                        </a>
                                    </form>
                                </div>
                            </div>

                            <!-- Prescriptions Table -->
                            <div class="panel panel-white">
                                <div class="panel-heading">
                                    <h4 class="panel-title">Prescriptions List</h4>
                                </div>
                                <div class="panel-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Patient</th>
                                                    <th>Date</th>
                                                    <th>Diagnosis</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $cnt = 1;
                                                if(mysqli_num_rows($result) > 0) {
                                                    while($row = mysqli_fetch_array($result)) {
                                                ?>
                                                <tr>
                                                    <td><?= $cnt ?></td>
                                                    <td>
                                                        <strong><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></strong><br>
                                                        <small>ID: <?= htmlspecialchars($row['ehr_no']) ?></small>
                                                    </td>
                                                    <td><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                                                    <td><?= htmlspecialchars(substr($row['diagnosis'],0,50)) . '...' ?></td>
                                                    <td>
                                                        <span class="status-badge status-<?= $row['status'] ?>">
                                                            <?= ucfirst($row['status']) ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <a href="view-prescription.php?id=<?= $row['id'] ?>" class="btn btn-primary btn-xs"><i class="fa fa-eye"></i> View</a>
                                                            <a href="edit-prescription.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-xs"><i class="fa fa-edit"></i> Edit</a>
                                                            <a href="print-prescription.php?id=<?= $row['id'] ?>" class="btn btn-info btn-xs" target="_blank"><i class="fa fa-print"></i> Print</a>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php $cnt++; } } else { ?>
                                                <tr>
                                                    <td colspan="6" class="text-center">No prescriptions found</td>
                                                </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <?php include 'include/footer.php'; ?>
    <?php include 'include/setting.php'; ?>
</div>

<?php include 'include/js.php'; ?>
</body>
</html>
