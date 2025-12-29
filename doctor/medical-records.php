<?php
session_start();
require_once('include/config.php');

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor'){
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$sql = mysqli_query($con, "SELECT * FROM doctor WHERE id = '$user_id'");
$user_data = mysqli_fetch_array($sql);

// Get search parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$patient_id = isset($_GET['patient_id']) ? $_GET['patient_id'] : '';

// Build query to get medical records (appointments + prescriptions)
$query = "SELECT 
            a.id as record_id,
            'appointment' as record_type,
            a.appointment_date as date,
            CONCAT(p.first_name, ' ', p.last_name) as patient_name,
            p.ehr_no as patient_id,
            p.phone,
            a.reason as description,
            a.status,
            CONCAT('Appointment - ', a.status) as record_title,
            NULL as diagnosis,
            NULL as medications,
            NULL as instructions
          FROM appointments a 
          JOIN patients p ON a.patient_id = p.id 
          WHERE a.doctor_id = '$user_id'";

// Add prescriptions
$check_prescriptions_table = mysqli_query($con, "SHOW TABLES LIKE 'prescriptions'");
if(mysqli_num_rows($check_prescriptions_table) > 0) {
    $query .= " UNION ALL
              SELECT 
                pr.id as record_id,
                'prescription' as record_type,
                pr.created_at as date,
                CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                p.ehr_no as patient_id,
                p.phone,
                pr.diagnosis as description,
                pr.status,
                CONCAT('Prescription - ', pr.status) as record_title,
                pr.diagnosis,
                pr.medication as medications,
                pr.instructions
              FROM prescriptions pr
              JOIN patients p ON pr.patient_id = p.id 
              WHERE pr.doctor_id = '$user_id'";
}

// Apply filters
$where_conditions = [];
if(!empty($search)) {
    $where_conditions[] = "(CONCAT(p.first_name, ' ', p.last_name) LIKE '%$search%' OR p.ehr_no LIKE '%$search%' OR p.phone LIKE '%$search%')";
}
if(!empty($patient_id)) {
    $where_conditions[] = "p.id = '$patient_id'";
}
if(count($where_conditions) > 0) {
    $query = "SELECT * FROM ($query) as combined_records WHERE " . implode(' AND ', $where_conditions);
}

$query .= " ORDER BY date DESC";
$result = mysqli_query($con, $query);

