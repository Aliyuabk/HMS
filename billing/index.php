<?php
session_start();
include('include/config.php');

// Restrict access to billing only
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'billing'){
    header("Location: ../index.php");
    exit;
}

/* =======================
   SUMMARY CARDS
======================= */
$today = date("Y-m-d");
$month = date("Y-m");

function getSummary($con, $table, $amountCol = 'price'){
    global $today, $month;
    $todayRow = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS total, SUM($amountCol) AS amount FROM $table WHERE DATE(created_at)='$today' AND status='paid'"));
    $monthRow = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS total, SUM($amountCol) AS amount FROM $table WHERE DATE_FORMAT(created_at,'%Y-%m')='$month' AND status='paid'"));
    $pendingRow = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS total, SUM($amountCol) AS amount FROM $table WHERE status='pending'"));
    return [
        'today_count' => $todayRow['total'] ?? 0,
        'today_amount' => $todayRow['amount'] ?? 0,
        'month_count' => $monthRow['total'] ?? 0,
        'month_amount' => $monthRow['amount'] ?? 0,
        'pending_count' => $pendingRow['total'] ?? 0,
        'pending_amount' => $pendingRow['amount'] ?? 0,
    ];
}

// Get summaries from all tables
$summaryGeneral = getSummary($con, 'payment_request');
$summaryPharmacy = getSummary($con, 'pharmacy_payment');
$summaryLab = getSummary($con, 'lab_payment_request', 'amount');

// Aggregate totals
$today_count = $summaryGeneral['today_count'] + $summaryPharmacy['today_count'] + $summaryLab['today_count'];
$today_amount = $summaryGeneral['today_amount'] + $summaryPharmacy['today_amount'] + $summaryLab['today_amount'];
$month_count = $summaryGeneral['month_count'] + $summaryPharmacy['month_count'] + $summaryLab['month_count'];
$month_amount = $summaryGeneral['month_amount'] + $summaryPharmacy['month_amount'] + $summaryLab['month_amount'];
$pending_count = $summaryGeneral['pending_count'] + $summaryPharmacy['pending_count'] + $summaryLab['pending_count'];
$pending_amount = $summaryGeneral['pending_amount'] + $summaryPharmacy['pending_amount'] + $summaryLab['pending_amount'];

/* =======================
   PAGINATION & SEARCH
======================= */
$limit = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? trim(mysqli_real_escape_string($con, $_GET['search'])) : '';

/* =======================
   FETCH PENDING PAYMENTS FROM ALL TABLES
======================= */
$pending_sql = "
SELECT id, patient_id, ehr_no, patient_name, item_name, price AS amount, 'General' AS source, status, created_at 
FROM payment_request 
WHERE status='pending' " . ($search ? "AND (patient_name LIKE '%$search%' OR ehr_no LIKE '%$search%' OR item_name LIKE '%$search%')" : "") . "
UNION ALL
SELECT id, patient_id, ehr_no, patient_name, item_name, price AS amount, 'Pharmacy' AS source, status, created_at 
FROM pharmacy_payment 
WHERE status='pending' " . ($search ? "AND (patient_name LIKE '%$search%' OR ehr_no LIKE '%$search%' OR item_name LIKE '%$search%')" : "") . "
UNION ALL
SELECT id, NULL AS patient_id, NULL AS ehr_no, CONCAT('Lab Req #', lab_request_id) AS patient_name, '' AS item_name, amount, 'Lab' AS source, status, created_at 
FROM lab_payment_request 
WHERE status='pending' " . ($search ? "AND (CONCAT('Lab Req #', lab_request_id) LIKE '%$search%' OR amount LIKE '%$search%')" : "") . "
ORDER BY created_at DESC
LIMIT $offset, $limit
";

$query = mysqli_query($con, $pending_sql);
if(!$query){
    die("Pending payments query failed: " . mysqli_error($con));
}

// Total rows for pagination
$total_sql = "
SELECT COUNT(*) AS total FROM (
    SELECT id FROM payment_request WHERE status='pending' " . ($search ? "AND (patient_name LIKE '%$search%' OR ehr_no LIKE '%$search%' OR item_name LIKE '%$search%')" : "") . "
    UNION ALL
    SELECT id FROM pharmacy_payment WHERE status='pending' " . ($search ? "AND (patient_name LIKE '%$search%' OR ehr_no LIKE '%$search%' OR item_name LIKE '%$search%')" : "") . "
    UNION ALL
    SELECT id FROM lab_payment_request WHERE status='pending' " . ($search ? "AND (CONCAT('Lab Req #', lab_request_id) LIKE '%$search%' OR amount LIKE '%$search%')" : "") . "
) AS t
";
$totalRows = mysqli_fetch_assoc(mysqli_query($con, $total_sql))['total'] ?? 0;
$totalPages = ceil($totalRows / $limit);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Cashier | Dashboard</title>
    <?php include('include/css.php'); ?>
