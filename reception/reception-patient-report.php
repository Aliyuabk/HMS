<?php
session_start();
include 'include/config.php';
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Reception | Patient Report</title>
		
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
									<h2 class="mainTitle">Reception | Patient Report</h2>
								</div>
								<ol class="breadcrumb">
									<li>
										<span>Reception</span>
									</li>
									<li class="active">
										<span>Patient Report</span>
									</li>
								</ol>
							</div>
						</section>
						<!-- end: PAGE TITLE -->
						
						<!-- start: PATIENT REPORT -->
						<div class="container-fluid container-fullw bg-white">
							<div class="row">
								<div class="col-md-12">
									<div class="panel panel-white">
										<div class="panel-heading">
											<h4 class="panel-title">Filter Patients</h4>
										</div>
										<div class="panel-body">
											<form method="post" action="">
												<div class="row">
													<div class="col-md-3">
														<div class="form-group">
															<label>Registration Date From</label>
															<input type="date" name="from_date" class="form-control">
														</div>
													</div>
													<div class="col-md-3">
														<div class="form-group">
															<label>Registration Date To</label>
															<input type="date" name="to_date" class="form-control">
														</div>
													</div>
													<div class="col-md-3">
														<div class="form-group">
															<label>Gender</label>
															<select name="gender" class="form-control">
																<option value="">All Gender</option>
																<option value="Male">Male</option>
																<option value="Female">Female</option>
																<option value="Other">Other</option>
															</select>
														</div>
													</div>
													<div class="col-md-3">
														<div class="form-group">
															<label>Blood Group</label>
															<select name="blood_group" class="form-control">
																<option value="">All Blood Groups</option>
																<option value="A+">A+</option>
																<option value="A-">A-</option>
																<option value="B+">B+</option>
																<option value="B-">B-</option>
																<option value="O+">O+</option>
																<option value="O-">O-</option>
																<option value="AB+">AB+</option>
																<option value="AB-">AB-</option>
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
											<h4 class="panel-title">Patient Report Summary</h4>
										</div>
										<div class="panel-body">
											<div class="row">
												<div class="col-md-3 col-sm-6">
													<div class="panel panel-white no-radius text-center">
														<div class="panel-body">
															<span class="fa-stack fa-2x"> 
																<i class="fa fa-square fa-stack-2x text-primary"></i> 
																<i class="fa fa-users fa-stack-1x fa-inverse"></i> 
															</span>
															<h2 class="StepTitle">Total Patients</h2>
															<p class="text-large">0</p>
														</div>
													</div>
												</div>
												<div class="col-md-3 col-sm-6">
													<div class="panel panel-white no-radius text-center">
														<div class="panel-body">
															<span class="fa-stack fa-2x"> 
																<i class="fa fa-square fa-stack-2x text-success"></i> 
																<i class="fa fa-male fa-stack-1x fa-inverse"></i> 
															</span>
															<h2 class="StepTitle">Male Patients</h2>
															<p class="text-large">0</p>
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
															<p class="text-large">0</p>
														</div>
													</div>
												</div>
												<div class="col-md-3 col-sm-6">
													<div class="panel panel-white no-radius text-center">
														<div class="panel-body">
															<span class="fa-stack fa-2x"> 
																<i class="fa fa-square fa-stack-2x text-info"></i> 
																<i class="fa fa-child fa-stack-1x fa-inverse"></i> 
															</span>
															<h2 class="StepTitle">New This Month</h2>
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
											<h4 class="panel-title">Patient Details</h4>
										</div>
										<div class="panel-body">
											<div class="table-responsive">
												<table class="table table-bordered table-hover" id="patientTable">
													<thead>
														<tr>
															<th>#</th>
															<th>Patient ID</th>
															<th>Full Name</th>
															<th>Gender</th>
															<th>Age</th>
															<th>Blood Group</th>
															<th>Phone</th>
															<th>Registration Date</th>
															<th>Last Visit</th>
														</tr>
													</thead>
													<tbody>
														<tr>
															<td colspan="9" class="text-center">No patient records found</td>
														</tr>
													</tbody>
												</table>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<!-- end: PATIENT REPORT -->
					</div>
				</div>
			</div>
			
			<!-- start: FOOTER -->
			<?php include('include/footer.php'); ?>
			<!-- end: FOOTER -->
		
			<!-- start: SETTINGS -->
			<?php include('include/setting.php'); ?>	
			<!-- end: SETTINGS -->
		</div>
		
		<?php include('include/js.php'); ?>
	</body>
</html>