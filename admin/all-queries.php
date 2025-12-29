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
if(isset($_GET['mark_status'])) {
    $query_id = $_GET['mark_status'];
    $status = $_GET['status'];
    mysqli_query($con, "UPDATE contact_queries SET status='$status' WHERE id='$query_id'");
    header("Location: all-queries.php");
    exit;
}

// Handle query deletion
if(isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    mysqli_query($con, "DELETE FROM contact_queries WHERE id='$delete_id'");
    header("Location: all-queries.php");
    exit;
}

// Search functionality
$search = '';
$status_filter = isset($_GET['status_filter']) ? $_GET['status_filter'] : '';

$where_clause = "1=1";
if(isset($_GET['search'])) {
    $search = $_GET['search'];
    $where_clause .= " AND (name LIKE '%$search%' OR 
                           email LIKE '%$search%' OR 
                           phone LIKE '%$search%' OR 
                           subject LIKE '%$search%')";
}

if($status_filter) {
    $where_clause .= " AND status='$status_filter'";
}

// Pagination
$per_page = 20;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * $per_page;

// Get total queries
$total_query = "SELECT COUNT(*) as total FROM contact_queries WHERE $where_clause";
$total_result = mysqli_query($con, $total_query);
$total_row = mysqli_fetch_assoc($total_result);
$total_queries = $total_row['total'];
$total_pages = ceil($total_queries / $per_page);

// Fetch queries with pagination
$query = "SELECT * FROM contact_queries 
          WHERE $where_clause 
          ORDER BY created_at DESC 
          LIMIT $offset, $per_page";
