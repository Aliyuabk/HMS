<?php
session_start();
include('include/config.php');

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'billing'){
    header("Location: ../index.php");
    exit;
}

// Fetch pending payments from all three tables, merged by patient
$paymentsQuery = "
SELECT patient_id, SUM(total_amount) AS total_amount, 
       GROUP_CONCAT(service_name SEPARATOR ', ') AS services,
       GROUP_CONCAT(status SEPARATOR ', ') AS statuses
FROM (
    SELECT pr.patient_id, pr.price AS total_amount, pr.item_name AS service_name, pr.status
    FROM payment_request pr
    WHERE pr.status='pending'
    UNION ALL
    SELECT pp.patient_id, pp.price AS total_amount, d.name AS service_name, pp.status
    FROM pharmacy_payment pp
    JOIN drugs d ON d.id = pp.item_id
    WHERE pp.status='pending'
    UNION ALL
    SELECT lp.patient_id, lp.amount AS total_amount, 'Lab Test' AS service_name, lp.status
    FROM lab_payment_request lp
    WHERE lp.status='pending'
) AS all_payments
GROUP BY patient_id
ORDER BY patient_id ASC
";

$paymentsResult = mysqli_query($con, $paymentsQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Pending Bills</title>
    <?php include 'include/css.php'; ?>
</head>
<body>
<div id="app">
<?php include('include/sidebar.php'); ?>
<div class="app-content">
<?php include('include/header.php'); ?>

<div class="main-content">
<div class="wrap-content container" id="container">

<section id="page-title">
    <div class="row">
        <div class="col-sm-8">
            <h2 class="mainTitle">Cashier | Pending Bills</h2>
        </div>
    </div>
</section>

<div class="container-fluid container-fullw bg-white">
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>#</th>
                <th>Patient Name</th>
                <th>EHR No</th>
                <th>Total Amount</th>
                <th>Services</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $count = 1;
        if($paymentsResult && mysqli_num_rows($paymentsResult) > 0){
            while($row = mysqli_fetch_assoc($paymentsResult)){
                $patientId = intval($row['patient_id']);
                $patientQuery = mysqli_query($con, "SELECT first_name, last_name, ehr_no FROM patients WHERE id=$patientId");
                $patient = ['first_name'=>'Unknown','last_name'=>'','ehr_no'=>'N/A'];
                if($patientQuery && mysqli_num_rows($patientQuery) > 0){
                    $patient = mysqli_fetch_assoc($patientQuery);
                }
        ?>
        <tr>
            <td><?= $count++; ?></td>
            <td><?= htmlspecialchars($patient['first_name'].' '.$patient['last_name']); ?></td>
            <td><?= htmlspecialchars($patient['ehr_no']); ?></td>
            <td>₦<?= number_format($row['total_amount'],2); ?></td>
            <td><?= htmlspecialchars($row['services']); ?></td>
            <td><?= ucfirst($row['statuses']); ?></td>
        </tr>
        <?php
            }
        } else {
            echo '<tr><td colspan="6" class="text-center">No pending bills found</td></tr>';
        }
        ?>
        </tbody>
    </table>
</div>

</div>
</div>
</div>

<?php include('include/footer.php'); ?>
<?php include('include/setting.php'); ?>
</div>
<?php include 'include/js.php'; ?>
</body>
</html>
