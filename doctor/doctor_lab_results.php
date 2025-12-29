<?php
session_start();
require_once('include/config.php');

/* =======================
   AUTH CHECK
======================= */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
    header("Location: ../index.php");
    exit;
}

$doctor_id = $_SESSION['user_id'];

/* =======================
   SEARCH & PAGINATION
======================= */
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$limit  = 10;
$page   = isset($_GET['page']) ? intval($_GET['page']) : 1;
$start  = ($page - 1) * $limit;

/* =======================
   FETCH LAB REQUESTS
======================= */
$sql = "
    SELECT lr.id AS request_id,
           lr.status,
           lr.priority,
           lr.created_at,
           p.first_name,
           p.last_name,
           p.ehr_no,
           a.appointment_date
    FROM lab_requests lr
    INNER JOIN patients p ON p.id = lr.patient_id
    LEFT JOIN appointments a ON a.id = lr.appointment_id
    WHERE lr.doctor_id = '$doctor_id'
";

if ($search) {
    $search_safe = mysqli_real_escape_string($con, $search);
    $sql .= " AND (p.first_name LIKE '%$search_safe%' OR p.last_name LIKE '%$search_safe%' OR lr.status LIKE '%$search_safe%')";
}

$sql .= " ORDER BY lr.created_at DESC LIMIT $start, $limit";

$requests = mysqli_query($con, $sql);
if (!$requests) die("Query failed: " . mysqli_error($con));

/* Total count for pagination */
$total_sql = "
    SELECT COUNT(*) AS total
    FROM lab_requests lr
    INNER JOIN patients p ON p.id = lr.patient_id
    WHERE lr.doctor_id = '$doctor_id'
";
if ($search) {
    $total_sql .= " AND (p.first_name LIKE '%$search_safe%' OR p.last_name LIKE '%$search_safe%' OR lr.status LIKE '%$search_safe%')";
}
$total_count = mysqli_fetch_assoc(mysqli_query($con, $total_sql))['total'];
$total_pages = ceil($total_count / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Doctor | Lab Results</title>
    <?php include 'include/css.php'; ?>
    <style>
        .test-item { margin-bottom: 10px; padding-bottom: 5px; border-bottom: 1px solid #eee; }
        .test-name { font-weight: 600; }
        .result-completed { color: green; font-weight: 500; }
        .result-pending { color: gray; font-style: italic; }
    </style>
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
                            <h2 class="mainTitle">Doctor | Lab Results</h2>
                        </div>
                        <ol class="breadcrumb">
                            <li><span>Doctor</span></li>
                            <li class="active"><span>Lab Results</span></li>
                        </ol>
                    </div>
                </section>

                <!-- Search Bar -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <form method="get" class="form-inline">
                            <input type="text" name="search" class="form-control input-sm" placeholder="Search patient or status" value="<?= htmlspecialchars($search) ?>">
                            <button type="submit" class="btn btn-primary btn-sm">Search</button>
                        </form>
                    </div>
                </div>

                <!-- Lab Requests Table -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-white">
                            <div class="panel-heading">
                                <h5 class="panel-title">Lab Requests</h5>
                            </div>
                            <div class="panel-body table-responsive">
                                <table class="table table-bordered table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Patient</th>
                                            <th>EHR No</th>
                                            <th>Appointment</th>
                                            <th>Priority</th>
                                            <th>Status</th>
                                            <th>Tests & Results</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $i = $start + 1; ?>
                                        <?php while ($req = mysqli_fetch_assoc($requests)) { 
                                            $tests_sql = mysqli_query($con, "
                                                SELECT test_name, result, unit, reference_range, status 
                                                FROM lab_request_tests 
                                                WHERE lab_request_id='{$req['request_id']}'
                                            ");
                                        ?>
                                        <tr>
                                            <td><?= $i++ ?></td>
                                            <td><?= htmlspecialchars($req['first_name'].' '.$req['last_name']) ?></td>
                                            <td><?= htmlspecialchars($req['ehr_no']) ?></td>
                                            <td><?= $req['appointment_date'] ? date('d M Y', strtotime($req['appointment_date'])) : '-' ?></td>
                                            <td><?= ucfirst($req['priority']) ?></td>
                                            <td>
                                                <?php if($req['status']=='pending'){ ?>
                                                    <span class="label label-warning">Pending</span>
                                                <?php } elseif($req['status']=='completed'){ ?>
                                                    <span class="label label-success">Completed</span>
                                                <?php } else { ?>
                                                    <span class="label label-danger"><?= ucfirst($req['status']) ?></span>
                                                <?php } ?>
                                            </td>
                                            <td>
                                                <div>
                                                    <?php while($t = mysqli_fetch_assoc($tests_sql)) { ?>
                                                        <div class="test-item">
                                                            <div class="test-name"><?= htmlspecialchars($t['test_name']) ?></div>
                                                            <div class="<?= $t['status']=='completed' ? 'result-completed' : 'result-pending' ?>">
                                                                Result: <?= $t['status']=='completed' ? htmlspecialchars($t['result']) : 'Pending' ?><br>
                                                                Unit: <?= htmlspecialchars($t['unit'] ?? '-') ?><br>
                                                                Reference: <?= htmlspecialchars($t['reference_range'] ?? '-') ?>
                                                            </div>
                                                        </div>
                                                    <?php } ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php } ?>

                                        <?php if(mysqli_num_rows($requests) == 0) { ?>
                                        <tr>
                                            <td colspan="7" class="text-center">No lab requests found.</td>
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
