<?php
session_start();
require_once('include/config.php');

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor'){
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch doctor info
$sql = mysqli_query($con, "SELECT * FROM doctor WHERE id='$user_id'");
$user_data = mysqli_fetch_assoc($sql);

// Today's date
$today = date('Y-m-d');

// Fetch today's appointments
$query = "
SELECT 
    a.*, 
    p.first_name, 
    p.last_name, 
    p.ehr_no, 
    p.phone
FROM appointments a
JOIN patients p ON a.patient_id = p.id
WHERE a.doctor_id='$user_id'
AND a.appointment_date='$today'
ORDER BY a.appointment_time ASC
";

$result = mysqli_query($con, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Doctor | Today's Appointments</title>
    <?php include 'include/css.php'; ?>
    <style>
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-scheduled { background: #f0ad4e; color: white; }
        .status-confirmed { background: #5cb85c; color: white; }
        .status-waiting { background: #5bc0de; color: white; }
        .status-cancelled { background: #d9534f; color: white; }
    </style>
</head>
<body>
<div id="app">

    <!-- Sidebar -->
    <div class="sidebar app-aside" id="sidebar">
        <?php include 'include/sidebar.php'; ?>
    </div>

    <!-- Main Content -->
    <div class="app-content">
        <?php include 'include/header.php'; ?>

        <div class="main-content">
            <div class="wrap-content container" id="container">

                <!-- Page Title -->
                <section id="page-title">
                    <div class="row">
                        <div class="col-sm-8">
                            <h2 class="mainTitle">Doctor | Today's Appointments</h2>
                        </div>
                        <ol class="breadcrumb">
                            <li>Doctor</li>
                            <li class="active">Today's Appointments</li>
                        </ol>
                    </div>
                </section>

                <!-- Appointments Table -->
                <div class="container-fluid container-fullw bg-white">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="panel panel-white">
                                <div class="panel-heading">
                                    <h4 class="panel-title">Today's Appointments (<?= date('F d, Y'); ?>)</h4>
                                </div>
                                <div class="panel-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Patient Details</th>
                                                    <th>Time</th>
                                                    <th>Reason</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $cnt = 1;
                                                if(mysqli_num_rows($result) > 0) {
                                                    while($row = mysqli_fetch_assoc($result)) {
                                                ?>
                                                <tr>
                                                    <td><?= $cnt++; ?></td>
                                                    <td>
                                                        <strong><?= htmlspecialchars($row['first_name'].' '.$row['last_name']); ?></strong><br>
                                                        <small>EHR No: <?= htmlspecialchars($row['ehr_no']); ?></small><br>
                                                        <small>Phone: <?= htmlspecialchars($row['phone']); ?></small>
                                                    </td>
                                                    <td><?= date('h:i A', strtotime($row['appointment_time'])); ?></td>
                                                    <td><?= htmlspecialchars($row['reason']); ?></td>
                                                    <td>
                                                        <span class="status-badge status-<?= $row['status']; ?>">
                                                            <?= ucfirst($row['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <a href="view-appointment.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-xs">
                                                                <i class="fa fa-eye"></i> View
                                                            </a>

                                                            <?php if($row['status'] == 'scheduled') { ?>
                                                                <a href="appointments.php?update_status=true&id=<?= $row['id']; ?>&status=confirmed" class="btn btn-success btn-xs" onclick="return confirm('Confirm this appointment?')">
                                                                    <i class="fa fa-check"></i> Confirm
                                                                </a>
                                                                <a href="todays-appointments.php?update_status=true&id=<?= $row['id']; ?>&status=cancelled" class="btn btn-danger btn-xs" onclick="return confirm('Cancel this appointment?')">
                                                                    <i class="fa fa-times"></i> Cancel
                                                                </a>
                                                            <?php } ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php
                                                    }
                                                } else {
                                                ?>
                                                <tr>
                                                    <td colspan="6" class="text-center">No appointments for today</td>
                                                </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
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
