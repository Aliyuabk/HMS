<?php
session_start();
require_once('include/config.php');

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor'){
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch doctor info
$sql = mysqli_query($con, "SELECT * FROM doctor WHERE id='$user_id'");
$user_data = mysqli_fetch_assoc($sql);

// Search parameter
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Fetch patients who have appointments with this doctor
$query = "
SELECT DISTINCT p.* 
FROM patients p
JOIN appointments a ON p.id = a.patient_id
WHERE a.doctor_id='$user_id'
";

if(!empty($search)) {
    $search_safe = mysqli_real_escape_string($con, $search);
    $query .= " AND (p.first_name LIKE '%$search_safe%' 
                    OR p.last_name LIKE '%$search_safe%' 
                    OR p.ehr_no LIKE '%$search_safe%' 
                    OR p.phone LIKE '%$search_safe%' 
                    OR p.email LIKE '%$search_safe%')";
}

$query .= " ORDER BY p.first_name ASC, p.last_name ASC";
$result = mysqli_query($con, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Doctor | Manage Patients</title>
    <?php include 'include/css.php'; ?>
</head>
<body>
<div id="app">
    <div class="sidebar app-aside" id="sidebar">
        <?php include('include/sidebar.php'); ?>
    </div>

    <div class="app-content">
        <?php include 'include/header.php'; ?>

        <div class="main-content">
            <div class="wrap-content container" id="container">

                <!-- Page Title -->
                <section id="page-title">
                    <div class="row">
                        <div class="col-sm-8">
                            <h2 class="mainTitle">Doctor | Manage Patients</h2>
                        </div>
                        <ol class="breadcrumb">
                            <li>Doctor</li>
                            <li class="active">Patients</li>
                        </ol>
                    </div>
                </section>

                <div class="container-fluid container-fullw bg-white">
                    <div class="row">
                        <div class="col-md-12">

                            <!-- Search Panel -->
                            <div class="panel panel-white">
                                <div class="panel-heading">
                                    <h4 class="panel-title">Search Patients</h4>
                                </div>
                                <div class="panel-body">
                                    <form method="get" class="form-inline">
                                        <div class="form-group">
                                            <input type="text" name="search" class="form-control" 
                                                placeholder="Search by name, EHR No, phone or email" 
                                                value="<?= htmlspecialchars($search); ?>" style="width: 400px;">
                                        </div>
                                        <button type="submit" class="btn btn-primary">Search</button>
                                        <a href="patients.php" class="btn btn-default">Show All</a>
                                    </form>
                                </div>
                            </div>

                            <!-- Patients Table -->
                            <div class="panel panel-white">
                                <div class="panel-heading">
                                    <h4 class="panel-title">Patients List</h4>
                                </div>
                                <div class="panel-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>EHR No</th>
                                                    <th>Name</th>
                                                    <th>Gender</th>
                                                    <th>Phone</th>
                                                    <th>Email</th>
                                                    <th>Last Visit</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $cnt = 1;
                                                if(mysqli_num_rows($result) > 0) {
                                                    while($row = mysqli_fetch_assoc($result)) {
                                                        // Fetch last visit date
                                                        $last_visit_query = mysqli_query($con, "
                                                            SELECT MAX(appointment_date) as last_visit 
                                                            FROM appointments 
                                                            WHERE patient_id='{$row['id']}' AND doctor_id='$user_id'
                                                        ");
                                                        $last_visit_data = mysqli_fetch_assoc($last_visit_query);
                                                        $last_visit = $last_visit_data['last_visit'];
                                                ?>
                                                <tr>
                                                    <td><?= $cnt++; ?></td>
                                                    <td><?= htmlspecialchars($row['ehr_no']); ?></td>
                                                    <td><?= htmlspecialchars($row['first_name'].' '.$row['last_name']); ?></td>
                                                    <td><?= htmlspecialchars($row['gender']); ?></td>
                                                    <td><?= htmlspecialchars($row['phone']); ?></td>
                                                    <td><?= htmlspecialchars($row['email']); ?></td>
                                                    <td><?= $last_visit ? date('M d, Y', strtotime($last_visit)) : 'Never'; ?></td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <a href="view-patient.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-xs">
                                                                <i class="fa fa-eye"></i> View
                                                            </a>
                                                            <a href="add-appointment.php?patient_id=<?= $row['id']; ?>" class="btn btn-success btn-xs">
                                                                <i class="fa fa-calendar"></i> Appointment
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php 
                                                    }
                                                } else { ?>
                                                <tr>
                                                    <td colspan="8" class="text-center">No patients found</td>
                                                </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
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

<?php include 'include/js.php'; ?>
</body>
</html>
