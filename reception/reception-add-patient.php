<?php
	session_start();
	 include('include/config.php');



function generateEHR($con) {
    do {
        $ehr = strval(rand(100000, 999999));
        $check = $con->prepare("SELECT id FROM patients WHERE ehr_no = ?");
        $check->bind_param("s", $ehr);
        $check->execute();
        $check->store_result();
    } while ($check->num_rows > 0);

    return $ehr;
}

if(isset($_POST['add_patient'])) {

    $ehr_no = generateEHR($con);

    $sql = "INSERT INTO patients (
        ehr_no, first_name, last_name, dob, gender, blood_group, marital_status,
        email, phone, address, emergency_contact,
        next_of_kin_name, next_of_kin_phone, next_of_kin_address, next_of_kin_city
    ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

    $stmt = $con->prepare($sql);

    $stmt->bind_param(
        "sssssssssssssss",
        $ehr_no,
        $_POST['first_name'],
        $_POST['last_name'],
        $_POST['dob'],
        $_POST['gender'],
        $_POST['blood_group'],
        $_POST['marital_status'],
        $_POST['email'],
        $_POST['phone'],
        $_POST['address'],
        $_POST['emergency_contact'],
        $_POST['next_of_kin_name'],
        $_POST['next_of_kin_phone'],
        $_POST['next_of_kin_address'],
        $_POST['next_of_kin_city']
    );

    if($stmt->execute()){
        $_SESSION['success'] = "Patient registered successfully. EHR No: $ehr_no";
    } else {
        $_SESSION['error'] = "Error: " . $stmt->error;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Reception | Add Patient</title>
		
			 <?php include('include/css.php'); ?>
	</head>
	<body>
		<div id="app">		
			<!-- SIDEBAR -->
		<?php  include('include/sidebar.php');?>
			<!-- SIDEBAR -->
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
									<h2 class="mainTitle">Reception | Add Patient</h2>
								</div>
								<ol class="breadcrumb">
									<li>
										<span>Reception</span>
									</li>
									<li class="active">
										<span>Add Patient </span>
									</li>
								</ol>
							</div>
						</section>
						<!-- end: PAGE TITLE -->
						
						<!-- start: ADD PATIENT FORM -->
						<div class="container-fluid container-fullw bg-white">
							<div class="row">
								<div class="col-md-12">
									<div class="panel panel-white">
										<div class="panel-heading">
											<h4 class="panel-title">Add New Patient</h4>
										</div>
										<?php if(isset($_SESSION['success'])) { ?>
										<div class="alert alert-success">
											<?= $_SESSION['success']; unset($_SESSION['success']); ?>
										</div>
										<?php } ?>

										<?php if(isset($_SESSION['error'])) { ?>
											<div class="alert alert-danger">
												<?= $_SESSION['error']; unset($_SESSION['error']); ?>
											</div>
										<?php } ?>
										<div class="panel-body">
											<form method="post" action="">
												<h4>Personal Information</h4>
												<div class="row">
													<div class="col-md-4">
														<div class="form-group">
															<label>First Name <span class="text-danger">*</span></label>
															<input type="text" name="first_name" class="form-control" required>
														</div>
													</div>
													<div class="col-md-4">
														<div class="form-group">
															<label>Last Name <span class="text-danger">*</span></label>
															<input type="text" name="last_name" class="form-control" required>
														</div>
													</div>
													<div class="col-md-4">
														<div class="form-group">
															<label>Date of Birth <span class="text-danger">*</span></label>
															<input type="date" name="dob" class="form-control" required>
														</div>
													</div>
												</div>
												
												<div class="row">
													<div class="col-md-4">
														<div class="form-group">
															<label>Gender <span class="text-danger">*</span></label>
															<select name="gender" class="form-control" required>
																<option value="">Select Gender</option>
																<option value="Male">Male</option>
																<option value="Female">Female</option>
																<option value="Other">Other</option>
															</select>
														</div>
													</div>
													<div class="col-md-4">
														<div class="form-group">
															<label>Blood Group</label>
															<select name="blood_group" class="form-control">
																<option value="">Select Blood Group</option>
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
													<div class="col-md-4">
														<div class="form-group">
															<label>Marital Status</label>
															<select name="marital_status" class="form-control">
																<option value="">Select Status</option>
																<option value="Single">Single</option>
																<option value="Married">Married</option>
																<option value="Divorced">Divorced</option>
																<option value="Widowed">Widowed</option>
															</select>
														</div>
													</div>
												</div>
												
												<h4>Contact Information</h4>
												<div class="row">
													<div class="col-md-6">
														<div class="form-group">
															<label>Email Address</label>
															<input type="email" name="email" class="form-control">
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group">
															<label>Phone Number <span class="text-danger">*</span></label>
															<input type="text" name="phone" class="form-control" required>
														</div>
													</div>
												</div>
												
												<div class="row">
													<div class="col-md-6">
														<div class="form-group">
															<label>Address</label>
															<textarea name="address" class="form-control" rows="3"></textarea>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group">
															<label>City</label>
															<input type="text" name="city" class="form-control">
														</div>
														<div class="form-group">
															<label>Emergency Contact</label>
															<input type="text" name="emergency_contact" class="form-control">
														</div>
													</div>
												</div>
												
												<h4>Next of King *</h4>
												<div class="row">
													<div class="col-md-6">
														<div class="form-group">
															<label>Next of King Name</label>
															<input type="text" name="next_of_kin_name" class="form-control">
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group">
															<label>Phone Number</label>
															<input type="text" name="next_of_kin_phone" class="form-control">
														</div>
													</div>
												</div>
												
												<div class="row">
													<div class="col-md-6">
														<div class="form-group">
															<label>Address </label>
															<input type="text" name="next_of_kin_address" class="form-control">
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group">
															<label>City</label>
															<input type="text" name="next_of_kin_city" class="form-control">
														</div>
													</div>
												</div>																							
												<div class="row">
													<div class="col-md-12">
														<button type="submit" name="add_patient" class="btn btn-primary">
															<i class="fa fa-user-plus"></i> Add Patient
														</button>
														<button type="reset" class="btn btn-default">
															<i class="fa fa-refresh"></i> Reset
														</button>
														<a href="reception-patients.php" class="btn btn-warning">
															<i class="fa fa-arrow-left"></i> Back to Patients List
														</a>
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
											<h4 class="panel-title">Quick Patient Registration</h4>
										</div>
										<div class="panel-body">
											<div class="alert alert-info">
												<h4><i class="fa fa-info-circle"></i> Important Notes</h4>
												<ol>
													<li>All fields marked with <span class="text-danger">*</span> are required</li>
													<li>MRN (Medical Record Number) will be generated automatically</li>
													<li>After registration, you can immediately book an appointment for the patient</li>
													<li>Ensure emergency contact information is accurate</li>
													<li>Verify insurance information if applicable</li>
												</ol>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<!-- end: ADD PATIENT FORM -->
					</div>
				</div>
			</div>
			
			<!-- start: FOOTER -->
			<?php include('include/footer.php'); ?>
			<!-- end: FOOTER -->
		
			<!-- start: SETTINGS -->
			<?php include('include/setting.php');?>	
			<!-- end: SETTINGS -->
		</div>
		
		<?php include('include/js.php'); ?>
	</body>
</html>