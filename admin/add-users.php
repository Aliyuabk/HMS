<?php
session_start();
include('include/config.php');

// Only allow Super Admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../index.php");
    exit;
}

if(isset($_POST['submit'])) {
    $role  = $_POST['role'];

    // Common fields
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $phone      = trim($_POST['phone'] ?? '');
    $password   = password_hash($_POST['password'] ?? 'hms123', PASSWORD_DEFAULT);

    // Optional fields
    $gender       = $_POST['gender'] ?? null;
    $dob          = $_POST['dob'] ?? null;
    $address      = $_POST['address'] ?? null;
    $specialization = $_POST['specialization'] ?? null;
    $license_no     = $_POST['license_no'] ?? null;

    // Decide table and fields based on role
    switch($role) {
        case 'reception':
            $table = 'reception';
            $fields = ['first_name','last_name','email','phone','gender','dob','address','password'];
            $values = [$first_name,$last_name,$email,$phone,$gender,$dob,$address,$password];
            break;

        case 'doctor':
            $table = 'doctor';
            $fields = ['first_name','last_name','email','phone','gender','specialization','license_no','password'];
            $values = [$first_name,$last_name,$email,$phone,$gender,$specialization,$license_no,$password];
            break;

        case 'pharmacy':
            $table = 'pharmacy';
            $fields = ['first_name','last_name','email','phone','password'];
            $values = [$first_name,$last_name,$email,$phone,$password];
            break;

        case 'lab':
            $table = 'lab';
            $fields = ['first_name','last_name','email','phone','password'];
            $values = [$first_name,$last_name,$email,$phone,$password];
            break;

		case 'radiology':
            $table = 'radiology';
            $fields = ['first_name','last_name','email','phone','password'];
            $values = [$first_name,$last_name,$email,$phone,$password];
            break;

         case 'billing':
            $table = 'billing';
            $fields = ['first_name','last_name','email','phone','password','role'];
            $values = [$first_name,$last_name,$email,$phone,$password,'Billing'];
            break;

        case 'admin':
            $table = 'admin';
            $fields = ['first_name','last_name','email','phone','password','role'];
            $values = [$first_name,$last_name,$email,$phone,$password,'Admin'];
            break;

        default:
            die("Invalid role selected.");
    }

    // Build placeholders for prepared statement
    $placeholders = implode(',', array_fill(0,count($fields),'?'));
    $fields_str = implode(',', $fields);

    $types = str_repeat('s', count($values)); // all strings
    $stmt = $con->prepare("INSERT INTO $table ($fields_str) VALUES ($placeholders)");
    $stmt->bind_param($types, ...$values);

    if($stmt->execute()){
        echo "<script>alert('New $role user added successfully!');</script>";
        echo "<script>window.location.href='manage-".$role.".php';</script>";
    } else {
        echo "<script>alert('Error adding user: ".$stmt->error."');</script>";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Admin | Add Users</title>
 		<?php include 'include/css.php';?>
			<script type="text/javascript">
				function valid(){
					if(document.adddoc.npass.value!= document.adddoc.cfpass.value)
					{
					alert("Password and Confirm Password Field do not match  !!");
					document.adddoc.cfpass.focus();
					return false;
					}
					return true;
				}
			</script>
			<script>
					function checkemailAvailability() {
						$("#loaderIcon").show();
						jQuery.ajax({
						url: "check_availability.php",
						data:'emailid='+$("#docemail").val(),
						type: "POST",
						success:function(data){
						$("#email-availability-status").html(data);
						$("#loaderIcon").hide();
						},
						error:function (){}
						});
					}
			</script>
	</head>
	<body>
		<div id="app">		
			<?php include('include/sidebar.php');?>
			<div class="app-content">
				<?php include('include/header.php');?>
				<div class="main-content" >
					<div class="wrap-content container" id="container">
						<!-- start: PAGE TITLE -->
						<section id="page-title">
							<div class="row">
								<div class="col-sm-8">
									<h1 class="mainTitle">Admin | Add User</h1>
																	</div>
								<ol class="breadcrumb">
									<li>
										<span>Admin</span>
									</li>
									<li class="active">
										<span>Add Users</span>
									</li>
								</ol>
							</div>
						</section>
						<!-- end: PAGE TITLE -->
						<!-- start: BASIC EXAMPLE -->
						<div class="container-fluid container-fullw bg-white">
							<div class="row">
								<div class="col-md-12">
									
									<div class="row margin-top-30">
										<div class="col-lg-8 col-md-12">
											<div class="panel panel-white">
												<div class="panel-heading">
													<h5 class="panel-title">Add User</h5>
												</div>
												<div class="panel-body">
									
												    <form method="post">
														<div class="form-group">
															<label>User Role</label>
															<select name="role" class="form-control" required>
																<option value="">Select Role</option>
																<option value="reception">Reception</option>
																<option value="doctor">Doctor</option>
																<option value="admin">Admin</option>
																<option value="pharmacy">Pharmacy</option>
																<option value="lab">Lab</option>
																<option value="billing">Billing</option>
																<!-- <option value="radiology">Radiology</option> -->
															</select>
														</div>

														<div class="form-group">
															<label>First Name</label>
															<input type="text" name="first_name" class="form-control" required>
														</div>

														<div class="form-group">
															<label>Last Name</label>
															<input type="text" name="last_name" class="form-control" required>
														</div>

														<div class="form-group">
															<label>Email</label>
															<input type="email" name="email" class="form-control" required>
														</div>

														<div class="form-group">
															<label>Phone</label>
															<input type="text" name="phone" class="form-control">
														</div>

														<div class="form-group">
															<label>Gender (for Doctor/Reception)</label>
															<select name="gender" class="form-control">
																<option value="">Select Gender</option>
																<option value="Male">Male</option>
																<option value="Female">Female</option>
																<option value="Other">Other</option>
															</select>
														</div>

														<div class="form-group">
															<label>Date of Birth (for Reception)</label>
															<input type="date" name="dob" class="form-control">
														</div>

														<div class="form-group">
															<label>Address (for Reception)</label>
															<textarea name="address" class="form-control"></textarea>
														</div>

														<div class="form-group">
															<label>Specialization (for Doctor)</label>
															<input type="text" name="specialization" class="form-control">
														</div>

														<div class="form-group">
															<label>License No (for Doctor)</label>
															<input type="text" name="license_no" class="form-control">
														</div>

														<div class="form-group">
															<label>Password</label>
															<input type="password" name="password" value="hms123" class="form-control" required>
														</div>

														<button type="submit" name="submit" class="btn btn-primary">Add User</button>
													</form>
												</div>
											</div>
										</div>
											
											</div>
										</div>
									<div class="col-lg-12 col-md-12">
											<div class="panel panel-white">
												
												
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
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
	 <?php include 'include/js.php';?>
	</body>
</html>