<?php
session_start();
include('include/config.php');

// Get parameters
$patient_id = isset($_GET['patient_id']) ? intval($_GET['patient_id']) : 0;
$created_date = isset($_GET['created_date']) ? $_GET['created_date'] : '';

if(!$patient_id || !$created_date){
    die("Invalid parameters");
}

// Fetch patient info
$patient = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM patients WHERE id=$patient_id"));
if(!$patient) die("Patient not found");

// Fetch all payments for this patient and date from all three tables
$payments = [];
$total_price = 0;

// 1️⃣ Department payments
$deptPayments = mysqli_query($con, "
    SELECT pr.*, d.dept_name, f.item_name, pr.price
    FROM payment_request pr
    LEFT JOIN department d ON pr.dept_id = d.id
    LEFT JOIN ehr_fees f ON pr.item_id = f.id
    WHERE pr.patient_id=$patient_id AND DATE(pr.created_at)='$created_date'
");
while($row = mysqli_fetch_assoc($deptPayments)){
    $row['source'] = 'Dept';
    $row['item_name'] = $row['item_name'] ?? 'Service';
    $payments[] = $row;
    $total_price += $row['price'];
}

// 2️⃣ Pharmacy payments
$pharmPayments = mysqli_query($con, "
    SELECT pp.*, d.name AS item_name, pp.price
    FROM pharmacy_payment pp
    LEFT JOIN drugs d ON pp.item_id=d.id
    WHERE pp.patient_id=$patient_id AND DATE(pp.created_at)='$created_date'
");
while($row = mysqli_fetch_assoc($pharmPayments)){
    $row['source'] = 'Pharmacy';
    $row['item_name'] = $row['item_name'] ?? 'Drug';
    $payments[] = $row;
    $total_price += $row['price'];
}

// 3️⃣ Lab payments
$labPayments = mysqli_query($con, "
    SELECT lpr.*, 'Lab Tests' AS item_name, lpr.amount AS price
    FROM lab_payment_request lpr
    WHERE lpr.patient_id=$patient_id AND DATE(lpr.created_at)='$created_date'
");
while($row = mysqli_fetch_assoc($labPayments)){
    $row['source'] = 'Lab';
    $payments[] = $row;
    $total_price += $row['price'];
}

if(empty($payments)) die("No payment records found for this patient on this date");

// Fetch cashier details
$user_id = $_SESSION['user_id'];
$cashier = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM billing WHERE id=$user_id"));

// Get created_at for receipt header (use first payment's created_at)
$created_at = $payments[0]['created_at'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Cashier | Receipt</title>
<style>
@page { margin: 0; }
body { width: 80mm; font-family: monospace; font-size: 12px; margin:0; }
.receipt { width:100%; padding:5px; position: relative; page-break-after: always; }
.watermark { position: absolute; top:40%; left:50%; transform:translate(-50%, -50%) rotate(-30deg); color: rgba(0,0,0,0.1); font-weight:bold; font-size:40px; }
.center { text-align:center; }
.right { text-align:right; }
.bold { font-weight:bold; }
hr { border-top:1px dashed #000; margin:5px 0; }
table { width:100%; border-collapse:collapse; }
td { padding:2px 0; }
.total { font-weight:bold; font-size:14px; }
</style>
</head>
<body onload="window.print();">

<?php foreach(['ORIGINAL','DUPLICATE'] as $type): ?>
<div class="receipt">
    <div class="watermark"><?= $type ?></div>
    <div class="center bold">
        <h2>Hospital Name</h2>
        <p>Address Line 1</p>
        <p>Tel: 080-XXXX-XXXX</p>
    </div>
    <hr>
    <table>
        <tr><td>Patient:</td><td><?= htmlspecialchars($patient['first_name'].' '.$patient['last_name']); ?></td></tr>
        <tr><td>EHR No:</td><td><?= htmlspecialchars($patient['ehr_no']); ?></td></tr>
        <tr><td>Date:</td><td><?= date("d-M-Y H:i", strtotime($created_at)); ?></td></tr>
    </table>
    <hr>
    <table>
        <tr><td class="bold">Service</td><td class="right bold">Amount</td><td class="right bold">Source</td></tr>
        <?php foreach($payments as $p): ?>
            <tr>
                <td><?= htmlspecialchars($p['item_name']); ?></td>
                <td class="right">₦<?= number_format($p['price'],2); ?></td>
                <td class="right"><?= $p['source']; ?></td>
            </tr>
        <?php endforeach; ?>
        <tr>
            <td class="total">TOTAL</td>
            <td class="right total">₦<?= number_format($total_price,2); ?></td>
            <td></td>
        </tr>
    </table>
    <hr>
    <table>
        <tr><td class="bold">Cashier:</td><td class="right bold"><?= $cashier['first_name'].' '.$cashier['last_name']; ?></td></tr>
        <tr><td class="bold">Phone:</td><td class="right bold"><?= $cashier['phone']; ?></td></tr>
    </table>
    <hr>
    <div class="center">
        <p>Thank you for your payment!</p>
        <p>Powered by HMS</p>
    </div>
</div>
<?php endforeach; ?>

</body>
</html>
