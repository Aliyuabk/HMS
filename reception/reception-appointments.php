<?php 
	session_start();
	 include('include/config.php');

?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Reception | All Appointments</title>
		
		<?php include('include/css.php'); ?>
	</head>
	<body>
		<div id="app">		
			<!-- SIDEBAR -->
		<?php include('include/sidebar.php');?>
			<!-- END SIDEBAR -->
			
			<!-- MAIN CONTENT -->
			<div class="app-content">
			<!-- start: TOP NAVBAR -->
			<?php include('include/header.php');?>
						
				<!-- end: TOP NAVBAR -->
				<div class="main-content" >
					<div class="wrap-content container" id="container">
						<!-- start: PAGE TITLE -->
						<section id="page-title">
							<div class="row">
								<div class="col-sm-8">
									<h2 class="mainTitle">Reception | All Appointments</h2>
								</div>
								<ol class="breadcrumb">
									<li>
										<span>Reception</span>
									</li>
									<li class="active">
										<span>All Appointments</span>
									</li>
								</ol>
							</div>
						</section>
						<!-- end: PAGE TITLE -->
						
						<!-- start: APPOINTMENTS TABLE -->
						<div class="container-fluid container-fullw bg-white">
							<div class="row">
								<div class="col-md-12">
									<div class="panel panel-white">
										<div class="panel-heading">
											<h4 class="panel-title">All Appointments</h4>
										</div>
										<div class="panel-body">
											<div class="table-responsive">
												<table class="table table-bordered table-hover">
													<thead>
														<tr>
															<th>#</th>
															<th>Appointment ID</th>
															<th>Patient Name</th>
															<th>Doctor</th>
															<th>Date</th>
															<th>Time</th>
															<th>Status</th>
															<th>Action</th>
														</tr>
													</thead>
													<tbody>
														<tr>
															<td colspan="8" class="text-center">No appointment records found</td>
														</tr>
													</tbody>
												</table>
											</div>
											<div class="text-right">
												<a href="reception-add-appointment.php" class="btn btn-success">
													<i class="fa fa-plus"></i> Add New Appointment
												</a>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<!-- end: APPOINTMENTS TABLE -->
					</div>
				</div>
			</div>
			
			<!-- start: FOOTER -->
			<?php include('include/footer.php');?>	
			<!-- end: FOOTER -->
			 <!-- START: SETTINGS  -->
			<?php include('include/setting.php');?>	
			<!-- end: SETTINGS -->
			
		</div>
		
		<?php include('include/js.php'); ?>
</html>