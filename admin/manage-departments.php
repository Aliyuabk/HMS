<?php
session_start();
include('include/config.php');

// Restrict access to admin only
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../index.php");
    exit;
}

// Pagination
$limit = 5;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Search
$search = "";
if(isset($_POST['search'])){
    $search = trim(mysqli_real_escape_string($con, $_POST['search']));
}

// Count total rows for pagination
if($search){
    $countQuery = mysqli_query($con, "SELECT COUNT(*) as total FROM department 
        WHERE dept_name LIKE '%$search%' OR section LIKE '%$search%'");
} else {
    $countQuery = mysqli_query($con, "SELECT COUNT(*) as total FROM department");
}

$totalRows = mysqli_fetch_assoc($countQuery)['total'];
$totalPages = ceil($totalRows / $limit);

// Fetch departments
if($search){
    $query = mysqli_query($con, "SELECT * FROM department 
        WHERE dept_name LIKE '%$search%' OR section LIKE '%$search%' 
        ORDER BY id DESC LIMIT $offset, $limit");
} else {
    $query = mysqli_query($con, "SELECT * FROM department ORDER BY section DESC LIMIT $offset, $limit");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin | Manage Departments</title>
    <?php include 'include/css.php';?>
</head>
<body>
<div id="app">
    <?php include 'include/sidebar.php'; ?>
    <div class="app-content">
        <?php include 'include/header.php'; ?>
        <div class="main-content">
            <div class="wrap-content container" id="container">
                <section id="page-title">
                    <div class="row">
                        <div class="col-sm-8">
                            <h2 class="mainTitle">Admin | Manage Departments</h2>
                        </div>
                        <ol class="breadcrumb">
                            <li>Admin</li>
                            <li class="active"> Manage Departments</li>
                        </ol>
                    </div>
                </section>

                <!-- Manage Department List -->
                <div class="container-fluid container-fullw bg-white">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="panel panel-white">
                                <div class="panel-heading">
                                    <h4 class="panel-title">Manage Department List</h4>
                                    <div class="panel-tools">
                                        <a href="add-department.php" class="btn btn-primary"><i class="fa fa-plus"></i> Add Department</a>
                                    </div>
                                </div>
                                <div class="panel-body">
                                    <!-- Search Form -->
                                    <form method="post" class="mb-3">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <input type="text" name="search" class="form-control" placeholder="Search by name or section" value="<?= htmlspecialchars($search); ?>">
                                            </div>
                                            <div class="col-md-2">
                                                <button class="btn btn-primary" type="submit"><i class="fa fa-search"></i> Search</button>
                                            </div>
                                        </div>
                                    </form>

                                    <!-- Table -->
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Department Name</th>
                                                <th>Section</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            if(mysqli_num_rows($query) > 0){
                                                $count = $offset + 1;
                                                while($row = mysqli_fetch_assoc($query)){
                                                    ?>
                                                    <tr>
                                                        <td><?= $count++; ?></td>
                                                        <td><?= htmlspecialchars($row['dept_name']); ?></td>
                                                        <td><?= htmlspecialchars(ucwords($row['section'])); ?></td>
                                                        <td>
                                                            <a href="edit-dept.php?id=<?= $row['id']; ?>" class="btn btn-warning btn-sm"><i class="fa fa-edit"></i></a>
                                                            <a href="delete.php?delete=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this department?');"><i class="fa fa-trash"></i></a>
                                                        </td>
                                                    </tr>
                                                    <?php
                                                }
                                            } else {
                                                echo '<tr><td colspan="4" class="text-center">No departments found.</td></tr>';
                                            }
                                            ?>
                                        </tbody>
                                    </table>

                                    <!-- Pagination -->
                                    <?php if($totalPages > 1): ?>
                                    <nav>
                                        <ul class="pagination">
                                            <li class="<?= ($page <= 1)?'disabled':'' ?>">
                                                <a href="?page=<?= $page-1 ?>">«</a>
                                            </li>
                                            <?php for($p=1;$p<=$totalPages;$p++): ?>
                                                <li class="<?= ($page==$p)?'active':'' ?>">
                                                    <a href="?page=<?= $p ?>"><?= $p ?></a>
                                                </li>
                                            <?php endfor; ?>
                                            <li class="<?= ($page >= $totalPages)?'disabled':'' ?>">
                                                <a href="?page=<?= $page+1 ?>">»</a>
                                            </li>
                                        </ul>
                                    </nav>
                                    <?php endif; ?>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
            
        </div>

        
    </div>
    <?php include 'include/footer.php'; ?>
        <?php include 'include/setting.php'; ?>
</div>

<?php include 'include/js.php'; ?>
</body>
</html>
