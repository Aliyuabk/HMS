<?php
session_start();
include('include/config.php');

if (!isset($_GET['ehr_no']) || empty($_GET['ehr_no'])) {
    die("Invalid EHR number");
}

$ehr_no = $_GET['ehr_no'];

// Fetch patient by EHR
$stmt = $con->prepare("SELECT id, first_name, last_name FROM patients WHERE ehr_no=? LIMIT 1");
$stmt->bind_param("s", $ehr_no);
$stmt->execute();
$patient = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$patient) {
    die("Patient not found");
}

// Fetch departments
$departments = mysqli_query($con, "SELECT * FROM department ORDER BY dept_name ASC");

// Handle form submission
if(isset($_POST['submit_payment'])){
    $patient_id   = $patient['id'];
    $ehr_no       = $_POST['ehr_no'];
    $patient_name = $_POST['patient_name'];
    $dept_id      = $_POST['dept_id'];
    $item_id      = $_POST['item_id'];

    // Fetch item details
    $stmtItem = $con->prepare("SELECT item_name, price FROM ehr_fees WHERE id=? LIMIT 1");
    $stmtItem->bind_param("i", $item_id);
    $stmtItem->execute();
    $item = $stmtItem->get_result()->fetch_assoc();
    $stmtItem->close();

    // Insert payment request
    $stmt = $con->prepare("INSERT INTO payment_request (patient_id, ehr_no, patient_name, dept_id, item_id, item_name, price) VALUES (?,?,?,?,?,?,?)");
    $stmt->bind_param("issiisd",
        $patient_id,
        $ehr_no,
        $patient_name,
        $dept_id,
        $item_id,
        $item['item_name'],
        $item['price']
    );

    if($stmt->execute()){
        $success = "Payment request submitted successfully";
    } else {
        $error = "Failed to submit payment request";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Reception | Payment Request</title>
    <?php include('include/css.php'); ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<div id="app">
    <?php include('include/sidebar.php'); ?>
    <div class="app-content">
        <?php include('include/header.php'); ?>

        <div class="main-content">
            <div class="wrap-content container">

                <h2>Payment Request</h2>

                <?php if(isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
                <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

                <form method="post">

                    <input type="hidden" name="patient_id" value="<?= $patient['id']; ?>">

                    <div class="form-group">
                        <label>EHR Number</label>
                        <input type="text" class="form-control" value="<?= $ehr_no; ?>" readonly name="ehr_no">
                    </div>

                    <div class="form-group">
                        <label>Patient Name</label>
                        <input type="text" class="form-control" value="<?= $patient['first_name'].' '.$patient['last_name']; ?>" readonly name="patient_name">
                    </div>

                    <div class="form-group">
                        <label>Department</label>
                        <select class="form-control" name="dept_id" id="dept_id" required>
                            <option value="">Select Department</option>
                            <?php while($dept = mysqli_fetch_assoc($departments)): ?>
                                <option value="<?= $dept['id']; ?>"><?= htmlentities($dept['dept_name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Item</label>
                        <select class="form-control" name="item_id" id="item_id" required>
                            <option value="">Select Item</option>
                        </select>
                    </div>

                    <button type="submit" name="submit_payment" class="btn btn-primary">Submit Payment Request</button>
                </form>

            </div>
        </div>
    </div>
    <?php include('include/footer.php'); ?>
    <?php include('include/setting.php'); ?>
</div>

<script>
$(document).ready(function(){
    $('#dept_id').on('change', function(){
        let dept_id = $(this).val();
        if(dept_id !== ''){
            $.ajax({
                url: 'fetch-items.php',
                type: 'POST',
                data: { dept_id: dept_id },
                success: function(response){
                    $('#item_id').html(response);
                }
            });
        } else {
            $('#item_id').html('<option value="">Select Item</option>');
        }
    });
});
</script>

<?php include('include/js.php'); ?>
</body>
</html>