$result = mysqli_query($con, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin | All Queries</title>
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
                                <h2 class="mainTitle">Admin | All Queries</h2>
                            </div>
                            <ol class="breadcrumb">
                                <li><span>Admin</span></li>
                                <li class="active"><span>All Queries</span></li>
                            </ol>
                        </div>
                    </section>

                    <div class="container-fluid container-fullw bg-white">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="panel panel-white">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">All Contact Queries</h4>
                                        <div class="panel-tools">
                                            <a href="unread-queries.php" class="btn btn-warning">
                                                <i class="fa fa-envelope"></i> View Unread Queries
                                            </a>
                                        </div>
                                    </div>
                                    <div class="panel-body">
                                        <form method="GET" action="" class="form-inline" style="margin-bottom: 20px;">
                                            <div class="form-group">
                                                <input type="text" name="search" class="form-control" placeholder="Search by name, email, phone or subject" value="<?php echo htmlentities($search); ?>" style="width: 250px;">
                                            </div>
                                            <div class="form-group">
                                                <select name="status_filter" class="form-control" style="width: 150px;">
                                                    <option value="">All Status</option>
                                                    <option value="unread" <?php echo $status_filter == 'unread' ? 'selected' : ''; ?>>Unread</option>
                                                    <option value="read" <?php echo $status_filter == 'read' ? 'selected' : ''; ?>>Read</option>
                                                    <option value="replied" <?php echo $status_filter == 'replied' ? 'selected' : ''; ?>>Replied</option>
                                                </select>
                                            </div>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fa fa-search"></i> Search
                                            </button>
                                            <?php if($search || $status_filter): ?>
                                            <a href="all-queries.php" class="btn btn-default">Clear Filters</a>
                                            <?php endif; ?>
                                        </form>

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
                                                        <th>Status</th>
                                                        <th>Received On</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $count = $offset + 1;
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
                                                            <button type="button" class="btn btn-link btn-xs" data-toggle="modal" data-target="#messageModal<?php echo $row['id']; ?>">
                                                                View Message
                                                            </button>
                                                            <!-- Message Modal -->
                                                            <div class="modal fade" id="messageModal<?php echo $row['id']; ?>" tabindex="-1" role="dialog">
                                                                <div class="modal-dialog modal-lg" role="document">
                                                                    <div class="modal-content">
                                                                        <div class="modal-header">
                                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                                <span aria-hidden="true">&times;</span>
                                                                            </button>
                                                                            <h4 class="modal-title">Message from <?php echo htmlentities($row['name']); ?></h4>
                                                                        </div>
                                                                        <div class="modal-body">
                                                                            <p><strong>Subject:</strong> <?php echo htmlentities($row['subject']); ?></p>
                                                                            <p><strong>Email:</strong> <?php echo htmlentities($row['email']); ?></p>
                                                                            <p><strong>Phone:</strong> <?php echo htmlentities($row['phone']); ?></p>
                                                                            <p><strong>Received:</strong> <?php echo date('d-m-Y H:i', strtotime($row['created_at'])); ?></p>
                                                                            <hr>
                                                                            <p><?php echo nl2br(htmlentities($row['message'])); ?></p>
                                                                        </div>
                                                                        <div class="modal-footer">
                                                                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                                                            <a href="reply-query.php?id=<?php echo $row['id']; ?>" class="btn btn-primary">
                                                                                <i class="fa fa-reply"></i> Reply
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span class="label label-<?php 
                                                                echo $row['status'] == 'unread' ? 'danger' : 
                                                                ($row['status'] == 'replied' ? 'success' : 'info');
                                                            ?>">
                                                                <?php echo ucfirst($row['status']); ?>
                                                            </span>
                                                        </td>
                                                        <td><?php echo date('d-m-Y H:i', strtotime($row['created_at'])); ?></td>
                                                        <td>
                                                            <div class="btn-group">
                                                                <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown">
                                                                    Actions <span class="caret"></span>
                                                                </button>
                                                                <ul class="dropdown-menu" role="menu">
                                                                    <li>
                                                                        <a href="#" data-toggle="modal" data-target="#messageModal<?php echo $row['id']; ?>">
                                                                            <i class="fa fa-eye"></i> View Details
                                                                        </a>
                                                                    </li>
                                                                    <li>
                                                                        <a href="reply-query.php?id=<?php echo $row['id']; ?>">
                                                                            <i class="fa fa-reply"></i> Reply
                                                                        </a>
                                                                    </li>
                                                                    <li class="divider"></li>
                                                                    <li>
                                                                        <a href="all-queries.php?mark_status=<?php echo $row['id']; ?>&status=read">
                                                                            <i class="fa fa-envelope-open"></i> Mark as Read
                                                                        </a>
                                                                    </li>
                                                                    <li>
                                                                        <a href="all-queries.php?mark_status=<?php echo $row['id']; ?>&status=replied">
                                                                            <i class="fa fa-check"></i> Mark as Replied
                                                                        </a>
                                                                    </li>
                                                                    <li>
                                                                        <a href="all-queries.php?mark_status=<?php echo $row['id']; ?>&status=unread">
                                                                            <i class="fa fa-envelope"></i> Mark as Unread
                                                                        </a>
                                                                    </li>
                                                                    <li class="divider"></li>
                                                                    <li>
                                                                        <a href="all-queries.php?delete_id=<?php echo $row['id']; ?>" 
                                                                           onclick="return confirm('Are you sure you want to delete this query?');">
                                                                            <i class="fa fa-trash text-danger"></i> Delete
                                                                        </a>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <?php
                                                        }
                                                    } else {
                                                    ?>
                                                    <tr>
                                                        <td colspan="9" class="text-center">No queries found.</td>
                                                    </tr>
                                                    <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        
                                        <!-- Pagination -->
                                        <?php if($total_pages > 1): ?>
                                        <div class="text-center">
                                            <ul class="pagination">
                                                <?php if($page > 1): ?>
                                                <li>
                                                    <a href="all-queries.php?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>&status_filter=<?php echo $status_filter; ?>">
                                                        &laquo; Previous
                                                    </a>
                                                </li>
                                                <?php endif; ?>
                                                
                                                <?php 
                                                $start = max(1, $page - 2);
                                                $end = min($total_pages, $page + 2);
                                                
                                                for($i = $start; $i <= $end; $i++):
                                                ?>
                                                <li class="<?php echo $i == $page ? 'active' : ''; ?>">
                                                    <a href="all-queries.php?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status_filter=<?php echo $status_filter; ?>">
                                                        <?php echo $i; ?>
                                                    </a>
                                                </li>
                                                <?php endfor; ?>
                                                
                                                <?php if($page < $total_pages): ?>
                                                <li>
                                                    <a href="all-queries.php?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>&status_filter=<?php echo $status_filter; ?>">
                                                        Next &raquo;
                                                    </a>
                                                </li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Query Statistics -->
                        <div class="row">
                            <div class="col-md-3 col-sm-6">
                                <div class="panel panel-white no-radius text-center">
                                    <div class="panel-body">
                                        <?php 
                                        $total_queries = mysqli_query($con, "SELECT COUNT(*) as total FROM contact_queries");
                                        $total = mysqli_fetch_assoc($total_queries);
                                        ?>
                                        <span class="fa-stack fa-2x"> 
                                            <i class="fa fa-square fa-stack-2x text-primary"></i> 
                                            <i class="fa fa-envelope fa-stack-1x fa-inverse"></i> 
                                        </span>
                                        <h2 class="StepTitle">Total Queries</h2>
                                        <p class="text-large"><?php echo $total['total']; ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="panel panel-white no-radius text-center">
                                    <div class="panel-body">
                                        <?php 
                                        $unread_queries = mysqli_query($con, "SELECT COUNT(*) as total FROM contact_queries WHERE status='unread'");
                                        $unread = mysqli_fetch_assoc($unread_queries);
                                        ?>
                                        <span class="fa-stack fa-2x"> 
                                            <i class="fa fa-square fa-stack-2x text-danger"></i> 
                                            <i class="fa fa-envelope-o fa-stack-1x fa-inverse"></i> 
                                        </span>
                                        <h2 class="StepTitle">Unread Queries</h2>
                                        <p class="text-large"><?php echo $unread['total']; ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="panel panel-white no-radius text-center">
                                    <div class="panel-body">
                                        <?php 
                                        $read_queries = mysqli_query($con, "SELECT COUNT(*) as total FROM contact_queries WHERE status='read'");
                                        $read = mysqli_fetch_assoc($read_queries);
                                        ?>
                                        <span class="fa-stack fa-2x"> 
                                            <i class="fa fa-square fa-stack-2x text-info"></i> 
                                            <i class="fa fa-envelope-open-o fa-stack-1x fa-inverse"></i> 
                                        </span>
                                        <h2 class="StepTitle">Read Queries</h2>
                                        <p class="text-large"><?php echo $read['total']; ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="panel panel-white no-radius text-center">
                                    <div class="panel-body">
                                        <?php 
                                        $replied_queries = mysqli_query($con, "SELECT COUNT(*) as total FROM contact_queries WHERE status='replied'");
                                        $replied = mysqli_fetch_assoc($replied_queries);
                                        ?>
                                        <span class="fa-stack fa-2x"> 
                                            <i class="fa fa-square fa-stack-2x text-success"></i> 
                                            <i class="fa fa-reply fa-stack-1x fa-inverse"></i> 
                                        </span>
                                        <h2 class="StepTitle">Replied Queries</h2>
                                        <p class="text-large"><?php echo $replied['total']; ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Recent Activity -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="panel panel-white">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">Recent Queries (Last 7 Days)</h4>
                                    </div>
                                    <div class="panel-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>Unread</th>
                                                        <th>Read</th>
                                                        <th>Replied</th>
                                                        <th>Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    for($i = 6; $i >= 0; $i--) {
                                                        $date = date('Y-m-d', strtotime("-$i days"));
                                                        $day_name = date('D', strtotime($date));
                                                        
                                                        $day_query = "SELECT 
                                                                        SUM(CASE WHEN status='unread' THEN 1 ELSE 0 END) as unread,
                                                                        SUM(CASE WHEN status='read' THEN 1 ELSE 0 END) as read_count,
                                                                        SUM(CASE WHEN status='replied' THEN 1 ELSE 0 END) as replied,
                                                                        COUNT(*) as total
                                                                     FROM contact_queries 
                                                                     WHERE DATE(created_at) = '$date'";
                                                        $day_result = mysqli_query($con, $day_query);
                                                        $day_stats = mysqli_fetch_assoc($day_result);
                                                    ?>
                                                    <tr>
                                                        <td><?php echo $day_name . ', ' . date('d M', strtotime($date)); ?></td>
                                                        <td class="text-danger"><?php echo $day_stats['unread']; ?></td>
                                                        <td class="text-info"><?php echo $day_stats['read_count']; ?></td>
                                                        <td class="text-success"><?php echo $day_stats['replied']; ?></td>
                                                        <td><strong><?php echo $day_stats['total']; ?></strong></td>
                                                    </tr>
                                                    <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="panel panel-white">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">Query Sources</h4>
                                    </div>
                                    <div class="panel-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Subject Category</th>
                                                        <th>Count</th>
                                                        <th>Percentage</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $subject_query = "SELECT 
                                                                        CASE 
                                                                            WHEN subject LIKE '%appointment%' THEN 'Appointment'
                                                                            WHEN subject LIKE '%emergency%' OR subject LIKE '%urgent%' THEN 'Emergency'
                                                                            WHEN subject LIKE '%bill%' OR subject LIKE '%payment%' THEN 'Billing'
                                                                            WHEN subject LIKE '%doctor%' THEN 'Doctor Inquiry'
                                                                            WHEN subject LIKE '%facility%' THEN 'Facility'
                                                                            WHEN subject LIKE '%feedback%' THEN 'Feedback'
                                                                            ELSE 'General Inquiry'
                                                                        END as category,
                                                                        COUNT(*) as count
                                                                     FROM contact_queries 
                                                                     GROUP BY category 
                                                                     ORDER BY count DESC";
                                                    $subject_result = mysqli_query($con, $subject_query);
                                                    $total_subjects = mysqli_num_rows(mysqli_query($con, "SELECT * FROM contact_queries"));
                                                    
                                                    if(mysqli_num_rows($subject_result) > 0) {
                                                        while($subject = mysqli_fetch_assoc($subject_result)) {
                                                            $percentage = ($subject['count'] / $total_subjects) * 100;
                                                    ?>
                                                    <tr>
                                                        <td><?php echo htmlentities($subject['category']); ?></td>
                                                        <td><?php echo $subject['count']; ?></td>
                                                        <td>
                                                            <div class="progress progress-sm">
                                                                <div class="progress-bar progress-bar-primary" role="progressbar" 
                                                                     style="width: <?php echo $percentage; ?>%">
                                                                    <?php echo number_format($percentage, 1); ?>%
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <?php
                                                        }
                                                    } else {
                                                    ?>
                                                    <tr>
                                                        <td colspan="3" class="text-center">No query data available.</td>
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