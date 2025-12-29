<?php
session_start();
require_once('include/config.php');

/* =======================
   AUTH CHECK
======================= */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'lab') {
    header("Location: index.php");
    exit;
}

/* =======================
   SEARCH & PAGINATION
======================= */
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$limit = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$start = ($page - 1) * $limit;

/* =======================
   FETCH CARDS DATA
======================= */
// Total activities
$total_activities = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total FROM lab_activity_log"))['total'];

// Total samples collected
 

// Total results submitted
$total_results = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total FROM lab_request_tests WHERE status='completed'"))['total'];

// Total payment requests
$total_payments = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total FROM lab_payment_request"))['total'];

/* =======================
   FETCH ACTIVITY LOGS
======================= */
$logs_sql = "
    SELECT 
        lr.patient_id,
        p.first_name AS patient_first,
        p.last_name AS patient_last,
        GROUP_CONCAT(DISTINCT t.test_name SEPARATOR ', ') AS tests,
        l.action,
        lr.status AS request_status,
        CONCAT(u.first_name,' ',u.last_name) AS staff_name,
        DATE(l.created_at) AS log_date
    FROM lab_activity_log l
    INNER JOIN lab_requests lr ON lr.id = l.lab_request_id
    INNER JOIN patients p ON p.id = lr.patient_id
    LEFT JOIN lab_request_tests t ON t.id=l.test_id
    LEFT JOIN lab u ON u.id = l.performed_by
    WHERE 1
";

if($search){
    $search_safe = mysqli_real_escape_string($con, $search);
    $logs_sql .= " AND (p.first_name LIKE '%$search_safe%' 
                        OR p.last_name LIKE '%$search_safe%' 
                        OR t.test_name LIKE '%$search_safe%' 
                        OR l.action LIKE '%$search_safe%')";
}

// Group multiple requests for same patient/date/action
$logs_sql .= " GROUP BY lr.patient_id, l.action, DATE(l.created_at)
               ORDER BY l.created_at DESC
               LIMIT $start, $limit";

$logs = mysqli_query($con, $logs_sql);
if(!$logs){
    die("Query failed: " . mysqli_error($con));
}

// Total logs count for pagination
$total_filtered_sql = "
    SELECT COUNT(DISTINCT lr.patient_id, l.action, DATE(l.created_at)) as total
    FROM lab_activity_log l
    INNER JOIN lab_requests lr ON lr.id = l.lab_request_id
    INNER JOIN patients p ON p.id = lr.patient_id
    LEFT JOIN lab_request_tests t ON t.id=l.test_id
    LEFT JOIN lab u ON u.id = l.performed_by
    WHERE 1";
if($search){
    $total_filtered_sql .= " AND (p.first_name LIKE '%$search_safe%' 
                        OR p.last_name LIKE '%$search_safe%' 
                        OR t.test_name LIKE '%$search_safe%' 
                        OR l.action LIKE '%$search_safe%')";
}
$total_filtered = mysqli_fetch_assoc(mysqli_query($con, $total_filtered_sql))['total'];
$total_pages = ceil($total_filtered / $limit);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Lab | Activity Log</title>
    <?php include 'include/css.php'; ?>
</head>
<body>
<div id="app">
    <div class="sidebar app-aside" id="sidebar"><?php include 'include/sidebar.php'; ?></div>
    <div class="app-content">
        <?php include 'include/header.php'; ?>

        <div class="main-content">
            <div class="wrap-content container" id="container">

                <section id="page-title">
                    <div class="row">
                        <div class="col-sm-8">
                            <h2 class="mainTitle">Lab Activity Log</h2>
                        </div>
                        <ol class="breadcrumb">
                            <li><span>Laboratory</span></li>
                            <li class="active"><span>Activity Log</span></li>
                        </ol>
                    </div>
                </section>

                <!-- Summary Cards -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="panel panel-primary text-center">
                            <div class="panel-heading">Total Activities</div>
                            <div class="panel-body"><h3><?= $total_activities ?></h3></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="panel panel-success text-center">
                            <div class="panel-heading">Results Submitted</div>
                            <div class="panel-body"><h3><?= $total_results ?></h3></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="panel panel-warning text-center">
                            <div class="panel-heading">Payments Requested</div>
                            <div class="panel-body"><h3><?= $total_payments ?></h3></div>
                        </div>
                    </div>
                </div>

                <!-- Search + Table -->
                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="panel panel-white">
                            <div class="panel-heading">
                                <h5 class="panel-title">Activity Logs</h5>
                                <div class="pull-right">
                                    <form method="get" class="form-inline">
                                        <input type="text" name="search" class="form-control input-sm" placeholder="Search..." value="<?= htmlspecialchars($search) ?>">
                                        <button type="submit" class="btn btn-primary btn-sm">Search</button>
                                    </form>
                                </div>
                            </div>
                            <div class="panel-body">
                                <table class="table table-bordered table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Patient</th>
                                            <th>Tests</th>
                                            <th>Action</th>
                                            <th>Request Status</th>
                                            <th>Performed By</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $i = $start + 1; while($log = mysqli_fetch_assoc($logs)){ ?>
                                        <tr>
                                            <td><?= $i++ ?></td>
                                            <td><?= htmlspecialchars($log['patient_first'].' '.$log['patient_last']) ?></td>
                                            <td><?= htmlspecialchars($log['tests'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($log['action']) ?></td>
                                            <td><?= ucfirst($log['request_status'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($log['staff_name'] ?? '-') ?></td>
                                            <td><?= date('d M Y', strtotime($log['log_date'])) ?></td>
                                        </tr>
                                        <?php } ?>
                                        <?php if(mysqli_num_rows($logs) == 0){ ?>
                                        <tr>
                                            <td colspan="7" class="text-center">No activity logs found.</td>
                                        </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>

                                <!-- Pagination -->
                                <?php if($total_pages > 1){ ?>
                                <nav>
                                    <ul class="pagination">
                                        <?php for($p=1;$p<=$total_pages;$p++){ ?>
                                        <li class="<?= $p==$page?'active':'' ?>">
                                            <a href="?page=<?= $p ?>&search=<?= htmlspecialchars($search) ?>"><?= $p ?></a>
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
