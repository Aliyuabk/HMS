<?php
session_start();
include('include/config.php');

// Only allow Admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../index.php");
    exit;
}

if(!isset($_GET['role']) || !isset($_GET['id'])){
    die('User Not Found');
}

$role = $_GET['role'];
$id   = intval($_GET['id']);

// Map roles to tables and fields
$role_table_map = [
    'reception' => ['table'=>'reception', 'fields'=>['first_name','last_name','email','phone','gender','dob','address','password']],
    'doctor'    => ['table'=>'doctor', 'fields'=>['first_name','last_name','email','phone','gender','specialization','license_no','password']],
    'pharmacy'  => ['table'=>'pharmacy', 'fields'=>['first_name','last_name','email','phone','password']],
    'lab'       => ['table'=>'lab', 'fields'=>['first_name','last_name','email','phone','password']],
	'radiology' => ['table'=>'radiology', 'fields'=>['first_name','last_name','email','phone','password']],
    'billing'   => ['table'=>'billing', 'fields'=>['first_name','last_name','email','phone','password']],
    'admin'     => ['table'=>'admin', 'fields'=>['first_name','last_name','email','phone','password','role']]
];

if(!array_key_exists($role, $role_table_map)){
    die('Invalid role selected.');
}

$table  = $role_table_map[$role]['table'];
$fields = $role_table_map[$role]['fields'];

// Fetch current user data
$stmt = $con->prepare("SELECT * FROM $table WHERE id=?");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
if($result->num_rows === 0){
    die('User not found.');
}
$user = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if(isset($_POST['submit'])){
    $update_values = [];
    $update_fields = [];

    foreach($fields as $field){
        if($field === 'password'){
            if(!empty($_POST['password'])){
                $update_fields[] = "$field=?";
                $update_values[] = password_hash($_POST['password'], PASSWORD_DEFAULT);
            }
        } elseif($field === 'role' && $role === 'admin') {
            $update_fields[] = "$field=?";
            $update_values[] = $_POST['role'];
        } else {
            $update_fields[] = "$field=?";
            if(in_array($field, ['pharmacist_name','lab_technician_name','cashier_name'])){
                $update_values[] = trim($_POST['first_name'].' '.$_POST['last_name']);
            } else {
                $update_values[] = trim($_POST[$field]);
            }
        }
    }

    $update_values[] = $id; // for WHERE
    $types = str_repeat('s', count($update_values)-1) . 'i';
    $sql = "UPDATE $table SET ".implode(',', $update_fields)." WHERE id=?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param($types, ...$update_values);

    if($stmt->execute()){
        echo "<script>alert('User info updated Successfully');</script>";
        echo "<script>window.location.href='manage-".$role.".php';</script>";
    } else {
        echo "<script>alert('Error: ".$stmt->error."');</script>";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Admin | Edit User</title>
		
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
function checkUsernameAvailability() {
    $("#loaderIcon").show();
    var username = $("#username").val();
    var user_id = "<?php echo $id; ?>";
    
    jQuery.ajax({
        url: "check-username.php",
        data: {username: username, user_id: user_id},
        type: "POST",
        success:function(data){
            $("#username-availability-status").html(data);
            $("#loaderIcon").hide();
        },
        error:function (){
            $("#loaderIcon").hide();
        }
    });
}

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
        error:function (){
            $("#loaderIcon").hide();
        }
    });
}
</script>
	</head>
	<body>
		<div id="app">		
<?php include('include/sidebar.php');?>
			<div class="app-content">
				<?php include('include/header.php');?>
						 
						
				<!-- end: TOP NAVBAR -->
				<div class="main-content" >
					<div class="wrap-content container" id="container">
						<!-- start: PAGE TITLE -->
						<section id="page-title">
							<div class="row">
								<div class="col-sm-8">
									<h1 class="mainTitle">Admin | Edit User</h1>
																	</div>
								<ol class="breadcrumb">
									<li>
										<span>Admin</span>
									</li>
									<li class="active">
										<span>Edit Users</span>
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
													<h5 class="panel-title">Edit User</h5>
												</div>
												<div class="panel-body">
									
											    <h2>Edit <?php echo ucfirst($role); ?> User</h2>
													<form method="post">
														<?php if(in_array($role,['reception','doctor','admin', 'billing', 'pharmacy', 'lab','radiology'])){ ?>
															<div class="form-group">
																<label>First Name</label>
																<input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" class="form-control" required>
															</div>
															<div class="form-group">
																<label>Last Name</label>
																<input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" class="form-control" required>
															</div>
														<?php } ?>

														<div class="form-group">
															<label>Email</label>
															<input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="form-control" required>
														</div>

														<div class="form-group">
															<label>Phone</label>
															<input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" class="form-control">
														</div>

														<?php if($role==='reception' || $role==='doctor'){ ?>
															<div class="form-group">
																<label>Gender</label>
																<select name="gender" class="form-control">
																	<option value="">Select Gender</option>
																	<option value="Male" <?php echo ($user['gender']=='Male')?'selected':''; ?>>Male</option>
																	<option value="Female" <?php echo ($user['gender']=='Female')?'selected':''; ?>>Female</option>
																	<option value="Other" <?php echo ($user['gender']=='Other')?'selected':''; ?>>Other</option>
																</select>
															</div>
														<?php } ?>

														<?php if($role==='reception'){ ?>
															<div class="form-group">
																<label>Date of Birth</label>
																<input type="date" name="dob" value="<?php echo $user['dob']; ?>" class="form-control">
															</div>
															<div class="form-group">
																<label>Address</label>
																<textarea name="address" class="form-control"><?php echo $user['address']; ?></textarea>
															</div>
														<?php } ?>

														<?php if($role==='doctor'){ ?>
															<div class="form-group">
																<label>Specialization</label>
																<input type="text" name="specialization" value="<?php echo $user['specialization']; ?>" class="form-control">
															</div>
															<div class="form-group">
																<label>License No</label>
																<input type="text" name="license_no" value="<?php echo $user['license_no']; ?>" class="form-control">
															</div>
														<?php } ?>

														<?php if($role==='admin'){ ?>
															<div class="form-group">
																<label>Role</label>
																<select name="role" class="form-control">
																	<option value="Admin" <?php echo ($user['role']=='Admin')?'selected':''; ?>>Admin</option>
																	<option value="Super Admin" <?php echo ($user['role']=='Super Admin')?'selected':''; ?>>Super Admin</option>
																</select>
															</div>
														<?php } ?>

														<div class="form-group">
															<label>Password</label>
															<input type="password" name="password" class="form-control" placeholder="Leave blank to keep current password">
															<small class="text-muted">Leave empty to keep current password</small>
														</div>

														<button type="submit" name="submit" class="btn btn-primary">Update User</button>
														<a href="manage-<?php echo $role; ?>.php" class="btn btn-secondary">Cancel</a>
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
						<!-- end: BASIC EXAMPLE -->
			
					
					
						
						
					
						<!-- end: SELECT BOXES -->
						
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