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
                                                    

if(isset($_GET['del']))
		  {
		  	$uid=$_GET['id'];
		          mysqli_query($con,"delete from staff where id ='$uid'");
                  $_SESSION['msg']="data deleted !!";
		  }
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Admin | Manage Users</title>
		
		   <?php include 'include/css.php';?>
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
									<h1 class="mainTitle">Admin | Manage Users</h1>
																	</div>
								<ol class="breadcrumb">
									<li>
										<span>Admin</span>
									</li>
									<li class="active">
										<span>Manage Users</span>
									</li>
								</ol>
							</div>
						</section>
						<!-- end: PAGE TITLE -->
						<!-- start: BASIC EXAMPLE -->
						<div class="container-fluid container-fullw bg-white">
						

									<div class="row">
								<div class="col-md-12">
									<h5 class="over-title margin-bottom-15">Manage <span class="text-bold">Users</span></h5>
									<p style="color:red;"><?php echo htmlentities($_SESSION['msg']);?>
								<?php echo htmlentities($_SESSION['msg']="");?></p>	
									<table class="table table-bordered table-hover">
										<thead>
											<tr>
												<th class="center">#</th>
												<th>Full Name</th>
												<th class="hidden-xs">Username</th>
												<th>Phone Number</th>
												<th>Email</th>
												<th>Role</th>
												<th>dept</th>
												<th>Creation Date </th>
												<th>Action</th>
												
											</tr>
										</thead>
										<tbody>
											<?php
											$sql=mysqli_query($con,"select * from staff");
											$cnt=1;
											while($row=mysqli_fetch_array($sql))
											{
											?>
											<tr>
												<td class="center"><?php echo $cnt;?>.</td>
												<td class="hidden-xs"><?php echo $row['full_name'];?></td>
												<td><?php echo $row['username'];?></td>
												<td><?php echo $row['phone_number'];?></td>
												<td><?php echo $row['email'];?></td>
												<td><?php echo $row['role'];?></td>
												<td><?php echo $row['dept'];?></td>
												<td><?php echo $row['created_at'];?></td><td >
												<div class="visible-md visible-lg hidden-sm hidden-xs">
													<a href="manage-users.php?id=<?php echo $row['id']?>&del=delete" onClick="return confirm('Are you sure you want to delete?')"class="btn btn-transparent btn-xs tooltips" tooltip-placement="top" tooltip="Remove"><i class="fa fa-times fa fa-white"></i></a>
												</div>
												<div class="visible-md visible-lg hidden-sm hidden-xs">
													<a href="edit-user.php?id=<?php echo $row['id']?>" class="btn btn-transparent btn-xs tooltips" tooltip-placement="top" ><i class="fa fa-edit fa fa-white"></i></a>
												</div>
												<div class="visible-md visible-lg hidden-sm hidden-xs">
													<a href="view-user.php?id=<?php echo $row['id']?>" class="btn btn-transparent btn-xs tooltips" tooltip-placement="top" ><i class="fa fa-eye fa fa-white"></i></a>
												</div>
												</td>
											</tr>
											
											<?php 
												$cnt=$cnt+1;
											 }?>
											
											
										</tbody>
									</table>
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