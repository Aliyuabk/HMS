<?php
session_start();
include('include/config.php');

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pharmacy'){
    header("Location: ../index.php");
    exit;
}

$month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');

$limit = 20;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page-1)*$limit;

$search = isset($_POST['search']) ? trim(mysqli_real_escape_string($con, $_POST['search'])) : "";

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
    WHERE DATE_FORMAT(dh.created_at,'%Y-%m') = '$month'
";

if($search){
    $baseQuery .= " AND (dr.name LIKE '%$search%' OR pt.first_name LIKE '%$search%' OR pt.last_name LIKE '%$search%')";
}

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

$dispenseQuery = mysqli_query($con, $baseQuery . " ORDER BY dh.created_at DESC LIMIT $offset, $limit");
if(!$dispenseQuery){
    die("Query Failed: " . mysqli_error($con));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Monthly Dispense Report</title>
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
            <h2 class="mainTitle">Monthly Dispense Report</h2>
        </div>
        <ol class="breadcrumb">
            <li><span>Pharmacy</span></li>
            <li class="active"><span>Monthly Report</span></li>
        </ol>
    </div>
</section>

<div class="container-fluid container-fullw bg-white">

    <div class="row mb-3">
        <div class="col-md-6">
            <form method="post" class="form-inline">
                <input type="text" name="search" class="form-control" placeholder="Search by drug or patient" value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-primary ml-2"><i class="fa fa-search"></i> Search</button>
            </form>
        </div>
        <div class="col-md-6 text-right">
            <form method="get" class="form-inline">
                <input type="month" name="month" class="form-control" value="<?= $month ?>">
                <button type="submit" class="btn btn-info ml-2"><i class="fa fa-filter"></i> Filter</button>
            </form>
            <a href="print-monthly-dispense.php?month=<?= $month ?>" class="btn btn-success ml-2" target="_blank"><i class="fa fa-print"></i> Print Report</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-white">
                <div class="panel-heading">
                    <h4 class="panel-title">Drugs Dispensed in <?= date('F Y', strtotime($month.'-01')) ?></h4>
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
                                <th>Date</th>
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
                                        <td>".date('d-m-Y', strtotime($row['created_at']))."</td>
                                      </tr>";
                                $sn++;
                            }
                        } else {
                            echo "<tr><td colspan='9' class='text-center'>No drugs dispensed for this month</td></tr>";
                        }
                        ?>
                        </tbody>
                    </table>

                    <?php if($totalPages > 1): ?>
                    <ul class="pagination">
                        <li class="<?= ($page<=1)?'disabled':'' ?>"><a href="?page=<?= $page-1 ?>&month=<?= $month ?>">«</a></li>
                        <?php for($p=1;$p<=$totalPages;$p++): ?>
                            <li class="<?= ($page==$p)?'active':'' ?>"><a href="?page=<?= $p ?>&month=<?= $month ?>"><?= $p ?></a></li>
                        <?php endfor; ?>
                        <li class="<?= ($page>=$totalPages)?'disabled':'' ?>"><a href="?page=<?= $page+1 ?>&month=<?= $month ?>">»</a></li>
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
