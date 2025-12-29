<?php
session_start();
include('include/config.php');

// Restrict access to billing only
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'billing'){
    header("Location: ../index.php");
    exit;
}

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Search
$search = "";
if(isset($_POST['search'])){
    $search = trim(mysqli_real_escape_string($con, $_POST['search']));
}

$searchSQL = "";
if($search){
    $searchSQL = " AND (p.first_name LIKE '%$search%' OR p.last_name LIKE '%$search%' OR p.ehr_no LIKE '%$search%')";
}

// Count total rows
$countQuery = "
SELECT COUNT(*) AS total FROM (
    SELECT pr.patient_id, DATE(pr.created_at) AS created_date
    FROM payment_request pr
    JOIN patients p ON p.id = pr.patient_id
    WHERE 1 $searchSQL
    UNION ALL
    SELECT pp.patient_id, DATE(pp.created_at) AS created_date
    FROM pharmacy_payment pp
    JOIN patients p ON p.id = pp.patient_id
    WHERE 1 $searchSQL
    UNION ALL
    SELECT lp.patient_id, DATE(lp.created_at) AS created_date
    FROM lab_payment_request lp
    JOIN patients p ON p.id = lp.patient_id
    WHERE 1 $searchSQL
) AS merged
";
$countResult = mysqli_query($con, $countQuery);
$totalRows = $countResult ? mysqli_fetch_assoc($countResult)['total'] : 0;
$totalPages = ceil($totalRows / $limit);

// Fetch merged payments
$paymentsQuery = "
SELECT patient_id, created_date, SUM(total_amount) AS total_amount, 
       GROUP_CONCAT(service_name SEPARATOR ', ') AS services,
       GROUP_CONCAT(status SEPARATOR ', ') AS statuses
FROM (
    SELECT pr.patient_id, DATE(pr.created_at) AS created_date, pr.price AS total_amount, pr.item_name AS service_name, pr.status
    FROM payment_request pr
    JOIN patients p ON p.id = pr.patient_id
    WHERE 1 $searchSQL
    UNION ALL
    SELECT pp.patient_id, DATE(pp.created_at) AS created_date, pp.price AS total_amount, d.name AS service_name, pp.status
    FROM pharmacy_payment pp
    JOIN drugs d ON d.id = pp.item_id
    JOIN patients p ON p.id = pp.patient_id
    WHERE 1 $searchSQL
    UNION ALL
    SELECT lp.patient_id, DATE(lp.created_at) AS created_date, lp.amount AS total_amount, 'Lab Test' AS service_name, lp.status
    FROM lab_payment_request lp
    JOIN patients p ON p.id = lp.patient_id
    WHERE 1 $searchSQL
) AS all_payments
GROUP BY patient_id, created_date
ORDER BY created_date DESC
LIMIT $offset, $limit
";

$paymentsResult = mysqli_query($con, $paymentsQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>All Payment Requests</title>
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
            <h2 class="mainTitle">All Payment Requests</h2>
        </div>
    </div>
</section>

<div class="container-fluid container-fullw bg-white">
    <!-- Search -->
    <form method="post" class="mb-3">
        <div class="row">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search by patient or EHR No" value="<?= htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary" type="submit"><i class="fa fa-search"></i> Search</button>
            </div>
        </div>
    </form>

    <!-- Payments Table -->
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>#</th>
                <th>Patient Name</th>
                <th>EHR No</th>
                <th>Total Amount</th>
                <th>Services</th>
                <th>Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $count = $offset + 1;
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
            <td><?= $row['created_date']; ?></td>
            <td><?= ucfirst($row['statuses']); ?></td>
        </tr>
        <?php
            }
        } else {
            echo '<tr><td colspan="7" class="text-center">No payment requests found</td></tr>';
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

<?php include('include/footer.php');?>
<?php include('include/setting.php');?>
</div>
<?php include 'include/js.php';?>
</body>
</html>
