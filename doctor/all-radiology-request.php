<?php
session_start();
require_once('include/config.php');

// Check doctor login
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor'){
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$sql = mysqli_query($con, "SELECT * FROM doctor WHERE id='$user_id'");
$user_data = mysqli_fetch_array($sql);

// Get filter/search parameters
$status = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$patient_id = isset($_GET['patient_id']) ? $_GET['patient_id'] : '';

// Build query
$query = "SELECT lr.*, p.first_name, p.last_name, p.ehr_no, a.appointment_date
          FROM radiology_requests lr
          JOIN patients p ON lr.patient_id = p.id
          LEFT JOIN appointments a ON lr.appointment_id = a.id
          WHERE lr.doctor_id = '$user_id'";

if(!empty($status)){
    $query .= " AND lr.status = '$status'";
}

if(!empty($search)){
    $query .= " AND (p.first_name LIKE '%$search%' OR p.last_name LIKE '%$search%' OR p.ehr_no LIKE '%$search%')";
}

if(!empty($patient_id)){
    $query .= " AND p.id = '$patient_id'";
}

$query .= " ORDER BY lr.created_at DESC";
$result = mysqli_query($con, $query);

// Get patients list for filter dropdown
$patients_list_query = mysqli_query($con, "SELECT DISTINCT p.id, CONCAT(p.first_name,' ',p.last_name) as name, p.ehr_no 
                                          FROM patients p
                                          JOIN appointments a ON p.id = a.patient_id
                                          WHERE a.doctor_id = '$user_id'
                                          ORDER BY p.first_name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Doctor | All Radiology Requests</title>
    <?php include 'include/css.php'; ?>
    <style>
        .status-badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-pending { background: #f0ad4e; color: white; }
        .status-completed { background: #5cb85c; color: white; }
        .status-cancelled { background: #d9534f; color: white; }
        .record-card {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
            background: white;
            transition: all 0.3s;
        }
        .record-card:hover {
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
<div id="app">
    <div class="sidebar app-aside" id="sidebar"><?php include('include/sidebar.php'); ?></div>
    <div class="app-content">
        <?php include 'include/header.php'; ?>
        <div class="main-content">
            <div class="wrap-content container" id="container">
                <section id="page-title">
                    <div class="row">
                        <div class="col-sm-8"><h2 class="mainTitle">Doctor | All Radiology Requests</h2></div>
                        <ol class="breadcrumb">
                            <li><span>Doctor</span></li>
                            <li class="active"><span>Radiology Requests</span></li>
                        </ol>
                    </div>
                </section>

                <div class="container-fluid container-fullw bg-white">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="panel panel-white">
                                <div class="panel-heading"><h4 class="panel-title">Filter Radiology Requests</h4></div>
                                <div class="panel-body">
                                    <form method="get" class="form-inline">
                                        <div class="form-group">
                                            <label>Status:</label>
                                            <select name="status" class="form-control">
                                                <option value="">All Status</option>
                                                <option value="pending" <?= ($status=='pending')?'selected':'' ?>>Pending</option>
                                                <option value="completed" <?= ($status=='completed')?'selected':'' ?>>Completed</option>
                                                <option value="cancelled" <?= ($status=='cancelled')?'selected':'' ?>>Cancelled</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Patient:</label>
                                            <select name="patient_id" class="form-control">
                                                <option value="">All Patients</option>
                                                <?php while($p = mysqli_fetch_array($patients_list_query)){ ?>
                                                    <option value="<?= $p['id'] ?>" <?= ($patient_id==$p['id'])?'selected':'' ?>>
                                                        <?= htmlspecialchars($p['name']).' ('.htmlspecialchars($p['ehr_no']).')' ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <input type="text" name="search" class="form-control" placeholder="Search patient name or ID..." value="<?= htmlspecialchars($search) ?>">
                                        </div>
                                        <button type="submit" class="btn btn-primary">Filter</button>
                                        <a href="all-request.php" class="btn btn-default">Reset</a>
                                        <a href="add-radiology-request.php" class="btn btn-success pull-right"><i class="fa fa-plus"></i> Add Radiology Request</a>
                                    </form>
                                </div>
                            </div>

                            <div class="panel panel-white">
                                <div class="panel-heading"><h4 class="panel-title">Radiology Requests</h4></div>
                                <div class="panel-body">
                                  
                                    <?php if($result && mysqli_num_rows($result)>0){ 
                                        while($row = mysqli_fetch_array($result)){ ?>
                                          <a href="edit-radiology-request.php?id=<?= $row['id'] ?>" style="text-decoration: none; color: inherit;">
                                        <div class="record-card">
                                            <div class="row">
                                                <div class="col-md-10">
                                                    <h4><?= htmlspecialchars($row['test_name']) ?> 
                                                        <span class="status-badge status-<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span>
                                                    </h4>
                                                    <p><strong>Patient:</strong> <?= htmlspecialchars($row['first_name'].' '.$row['last_name']) ?> | ID: <?= htmlspecialchars($row['ehr_no']) ?></p>
                                                    <?php if(!empty($row['appointment_date'])){ ?>
                                                        <p><strong>Appointment Date:</strong> <?= date('d M Y', strtotime($row['appointment_date'])) ?></p>
                                                    <?php } ?>
                                                    <?php if(!empty($row['notes'])){ ?>
                                                        <p><strong>Notes:</strong> <?= htmlspecialchars($row['notes']) ?></p>
                                                    <?php } ?>
                                                    <p><small class="text-muted">Requested on <?= date('d M Y', strtotime($row['created_at'])) ?></small></p>
                                                </div>
                                                <div class="col-md-2 text-right">
                                                    <div class="btn-group-vertical">
                                                        <a href="edit-radiology-request.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-xs"><i class="fa fa-edit"></i> Edit</a>
                                                        <a href="all-radiology-request.php?delete=<?= $row['id'] ?>" class="btn btn-danger btn-xs" onclick="return confirm('Are you sure to delete this request?')"><i class="fa fa-trash"></i> Delete</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        </a>
                                    <?php } 
                                    } else { ?>
                                        <div class="text-center" style="padding: 50px 0;">
                                            <i class="fa fa-folder-open fa-4x text-muted" style="margin-bottom: 20px;"></i>
                                            <h4>No radiology requests found</h4>
                                            <a href="add-radiology-request.php" class="btn btn-success"><i class="fa fa-plus"></i> Add Radiology Request</a>
                                        </div>
                                    <?php } ?>
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

<?php
// Optional: handle deletion
if(isset($_GET['delete'])){
    $id = intval($_GET['delete']);
    mysqli_query($con, "DELETE FROM radiology_requests WHERE id='$id' AND doctor_id='$user_id'");
    echo "<script>window.location.href='all-radiology-request.php';</script>";
}
?>
</body>
</html>
