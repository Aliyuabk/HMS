<?php
session_start();
require_once('include/config.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if (!isset($_GET['id'])) {
    header("Location: appointments.php");
    exit;
}

$appointment_id = intval($_GET['id']);

/* ==========================
   FETCH APPOINTMENT DETAILS
========================== */
$query = "
SELECT 
    a.*,
    p.first_name,
    p.last_name,
    p.ehr_no,
    p.phone,
    p.gender,
    p.dob,
    d.first_name AS doctor_fname,
    d.last_name AS doctor_lname,
    d.specialization
FROM appointments a
JOIN patients p ON a.patient_id = p.id
JOIN doctor d ON a.doctor_id = d.id
WHERE a.id='$appointment_id' AND a.doctor_id='$user_id'
";

$result = mysqli_query($con, $query);

if (mysqli_num_rows($result) == 0) {
    header("Location: appointments.php");
    exit;
}

$data = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Doctor | View Appointment</title>
    <?php include 'include/css.php'; ?>

    <style>
        .info-label {
            font-weight: bold;
            color: #555;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-scheduled { background:#f0ad4e; color:#fff; }
        .status-confirmed { background:#5cb85c; color:#fff; }
        .status-waiting { background:#5bc0de; color:#fff; }
        .status-cancelled { background:#d9534f; color:#fff; }
    </style>
</head>

<body>
<div id="app">

    <!-- SIDEBAR -->
    <div class="sidebar app-aside" id="sidebar">
        <?php include 'include/sidebar.php'; ?>
    </div>

    <!-- MAIN CONTENT -->
    <div class="app-content">
        <?php include 'include/header.php'; ?>

        <div class="main-content">
            <div class="wrap-content container" id="container">

                <!-- PAGE TITLE -->
                <section id="page-title">
                    <div class="row">
                        <div class="col-sm-8">
                            <h2 class="mainTitle">Doctor | View Appointment</h2>
                        </div>
                        <ol class="breadcrumb">
                            <li>Doctor</li>
                            <li>
                                <a href="appointments.php">Appointments</a>
                            </li>
                            <li class="active">View</li>
                        </ol>
                    </div>
                </section>

                <div class="container-fluid container-fullw bg-white">
                    <div class="row">

                        <!-- PATIENT DETAILS -->
                        <div class="col-md-6">
                            <div class="panel panel-white">
                                <div class="panel-heading">
                                    <h4 class="panel-title">Patient Information</h4>
                                </div>
                                <div class="panel-body">
                                    <p><span class="info-label">Name:</span>
                                        <?= htmlspecialchars($data['first_name'].' '.$data['last_name']); ?></p>

                                    <p><span class="info-label">EHR No:</span>
                                        <?= htmlspecialchars($data['ehr_no']); ?></p>

                                    <p><span class="info-label">Phone:</span>
                                        <?= htmlspecialchars($data['phone']); ?></p>

                                    <p><span class="info-label">Gender:</span>
                                        <?= htmlspecialchars($data['gender']); ?></p>

                                    <p><span class="info-label">Date of Birth:</span>
                                        <?= $data['dob'] ? date('M d, Y', strtotime($data['dob'])) : 'N/A'; ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- APPOINTMENT DETAILS -->
                        <div class="col-md-6">
                            <div class="panel panel-white">
                                <div class="panel-heading">
                                    <h4 class="panel-title">Appointment Information</h4>
                                </div>
                                <div class="panel-body">
                                    <p><span class="info-label">Doctor:</span>
                                        Dr. <?= htmlspecialchars($data['doctor_fname'].' '.$data['doctor_lname']); ?></p>

                                    <p><span class="info-label">Specialization:</span>
                                        <?= htmlspecialchars($data['specialization']); ?></p>

                                    <p><span class="info-label">Date:</span>
                                        <?= date('M d, Y', strtotime($data['appointment_date'])); ?></p>

                                    <p><span class="info-label">Time:</span>
                                        <?= date('h:i A', strtotime($data['appointment_time'])); ?></p>

                                    <p><span class="info-label">Type:</span>
                                        <?= htmlspecialchars($data['appointment_type']); ?></p>

                                    <p><span class="info-label">Priority:</span>
                                        <?= ucfirst($data['priority']); ?></p>

                                    <p><span class="info-label">Status:</span>
                                        <span class="status-badge status-<?= $data['status']; ?>">
                                            <?= ucfirst($data['status']); ?>
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- REASON -->
                        <div class="col-md-12">
                            <div class="panel panel-white">
                                <div class="panel-heading">
                                    <h4 class="panel-title">Reason for Appointment</h4>
                                </div>
                                <div class="panel-body">
                                    <?= nl2br(htmlspecialchars($data['reason'])); ?>
                                </div>
                            </div>
                        </div>

                        <!-- ACTION BUTTON -->
                        <div class="col-md-12 text-right">
                            <a href="appointments.php" class="btn btn-default">
                                <i class="fa fa-arrow-left"></i> Back to Appointments
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
