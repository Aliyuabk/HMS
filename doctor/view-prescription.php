<?php
session_start();
require_once('include/config.php');

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor'){
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$prescription_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$query = "
    SELECT pr.*, p.first_name, p.last_name, p.ehr_no, a.appointment_date
    FROM prescriptions pr
    JOIN patients p ON pr.patient_id = p.id
    LEFT JOIN appointments a ON pr.appointment_id = a.id
    WHERE pr.id = '$prescription_id' AND pr.doctor_id = '$user_id'
";
$result = mysqli_query($con, $query);

if(mysqli_num_rows($result) == 0){
    die("Prescription not found or access denied.");
}

$row = mysqli_fetch_assoc($result);
?>
<!DOCTYPE html>
<html lang="en">
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
            <div class="wrap-content container" id="container">
                <h3>Prescription Details</h3>
                <div class="panel panel-white">
                    <div class="panel-body">
                        <p><strong>Patient:</strong> <?= htmlspecialchars($row['first_name'].' '.$row['last_name']) ?> (ID: <?= htmlspecialchars($row['ehr_no']) ?>)</p>
                        <p><strong>Appointment Date:</strong> <?= $row['appointment_date'] ?? 'N/A' ?></p>
                        <p><strong>Diagnosis:</strong> <?= nl2br(htmlspecialchars($row['diagnosis'])) ?></p>
                        <p><strong>Medications:</strong> <?= nl2br(htmlspecialchars($row['medication'])) ?></p>
                        <p><strong>Instructions:</strong> <?= nl2br(htmlspecialchars($row['instructions'])) ?></p>
                        <p><strong>Status:</strong> <?= ucfirst($row['status']) ?></p>
                        <p><strong>Created At:</strong> <?= $row['created_at'] ?></p>
                        <a href="edit-prescription.php?id=<?= $row['id'] ?>" class="btn btn-warning">Edit</a>
                        <a href="print-prescription.php?id=<?= $row['id'] ?>" target="_blank" class="btn btn-info">Print</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'include/footer.php'; ?>
</div>
<?php include 'include/js.php'; ?>
</body>
</html>
