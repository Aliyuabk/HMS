<?php
session_start();
include('include/config.php');

// Helper function to get day name
function dayName($date) {
    return date('l', strtotime($date));
}

// Get selected doctor and date
$doctorId = isset($_GET['doctor_id']) ? (int)$_GET['doctor_id'] : 1;
$selectedDate = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Fetch doctor info
$doctorName = "Unknown Doctor";
if($docResult = $con->query("SELECT first_name, last_name FROM doctor WHERE id=$doctorId")) {
    $doctor = $docResult->fetch_assoc();
    if($doctor) $doctorName = "Dr. " . $doctor['first_name'] . " " . $doctor['last_name'];
}

// Week boundaries
$startOfWeek = date('Y-m-d', strtotime('monday this week', strtotime($selectedDate)));
$endOfWeek   = date('Y-m-d', strtotime('sunday this week', strtotime($selectedDate)));

// Fetch weekly schedule
$weeklySchedule = [];
$stmt = $con->prepare("
    SELECT shift_date, shift_type
    FROM duty_roster
    WHERE doctor_id=? AND shift_date BETWEEN ? AND ?
    ORDER BY shift_date, FIELD(shift_type,'Morning','Evening','Night','Off')
");
if($stmt){
    $stmt->bind_param("iss", $doctorId, $startOfWeek, $endOfWeek);
    $stmt->execute();
    $result = $stmt->get_result();
    while($row = $result->fetch_assoc()){
        $date = $row['shift_date'];
        $type = $row['shift_type'];
        $weeklySchedule[$date][$type] = $type;
    }
    $stmt->close();
} else {
    die("Error fetching weekly schedule: " . $con->error);
}

// Fetch today's appointments
$appointments = [];
$stmt = $con->prepare("
    SELECT a.appointment_time, a.appointment_type AS type, a.status, 
           p.first_name AS patient_fname, p.last_name AS patient_lname
    FROM appointments a
    JOIN patients p ON a.patient_id = p.id
    WHERE a.doctor_id=? AND a.appointment_date=?
    ORDER BY a.appointment_time ASC
");
if($stmt){
    $stmt->bind_param("is", $doctorId, $selectedDate);
    $stmt->execute();
    $result = $stmt->get_result();
    while($row = $result->fetch_assoc()){
        $appointments[] = $row;
    }
    $stmt->close();
}

// Fetch upcoming leaves/time off (next 10 entries)
$leaves = [];
$stmt = $con->prepare("
    SELECT d.first_name, d.last_name, dr.shift_type, dr.shift_date
    FROM duty_roster dr
    JOIN doctor d ON dr.doctor_id = d.id
    WHERE dr.shift_type='Off' AND dr.shift_date >= ?
    ORDER BY dr.shift_date ASC
    LIMIT 10
");
if($stmt){
    $stmt->bind_param("s", $selectedDate);
    $stmt->execute();
    $result = $stmt->get_result();
    while($row = $result->fetch_assoc()){
        $leaves[] = $row;
    }
    $stmt->close();
}

// Function to return label class for shifts
function shiftLabel($shiftType) {
    switch($shiftType) {
        case 'Morning': return 'success';
        case 'Evening': return 'info';
        case 'Night':   return 'warning';
        case 'Off':     return 'danger';
        default:        return 'default';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Reception | Doctor Schedule</title>
    <?php include('include/css.php'); ?>
</head>
<body>
<div id="app">
    <?php include('include/sidebar.php'); ?>
    <div class="app-content">
        <?php include('include/header.php'); ?>

        <div class="main-content">
            <div class="wrap-content container" id="container">

                <!-- Page Title -->
                <section id="page-title">
                    <div class="row">
                        <div class="col-sm-8">
                            <h2 class="mainTitle">Reception | Doctor Schedule</h2>
                        </div>
                        <ol class="breadcrumb">
                            <li><span>Reception</span></li>
                            <li class="active"><span>Doctor Schedule</span></li>
                        </ol>
                    </div>
                </section>

                <!-- Doctor Selection -->
                <div class="container-fluid container-fullw bg-white">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Select Doctor</label>
                                <select class="form-control" id="doctor-select" onchange="updateSchedule()">
                                    <?php
                                    $docResult = $con->query("SELECT * FROM doctor ORDER BY first_name");
                                    while($doc = $docResult->fetch_assoc()){
                                        $selected = ($doc['id']==$doctorId) ? 'selected' : '';
                                        echo "<option value='{$doc['id']}' $selected>Dr. {$doc['first_name']} {$doc['last_name']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Select Date</label>
                                <input type="date" class="form-control" id="schedule-date" value="<?php echo $selectedDate; ?>" onchange="updateSchedule()">
                            </div>
                        </div>
                    </div>

                    <!-- Weekly Schedule -->
                    <div class="panel panel-white">
                        <div class="panel-heading">
                            <h4 class="panel-title">Weekly Schedule - <?php echo $doctorName; ?></h4>
                        </div>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>Day</th>
                                            <th>Morning</th>
                                            <th>Evening</th>
                                            <th>Night</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    for($d=strtotime($startOfWeek); $d<=strtotime($endOfWeek); $d+=86400){
                                        $dateStr = date('Y-m-d', $d);
                                        $schedule = $weeklySchedule[$dateStr] ?? [];
                                        ?>
                                        <tr>
                                            <td><?php echo dayName($dateStr); ?></td>
                                            <td><span class="label label-<?php echo isset($schedule['Morning']) ? shiftLabel('Morning') : 'default'; ?>">
                                                <?php echo $schedule['Morning'] ?? 'Not Available'; ?></span></td>
                                            <td><span class="label label-<?php echo isset($schedule['Evening']) ? shiftLabel('Evening') : 'default'; ?>">
                                                <?php echo $schedule['Evening'] ?? 'Not Available'; ?></span></td>
                                            <td><span class="label label-<?php echo isset($schedule['Night']) ? shiftLabel('Night') : 'default'; ?>">
                                                <?php echo $schedule['Night'] ?? 'Not Available'; ?></span></td>
                                            <td><span class="label label-<?php echo isset($schedule['Off']) ? shiftLabel('Off') : 'default'; ?>">
                                                <?php echo isset($schedule['Off']) ? 'Off' : 'Regular'; ?></span></td>
                                        </tr>
                                    <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Today's Appointments -->
                    <div class="panel panel-white">
                        <div class="panel-heading">
                            <h4 class="panel-title">Today's Appointments</h4>
                        </div>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>Time</th>
                                            <th>Patient</th>
                                            <th>Type</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php if(!empty($appointments)): ?>
                                        <?php foreach($appointments as $a): ?>
                                        <tr>
                                            <td><?php echo $a['appointment_time']; ?></td>
                                            <td><?php echo $a['patient_fname'].' '.$a['patient_lname']; ?></td>
                                            <td><?php echo $a['type']; ?></td>
                                            <td><span class="label label-<?php 
                                                echo strtolower($a['status'])=='confirmed' ? 'success' : 
                                                     (strtolower($a['status'])=='waiting' ? 'warning' : 
                                                     (strtolower($a['status'])=='cancelled' ? 'danger' : 'default')); ?>">
                                                <?php echo ucfirst($a['status']); ?></span></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="4" class="text-center">No appointments today.</td></tr>
                                    <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Upcoming Leaves -->
                    <div class="panel panel-white">
                        <div class="panel-heading">
                            <h4 class="panel-title">Upcoming Leave/Time Off</h4>
                        </div>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>Doctor</th>
                                            <th>Leave Type</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php if(!empty($leaves)): ?>
                                        <?php foreach($leaves as $l): ?>
                                        <tr>
                                            <td><?php echo "Dr. ".$l['first_name']." ".$l['last_name']; ?></td>
                                            <td><?php echo $l['shift_type']; ?></td>
                                            <td><?php echo $l['shift_date']; ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="3" class="text-center">No upcoming leaves/time off.</td></tr>
                                    <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <?php include('include/footer.php'); ?>
        <?php include('include/setting.php'); ?>
    </div>

    <?php include('include/js.php'); ?>
    <script>
function updateSchedule() {
    const doctorId = document.getElementById('doctor-select').value;
    const date = document.getElementById('schedule-date').value;

    // Build URL with current page and query parameters
    const url = new URL(window.location.href);
    url.searchParams.set('doctor_id', doctorId);
    url.searchParams.set('date', date);

    // Redirect to updated URL
    window.location.href = url.toString();
}
</script>

    <script>
    function updateSchedule() {
        const doctorId = document.getElementById('doctor-select').value;
        const date = document.getElementById('schedule-date').value;
        window.location.href = `?doctor_id=${doctorId}&date=${date}`;
    }
    </script>
</div>
</body>
</html>
