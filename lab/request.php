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
        header("Location: request.php");
        exit;
    }
}

/* =======================
   FILTER & SEARCH
======================= */
$filter_status = $_GET['status'] ?? 'all';
$search = mysqli_real_escape_string($con, $_GET['search'] ?? '');
$filter_sql = "WHERE lr.status != 'completed'";
if ($filter_status != 'all') $filter_sql .= " AND lr.status='$filter_status'";
if ($search) $filter_sql .= " AND (p.first_name LIKE '%$search%' OR p.last_name LIKE '%$search%' OR p.ehr_no LIKE '%$search%')";

/* =======================
   PAGINATION SETTINGS
======================= */
$limit = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$start = ($page - 1) * $limit;

// Count total filtered requests
$total_filtered = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total FROM lab_requests lr INNER JOIN patients p ON p.id = lr.patient_id $filter_sql"))['total'];
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
    <title>Lab | Pending Requests</title>
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
                        <div class="col-sm-8"><h2 class="mainTitle">Pending & In-Progress Requests</h2></div>
                        <ol class="breadcrumb">
                            <li><span>Laboratory</span></li>
                            <li class="active"><span>Requests</span></li>
                        </ol>
                    </div>
                </section>

                <!-- Filter + Search -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <form method="get" class="form-inline pull-right">
                            <label>Status:</label>
                            <select name="status" class="form-control input-sm" onchange="this.form.submit()">
                                <option value="all" <?= $filter_status=='all'?'selected':''; ?>>All</option>
                                <option value="pending" <?= $filter_status=='pending'?'selected':''; ?>>Pending</option>
                                <option value="in_progress" <?= $filter_status=='in_progress'?'selected':''; ?>>In Progress</option>
                            </select>
                            <input type="text" name="search" class="form-control input-sm" placeholder="Search patient" value="<?= htmlspecialchars($search) ?>">
                            <button class="btn btn-primary btn-sm">Search</button>
                        </form>
                    </div>
                </div>

                <!-- Requests Table -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-white">
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
                                                    <a href="view-request.php?id=<?= $r['id']; ?>" class="btn btn-success btn-xs">Enter Results</a>
                                                    <?php } ?>
                                                
                                                <?php if($r['status']=='in_progress') { ?>
                                                    <a href="request.php?action=completed&id=<?= $r['id']; ?>" class="btn btn-primary btn-xs">Mark Completed</a>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                        <?php } ?>
                                        <?php if(mysqli_num_rows($requests)==0){ ?>
                                        <tr><td colspan="9" class="text-center">No pending or in-progress requests found.</td></tr>
                                        <?php } ?>
                                    </tbody>
                                </table>

                                <!-- Pagination -->
                                <?php if($total_pages>1){ ?>
                                <nav>
                                    <ul class="pagination">
                                        <?php for($p=1;$p<=$total_pages;$p++){ ?>
                                        <li class="<?= $p==$page?'active':''; ?>">
                                            <a href="?page=<?= $p ?>&status=<?= $filter_status ?>&search=<?= urlencode($search) ?>"><?= $p ?></a>
                                        </li>
                                        <?php } ?>
                                    </ul>
                                </nav>
                                <?php } ?>

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
