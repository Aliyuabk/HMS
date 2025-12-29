<?php
session_start();
include('include/config.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$limit = 10; // patients per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

/* =========================
   SEARCH & FILTER LOGIC
========================= */
$where = [];
$params = [];
$types = "";

// Search by EHR or name
if (!empty($_POST['search'])) {
    $where[] = "(ehr_no LIKE ? OR first_name LIKE ? OR last_name LIKE ?)";
    $search = "%" . $_POST['search'] . "%";
    $params = array_merge($params, [$search, $search, $search]);
    $types .= "sss";
}

// Gender filter
if (!empty($_POST['gender'])) {
    $where[] = "gender = ?";
    $params[] = $_POST['gender'];
    $types .= "s";
}

// Date range filter
if (!empty($_POST['date_from']) && !empty($_POST['date_to'])) {
    $where[] = "DATE(created_at) BETWEEN ? AND ?";
    $params[] = $_POST['date_from'];
    $params[] = $_POST['date_to'];
    $types .= "ss";
}

// COUNT for pagination
$countSql = "SELECT COUNT(*) total FROM patients";
if ($where) {
    $countSql .= " WHERE " . implode(" AND ", $where);
}

$countStmt = $con->prepare($countSql);
if (!empty($where)) {
    $countTypes = str_repeat("s", count($params));
    $countStmt->bind_param($countTypes, ...$params);
}
$countStmt->execute();
$totalRows = $countStmt->get_result()->fetch_assoc()['total'];

$totalPages = ceil($totalRows / $limit);

// Fetch patients
$sql = "SELECT * FROM patients";
if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= "ii";

$stmt = $con->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

/* =========================
   DASHBOARD COUNTS
========================= */
$totalPatients = $con->query("SELECT COUNT(*) total FROM patients")->fetch_assoc()['total'];
$malePatients = $con->query("SELECT COUNT(*) total FROM patients WHERE gender='Male'")->fetch_assoc()['total'];
$femalePatients = $con->query("SELECT COUNT(*) total FROM patients WHERE gender='Female'")->fetch_assoc()['total'];
$newThisMonth = $con->query("
    SELECT COUNT(*) total FROM patients 
    WHERE MONTH(created_at)=MONTH(CURRENT_DATE()) 
    AND YEAR(created_at)=YEAR(CURRENT_DATE())
")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin | All Patients</title>
    <?php include 'include/css.php';?>
    <style>
        /* Professional table styling */
        table.table {
            border-collapse: collapse;
            width: 100%;
        }
        table.table th, table.table td {
            padding: 10px;
            text-align: left;
        }
        table.table th {
            background-color: #2a3f54;
            color: #fff;
        }
        table.table tr:nth-child(even) {
            background-color: #f5f5f5;
        }
        table.table tr:hover {
            background-color: #d9edf7;
        }
        .filter-card {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0px 2px 5px rgba(0,0,0,0.1);
        }
        .pagination li a {
            padding: 8px 12px;
            margin: 0 3px;
            border-radius: 4px;
            color: #2a3f54;
            border: 1px solid #ddd;
        }
        .pagination li.active a {
            background-color: #2a3f54;
            color: #fff;
            border: 1px solid #2a3f54;
        }
        .dashboard-card {
            border-radius: 10px;
            box-shadow: 0px 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            color: #fff;
        }
        .dashboard-card .fa-stack-2x {
            opacity: 0.2;
        }
        .dashboard-card h2 {
            margin-top: 10px;
        }
        .dashboard-card p {
            font-size: 1.5em;
            font-weight: bold;
            margin: 0;
        }
    </style>
</head>
<body>
<div id="app">
    <?php include 'include/sidebar.php'; ?>

    <div class="app-content">
        <?php include 'include/header.php';?>

        <div class="main-content">
            <div class="wrap-content container" id="container">
                <section id="page-title">
                    <div class="row">
                        <div class="col-sm-8">
                            <h2 class="mainTitle">Admin | All Patients</h2>
                        </div>
                        <ol class="breadcrumb">
                            <li><span>Admin</span></li>
                            <li class="active"><span>All Patients</span></li>
                        </ol>
                    </div>
                </section>

                <!-- FILTERS -->
                <div class="filter-card">
                    <form method="post" action="">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label>Search</label>
                                <input type="text" name="search" class="form-control" placeholder="Name or MRN">
                            </div>
                            <div class="col-md-2 mb-3">
                                <label>Gender</label>
                                <select name="gender" class="form-control">
                                    <option value="">All</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label>Date From</label>
                                <input type="date" name="date_from" class="form-control">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label>Date To</label>
                                <input type="date" name="date_to" class="form-control">
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100"><i class="fa fa-search"></i> Search</button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- PATIENT TABLE -->
                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>MRN</th>
                                <th>Patient Name</th>
                                <th>Gender</th>
                                <th>Date of Birth</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Registered Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; while ($row = $result->fetch_assoc()) { ?>
                            <tr>
                                <td><?= $i++; ?></td>
                                <td><?= htmlspecialchars($row['ehr_no']); ?></td>
                                <td><?= htmlspecialchars($row['first_name'].' '.$row['last_name']); ?></td>
                                <td><?= htmlspecialchars($row['gender']); ?></td>
                                <td><?= htmlspecialchars($row['dob']); ?></td>
                                <td><?= htmlspecialchars($row['phone']); ?></td>
                                <td><?= htmlspecialchars($row['email']); ?></td>
                                <td><?= date("Y-m-d", strtotime($row['created_at'])); ?></td>
                                <td>
                                    <a href="view-patient.php?id=<?= $row['id']; ?>" class="btn btn-xs btn-info"><i class="fa fa-eye"></i></a>
                                    <a href="edit-patient.php?id=<?= $row['id']; ?>" class="btn btn-xs btn-warning"><i class="fa fa-edit"></i></a>
                                    <a href="delete-patient.php?id=<?= $row['id']; ?>" class="btn btn-xs btn-danger" onclick="return confirm('Are you sure?');"><i class="fa fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <!-- PAGINATION -->
                <div class="row mt-3">
                    <div class="col-md-12">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $page - 1 ?>">«</a>
                            </li>
                            <?php for ($i = 1; $i <= $totalPages; $i++) { ?>
                            <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                            </li>
                            <?php } ?>
                            <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $page + 1 ?>">»</a>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- DASHBOARD CARDS -->
                <div class="row mt-4">
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="dashboard-card bg-primary text-center">
                            <i class="fa fa-users fa-3x"></i>
                            <h2>Total Patients</h2>
                            <p><?= $totalPatients ?></p>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="dashboard-card bg-success text-center">
                            <i class="fa fa-male fa-3x"></i>
                            <h2>Male Patients</h2>
                            <p><?= $malePatients ?></p>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="dashboard-card bg-info text-center">
                            <i class="fa fa-female fa-3x"></i>
                            <h2>Female Patients</h2>
                            <p><?= $femalePatients ?></p>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="dashboard-card bg-warning text-center">
                            <i class="fa fa-user-plus fa-3x"></i>
                            <h2>New This Month</h2>
                            <p><?= $newThisMonth ?></p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <?php include 'include/footer.php';?>
    <?php include 'include/setting.php';?>
</div>
<?php include 'include/js.php';?>
</body>
</html>
