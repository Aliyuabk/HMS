<?php 
session_start();
include('include/config.php');

?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Reception | Billing Report</title>
		
		<link href="http://fonts.googleapis.com/css?family=Lato:300,400,400italic,600,700|Raleway:300,400,500,600,700|Crete+Round:400italic" rel="stylesheet" type="text/css" />
		<link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.min.css">
		<link rel="stylesheet" href="vendor/fontawesome/css/font-awesome.min.css">
		<link rel="stylesheet" href="vendor/themify-icons/themify-icons.min.css">
		<link href="vendor/animate.css/animate.min.css" rel="stylesheet" media="screen">
		<link href="vendor/perfect-scrollbar/perfect-scrollbar.min.css" rel="stylesheet" media="screen">
		<link href="vendor/switchery/switchery.min.css" rel="stylesheet" media="screen">
		<link href="vendor/bootstrap-touchspin/jquery.bootstrap-touchspin.min.css" rel="stylesheet" media="screen">
		<link href="vendor/select2/select2.min.css" rel="stylesheet" media="screen">
		<link href="vendor/bootstrap-datepicker/bootstrap-datepicker3.standalone.min.css" rel="stylesheet" media="screen">
		<link href="vendor/bootstrap-timepicker/bootstrap-timepicker.min.css" rel="stylesheet" media="screen">
		<link rel="stylesheet" href="assets/css/styles.css">
		<link rel="stylesheet" href="assets/css/plugins.css">
		<link rel="stylesheet" href="assets/css/themes/theme-1.css" id="skin_color" />
        <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
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
									<h2 class="mainTitle">Reception | Billing Report</h2>
								</div>
								<ol class="breadcrumb">
									<li>
										<span>Reception</span>
									</li>
									<li class="active">
										<span>Billing Report</span>
									</li>
								</ol>
							</div>
						</section>
						<!-- end: PAGE TITLE -->
						
						<!-- start: BILLING REPORT -->
						<div class="container-fluid container-fullw bg-white">
							<div class="row">
								<div class="col-md-12">
									<div class="panel panel-white">
										<div class="panel-heading">
											<h4 class="panel-title">Filter Billing Records</h4>
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
															<label>Payment Status</label>
															<select name="payment_status" class="form-control">
																<option value="">All Status</option>
																<option value="paid">Paid</option>
																<option value="pending">Pending</option>
																<option value="partial">Partial</option>
															</select>
														</div>
													</div>
													<div class="col-md-3">
														<div class="form-group">
															<label>Payment Method</label>
															<select name="payment_method" class="form-control">
																<option value="">All Methods</option>
																<option value="cash">Cash</option>
																<option value="card">Card</option>
																<option value="insurance">Insurance</option>
																<option value="online">Online</option>
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
											<h4 class="panel-title">Billing Report Summary</h4>
										</div>
										<div class="panel-body">
											<div class="row">
												<div class="col-md-3 col-sm-6">
													<div class="panel panel-white no-radius text-center">
														<div class="panel-body">
															<span class="fa-stack fa-2x"> 
																<i class="fa fa-square fa-stack-2x text-primary"></i> 
																<i class="fa fa-money fa-stack-1x fa-inverse"></i> 
															</span>
															<h2 class="StepTitle">Total Revenue</h2>
															<p class="text-large">$0.00</p>
														</div>
													</div>
												</div>
												<div class="col-md-3 col-sm-6">
													<div class="panel panel-white no-radius text-center">
														<div class="panel-body">
															<span class="fa-stack fa-2x"> 
																<i class="fa fa-square fa-stack-2x text-success"></i> 
																<i class="fa fa-check-circle fa-stack-1x fa-inverse"></i> 
															</span>
															<h2 class="StepTitle">Paid Amount</h2>
															<p class="text-large">$0.00</p>
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
															<h2 class="StepTitle">Pending Amount</h2>
															<p class="text-large">$0.00</p>
														</div>
													</div>
												</div>
												<div class="col-md-3 col-sm-6">
													<div class="panel panel-white no-radius text-center">
														<div class="panel-body">
															<span class="fa-stack fa-2x"> 
																<i class="fa fa-square fa-stack-2x text-info"></i> 
																<i class="fa fa-file-text-o fa-stack-1x fa-inverse"></i> 
															</span>
															<h2 class="StepTitle">Total Invoices</h2>
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
											<h4 class="panel-title">Billing Details</h4>
										</div>
										<div class="panel-body">
											<div class="table-responsive">
												<table class="table table-bordered table-hover" id="billingTable">
													<thead>
														<tr>
															<th>#</th>
															<th>Invoice No</th>
															<th>Patient Name</th>
															<th>Date</th>
															<th>Service Type</th>
															<th>Total Amount</th>
															<th>Paid Amount</th>
															<th>Balance</th>
															<th>Status</th>
															<th>Payment Method</th>
														</tr>
													</thead>
													<tbody>
														<tr>
															<td colspan="10" class="text-center">No billing records found</td>
														</tr>
													</tbody>
												</table>
											</div>
										</div>
									</div>
								</div>
							</div>
							
							<div class="row">
								<div class="col-md-6">
									<div class="panel panel-white">
										<div class="panel-heading">
											<h4 class="panel-title">Payment Method Distribution</h4>
										</div>
										<div class="panel-body">
											<div class="table-responsive">
												<table class="table table-bordered">
													<thead>
														<tr>
															<th>Payment Method</th>
															<th>Count</th>
															<th>Amount</th>
															<th>Percentage</th>
														</tr>
													</thead>
													<tbody>
														<tr>
															<td colspan="4" class="text-center">No payment data available</td>
														</tr>
													</tbody>
												</table>
											</div>
										</div>
									</div>
								</div>
								<div class="col-md-6">
									<div class="panel panel-white">
										<div class="panel-heading">
											<h4 class="panel-title">Service Type Revenue</h4>
										</div>
										<div class="panel-body">
											<div class="table-responsive">
												<table class="table table-bordered">
													<thead>
														<tr>
															<th>Service Type</th>
															<th>Count</th>
															<th>Revenue</th>
														</tr>
													</thead>
													<tbody>
														<tr>
															<td colspan="3" class="text-center">No service data available</td>
														</tr>
													</tbody>
												</table>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<!-- end: BILLING REPORT -->
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