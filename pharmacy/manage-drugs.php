<?php
session_start();
include('include/config.php');

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pharmacy'){
    header("Location: ../index.php");
    exit;
}

/* Pagination */
$limit = 15;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page-1)*$limit;

/* Search */
$search = "";
if(isset($_POST['search'])){
    $search = mysqli_real_escape_string($con, trim($_POST['search']));
}

/* Fetch Drugs */
$sql = "SELECT * FROM drugs WHERE 1";
if($search != ""){
    $sql .= " AND (name LIKE '%$search%' OR brand LIKE '%$search%' OR batch_no LIKE '%$search%')";
}
$sql .= " ORDER BY id DESC LIMIT $offset, $limit";
$query = mysqli_query($con, $sql);
$totalRows = mysqli_fetch_assoc(mysqli_query($con,"SELECT COUNT(*) AS total FROM drugs WHERE 1"))['total'] ?? 0;
$totalPages = ceil($totalRows / $limit);

/* Delete drug */
if(isset($_GET['delete'])){
    $id = intval($_GET['delete']);
    $drug = mysqli_fetch_assoc(mysqli_query($con,"SELECT * FROM drugs WHERE id=$id"));
    mysqli_query($con,"DELETE FROM drugs WHERE id=$id");
    $user_id = $_SESSION['user_id'];
    $action = "Deleted drug: {$drug['name']} (Batch: {$drug['batch_no']})";
    mysqli_query($con,"INSERT INTO user_log (user_id,user_role,action,ip_address) VALUES ($user_id,'pharmacy','".mysqli_real_escape_string($con,$action)."','".$_SERVER['REMOTE_ADDR']."')");
    header("Location: manage-drugs.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Drugs</title>
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
            <h2 class="mainTitle">Manage Drugs</h2>
        </div>
    </div>
</section>

<div class="container-fluid container-fullw bg-white">

<form method="post" class="mb-3">
    <div class="row">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Search by name, brand or batch" value="<?= htmlspecialchars($search) ?>">
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
    <th>Name</th>
    <th>Brand</th>
    <th>Batch No</th>
    <th>Expiry Date</th>
    <th>Quantity</th>
    <th>Unit</th>
    <th>Price (₦)</th>
    <th>Actions</th>
</tr>
</thead>
<tbody>
<?php
$sn = $offset+1;
if(mysqli_num_rows($query) > 0){
    while($row = mysqli_fetch_assoc($query)){
?>
<tr>
    <td><?= $sn++ ?></td>
    <td><?= htmlspecialchars($row['name']) ?></td>
    <td><?= htmlspecialchars($row['brand']) ?></td>
    <td><?= htmlspecialchars($row['batch_no']) ?></td>
    <td><?= $row['expiry_date'] ?></td>
    <td><?= $row['quantity'] ?></td>
    <td><?= htmlspecialchars($row['unit']) ?></td>
    <td><?= number_format($row['price'],2) ?></td>
    <td>
        <a href="edit-drug.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
        <a href="manage-drugs.php?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
    </td>
</tr>
<?php
    }
}else{
    echo '<tr><td colspan="9" class="text-center">No drugs found</td></tr>';
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
