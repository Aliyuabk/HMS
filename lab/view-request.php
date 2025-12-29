<?php
session_start();
require_once('include/config.php');

/* =======================
   AUTH CHECK
======================= */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'lab') {
    header("Location: index.php");
    exit;
}

$request_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$request_id) {
    header("Location: ../index.php");
    exit;
}

/* =======================
   FETCH REQUEST + PATIENT
======================= */
$request = mysqli_fetch_assoc(mysqli_query($con, "
    SELECT lr.*, p.id as patient_id, p.first_name, p.last_name, p.ehr_no, a.appointment_date
    FROM lab_requests lr
    INNER JOIN patients p ON p.id=lr.patient_id
    LEFT JOIN appointments a ON a.id=lr.appointment_id
    WHERE lr.id='$request_id'
"));

$tests = mysqli_query($con, "
    SELECT * FROM lab_request_tests 
    WHERE lab_request_id='$request_id'
");

/* =======================
   HANDLE RESULT SUBMISSION
======================= */
if (isset($_POST['submit_results']) && $request['status'] != 'completed') {

    $lab_id = $_SESSION['user_id']; // Current lab staff id

    foreach ($_POST['results'] as $test_id => $result) {
        $result = mysqli_real_escape_string($con, $result);
        $unit = mysqli_real_escape_string($con, $_POST['unit'][$test_id]);
        $ref  = mysqli_real_escape_string($con, $_POST['reference_range'][$test_id]);

        // Update the test result
        mysqli_query($con, "
            UPDATE lab_request_tests
            SET result='$result', unit='$unit', reference_range='$ref', status='completed', performed_by ='$lab_id', completed_at=NOW()
            WHERE id='$test_id'
        ");

        // Log per-test activity
        mysqli_query($con, "
            INSERT INTO lab_activity_log (lab_request_id, test_id, action, performed_by)
            VALUES ('$request_id', '$test_id', 'Result Submitted', '$lab_id')
        ");
    }

    // Update main request status to completed
    mysqli_query($con, "
        UPDATE lab_requests
        SET status='completed', updated_at=NOW()
        WHERE id='$request_id'
    ");

    // Log overall request completion
    mysqli_query($con, "
        INSERT INTO lab_activity_log (lab_request_id, action, performed_by)
        VALUES ('$request_id', 'Request Completed', '$lab_id')
    ");

    // Create or update payment request
    $price_row = mysqli_fetch_assoc(mysqli_query($con, "SELECT price FROM lab_price LIMIT 1"));
    $total_amount = $price_row ? $price_row['price'] : 0;

    $check = mysqli_query($con, "SELECT id FROM lab_payment_request WHERE lab_request_id='$request_id'");
    if(mysqli_num_rows($check) > 0){
        mysqli_query($con, "
            UPDATE lab_payment_request 
            SET amount='$total_amount', status='pending', updated_at=NOW()
            WHERE lab_request_id='$request_id'
        ");
    } else {
        mysqli_query($con, "
            INSERT INTO lab_payment_request 
            (lab_request_id, patient_id, amount, status)
            VALUES ('{$request_id}', '{$request['patient_id']}', '$total_amount', 'pending')
        ");
    }

    echo "<script>alert('Results submitted successfully. Payment request created/updated.');window.location='index.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Lab | View Request</title>
    <?php include 'include/css.php'; ?>
</head>
<body>
<div id="app">
    <div class="sidebar app-aside" id="sidebar"><?php include 'include/sidebar.php'; ?></div>
    <div class="app-content">
        <?php include 'include/header.php'; ?>

        <div class="main-content">
            <div class="wrap-content container" id="container">

                <section id="page-title">
                    <div class="row">
                        <div class="col-sm-8">
                            <h2 class="mainTitle">Lab Request Details</h2>
                        </div>
                        <ol class="breadcrumb">
                            <li><span>Laboratory</span></li>
                            <li class="active"><span>View Request</span></li>
                        </ol>
                    </div>
                </section>

                <div class="container-fluid container-fullw bg-white">
                    <div class="row">
                        <div class="col-md-12">

                            <div class="panel panel-white">
                                <div class="panel-heading">
                                    <strong>Patient: <?= htmlspecialchars($request['first_name'].' '.$request['last_name']); ?></strong>
                                    | EHR No: <?= htmlspecialchars($request['ehr_no']); ?>
                                </div>
                                <div class="panel-body">
                                    <p><strong>Appointment Date:</strong> <?= $request['appointment_date'] ? date('d M Y', strtotime($request['appointment_date'])) : 'N/A'; ?></p>
                                    <p><strong>Requested On:</strong> <?= date('d M Y', strtotime($request['created_at'])); ?></p>
                                    <p><strong>Priority:</strong> <?= ucfirst($request['priority']); ?></p>
                                    <p><strong>Status:</strong> <?= ucfirst($request['status']); ?></p>

                                    <hr>

                                    <?php if($request['status'] != 'completed') { ?>
                                    <!-- Show form only if not completed -->
                                    <form method="post">

                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Test Name</th>
                                                    <th>Sample Type</th>
                                                    <th>Result</th>
                                                    <th>Unit</th>
                                                    <th>Reference Range</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while($t = mysqli_fetch_assoc($tests)) { ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($t['test_name']); ?></td>
                                                    <td><?= ucfirst($t['sample_type']); ?></td>
                                                    <td><input type="text" name="results[<?= $t['id']; ?>]" class="form-control" value="<?= htmlspecialchars($t['result']); ?>"></td>
                                                    <td><input type="text" name="unit[<?= $t['id']; ?>]" class="form-control" value="<?= htmlspecialchars($t['unit']); ?>"></td>
                                                    <td><input type="text" name="reference_range[<?= $t['id']; ?>]" class="form-control" value="<?= htmlspecialchars($t['reference_range']); ?>"></td>
                                                    <td><?= ucfirst($t['status']); ?></td>
                                                </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>

                                        <button type="submit" name="submit_results" class="btn btn-success">Submit Results & Create Payment</button>
                                    </form>
                                    <?php } else { ?>
                                        <p class="alert alert-info">Results have already been submitted for this request.</p>
                                    <?php } ?>

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
