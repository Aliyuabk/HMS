<?php
session_start();
include('include/config.php');

// Get appointment ID
if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
    die("Invalid appointment ID");
}

$appt_id = (int)$_GET['id'];

// Fetch appointment details
$stmt = $con->prepare("
    SELECT a.*, p.first_name AS patient_first, p.last_name AS patient_last,
           d.first_name AS doctor_first, d.last_name AS doctor_last
    FROM appointments a
    JOIN patients p ON a.patient_id = p.id
    JOIN doctor d ON a.doctor_id = d.id
    WHERE a.id = ?
    LIMIT 1
");
$stmt->bind_param("i", $appt_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows === 0){
    die("Appointment not found");
}

$appointment = $result->fetch_assoc();
$stmt->close();

// Fetch all doctors
$doctors = mysqli_query($con, "SELECT id, first_name, last_name FROM doctor WHERE status='active'");

// Handle form submission
if(isset($_POST['update_appointment'])){
    $doctor_id        = (int)$_POST['doctor_id'];
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];
    $appointment_type = $_POST['appointment_type'] ?? 'consultation';
    $reason           = trim($_POST['reason']);
    $status           = $_POST['status'];
    $priority         = $_POST['priority'];

    $stmtUpdate = $con->prepare("
        UPDATE appointments 
        SET doctor_id=?, appointment_date=?, appointment_time=?, appointment_type=?, reason=?, status=?, priority=?
        WHERE id=?
    ");
    $stmtUpdate->bind_param(
        "issssssi",
        $doctor_id,
        $appointment_date,
        $appointment_time,
        $appointment_type,
        $reason,
        $status,
        $priority,
        $appt_id
    );

    if($stmtUpdate->execute()){
        echo "<script>alert('Appointment updated successfully');window.location='reception-all-appointments.php';</script>";
    } else {
        die("Error updating appointment: " . $stmtUpdate->error);
    }
    $stmtUpdate->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Appointment</title>
    <?php include('include/css.php'); ?>
</head>
<body>
<div id="app">
<?php include('include/sidebar.php'); ?>
<div class="app-content">
<?php include('include/header.php'); ?>
<div class="main-content">
<div class="wrap-content container">

<h2>Edit Appointment</h2>
<div class="container-fluid bg-white">
<div class="panel panel-white">
<div class="panel-heading"><h4>Edit Appointment</h4></div>
<div class="panel-body">

<form method="post">

<!-- Patient (readonly) -->
<div class="row">
<div class="col-md-6">
<label>Patient Name</label>
<input type="text" class="form-control" value="<?= htmlentities($appointment['patient_first'].' '.$appointment['patient_last']); ?>" readonly>
</div>

<div class="col-md-6">
<label>Doctor *</label>
<select name="doctor_id" class="form-control" required>
<?php while($doc = mysqli_fetch_assoc($doctors)) { 
    $selected = ($doc['id'] == $appointment['doctor_id']) ? 'selected' : '';
?>
<option value="<?= $doc['id']; ?>" <?= $selected; ?>>Dr. <?= htmlentities($doc['first_name'].' '.$doc['last_name']); ?></option>
<?php } ?>
</select>
</div>
</div>

<hr>

<!-- Date & Time -->
<div class="row">
<div class="col-md-6">
<label>Date *</label>
<input type="date" name="appointment_date" class="form-control" value="<?= $appointment['appointment_date']; ?>" required>
</div>
<div class="col-md-6">
<label>Time *</label>
<input type="time" name="appointment_time" class="form-control" value="<?= $appointment['appointment_time']; ?>" required>
</div>
</div>

<hr>

<label>Reason</label>
<textarea name="reason" class="form-control"><?= htmlentities($appointment['reason']); ?></textarea>

<hr>

<div class="row">
<div class="col-md-6">
<label>Status</label>
<select name="status" class="form-control">
<?php 
$statuses = ['scheduled','confirmed','waiting','cancelled'];
foreach($statuses as $s){
    $sel = ($s == $appointment['status']) ? 'selected' : '';
    echo "<option value='$s' $sel>".ucfirst($s)."</option>";
}
?>
</select>
</div>

<div class="col-md-6">
<label>Priority</label>
<select name="priority" class="form-control">
<?php 
$priorities = ['normal','urgent','emergency'];
foreach($priorities as $p){
    $sel = ($p == $appointment['priority']) ? 'selected' : '';
    echo "<option value='$p' $sel>".ucfirst($p)."</option>";
}
?>
</select>
</div>
</div>

<br>
<button type="submit" name="update_appointment" class="btn btn-primary">
<i class="fa fa-save"></i> Update Appointment
</button>

</form>
</div>
</div>
</div>

</div>
</div>
</div>

<?php include('include/footer.php'); ?>
<?php include('include/setting.php'); ?>
<?php include('include/js.php'); ?>
</body>
</html>