</head>
<body>
<div id="app">
<?php include('include/sidebar.php'); ?>
<div class="app-content">
<?php include('include/header.php'); ?>

<div class="main-content">
<div class="wrap-content container" id="container">

<section id="page-title">
    <div class="row">
        <div class="col-sm-8"><h2 class="mainTitle">Cashier | Dashboard</h2></div>
        <ol class="breadcrumb">
            <li><span>Cashier</span></li>
            <li class="active"><span>Dashboard</span></li>
        </ol>
    </div>
</section>

<!-- Summary Cards -->
 <a href="today-bills.php">
<div class="row">
    <div class="col-sm-4">
        <div class="panel panel-blue text-white text-center">
            <div class="panel-body">
                <h4 style="color:white;">Today Bills</h4>
                <p class="text-large"><?= $today_count ?></p>
                <p class="text-muted">Amount: ₦<?= number_format($today_amount, 2) ?></p>
            </div>
        </div>
        </a>
    </div>
    <a href="monthly-bills.php">
    <div class="col-sm-4">
        <div class="panel panel-green text-center">
            <div class="panel-body">
                <h4 style="color:white;">This Month</h4>
                <p class="text-large"><?= $month_count ?></p>
                <p class="text-muted">Amount: ₦<?= number_format($month_amount, 2) ?></p>
            </div>
        </div>
    </div>
    </a>
    <a href="pending-bills.php">
    <div class="col-sm-4">
        <div class="panel panel-red text-center">
            <div class="panel-body">
                <h4 style="color:white;">Pending Bills</h4>
                <p class="text-large"><?= $pending_count ?></p>
                <p class="text-muted">Amount: ₦<?= number_format($pending_amount, 2) ?></p>
            </div>
        </div>
    </div>
    </a>
</div>

<!-- Search Form -->
<div class="row mb-3">
    <div class="col-md-6">
        <form method="get" class="form-inline">
            <input type="text" name="search" class="form-control input-sm" placeholder="Search by patient or service" value="<?= htmlspecialchars($search) ?>">
            <button class="btn btn-primary btn-sm" type="submit"><i class="fa fa-search"></i> Search</button>
        </form>
    </div>
</div>

<!-- Pending Payments Table -->
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-white">
            <div class="panel-heading"><h4>Pending Payments</h4></div>
            <div class="panel-body">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Patient Number</th>
                            <th>Patient Name / Lab Request</th>
                            <th>Amount</th>
                            <th>Service / Source</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $count = $offset + 1;
                    if(mysqli_num_rows($query) > 0){
                        while($row = mysqli_fetch_assoc($query)){
                    ?>
                        <tr>
                            <td><?= $count++; ?></td>
                            <td><?= htmlspecialchars($row['ehr_no'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($row['patient_name']) ?></td>
                            <td>₦<?= number_format($row['amount'], 2) ?></td>
                            <td><?= htmlspecialchars($row['source']) ?></td>
                            <td><span class="label label-danger"><?= ucfirst($row['status']) ?></span></td>
                        </tr>
                    <?php
                        }
                    } else {
                        echo '<tr><td colspan="6" class="text-center">No pending payments found</td></tr>';
                    }
                    ?>
                    </tbody>
                </table>

                <!-- Pagination -->
                <?php if($totalPages > 1): ?>
                <ul class="pagination">
                    <li class="<?= ($page<=1)?'disabled':'' ?>"><a href="?page=<?= $page-1 ?>&search=<?= htmlspecialchars($search) ?>">«</a></li>
                    <?php for($p=1;$p<=$totalPages;$p++): ?>
                        <li class="<?= ($page==$p)?'active':'' ?>"><a href="?page=<?= $p ?>&search=<?= htmlspecialchars($search) ?>"><?= $p ?></a></li>
                    <?php endfor; ?>
                    <li class="<?= ($page>=$totalPages)?'disabled':'' ?>"><a href="?page=<?= $page+1 ?>&search=<?= htmlspecialchars($search) ?>">»</a></li>
                </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

</div>
</div>
</div>

<?php include('include/footer.php');?>
<?php include('include/setting.php');?>
</div>
<?php include 'include/js.php';?>
</body>
</html>
