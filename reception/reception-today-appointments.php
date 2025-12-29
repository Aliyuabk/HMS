<?php
session_start();
include('include/config.php');

$today = date('Y-m-d');

/* =========================
   FETCH TODAY'S APPOINTMENTS
========================= */
$query = "
    SELECT a.id AS appointment_id, a.appointment_time, a.status,
           p.first_name AS patient_first, p.last_name AS patient_last,
           d.first_name AS doctor_first, d.last_name AS doctor_last,
           r.room_name
    FROM appointments a
    LEFT JOIN patients p ON a.patient_id = p.id
    LEFT JOIN doctor d ON a.doctor_id = d.id
    LEFT JOIN rooms r ON a.room_id = r.id
    WHERE a.appointment_date='$today'
    ORDER BY a.appointment_time ASC
";

$result = mysqli_query($con, $query);
if(!$result){
    die("Query Failed: " . mysqli_error($con));
}

/* =========================
   FETCH APPOINTMENT STATISTICS
========================= */
$stats_query = "
    SELECT status, COUNT(*) AS count
    FROM appointments
    WHERE appointment_date='$today'
    GROUP BY status
";
$stats_result = mysqli_query($con, $stats_query);
$stats = [
    'scheduled' => 0,
    'completed' => 0,
    'waiting'   => 0,
    'cancelled' => 0
];
if($stats_result){
    while($row = mysqli_fetch_assoc($stats_result)){
        $stats[$row['status']] = $row['count'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
        <title>Reception | Today Appointments</title>
		<?php include('include/css.php'); ?>
</head>
<body>
<div id="app">
    <?php include('include/sidebar.php'); ?>
    <div class="app-content">
        <?php include('include/header.php'); ?>
        <div class="main-content">
            <div class="wrap-content container" id="container">
                
                <h2>Reception | Today's Appointments</h2>

                <!-- APPOINTMENTS TABLE -->
                <div class="container-fluid bg-white">
                    <div class="panel panel-white">
                        <div class="panel-heading">
                            <h4 class="panel-title">Today's Appointments List</h4>
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
                                            <th>Appointment Time</th>
                                            <th>Status</th>
                                            <th>Room</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(mysqli_num_rows($result) > 0): 
                                            $i = 1;
                                            while($row = mysqli_fetch_assoc($result)): ?>
                                            <tr>
                                                <td><?= $i++; ?></td>
                                                <td><?= htmlentities($row['patient_first'].' '.$row['patient_last']); ?></td>
                                                <td>Dr. <?= htmlentities($row['doctor_first'].' '.$row['doctor_last']); ?></td>
                                                <td><?= date('h:i A', strtotime($row['appointment_time'])); ?></td>
                                                <td><?= ucfirst($row['status']); ?></td>
                                                <td><?= htmlentities($row['room_name'] ?? '-'); ?></td>
                                                <td>
                                                    <a href="reception-view-appointment.php?id=<?= $row['appointment_id']; ?>" class="btn btn-info btn-xs">View</a>
                                                    <a href="reception-edit-appointment.php?id=<?= $row['appointment_id']; ?>" class="btn btn-warning btn-xs">Edit</a>
                                                </td>
                                            </tr>
                                        <?php endwhile; else: ?>
                                            <tr>
                                                <td colspan="7" class="text-center">No appointments for today</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <div class="text-right">
                                <a href="reception-all-appointments.php" class="btn btn-primary">View All Appointments</a>
                            </div>
                        </div>
                    </div>

                    <!-- STATISTICS -->
                    <div class="row">
                        <div class="col-md-3">
                            <div class="panel panel-white text-center">
                                <h3 class="text-primary"><?= $stats['scheduled']; ?></h3>
                                <p>Scheduled</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="panel panel-white text-center">
                                <h3 class="text-success"><?= $stats['completed']; ?></h3>
                                <p>Completed</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="panel panel-white text-center">
                                <h3 class="text-warning"><?= $stats['waiting']; ?></h3>
                                <p>Waiting</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="panel panel-white text-center">
                                <h3 class="text-danger"><?= $stats['cancelled']; ?></h3>
                                <p>Cancelled</p>
                            </div>
                        </div>
                    </div>
                    
                </div>
                <!-- END APPOINTMENTS TABLE -->

            </div>
        </div>
    </div>
	<?php include('include/footer.php'); ?>
</div>
        <?php include('include/setting.php'); ?>
	<?php include('include/js.php'); ?>
</body>
</html>
