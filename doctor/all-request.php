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
   SEARCH & FETCH REQUESTS
======================= */
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$sql = "
    SELECT 
        lr.id,
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

if($search){
    $search_safe = mysqli_real_escape_string($con, $search);
    $sql .= " AND (p.first_name LIKE '%$search_safe%' OR p.last_name LIKE '%$search_safe%' OR p.ehr_no LIKE '%$search_safe%')";
}

$sql .= " ORDER BY lr.created_at DESC";

$requests = mysqli_query($con, $sql);
if(!$requests) die("Query failed: " . mysqli_error($con));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Doctor | Lab Requests</title>
    <?php include 'include/css.php'; ?>
</head>

<body>
<div id="app">

    <div class="sidebar app-aside" id="sidebar">
        <?php include 'include/sidebar.php'; ?>
    </div>

    <div class="app-content">
        <?php include 'include/header.php'; ?>

        <div class="main-content">
            <div class="wrap-content container" id="container">

                <section id="page-title">
                    <div class="row">
                        <div class="col-sm-8">
                            <h2 class="mainTitle">Doctor | Lab Requests</h2>
                        </div>
                        <ol class="breadcrumb">
                            <li><span>Doctor</span></li>
                            <li class="active"><span>Lab Requests</span></li>
                        </ol>
                    </div>
                </section>

                <div class="container-fluid container-fullw bg-white">

                    <!-- Search Bar -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <form method="get" class="form-inline">
                                <input type="text" name="search" class="form-control input-sm" placeholder="Search patient or EHR" value="<?= htmlspecialchars($search) ?>">
                                <button type="submit" class="btn btn-primary btn-sm">Search</button>
                            </form>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">

                            <?php if(mysqli_num_rows($requests) == 0){ ?>
                                <div class="alert alert-info">
                                    No lab requests found.
                                </div>
                            <?php } ?>

                            <?php while($row = mysqli_fetch_assoc($requests)){ ?>

                            <div class="panel panel-white">
                                <div class="panel-heading">

                                    <strong>Patient: <?= htmlspecialchars($row['first_name'].' '.$row['last_name']); ?></strong>
                                    | EHR: <?= htmlspecialchars($row['ehr_no']); ?>

                                    <span class="pull-right">
                                        <?php if($row['status'] == 'pending'){ ?>
                                            <span class="label label-warning">Pending</span>
                                        <?php } elseif($row['status'] == 'completed'){ ?>
                                            <span class="label label-success">Completed</span>
                                        <?php } elseif($row['status'] == 'cancelled'){ ?>
                                            <span class="label label-danger">Cancelled</span>
                                        <?php } ?>

                                        <?php if($row['priority'] == 'urgent'){ ?>
                                            <span class="label label-danger">Urgent</span>
                                        <?php } ?>
                                    </span>

                                </div>

                                <div class="panel-body">

                                    <p><strong>Appointment Date:</strong> <?= $row['appointment_date'] ? date('d M Y', strtotime($row['appointment_date'])) : 'N/A'; ?></p>
                                    <p><strong>Requested On:</strong> <?= date('d M Y', strtotime($row['created_at'])); ?></p>

                                    <hr>

                                    <p><strong>Requested Tests:</strong></p>
                                    <ul>
                                        <?php
                                        $tests = mysqli_query($con, "
                                            SELECT test_name, sample_type, status, result, unit, reference_range
                                            FROM lab_request_tests
                                            WHERE lab_request_id = '{$row['id']}'
                                        ");

                                        while($t = mysqli_fetch_assoc($tests)){
                                        ?>
                                            <li>
                                                <strong><?= htmlspecialchars($t['test_name']); ?></strong>
                                                <small>(<?= ucfirst($t['sample_type']); ?> - <?= ucfirst($t['status']); ?>)</small>
                                                <?php if($t['status'] == 'completed'){ ?>
                                                    <br>
                                                    <em>Result:</em> <?= htmlspecialchars($t['result'].' '.$t['unit'].' (Ref: '.$t['reference_range'].')') ?>
                                                <?php } ?>
                                            </li>
                                        <?php } ?>
                                    </ul>

                                    <?php if($row['status'] == 'completed'){ ?>
                                        <hr>
                                        <a href="doctor_lab_results.php?id=<?= $row['id'] ?>" class="btn btn-success btn-sm float-end">View Full Results</a>
                                    <?php } ?>

                                </div>
                            </div>

                            <?php } ?>

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