// Get patients list for filter dropdown
$patients_list_query = mysqli_query($con, "SELECT DISTINCT p.id, CONCAT(p.first_name, ' ', p.last_name) as name, p.ehr_no 
                                          FROM patients p 
                                          JOIN appointments a ON p.id = a.patient_id 
                                          WHERE a.doctor_id = '$user_id' 
                                          ORDER BY p.first_name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Doctor | Medical Records</title>
    <?php include 'include/css.php'; ?>
    <style>
        .record-type { padding: 3px 8px; border-radius: 3px; font-size: 12px; font-weight: bold; text-transform: uppercase; }
        .type-appointment { background: #3498db; color: white; }
        .type-prescription { background: #2ecc71; color: white; }
        .record-card { border: 1px solid #ddd; border-radius: 5px; padding: 15px; margin-bottom: 15px; background: white; transition: all 0.3s; }
        .record-card:hover { box-shadow: 0 2px 5px rgba(0,0,0,0.1); transform: translateY(-2px); }
        .status-badge { padding: 3px 8px; border-radius: 3px; font-size: 12px; font-weight: bold; }
        .status-scheduled, .status-pending { background: #f0ad4e; color: white; }
        .status-completed { background: #5cb85c; color: white; }
        .status-cancelled { background: #d9534f; color: white; }
    </style>
</head>
<body>
<div id="app">
    <div class="sidebar app-aside" id="sidebar">
        <?php include('include/sidebar.php'); ?>
    </div>

    <div class="app-content">
        <?php include 'include/header.php'; ?>
        <div class="main-content">
            <div class="wrap-content container" id="container">
                <section id="page-title">
                    <div class="row">
                        <div class="col-sm-8">
                            <h2 class="mainTitle">Doctor | Medical Records</h2>
                        </div>
                        <ol class="breadcrumb">
                            <li><span>Doctor</span></li>
                            <li class="active"><span>Medical Records</span></li>
                        </ol>
                    </div>
                </section>

                <!-- Filter -->
                <div class="panel panel-white">
                    <div class="panel-heading"><h5 class="panel-title">Search Medical Records</h5></div>
                    <div class="panel-body">
                        <form method="get" class="form-inline">
                            <div class="form-group">
                                <label>Patient:</label>
                                <select name="patient_id" class="form-control" style="width: 250px;">
                                    <option value="">All Patients</option>
                                    <?php while($patient = mysqli_fetch_array($patients_list_query)) { ?>
                                        <option value="<?= $patient['id'] ?>" <?= ($patient_id == $patient['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($patient['name']) ?> (ID: <?= htmlspecialchars($patient['ehr_no']) ?>)
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <input type="text" name="search" class="form-control" placeholder="Search name or ID..." value="<?= htmlspecialchars($search) ?>" style="width: 300px;">
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Search</button>
                            <a href="medical-records.php" class="btn btn-default">Reset</a>
                        </form>
                    </div>
                </div>

                <!-- Records -->
                <div class="panel panel-white">
                    <div class="panel-heading">
                        <h5 class="panel-title">Medical Records</h5>
                    </div>
                    <div class="panel-body">
                        <?php if($result && mysqli_num_rows($result) > 0) { 
                            while($row = mysqli_fetch_array($result)) { ?>
                                <div class="record-card" data-type="<?= $row['record_type'] ?>" data-id="<?= $row['record_id'] ?>">
                                    <div class="row">
                                        <div class="col-md-10">
                                            <span class="record-type type-<?= $row['record_type'] ?>"><?= ucfirst($row['record_type']) ?></span>
                                            &nbsp;
                                            <span class="status-badge status-<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span>
                                            <div class="pull-right text-muted"><i class="fa fa-calendar"></i> <?= date('M d, Y', strtotime($row['date'])) ?></div>
                                            <div class="clearfix"></div>
                                            <h4><?= htmlspecialchars($row['record_title']) ?></h4>
                                            <strong><?= htmlspecialchars($row['patient_name']) ?></strong>
                                            <small class="text-muted">| ID: <?= htmlspecialchars($row['patient_id']) ?> | Phone: <?= htmlspecialchars($row['phone']) ?></small>

                                            <?php if($row['record_type'] == 'appointment') { ?>
                                                <p><strong>Reason:</strong> <?= htmlspecialchars($row['description']) ?></p>
                                            <?php } else { ?>
                                                <?php if(!empty($row['diagnosis'])) { ?><p><strong>Diagnosis:</strong> <?= htmlspecialchars($row['diagnosis']) ?></p><?php } ?>
                                                <?php if(!empty($row['medications'])) { ?><p><strong>Medications:</strong><br><?= nl2br(htmlspecialchars($row['medications'])) ?></p><?php } ?>
                                                <?php if(!empty($row['instructions'])) { ?><p><strong>Instructions:</strong> <?= htmlspecialchars($row['instructions']) ?></p><?php } ?>
                                            <?php } ?>
                                        </div>
                                        <div class="col-md-2 text-right">
                                            <button class="btn btn-info btn-xs" onclick="printRecord('<?= $row['record_id'] ?>')"><i class="fa fa-print"></i> Print</button>
                                        </div>
                                    </div>
                                </div>
                        <?php } } else { ?>
                            <div class="text-center" style="padding: 50px 0;">
                                <i class="fa fa-folder-open fa-4x text-muted" style="margin-bottom: 20px;"></i>
                                <h4>No medical records found</h4>
                            </div>
                        <?php } ?>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <?php include 'include/footer.php'; ?>
    <?php include 'include/setting.php'; ?>
</div>

<?php include 'include/js.php'; ?>
<script>
function printRecord(id) {
    var record = document.querySelector('.record-card[data-id="'+id+'"]');
    if(!record) { alert('Record not found!'); return; }
    var printContents = record.innerHTML;
    var originalContents = document.body.innerHTML;
    document.body.innerHTML = printContents;
    window.print();
    document.body.innerHTML = originalContents;
    location.reload(); // reload to restore JS functionality
}
</script>
</body>
</html>
