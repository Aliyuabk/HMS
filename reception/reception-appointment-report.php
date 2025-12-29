<?php 
	session_start();
	 include('include/config.php');
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Reception | Appointment Report</title>
		
		<?php include('include/css.php'); ?>
	</head>
	<body>
		<div id="app">		
			<!-- SIDEBAR -->
			<?php include('include/sidebar.php'); ?>
			
			<!-- MAIN CONTENT -->
			<div class="app-content">
			<?php include('include/header.php'); ?>
				<!-- end: TOP NAVBAR -->
				<div class="main-content" >
					<div class="wrap-content container" id="container">
						<!-- start: PAGE TITLE -->
						<section id="page-title">
							<div class="row">
								<div class="col-sm-8">
									<h2 class="mainTitle">Reception | Appointment Report</h2>
								</div>
								<ol class="breadcrumb">
									<li>
										<span>Reception</span>
									</li>
									<li class="active">
										<span>Appointment Report</span>
									</li>
								</ol>
							</div>
						</section>
						<!-- end: PAGE TITLE -->
						
						<!-- start: APPOINTMENT REPORT -->
						<div class="container-fluid container-fullw bg-white">
							<div class="row">
								<div class="col-md-12">
									<div class="panel panel-white">
										<div class="panel-heading">
											<h4 class="panel-title">Filter Appointments</h4>
										</div>
										<div class="panel-body">
											<form method="post" action="">
												<div class="row">
													<div class="col-md-3">
														<div class="form-group">
															<label>From Date</label>
															<input type="date" name="from_date" class="form-control" required>
														</div>
													</div>
													<div class="col-md-3">
														<div class="form-group">
															<label>To Date</label>
															<input type="date" name="to_date" class="form-control" required>
														</div>
													</div>
													<div class="col-md-3">
														<div class="form-group">
															<label>Doctor</label>
															<select name="doctor" class="form-control">
																<option value="">All Doctors</option>
																<option value="1">Dr. John Smith</option>
																<option value="2">Dr. Sarah Johnson</option>
															</select>
														</div>
													</div>
													<div class="col-md-3">
														<div class="form-group">
															<label>Status</label>
															<select name="status" class="form-control">
																<option value="">All Status</option>
																<option value="scheduled">Scheduled</option>
																<option value="completed">Completed</option>
																<option value="cancelled">Cancelled</option>
															</select>
														</div>
													</div>
												</div>
												<div class="row">
													<div class="col-md-12">
														<button type="submit" name="filter" class="btn btn-primary">
															<i class="fa fa-filter"></i> Filter
														</button>
														<button type="button" class="btn btn-success" onclick="window.print()">
															<i class="fa fa-print"></i> Print Report
														</button>
													</div>
												</div>
											</form>
										</div>
									</div>
								</div>
							</div>
							
							<div class="row">
								<div class="col-md-12">
									<div class="panel panel-white">
										<div class="panel-heading">
											<h4 class="panel-title">Appointment Report Summary</h4>
										</div>
										<div class="panel-body">
											<div class="row">
												<div class="col-md-3 col-sm-6">
													<div class="panel panel-white no-radius text-center">
														<div class="panel-body">
															<span class="fa-stack fa-2x"> 
																<i class="fa fa-square fa-stack-2x text-primary"></i> 
																<i class="fa fa-calendar fa-stack-1x fa-inverse"></i> 
															</span>
															<h2 class="StepTitle">Total Appointments</h2>
															<p class="text-large">0</p>
														</div>
													</div>
												</div>
												<div class="col-md-3 col-sm-6">
													<div class="panel panel-white no-radius text-center">
														<div class="panel-body">
															<span class="fa-stack fa-2x"> 
																<i class="fa fa-square fa-stack-2x text-success"></i> 
																<i class="fa fa-check fa-stack-1x fa-inverse"></i> 
															</span>
															<h2 class="StepTitle">Completed</h2>
															<p class="text-large">0</p>
														</div>
													</div>
												</div>
												<div class="col-md-3 col-sm-6">
													<div class="panel panel-white no-radius text-center">
														<div class="panel-body">
															<span class="fa-stack fa-2x"> 
																<i class="fa fa-square fa-stack-2x text-warning"></i> 
																<i class="fa fa-clock-o fa-stack-1x fa-inverse"></i> 
															</span>
															<h2 class="StepTitle">Scheduled</h2>
															<p class="text-large">0</p>
														</div>
													</div>
												</div>
												<div class="col-md-3 col-sm-6">
													<div class="panel panel-white no-radius text-center">
														<div class="panel-body">
															<span class="fa-stack fa-2x"> 
																<i class="fa fa-square fa-stack-2x text-danger"></i> 
																<i class="fa fa-times fa-stack-1x fa-inverse"></i> 
															</span>
															<h2 class="StepTitle">Cancelled</h2>
															<p class="text-large">0</p>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							
							<div class="row">
								<div class="col-md-12">
									<div class="panel panel-white">
										<div class="panel-heading">
											<h4 class="panel-title">Appointment Details</h4>
										</div>
										<div class="panel-body">
											<div class="table-responsive">
												<table class="table table-bordered table-hover" id="appointmentTable">
													<thead>
														<tr>
															<th>#</th>
															<th>Patient Name</th>
															<th>Doctor Name</th>
															<th>Appointment Date</th>
															<th>Appointment Time</th>
															<th>Status</th>
															<th>Room</th>
														</tr>
													</thead>
													<tbody>
														<tr>
															<td colspan="7" class="text-center">No appointment records found</td>
														</tr>
													</tbody>
												</table>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<!-- end: APPOINTMENT REPORT -->
					</div>
				</div>
			</div>
			
			<!-- start: FOOTER -->
			<?php include('include/footer.php');?>
			<!-- end: FOOTER -->
		
			<!-- start: SETTINGS -->
			<?php include('include/setting.php');?>	
			<!-- end: SETTINGS -->
		</div>
		
		<?php include('include/js.php'); ?>
	</body>
</html>