<?php
session_start();
include('include/config.php');

// Restrict access to pharmacy
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pharmacy'){
    header("Location: ../index.php");
    exit;
}

// Get drug ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if($id <= 0){
    header("Location: manage-drugs.php");
    exit;
}

// Fetch drug details
$drugQuery = mysqli_query($con, "SELECT * FROM drugs WHERE id=$id");
if(mysqli_num_rows($drugQuery) == 0){
    header("Location: manage-drugs.php");
    exit;
}
$drug = mysqli_fetch_assoc($drugQuery);

// Handle form submission
if(isset($_POST['update_drug'])){
    $name = mysqli_real_escape_string($con, trim($_POST['name']));
    $brand = mysqli_real_escape_string($con, trim($_POST['brand']));
    $batch_no = mysqli_real_escape_string($con, trim($_POST['batch_no']));
    $expiry_date = $_POST['expiry_date'] ?? null;
    $quantity = intval($_POST['quantity']);
    $unit = mysqli_real_escape_string($con, trim($_POST['unit']));
    $price = floatval($_POST['price']);

    mysqli_query($con, "UPDATE drugs SET 
                        name='$name', 
                        brand='$brand', 
                        batch_no='$batch_no', 
                        expiry_date='$expiry_date', 
                        quantity=$quantity, 
                        unit='$unit', 
                        price=$price
                        WHERE id=$id");

    // Log update action
    $user_id = $_SESSION['user_id'];
    $action = "Updated drug: $name, Batch: $batch_no, Quantity: $quantity $unit, Price: ₦$price";
    mysqli_query($con, "INSERT INTO user_log (user_id,user_role,action,ip_address) 
                        VALUES ($user_id,'pharmacy','".mysqli_real_escape_string($con,$action)."','".$_SERVER['REMOTE_ADDR']."')");

    $success = "Drug updated successfully!";
    // Refresh drug data
    $drug = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM drugs WHERE id=$id"));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Drug</title>
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
            <h2 class="mainTitle">Edit Drug: <?= htmlspecialchars($drug['name']) ?></h2>
        </div>
    </div>
</section>

<div class="container-fluid container-fullw bg-white">

<?php if(isset($success)): ?>
    <div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>

<form method="post">
    <div class="form-group">
        <label>Drug Name</label>
        <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($drug['name']) ?>">
    </div>
    <div class="form-group">
        <label>Brand</label>
        <input type="text" name="brand" class="form-control" value="<?= htmlspecialchars($drug['brand']) ?>">
    </div>
    <div class="form-group">
        <label>Batch Number</label>
        <input type="text" name="batch_no" class="form-control" required value="<?= htmlspecialchars($drug['batch_no']) ?>">
    </div>
    <div class="form-group">
        <label>Expiry Date</label>
        <input type="date" name="expiry_date" class="form-control" value="<?= $drug['expiry_date'] ?>">
    </div>
    <div class="form-group">
        <label>Quantity</label>
        <input type="number" name="quantity" class="form-control" required value="<?= $drug['quantity'] ?>">
    </div>
    <div class="form-group">
        <label>Unit</label>
        <input type="text" name="unit" class="form-control" required value="<?= htmlspecialchars($drug['unit']) ?>">
    </div>
    <div class="form-group">
        <label>Price (₦)</label>
        <input type="number" step="0.01" name="price" class="form-control" required value="<?= $drug['price'] ?>">
    </div>
    <button type="submit" name="update_drug" class="btn btn-success">Update Drug</button>
    <a href="manage-drugs.php" class="btn btn-secondary">Cancel</a>
</form>

</div>
</div>
</div>

<?php include('include/footer.php'); ?>
<?php include('include/setting.php'); ?>
</div>
<?php include('include/js.php'); ?>
</body>
</html>
