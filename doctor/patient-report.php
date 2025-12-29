<?php
session_start();
require_once('include/config.php');

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor'){
    header("Location: ../index.php");
    exit;
}

$doctor_id = $_SESSION['user_id'];

$date_from = $date_to = "";
$patients = [];
$stats = [
    'total' => 0,
    'male' => 0,
    'female' => 0,
    'avg_visits' => 0
];

if(isset($_POST['date_from'])){
    $date_from = mysqli_real_escape_string($con, $_POST['date_from']);
    $date_to   = mysqli_real_escape_string($con, $_POST['date_to']);

    $sql = "
        SELECT 
            p.*,
            COUNT(a.id) AS total_visits,
            MAX(a.appointment_date) AS last_visit
        FROM patients p
        JOIN appointments a ON a.patient_id = p.id
        WHERE a.doctor_id = '$doctor_id'
    ";

    if(!empty($date_from) && !empty($date_to)){
        $sql .= " AND DATE(a.appointment_date) BETWEEN '$date_from' AND '$date_to'";
    }

    $sql .= " GROUP BY p.id ORDER BY p.first_name ASC";

    $query = mysqli_query($con, $sql);

    $total_visits_sum = 0;

    while($row = mysqli_fetch_assoc($query)){
        $patients[] = $row;
        $stats['total']++;

        if($row['gender'] === 'Male') $stats['male']++;
        if($row['gender'] === 'Female') $stats['female']++;

        $total_visits_sum += $row['total_visits'];
    }

    if($stats['total'] > 0){
        $stats['avg_visits'] = round($total_visits_sum / $stats['total'], 1);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Doctor | Patient Report</title>
    <?php include 'include/css.php'; ?>
</head>
<body>
<div id="app">
    <?php include 'include/sidebar.php'; ?>

    <div class="app-content">
        <?php include 'include/header.php'; ?>

        <div class="main-content">
            <div class="wrap-content container" id="container">

                <section id="page-title">
                    <div class="row">
                        <div class="col-sm-8">
                            <h2 class="mainTitle">Doctor | Patient Report</h2>
                        </div>
                        <ol class="breadcrumb">
                            <li><span>Doctor</span></li>
                            <li class="active"><span>Patient Report</span></li>
                        </ol>
                    </div>
                </section>

                <div class="container-fluid container-fullw bg-white">

                    <!-- FILTER -->
                    <div class="panel panel-white">
                        <div class="panel-heading">
                            <h4 class="panel-title">Report Filters</h4>
                        </div>
                        <div class="panel-body">
                            <form method="post">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label>Date From</label>
                                        <input type="date" name="date_from" class="form-control" value="<?= $date_from ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label>Date To</label>
                                        <input type="date" name="date_to" class="form-control" value="<?= $date_to ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <br>
                                        <button class="btn btn-primary">
                                            <i class="fa fa-search"></i> Generate Report
                                        </button>
                                        <button type="button" class="btn btn-default" onclick="window.print()">
                                            <i class="fa fa-print"></i> Print
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- TABLE -->
                    <div class="panel panel-white">
                        <div class="panel-heading">
                            <h4 class="panel-title">Patient Report</h4>
                        </div>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>EHR No</th>
                                        <th>Full Name</th>
                                        <th>Gender</th>
                                        <th>Age</th>
                                        <th>Contact</th>
                                        <th>Registered</th>
                                        <th>Total Visits</th>
                                        <th>Last Visit</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php if(count($patients) > 0){
                                        $i=1;
                                        foreach($patients as $p){
                                            $age = $p['dob'] ? date_diff(date_create($p['dob']), date_create('today'))->y : '-';
                                    ?>
                                        <tr>
                                            <td><?= $i++ ?></td>
                                            <td><?= htmlspecialchars($p['ehr_no']) ?></td>
                                            <td><?= htmlspecialchars($p['first_name'].' '.$p['last_name']) ?></td>
                                            <td><?= $p['gender'] ?></td>
                                            <td><?= $age ?></td>
                                            <td><?= htmlspecialchars($p['phone']) ?></td>
                                            <td><?= date('d M Y', strtotime($p['created_at'])) ?></td>
                                            <td><?= $p['total_visits'] ?></td>
                                            <td><?= date('d M Y', strtotime($p['last_visit'])) ?></td>
                                        </tr>
                                    <?php }} else { ?>
                                        <tr>
                                            <td colspan="9" class="text-center">No data available</td>
                                        </tr>
                                    <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- SUMMARY -->
                    <div class="row">
                        <div class="col-md-3">
                            <div class="panel panel-white text-center">
                                <div class="panel-body">
                                    <h2><?= $stats['total'] ?></h2>
                                    <p>Total Patients</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="panel panel-white text-center">
                                <div class="panel-body">
                                    <h2><?= $stats['male'] ?></h2>
                                    <p>Male Patients</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="panel panel-white text-center">
                                <div class="panel-body">
                                    <h2><?= $stats['female'] ?></h2>
                                    <p>Female Patients</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="panel panel-white text-center">
                                <div class="panel-body">
                                    <h2><?= $stats['avg_visits'] ?></h2>
                                    <p>Avg. Visits</p>
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
