<?php
session_start();
include('include/config.php');
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../index.php");
    exit;
}
$user_id = $_SESSION['user_id'];
$sql=mysqli_query($con,"select * from staff where id = '$user_id'");
$data=mysqli_fetch_array($sql);

// Handle appointment booking
if(isset($_POST['book_appointment'])) {
    $patient_id = $_POST['patient_id'];
    $doctor_id = $_POST['doctor_id'];
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];
    $reason = $_POST['reason'];
    $status = 'scheduled';
    
    // Generate appointment ID
    $appointment_id = 'APT' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    
    $query = "INSERT INTO appointments (appointment_id, patient_id, doctor_id, appointment_date, appointment_time, reason, status, created_by) 
              VALUES ('$appointment_id', '$patient_id', '$doctor_id', '$appointment_date', '$appointment_time', '$reason', '$status', '$user_id')";
    
    if(mysqli_query($con, $query)) {
        $success = "Appointment booked successfully! Appointment ID: $appointment_id";
    } else {
        $error = "Error booking appointment: " . mysqli_error($con);
    }
}

// Fetch patients
$patients_result = mysqli_query($con, "SELECT id, patient_id, fullname FROM patients ORDER BY fullname");

// Fetch doctors
$doctors_result = mysqli_query($con, "SELECT id, fullname, specialization FROM staff WHERE role='doctor' AND status='active' ORDER BY fullname");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin | Book Appointment</title>
     <?php include 'include/css.php';?>
</head>
<body>
    <div id="app">		
        <?php include('include/sidebar.php');?>
        <div class="app-content">
            <?php include('include/header.php');?>						
            <div class="main-content">
                <div class="wrap-content container" id="container">
                    <section id="page-title">
                        <div class="row">
                            <div class="col-sm-8">
                                <h2 class="mainTitle">Admin | Book Appointment</h2>
                            </div>
                            <ol class="breadcrumb">
                                <li><span>Admin</span></li>
                                <li class="active"><span>Book Appointment</span></li>
                            </ol>
                        </div>
                    </section>

                    <div class="container-fluid container-fullw bg-white">
                        <div class="row">
                            <div class="col-md-8 col-md-offset-2">
                                <div class="panel panel-white">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">Book New Appointment</h4>
                                    </div>
                                    <div class="panel-body">
                                        <?php if(isset($success)): ?>
                                        <div class="alert alert-success">
                                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                            <strong>Success!</strong> <?php echo $success; ?>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if(isset($error)): ?>
                                        <div class="alert alert-danger">
                                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                            <strong>Error!</strong> <?php echo $error; ?>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <form method="POST" action="">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Select Patient <span class="text-danger">*</span></label>
                                                        <select name="patient_id" class="form-control" required>
                                                            <option value="">-- Select Patient --</option>
                                                            <?php while($patient = mysqli_fetch_assoc($patients_result)): ?>
                                                            <option value="<?php echo $patient['id']; ?>">
                                                                <?php echo htmlentities($patient['fullname']) . ' (ID: ' . $patient['patient_id'] . ')'; ?>
                                                            </option>
                                                            <?php endwhile; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Select Doctor <span class="text-danger">*</span></label>
                                                        <select name="doctor_id" class="form-control" required>
                                                            <option value="">-- Select Doctor --</option>
                                                            <?php while($doctor = mysqli_fetch_assoc($doctors_result)): ?>
                                                            <option value="<?php echo $doctor['id']; ?>">
                                                                <?php echo htmlentities($doctor['fullname']) . ' (' . $doctor['specialization'] . ')'; ?>
                                                            </option>
                                                            <?php endwhile; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Appointment Date <span class="text-danger">*</span></label>
                                                        <input type="date" name="appointment_date" class="form-control" required 
                                                               min="<?php echo date('Y-m-d'); ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Appointment Time <span class="text-danger">*</span></label>
                                                        <select name="appointment_time" class="form-control" required>
                                                            <option value="">-- Select Time --</option>
                                                            <?php
                                                            for($hour = 9; $hour <= 17; $hour++) {
                                                                for($minute = 0; $minute <= 30; $minute += 30) {
                                                                    $time = sprintf("%02d:%02d", $hour, $minute);
                                                                    echo "<option value='$time'>$time</option>";
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label>Reason for Appointment</label>
                                                        <textarea name="reason" class="form-control" rows="3" placeholder="Enter reason for appointment..."></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <button type="submit" name="book_appointment" class="btn btn-primary">
                                                        <i class="fa fa-calendar"></i> Book Appointment
                                                    </button>
                                                    <button type="reset" class="btn btn-default">Reset</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="panel panel-white">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">Recent Appointments</h4>
                                    </div>
                                    <div class="panel-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Appointment ID</th>
                                                        <th>Patient Name</th>
                                                        <th>Doctor Name</th>
                                                        <th>Date</th>
                                                        <th>Time</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $recent_query = "SELECT a.*, p.fullname as patient_name, s.fullname as doctor_name 
                                                                    FROM appointments a 
                                                                    JOIN patients p ON a.patient_id = p.id 
                                                                    JOIN staff s ON a.doctor_id = s.id 
                                                                    ORDER BY a.created_at DESC LIMIT 10";
                                                    $recent_result = mysqli_query($con, $recent_query);
                                                    $count = 1;
                                                    
                                                    if(mysqli_num_rows($recent_result) > 0) {
                                                        while($row = mysqli_fetch_assoc($recent_result)) {
                                                    ?>
                                                    <tr>
                                                        <td><?php echo $count++; ?></td>
                                                        <td><?php echo htmlentities($row['appointment_id']); ?></td>
                                                        <td><?php echo htmlentities($row['patient_name']); ?></td>
                                                        <td><?php echo htmlentities($row['doctor_name']); ?></td>
                                                        <td><?php echo date('d-m-Y', strtotime($row['appointment_date'])); ?></td>
                                                        <td><?php echo $row['appointment_time']; ?></td>
                                                        <td>
                                                            <span class="label label-<?php 
                                                                echo $row['status'] == 'completed' ? 'success' : 
                                                                ($row['status'] == 'cancelled' ? 'danger' : 'warning');
                                                            ?>">
                                                                <?php echo ucfirst($row['status']); ?>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    <?php
                                                        }
                                                    } else {
                                                    ?>
                                                    <tr>
                                                        <td colspan="7" class="text-center">No appointments found.</td>
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
        <?php include('include/footer.php');?>
        <?php include('include/setting.php');?>
    </div>
    
     <?php include 'include/js.php';?>
</body>
</html>