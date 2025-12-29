<?php	
	session_start();
	include('include/config.php');
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Reception | Dashboard</title>
		
		 <?php include('include/css.php'); ?>
	</head>
	<body>
		<div id="app">		
			<!-- SIDEBAR -->
			<?php include 'include/sidebar.php';?>
			
			<!-- MAIN CONTENT -->
			<div class="app-content">
				<!-- start: TOP NAVBAR -->
				<?php include 'include/header.php';?>
						<!-- end: TOP NAVBAR -->	
				<!-- end: TOP NAVBAR -->
				<div class="main-content" >
					<div class="wrap-content container" id="container">
						<!-- start: PAGE TITLE -->
						<section id="page-title">
							<div class="row">
								<div class="col-sm-8">
									<h2 class="mainTitle">Reception | Dashboard</h2>
								</div>
								<ol class="breadcrumb">
									<li>
										<span>Reception</span>
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
								<div class="col-sm-3">
									<div class="panel panel-white no-radius text-center">
										<div class="panel-body">
											<span class="fa-stack fa-2x"> 
												<i class="fa fa-square fa-stack-2x text-primary"></i> 
												<i class="fa fa-calendar fa-stack-1x fa-inverse"></i> 
											</span>
											<h2 class="StepTitle">Today's Appointments</h2>
											<p class="links cl-effect-1">
												<a href="reception-today-appointments.php">
													Total: 0												</a>
											</p>
										</div>
									</div>
								</div>
								
								<!-- Total Patients -->
								<div class="col-sm-3">
									<div class="panel panel-white no-radius text-center">
										<div class="panel-body">
											<span class="fa-stack fa-2x"> 
												<i class="fa fa-square fa-stack-2x text-success"></i> 
												<i class="fa fa-users fa-stack-1x fa-inverse"></i> 
											</span>
											<h2 class="StepTitle">Total Patients</h2>
											<p class="links cl-effect-1">
												<a href="reception-patients.php">
													Total: 0												</a>
											</p>
										</div>
									</div>
								</div>
								
								<!-- Available Doctors -->
								<div class="col-sm-3">
									<div class="panel panel-white no-radius text-center">
										<div class="panel-body">
											<span class="fa-stack fa-2x"> 
												<i class="fa fa-square fa-stack-2x text-warning"></i> 
												<i class="fa fa-user-md fa-stack-1x fa-inverse"></i> 
											</span>
											<h2 class="StepTitle">Available Doctors</h2>
											<p class="links cl-effect-1">
												<a href="reception-doctors.php">
													Total: 0												</a>
											</p>
										</div>
									</div>
								</div>

								<!-- Pending Bills -->
								<div class="col-sm-3">
									<div class="panel panel-white no-radius text-center">
										<div class="panel-body">
											<span class="fa-stack fa-2x"> 
												<i class="fa fa-square fa-stack-2x text-danger"></i> 
												<i class="fa fa-money fa-stack-1x fa-inverse"></i> 
											</span>
											<h2 class="StepTitle">Pending Bills</h2>
											<p class="links cl-effect-1">
												<a href="reception-billing.php?status=pending">
													Total: 0												</a>
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
															<th>Doctor</th>
															<th>Appointment Time</th>
															<th>Status</th>
															<th>Action</th>
														</tr>
													</thead>
													<tbody>
																													<tr>
																<td colspan="6" class="text-center">No appointments for today</td>
															</tr>
																											</tbody>
												</table>
											</div>
											<div class="text-right">
												<a href="reception-appointments.php" class="btn btn-primary">View All Appointments</a>
												<a href="reception-add-appointment.php" class="btn btn-success">Add New Appointment</a>
											</div>
										</div>
									</div>
								</div>
							</div>

							<!-- Recent Patients -->
							<div class="row">
								<div class="col-md-12">
									<div class="panel panel-white">
										<div class="panel-heading">
											<h4 class="panel-title">Recent Patients</h4>
										</div>
										<div class="panel-body">
											<div class="table-responsive">
												<table class="table table-bordered table-hover">
													<thead>
														<tr>
															<th>#</th>
															<th>Patient Name</th>
															<th>Phone</th>
															<th>Email</th>
															<th>Registration Date</th>
															<th>Action</th>
														</tr>
													</thead>
													<tbody>
																													<tr>
																<td colspan="6" class="text-center">No recent patients</td>
															</tr>
																											</tbody>
												</table>
											</div>
											<div class="text-right">
												<a href="reception-patients.php" class="btn btn-primary">View All Patients</a>
												<a href="reception-add-patient.php" class="btn btn-success">Add New Patient</a>
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
			<?php include 'include/footer.php';?>
			<!-- end: FOOTER -->
		
			<!-- start: SETTINGS -->
			<?php include 'include/setting.php';?>
			<!-- end: SETTINGS -->
		</div>
		
		  <?php include('include/js.php'); ?>
	</body>
</html>