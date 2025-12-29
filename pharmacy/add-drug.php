<?php
session_start();
include('include/config.php');

// Restrict to pharmacy
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pharmacy'){
    header("Location: ../index.php");
    exit;
}

if(isset($_POST['add_drug'])){
    $name = mysqli_real_escape_string($con, trim($_POST['name']));
    $brand = mysqli_real_escape_string($con, trim($_POST['brand']));
    $batch_no = mysqli_real_escape_string($con, trim($_POST['batch_no']));
    $expiry_date = $_POST['expiry_date'] ?? null;
    $quantity = intval($_POST['quantity']);
    $unit = mysqli_real_escape_string($con, trim($_POST['unit']));
    $price = floatval($_POST['price']);

    // Check for duplicate batch
    $check = mysqli_query($con, "SELECT * FROM drugs WHERE name='$name' AND batch_no='$batch_no'");
    if(mysqli_num_rows($check) > 0){
        $error = "Drug with this batch number already exists!";
    } else {
        mysqli_query($con, "INSERT INTO drugs (name, brand, batch_no, expiry_date, quantity, unit, price) 
                            VALUES ('$name','$brand','$batch_no','$expiry_date',$quantity,'$unit',$price)");

        // Log action
        $user_id = $_SESSION['user_id'];
        $action = "Added drug: $name, Batch: $batch_no, Quantity: $quantity $unit";
        mysqli_query($con, "INSERT INTO user_log (user_id,user_role,action,ip_address) 
                            VALUES ($user_id,'pharmacy','".mysqli_real_escape_string($con,$action)."','".$_SERVER['REMOTE_ADDR']."')");
        
        $success = "Drug added successfully!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Add Drug</title>
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
            <h2 class="mainTitle">Add New Drug</h2>
        </div>
    </div>
</section>

<div class="container-fluid container-fullw bg-white">
<?php if(isset($error)): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>
<?php if(isset($success)): ?>
    <div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>

<form method="post">
    <div class="form-group">
        <label>Drug Name</label>
        <input type="text" name="name" class="form-control" required>
    </div>
    <div class="form-group">
        <label>Brand</label>
        <input type="text" name="brand" class="form-control">
    </div>
    <div class="form-group">
        <label>Batch Number</label>
        <input type="text" name="batch_no" class="form-control" required>
    </div>
    <div class="form-group">
        <label>Expiry Date</label>
        <input type="date" name="expiry_date" class="form-control">
    </div>
    <div class="form-group">
        <label>Quantity</label>
        <input type="number" name="quantity" class="form-control" required>
    </div>
    <div class="form-group">
        <label>Unit</label>
        <input type="text" name="unit" class="form-control" value="tablet" required>
    </div>
    <div class="form-group">
        <label>Price (₦)</label>
        <input type="number" step="0.01" name="price" class="form-control" required>
    </div>
    <button type="submit" name="add_drug" class="btn btn-success">Add Drug</button>
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
