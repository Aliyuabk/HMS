<?php
session_start();
require_once('include/config.php');

// Check doctor login
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor'){
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$sql = mysqli_query($con, "SELECT * FROM doctor WHERE id='$user_id'");
$user_data = mysqli_fetch_array($sql);

// Handle form submission
if(isset($_POST['submit'])){
    $patient_id = mysqli_real_escape_string($con, $_POST['patient_id']);
    $appointment_id = !empty($_POST['appointment_id']) ? mysqli_real_escape_string($con, $_POST['appointment_id']) : NULL;
    $test_name = mysqli_real_escape_string($con, $_POST['test_name']);
    $notes = mysqli_real_escape_string($con, $_POST['notes']);

    $query = "INSERT INTO radiology_requests (doctor_id, patient_id, appointment_id, test_name, notes, status) 
              VALUES ('$user_id', '$patient_id', ".($appointment_id ? "'$appointment_id'" : "NULL").", '$test_name', '$notes', 'pending')";
    
    if(mysqli_query($con, $query)){
        echo "<script>alert('Radiology request added successfully!');window.location.href='all-radiology-request.php';</script>";
        exit;
    } else {
        echo "<script>alert('Error: ".mysqli_error($con)."');</script>";
    }
}

// Get patients list who have appointments with this doctor
$patients_query = mysqli_query($con, "
    SELECT a.id as appointment_id, p.id as patient_id, CONCAT(p.first_name,' ',p.last_name) as name, p.ehr_no, a.appointment_date
    FROM patients p
    INNER JOIN appointments a ON p.id = a.patient_id
    WHERE a.doctor_id = '$user_id'
    ORDER BY a.appointment_date ASC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Doctor | Add Radiology Request</title>
    <?php include 'include/css.php'; ?>
</head>
<body>
<div id="app">
    <div class="sidebar app-aside" id="sidebar"><?php include('include/sidebar.php'); ?></div>
    <div class="app-content">
        <?php include 'include/header.php'; ?>
        <div class="main-content">
            <div class="wrap-content container" id="container">
                <section id="page-title">
                    <div class="row">
                        <div class="col-sm-8"><h2 class="mainTitle">Doctor | Add Radiology Request</h2></div>
                        <ol class="breadcrumb">
                            <li><span>Doctor</span></li>
                            <li class="active"><span>Radiology Requests</span></li>
                        </ol>
                    </div>
                </section>

                <div class="container-fluid container-fullw bg-white">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="panel panel-white">
                                <div class="panel-heading"><h5 class="panel-title">Add Radiology Request</h5></div>
                                <div class="panel-body">
                                    <form method="post">
                                        <div class="form-group">
                                            <label>Select Patient (Appointment)</label>
                                            <select name="patient_id" class="form-control" required>
                                                <option value="">Select Patient</option>
                                                <?php while($p = mysqli_fetch_array($patients_query)){ ?>
                                                <option value="<?= $p['patient_id'] ?>" data-appointment="<?= $p['appointment_id'] ?>">
                                                    <?= htmlspecialchars($p['name']) ?> (<?= htmlspecialchars($p['ehr_no']) ?>) - <?= date('d M Y', strtotime($p['appointment_date'])) ?>
                                                </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>Appointment ID (Auto-fill)</label>
                                            <input type="text" name="appointment_id" class="form-control" placeholder="Appointment ID" readonly>
                                        </div>

                                        <div class="form-group">
                                            <label>Test Name</label>
                                            <input type="text" name="test_name" class="form-control" placeholder="E.g., CBC, X-Ray" required>
                                        </div>

                                        <div class="form-group">
                                            <label>Notes</label>
                                            <textarea name="notes" class="form-control" rows="3" placeholder="Any additional instructions..."></textarea>
                                        </div>

                                        <button type="submit" name="submit" class="btn btn-primary">Submit Radiology Request</button>
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
<script>
// Auto-fill Appointment ID when patient selected
document.querySelector('select[name="patient_id"]').addEventListener('change', function() {
    let appointmentId = this.selectedOptions[0].getAttribute('data-appointment');
    document.querySelector('input[name="appointment_id"]').value = appointmentId;
});
</script>
</body>
</html>
