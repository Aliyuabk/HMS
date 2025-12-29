<?php
session_start();
include('include/config.php');

/* =========================
   FETCH ALL APPOINTMENTS
========================= */
$appointmentsQuery = "
    SELECT a.id, a.appointment_date, a.appointment_time, a.status, a.priority,
           p.first_name AS patient_first, p.last_name AS patient_last,
           d.first_name AS doctor_first, d.last_name AS doctor_last,
           r.room_name, b.bed_number
    FROM appointments a
    JOIN patients p ON a.patient_id = p.id
    JOIN doctor d ON a.doctor_id = d.id
    LEFT JOIN rooms r ON a.room_id = r.id
    LEFT JOIN beds b ON a.bed_id = b.id
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
";

$appointments = mysqli_query($con, $appointmentsQuery);
if(!$appointments){
    die("Error fetching appointments: " . mysqli_error($con));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
       <title>Reception | All Appointments</title>
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
    <h2>Reception | All Appointments</h2>
</section>

<div class="container-fluid bg-white">
<div class="panel panel-white">
<div class="panel-heading">
    <h4 class="panel-title">All Appointments List</h4>
    <div class="panel-tools">
        <a href="reception-add-appointment.php" class="btn btn-primary">
            <i class="fa fa-plus"></i> Add New Appointment
        </a>
    </div>
</div>

<div class="panel-body">
<div class="table-responsive">
<table class="table table-bordered table-hover">
    <thead>
        <tr>
            <th>#</th>
            <th>Patient Name</th>
            <th>Doctor</th>
            <th>Date</th>
            <th>Time</th>
            <th>Status</th>
            <th>Priority</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if(mysqli_num_rows($appointments) > 0): 
            $count = 1;
            while($appt = mysqli_fetch_assoc($appointments)): ?>
            <tr>
                <td><?= $count++; ?></td>
                <td><?= htmlentities($appt['patient_first'] . ' ' . $appt['patient_last']); ?></td>
                <td>Dr. <?= htmlentities($appt['doctor_first'] . ' ' . $appt['doctor_last']); ?></td>
                <td><?= htmlentities($appt['appointment_date']); ?></td>
                <td><?= htmlentities($appt['appointment_time']); ?></td>
                <td><?= htmlentities(ucfirst($appt['status'])); ?></td>
                <td><?= htmlentities(ucfirst($appt['priority'])); ?></td>
                <td>
                    <a href="reception-edit-appointment.php?id=<?= $appt['id']; ?>" class="btn btn-sm btn-warning">
                        <i class="fa fa-edit"></i> 
                    </a>
                    <a href="reception-delete-appointment.php?id=<?= $appt['id']; ?>" 
                       class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this appointment?');">
                        <i class="fa fa-trash"></i> 
                    </a>
                </td>
            </tr>
        <?php endwhile; else: ?>
            <tr>
                <td colspan="10" class="text-center">No appointments found</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
</div>
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
