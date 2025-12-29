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

// Handle query status update
if(isset($_GET['mark_read'])) {
    $query_id = $_GET['mark_read'];
    mysqli_query($con, "UPDATE contact_queries SET status='read' WHERE id='$query_id'");
    header("Location: unread-queries.php");
    exit;
}

// Fetch unread queries
$query = "SELECT * FROM contact_queries WHERE status='unread' ORDER BY created_at DESC";
$result = mysqli_query($con, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin | Unread Queries</title>
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
                                <h2 class="mainTitle">Admin | Unread Queries</h2>
                            </div>
                            <ol class="breadcrumb">
                                <li><span>Admin</span></li>
                                <li class="active"><span>Unread Queries</span></li>
                            </ol>
                        </div>
                    </section>

                    <div class="container-fluid container-fullw bg-white">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="panel panel-white">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">Unread Contact Queries</h4>
                                        <div class="panel-tools">
                                            <a href="all-queries.php" class="btn btn-primary">
                                                <i class="fa fa-list"></i> View All Queries
                                            </a>
                                        </div>
                                    </div>
                                    <div class="panel-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Name</th>
                                                        <th>Email</th>
                                                        <th>Phone</th>
                                                        <th>Subject</th>
                                                        <th>Message</th>
                                                        <th>Received On</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $count = 1;
                                                    if(mysqli_num_rows($result) > 0) {
                                                        while($row = mysqli_fetch_assoc($result)) {
                                                    ?>
                                                    <tr>
                                                        <td><?php echo $count++; ?></td>
                                                        <td><?php echo htmlentities($row['name']); ?></td>
                                                        <td><?php echo htmlentities($row['email']); ?></td>
                                                        <td><?php echo htmlentities($row['phone']); ?></td>
                                                        <td><?php echo htmlentities($row['subject']); ?></td>
                                                        <td>
                                                            <?php 
                                                            $message = htmlentities($row['message']);
                                                            if(strlen($message) > 100) {
                                                                echo substr($message, 0, 100) . '...';
                                                            } else {
                                                                echo $message;
                                                            }
                                                            ?>
                                                        </td>
                                                        <td><?php echo date('d-m-Y H:i', strtotime($row['created_at'])); ?></td>
                                                        <td>
                                                            <a href="view-query.php?id=<?php echo $row['id']; ?>" class="btn btn-info btn-xs">
                                                                <i class="fa fa-eye"></i> View
                                                            </a>
                                                            <a href="unread-queries.php?mark_read=<?php echo $row['id']; ?>" 
                                                               class="btn btn-success btn-xs">
                                                                <i class="fa fa-check"></i> Mark as Read
                                                            </a>
                                                            <a href="reply-query.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-xs">
                                                                <i class="fa fa-reply"></i> Reply
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    <?php
                                                        }
                                                    } else {
                                                    ?>
                                                    <tr>
                                                        <td colspan="8" class="text-center">No unread queries found.</td>
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
        <?php include('include/footer.php');?>
        <?php include('include/setting.php');?>
    </div>
    
     <?php include 'include/js.php';?>
</body>
</html>