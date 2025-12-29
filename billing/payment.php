<?php
session_start();
include('include/config.php');

// Restrict access
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'billing'){
    header("Location: ../index.php");
    exit;
}

// --- Pagination ---
$limit = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// --- Search ---
$search = "";
if(isset($_POST['search'])){
    $search = trim(mysqli_real_escape_string($con, $_POST['search']));
}

// --- Count total pending payments ---
$countQueryStr = "
    SELECT COUNT(*) AS total FROM (
        SELECT pr.patient_id
        FROM payment_request pr
        INNER JOIN patients p ON p.id = pr.patient_id
        WHERE pr.status='pending'
        ".($search ? " AND (p.first_name LIKE '%$search%' OR p.last_name LIKE '%$search%' OR p.ehr_no LIKE '%$search%')" : "")."
        
        UNION ALL
        
        SELECT pp.patient_id
        FROM pharmacy_payment pp
        INNER JOIN patients p ON p.id = pp.patient_id
        WHERE pp.status='pending'
        ".($search ? " AND (p.first_name LIKE '%$search%' OR p.last_name LIKE '%$search%' OR p.ehr_no LIKE '%$search%')" : "")."
        
        UNION ALL
        
        SELECT lpr.patient_id
        FROM lab_payment_request lpr
        INNER JOIN patients p ON p.id = lpr.patient_id
        WHERE lpr.status='pending'
        ".($search ? " AND (p.first_name LIKE '%$search%' OR p.last_name LIKE '%$search%' OR p.ehr_no LIKE '%$search%')" : "")."
    ) AS sub
";
$countQuery = mysqli_query($con, $countQueryStr);
$totalRows = mysqli_fetch_assoc($countQuery)['total'] ?? 0;
$totalPages = ceil($totalRows / $limit);

// --- Fetch pending payments ---
$queryStr = "
    SELECT pr.patient_id, CONCAT(p.first_name,' ',p.last_name) AS patient_name, p.ehr_no,
           'payment_request' AS source_table, DATE(pr.created_at) AS created_date,
           SUM(pr.price) AS total_amount, GROUP_CONCAT(pr.item_name SEPARATOR ', ') AS services
    FROM payment_request pr
    INNER JOIN patients p ON p.id = pr.patient_id
    WHERE pr.status='pending'
    ".($search ? " AND (p.first_name LIKE '%$search%' OR p.last_name LIKE '%$search%' OR p.ehr_no LIKE '%$search%')" : "")."
    GROUP BY pr.patient_id, DATE(pr.created_at)
    
    UNION ALL
    
    SELECT pp.patient_id, CONCAT(p.first_name,' ',p.last_name) AS patient_name, p.ehr_no,
           'pharmacy_payment' AS source_table, DATE(pp.created_at) AS created_date,
           SUM(pp.price) AS total_amount, GROUP_CONCAT(pp.item_name SEPARATOR ', ') AS services
    FROM pharmacy_payment pp
    INNER JOIN patients p ON p.id = pp.patient_id
    WHERE pp.status='pending'
    ".($search ? " AND (p.first_name LIKE '%$search%' OR p.last_name LIKE '%$search%' OR p.ehr_no LIKE '%$search%')" : "")."
    GROUP BY pp.patient_id, DATE(pp.created_at)
    
    UNION ALL
    
    SELECT lpr.patient_id, CONCAT(p.first_name,' ',p.last_name) AS patient_name, p.ehr_no,
           'lab_payment_request' AS source_table, DATE(lpr.created_at) AS created_date,
           SUM(lpr.amount) AS total_amount, 'Lab Tests' AS services
    FROM lab_payment_request lpr
    INNER JOIN patients p ON p.id = lpr.patient_id
    WHERE lpr.status='pending'
    ".($search ? " AND (p.first_name LIKE '%$search%' OR p.last_name LIKE '%$search%' OR p.ehr_no LIKE '%$search%')" : "")."
    GROUP BY lpr.patient_id, DATE(lpr.created_at)
    
    ORDER BY created_date DESC
    LIMIT $offset, $limit
";

$query = mysqli_query($con, $queryStr);
if(!$query) die("Query failed: " . mysqli_error($con));
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <title>Cashier | Payment Dashboard</title>
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
            <h2 class="mainTitle">Cashier | Payment Dashboard</h2>
        </div>
    </div>
