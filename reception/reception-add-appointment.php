<?php
session_start();
include('include/config.php');

/* =========================
   FETCH ACTIVE DOCTORS
========================= */
$doctors = mysqli_query($con, "
    SELECT id, first_name, last_name
    FROM doctor
    WHERE status='active'
");

/* =========================
   HANDLE FORM SUBMISSION
========================= */
if (isset($_POST['add_appointment'])) {

    $patient_number   = trim($_POST['patient_number']);
    $doctor_id        = (int)$_POST['doctor_id'];
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];
    $appointment_type = $_POST['appointment_type'] ?? 'consultation';
    $reason           = trim($_POST['reason']);
    $status           = $_POST['status'];
    $priority         = $_POST['priority'];

    // Validate EHR number length
    if(strlen($patient_number) !== 6){
        echo "<script>alert('Patient EHR number must be 6 digits');</script>";
    } else {
        // Fetch patient ID
        $stmt = $con->prepare("SELECT id FROM patients WHERE ehr_no = ? LIMIT 1");
        if(!$stmt) die("Prepare failed: " . $con->error);
        $stmt->bind_param("s", $patient_number);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows === 0){
            echo "<script>alert('Patient not found');</script>";
        } else {
            $patient = $result->fetch_assoc();
            $patient_id = $patient['id'];

            // Insert appointment
            $stmtInsert = $con->prepare("
                INSERT INTO appointments
                (patient_id, doctor_id, appointment_date, appointment_time, appointment_type, reason, status, priority)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            if(!$stmtInsert) die("Prepare failed: " . $con->error);

            $stmtInsert->bind_param(
                "iissssss",
                $patient_id,
                $doctor_id,
                $appointment_date,
                $appointment_time,
                $appointment_type,
                $reason,
                $status,
                $priority
            );

            if($stmtInsert->execute()){
                echo "<script>
                    alert('Appointment added successfully');
                    window.location='reception-today-appointments.php';
                </script>";
            } else {
                die("Error adding appointment: " . $stmtInsert->error);
            }
            $stmtInsert->close();
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Reception | Add Appointment</title>
    <?php include('include/css.php'); ?>
</head>
<body>
<div id="app">
<?php include('include/sidebar.php'); ?>
<div class="app-content">
<?php include('include/header.php'); ?>

<div class="main-content">
<div class="wrap-content container">

<h2>Reception | Add Appointment</h2>

<div class="container-fluid bg-white">
<div class="panel panel-white">
<div class="panel-heading"><h4>Add New Appointment</h4></div>
<div class="panel-body">

<form method="post">

<!-- Patient -->
<div class="row">
<div class="col-md-6">
<label>Patient EHR No (6 digits) *</label>
<input type="text" name="patient_number" id="patient_number" maxlength="6" class="form-control" required>

<label>Patient Name *</label>
<input type="text" id="patient_name" class="form-control" readonly>
</div>

<div class="col-md-6">
<label>Doctor *</label>
<select name="doctor_id" class="form-control" required>
<option value="">Select Doctor</option>
<?php while($doc = mysqli_fetch_assoc($doctors)) { ?>
<option value="<?= $doc['id']; ?>">Dr. <?= htmlentities($doc['first_name'].' '.$doc['last_name']); ?></option>
<?php } ?>
</select>
</div>
</div>

<hr>

<!-- Date & Time -->
<div class="row">
<div class="col-md-6">
<label>Date *</label>
<input type="date" name="appointment_date" class="form-control" required>
</div>
<div class="col-md-6">
<label>Time *</label>
<input type="time" name="appointment_time" class="form-control" required>
</div>
</div>

<hr>

<label>Reason</label>
<textarea name="reason" class="form-control"></textarea>

<hr>

<div class="row">
<div class="col-md-6">
<label>Status</label>
<select name="status" class="form-control">
<option value="scheduled">Scheduled</option>
<option value="confirmed">Confirmed</option>
<option value="waiting">Waiting</option>
<option value="cancelled">Cancelled</option>
</select>
</div>

<div class="col-md-6">
<label>Priority</label>
<select name="priority" class="form-control">
<option value="normal">Normal</option>
<option value="urgent">Urgent</option>
<option value="emergency">Emergency</option>
</select>
</div>
</div>

<br>
<button type="submit" name="add_appointment" class="btn btn-primary">
<i class="fa fa-plus"></i> Add Appointment
</button>

</form>
</div>
</div>
</div>

</div>
<?php include('include/footer.php'); ?>
</div>
</div>

<?php include('include/setting.php'); ?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function(){
    // Fetch patient name by EHR number
    $('#patient_number').on('keyup', function(){
        let ehr = $(this).val().trim();
        if(ehr.length === 6){
            $.post('fetch-patient.php', { ehr_no: ehr }, function(response){
                $('#patient_name').val(response || 'Not found');
            });
        } else {
            $('#patient_name').val('');
        }
    });
});
</script>
<?php include('include/js.php'); ?>
</body>
</html>
