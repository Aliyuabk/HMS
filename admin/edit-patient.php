<?php
session_start();
include('include/config.php');

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../index.php");
    exit;
}
$id = $_GET['id'];

/* Fetch patient */
$stmt = $con->prepare("SELECT * FROM patients WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$patient = $stmt->get_result()->fetch_assoc();

if (!$patient) {
    die("Patient not found");
}

/* Update patient */
if (isset($_POST['update_patient'])) {

    $first_name = $_POST['first_name'];
    $last_name  = $_POST['last_name'];
    $gender     = $_POST['gender'];
    $dob        = $_POST['dob'];
    $phone      = $_POST['phone'];
    $email      = $_POST['email'];
    $address    = $_POST['address'];

    $update = $con->prepare("
        UPDATE patients 
        SET first_name=?, last_name=?, gender=?, dob=?, phone=?, email=?, address=?
        WHERE id=?
    ");

    $update->bind_param(
        "sssssssi",
        $first_name,
        $last_name,
        $gender,
        $dob,
        $phone,
        $email,
        $address,
        $id
    );

    if ($update->execute()) {
        $success = "Patient information updated successfully";
    } else {
        $error = "Update failed. Try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin | Edit Patient</title>

     <?php include 'include/css.php';?>
</head>

<body>
<div id="app">

    <!-- SIDEBAR -->
    <?php include 'include/sidebar.php'; ?>
    <!-- END SIDEBAR -->

    <div class="app-content">
        <!-- TOP NAVBAR -->
        <?php include 'include/header.php'; ?>
        <!-- END TOP NAVBAR -->

        <div class="main-content">
            <div class="wrap-content container" id="container">

                <!-- PAGE TITLE -->
                <section id="page-title">
                    <div class="row">
                        <div class="col-sm-8">
                            <h2 class="mainTitle">Admin | Edit Patient</h2>
                        </div>
                        <ol class="breadcrumb">
                            <li><span>Admin</span></li>
                            <li class="active"><span>Edit Patient</span></li>
                        </ol>
                    </div>
                </section>
                <!-- END PAGE TITLE -->

                <!-- EDIT PATIENT -->
                <div class="container-fluid container-fullw bg-white">
                    <div class="row">
                        <div class="col-md-12">

                            <div class="panel panel-white">
                                <div class="panel-heading">
                                    <h4 class="panel-title">Update Patient Information</h4>
                                </div>

                                <div class="panel-body">

                                    <?php if (isset($success)) { ?>
                                        <div class="alert alert-success"><?= $success; ?></div>
                                    <?php } ?>

                                    <?php if (isset($error)) { ?>
                                        <div class="alert alert-danger"><?= $error; ?></div>
                                    <?php } ?>

                                    <form method="post" autocomplete="off">

                                        <div class="row">
                                            <div class="col-md-6 form-group">
                                                <label>First Name</label>
                                                <input type="text" name="first_name" class="form-control"
                                                       value="<?= $patient['first_name']; ?>" required>
                                            </div>

                                            <div class="col-md-6 form-group">
                                                <label>Last Name</label>
                                                <input type="text" name="last_name" class="form-control"
                                                       value="<?= $patient['last_name']; ?>" required>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 form-group">
                                                <label>Email</label>
                                                <input type="email" name="email" class="form-control"
                                                       value="<?= $patient['email']; ?>">
                                            </div>

                                            <div class="col-md-6 form-group">
                                                <label>Phone</label>
                                                <input type="text" name="phone" class="form-control"
                                                       value="<?= $patient['phone']; ?>">
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 form-group">
                                                <label>Date of Birth</label>
                                                <input type="date" name="dob" class="form-control"
                                                       value="<?= $patient['dob']; ?>">
                                            </div>

                                            <div class="col-md-6 form-group">
                                                <label>Gender</label>
                                                <select name="gender" class="form-control">
                                                    <option value="Male" <?= ($patient['gender']=='Male')?'selected':''; ?>>Male</option>
                                                    <option value="Female" <?= ($patient['gender']=='Female')?'selected':''; ?>>Female</option>
                                                    <option value="Other" <?= ($patient['gender']=='Other')?'selected':''; ?>>Other</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-12 form-group">
                                                <label>Address</label>
                                                <textarea name="address" class="form-control" rows="3"><?= $patient['address']; ?></textarea>
                                            </div>
                                        </div>

                                        <button type="submit" name="update_patient" class="btn btn-primary">
                                            <i class="fa fa-save"></i> Update Patient
                                        </button>

                                        <a href="view-patient.php?id=<?= $patient['id']; ?>" class="btn btn-default">
                                            Cancel
                                        </a>

                                    </form>

                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- END EDIT PATIENT -->

            </div>
        </div>
    </div>

    <?php include 'include/footer.php'; ?>
    <?php include 'include/setting.php'; ?>

</div>
          <?php include 'include/js.php';?>
	</body>
</html>