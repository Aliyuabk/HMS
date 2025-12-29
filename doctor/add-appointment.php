<?php
session_start();
require_once('include/config.php');

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor'){
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if(!isset($_GET['patient_id'])){
    header("Location: patients.php");
    exit;
}

$patient_id = intval($_GET['patient_id']);

// Fetch patient info
$patient_query = mysqli_query($con, "SELECT * FROM patients WHERE id='$patient_id'");
if(mysqli_num_rows($patient_query) == 0){
    header("Location: patients.php");
    exit;
}
$patient = mysqli_fetch_assoc($patient_query);

// Handle form submission
if(isset($_POST['submit'])){
    $date = $_POST['appointment_date'];
    $time = $_POST['appointment_time'];
    $type = mysqli_real_escape_string($con, $_POST['appointment_type']);
    $reason = mysqli_real_escape_string($con, $_POST['reason']);
    $priority = $_POST['priority'];
    $status = 'scheduled';

    $insert = mysqli_query($con, "
        INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, appointment_type, reason, status, priority) 
        VALUES ('$patient_id', '$user_id', '$date', '$time', '$type', '$reason', '$status', '$priority')
    ");

    if($insert){
        echo "<script>alert('Appointment added successfully!'); window.location.href='view-patient.php?id=$patient_id';</script>";
    } else {
        echo "<script>alert('Failed to add appointment.');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Doctor | Add Appointment</title>
    <?php include 'include/css.php'; ?>
</head>
<body>
<div id="app">
    <div class="sidebar app-aside" id="sidebar">
        <?php include('include/sidebar.php'); ?>
    </div>

    <div class="app-content">
        <?php include 'include/header.php'; ?>

        <div class="main-content">
            <div class="wrap-content container" id="container">

                <section id="page-title">
                    <div class="row">
                        <div class="col-sm-8">
                            <h2 class="mainTitle">Doctor | Add Appointment</h2>
                        </div>
                        <ol class="breadcrumb">
                            <li>Doctor</li>
                            <li><a href="patients.php">Patients</a></li>
                            <li class="active">Add Appointment</li>
                        </ol>
                    </div>
                </section>

                <div class="container-fluid container-fullw bg-white">
                    <div class="row">
                        <div class="col-md-8 col-md-offset-2">
                            <div class="panel panel-white">
                                <div class="panel-heading">
                                    <h4 class="panel-title">Patient: <?= htmlspecialchars($patient['first_name'].' '.$patient['last_name']); ?></h4>
                                </div>
                                <div class="panel-body">
                                    <form method="post">
                                        <div class="form-group">
                                            <label>Date</label>
                                            <input type="date" name="appointment_date" class="form-control" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Time</label>
                                            <input type="time" name="appointment_time" class="form-control" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Type</label>
                                            <input type="text" name="appointment_type" class="form-control" placeholder="e.g., Consultation" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Priority</label>
                                            <select name="priority" class="form-control">
                                                <option value="normal">Normal</option>
                                                <option value="urgent">Urgent</option>
                                                <option value="emergency">Emergency</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Reason</label>
                                            <textarea name="reason" class="form-control" rows="4" required></textarea>
                                        </div>
                                        <button type="submit" name="submit" class="btn btn-primary">Add Appointment</button>
                                        <a href="view-patient.php?id=<?= $patient_id; ?>" class="btn btn-default">Back</a>
                                    </form>
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
