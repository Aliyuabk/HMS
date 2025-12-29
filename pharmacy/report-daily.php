<?php
session_start();
include('include/config.php');

// Access control
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pharmacy'){
    header("Location: ../index.php");
    exit;
}

$today = date('Y-m-d');

// Pagination
$limit = 20;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page-1)*$limit;

// Search
$search = isset($_POST['search']) ? trim(mysqli_real_escape_string($con, $_POST['search'])) : "";

// Base query
$baseQuery = "
    SELECT dh.id, dh.quantity, dh.created_at, 
           dr.name AS drug_name, dr.brand, dr.unit,
           pt.first_name AS patient_first, pt.last_name AS patient_last,
           d.first_name AS doctor_first, d.last_name AS doctor_last,
           p.id AS prescription_id
    FROM dispense_history dh
    JOIN drugs dr ON dh.drug_id = dr.id
    JOIN prescriptions p ON dh.prescription_id = p.id
    JOIN patients pt ON p.patient_id = pt.id
    JOIN doctor d ON p.doctor_id = d.id
    WHERE DATE(dh.created_at) = '$today'
";

// Add search filter
if($search){
    $baseQuery .= " AND (dr.name LIKE '%$search%' OR pt.first_name LIKE '%$search%' OR pt.last_name LIKE '%$search%')";
}

// Count total rows
$countQuery = mysqli_query($con, str_replace("SELECT dh.id, dh.quantity, dh.created_at, 
           dr.name AS drug_name, dr.brand, dr.unit,
           pt.first_name AS patient_first, pt.last_name AS patient_last,
           d.first_name AS doctor_first, d.last_name AS doctor_last,
           p.id AS prescription_id", "SELECT COUNT(*) AS total", $baseQuery));

if(!$countQuery){
    die("Count Query Failed: " . mysqli_error($con));
}

$totalRows = mysqli_fetch_assoc($countQuery)['total'] ?? 0;
$totalPages = ceil($totalRows / $limit);

// Fetch data with limit
$dispenseQuery = mysqli_query($con, $baseQuery . " ORDER BY dh.created_at DESC LIMIT $offset, $limit");
if(!$dispenseQuery){
    die("Query Failed: " . mysqli_error($con));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Daily Dispense Report</title>
    <?php include 'include/css.php'; ?>
</head>
<body>
<div id="app">
<?php include('include/sidebar.php'); ?>
<div class="app-content">
<?php include('include/header.php'); ?>

<div class="main-content">
<div class="wrap-content container" id="container">

<section id="page-title">
    <div class="row">
        <div class="col-sm-8">
            <h2 class="mainTitle">Daily Dispense Report</h2>
        </div>
        <ol class="breadcrumb">
            <li><span>Pharmacy</span></li>
            <li class="active"><span>Daily Report</span></li>
        </ol>
    </div>
</section>

<div class="container-fluid container-fullw bg-white">

    <div class="row mb-3">
        <div class="col-md-6">
            <form method="post" class="form-inline">
                <input type="text" name="search" class="form-control" placeholder="Search by drug or patient" value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-primary ml-2"><i class="fa fa-search"></i> Search</button>
                <a href="report-daily.php" class="btn btn-secondary ml-2">Reset</a>
            </form>
        </div>
        <div class="col-md-6 text-right">
            <a href="print-daily-dispense.php?date=<?= $today ?>" class="btn btn-info" target="_blank"><i class="fa fa-print"></i> Print Report</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-white">
                <div class="panel-heading">
                    <h4 class="panel-title">Drugs Dispensed Today (<?= date('d-m-Y', strtotime($today)) ?>)</h4>
                </div>
                <div class="panel-body">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Prescription ID</th>
                                <th>Patient</th>
                                <th>Doctor</th>
                                <th>Drug</th>
                                <th>Brand</th>
                                <th>Quantity</th>
                                <th>Unit</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $sn = $offset + 1;
                        if(mysqli_num_rows($dispenseQuery) > 0){
                            while($row = mysqli_fetch_assoc($dispenseQuery)){
                                echo "<tr>
                                        <td>{$sn}</td>
                                        <td>{$row['prescription_id']}</td>
                                        <td>".htmlspecialchars($row['patient_first'].' '.$row['patient_last'])."</td>
                                        <td>".htmlspecialchars($row['doctor_first'].' '.$row['doctor_last'])."</td>
                                        <td>".htmlspecialchars($row['drug_name'])."</td>
                                        <td>".htmlspecialchars($row['brand'])."</td>
                                        <td>{$row['quantity']}</td>
                                        <td>".htmlspecialchars($row['unit'])."</td>
                                        <td>".date('H:i:s', strtotime($row['created_at']))."</td>
                                      </tr>";
                                $sn++;
                            }
                        } else {
                            echo "<tr><td colspan='9' class='text-center'>No drugs dispensed today</td></tr>";
                        }
                        ?>
                        </tbody>
                    </table>

                    <?php if($totalPages > 1): ?>
                    <ul class="pagination">
                        <li class="<?= ($page<=1)?'disabled':'' ?>"><a href="?page=<?= $page-1 ?>">«</a></li>
                        <?php for($p=1;$p<=$totalPages;$p++): ?>
                            <li class="<?= ($page==$p)?'active':'' ?>"><a href="?page=<?= $p ?>"><?= $p ?></a></li>
                        <?php endfor; ?>
                        <li class="<?= ($page>=$totalPages)?'disabled':'' ?>"><a href="?page=<?= $page+1 ?>">»</a></li>
                    </ul>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>

</div>
</div>
</div>

<?php include('include/footer.php'); ?>
<?php include('include/setting.php'); ?>
</div>
<?php include 'include/js.php'; ?>
</body>
</html>
