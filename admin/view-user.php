<?php
session_start();
include('include/config.php');
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../index.php");
    exit;
}
 $user_id = $_SESSION['user_id'];
  $sql=mysqli_query($con,"select * from staff where id = '$user_id'");
    $data=mysqli_fetch_array($sql);
                                                    

if(!isset($_GET['id'])){
    die('User Not Found');
}
$id = $_GET['id'];

?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Admin | View Users</title>
		
		 <?php include 'include/css.php';?>
<script type="text/javascript">
function valid()
{
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
						 
						
				<!-- end: TOP NAVBAR -->
				<div class="main-content" >
					<div class="wrap-content container" id="container">
						<!-- start: PAGE TITLE -->
						<section id="page-title">
							<div class="row">
								<div class="col-sm-8">
									<h1 class="mainTitle">Admin | View User</h1>
																	</div>
								<ol class="breadcrumb">
									<li>
										<span>Admin</span>
									</li>
									<li class="active">
										<span>View Users</span>
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
													<h5 class="panel-title">View User</h5>
												</div>
												<div class="panel-body">
									
													<form role="form" name="adddoc" method="post" onSubmit="return valid();">
													<?php
                                                    $sql=mysqli_query($con,"select * from staff where id = '$id'");
                                                    $cnt=1;
                                                    while($row=mysqli_fetch_array($sql))
                                                    {
                                                    ?>
														<div class="form-group">
															<label for="doctorname">
																 View  Full Name
															</label>
															<input type="text" name="full_name" value="<?php echo $row['full_name'];?>" class="form-control"  placeholder="Enter User Full Name" required="true"disabled>
														</div>
														<div class="form-group">
															<label for="doctorname">
																View User Name
															</label>
															<input type="text" name="username" class="form-control" value="<?php echo $row['username'];?>"  placeholder="Enter User Name" required="true" disabled>
														</div>
														<div class="form-group">
															<label for="address">
																View Phone Number
															</label>
															<input type=text name="phone_number" class="form-control" value="<?php echo $row['phone_number'];?>" placeholder="Enter User Phone Number" required="true" disabled>														</div>
														 
														<div class="form-group">
															<label for="fess">
																View Email Address
															</label>
															<input type="email" name="email" class="form-control" value="<?php echo $row['email'];?>" placeholder="Enter User Email address" required="true" disabled>
														</div>
                                                        	<div class="form-group">
															<label for="fess">
																View Role 
															</label>
															<input type="email" name="email" class="form-control" value="<?php echo $row['role'];?>" placeholder="Enter User Email address" required="true" disabled>
														</div>
													
														<div class="form-group">
															<label for="exampleInputPassword1">
																 View Department
															</label>
															<input type="text" name="dept"  class="form-control" value="<?php echo $row['dept'];?>" placeholder="Enter User Department Password" required="required"disabled>
														</div>
														 <a href="edit-user.php?id=<?php echo $row['id']?>" class="btn btn-o btn-success"> Edit <i class="fa fa-edit"></i> </a>
                                                            <?php 
                                                                $cnt=$cnt+1;
                                                            }?>
														
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