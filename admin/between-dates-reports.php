<?php
session_start();
include('include/config.php');

// Restrict access to admin only
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$sql = mysqli_query($con, "SELECT * FROM staff WHERE id = '$user_id'");
$data = mysqli_fetch_array($sql);

// Default date range: current month
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$report_type = isset($_GET['report_type']) ? $_GET['report_type'] : 'appointments';

// Fetch report data based on type
if($report_type == 'appointments') {
    $query = "SELECT COUNT(*) AS total, 
                     SUM(CASE WHEN status = 'scheduled' THEN 1 ELSE 0 END) AS scheduled,
                     SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) AS confirmed,
                     SUM(CASE WHEN status = 'waiting' THEN 1 ELSE 0 END) AS waiting,
                     SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled
              FROM appointments 
              WHERE appointment_date BETWEEN '$start_date' AND '$end_date'";
    $result = mysqli_query($con, $query);
    $stats = mysqli_fetch_assoc($result);

    $details_query = "SELECT a.*, p.fullname AS patient_name, s.fullname AS doctor_name 
                      FROM appointments a
                      JOIN patients p ON a.patient_id = p.id
                      JOIN staff s ON a.doctor_id = s.id
                      WHERE a.appointment_date BETWEEN '$start_date' AND '$end_date'
                      ORDER BY a.appointment_date DESC";
    $details_result = mysqli_query($con, $details_query);

} elseif($report_type == 'patients') {
    $query = "SELECT COUNT(*) AS total,
                     SUM(CASE WHEN gender='Male' THEN 1 ELSE 0 END) AS male,
                     SUM(CASE WHEN gender='Female' THEN 1 ELSE 0 END) AS female
              FROM patients 
              WHERE created_at BETWEEN '$start_date' AND '$end_date'";
    $result = mysqli_query($con, $query);
    $stats = mysqli_fetch_assoc($result);

    $details_query = "SELECT * FROM patients 
                      WHERE created_at BETWEEN '$start_date' AND '$end_date'
                      ORDER BY created_at DESC";
    $details_result = mysqli_query($con, $details_query);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin | Between Dates Reports</title>
    <?php include 'include/css.php';?>
</head>
<body>
<div id="app">
    <?php include('include/sidebar.php');?>
    <div class="app-content">
        <?php include('include/header.php');?>
        <div class="main-content">
            <div class="wrap-content container" id="container">
                
                <!-- Page Title -->
                <section id="page-title">
                    <div class="row">
                        <div class="col-sm-8">
                            <h2 class="mainTitle">Admin | Between Dates Reports</h2>
                        </div>
                        <ol class="breadcrumb">
                            <li><span>Admin</span></li>
                            <li class="active"><span>Between Dates Reports</span></li>
                        </ol>
                    </div>
                </section>
                
                <!-- Generate Report Form -->
                <div class="container-fluid container-fullw bg-white">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="panel panel-white">
                                <div class="panel-heading">
                                    <h4 class="panel-title">Generate Report</h4>
                                </div>
                                <div class="panel-body">
                                    <form method="GET" action="" class="form-inline">
                                        <div class="form-group">
                                            <label>Report Type: </label>
                                            <select name="report_type" class="form-control" style="width: 200px;">
                                                <option value="appointments" <?= $report_type=='appointments' ? 'selected':''; ?>>Appointments</option>
                                                <option value="patients" <?= $report_type=='patients' ? 'selected':''; ?>>Patients</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>From: </label>
                                            <input type="date" name="start_date" class="form-control" value="<?= $start_date; ?>" style="width: 150px;">
                                        </div>
                                        <div class="form-group">
                                            <label>To: </label>
                                            <input type="date" name="end_date" class="form-control" value="<?= $end_date; ?>" style="width: 150px;">
                                        </div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fa fa-search"></i> Generate
                                        </button>
                                        <button type="button" class="btn btn-success" onclick="window.print()">
                                            <i class="fa fa-print"></i> Print Report
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Summary Stats -->
                    <div class="row">
                        <?php if($report_type=='appointments'): ?>
                        <div class="col-md-2 col-sm-6">
                            <div class="panel panel-white no-radius text-center">
                                <div class="panel-body">
                                    <span class="fa-stack fa-2x"><i class="fa fa-square fa-stack-2x text-primary"></i><i class="fa fa-calendar fa-stack-1x fa-inverse"></i></span>
                                    <h2 class="StepTitle">Total</h2>
                                    <p class="text-large"><?= $stats['total'] ?: 0; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 col-sm-6">
                            <div class="panel panel-white no-radius text-center">
                                <div class="panel-body">
                                    <span class="fa-stack fa-2x"><i class="fa fa-square fa-stack-2x text-success"></i><i class="fa fa-check fa-stack-1x fa-inverse"></i></span>
                                    <h2 class="StepTitle">Confirmed</h2>
                                    <p class="text-large"><?= $stats['confirmed'] ?: 0; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 col-sm-6">
                            <div class="panel panel-white no-radius text-center">
                                <div class="panel-body">
                                    <span class="fa-stack fa-2x"><i class="fa fa-square fa-stack-2x text-warning"></i><i class="fa fa-clock-o fa-stack-1x fa-inverse"></i></span>
                                    <h2 class="StepTitle">Scheduled</h2>
                                    <p class="text-large"><?= $stats['scheduled'] ?: 0; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 col-sm-6">
                            <div class="panel panel-white no-radius text-center">
                                <div class="panel-body">
                                    <span class="fa-stack fa-2x"><i class="fa fa-square fa-stack-2x text-info"></i><i class="fa fa-clock-o fa-stack-1x fa-inverse"></i></span>
                                    <h2 class="StepTitle">Waiting</h2>
                                    <p class="text-large"><?= $stats['waiting'] ?: 0; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 col-sm-6">
                            <div class="panel panel-white no-radius text-center">
                                <div class="panel-body">
                                    <span class="fa-stack fa-2x"><i class="fa fa-square fa-stack-2x text-danger"></i><i class="fa fa-times fa-stack-1x fa-inverse"></i></span>
                                    <h2 class="StepTitle">Cancelled</h2>
                                    <p class="text-large"><?= $stats['cancelled'] ?: 0; ?></p>
                                </div>
                            </div>
                        </div>
                        <?php elseif($report_type=='patients'): ?>
                        <div class="col-md-4 col-sm-6">
                            <div class="panel panel-white no-radius text-center">
                                <div class="panel-body">
                                    <span class="fa-stack fa-2x"><i class="fa fa-square fa-stack-2x text-primary"></i><i class="fa fa-users fa-stack-1x fa-inverse"></i></span>
                                    <h2 class="StepTitle">Total Patients</h2>
                                    <p class="text-large"><?= $stats['total'] ?: 0; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-6">
                            <div class="panel panel-white no-radius text-center">
                                <div class="panel-body">
                                    <span class="fa-stack fa-2x"><i class="fa fa-square fa-stack-2x text-info"></i><i class="fa fa-male fa-stack-1x fa-inverse"></i></span>
                                    <h2 class="StepTitle">Male Patients</h2>
                                    <p class="text-large"><?= $stats['male'] ?: 0; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-6">
                            <div class="panel panel-white no-radius text-center">
                                <div class="panel-body">
                                    <span class="fa-stack fa-2x"><i class="fa fa-square fa-stack-2x text-warning"></i><i class="fa fa-female fa-stack-1x fa-inverse"></i></span>
                                    <h2 class="StepTitle">Female Patients</h2>
                                    <p class="text-large"><?= $stats['female'] ?: 0; ?></p>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Detailed Table -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="panel panel-white">
                                <div class="panel-heading">
                                    <h4 class="panel-title">Detailed Report</h4>
                                </div>
                                <div class="panel-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover">
                                            <thead>
                                                <?php if($report_type=='appointments'): ?>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Appointment ID</th>
                                                    <th>Patient Name</th>
                                                    <th>Doctor Name</th>
                                                    <th>Date</th>
                                                    <th>Time</th>
                                                    <th>Status</th>
                                                </tr>
                                                <?php elseif($report_type=='patients'): ?>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Patient ID</th>
                                                    <th>Full Name</th>
                                                    <th>Gender</th>
                                                    <th>Email</th>
                                                    <th>Phone</th>
                                                    <th>Registration Date</th>
                                                </tr>
                                                <?php endif; ?>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $count = 1;
                                                if(isset($details_result) && mysqli_num_rows($details_result) > 0) {
                                                    while($row = mysqli_fetch_assoc($details_result)) {
                                                ?>
                                                <tr>
                                                    <?php if($report_type=='appointments'): ?>
                                                    <td><?= $count++; ?></td>
                                                    <td><?= htmlentities($row['id']); ?></td>
                                                    <td><?= htmlentities($row['patient_name']); ?></td>
                                                    <td><?= htmlentities($row['doctor_name']); ?></td>
                                                    <td><?= date('d-m-Y', strtotime($row['appointment_date'])); ?></td>
                                                    <td><?= $row['appointment_time']; ?></td>
                                                    <td>
                                                        <span class="label label-<?= 
                                                            $row['status']=='confirmed'?'success':($row['status']=='cancelled'?'danger':'warning')?>">
                                                            <?= ucfirst($row['status']); ?>
                                                        </span>
                                                    </td>
                                                    <?php elseif($report_type=='patients'): ?>
                                                    <td><?= $count++; ?></td>
                                                    <td><?= htmlentities($row['ehr_no']); ?></td>
                                                    <td><?= htmlentities($row['first_name']); ?> <?= htmlentities($row['last_name']); ?></td>
                                                    <td><?= htmlentities($row['gender']); ?></td>
                                                    <td><?= htmlentities($row['email']); ?></td>
                                                    <td><?= htmlentities($row['phone']); ?></td>
                                                    <td><?= date('d-m-Y H:i', strtotime($row['created_at'])); ?></td>
                                                    <?php endif; ?>
                                                </tr>
                                                <?php
                                                    }
                                                } else {
                                                    $colspan = $report_type=='appointments'?7:7;
                                                    echo "<tr><td colspan='$colspan' class='text-center'>No records found for the selected period.</td></tr>";
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div> <!-- container-fluid -->
            </div> <!-- wrap-content -->
        </div> <!-- main-content -->
    </div> <!-- app-content -->

    <?php include('include/footer.php');?>
    <?php include('include/setting.php');?>
</div>

<?php include 'include/js.php';?>
</body>
</html>
