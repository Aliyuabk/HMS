<?php 
session_start();
include 'include/config.php';
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Reception | Room Management</title>
		
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
									<h2 class="mainTitle">Reception | Room Management</h2>
								</div>
								<ol class="breadcrumb">
									<li>
										<span>Reception</span>
									</li>
									<li class="active">
										<span>Room Management</span>
									</li>
								</ol>
							</div>
						</section>
						<!-- end: PAGE TITLE -->
						
						<!-- start: ROOM MANAGEMENT -->
						<div class="container-fluid container-fullw bg-white">
							<div class="row">
								<div class="col-md-12">
									<div class="panel panel-white">
										<div class="panel-heading">
											<h4 class="panel-title">Room Status Dashboard</h4>
										</div>
										<div class="panel-body">
											<div class="row">
												<div class="col-md-3">
													<div class="room-card">
														<div class="room-header bg-primary">
															<h4>Room 101</h4>
															<p>Consultation Room</p>
														</div>
														<div class="room-body">
															<p><strong>Doctor:</strong> Dr. Abduljalal Auwal</p>
															<p><strong>Current Patient:</strong> John Doe</p>
															<p><strong>Status:</strong> <span class="label label-success">In Use</span></p>
															<p><strong>Next Appointment:</strong> 10:15 AM</p>
															<div class="room-actions">
																<button class="btn btn-sm btn-info">View Details</button>
																<button class="btn btn-sm btn-warning">Update Status</button>
															</div>
														</div>
													</div>
												</div>
												<div class="col-md-3">
													<div class="room-card">
														<div class="room-header bg-success">
															<h4>Room 102</h4>
															<p>Consultation Room</p>
														</div>
														<div class="room-body">
															<p><strong>Doctor:</strong> Dr. Sarah Johnson</p>
															<p><strong>Current Patient:</strong> Jane Smith</p>
															<p><strong>Status:</strong> <span class="label label-success">In Use</span></p>
															<p><strong>Next Appointment:</strong> 11:00 AM</p>
															<div class="room-actions">
																<button class="btn btn-sm btn-info">View Details</button>
																<button class="btn btn-sm btn-warning">Update Status</button>
															</div>
														</div>
													</div>
												</div>
												<div class="col-md-3">
													<div class="room-card">
														<div class="room-header bg-warning">
															<h4>Room 103</h4>
															<p>Examination Room</p>
														</div>
														<div class="room-body">
															<p><strong>Doctor:</strong> Dr. Michael Chen</p>
															<p><strong>Current Patient:</strong> None</p>
															<p><strong>Status:</strong> <span class="label label-warning">Cleaning</span></p>
															<p><strong>Next Appointment:</strong> 11:30 AM</p>
															<div class="room-actions">
																<button class="btn btn-sm btn-info">View Details</button>
																<button class="btn btn-sm btn-success">Mark Available</button>
															</div>
														</div>
													</div>
												</div>
												<div class="col-md-3">
													<div class="room-card">
														<div class="room-header bg-danger">
															<h4>Room 104</h4>
															<p>Pediatric Room</p>
														</div>
														<div class="room-body">
															<p><strong>Doctor:</strong> Dr. Emily Brown</p>
															<p><strong>Current Patient:</strong> None</p>
															<p><strong>Status:</strong> <span class="label label-danger">Not Available</span></p>
															<p><strong>Next Appointment:</strong> N/A</p>
															<div class="room-actions">
																<button class="btn btn-sm btn-info">View Details</button>
																<button class="btn btn-sm btn-success">Mark Available</button>
															</div>
														</div>
													</div>
												</div>
											</div>
											
											<div class="row" style="margin-top: 20px;">
												<div class="col-md-3">
													<div class="room-card">
														<div class="room-header bg-info">
															<h4>Room 201</h4>
															<p>Private Room</p>
														</div>
														<div class="room-body">
															<p><strong>Patient:</strong> Robert Johnson</p>
															<p><strong>Admitted:</strong> 2024-01-18</p>
															<p><strong>Status:</strong> <span class="label label-primary">Occupied</span></p>
															<p><strong>Discharge:</strong> 2024-01-25</p>
															<div class="room-actions">
																<button class="btn btn-sm btn-info">View Details</button>
																<button class="btn btn-sm btn-warning">Update</button>
															</div>
														</div>
													</div>
												</div>
												<div class="col-md-3">
													<div class="room-card">
														<div class="room-header bg-success">
															<h4>Room 202</h4>
															<p>Semi-Private Room</p>
														</div>
														<div class="room-body">
															<p><strong>Patient:</strong> Maria Garcia</p>
															<p><strong>Admitted:</strong> 2024-01-20</p>
															<p><strong>Status:</strong> <span class="label label-primary">Occupied</span></p>
															<p><strong>Discharge:</strong> 2024-01-22</p>
															<div class="room-actions">
																<button class="btn btn-sm btn-info">View Details</button>
																<button class="btn btn-sm btn-warning">Update</button>
															</div>
														</div>
													</div>
												</div>
												<div class="col-md-3">
													<div class="room-card">
														<div class="room-header bg-success">
															<h4>Room 203</h4>
															<p>General Ward</p>
														</div>
														<div class="room-body">
															<p><strong>Patient:</strong> David Wilson</p>
															<p><strong>Admitted:</strong> 2024-01-21</p>
															<p><strong>Status:</strong> <span class="label label-primary">Occupied</span></p>
															<p><strong>Discharge:</strong> 2024-01-23</p>
															<div class="room-actions">
																<button class="btn btn-sm btn-info">View Details</button>
																<button class="btn btn-sm btn-warning">Update</button>
															</div>
														</div>
													</div>
												</div>
												<div class="col-md-3">
													<div class="room-card">
														<div class="room-header bg-default">
															<h4>Room 204</h4>
															<p>General Ward</p>
														</div>
														<div class="room-body">
															<p><strong>Patient:</strong> None</p>
															<p><strong>Admitted:</strong> N/A</p>
															<p><strong>Status:</strong> <span class="label label-success">Available</span></p>
															<p><strong>Discharge:</strong> N/A</p>
															<div class="room-actions">
																<button class="btn btn-sm btn-info">View Details</button>
																<button class="btn btn-sm btn-primary">Assign Patient</button>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							
							<div class="row">
								<div class="col-md-6">
									<div class="panel panel-white">
										<div class="panel-heading">
											<h4 class="panel-title">Room Assignment</h4>
										</div>
										<div class="panel-body">
											<form method="post" action="">
												<div class="row">
													<div class="col-md-6">
														<div class="form-group">
															<label>Patient</label>
															<select name="patient_id" class="form-control">
																<option value="">Select Patient</option>
																<option value="1">John Doe</option>
																<option value="2">Jane Smith</option>
																<option value="3">New Patient</option>
															</select>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group">
															<label>Room Type</label>
															<select name="room_type" class="form-control">
																<option value="">Select Room Type</option>
																<option value="consultation">Consultation Room</option>
																<option value="private">Private Room</option>
																<option value="semi_private">Semi-Private Room</option>
																<option value="general">General Ward</option>
																<option value="emergency">Emergency Room</option>
															</select>
														</div>
													</div>
												</div>
												
												<div class="row">
													<div class="col-md-6">
														<div class="form-group">
															<label>Room Number</label>
															<select name="room_number" class="form-control">
																<option value="">Select Available Room</option>
																<option value="204">Room 204 (Available)</option>
																<option value="205">Room 205 (Available)</option>
																<option value="206">Room 206 (Available)</option>
															</select>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group">
															<label>Admission Date</label>
															<input type="date" name="admission_date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
														</div>
													</div>
												</div>
												
												<div class="row">
													<div class="col-md-6">
														<div class="form-group">
															<label>Expected Discharge</label>
															<input type="date" name="discharge_date" class="form-control">
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group">
															<label>Assigned Doctor</label>
															<select name="assigned_doctor" class="form-control">
																<option value="">Select Doctor</option>
																<option value="1">Dr. Abduljalal Auwal</option>
																<option value="2">Dr. Sarah Johnson</option>
																<option value="3">Dr. Michael Chen</option>
															</select>
														</div>
													</div>
												</div>
												
												<div class="row">
													<div class="col-md-12">
														<div class="form-group">
															<label>Notes</label>
															<textarea name="notes" class="form-control" rows="2" placeholder="Any special requirements or notes"></textarea>
														</div>
													</div>
												</div>
												
												<div class="row">
													<div class="col-md-12">
														<button type="submit" name="assign_room" class="btn btn-primary">
															<i class="fa fa-bed"></i> Assign Room
														</button>
														<button type="reset" class="btn btn-default">
															<i class="fa fa-refresh"></i> Reset
														</button>
													</div>
												</div>
											</form>
										</div>
									</div>
								</div>
								
								<div class="col-md-6">
									<div class="panel panel-white">
										<div class="panel-heading">
											<h4 class="panel-title">Room Status Summary</h4>
										</div>
										<div class="panel-body">
											<div class="row">
												<div class="col-md-6">
													<div class="panel panel-white no-radius text-center">
														<div class="panel-body">
															<span class="fa-stack fa-2x"> 
																<i class="fa fa-square fa-stack-2x text-primary"></i> 
																<i class="fa fa-bed fa-stack-1x fa-inverse"></i> 
															</span>
															<h2 class="StepTitle">Total Rooms</h2>
															<p class="text-large">8</p>
														</div>
													</div>
												</div>
												<div class="col-md-6">
													<div class="panel panel-white no-radius text-center">
														<div class="panel-body">
															<span class="fa-stack fa-2x"> 
																<i class="fa fa-square fa-stack-2x text-success"></i> 
																<i class="fa fa-check fa-stack-1x fa-inverse"></i> 
															</span>
															<h2 class="StepTitle">Available</h2>
															<p class="text-large">1</p>
														</div>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="panel panel-white no-radius text-center">
														<div class="panel-body">
															<span class="fa-stack fa-2x"> 
																<i class="fa fa-square fa-stack-2x text-warning"></i> 
																<i class="fa fa-users fa-stack-1x fa-inverse"></i> 
															</span>
															<h2 class="StepTitle">Occupied</h2>
															<p class="text-large">3</p>
														</div>
													</div>
												</div>
												<div class="col-md-6">
													<div class="panel panel-white no-radius text-center">
														<div class="panel-body">
															<span class="fa-stack fa-2x"> 
																<i class="fa fa-square fa-stack-2x text-danger"></i> 
																<i class="fa fa-times fa-stack-1x fa-inverse"></i> 
															</span>
															<h2 class="StepTitle">Maintenance</h2>
															<p class="text-large">1</p>
														</div>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-12">
													<div class="alert alert-info">
														<h4><i class="fa fa-info-circle"></i> Room Status Legend</h4>
														<p><span class="label label-success">Available</span> Room is clean and ready for use</p>
														<p><span class="label label-primary">Occupied</span> Patient currently in room</p>
														<p><span class="label label-warning">Cleaning</span> Room being cleaned/maintained</p>
														<p><span class="label label-danger">Not Available</span> Room out of service</p>
														<p><span class="label label-info">In Use</span> Currently being used for consultation</p>
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
											<h4 class="panel-title">Room Utilization Report</h4>
										</div>
										<div class="panel-body">
											<div class="table-responsive">
												<table class="table table-bordered table-hover">
													<thead>
														<tr>
															<th>Room No</th>
															<th>Room Type</th>
															<th>Current Patient</th>
															<th>Admission Date</th>
															<th>Expected Discharge</th>
															<th>Assigned Doctor</th>
															<th>Status</th>
															<th>Action</th>
														</tr>
													</thead>
													<tbody>
														<tr>
															<td>101</td>
															<td>Consultation Room</td>
															<td>John Doe</td>
															<td>N/A</td>
															<td>N/A</td>
															<td>Dr. Abduljalal Auwal</td>
															<td><span class="label label-info">In Use</span></td>
															<td>
																<button class="btn btn-xs btn-info">View</button>
																<button class="btn btn-xs btn-warning">Update</button>
															</td>
														</tr>
														<tr>
															<td>102</td>
															<td>Consultation Room</td>
															<td>Jane Smith</td>
															<td>N/A</td>
															<td>N/A</td>
															<td>Dr. Sarah Johnson</td>
															<td><span class="label label-info">In Use</span></td>
															<td>
																<button class="btn btn-xs btn-info">View</button>
																<button class="btn btn-xs btn-warning">Update</button>
															</td>
														</tr>
														<tr>
															<td>201</td>
															<td>Private Room</td>
															<td>Robert Johnson</td>
															<td>2024-01-18</td>
															<td>2024-01-25</td>
															<td>Dr. Michael Chen</td>
															<td><span class="label label-primary">Occupied</span></td>
															<td>
																<button class="btn btn-xs btn-info">View</button>
																<button class="btn btn-xs btn-warning">Update</button>
															</td>
														</tr>
														<tr>
															<td>202</td>
															<td>Semi-Private Room</td>
															<td>Maria Garcia</td>
															<td>2024-01-20</td>
															<td>2024-01-22</td>
															<td>Dr. Sarah Johnson</td>
															<td><span class="label label-primary">Occupied</span></td>
															<td>
																<button class="btn btn-xs btn-info">View</button>
																<button class="btn btn-xs btn-warning">Update</button>
															</td>
														</tr>
														<tr>
															<td>203</td>
															<td>General Ward</td>
															<td>David Wilson</td>
															<td>2024-01-21</td>
															<td>2024-01-23</td>
															<td>Dr. Abduljalal Auwal</td>
															<td><span class="label label-primary">Occupied</span></td>
															<td>
																<button class="btn btn-xs btn-info">View</button>
																<button class="btn btn-xs btn-warning">Update</button>
															</td>
														</tr>
														<tr>
															<td>204</td>
															<td>General Ward</td>
															<td>-</td>
															<td>-</td>
															<td>-</td>
															<td>-</td>
															<td><span class="label label-success">Available</span></td>
															<td>
																<button class="btn btn-xs btn-info">View</button>
																<button class="btn btn-xs btn-primary">Assign</button>
															</td>
														</tr>
													</tbody>
												</table>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<!-- end: ROOM MANAGEMENT -->
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