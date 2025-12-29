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

// Fetch user logs
$query = "SELECT ul.*, s.fullname, s.role 
          FROM user_logs ul 
          LEFT JOIN staff s ON ul.user_id = s.id 
          ORDER BY ul.login_time DESC 
          LIMIT 100";
$result = mysqli_query($con, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin | User Logs</title>
     <?php include 'include/css.php';?>
</head>
<body>
    <div id="app">		
        <?php include('include/sidebar.php');?>
        <div class="app-content">
            <?php include('include/header.php');?>						
            <div class="main-content">
                <div class="wrap-content container" id="container">
                    <section id="page-title">
                        <div class="row">
                            <div class="col-sm-8">
                                <h2 class="mainTitle">Admin | User Logs</h2>
                            </div>
                            <ol class="breadcrumb">
                                <li><span>Admin</span></li>
                                <li class="active"><span>User Logs</span></li>
                            </ol>
                        </div>
                    </section>

                    <div class="container-fluid container-fullw bg-white">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="panel panel-white">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">User Activity Logs</h4>
                                    </div>
                                    <div class="panel-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>User Name</th>
                                                        <th>Role</th>
                                                        <th>IP Address</th>
                                                        <th>Login Time</th>
                                                        <th>Logout Time</th>
                                                        <th>Session Duration</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $count = 1;
                                                    if(mysqli_num_rows($result) > 0) {
                                                        while($row = mysqli_fetch_assoc($result)) {
                                                            $login_time = strtotime($row['login_time']);
                                                            $logout_time = $row['logout_time'] ? strtotime($row['logout_time']) : time();
                                                            $duration = $logout_time - $login_time;
                                                            
                                                            // Convert seconds to hours, minutes, seconds
                                                            $hours = floor($duration / 3600);
                                                            $minutes = floor(($duration % 3600) / 60);
                                                            $seconds = $duration % 60;
                                                            $duration_str = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
                                                    ?>
                                                    <tr>
                                                        <td><?php echo $count++; ?></td>
                                                        <td><?php echo htmlentities($row['fullname']); ?></td>
                                                        <td>
                                                            <span class="label label-<?php echo $row['role'] == 'admin' ? 'danger' : ($row['role'] == 'doctor' ? 'warning' : 'info'); ?>">
                                                                <?php echo ucfirst($row['role']); ?>
                                                            </span>
                                                        </td>
                                                        <td><?php echo htmlentities($row['ip_address']); ?></td>
                                                        <td><?php echo date('d-m-Y H:i:s', $login_time); ?></td>
                                                        <td>
                                                            <?php echo $row['logout_time'] ? date('d-m-Y H:i:s', strtotime($row['logout_time'])) : '<span class="label label-warning">Still Active</span>'; ?>
                                                        </td>
                                                        <td><?php echo $duration_str; ?></td>
                                                        <td>
                                                            <?php if($row['logout_time']): ?>
                                                                <span class="label label-success">Logged Out</span>
                                                            <?php else: ?>
                                                                <span class="label label-primary">Active</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                    <?php
                                                        }
                                                    } else {
                                                    ?>
                                                    <tr>
                                                        <td colspan="8" class="text-center">No user logs found.</td>
                                                    </tr>
                                                    <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
        
        <?php include('include/setting.php');?>
    </div>
    <?php include('include/footer.php');?>
    
      <?php include 'include/js.php';?>
</body>
</html>