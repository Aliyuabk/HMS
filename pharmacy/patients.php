<?php
session_start();
include('include/config.php');

// Restrict access to pharmacy
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pharmacy'){
    header("Location: ../index.php");
    exit;
}

/* Pagination */
$limit = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = ($page < 1) ? 1 : $page;
$offset = ($page - 1) * $limit;

/* Search */
$search = "";
if(isset($_POST['search'])){
    $search = mysqli_real_escape_string($con, trim($_POST['search']));
}

/* Base SQL */
$sqlBase = "FROM patients WHERE 1";
if($search != ""){
    $sqlBase .= " AND (first_name LIKE '%$search%' OR last_name LIKE '%$search%' OR ehr_no LIKE '%$search%' OR phone LIKE '%$search%')";
}

/* Fetch patients */
$query = mysqli_query($con, "
    SELECT * $sqlBase
    ORDER BY created_at DESC
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
    <title>Patients</title>
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
            <h2 class="mainTitle">Patients</h2>
        </div>
        <ol class="breadcrumb">
            <li><span>Pharmacy</span></li>
            <li class="active"><span>Patients</span></li>
        </ol>
    </div>
</section>

<div class="container-fluid container-fullw bg-white">

<!-- Search Form -->
<form method="post" class="mb-3">
    <div class="row">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Search by name, EHR No or phone" value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary"><i class="fa fa-search"></i> Search</button>
        </div>
    </div>
</form>

<!-- Patients Table -->
<table class="table table-bordered table-hover">
<thead>
<tr>
    <th>#</th>
    <th>EHR No</th>
    <th>Name</th>
    <th>Gender</th>
    <th>Phone</th>
    <th>DOB</th>
    <th>Address</th>
    <th>Registered On</th>
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
    <td><?= htmlspecialchars($row['ehr_no']) ?></td>
    <td><?= htmlspecialchars($row['first_name'].' '.$row['last_name']) ?></td>
    <td><?= htmlspecialchars($row['gender']) ?></td>
    <td><?= htmlspecialchars($row['phone']) ?></td>
    <td><?= $row['dob'] ? date('d M Y', strtotime($row['dob'])) : '-' ?></td>
    <td><?= nl2br(htmlspecialchars($row['address'])) ?></td>
    <td><?= date('d M Y', strtotime($row['created_at'])) ?></td>
</tr>
<?php
    }
}else{
    echo '<tr><td colspan="8" class="text-center">No patients found</td></tr>';
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
