<?php
session_start();
require_once('include/config.php');

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'reception'){
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$sql = mysqli_query($con, "SELECT * FROM reception WHERE id = '$user_id'");
$user_data = mysqli_fetch_array($sql);

// Handle form submission for profile update
if(isset($_POST['update_profile'])) {
    $first_name = mysqli_real_escape_string($con, $_POST['first_name']);
	$last_name = mysqli_real_escape_string($con, $_POST['last_name']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $phone = mysqli_real_escape_string($con, $_POST['phone']);
    
    $update_query = "UPDATE reception SET first_name = '$first_name', last_name = '$last_name', email = '$email', phone = '$phone' WHERE id = '$user_id'";
    
    if(mysqli_query($con, $update_query)) {
        echo "<script>alert('Profile updated successfully!');</script>";
        echo "<script>window.location.href='profile.php';</script>";
    } else {
        echo "<script>alert('Error updating profile: " . mysqli_error($con) . "');</script>";
    }
}

// Handle password change
if(isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Verify current password
    if(password_verify($current_password, $user_data['password'])) {
        if($new_password === $confirm_password) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $password_query = "UPDATE reception SET password = '$hashed_password' WHERE id = '$user_id'";
            
            if(mysqli_query($con, $password_query)) {
                echo "<script>alert('Password changed successfully!');</script>";
                echo "<script>window.location.href='profile.php';</script>";
            } else {
                echo "<script>alert('Error changing password: " . mysqli_error($con) . "');</script>";
            }
        } else {
            echo "<script>alert('New passwords do not match!');</script>";
        }
    } else {
        echo "<script>alert('Current password is incorrect!');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Reception | Profile Settings</title>
		
		<?php include 'include/css.php'; ?>
	</head>
	<body>
		<div id="app">		
			<?php include 'include/sidebar.php'; ?>
			
			<div class="app-content">
				<?php include 'include/header.php'; ?>
						
				<!-- end: TOP NAVBAR -->
				<div class="main-content" >
					<div class="wrap-content container" id="container">
						<!-- start: PAGE TITLE -->
						<section id="page-title">
							<div class="row">
								<div class="col-sm-8">
									<h2 class="mainTitle">Receptionist | Profile Settings</h2>
																	</div>
								<ol class="breadcrumb">
									<li>
										<span>Receptionist</span>
									</li>
									<li class="active">
										<span>Profile Settings</span>
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
										<div class="col-lg-6 col-md-12">
											<div class="panel panel-white">
												<div class="panel-heading">
													<h5 class="panel-title">Update Profile Information</h5>
												</div>
												<div class="panel-body">
													<form role="form" method="post">
														<div class="form-group">
															<label>First Name</label>
															<input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($user_data['first_name']); ?>" required>
														</div>
														<div class="form-group">
															<label>Last Name</label>
															<input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($user_data['last_name']); ?>">
															<small class="text-muted">Username cannot be changed</small>
														</div>
														<div class="form-group">
															<label>Email Address</label>
															<input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
														</div>
														<div class="form-group">
															<label>Phone Number</label>
															<input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($user_data['phone']); ?>">
														</div>
														<button type="submit" name="update_profile" class="btn btn-primary">
															Update Profile
														</button>
													</form>
												</div>
											</div>
										</div>
											
										<div class="col-lg-6 col-md-12">
											<div class="panel panel-white">
												<div class="panel-heading">
													<h5 class="panel-title">Change Password</h5>
												</div>
												<div class="panel-body">
													<form role="form" method="post">
														<div class="form-group">
															<label>Current Password</label>
															<input type="password" name="current_password" class="form-control" required>
														</div>
														<div class="form-group">
															<label>New Password</label>
															<input type="password" name="new_password" class="form-control" required>
														</div>
														<div class="form-group">
															<label>Confirm New Password</label>
															<input type="password" name="confirm_password" class="form-control" required>
														</div>
														<button type="submit" name="change_password" class="btn btn-primary">
															Change Password
														</button>
													</form>
												</div>
											</div>
											
											<div class="panel panel-white">
												<div class="panel-heading">
													<h5 class="panel-title">Account Information</h5>
												</div>
												<div class="panel-body">
													<table class="table table-bordered">
														<tr>
															<th>Account Created:</th>
															<td><?php echo date('M d, Y', strtotime($user_data['created_at'])); ?></td>
														</tr>
														<tr>
															<th>Last Login:</th>
															<td><?php echo date('M d, Y H:i:s'); ?></td>
														</tr>
														<tr>
															<th>Account Status:</th>
															<td><span class="label label-success">Active</span></td>
														</tr>
													</table>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<!-- end: BASIC EXAMPLE -->
					</div>
				</div>
			</div>
			<!-- start: FOOTER -->
			<?php include 'include/footer.php'; ?>		<!-- end: FOOTER -->
		
			<!-- start: SETTINGS -->
			<?php include 'include/setting.php'; ?>	
			<!-- end: SETTINGS -->
		</div>
		<?php include 'include/js.php'; ?>
	</body>
</html>