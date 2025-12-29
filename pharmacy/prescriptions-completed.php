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
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
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
            WHERE p.status='completed'";

if($search != ""){
    $sqlBase .= " AND (
        pt.first_name LIKE '%$search%' OR 
        pt.last_name LIKE '%$search%' OR 
        d.first_name LIKE '%$search%' OR 
        d.last_name LIKE '%$search%' OR
        pt.ehr_no LIKE '%$search%'
    )";
}

/* Fetch completed prescriptions */
$query = mysqli_query($con, "
    SELECT p.id, p.medication, p.dosage, p.instructions, p.created_at,
           CONCAT(pt.first_name,' ',pt.last_name) AS patient_name, pt.ehr_no,
           CONCAT(d.first_name,' ',d.last_name) AS doctor_name
    $sqlBase
    ORDER BY p.created_at DESC
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
    <title>Completed Prescriptions</title>
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
            <h2 class="mainTitle">Completed Prescriptions</h2>
        </div>
        <ol class="breadcrumb">
            <li><span>Pharmacy</span></li>
            <li class="active"><span>Completed Prescriptions</span></li>
        </ol>
    </div>
</section>

<div class="container-fluid container-fullw bg-white">

<!-- Search Form -->
<form method="post" class="mb-3">
    <div class="row">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Search patient, doctor or EHR No" value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary"><i class="fa fa-search"></i> Search</button>
        </div>
    </div>
</form>

<!-- Completed Prescriptions Table -->
<table class="table table-bordered table-hover">
<thead>
<tr>
    <th>#</th>
    <th>Date</th>
    <th>EHR No</th>
    <th>Patient</th>
    <th>Doctor</th>
    <th>Medication</th>
    <th>Dosage</th>
    <th>Instructions</th>
    <th>Action</th>
</tr>
</thead>
<tbody>
<?php
$sn = $offset+1;
if(mysqli_num_rows($query) > 0){
    while($row = mysqli_fetch_assoc($query)){
?>
<tr>
    <td><?= $sn++; ?></td>
    <td><?= date('d M Y', strtotime($row['created_at'])) ?></td>
    <td><?= htmlspecialchars($row['ehr_no']) ?></td>
    <td><?= htmlspecialchars($row['patient_name']) ?></td>
    <td><?= htmlspecialchars($row['doctor_name']) ?></td>
    <td><?= nl2br($row['medication']) ?></td>
    <td><?= nl2br($row['dosage']) ?></td>
    <td><?= nl2br($row['instructions']) ?></td>
    <td>
        <a href="print-prescription.php?id=<?= $row['id'] ?>" target="_blank" class="btn btn-info btn-xs">
            <i class="fa fa-print"></i> Print
        </a>
    </td>
</tr>
<?php
    }
}else{
    echo '<tr><td colspan="9" class="text-center">No completed prescriptions found</td></tr>';
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
<?php include('include/js.php'); ?>
</body>
</html>
