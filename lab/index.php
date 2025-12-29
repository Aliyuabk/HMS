<?php
session_start();
require_once('include/config.php');

/* =======================
   AUTH CHECK
======================= */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'lab') {
    header("Location: ../index.php");
    exit;
}

/* =======================
   HANDLE STATUS CHANGE
======================= */
if (isset($_GET['action'], $_GET['id'])) {
    $request_id = intval($_GET['id']);
    $action = $_GET['action'];

    if (in_array($action, ['in_progress','completed'])) {
        mysqli_query($con, "UPDATE lab_requests SET status='$action', updated_at=NOW() WHERE id='$request_id'");
        header("Location: index.php");
        exit;
    }
}

/* =======================
   FILTER STATUS
======================= */
$filter_status = isset($_GET['status']) ? $_GET['status'] : 'all';
$filter_sql = ($filter_status && $filter_status != 'all') ? "WHERE lr.status='$filter_status'" : "";

/* =======================
   CARDS DATA
======================= */
$total_requests = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total FROM lab_requests"))['total'];
$pending_requests = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total FROM lab_requests WHERE status='pending'"))['total'];
$inprogress_requests = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total FROM lab_requests WHERE status='in_progress'"))['total'];
$completed_requests = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total FROM lab_requests WHERE status='completed'"))['total'];
$pending_payments = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total FROM lab_payment_request WHERE status='pending'"))['total'];

/* =======================
   PAGINATION SETTINGS
======================= */
$limit = 10; // requests per page
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$start = ($page - 1) * $limit;

// Count total filtered requests
$total_filtered = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total FROM lab_requests lr $filter_sql"))['total'];
$total_pages = ceil($total_filtered / $limit);

/* =======================
   FETCH LAB REQUESTS
======================= */
$requests = mysqli_query($con, "
    SELECT lr.*, p.first_name, p.last_name, p.ehr_no, a.appointment_date,
           COALESCE(lp.amount, 0) as payment_amount,
           COALESCE(lp.status, 'pending') as payment_status
    FROM lab_requests lr
    INNER JOIN patients p ON p.id = lr.patient_id
    LEFT JOIN appointments a ON a.id = lr.appointment_id
    LEFT JOIN lab_payment_request lp ON lp.lab_request_id = lr.id
    $filter_sql
    ORDER BY lr.created_at DESC
    LIMIT $start, $limit
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Lab | Dashboard</title>
    <?php include 'include/css.php'; ?>
</head>
<body>
<div id="app">
    <div class="sidebar app-aside" id="sidebar"><?php include 'include/sidebar.php'; ?></div>
    <div class="app-content">
        <?php include 'include/header.php'; ?>

        <div class="main-content">
            <div class="wrap-content container" id="container">

                <!-- Page Title -->
                <section id="page-title">
                    <div class="row">
                        <div class="col-sm-8">
                            <h2 class="mainTitle">Lab Dashboard</h2>
                        </div>
                        <ol class="breadcrumb">
                            <li><span>Laboratory</span></li>
                            <li class="active"><span>Dashboard</span></li>
                        </ol>
                    </div>
                </section>

                <!-- Summary Cards -->
                <!-- Summary Cards -->
				<div class="row">
					<div class="col-md-2 col-sm-4">
						<div style="background-color:#337ab7;color:#fff;padding:15px;border-radius:5px;text-align:center;margin-bottom:10px;">
							<h5>Total Requests</h5>
							<h3><?= $total_requests ?></h3>
						</div>
					</div>
					<div class="col-md-2 col-sm-4">
						<div style="background-color:#f0ad4e;color:#fff;padding:15px;border-radius:5px;text-align:center;margin-bottom:10px;">
							<h5>Pending</h5>
							<h3><?= $pending_requests ?></h3>
						</div>
					</div>
					<div class="col-md-2 col-sm-4">
						<div style="background-color:#5bc0de;color:#fff;padding:15px;border-radius:5px;text-align:center;margin-bottom:10px;">
							<h5>In Progress</h5>
							<h3><?= $inprogress_requests ?></h3>
						</div>
					</div>
					<div class="col-md-2 col-sm-4">
						<div style="background-color:#5cb85c;color:#fff;padding:15px;border-radius:5px;text-align:center;margin-bottom:10px;">
							<h5>Completed</h5>
							<h3><?= $completed_requests ?></h3>
						</div>
					</div>
					<div class="col-md-2 col-sm-4">
						<div style="background-color:#d9534f;color:#fff;padding:15px;border-radius:5px;text-align:center;margin-bottom:10px;">
							<h5>Pending Payments</h5>
							<h3><?= $pending_payments ?></h3>
						</div>
					</div>
				</div>
                <!-- Table + Filter -->
                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="panel panel-white">
                            <div class="panel-heading">
                                <h5 class="panel-title">Lab Requests</h5>
                                <div class="pull-right">
                                    <form method="get" class="form-inline">
                                        <label>Status:</label>
                                        <select name="status" class="form-control input-sm" onchange="this.form.submit()">
                                            <option value="all" <?= $filter_status=='all'?'selected':''; ?>>All</option>
                                            <option value="pending" <?= $filter_status=='pending'?'selected':''; ?>>Pending</option>
                                            <option value="in_progress" <?= $filter_status=='in_progress'?'selected':''; ?>>In Progress</option>
                                            <option value="completed" <?= $filter_status=='completed'?'selected':''; ?>>Completed</option>
                                        </select>
                                    </form>
                                </div>
                            </div>
                            <div class="panel-body">
                                <table class="table table-bordered table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Patient</th>
                                            <th>EHR No</th>
                                            <th>Appointment Date</th>
                                            <th>Request Date</th>
                                            <th>Status</th>
                                            <th>Payment Status</th>
                                            <th>Amount (₦)</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $i = $start + 1; while($r = mysqli_fetch_assoc($requests)) { ?>
                                        <tr>
                                            <td><?= $i++; ?></td>
                                            <td><?= htmlspecialchars($r['first_name'].' '.$r['last_name']); ?></td>
                                            <td><?= htmlspecialchars($r['ehr_no']); ?></td>
                                            <td><?= $r['appointment_date'] ? date('d M Y', strtotime($r['appointment_date'])) : 'N/A'; ?></td>
                                            <td><?= date('d M Y', strtotime($r['created_at'])); ?></td>
                                            <td><?= ucfirst($r['status']); ?></td>
                                            <td><?= ucfirst($r['payment_status']); ?></td>
                                            <td><?= number_format($r['payment_amount'],2); ?></td>
                                            <td>
                                                <?php if($r['status']=='pending') { ?>
                                                    <a href="?action=in_progress&id=<?= $r['id']; ?>" class="btn btn-warning btn-xs">Start</a>
                                                <?php } ?>
                                                <a href="view-request.php?id=<?= $r['id']; ?>" class="btn btn-success btn-xs">Enter Results</a>
                                                <?php if($r['status']=='in_progress') { ?>
                                                    <a href="?action=completed&id=<?= $r['id']; ?>" class="btn btn-primary btn-xs">Mark Completed</a>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>

                                <!-- Pagination -->
                                <nav>
                                    <ul class="pagination">
                                        <?php for($p=1;$p<=$total_pages;$p++) { ?>
                                        <li class="<?= $p==$page?'active':''; ?>">
                                            <a href="?page=<?= $p; ?>&status=<?= $filter_status ?>"><?= $p; ?></a>
                                        </li>
                                        <?php } ?>
                                    </ul>
                                </nav>

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
