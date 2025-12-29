<?php
session_start();
include('include/config.php');

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pharmacy'){
    header("Location: ../index.php");
    exit;
}

/* Pagination */
$limit = 20;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page-1)*$limit;

/* Search */
$search = "";
if(isset($_POST['search'])){
    $search = mysqli_real_escape_string($con, trim($_POST['search']));
}

/* Base SQL */
$sqlBase = "FROM user_log ul
            LEFT JOIN prescriptions p ON ul.reference_id=p.id
            LEFT JOIN patients pt ON pt.id=p.patient_id
            WHERE ul.user_role='pharmacy'";

if($search != ""){
    $sqlBase .= " AND (ul.action LIKE '%$search%' OR pt.first_name LIKE '%$search%' OR pt.last_name LIKE '%$search%')";
}

/* Fetch log */
$query = mysqli_query($con, "
    SELECT ul.*, CONCAT(pt.first_name,' ',pt.last_name) AS patient_name
    $sqlBase
    ORDER BY ul.created_at DESC
    LIMIT $offset, $limit
");

$countQuery = mysqli_query($con, "SELECT COUNT(*) AS total $sqlBase");
$totalRows  = mysqli_fetch_assoc($countQuery)['total'] ?? 0;
$totalPages = ceil($totalRows / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Audit Log</title>
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
            <h2 class="mainTitle">Audit Log</h2>
        </div>
        <ol class="breadcrumb">
            <li><span>Pharmacy</span></li>
            <li class="active"><span>Audit Log</span></li>
        </ol>
    </div>
</section>

<div class="container-fluid container-fullw bg-white">

<form method="post" class="mb-3">
    <div class="row">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Search by action or patient" value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary"><i class="fa fa-search"></i> Search</button>
        </div>
    </div>
</form>

<table class="table table-bordered table-hover">
<thead>
<tr>
    <th>#</th>
    <th>User ID</th>
    <th>Action</th>
    <th>Patient</th>
    <th>IP Address</th>
    <th>Date</th>
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
    <td><?= htmlspecialchars($row['user_id']) ?></td>
    <td><?= htmlspecialchars($row['action']) ?></td>
    <td><?= htmlspecialchars($row['patient_name'] ?? '-') ?></td>
    <td><?= htmlspecialchars($row['ip_address']) ?></td>
    <td><?= date('d M Y H:i', strtotime($row['created_at'])) ?></td>
</tr>
<?php
    }
}else{
    echo '<tr><td colspan="6" class="text-center">No logs found</td></tr>';
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
