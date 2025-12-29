<?php
session_start();
require_once('include/config.php');

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor'){
    header("Location: ../index.php");
    exit;
}

$doctor_id = $_SESSION['user_id'];

$date_from = $date_to = $status = $patient = "";
$prescriptions = [];

$stats = [
    'total' => 0,
    'completed' => 0,
    'pending' => 0,
    'cancelled' => 0
];

/* Load patients for dropdown */
$patients_q = mysqli_query($con,"
    SELECT DISTINCT p.id, CONCAT(p.first_name,' ',p.last_name) AS name
    FROM patients p
    JOIN prescriptions pr ON pr.patient_id = p.id
    WHERE pr.doctor_id = '$doctor_id'
    ORDER BY name ASC
");

/* Generate report */
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $date_from = mysqli_real_escape_string($con, $_POST['date_from']);
    $date_to   = mysqli_real_escape_string($con, $_POST['date_to']);
    $status    = mysqli_real_escape_string($con, $_POST['status']);
    $patient   = mysqli_real_escape_string($con, $_POST['patient']);

    $sql = "
        SELECT 
            pr.*,
            CONCAT(p.first_name,' ',p.last_name) AS patient_name
        FROM prescriptions pr
        JOIN patients p ON p.id = pr.patient_id
        WHERE pr.doctor_id = '$doctor_id'
    ";

    if(!empty($date_from) && !empty($date_to)){
        $sql .= " AND DATE(pr.created_at) BETWEEN '$date_from' AND '$date_to'";
    }
    if(!empty($status)){
        $sql .= " AND pr.status = '$status'";
    }
    if(!empty($patient)){
        $sql .= " AND pr.patient_id = '$patient'";
    }

    $sql .= " ORDER BY pr.created_at DESC";
    $query = mysqli_query($con, $sql);

    while($row = mysqli_fetch_assoc($query)){
        $prescriptions[] = $row;
        $stats['total']++;

        if($row['status'] === 'completed') $stats['completed']++;
        if($row['status'] === 'pending') $stats['pending']++;
        if($row['status'] === 'cancelled') $stats['cancelled']++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Doctor | Prescription Report</title>
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
                            <h2 class="mainTitle">Doctor | Prescription Report</h2>
                        </div>
                        <ol class="breadcrumb">
                            <li><span>Doctor</span></li>
                            <li class="active"><span>Prescription Report</span></li>
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
                                    <div class="col-md-3">
                                        <label>Date From</label>
                                        <input type="date" name="date_from" class="form-control" value="<?= $date_from ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label>Date To</label>
                                        <input type="date" name="date_to" class="form-control" value="<?= $date_to ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label>Status</label>
                                        <select name="status" class="form-control">
                                            <option value="">All</option>
                                            <option value="pending" <?= $status=='pending'?'selected':'' ?>>Pending</option>
                                            <option value="completed" <?= $status=='completed'?'selected':'' ?>>Completed</option>
                                            <option value="cancelled" <?= $status=='cancelled'?'selected':'' ?>>Cancelled</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label>Patient</label>
                                        <select name="patient" class="form-control">
                                            <option value="">All Patients</option>
                                            <?php while($p = mysqli_fetch_assoc($patients_q)){ ?>
                                                <option value="<?= $p['id'] ?>" <?= $patient==$p['id']?'selected':'' ?>>
                                                    <?= htmlspecialchars($p['name']) ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <br>
                                <button class="btn btn-primary">
                                    <i class="fa fa-search"></i> Generate Report
                                </button>
                                <button type="button" class="btn btn-default" onclick="window.print()">
                                    <i class="fa fa-print"></i> Print
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- TABLE -->
                    <div class="panel panel-white">
                        <div class="panel-heading">
                            <h4 class="panel-title">Prescription Report</h4>
                        </div>
                        <div class="panel-body">
                            <table class="table table-bordered table-hover">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>ID</th>
                                    <th>Patient</th>
                                    <th>Date</th>
                                    <th>Medication</th>
                                    <th>Dosage</th>
                                    <th>Instructions</th>
                                    <th>Status</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php if(count($prescriptions)>0){
                                    $i=1;
                                    foreach($prescriptions as $pr){ ?>
                                        <tr>
                                            <td><?= $i++ ?></td>
                                            <td><?= $pr['id'] ?></td>
                                            <td><?= htmlspecialchars($pr['patient_name']) ?></td>
                                            <td><?= date('d M Y', strtotime($pr['created_at'])) ?></td>
                                            <td><?= nl2br(htmlspecialchars($pr['medication'])) ?></td>
                                            <td><?= htmlspecialchars($pr['dosage']) ?></td>
                                            <td><?= htmlspecialchars($pr['instructions']) ?></td>
                                            <td><?= ucfirst($pr['status']) ?></td>
                                        </tr>
                                <?php }} else { ?>
                                    <tr>
                                        <td colspan="8" class="text-center">No prescription data available</td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- SUMMARY -->
                    <div class="row">
                        <div class="col-md-3"><div class="panel panel-white text-center"><div class="panel-body"><h2><?= $stats['total'] ?></h2><p>Total</p></div></div></div>
                        <div class="col-md-3"><div class="panel panel-white text-center"><div class="panel-body"><h2><?= $stats['completed'] ?></h2><p>Completed</p></div></div></div>
                        <div class="col-md-3"><div class="panel panel-white text-center"><div class="panel-body"><h2><?= $stats['pending'] ?></h2><p>Pending</p></div></div></div>
                        <div class="col-md-3"><div class="panel panel-white text-center"><div class="panel-body"><h2><?= $stats['cancelled'] ?></h2><p>Cancelled</p></div></div></div>
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
