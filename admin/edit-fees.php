<?php
session_start();
include('include/config.php');

// Admin only
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../index.php");
    exit;
}

// Validate ID
if(!isset($_GET['id']) || empty($_GET['id'])){
    header("Location: manage-fees.php");
    exit;
}

$id = intval($_GET['id']);
$success = $error = "";

// Fetch all departments
$departments = [];
$deptQuery = mysqli_query($con, "SELECT dept_name FROM department ORDER BY dept_name ASC");
while($row = mysqli_fetch_assoc($deptQuery)){
    $departments[] = $row['dept_name'];
}

// Fetch existing fee
$stmt = $con->prepare("SELECT * FROM ehr_fees WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows == 0){
    header("Location: manage-fees.php");
    exit;
}

$fee = $result->fetch_assoc();
$stmt->close();

// Update fee
if(isset($_POST['submit'])){
    $item_name = trim($_POST['item_name']);
    $dept = trim($_POST['dept']);
    $price = trim($_POST['price']);

    if($item_name && $dept && $price){
        $stmt = $con->prepare("
            UPDATE ehr_fees 
            SET item_name = ?, dept = ?, price = ?
            WHERE id = ?
        ");
        $stmt->bind_param("sssi", $item_name, $dept, $price, $id);

        if($stmt->execute()){
            $success = "EHR fee updated successfully!";
            // refresh displayed data
            $fee['item_name'] = $item_name;
            $fee['dept'] = $dept;
            $fee['price'] = $price;
        } else {
            $error = "Failed to update fee.";
        }
        $stmt->close();
    } else {
        $error = "All fields are required.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit EHR Fees</title>
    <?php include 'include/css.php'; ?>
</head>
<body>
<div id="app">
<?php include 'include/sidebar.php'; ?>
<div class="app-content">
<?php include 'include/header.php'; ?>

<div class="main-content">
<div class="wrap-content container" id="container">

<section id="page-title">
    <div class="row">
        <div class="col-sm-8">
            <h1 class="mainTitle">Edit EHR Fee</h1>
        </div>
        <ol class="breadcrumb">
            <li>Admin</li>
            <li class="active">Edit Fee</li>
        </ol>
    </div>
</section>

<?php if($success): ?>
<div class="alert alert-success"><?= $success; ?></div>
<?php endif; ?>

<?php if($error): ?>
<div class="alert alert-danger"><?= $error; ?></div>
<?php endif; ?>

<div class="container-fluid container-fullw bg-white">
<div class="row">
<div class="col-md-6">

<div class="panel panel-white">
<div class="panel-heading">
    <h5 class="panel-title">Update Fee Details</h5>
</div>

<div class="panel-body">
<form method="post">

<div class="form-group">
    <label>Item Name *</label>
    <input type="text" name="item_name" class="form-control" required
           value="<?= htmlspecialchars($fee['item_name']); ?>">
</div>

<div class="form-group">
    <label>Department *</label>
    <select name="dept" class="form-control" required>
        <option value="">Select Department</option>
        <?php foreach($departments as $dept): ?>
            <option value="<?= htmlspecialchars($dept); ?>"
                <?= ($fee['dept'] == $dept) ? 'selected' : ''; ?>>
                <?= htmlspecialchars($dept); ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>


<div class="form-group">
    <label>Price (₦) *</label>
    <input type="text" name="price" class="form-control" required
           value="<?= htmlspecialchars($fee['price']); ?>">
</div>

<button type="submit" name="submit" class="btn btn-success">Update Fee</button>
<a href="manage-fees.php" class="btn btn-default">Cancel</a>

</form>
</div>
</div>

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
