<?php
session_start();
require_once('include/config.php');

// Check if doctor is logged in
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor'){
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$sql = mysqli_query($con, "SELECT * FROM doctor WHERE id = '$user_id'");
$user_data = mysqli_fetch_array($sql);

// Get patient and appointment details if provided
$patient_id = isset($_GET['patient_id']) ? $_GET['patient_id'] : '';
$appointment_id = isset($_GET['appointment_id']) ? $_GET['appointment_id'] : '';

if($patient_id) {
    $patient_query = mysqli_query($con, "SELECT * FROM patients WHERE id = '$patient_id'");
    $patient_data = mysqli_fetch_array($patient_query);
}

if($appointment_id) {
    $appointment_query = mysqli_query($con, "
        SELECT a.*, p.first_name, p.last_name, p.ehr_no 
        FROM appointments a 
        JOIN patients p ON a.patient_id = p.id 
        WHERE a.id = '$appointment_id' AND a.doctor_id = '$user_id'
    ");
    $appointment_data = mysqli_fetch_array($appointment_query);
    if($appointment_data) {
        $patient_id = $appointment_data['patient_id'];
        $patient_data = [
            'first_name' => $appointment_data['first_name'],
            'last_name'  => $appointment_data['last_name'],
            'ehr_no'     => $appointment_data['ehr_no']
        ];
    }
}

// Handle form submission
if(isset($_POST['submit'])) {
    $patient_id = mysqli_real_escape_string($con, $_POST['patient_id']);
    $appointment_id = !empty($_POST['appointment_id']) ? mysqli_real_escape_string($con, $_POST['appointment_id']) : NULL;
    $diagnosis = mysqli_real_escape_string($con, $_POST['diagnosis']);
    $medications = mysqli_real_escape_string($con, $_POST['medications']);
    $dosage = mysqli_real_escape_string($con, $_POST['dosage']);
    $instructions = mysqli_real_escape_string($con, $_POST['instructions']);
    $status = mysqli_real_escape_string($con, $_POST['status']);

    $query = "INSERT INTO prescriptions 
        (doctor_id, patient_id, appointment_id, diagnosis, medication, dosage, instructions, status)
        VALUES 
        ('$user_id', '$patient_id', " . ($appointment_id ? "'$appointment_id'" : "NULL") . ", '$diagnosis', '$medications', " . ($dosage ? "'$dosage'" : "NULL") . ", '$instructions', '$status')";

    if(mysqli_query($con, $query)) {
        echo "<script>alert('Prescription added successfully!');</script>";
        echo "<script>window.location.href='prescriptions.php';</script>";
        exit;
    } else {
        echo "<script>alert('Error: " . mysqli_error($con) . "');</script>";
    }
}

// Get patients list for dropdown
$patients_query = mysqli_query($con, "
    SELECT DISTINCT p.id, p.ehr_no, p.first_name, p.last_name
    FROM patients p
    INNER JOIN appointments a ON p.id = a.patient_id
    WHERE a.doctor_id = '$user_id'
    ORDER BY p.first_name ASC, p.last_name ASC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Doctor | Add Prescription</title>
    <?php include 'include/css.php'; ?>
    <script>
        // Live preview of medications
        function updatePreview() {
            let meds = document.getElementsByName('medications')[0].value;
            let dosage = document.getElementsByName('dosage')[0].value;
            let content = "<strong>Medications:</strong><br>" + meds + (dosage ? "<br><strong>Dosage:</strong><br>" + dosage : "");
            document.getElementById('medicationsPreview').innerHTML = content;
            document.getElementById('previewSection').style.display = meds ? 'block' : 'none';
            document.getElementById('emptyPreview').style.display = meds ? 'none' : 'block';
        }
    </script>
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
                            <h2 class="mainTitle">Doctor | Add Prescription</h2>
                        </div>
                        <ol class="breadcrumb">
                            <li><span>Doctor</span></li>
                            <li><span>Prescriptions</span></li>
                            <li class="active"><span>Add Prescription</span></li>
                        </ol>
                    </div>
                </section>
                
                <div class="container-fluid container-fullw bg-white">
                    <div class="row">
                        <!-- Form Section -->
                        <div class="col-md-8">
                            <div class="panel panel-white">
                                <div class="panel-heading"><h5 class="panel-title">Add Prescription</h5></div>
                                <div class="panel-body">
                                    <form method="post">
                                        <div class="form-group">
                                            <label>Select Patient</label>
                                            <select name="patient_id" class="form-control" required>
                                                <option value="">Select Patient</option>
                                                <?php while($patient = mysqli_fetch_array($patients_query)) { ?>
                                                    <option value="<?= $patient['id'] ?>" <?= ($patient_id == $patient['id']) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?> (ID: <?= htmlspecialchars($patient['ehr_no']) ?>)
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>Appointment ID (Optional)</label>
                                            <input type="text" name="appointment_id" class="form-control" value="<?= htmlspecialchars($appointment_id) ?>" placeholder="Appointment ID">
                                        </div>

                                        <div class="form-group">
                                            <label>Diagnosis</label>
                                            <textarea name="diagnosis" class="form-control" rows="3" placeholder="Enter diagnosis..." required></textarea>
                                        </div>

                                        <div class="form-group">
                                            <label>Medications</label>
                                            <textarea name="medications" class="form-control" rows="5" placeholder="Medicine - Frequency - Duration" required oninput="updatePreview()"></textarea>
                                        </div>

                                        <div class="form-group">
                                            <label>Dosage (Optional)</label>
                                            <textarea name="dosage" class="form-control" rows="3" placeholder="Dosage details..." oninput="updatePreview()"></textarea>
                                        </div>

                                        <div class="form-group">
                                            <label>Instructions</label>
                                            <textarea name="instructions" class="form-control" rows="3" placeholder="Patient instructions..." required></textarea>
                                        </div>

                                        <div class="form-group">
                                            <label>Status</label>
                                            <select name="status" class="form-control" required>
                                                <option value="pending">Pending</option>
                                                <option value="completed" selected>Completed</option>
                                                <option value="cancelled">Cancelled</option>
                                            </select>
                                        </div>

                                        <button type="submit" name="submit" class="btn btn-primary">Save Prescription</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Preview Section -->
                        <div class="col-md-4">
                            <div class="panel panel-white">
                                <div class="panel-heading"><h5 class="panel-title">Prescription Preview</h5></div>
                                <div class="panel-body">
                                    <div class="prescription-box" id="previewSection" style="display:none;">
                                        <div class="prescription-header text-center">
                                            <h4>MEDICAL PRESCRIPTION</h4>
                                            <p>HealthDis Hospital</p>
                                            <p>Dr. <?= htmlspecialchars($user_data['first_name'] . ' ' . $user_data['last_name']) ?></p>
                                            <hr>
                                        </div>
                                        <div id="previewContent">
                                            <p><strong>Date:</strong> <?= date('F d, Y') ?></p>
                                            <div id="medicationsPreview"></div>
                                        </div>
                                    </div>
                                    <div id="emptyPreview" class="text-center">
                                        <p>Enter medications to see preview</p>
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
