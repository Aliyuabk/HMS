<?php
session_start();
include('include/config.php');

// Restrict access to admin only
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../index.php");
    exit;
}

// Get department ID
if(!isset($_GET['id']) || empty($_GET['id'])){
    header("Location: manage-department.php");
    exit;
}

$id = intval($_GET['id']);
$success = $error = "";

// Fetch existing department
$stmt = $con->prepare("SELECT * FROM department WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows == 0){
    header("Location: manage-department.php");
    exit;
}

$dept = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if(isset($_POST['submit'])){
    $department_name = trim($_POST['department_name']);
    $section = trim($_POST['section']);

    if(!empty($department_name) && !empty($section)){
        // Update department securely
        $stmt = $con->prepare("UPDATE department SET dept_name = ?, section = ? WHERE id = ?");
        $stmt->bind_param("ssi", $department_name, $section, $id);
        if($stmt->execute()){
            $success = "Department updated successfully!";
            // Refresh data
            $dept['dept_name'] = $department_name;
            $dept['section'] = $section;
        } else {
            $error = "Error updating department. Please try again.";
        }
        $stmt->close();
    } else {
        $error = "Please fill all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Department</title>
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
                            <h1 class="mainTitle">Edit Department</h1>
                        </div>
                        <ol class="breadcrumb">
                            <li>Admin</li>
                            <li class="active">Edit Department</li>
                        </ol>
                    </div>
                </section>

                <!-- Alerts -->
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
                                    <h5 class="panel-title">Update Department</h5>
                                </div>
                                <div class="panel-body">
                                    <form method="post">
                                        <div class="form-group">
                                            <label>Department Name *</label>
                                            <input type="text" name="department_name" class="form-control" required
                                                   value="<?= htmlspecialchars($dept['dept_name']); ?>">
                                        </div>

                                        <div class="form-group">
                                            <label>Section *</label>
                                            <select name="section" class="form-control" required>
                                                <option value="">Select Section</option>
                                                <option value="clinical" <?= $dept['section']=='clinical'?'selected':'' ?>>Clinical Department</option>
                                                <option value="support diagnostic" <?= $dept['section']=='support diagnostic'?'selected':'' ?>>Support & Diagnostic Department</option>
                                                <option value="public health and preventive services" <?= $dept['section']=='public health and preventive services'?'selected':'' ?>>Public Health & Preventive Services</option>
                                                <option value="administrative" <?= $dept['section']=='administrative'?'selected':'' ?>>Administrative</option>
                                            </select>
                                        </div>

                                        <button type="submit" name="submit" class="btn btn-success">Update Department</button>
                                        <a href="manage-department.php" class="btn btn-default">Cancel</a>
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
