<?php
session_start();
require_once('include/config.php');

// Check connection
if (!$con) {
    die("Database connection failed: " . mysqli_connect_error());
}

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor'){
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$sql = mysqli_query($con, "SELECT * FROM doctor WHERE id = '$user_id'");
if(!$sql) {
    die("Error fetching user data: " . mysqli_error($con));
}
$user_data = mysqli_fetch_array($sql);

// Initialize variables
$today_appointments = 0;
$total_patients = 0;
$pending_prescriptions = 0;
$today = date('Y-m-d');

// Check if appointments table exists
$check_appointments = mysqli_query($con, "SHOW TABLES LIKE 'appointments'");
if(mysqli_num_rows($check_appointments) > 0) {
    // Get today's appointments count
    $appointments_query = mysqli_query($con, "SELECT COUNT(*) as total FROM appointments WHERE doctor_id = '$user_id' AND appointment_date = '$today' AND status = 'scheduled'");
    if($appointments_query) {
        $appointments_data = mysqli_fetch_array($appointments_query);
        $today_appointments = $appointments_data['total'];
    }
    
    // Get total patients count
    $patients_query = mysqli_query($con, "SELECT COUNT(DISTINCT patient_id) as total FROM appointments WHERE doctor_id = '$user_id'");
    if($patients_query) {
        $patients_data = mysqli_fetch_array($patients_query);
        $total_patients = $patients_data['total'];
    }
}

