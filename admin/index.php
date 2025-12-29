<?php
session_start();
include('include/config.php');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check admin login
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../index.php");
    exit;
}

// Get logged-in admin info


// Function to get total rows safely
if(!function_exists('getTotal')) {
    function getTotal($con, $table){
        $table = mysqli_real_escape_string($con, $table);
        $result = mysqli_query($con, "SELECT COUNT(*) as total FROM `$table`");
        if(!$result) return 0; // if table doesn't exist, return 0
        $row = mysqli_fetch_assoc($result);
        return $row['total'];
    }
}

// Table names
$staff_table = "staff";
$doctor_table = "doctor";
$reception_table = "reception";
$pharmacy_table = "pharmacy";
$lab_table = "lab";
$billing_table = "billing";
$admin_table = "admin";
$patients_table = "patients";
$appointments_table = "appointments";
$queries_table = "queries";

// Fetch totals
$total_users = getTotal($con, $staff_table);
$total_doctors = getTotal($con, $doctor_table);
$total_reception = getTotal($con, $reception_table);
$total_pharmacy = getTotal($con, $pharmacy_table);
$total_lab = getTotal($con, $lab_table);
$total_billing = getTotal($con, $billing_table);
$total_admin = getTotal($con, $admin_table);
$total_patients = getTotal($con, $patients_table);
$total_appointments = getTotal($con, $appointments_table);
$total_queries = getTotal($con, $queries_table);

// Complete total (all users)
$complete_total =  $total_doctors + $total_reception + $total_pharmacy + $total_lab + $total_billing + $total_admin;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin  | Dashboard</title>
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
            <h2 class="mainTitle">Admin | Dashboard</h2>
        </div>
        <ol class="breadcrumb">
            <li><span>Admin</span></li>
            <li class="active"><span>Dashboard</span></li>
        </ol>
    </div>
</section>

<div class="container-fluid container-fullw bg-white">
    <div class="row">

        <?php 
        // Array of dashboard cards
        $cards = [
            ['title'=>'Users','icon'=>'fa-users','count'=>$complete_total],
            ['title'=>'Doctors','icon'=>'fa-user-md','count'=>$total_doctors,'link'=>'manage-doctor.php'],
            ['title'=>'Patients','icon'=>'fa-smile-o','count'=>$total_patients,'link'=>'manage-patients.php'],
            ['title'=>'Reception','icon'=>'fa-user','count'=>$total_reception,'link'=>'manage-reception.php'],
            ['title'=>'Pharmacy','icon'=>'fa-medkit','count'=>$total_pharmacy,'link'=>'manage-pharmacy.php'],
            ['title'=>'Laboratory','icon'=>'fa-flask','count'=>$total_lab,'link'=>'manage-lab.php'],
            ['title'=>'Billing','icon'=>'fa-money','count'=>$total_billing,'link'=>'manage-billing.php'],
            ['title'=>'Admins','icon'=>'fa-user-secret','count'=>$total_admin,'link'=>'manage-admin.php'],
            ['title'=>'Queries','icon'=>'fa-question','count'=>$total_queries,'link'=>'unread-queries.php'],
        ];

        foreach($cards as $card): ?>
        <div class="col-sm-4">
            <div class="panel panel-white no-radius text-center">
                <div class="panel-body">
                    <span class="fa-stack fa-2x">
                        <i class="fa fa-square fa-stack-2x text-primary"></i>
                        <i class="fa <?php echo $card['icon']; ?> fa-stack-1x fa-inverse"></i>
                    </span>
                    <h2 class="StepTitle"><?php echo $card['title']; ?></h2>
                    <p class="links cl-effect-1">
                        <a href="<?php echo $card['link']; ?>">
                            Total <?php echo $card['title']; ?>: <?php echo $card['count']; ?>
                        </a>
                    </p>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

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
