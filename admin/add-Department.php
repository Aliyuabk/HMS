<?php
session_start();
include('include/config.php');

// Restrict access to admin only
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../index.php");
    exit;
}

$success = "";
$error = "";

if(isset($_POST['submit'])){
    // Sanitize input
    $department_name = trim(mysqli_real_escape_string($con, $_POST['department_name']));
    $section = trim(mysqli_real_escape_string($con, $_POST['section']));

    // Validate inputs
    if(!empty($department_name) && !empty($section)){
        $stmt = $con->prepare("INSERT INTO department (dept_name, section) VALUES (?, ?)");
        $stmt->bind_param("ss", $department_name, $section);

        if($stmt->execute()){
            $success = "Department added successfully";
        } else {
            $error = "Error adding Department. Please try again.";
        }
        $stmt->close();
    }
} else {
        $error = "Please fill all fields correctly.";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin | Add Department</title>
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
                            <h1 class="mainTitle">Admin | Add Department</h1>
                        </div>
                        <ol class="breadcrumb">
                            <li><span>Admin</span></li>
                            <li class="active"><span>Add Department</span></li>
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

                <!-- ADD Department FORM -->
                <div class="container-fluid container-fullw bg-white">
                    <div class="row">
                        <div class="col-md-8 col-lg-8">
                            <div class="panel panel-white">
                                <div class="panel-heading">
                                    <h5 class="panel-title">Add Department</h5>
                                </div>
                                <div class="panel-body">
                                    <form method="post">
                                        <div class="form-group">
                                            <label>Department Name *</label>
                                            <input type="text" name="department_name" class="form-control" required>
                                        </div>

                                        <div class="form-group">
                                            <label>Section *</label>
                                            <select name="section" class="form-control" required>
                                                <option value="">Select Section</option>
                                                <option value="clinical">Clinical Department</option>
                                                <option value="support diagnostic">Support & Diagnostic Department</option>
                                                <option value="public health and preventive services">Public Health & Preventive Services</option>
                                                <option value="administrative">Administrative</option>
                                            </select>
                                        </div>

                                        <button type="submit" name="submit" class="btn btn-primary">Add Department</button>
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
