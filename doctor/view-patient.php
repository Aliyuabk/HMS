<?php
session_start();
require_once('include/config.php');

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor'){
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if(!isset($_GET['id'])){
    header("Location: patients.php");
    exit;
}

$patient_id = intval($_GET['id']);

// Fetch patient info
$query = "SELECT * FROM patients WHERE id='$patient_id'";
$result = mysqli_query($con, $query);

if(mysqli_num_rows($result) == 0){
    header("Location: patients.php");
    exit;
}

$patient = mysqli_fetch_assoc($result);

// Fetch appointments of this patient for this doctor
$appointments_query = "
SELECT * FROM appointments 
WHERE patient_id='$patient_id' AND doctor_id='$user_id'
ORDER BY appointment_date DESC, appointment_time DESC
";
$appointments = mysqli_query($con, $appointments_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Doctor | View Patient</title>
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
                            <h2 class="mainTitle">Doctor | View Patient</h2>
                        </div>
                        <ol class="breadcrumb">
                            <li>Doctor</li>
                            <li><a href="patients.php">Patients</a></li>
                            <li class="active">View</li>
                        </ol>
                    </div>
                </section>

                <div class="container-fluid container-fullw bg-white">
                    <div class="row">

                        <!-- PATIENT INFO -->
                        <div class="col-md-6">
                            <div class="panel panel-white">
                                <div class="panel-heading">
                                    <h4 class="panel-title">Patient Information</h4>
                                </div>
                                <div class="panel-body">
                                    <p><strong>Name:</strong> <?= htmlspecialchars($patient['first_name'].' '.$patient['last_name']); ?></p>
                                    <p><strong>EHR No:</strong> <?= htmlspecialchars($patient['ehr_no']); ?></p>
                                    <p><strong>Gender:</strong> <?= htmlspecialchars($patient['gender']); ?></p>
                                    <p><strong>DOB:</strong> <?= $patient['dob'] ? date('M d, Y', strtotime($patient['dob'])) : 'N/A'; ?></p>
                                    <p><strong>Phone:</strong> <?= htmlspecialchars($patient['phone']); ?></p>
                                    <p><strong>Email:</strong> <?= htmlspecialchars($patient['email']); ?></p>
                                    <p><strong>Address:</strong> <?= htmlspecialchars($patient['address']); ?></p>
                                    <p><strong>Next of Kin:</strong> <?= htmlspecialchars($patient['next_of_kin_name']); ?> (<?= htmlspecialchars($patient['next_of_kin_phone']); ?>)</p>
                                </div>
                            </div>
                        </div>

                        <!-- APPOINTMENT HISTORY -->
                        <div class="col-md-6">
                            <div class="panel panel-white">
                                <div class="panel-heading">
                                    <h4 class="panel-title">Appointments</h4>
                                </div>
                                <div class="panel-body">
                                    <?php if(mysqli_num_rows($appointments) > 0){ ?>
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Time</th>
                                                <th>Reason</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while($a = mysqli_fetch_assoc($appointments)){ ?>
                                            <tr>
                                                <td><?= date('M d, Y', strtotime($a['appointment_date'])); ?></td>
                                                <td><?= date('h:i A', strtotime($a['appointment_time'])); ?></td>
                                                <td><?= htmlspecialchars($a['reason']); ?></td>
                                                <td><?= ucfirst($a['status']); ?></td>
                                            </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                    <?php } else { ?>
                                        <p>No appointments found for this patient.</p>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12 text-right">
                            <a href="add-appointment.php?patient_id=<?= $patient['id']; ?>" class="btn btn-success">
                                <i class="fa fa-calendar"></i> Add Appointment
                            </a>
                            <a href="patients.php" class="btn btn-default">
                                <i class="fa fa-arrow-left"></i> Back to Patients
                            </a>
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