// Check if prescriptions table exists
$check_prescriptions = mysqli_query($con, "SHOW TABLES LIKE 'prescriptions'");
if(mysqli_num_rows($check_prescriptions) > 0) {
    // Get pending prescriptions
    $prescriptions_query = mysqli_query($con, "SELECT COUNT(*) as total FROM prescriptions WHERE doctor_id = '$user_id' AND status = 'pending'");
    if($prescriptions_query) {
        $prescriptions_data = mysqli_fetch_array($prescriptions_query);
        $pending_prescriptions = $prescriptions_data['total'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Doctor | Dashboard</title>
		
		<?php include 'include/css.php'; ?>
	</head>
	<body>
		<div id="app">		
			<!-- SIDEBAR -->

			<?php include 'include/sidebar.php'; ?>
			<!-- MAIN CONTENT -->
			<div class="app-content">
			<?php include 'include/header.php';?>
				<!-- end: TOP NAVBAR -->
				<div class="main-content" >
					<div class="wrap-content container" id="container">
						<!-- start: PAGE TITLE -->
						<section id="page-title">
							<div class="row">
								<div class="col-sm-8">
									<h2 class="mainTitle">Doctor | Dashboard</h2>
								</div>
								<ol class="breadcrumb">
									<li>
										<span>Doctor</span>
									</li>
									<li class="active">
										<span>Dashboard</span>
									</li>
								</ol>
							</div>
						</section>
						<!-- end: PAGE TITLE -->
						
						<!-- start: DASHBOARD WIDGETS -->
						<div class="container-fluid container-fullw bg-white">
							<div class="row">
								<!-- Today's Appointments -->
								<div class="col-sm-4">
									<div class="panel panel-white no-radius text-center">
										<div class="panel-body">
											<span class="fa-stack fa-2x"> 
												<i class="fa fa-square fa-stack-2x text-primary"></i> 
												<i class="fa fa-calendar fa-stack-1x fa-inverse"></i> 
											</span>
											<h2 class="StepTitle">Today's Appointments</h2>
											<p class="links cl-effect-1">
												<a href="today-appointments.php">
													Total: <?php echo $today_appointments; ?>
												</a>
											</p>
										</div>
									</div>
								</div>
								
								<!-- Total Patients -->
								<div class="col-sm-4">
									<div class="panel panel-white no-radius text-center">
										<div class="panel-body">
											<span class="fa-stack fa-2x"> 
												<i class="fa fa-square fa-stack-2x text-success"></i> 
												<i class="fa fa-users fa-stack-1x fa-inverse"></i> 
											</span>
											<h2 class="StepTitle">My Patients</h2>
											<p class="links cl-effect-1">
												<a href="patients.php">
													Total: <?php echo $total_patients; ?>
												</a>
											</p>
										</div>
									</div>
								</div>
								
								<!-- Pending Prescriptions -->
								<div class="col-sm-4">
									<div class="panel panel-white no-radius text-center">
										<div class="panel-body">
											<span class="fa-stack fa-2x"> 
												<i class="fa fa-square fa-stack-2x text-warning"></i> 
												<i class="fa fa-file-text fa-stack-1x fa-inverse"></i> 
											</span>
											<h2 class="StepTitle">Pending Prescriptions</h2>
											<p class="links cl-effect-1">
												<a href="prescriptions.php?status=pending">
													Total: <?php echo $pending_prescriptions; ?>
												</a>
											</p>
										</div>
									</div>
								</div>
							</div>
							
							<!-- Today's Appointments Table -->
							<div class="row">
								<div class="col-md-12">
									<div class="panel panel-white">
										<div class="panel-heading">
											<h4 class="panel-title">Today's Appointments</h4>
										</div>
										<div class="panel-body">
											<div class="table-responsive">
												<table class="table table-bordered table-hover">
													<thead>
														<tr>
															<th>#</th>
															<th>Patient Name</th>
															<th>Appointment Time</th>
															<th>Status</th>
															<th>Action</th>
														</tr>
													</thead>
													<tbody>
														<?php
														$check_appointments = mysqli_query($con, "SHOW TABLES LIKE 'appointments'");
														$check_patients = mysqli_query($con, "SHOW TABLES LIKE 'patients'");
														
														if(mysqli_num_rows($check_appointments) > 0 && mysqli_num_rows($check_patients) > 0) {
															$query = "SELECT a.*, p.name as patient_name, p.patient_id as patient_code
																	  FROM appointments a 
																	  JOIN patients p ON a.patient_id = p.id 
																	  WHERE a.doctor_id = '$user_id' 
																	  AND a.appointment_date = '$today'
																	  ORDER BY a.appointment_time ASC 
																	  LIMIT 10";
															$result = mysqli_query($con, $query);
															$cnt = 1;
															if($result && mysqli_num_rows($result) > 0) {
																while($row = mysqli_fetch_array($result)) {
														?>
														<tr>
															<td><?php echo $cnt; ?></td>
															<td>
																<?php echo htmlspecialchars($row['patient_name']); ?>
																<small class="text-muted d-block">ID: <?php echo htmlspecialchars($row['patient_code']); ?></small>
															</td>
															<td><?php echo date('h:i A', strtotime($row['appointment_time'])); ?></td>
															<td>
																<span class="label label-<?php 
																	if($row['status'] == 'completed') echo 'success';
																	elseif($row['status'] == 'cancelled') echo 'danger';
																	else echo 'warning';
																?>">
																	<?php echo ucfirst($row['status']); ?>
																</span>
															</td>
															<td>
																<a href="view-appointment.php?id=<?php echo $row['id']; ?>" class="btn btn-xs btn-primary">
																	<i class="fa fa-eye"></i> View
																</a>
															</td>
														</tr>
														<?php $cnt++; } 
															} else { ?>
															<tr>
																<td colspan="5" class="text-center">No appointments for today</td>
															</tr>
														<?php } 
														} else { ?>
														<tr>
															<td colspan="5" class="text-center">
																<div class="alert alert-info">
																	<h4>Database Setup Required</h4>
																	<p>The appointments table needs to be created.</p>
																	<?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'doctor') { ?>
																		<a href="../admin/setup-database.php" class="btn btn-primary">Setup Database</a>
																	<?php } else { ?>
																		<p>Please contact administrator to setup the appointments system.</p>
																	<?php } ?>
																</div>
															</td>
														</tr>
														<?php } ?>
													</tbody>
												</table>
											</div>
											<div class="text-right">
												<a href="appointments.php" class="btn btn-primary">View All Appointments</a>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<!-- end: DASHBOARD WIDGETS -->
					</div>
				</div>
			</div>
			
			<!-- start: FOOTER -->
			<?php include 'include/footer.php'; ?>
			<!-- end: FOOTER -->
		
			<!-- start: SETTINGS -->
			<?php include 'include/setting.php'; ?>
			<!-- end: SETTINGS -->
		</div>
		
		<?php include 'include/js.php'; ?>
	</body>
</html>