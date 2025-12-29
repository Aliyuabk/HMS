<?php
session_start();
require_once('include/config.php');

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor'){
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$prescription_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch prescription
$query = "
    SELECT pr.*, p.first_name, p.last_name, p.ehr_no
    FROM prescriptions pr
    JOIN patients p ON pr.patient_id = p.id
    WHERE pr.id='$prescription_id' AND pr.doctor_id='$user_id'
";
$result = mysqli_query($con, $query);
if(mysqli_num_rows($result) == 0){
    die("Prescription not found or access denied.");
}
$prescription = mysqli_fetch_assoc($result);

// Handle form submission
if(isset($_POST['submit'])){
    $diagnosis = mysqli_real_escape_string($con, $_POST['diagnosis']);
    $medication = mysqli_real_escape_string($con, $_POST['medication']);
    $instructions = mysqli_real_escape_string($con, $_POST['instructions']);
    $status = mysqli_real_escape_string($con, $_POST['status']);

    $update = "
        UPDATE prescriptions SET
        diagnosis='$diagnosis',
        medication='$medication',
        instructions='$instructions',
        status='$status',
        updated_at=CURRENT_TIMESTAMP
        WHERE id='$prescription_id' AND doctor_id='$user_id'
    ";
    if(mysqli_query($con, $update)){
        echo "<script>alert('Prescription updated successfully'); window.location.href='view-prescription.php?id=$prescription_id';</script>";
        exit;
    } else {
        echo "<script>alert('Error: ".mysqli_error($con)."');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Prescription</title>
    <?php include 'include/css.php'; ?>
</head>
<body>
<div id="app">
    <?php include 'include/sidebar.php'; ?>
    <div class="app-content">
        <?php include 'include/header.php'; ?>
        <div class="main-content">
            <div class="wrap-content container" id="container">
                <h3>Edit Prescription</h3>
                <div class="panel panel-white">
                    <div class="panel-body">
                        <form method="post">
                            <p><strong>Patient:</strong> <?= htmlspecialchars($prescription['first_name'].' '.$prescription['last_name']) ?> (ID: <?= htmlspecialchars($prescription['ehr_no']) ?>)</p>
                            <div class="form-group">
                                <label>Diagnosis</label>
                                <textarea name="diagnosis" class="form-control" required><?= htmlspecialchars($prescription['diagnosis']) ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Medications</label>
                                <textarea name="medication" class="form-control" required><?= htmlspecialchars($prescription['medication']) ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Instructions</label>
                                <textarea name="instructions" class="form-control"><?= htmlspecialchars($prescription['instructions']) ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" class="form-control" required>
                                    <option value="pending" <?= $prescription['status']=='pending'?'selected':'' ?>>Pending</option>
                                    <option value="completed" <?= $prescription['status']=='completed'?'selected':'' ?>>Completed</option>
                                    <option value="cancelled" <?= $prescription['status']=='cancelled'?'selected':'' ?>>Cancelled</option>
                                </select>
                            </div>
                            <button type="submit" name="submit" class="btn btn-primary">Update Prescription</button>
                        </form>
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
