<?php
session_start();
include('include/config.php');

/* =========================
   ACCESS CONTROL
========================= */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pharmacy') {
    header("Location: ../index.php");
    exit;
}

$today = date('Y-m-d');

/* =========================
   DASHBOARD STATISTICS
========================= */

// Pending prescriptions today
$q_pending_today = mysqli_query($con,"
    SELECT COUNT(*) AS total 
    FROM prescriptions 
    WHERE status='pending' 
    AND DATE(created_at)='$today'
");
$pending_today = mysqli_fetch_assoc($q_pending_today)['total'] ?? 0;

// Dispensed today
$q_dispensed_today = mysqli_query($con,"
    SELECT COUNT(*) AS total 
    FROM prescriptions 
    WHERE status='completed' 
    AND DATE(updated_at)='$today'
");
$dispensed_today = mysqli_fetch_assoc($q_dispensed_today)['total'] ?? 0;

// Cancelled prescriptions
$q_cancelled = mysqli_query($con,"
    SELECT COUNT(*) AS total 
    FROM prescriptions 
    WHERE status='cancelled'
");
$cancelled_total = mysqli_fetch_assoc($q_cancelled)['total'] ?? 0;

// Doctors who sent prescriptions today
$q_doctors_today = mysqli_query($con,"
    SELECT COUNT(DISTINCT doctor_id) AS total 
    FROM prescriptions 
    WHERE DATE(created_at)='$today'
");
$doctors_today = mysqli_fetch_assoc($q_doctors_today)['total'] ?? 0;

// Patients waiting (pending prescriptions)
$q_patients_waiting = mysqli_query($con,"
    SELECT COUNT(DISTINCT patient_id) AS total 
    FROM prescriptions 
    WHERE status='pending'
");
$patients_waiting = mysqli_fetch_assoc($q_patients_waiting)['total'] ?? 0;

// Total pending prescriptions
$q_total_pending = mysqli_query($con,"
    SELECT COUNT(*) AS total 
    FROM prescriptions 
    WHERE status='pending'
");
$total_pending = mysqli_fetch_assoc($q_total_pending)['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Pharmacy | Dashboard</title>
    <?php include 'include/css.php'; ?>
</head>
<body>
<div id="app">

<?php include('include/sidebar.php'); ?>
<div class="app-content">
<?php include('include/header.php'); ?>

<div class="main-content">
<div class="wrap-content container" id="container">

<!-- PAGE TITLE -->
<section id="page-title">
    <div class="row">
        <div class="col-sm-8">
            <h2 class="mainTitle">Pharmacy | Dashboard</h2>
        </div>
        <ol class="breadcrumb">
            <li><span>Pharmacy</span></li>
            <li class="active"><span>Dashboard</span></li>
        </ol>
    </div>
</section>

<div class="container-fluid container-fullw bg-white">

    <!-- FIRST ROW -->
    <div class="row">

        <!-- Pending Today -->
        <div class="col-sm-4">
            <div class="panel panel-white no-radius text-center">
                <div class="panel-body">
                    <span class="fa-stack fa-2x">
                        <i class="fa fa-square fa-stack-2x text-primary"></i>
                        <i class="fa fa-clock-o fa-stack-1x fa-inverse"></i>
                    </span>
                    <h2 class="StepTitle">Pending Today</h2>
                    <p class="text-large"><?= $pending_today ?></p>
                    <p class="text-muted">Prescriptions</p>
                    <a href="prescriptions-pending.php" class="btn btn-primary btn-sm">View</a>
                </div>
            </div>
        </div>

        <!-- Dispensed Today -->
        <div class="col-sm-4">
            <div class="panel panel-white no-radius text-center">
                <div class="panel-body">
                    <span class="fa-stack fa-2x">
                        <i class="fa fa-square fa-stack-2x text-success"></i>
                        <i class="fa fa-check-circle fa-stack-1x fa-inverse"></i>
                    </span>
                    <h2 class="StepTitle">Dispensed Today</h2>
                    <p class="text-large"><?= $dispensed_today ?></p>
                    <p class="text-muted">Completed</p>
                    <a href="prescriptions-completed.php" class="btn btn-success btn-sm">View</a>
                </div>
            </div>
        </div>

        <!-- Cancelled -->
        <div class="col-sm-4">
            <div class="panel panel-white no-radius text-center">
                <div class="panel-body">
                    <span class="fa-stack fa-2x">
                        <i class="fa fa-square fa-stack-2x text-danger"></i>
                        <i class="fa fa-times-circle fa-stack-1x fa-inverse"></i>
                    </span>
                    <h2 class="StepTitle">Cancelled</h2>
                    <p class="text-large"><?= $cancelled_total ?></p>
                    <p class="text-muted">Prescriptions</p>
                    <a href="prescriptions-cancelled.php" class="btn btn-danger btn-sm">View</a>
                </div>
            </div>
        </div>

    </div>

    <!-- SECOND ROW -->
    <div class="row">

        <!-- Doctors Today -->
        <div class="col-sm-4">
            <div class="panel panel-white no-radius text-center">
                <div class="panel-body">
                    <span class="fa-stack fa-2x">
                        <i class="fa fa-square fa-stack-2x text-warning"></i>
                        <i class="fa fa-user-md fa-stack-1x fa-inverse"></i>
                    </span>
                    <h2 class="StepTitle">Doctors Today</h2>
                    <p class="text-large"><?= $doctors_today ?></p>
                    <p class="text-muted">Sent Prescriptions</p>
                </div>
            </div>
        </div>

        <!-- Patients Waiting -->
        <div class="col-sm-4">
            <div class="panel panel-white no-radius text-center">
                <div class="panel-body">
                    <span class="fa-stack fa-2x">
                        <i class="fa fa-square fa-stack-2x text-info"></i>
                        <i class="fa fa-users fa-stack-1x fa-inverse"></i>
                    </span>
                    <h2 class="StepTitle">Patients Waiting</h2>
                    <p class="text-large"><?= $patients_waiting ?></p>
                    <p class="text-muted">Pending Service</p>
                </div>
            </div>
        </div>

        <!-- Total Pending -->
        <div class="col-sm-4">
            <div class="panel panel-white no-radius text-center">
                <div class="panel-body">
                    <span class="fa-stack fa-2x">
                        <i class="fa fa-square fa-stack-2x text-primary"></i>
                        <i class="fa fa-files-o fa-stack-1x fa-inverse"></i>
                    </span>
                    <h2 class="StepTitle">Total Pending</h2>
                    <p class="text-large"><?= $total_pending ?></p>
                    <p class="text-muted">Prescriptions</p>
                </div>
            </div>
        </div>

    </div>

</div>
</div>
</div>

<?php include('include/footer.php'); ?>
<?php include('include/setting.php'); ?>
</div>
<?php include 'include/js.php'; ?>
</body>
</html>