</section>

<div class="container-fluid container-fullw bg-white">
    <!-- Search Form -->
    <form method="post" class="mb-3">
        <div class="row">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search by name, EHR or Lab Req #" value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary"><i class="fa fa-search"></i> Search</button>
            </div>
        </div>
    </form>

    <!-- Payments Table -->
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>#</th>
                <th>Patient Number</th>
                <th>Patient Name / Lab Request</th>
                <th>Total Amount</th>
                <th>Services</th>
                <th>Source</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $count = $offset + 1;
        if(mysqli_num_rows($query) > 0){
            while($row = mysqli_fetch_assoc($query)){
                $rowId = $row['patient_id'].'-'.$row['source_table'].'-'.strtotime($row['created_date']);
        ?>
            <tr id="row-<?= $rowId; ?>">
                <td><?= $count++; ?></td>
                <td><?= htmlspecialchars($row['ehr_no'] ?: '-') ?></td>
                <td><?= htmlspecialchars($row['patient_name']) ?></td>
                <td>₦<?= number_format($row['total_amount'], 2) ?></td>
                <td><?= htmlspecialchars($row['services'] ?: '-') ?></td>
                <td><?= ($row['source_table']=='pharmacy_payment') ? 'Pharmacy' : (($row['source_table']=='lab_payment_request') ? 'Lab' : 'Dept') ?></td>
                <td><?= $row['created_date'] ?></td>
                <td>
                    <button class="btn btn-success btn-sm"
                        onclick="printPayment('<?= $row['patient_id']; ?>','<?= $row['source_table']; ?>','<?= $row['created_date']; ?>')">
                        <i class="fa fa-print"></i> Print & Mark Paid
                    </button> 
                </td>
            </tr>
        <?php
            }
        } else {
            echo '<tr><td colspan="8" class="text-center">No pending payment requests found</td></tr>';
        }
        ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <?php if($totalPages > 1): ?>
    <ul class="pagination">
        <li class="<?= ($page<=1)?'disabled':'' ?>"><a href="?page=<?= $page-1 ?>">«</a></li>
        <?php for($p=1;$p<=$totalPages;$p++): ?>
            <li class="<?= ($page==$p)?'active':'' ?>"><a href="?page=<?= $p ?>"><?= $p ?></a></li>
        <?php endfor; ?>
        <li class="<?= ($page>=$totalPages)?'disabled':'' ?>"><a href="?page=<?= $page+1 ?>">»</a></li>
    </ul>
    <?php endif; ?>

</div>
</div>
</div>

<?php include('include/footer.php'); ?>
<?php include('include/setting.php'); ?>
</div>
<?php include 'include/js.php'; ?>

<script>
// Print & mark paid
function printPayment(patientId, tableName, createdDate){
    fetch('mark-paid.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ patient_id: patientId, table: tableName, created_date: createdDate })
    })
    .then(res => res.json())
    .then(data => {
        if(data.success){
            const row = document.getElementById('row-' + patientId + '-' + tableName + '-' + new Date(createdDate).getTime());
            if(row) row.remove();
            const iframe = document.createElement('iframe');
            iframe.style.position='fixed';
            iframe.style.width='0';
            iframe.style.height='0';
            iframe.src = `print-receipt.php?patient_id=${patientId}&table=${tableName}&created_date=${createdDate}`;
            document.body.appendChild(iframe);
            iframe.contentWindow.focus();
            iframe.contentWindow.print();
        } else alert('Failed to mark as paid.');
    })
    .catch(err => console.error(err));
}

// Delete payment row
function deletePayment(patientId, tableName, createdDate){
    if(!confirm('Are you sure you want to delete this payment request?')) return;
    fetch('delete-payment.php', {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body:JSON.stringify({patient_id:patientId, table:tableName, created_date:createdDate})
    })
    .then(res=>res.json())
    .then(data=>{
        if(data.success){
            const row = document.getElementById('row-' + patientId + '-' + tableName + '-' + new Date(createdDate).getTime());
            if(row) row.remove();
        } else alert('Failed to delete payment.');
    }).catch(err=>console.error(err));
}
</script>
</body>
</html>
