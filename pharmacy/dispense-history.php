<?php
session_start();
include('include/config.php');

// Restrict to pharmacy
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pharmacy'){
    header("Location: ../index.php");
    exit;
}

/* Pagination */
$limit = 15;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$page = ($page < 1) ? 1 : $page;
$offset = ($page - 1) * $limit;

/* Search */
$search = "";
if(isset($_POST['search'])){
    $search = mysqli_real_escape_string($con, trim($_POST['search']));
}

/* Base SQL */
$sqlBase = "FROM prescriptions p
            INNER JOIN patients pt ON pt.id=p.patient_id
            INNER JOIN doctor d ON d.id=p.doctor_id
            INNER JOIN user_log ul ON ul.reference_id=p.id
            WHERE p.status='completed' AND ul.user_role='pharmacy'";

if($search != ""){
    $sqlBase .= " AND (
        pt.first_name LIKE '%$search%' OR
        pt.last_name LIKE '%$search%' OR
        d.first_name LIKE '%$search%' OR
        d.last_name LIKE '%$search%' OR
        ul.action LIKE '%$search%'
    )";
}

/* Fetch dispensed prescriptions */
$query = mysqli_query($con, "
    SELECT p.id, p.medication, p.dosage, p.instructions, p.updated_at,
           CONCAT(pt.first_name,' ',pt.last_name) AS patient_name, pt.ehr_no,
           CONCAT(d.first_name,' ',d.last_name) AS doctor_name,
           ul.action AS dispensed_by, ul.created_at AS dispensed_on
    $sqlBase
    ORDER BY ul.created_at DESC
    LIMIT $offset, $limit
");
if(!$query) die("Query failed: ".mysqli_error($con));

/* Count for pagination */
$countQuery = mysqli_query($con, "SELECT COUNT(*) AS total $sqlBase");
$totalRows  = mysqli_fetch_assoc($countQuery)['total'] ?? 0;
$totalPages = ceil($totalRows / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Dispense History</title>
    <?php include 'include/css.php'; ?>
</head>
<body>
<div id="app">
<?php include('include/sidebar.php'); ?>
<div class="app-content">
<?php include('include/header.php'); ?>

<div class="main-content">
<div class="wrap-content container">

<section id="page-title">
    <div class="row">
        <div class="col-sm-8">
            <h2 class="mainTitle">Dispense History</h2>
        </div>
        <ol class="breadcrumb">
            <li><span>Pharmacy</span></li>
            <li class="active"><span>History</span></li>
        </ol>
    </div>
</section>

<div class="container-fluid container-fullw bg-white">

<!-- Search Form -->
<form method="post" class="mb-3">
    <div class="row">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Search by patient, doctor or action" value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary"><i class="fa fa-search"></i> Search</button>
        </div>
    </div>
</form>

<!-- Dispense History Table -->
<table class="table table-bordered table-hover">
<thead>
<tr>
    <th>#</th>
    <th>Dispensed On</th>
    <th>Patient</th>
    <th>EHR No</th>
    <th>Doctor</th>
    <th>Medication</th>
    <th>Dosage</th>
    <th>Dispensed By</th>
</tr>
</thead>
<tbody>
<?php
$sn = $offset + 1;
if(mysqli_num_rows($query) > 0){
    while($row = mysqli_fetch_assoc($query)){
?>
<tr>
    <td><?= $sn++; ?></td>
    <td><?= date('d M Y H:i', strtotime($row['dispensed_on'])) ?></td>
    <td><?= htmlspecialchars($row['patient_name']) ?></td>
    <td><?= htmlspecialchars($row['ehr_no']) ?></td>
    <td><?= htmlspecialchars($row['doctor_name']) ?></td>
    <td><?= nl2br($row['medication']) ?></td>
    <td><?= nl2br($row['dosage']) ?></td>
    <td><?= htmlspecialchars($row['dispensed_by']) ?></td>
</tr>
<?php
    }
}else{
    echo '<tr><td colspan="8" class="text-center">No dispensed prescriptions found</td></tr>';
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
<?php include('include/js.php'); ?>
</body>
</html>
