<?php
session_start();
include('include/config.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pharmacy') {
    header("Location: ../index.php");
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid prescription ID");
}

$id = (int)$_GET['id'];

$query = mysqli_query($con, "
    SELECT 
        p.*,
        CONCAT(pt.first_name,' ',pt.last_name) AS patient_name,
        pt.ehr_no,
        pt.gender,
        pt.dob,
        CONCAT(d.first_name,' ',d.last_name) AS doctor_name,
        d.specialization
    FROM prescriptions p
    INNER JOIN patients pt ON pt.id = p.patient_id
    INNER JOIN doctor d ON d.id = p.doctor_id
    WHERE p.id = $id
");

if (!$query || mysqli_num_rows($query) == 0) {
    die("Prescription not found");
}

$row = mysqli_fetch_assoc($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Prescription</title>
    <?php include 'include/css.php'; ?>
</head>
<body>
<div id="app">
<?php include 'include/sidebar.php'; ?>
<div class="app-content">
<?php include 'include/header.php'; ?>

<div class="main-content">
<div class="wrap-content container">

<h3>Prescription Details</h3>

<table class="table table-bordered">
<tr><th>EHR No</th><td><?= $row['ehr_no'] ?></td></tr>
<tr><th>Patient</th><td><?= $row['patient_name'] ?></td></tr>
<tr><th>Gender</th><td><?= $row['gender'] ?></td></tr>
<tr><th>DOB</th><td><?= $row['dob'] ?></td></tr>
<tr><th>Doctor</th><td><?= $row['doctor_name'] ?> (<?= $row['specialization'] ?>)</td></tr>
<tr><th>Diagnosis</th><td><?= nl2br($row['diagnosis']) ?></td></tr>
<tr><th>Medication</th><td><?= nl2br($row['medication']) ?></td></tr>
<tr><th>Dosage</th><td><?= nl2br($row['dosage']) ?></td></tr>
<tr><th>Instructions</th><td><?= nl2br($row['instructions']) ?></td></tr>
<tr><th>Status</th><td><span class="label label-warning"><?= ucfirst($row['status']) ?></span></td></tr>
<tr><th>Date</th><td><?= date('d M Y', strtotime($row['created_at'])) ?></td></tr>
</table>

<a href="prescriptions-pending.php" class="btn btn-default">Back</a>

</div>
</div>
</div>
</div>
<?php include 'include/js.php'; ?>
</body>
</html>
