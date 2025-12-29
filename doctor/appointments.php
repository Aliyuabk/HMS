<?php
session_start();
require_once('include/config.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/* ==========================
   FETCH DOCTOR DETAILS
========================== */
$doctor_q = mysqli_query($con, "SELECT * FROM doctor WHERE id='$user_id'");
$doctor = mysqli_fetch_assoc($doctor_q);

/* ==========================
   UPDATE APPOINTMENT STATUS
========================== */
if (isset($_GET['update_status'])) {
    $appointment_id = intval($_GET['id']);
    $status = $_GET['status'];

    // allowed statuses ONLY
    $allowed_status = ['confirmed', 'waiting', 'cancelled'];

    if (in_array($status, $allowed_status)) {
        mysqli_query($con, "
            UPDATE appointments 
            SET status='$status' 
            WHERE id='$appointment_id' 
            AND doctor_id='$user_id'
        ");
    }

    header("Location: appointments.php");
    exit;
}

/* ==========================
   FILTER & SEARCH
========================== */
$filter = $_GET['filter'] ?? 'all';
$search = $_GET['search'] ?? '';

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
";

if ($filter == 'today') {
    $query .= " AND a.appointment_date = CURDATE()";
} elseif ($filter == 'upcoming') {
    $query .= " AND a.appointment_date > CURDATE()";
} elseif ($filter == 'past') {
    $query .= " AND a.appointment_date < CURDATE()";
} elseif ($filter == 'cancelled') {
    $query .= " AND a.status='cancelled'";
}

if (!empty($search)) {
    $query .= " AND (
        p.first_name LIKE '%$search%' OR
        p.last_name LIKE '%$search%' OR
        p.ehr_no LIKE '%$search%' OR
        p.phone LIKE '%$search%'
    )";
}

$query .= " ORDER BY a.appointment_date DESC, a.appointment_time DESC";
$result = mysqli_query($con, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Doctor | Manage Appointments</title>
    <?php include 'include/css.php'; ?>

    <style>
        .status-badge {
            padding: 4px 8px;
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
                            <h2 class="mainTitle">Doctor | Manage Appointments</h2>
                        </div>
                        <ol class="breadcrumb">
                            <li>Doctor</li>
                            <li class="active">Appointments</li>
                        </ol>
                    </div>
                </section>

                <div class="container-fluid container-fullw bg-white">
                    <div class="row">
                        <div class="col-md-12">

                            <!-- FILTER PANEL -->
                            <div class="panel panel-white">
                                <div class="panel-heading">
                                    <h4 class="panel-title">Filter Appointments</h4>
                                </div>
                                <div class="panel-body">
                                    <form method="get" class="form-inline">
                                        <div class="form-group">
                                            <label>Show:</label>
                                            <select name="filter" class="form-control">
                                                <option value="all">All</option>
                                                <option value="today" <?= ($filter=='today')?'selected':''; ?>>Today</option>
                                                <option value="upcoming" <?= ($filter=='upcoming')?'selected':''; ?>>Upcoming</option>
                                                <option value="past" <?= ($filter=='past')?'selected':''; ?>>Past</option>
                                                <option value="cancelled" <?= ($filter=='cancelled')?'selected':''; ?>>Cancelled</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <input type="text" name="search" class="form-control"
                                                   placeholder="Search patient..."
                                                   value="<?= htmlspecialchars($search); ?>">
                                        </div>

                                        <button type="submit" class="btn btn-primary">Filter</button>
                                        <a href="appointments.php" class="btn btn-default">Reset</a>
                                    </form>
                                </div>
                            </div>

                            <!-- APPOINTMENTS TABLE -->
                            <div class="panel panel-white">
                                <div class="panel-heading">
                                    <h4 class="panel-title">Appointments List</h4>
                                </div>

                                <div class="panel-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Patient</th>
                                                <th>Date & Time</th>
                                                <th>Reason</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                            </thead>
                                            <tbody>

                                            <?php
                                            $cnt = 1;
                                            if (mysqli_num_rows($result) > 0) {
                                                while ($row = mysqli_fetch_assoc($result)) {
                                            ?>
                                            <tr>
                                                <td><?= $cnt++; ?></td>

                                                <td>
                                                    <strong><?= htmlspecialchars($row['first_name'].' '.$row['last_name']); ?></strong><br>
                                                    <small>EHR No: <?= htmlspecialchars($row['ehr_no']); ?></small><br>
                                                    <small>Phone: <?= htmlspecialchars($row['phone']); ?></small>
                                                </td>

                                                <td>
                                                    <?= date('M d, Y', strtotime($row['appointment_date'])); ?><br>
                                                    <small><?= date('h:i A', strtotime($row['appointment_time'])); ?></small>
                                                </td>

                                                <td><?= htmlspecialchars($row['reason']); ?></td>

                                                <td>
                                                    <span class="status-badge status-<?= $row['status']; ?>">
                                                        <?= ucfirst($row['status']); ?>
                                                    </span>
                                                </td>

                                                <td>
                                                    <a href="view-appointment.php?id=<?= $row['id']; ?>"
                                                       class="btn btn-primary btn-xs">
                                                        <i class="fa fa-eye"></i> View
                                                    </a>

                                                    <?php if ($row['status'] == 'scheduled') { ?>
                                                        <a href="?update_status=true&id=<?= $row['id']; ?>&status=confirmed"
                                                           class="btn btn-success btn-xs"
                                                           onclick="return confirm('Confirm appointment?')">
                                                            Confirm
                                                        </a>

                                                        <a href="?update_status=true&id=<?= $row['id']; ?>&status=cancelled"
                                                           class="btn btn-danger btn-xs"
                                                           onclick="return confirm('Cancel appointment?')">
                                                            Cancel
                                                        </a>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                            <?php
                                                }
                                            } else {
                                            ?>
                                            <tr>
                                                <td colspan="6" class="text-center">
                                                    No appointments found
                                                </td>
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
