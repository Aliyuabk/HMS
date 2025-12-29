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

// Get lab request ID
$request_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch lab request details
$request_query = mysqli_query($con, "SELECT * FROM lab_requests WHERE id='$request_id' AND doctor_id='$user_id'");
if(mysqli_num_rows($request_query) == 0){
    echo "<script>alert('Lab request not found');window.location.href='all-request.php';</script>";
    exit;
}
$request = mysqli_fetch_array($request_query);

// Handle form submission
if(isset($_POST['submit'])){
    $patient_id = mysqli_real_escape_string($con, $_POST['patient_id']);
    $appointment_id = !empty($_POST['appointment_id']) ? mysqli_real_escape_string($con, $_POST['appointment_id']) : NULL;
    $test_name = mysqli_real_escape_string($con, $_POST['test_name']);
    $notes = mysqli_real_escape_string($con, $_POST['notes']);
    $status = mysqli_real_escape_string($con, $_POST['status']);

    $update_query = "UPDATE lab_requests SET 
                        patient_id='$patient_id',
                        appointment_id=" . ($appointment_id ? "'$appointment_id'" : "NULL") . ",
                        test_name='$test_name',
                        notes='$notes',
                        status='$status',
                        updated_at=CURRENT_TIMESTAMP
                     WHERE id='$request_id' AND doctor_id='$user_id'";

    if(mysqli_query($con, $update_query)){
        echo "<script>alert('Lab request updated successfully!');window.location.href='all-request.php';</script>";
        exit;
    } else {
        echo "<script>alert('Error: ".mysqli_error($con)."');</script>";
    }
}

// Get patients list
$patients_query = mysqli_query($con, "SELECT id, CONCAT(first_name,' ',last_name) as name, ehr_no FROM patients ORDER BY first_name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Doctor | Edit Lab Request</title>
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
                        <div class="col-sm-8"><h2 class="mainTitle">Doctor | Edit Lab Request</h2></div>
                        <ol class="breadcrumb">
                            <li><span>Doctor</span></li>
                            <li class="active"><span>Lab Requests</span></li>
                        </ol>
                    </div>
                </section>

                <div class="container-fluid container-fullw bg-white">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="panel panel-white">
                                <div class="panel-heading"><h5 class="panel-title">Edit Lab Request</h5></div>
                                <div class="panel-body">
                                    <form method="post">
                                        <div class="form-group">
                                            <label>Select Patient</label>
                                            <select name="patient_id" class="form-control" required>
                                                <option value="">Select Patient</option>
                                                <?php 
                                                mysqli_data_seek($patients_query, 0);
                                                while($p = mysqli_fetch_array($patients_query)){ 
                                                ?>
                                                <option value="<?= $p['id'] ?>" <?= ($request['patient_id']==$p['id'])?'selected':'' ?>>
                                                    <?= htmlspecialchars($p['name']) ?> (<?= htmlspecialchars($p['ehr_no']) ?>)
                                                </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>Appointment ID (Optional)</label>
                                            <input type="text" name="appointment_id" class="form-control" value="<?= htmlspecialchars($request['appointment_id']) ?>" placeholder="Appointment ID">
                                        </div>

                                        <div class="form-group">
                                            <label>Test Name</label>
                                            <input type="text" name="test_name" class="form-control" value="<?= htmlspecialchars($request['test_name']) ?>" required>
                                        </div>

                                        <div class="form-group">
                                            <label>Notes</label>
                                            <textarea name="notes" class="form-control" rows="3"><?= htmlspecialchars($request['notes']) ?></textarea>
                                        </div>

                                        <div class="form-group">
                                            <label>Status</label>
                                            <select name="status" class="form-control" required>
                                                <option value="pending" <?= ($request['status']=='pending')?'selected':'' ?>>Pending</option>
                                                <option value="completed" <?= ($request['status']=='completed')?'selected':'' ?>>Completed</option>
                                                <option value="cancelled" <?= ($request['status']=='cancelled')?'selected':'' ?>>Cancelled</option>
                                            </select>
                                        </div>

                                        <button type="submit" name="submit" class="btn btn-primary">Update Lab Request</button>
                                        <a href="all-request.php" class="btn btn-default">Cancel</a>
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
