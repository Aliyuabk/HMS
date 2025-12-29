<?php
session_start();
include('include/config.php');

// Restrict access
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../index.php");
    exit;
}

// Pagination
$limit = 5;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Search
$search = "";
if(isset($_POST['search'])){
    $search = trim(mysqli_real_escape_string($con, $_POST['search']));
}

// Count total rows
if($search){
    $countQuery = mysqli_query($con, "
        SELECT COUNT(*) AS total 
        FROM ehr_fees f
        JOIN department d ON f.dept = d.id
        WHERE f.item_name LIKE '%$search%' OR d.dept_name LIKE '%$search%'
    ");
} else {
    $countQuery = mysqli_query($con, "SELECT COUNT(*) AS total FROM ehr_fees");
}
$totalRows = mysqli_fetch_assoc($countQuery)['total'];
$totalPages = ceil($totalRows / $limit);

// Fetch data
if($search){
    $query = mysqli_query($con, "
        SELECT f.*, d.dept_name 
        FROM ehr_fees f
        JOIN department d ON f.dept = d.id
        WHERE f.item_name LIKE '%$search%' OR d.dept_name LIKE '%$search%'
        ORDER BY f.id DESC 
        LIMIT $offset, $limit
    ");
} else {
    $query = mysqli_query($con, "
        SELECT f.*, d.dept_name 
        FROM ehr_fees f
        JOIN department d ON f.dept = d.id
        ORDER BY f.id DESC 
        LIMIT $offset, $limit
    ");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin | Manage Fees</title>
    <?php include 'include/css.php';?>
</head>
<body>
<div id="app">
<?php include 'include/sidebar.php'; ?>
<div class="app-content">
<?php include 'include/header.php'; ?>

<div class="main-content">
<div class="wrap-content container" id="container">

<section id="page-title">
    <h2 class="mainTitle">Admin | Manage Fees</h2>
</section>

<!-- Alerts -->
<?php
if(isset($_SESSION['success'])){
    echo '<div class="alert alert-success">'.$_SESSION['success'].'</div>';
    unset($_SESSION['success']);
}
if(isset($_SESSION['error'])){
    echo '<div class="alert alert-danger">'.$_SESSION['error'].'</div>';
    unset($_SESSION['error']);
}
?>

<div class="panel panel-white">
<div class="panel-heading">
    <h4 class="panel-title">Fees List</h4>
    <a href="add-fees.php" class="btn btn-primary pull-right">
        <i class="fa fa-plus"></i> Add Fees
    </a>
</div>

<div class="panel-body">

<form method="post" class="mb-3">
    <div class="row">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control"
                   placeholder="Search item or department"
                   value="<?= htmlspecialchars($search); ?>">
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary">Search</button>
        </div>
    </div>
</form>

<table class="table table-bordered table-striped">
<thead>
<tr>
    <th>#</th>
    <th>Item Name</th>
    <th>Department</th>
    <th>Price (₦)</th>
    <th>Actions</th>
</tr>
</thead>
<tbody>
<?php
$count = $offset + 1;
if(mysqli_num_rows($query) > 0){
    while($row = mysqli_fetch_assoc($query)){
?>
<tr>
    <td><?= $count++; ?></td>
    <td><?= htmlspecialchars($row['item_name']); ?></td>
    <td><?= htmlspecialchars(ucfirst($row['dept_name'])); ?></td>
    <td>₦<?= number_format($row['price']); ?></td>
    <td>
        <a href="edit-fees.php?id=<?= $row['id']; ?>" class="btn btn-warning btn-sm">
            <i class="fa fa-edit"></i>
        </a>
        <a href="delete-fees.php?id=<?= $row['id']; ?>"
           class="btn btn-danger btn-sm"
           onclick="return confirm('Delete this fee?');">
            <i class="fa fa-trash"></i>
        </a>
    </td>
</tr>
<?php } } else { ?>
<tr>
    <td colspan="5" class="text-center">No fees found</td>
</tr>
<?php } ?>
</tbody>
</table>

<!-- Pagination -->
<?php if($totalPages > 1): ?>
<ul class="pagination">
    <li class="<?= ($page<=1)?'disabled':'' ?>">
        <a href="?page=<?= $page-1 ?>">«</a>
    </li>
    <?php for($p=1;$p<=$totalPages;$p++): ?>
        <li class="<?= ($page==$p)?'active':'' ?>">
            <a href="?page=<?= $p ?>"><?= $p ?></a>
        </li>
    <?php endfor; ?>
    <li class="<?= ($page>=$totalPages)?'disabled':'' ?>">
        <a href="?page=<?= $page+1 ?>">»</a>
    </li>
</ul>
<?php endif; ?>

</div>
</div>

</div>
</div>
</div>

<?php include 'include/footer.php'; ?>
<?php include 'include/setting.php'; ?>
</div>
</div>
<?php include 'include/js.php'; ?>
</body>
</html>
