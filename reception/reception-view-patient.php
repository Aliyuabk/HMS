<?php
session_start();
include('include/config.php');

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'reception'){
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Reception | View Patient</title>

		<?php include('include/css.php'); ?>
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
                            <h2 class="mainTitle">Reception | View Patient</h2>
                        </div>
                        <ol class="breadcrumb">
                            <li><span>Reception</span></li>
                            <li class="active"><span>View Patient</span></li>
                        </ol>
                    </div>
                </section>
                <!-- END PAGE TITLE -->

                <!-- VIEW PATIENT -->
                <div class="container-fluid container-fullw bg-white">
                    <div class="row">
                        <div class="col-md-12">

                            <div class="panel panel-white">
                                <div class="panel-heading">
                                    <h4 class="panel-title">Patient Information</h4>
                                </div>

                                <div class="panel-body">

                                    <div class="row">
                                        <div class="col-md-6 form-group">
                                            <label>First Name</label>
                                            <input type="text" class="form-control" value="<?= $patient['first_name']; ?>" readonly>
                                        </div>

                                        <div class="col-md-6 form-group">
                                            <label>Last Name</label>
                                            <input type="text" class="form-control" value="<?= $patient['last_name']; ?>" readonly>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 form-group">
                                            <label>Email</label>
                                            <input type="text" class="form-control" value="<?= $patient['email']; ?>" readonly>
                                        </div>

                                        <div class="col-md-6 form-group">
                                            <label>Phone</label>
                                            <input type="text" class="form-control" value="<?= $patient['phone']; ?>" readonly>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 form-group">
                                            <label>Date of Birth</label>
                                            <input type="text" class="form-control" value="<?= $patient['dob']; ?>" readonly>
                                        </div>

                                        <div class="col-md-6 form-group">
                                            <label>Gender</label>
                                            <input type="text" class="form-control" value="<?= $patient['gender']; ?>" readonly>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12 form-group">
                                            <label>Address</label>
                                            <textarea class="form-control" rows="3" readonly><?= $patient['address']; ?></textarea>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <a href="reception-edit-patient.php?id=<?= $patient['id']; ?>" class="btn btn-primary">
                                                <i class="fa fa-edit"></i> Edit Patient
                                            </a>
                                            <a href="reception-patients.php" class="btn btn-default">
                                                Back to List
                                            </a>
                                        </div>
                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- END VIEW PATIENT -->

            </div>
        </div>
    </div>

    <?php include 'include/footer.php'; ?>
    <?php include 'include/setting.php'; ?>

</div>

        <?php include('include/js.php'); ?>
	</body>
</html>
