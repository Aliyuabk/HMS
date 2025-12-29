<?php
session_start();
include('include/config.php');

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pharmacy'){
    header("Location: ../index.php");
    exit;
}

if(!isset($_GET['id']) || !is_numeric($_GET['id'])) die("Invalid ID");

$id = (int)$_GET['id'];

$query = mysqli_query($con, "
    SELECT p.*, 
           CONCAT(pt.first_name,' ',pt.last_name) AS patient_name, pt.ehr_no,
           CONCAT(d.first_name,' ',d.last_name) AS doctor_name, d.specialization
    FROM prescriptions p
    INNER JOIN patients pt ON pt.id=p.patient_id
    INNER JOIN doctor d ON d.id=p.doctor_id
    WHERE p.id=$id
");

if(!$query || mysqli_num_rows($query)==0) die("Prescription not found");

$row = mysqli_fetch_assoc($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Print Prescription</title>
    <style>
        body{font-family: Arial; margin: 30px;}
        h2{text-align:center;}
        table{width:100%; border-collapse: collapse;}
        td, th{padding: 8px; border:1px solid #000;}
        .print-btn{margin-top:20px; text-align:center;}
    </style>
</head>
<body>
<h2>Prescription</h2>
<table>
<tr><th>EHR No</th><td><?= $row['ehr_no'] ?></td></tr>
<tr><th>Patient</th><td><?= $row['patient_name'] ?></td></tr>
<tr><th>Doctor</th><td><?= $row['doctor_name'] ?> (<?= $row['specialization'] ?>)</td></tr>
<tr><th>Diagnosis</th><td><?= nl2br($row['diagnosis']) ?></td></tr>
<tr><th>Medication</th><td><?= nl2br($row['medication']) ?></td></tr>
<tr><th>Dosage</th><td><?= nl2br($row['dosage']) ?></td></tr>
<tr><th>Instructions</th><td><?= nl2br($row['instructions']) ?></td></tr>
<tr><th>Date</th><td><?= date('d M Y', strtotime($row['created_at'])) ?></td></tr>
</table>

<div class="print-btn">
    <button onclick="window.print()">Print Prescription</button>
    <a href="dispense-history.php">Back</a>
</div>

</body>
</html>
