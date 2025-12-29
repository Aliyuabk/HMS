<?php
session_start();
require_once('include/config.php');

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor'){
    header("Location: ../index.php");
    exit;
}
            $user_id = $_SESSION['user_id'];
            $sql = mysqli_query($con, "SELECT * FROM doctor WHERE id = '$user_id'");
            $user_data = mysqli_fetch_array($sql);
$prescription_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$query = "
    SELECT pr.*, p.first_name, p.last_name, p.ehr_no
    FROM prescriptions pr
    JOIN patients p ON pr.patient_id = p.id
    WHERE pr.id='$prescription_id' AND pr.doctor_id='$user_id'
";
$result = mysqli_query($con, $query);

if(mysqli_num_rows($result)==0){
    die("Prescription not found or access denied.");
}

$row = mysqli_fetch_assoc($result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Print Prescription</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .prescription { max-width: 600px; margin: auto; border: 1px solid #000; padding: 20px; }
        h2 { text-align: center; }
        hr { margin: 10px 0; }
        pre { white-space: pre-wrap; }
    </style>
    <script>window.onload = function(){ window.print(); }</script>
</head>
<body>
<div class="prescription">
    <h2>Medical Prescription</h2>
    <p><strong>Patient:</strong> <?= htmlspecialchars($row['first_name'].' '.$row['last_name']) ?> (ID: <?= htmlspecialchars($row['ehr_no']) ?>)</p>
    <p><strong>Date:</strong> <?= date('F d, Y', strtotime($row['created_at'])) ?></p>
    <hr>
    <p><strong>Diagnosis:</strong><br><?= nl2br(htmlspecialchars($row['diagnosis'])) ?></p>
    <p><strong>Medications:</strong><br><pre><?= htmlspecialchars($row['medication']) ?></pre></p>
    <p><strong>Instructions:</strong><br><?= nl2br(htmlspecialchars($row['instructions'])) ?></p>
    <p><strong>Status:</strong> <?= ucfirst($row['status']) ?></p>
    <hr>
    <p>Dr. <?= htmlspecialchars($user_data['first_name'].' '.$user_data['last_name']) ?></p>
    <p> <?= htmlspecialchars($user_data['phone']) ?></p>
</div>
</body>
</html>
