<?php
session_start();
require_once('include/config.php');

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor'){
    header("Location: ../index.php");
    exit;
}

$doctor_id = $_SESSION['user_id'];

$fromDate = $toDate = $status = "";
$appointments = [];
$summary = [
    'total' => 0,
    'scheduled' => 0,
    'completed' => 0,
    'cancelled' => 0
];

if(isset($_POST['generate'])){
    $fromDate = mysqli_real_escape_string($con, $_POST['fromDate']);
    $toDate   = mysqli_real_escape_string($con, $_POST['toDate']);
    $status   = mysqli_real_escape_string($con, $_POST['status']);

    // Base query
    $sql = "
        SELECT 
            a.*, 
            CONCAT(p.first_name,' ',p.last_name) AS patient_name
        FROM appointments a
        JOIN patients p ON p.id = a.patient_id
        WHERE a.doctor_id = '$doctor_id'
        AND DATE(a.appointment_date) BETWEEN '$fromDate' AND '$toDate'
    ";

    if(!empty($status)){
        $sql .= " AND a.status = '$status'";
    }

    $sql .= " ORDER BY a.appointment_date ASC";

    $query = mysqli_query($con, $sql);

    while($row = mysqli_fetch_assoc($query)){
        $appointments[] = $row;
        $summary['total']++;

        if(isset($summary[$row['status']])){
            $summary[$row['status']]++;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Doctor | Appointment Report</title>
    <?php include 'include/css.php';?>
</head>
<body>
<div id="app">
    <?php include 'include/sidebar.php';?>

    <div class="app-content">
        <?php include 'include/header.php';?>

        <div class="main-content">
            <div class="wrap-content container" id="container">

                <section id="page-title">
                    <div class="row">
                        <div class="col-sm-8">
                            <h2 class="mainTitle">Doctor | Appointment Report</h2>
                        </div>
                        <ol class="breadcrumb">
                            <li><span>Doctor</span></li>
                            <li class="active"><span>Appointment Report</span></li>
                        </ol>
                    </div>
                </section>

                <div class="container-fluid container-fullw bg-white">
                    <div class="panel panel-white">
                        <div class="panel-heading">
                            <h4 class="panel-title">Generate Appointment Report</h4>
                        </div>

                        <div class="panel-body">
                            <form method="post">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label>From Date</label>
                                        <input type="date" name="fromDate" class="form-control" required value="<?= $fromDate ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label>To Date</label>
                                        <input type="date" name="toDate" class="form-control" required value="<?= $toDate ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label>Status</label>
                                        <select name="status" class="form-control">
                                            <option value="">All</option>
                                            <option value="scheduled" <?= ($status=='scheduled')?'selected':'' ?>>Scheduled</option>
                                            <option value="completed" <?= ($status=='completed')?'selected':'' ?>>Completed</option>
                                            <option value="cancelled" <?= ($status=='cancelled')?'selected':'' ?>>Cancelled</option>
                                        </select>
                                    </div>
                                </div>
                                <br>
                                <button name="generate" class="btn btn-primary">
                                    <i class="fa fa-search"></i> Generate Report
                                </button>
                                <button type="button" onclick="window.print()" class="btn btn-success">
                                    <i class="fa fa-print"></i> Print
                                </button>
                            </form>

                            <!-- SUMMARY -->
                            <div class="table-responsive" style="margin-top:30px;">
                                <h4>Appointment Report Summary</h4>
                                <table class="table table-bordered">
                                    <tr>
                                        <th>Date Range</th>
                                        <th>Total</th>
                                        <th>Scheduled</th>
                                        <th>Completed</th>
                                        <th>Cancelled</th>
                                    </tr>
                                    <tr>
                                        <td><?= $fromDate && $toDate ? "$fromDate to $toDate" : "-" ?></td>
                                        <td><?= $summary['total'] ?></td>
                                        <td><?= $summary['scheduled'] ?></td>
                                        <td><?= $summary['completed'] ?></td>
                                        <td><?= $summary['cancelled'] ?></td>
                                    </tr>
                                </table>
                            </div>

                            <!-- DETAILS -->
                            <div class="table-responsive" style="margin-top:30px;">
                                <h4>Detailed Appointment List</h4>
                                <table class="table table-bordered table-hover">
                                    <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Patient</th>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Status</th>
                                        <th>Reason</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php if(count($appointments) > 0){ 
                                        $i=1;
                                        foreach($appointments as $row){ ?>
                                        <tr>
                                            <td><?= $i++ ?></td>
                                            <td><?= htmlspecialchars($row['patient_name']) ?></td>
                                            <td><?= date('d M Y', strtotime($row['appointment_date'])) ?></td>
                                            <td><?= $row['appointment_time'] ?? '-' ?></td>
                                            <td>
                                                <span class="label label-<?= 
                                                    $row['status']=='completed'?'success':($row['status']=='cancelled'?'danger':'warning') ?>">
                                                    <?= ucfirst($row['status']) ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($row['reason']) ?></td>
                                        </tr>
                                    <?php }} else { ?>
                                        <tr>
                                            <td colspan="6" class="text-center">No records found</td>
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

    <?php include 'include/footer.php';?>
    <?php include 'include/setting.php';?>
</div>

<?php include 'include/js.php';?>
</body>
</html>
