<?php
session_start();
include('include/config.php');

// Only allow super admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Handle deletion
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $con->prepare("DELETE FROM radiology WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: manage-radiology.php");
    exit;
}

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Search
$where = [];
$params = [];
$types = "";
if (!empty($_POST['search'])) {
    $where[] = "(first_name LIKE ? OR last_name LIKE ? OR phone LIKE ?)";
    $search = "%".$_POST['search']."%";
    $params = array_merge($params, [$search, $search, $search]);
    $types .= "sss";
}

// Count total
$countSql = "SELECT COUNT(*) as total FROM radiology";
if ($where) $countSql .= " WHERE ".implode(" AND ", $where);
$countStmt = $con->prepare($countSql);
if ($where) $countStmt->bind_param($types, ...$params);
$countStmt->execute();
$totalRows = $countStmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

// Fetch radiology users
$sql = "SELECT * FROM radiology";
if ($where) $sql .= " WHERE ".implode(" AND ", $where);
$sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= "ii";

$stmt = $con->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin | Radiology</title>
		
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
                            <h2 class="mainTitle">Admin | Radiology</h2>
                        </div>
                        <ol class="breadcrumb">
                            <li>Admin</li>
                            <li class="active">All Radiology</li>
                        </ol>
                    </div>
                </section>

                <!-- Radiology List -->
                <div class="container-fluid container-fullw bg-white">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="panel panel-white">
                                <div class="panel-heading">
                                    <h4 class="panel-title">Radiology List</h4>
                                    <div class="panel-tools">
                                        <a href="add-users.php" class="btn btn-primary"><i class="fa fa-plus"></i> Add Radiology</a>
                                    </div>
                                </div>
                                <div class="panel-body">
                                    <!-- Search Form -->
                                    <form method="post" class="mb-3">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <input type="text" name="search" class="form-control" placeholder="Search by name or phone">
                                            </div>
                                            <div class="col-md-2">
                                                <button class="btn btn-primary" type="submit"><i class="fa fa-search"></i> Search</button>
                                            </div>
                                        </div>
                                    </form>

                                    <!-- Table -->
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Name</th>
                                                <th>Phone</th>
                                                <th>Email</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $i=1; while($row = $result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?= $i++; ?></td>
                                                <td><?= htmlspecialchars($row['first_name'].' '.$row['last_name']); ?></td>
                                                <td><?= htmlspecialchars($row['phone']); ?></td>
                                                <td><?= htmlspecialchars($row['email']); ?></td>
                                                <td style="border-radius: 10px; color: white; background-color: <?= ($row['status'] == 'Active') ? 'green' : 'red';  ?>"><?= htmlspecialchars($row['status']); ?></td>
                                                <td>
                                                    <a href="edit-user.php?id=<?= $row['id']; ?>&role=radiology" class="btn btn-warning btn-sm"><i class="fa fa-edit"></i></a>
                                                    <a href="manage-radiology.php?delete=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this user?');"><i class="fa fa-trash"></i></a>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>

                                    <!-- Pagination -->
                                    <nav>
                                        <ul class="pagination">
                                            <li class="<?= ($page<=1)?'disabled':'' ?>"><a href="?page=<?= $page-1 ?>">«</a></li>
                                            <?php for($p=1;$p<=$totalPages;$p++): ?>
                                                <li class="<?= ($page==$p)?'active':'' ?>"><a href="?page=<?= $p ?>"><?= $p ?></a></li>
                                            <?php endfor; ?>
                                            <li class="<?= ($page>=$totalPages)?'disabled':'' ?>"><a href="?page=<?= $page+1 ?>">»</a></li>
                                        </ul>
                                    </nav>
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
</div>

     <?php include 'include/js.php';?>
	</body>
</html>

