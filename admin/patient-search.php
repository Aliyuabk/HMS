<?php
session_start();
include('include/config.php');

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../index.php");
    exit;
}

$search_results = []; 
$search_term = '';

if(isset($_GET['search'])) {
    $search_term = mysqli_real_escape_string($con, $_GET['search']);
    
    $query = "SELECT p.*, 
                     (SELECT COUNT(*) FROM appointments WHERE patient_id = p.id) as total_appointments,
                     (SELECT MAX(appointment_date) FROM appointments WHERE patient_id = p.id) as last_appointment
              FROM patients p 
              WHERE CONCAT(p.first_name, ' ', p.last_name) LIKE '%$search_term%' 
                 OR p.id LIKE '%$search_term%' 
                 OR p.email LIKE '%$search_term%' 
                 OR p.phone LIKE '%$search_term%'
              ORDER BY p.created_at DESC";
    
    $search_results = mysqli_query($con, $query);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin | Patient Search</title>
    <?php include 'include/css.php';?>
</head>
<body>
<div id="app">		
    <?php include('include/sidebar.php');?>
    <div class="app-content">
        <?php include('include/header.php');?>						
        <div class="main-content">
            <div class="wrap-content container" id="container">
                <section id="page-title">
                    <div class="row">
                        <div class="col-sm-8">
                            <h2 class="mainTitle">Admin | Patient Search</h2>
                        </div>
                        <ol class="breadcrumb">
                            <li><span>Admin</span></li>
                            <li class="active"><span>Patient Search</span></li>
                        </ol>
                    </div>
                </section>

                <div class="container-fluid container-fullw bg-white">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="panel panel-white">
                                <div class="panel-heading">
                                    <h4 class="panel-title">Search Patients</h4>
                                </div>
                                <div class="panel-body">
                                    <form method="GET" action="" class="form-inline">
                                        <div class="form-group" style="width: 80%;">
                                            <input type="text" name="search" class="form-control" 
                                                   placeholder="Search by name, patient ID, email, or phone..." 
                                                   value="<?php echo htmlentities($search_term); ?>" 
                                                   style="width: 100%;">
                                        </div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fa fa-search"></i> Search
                                        </button>
                                        <?php if($search_term): ?>
                                        <a href="patient-search.php" class="btn btn-default">Clear</a>
                                        <?php endif; ?>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php if(isset($_GET['search'])): ?>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="panel panel-white">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        Search Results for "<?php echo htmlentities($search_term); ?>"
                                        <span class="badge badge-primary">
                                            <?php echo mysqli_num_rows($search_results); ?> patient(s) found
                                        </span>
                                    </h4>
                                </div>
                                <div class="panel-body">
                                    <?php if(mysqli_num_rows($search_results) > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Patient ID</th>
                                                    <th>Full Name</th>
                                                    <th>Gender</th>
                                                    <th>Date of Birth</th>
                                                    <th>Phone</th>
                                                    <th>Total Appointments</th>
                                                    <th>Last Appointment</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $count = 1;
                                                while($patient = mysqli_fetch_assoc($search_results)) {
                                                ?>
                                                <tr>
                                                    <td><?php echo $count++; ?></td>
                                                    <td><?php echo htmlentities($patient['ehr_no']); ?></td>
                                                    <td><?php echo htmlentities($patient['first_name'].' '.$patient['last_name']); ?></td>
                                                    <td><?php echo htmlentities($patient['gender']); ?></td>
                                                    <td><?php echo date('d-m-Y', strtotime($patient['dob'])); ?></td>
                                                    <td><?php echo htmlentities($patient['phone']); ?></td>
                                                    <td class="text-center">
                                                        <span class="badge badge-info"><?php echo $patient['total_appointments']; ?></span>
                                                    </td>
                                                    <td>
                                                        <?php 
                                                        if($patient['last_appointment']) {
                                                            echo date('d-m-Y', strtotime($patient['last_appointment']));
                                                        } else {
                                                            echo '<span class="text-muted">No appointments</span>';
                                                        }
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <a href="view-patient.php?id=<?php echo $patient['id']; ?>" class="btn btn-info btn-xs">
                                                            <i class="fa fa-eye"></i> View
                                                        </a>
                                                        <a href="edit-patient.php?id=<?php echo $patient['id']; ?>" class="btn btn-warning btn-xs">
                                                            <i class="fa fa-edit"></i> Edit
                                                        </a>
                                                        <a href="patient-appointments.php?patient_id=<?php echo $patient['id']; ?>" class="btn btn-primary btn-xs">
                                                            <i class="fa fa-calendar"></i> Appointments
                                                        </a>
                                                    </td>
                                                </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <?php else: ?>
                                    <div class="alert alert-warning">
                                        <strong>No patients found!</strong> Try searching with different terms.
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Quick Stats -->
                    <div class="row">
                        <?php
                        $stats = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS total FROM patients"));
                        $new_today = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS total FROM patients WHERE DATE(created_at)=CURDATE()"));
                        $male = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS total FROM patients WHERE gender='Male'"));
                        $female = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS total FROM patients WHERE gender='Female'"));
                        ?>
                        <div class="col-md-3 col-sm-6">
                            <div class="panel panel-white no-radius text-center">
                                <div class="panel-body">
                                    <span class="fa-stack fa-2x"> 
                                        <i class="fa fa-square fa-stack-2x text-primary"></i> 
                                        <i class="fa fa-users fa-stack-1x fa-inverse"></i> 
                                    </span>
                                    <h2 class="StepTitle">Total Patients</h2>
                                    <p class="text-large"><?php echo $stats['total']; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="panel panel-white no-radius text-center">
                                <div class="panel-body">
                                    <span class="fa-stack fa-2x"> 
                                        <i class="fa fa-square fa-stack-2x text-success"></i> 
                                        <i class="fa fa-user-plus fa-stack-1x fa-inverse"></i> 
                                    </span>
                                    <h2 class="StepTitle">New Today</h2>
                                    <p class="text-large"><?php echo $new_today['total']; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="panel panel-white no-radius text-center">
                                <div class="panel-body">
                                    <span class="fa-stack fa-2x"> 
                                        <i class="fa fa-square fa-stack-2x text-info"></i> 
                                        <i class="fa fa-male fa-stack-1x fa-inverse"></i> 
                                    </span>
                                    <h2 class="StepTitle">Male Patients</h2>
                                    <p class="text-large"><?php echo $male['total']; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="panel panel-white no-radius text-center">
                                <div class="panel-body">
                                    <span class="fa-stack fa-2x"> 
                                        <i class="fa fa-square fa-stack-2x text-warning"></i> 
                                        <i class="fa fa-female fa-stack-1x fa-inverse"></i> 
                                    </span>
                                    <h2 class="StepTitle">Female Patients</h2>
                                    <p class="text-large"><?php echo $female['total']; ?></p>
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
</div>
<?php include 'include/js.php';?>
</body>
</html>
