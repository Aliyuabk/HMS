<?php
session_start();
require_once('include/config.php');

/* =======================
   AUTH CHECK
======================= */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
    header("Location: ../index.php");
    exit;
}

$doctor_id = $_SESSION['user_id'];

/* =======================
   HANDLE FORM SUBMISSION
======================= */
if (isset($_POST['submit'])) {

    $patient_id     = mysqli_real_escape_string($con, $_POST['patient_id']);
    $appointment_id = !empty($_POST['appointment_id']) 
                        ? mysqli_real_escape_string($con, $_POST['appointment_id']) 
                        : NULL;
    $priority       = mysqli_real_escape_string($con, $_POST['priority']);
    $notes          = mysqli_real_escape_string($con, $_POST['notes']);

    // Insert main lab request
    $sql_request = "
        INSERT INTO lab_requests 
        (doctor_id, patient_id, appointment_id, request_note, priority, status)
        VALUES 
        ('$doctor_id', '$patient_id', " . ($appointment_id ? "'$appointment_id'" : "NULL") . ", '$notes', '$priority', 'pending')
    ";

    if (mysqli_query($con, $sql_request)) {

        $lab_request_id = mysqli_insert_id($con);

        // Insert each test
        foreach ($_POST['tests'] as $i => $test_name) {

            $test_name   = mysqli_real_escape_string($con, $test_name);
            $sample_type = mysqli_real_escape_string($con, $_POST['sample_type'][$i]);

            mysqli_query($con, "
                INSERT INTO lab_request_tests
                (lab_request_id, test_name, sample_type, status)
                VALUES
                ('$lab_request_id', '$test_name', '$sample_type', 'pending')
            ");
        }

        echo "<script>
            alert('Lab request submitted successfully');
            window.location.href='all-request.php';
        </script>";
        exit;

    } else {
        echo "<script>alert('Database Error: ".mysqli_error($con)."');</script>";
    }
}

/* =======================
   GET PATIENTS WITH APPOINTMENTS
======================= */
$patients_query = mysqli_query($con, "
    SELECT 
        a.id AS appointment_id,
        p.id AS patient_id,
        CONCAT(p.first_name,' ',p.last_name) AS fullname,
        p.ehr_no,
        a.appointment_date
    FROM appointments a
    INNER JOIN patients p ON p.id = a.patient_id
    WHERE a.doctor_id = '$doctor_id'
    ORDER BY a.appointment_date DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Doctor | Add Lab Request</title>
    <?php include 'include/css.php'; ?>
</head>

<body>
<div id="app">

    <div class="sidebar app-aside" id="sidebar">
        <?php include 'include/sidebar.php'; ?>
    </div>

    <div class="app-content">
        <?php include 'include/header.php'; ?>

        <div class="main-content">
            <div class="wrap-content container" id="container">

                <section id="page-title">
                    <div class="row">
                        <div class="col-sm-8">
                            <h2 class="mainTitle">Doctor | Add Lab Request</h2>
                        </div>
                        <ol class="breadcrumb">
                            <li><span>Doctor</span></li>
                            <li class="active"><span>Lab Request</span></li>
                        </ol>
                    </div>
                </section>

                <div class="container-fluid container-fullw bg-white">
                    <div class="row">
                        <div class="col-md-10">

                            <div class="panel panel-white">
                                <div class="panel-heading">
                                    <h5 class="panel-title">New Laboratory Request</h5>
                                </div>

                                <div class="panel-body">

                                    <form method="post">

                                        <!-- Patient -->
                                        <div class="form-group">
                                            <label>Select Patient</label>
                                            <select name="patient_id" class="form-control" id="patientSelect" required>
                                                <option value="">-- Select Patient --</option>
                                                <?php while($p = mysqli_fetch_array($patients_query)) { ?>
                                                <option value="<?= $p['patient_id']; ?>"
                                                        data-appointment="<?= $p['appointment_id']; ?>">
                                                    <?= htmlspecialchars($p['fullname']); ?>
                                                    (<?= $p['ehr_no']; ?>)
                                                    - <?= date('d M Y', strtotime($p['appointment_date'])); ?>
                                                </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <!-- Appointment -->
                                        <div class="form-group">
                                            <label>Appointment ID</label>
                                            <input type="text" name="appointment_id" class="form-control" readonly>
                                        </div>

                                        <!-- Priority -->
                                        <div class="form-group">
                                            <label>Priority</label>
                                            <select name="priority" class="form-control">
                                                <option value="routine">Routine</option>
                                                <option value="urgent">Urgent</option>
                                            </select>
                                        </div>

                                        <hr>

                                        <!-- Tests -->
                                        <h5>Requested Tests</h5>

                                        <div id="tests-wrapper">
                                            <div class="row test-row">
                                                <div class="col-md-5">
                                                    <input type="text" name="tests[]" class="form-control"
                                                           placeholder="Test name (e.g. FBC)" required>
                                                </div>

                                                <div class="col-md-4">
                                                    <select name="sample_type[]" class="form-control" required>
                                                        <option value="">Sample Type</option>
                                                        <option value="blood">Blood</option>
                                                        <option value="urine">Urine</option>
                                                        <option value="stool">Stool</option>
                                                        <option value="sputum">Sputum</option>
                                                        <option value="swab">Swab</option>
                                                    </select>
                                                </div>

                                                <div class="col-md-3">
                                                    <button type="button" class="btn btn-danger remove-test">Remove</button>
                                                </div>
                                            </div>
                                        </div>

                                        <br>
                                        <button type="button" class="btn btn-success" id="addTest">
                                            + Add Another Test
                                        </button>

                                        <hr>

                                        <!-- Notes -->
                                        <div class="form-group">
                                            <label>Clinical Notes</label>
                                            <textarea name="notes" class="form-control" rows="3"
                                                      placeholder="Any special instructions for the lab"></textarea>
                                        </div>

                                        <button type="submit" name="submit" class="btn btn-primary">
                                            Submit Lab Request
                                        </button>

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
// Auto-fill appointment ID
document.getElementById('patientSelect').addEventListener('change', function () {
    let appointment = this.selectedOptions[0].getAttribute('data-appointment');
    document.querySelector('input[name="appointment_id"]').value = appointment || '';
});

// Add new test row
document.getElementById('addTest').addEventListener('click', function () {
    let wrapper = document.getElementById('tests-wrapper');
    let row = document.querySelector('.test-row').cloneNode(true);
    row.querySelectorAll('input, select').forEach(el => el.value = '');
    wrapper.appendChild(row);
});

// Remove test row
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('remove-test')) {
        let rows = document.querySelectorAll('.test-row');
        if (rows.length > 1) {
            e.target.closest('.test-row').remove();
        }
    }
});
</script>

</body>
</html>
