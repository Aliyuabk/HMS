<?php
session_start();
include('include/config.php');

// Restrict access to admin only
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../index.php");
    exit;
}
// Fetch all departments
$departments = [];
$deptQuery = mysqli_query($con, "SELECT * FROM department ORDER BY section  ASC");
while($row = mysqli_fetch_assoc($deptQuery)){
    $departments[] = $row;
}
$success = "";
$error = "";

if(isset($_POST['submit'])){
    // Sanitize input
    $item_name = trim(mysqli_real_escape_string($con, $_POST['item_name']));
    $dept = trim(mysqli_real_escape_string($con, $_POST['dept']));
    $price_input = str_replace(',', '', $_POST['price']); // remove commas if any
    $price = filter_var($price_input, FILTER_VALIDATE_INT);

    // Validate inputs
    if(!empty($item_name) && !empty($dept) && $price !== false && $price > 0){

        // Insert into ehr_fees table
        $insert_fees = mysqli_query($con, "INSERT INTO ehr_fees (item_name, dept, price) 
        VALUES ('$item_name', '$dept', '$price')");

        if($insert_fees){
            $success = "Fees added successfully: ₦$price.";
        } else {
            $error = "Error adding fees. Please try again.";
        }
    } else {
        $error = "Please fill all fields correctly. Price must be a number greater than 0.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin | Add Fees</title>
    <?php include 'include/css.php';?>
</head>
<body>
<div id="app">
    <?php include('include/sidebar.php');?>
    <div class="app-content">
        <?php include('include/header.php');?>

        <div class="main-content">
            <div class="wrap-content container" id="container">

                <!-- PAGE TITLE -->
                <section id="page-title">
                    <div class="row">
                        <div class="col-sm-8">
                            <h1 class="mainTitle">Admin | Add Fees</h1>
                        </div>
                        <ol class="breadcrumb">
                            <li><span>Admin</span></li>
                            <li class="active"><span>Add Fees</span></li>
                        </ol>
                    </div>
                </section>

                <!-- ALERTS -->
                <?php if($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                <?php if($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <!-- ADD FEES FORM -->
                <div class="container-fluid container-fullw bg-white">
                    <div class="row">
                        <div class="col-md-8 col-lg-8">
                            <div class="panel panel-white">
                                <div class="panel-heading">
                                    <h5 class="panel-title">Add Fees</h5>
                                </div>
                                <div class="panel-body">
                                    <form method="post">
                                        <div class="form-group">
                                            <label>Item Name *</label>
                                            <input type="text" name="item_name" class="form-control" required>
                                        </div>
                                       <div class="form-group">
                                            <label>Department *</label>
                                            <select name="dept" class="form-control" required>
                                                <option value="">Select Department</option>
                                                <?php foreach($departments as $dept): ?>
                                                    <option value="<?= htmlspecialchars($dept['id']); ?>"
                                                        <?= ($fee['dept'] == $dept['id']) ? 'selected' : ''; ?>>
                                                        <?= htmlspecialchars($dept['dept_name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>Item Price (₦) *</label>
                                            <input type="number" name="price" class="form-control" placeholder="20000" required min="1">
                                        </div>
                                        <button type="submit" name="submit" class="btn btn-primary">Add EHR Fees</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <?php include('include/footer.php');?>
    <?php include('include/setting.php');?>
    <?php include('include/js.php');?>
</div>
</body>
</html>
