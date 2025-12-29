<?php
	session_start();
	include('include/config.php');
// Use selected date from GET or default to today
$selectedDate = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Fetch all doctors
$result = mysqli_query($con, "SELECT * FROM doctor ORDER BY first_name ASC");
$totalDoctors = mysqli_num_rows($result);

// Fetch duty roster for selected date
$dutyRoster = [];
$dutyResult = mysqli_query($con, "SELECT * FROM duty_roster WHERE shift_date='$selectedDate'");
while($row = mysqli_fetch_assoc($dutyResult)){
    $dutyRoster[$row['doctor_id']] = $row['shift_type']; // Morning, Evening, Night, Off
}

// Fetch appointments for selected date
$appointments = [];
$appointmentResult = mysqli_query($con, "SELECT doctor_id FROM appointments WHERE appointment_date='$selectedDate'");
while($row = mysqli_fetch_assoc($appointmentResult)){
    $appointments[$row['doctor_id']][] = 1; // flag if doctor has appointment
}

// Helper function to determine availability
function getDoctorAvailability($doctorId, $dutyRoster, $appointments){
    $shift = $dutyRoster[$doctorId] ?? null;

    if($shift === 'Off'){
        return ['label'=>'On Leave', 'class'=>'danger'];
    }
    if($shift){ // has a shift
        if(isset($appointments[$doctorId]) && count($appointments[$doctorId]) > 0){
            return ['label'=>'Busy', 'class'=>'warning'];
        } else {
            return ['label'=>'Available ('.$shift.')', 'class'=>'success'];
        }
    }
    return ['label'=>'Not Scheduled','class'=>'default'];
}

// Count statistics
$availableDoctors = 0;
$busyDoctors = 0;
$onLeaveDoctors = 0;
mysqli_data_seek($result,0); // reset pointer
while($doc = mysqli_fetch_assoc($result)){
    $status = getDoctorAvailability($doc['id'], $dutyRoster, $appointments);
    if($status['class']=='success') $availableDoctors++;
    if($status['class']=='warning') $busyDoctors++;
    if($status['class']=='danger') $onLeaveDoctors++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <?php include('include/css.php'); ?>
</head>
<body>
<div id="app">
<?php include('include/sidebar.php'); ?>
<div class="app-content">
<?php include('include/header.php'); ?>

<div class="main-content">
<div class="wrap-content container">

<section id="page-title">
    <h2>Reception | All Doctors</h2>
</section>

<!-- Date Picker Form -->
<form method="get" class="form-inline" style="margin-bottom:20px;">
    <label>Select Date: </label>
    <input type="date" name="date" class="form-control" value="<?= $selectedDate ?>">
    <button type="submit" class="btn btn-primary">Go</button>
</form>

<!-- Doctors Table -->
<div class="panel panel-white">
    <div class="panel-heading">
        <h4 class="panel-title">Doctors List</h4>
    </div>
    <div class="panel-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Doctor ID</th>
                        <th>Name</th>
                        <th>Specialization</th>
                        <th>Department</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $count = 1;
                mysqli_data_seek($result,0); // reset pointer
                while($row = mysqli_fetch_assoc($result)):
                    $status = getDoctorAvailability($row['id'], $dutyRoster, $appointments);
                ?>
                    <tr>
                        <td><?= $count++ ?></td>
                        <td><?= htmlentities($row['license_no']) ?></td>
                        <td>Dr. <?= htmlentities($row['first_name'].' '.$row['last_name']) ?></td>
                        <td><?= htmlentities($row['specialization']) ?></td>
                        <td><?= htmlentities($row['specialization']) ?></td>
                        <td><?= htmlentities($row['phone']) ?></td>
                        <td><?= htmlentities($row['email']) ?></td>
                        <td><span class="label label-<?= $status['class'] ?>"><?= $status['label'] ?></span></td>
                        <td>
                            <a href="#" class="btn btn-xs btn-info"><i class="fa fa-eye"></i></a>
                            <a href="reception-doctor-schedule.php?id=<?= $row['id'] ?>" class="btn btn-xs btn-warning"><i class="fa fa-calendar"></i></a>
                            <a href="reception-add-appointment.php?doctor_id=<?= $row['id'] ?>" class="btn btn-xs btn-success"><i class="fa fa-plus"></i></a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row" style="margin-top:20px;">
    <div class="col-md-3 col-sm-6">
        <div class="panel panel-white no-radius text-center">
            <div class="panel-body">
                <h2>Total Doctors</h2>
                <p class="text-large"><?= $totalDoctors ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="panel panel-white no-radius text-center">
            <div class="panel-body">
                <h2>Available Today</h2>
                <p class="text-large"><?= $availableDoctors ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="panel panel-white no-radius text-center">
            <div class="panel-body">
                <h2>Busy</h2>
                <p class="text-large"><?= $busyDoctors ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="panel panel-white no-radius text-center">
            <div class="panel-body">
                <h2>On Leave</h2>
                <p class="text-large"><?= $onLeaveDoctors ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Doctor Cards -->
<div class="row" style="margin-top:20px;">
<?php
mysqli_data_seek($result,0); // reset pointer
while($doc = mysqli_fetch_assoc($result)):
    $status = getDoctorAvailability($doc['id'], $dutyRoster, $appointments);
?>
<div class="col-md-3">
    <div class="doctor-card text-center panel panel-white">
        <div class="doctor-avatar"><br><br>
            <img src="assets/images/images.jpg" alt="Doctor" class="img-circle" width="80">
        </div>
        <h4>Dr. <?= htmlentities($doc['first_name'].' '.$doc['last_name']) ?></h4>
        <p class="text-muted"><?= htmlentities($doc['specialization']) ?></p>
        <p><span class="label label-<?= $status['class'] ?>"><?= $status['label'] ?></span></p>
        <p>Room: <?= htmlentities($doc['room'] ?? '-') ?></p>
        <a href="reception-add-appointment.php?doctor_id=<?= $doc['id'] ?>" class="btn btn-primary btn-sm">Book Appointment</a>
        <br><br>
    </div>
</div>
<?php endwhile; ?>
</div>

</div>
</div>


</div>
<?php include('include/footer.php'); ?>

</div>
<?php include('include/setting.php'); ?>
    <?php include('include/js.php'); ?>
</body>
</html>
