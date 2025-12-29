<?php
session_start();
include('include/config.php');

/* =========================
   ACCESS CONTROL
========================= */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pharmacy') {
    header("Location: ../index.php");
    exit;
}

/* =========================
   PAGINATION & SEARCH
========================= */
$limit  = 10;
$page   = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page   = ($page < 1) ? 1 : $page;
$offset = ($page - 1) * $limit;

$search = "";
if (isset($_POST['search'])) {
    $search = mysqli_real_escape_string($con, trim($_POST['search']));
}

/* =========================
   MAIN QUERY
========================= */
$sqlBase = "
    FROM prescriptions p
    INNER JOIN patients pt ON pt.id = p.patient_id
    INNER JOIN doctor d ON d.id = p.doctor_id
    WHERE p.status = 'pending'
";

if ($search !== "") {
    $sqlBase .= "
        AND (
            pt.first_name LIKE '%$search%' OR
            pt.last_name LIKE '%$search%' OR
            d.first_name LIKE '%$search%' OR
            d.last_name LIKE '%$search%' OR
            pt.ehr_no LIKE '%$search%'
        )
    ";
}

/* =========================
   FETCH DATA
========================= */
$query = mysqli_query($con, "
    SELECT 
        p.id,
        p.medication,
        p.created_at,
        CONCAT(pt.first_name,' ',pt.last_name) AS patient_name,
        pt.ehr_no,
        CONCAT(d.first_name,' ',d.last_name) AS doctor_name
    $sqlBase
    ORDER BY p.created_at DESC
    LIMIT $offset, $limit
");

if (!$query) {
    die("Prescription query failed: " . mysqli_error($con));
}

/* =========================
   COUNT FOR PAGINATION
========================= */
$countQuery = mysqli_query($con, "
    SELECT COUNT(*) AS total
    $sqlBase
");

if (!$countQuery) {
    die("Count query failed: " . mysqli_error($con));
}

$totalRows  = mysqli_fetch_assoc($countQuery)['total'] ?? 0;
$totalPages = ceil($totalRows / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Pharmacy | Pending Prescriptions</title>
    <?php include 'include/css.php'; ?>
</head>
<body>
<div id="app">

<?php include('include/sidebar.php'); ?>
<div class="app-content">
<?php include('include/header.php'); ?>

<div class="main-content">
<div class="wrap-content container" id="container">

<!-- PAGE TITLE -->
<section id="page-title">
    <div class="row">
        <div class="col-sm-8">
            <h2 class="mainTitle">Pending Prescriptions</h2>
        </div>
        <ol class="breadcrumb">
            <li><span>Pharmacy</span></li>
            <li class="active"><span>Pending Prescriptions</span></li>
        </ol>
    </div>
</section>

<div class="container-fluid container-fullw bg-white">

<div class="row">
<div class="col-md-12">
<div class="panel panel-white">

<div class="panel-heading">
    <h4 class="panel-title">Pending Prescriptions</h4>
</div>

<div class="panel-body">

<!-- SEARCH -->
<form method="post" class="mb-3">
    <div class="row">
        <div class="col-md-4">
            <input type="text"
                   name="search"
                   class="form-control"
                   placeholder="Search patient, doctor or EHR No"
                   value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary">
                <i class="fa fa-search"></i> Search
            </button>
        </div>
    </div>
</form>

<!-- TABLE -->
<table class="table table-bordered table-hover">
<thead>
<tr>
    <th>#</th>
    <th>Date</th>
    <th>EHR No</th>
    <th>Patient</th>
    <th>Doctor</th>
    <th>Medication</th>
    <th>Status</th>
    <th width="180">Action</th>
</tr>
</thead>
<tbody>

<?php
$sn = $offset + 1;
if (mysqli_num_rows($query) > 0) {
    while ($row = mysqli_fetch_assoc($query)) {
?>
<tr>
    <td><?= $sn++; ?></td>
    <td><?= date('d M Y', strtotime($row['created_at'])) ?></td>
    <td><?= htmlspecialchars($row['ehr_no']) ?></td>
    <td><?= htmlspecialchars($row['patient_name']) ?></td>
    <td><?= htmlspecialchars($row['doctor_name']) ?></td>
    <td><?= htmlspecialchars($row['medication']) ?></td>
    <td>
        <span class="label label-warning">Pending</span>
    </td>
    <td>
        <a href="prescription-view.php?id=<?= $row['id'] ?>"
           class="btn btn-info btn-xs">
           <i class="fa fa-eye"></i> View
        </a>

        <a href="dispense.php?id=<?= $row['id'] ?>"
           class="btn btn-success btn-xs"
           onclick="return confirm('Dispense this prescription?')">
           <i class="fa fa-check"></i> Dispense
        </a>

        <a href="cancel-prescription.php?id=<?= $row['id'] ?>"
           class="btn btn-danger btn-xs"
           onclick="return confirm('Cancel this prescription?')">
           <i class="fa fa-times"></i> Cancel
        </a>
    </td>
</tr>
<?php
    }
} else {
    echo '<tr><td colspan="8" class="text-center">No pending prescriptions found</td></tr>';
}
?>

</tbody>
</table>

<!-- PAGINATION -->
<?php if ($totalPages > 1): ?>
<ul class="pagination">
    <li class="<?= ($page <= 1) ? 'disabled' : '' ?>">
        <a href="?page=<?= $page - 1 ?>">«</a>
    </li>
    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
        <li class="<?= ($page == $p) ? 'active' : '' ?>">
            <a href="?page=<?= $p ?>"><?= $p ?></a>
        </li>
    <?php endfor; ?>
    <li class="<?= ($page >= $totalPages) ? 'disabled' : '' ?>">
        <a href="?page=<?= $page + 1 ?>">»</a>
    </li>
</ul>
<?php endif; ?>

</div>
</div>
</div>
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
