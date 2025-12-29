<?php
session_start();
include('include/config.php');

// Access control
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pharmacy'){
    header("Location: ../index.php");
    exit;
}

// Get prescription ID
$prescription_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if($prescription_id <= 0){
    header("Location: prescriptions-pending.php");
    exit;
}

// Fetch prescription details with doctor & patient info
$presQuery = mysqli_query($con, "
    SELECT p.*, 
           d.first_name AS doctor_first, d.last_name AS doctor_last,
           pt.first_name AS patient_first, pt.last_name AS patient_last, pt.ehr_no
    FROM prescriptions p
    JOIN doctor d ON p.doctor_id=d.id
    JOIN patients pt ON p.patient_id=pt.id
    WHERE p.id=$prescription_id
");
if(!$presQuery) die("Error fetching prescription: ".mysqli_error($con));
if(mysqli_num_rows($presQuery) == 0) die("Prescription not found!");
$prescription = mysqli_fetch_assoc($presQuery);

// Search for drugs
$search = '';
$search_sql = '';
if(isset($_POST['search'])){
    $search = trim(mysqli_real_escape_string($con, $_POST['search']));
    if($search != '') {
        $search_sql = " AND (name LIKE '%$search%' OR brand LIKE '%$search%' OR batch_no LIKE '%$search%') ";
    }
}

// Fetch available drugs
$drugsQuery = mysqli_query($con, "SELECT * FROM drugs WHERE quantity > 0 $search_sql ORDER BY name ASC");
if(!$drugsQuery) die("Error fetching drugs: " . mysqli_error($con));

// Handle dispensing
if(isset($_POST['dispense'])){
    $selected_drugs = $_POST['drug'] ?? [];
    $quantities = $_POST['quantity'] ?? [];
    
    if(count($selected_drugs) == 0){
        $error = "Please select at least one drug to dispense.";
    } else {
        $total_items = 0;
        $total_price = 0;
        foreach($selected_drugs as $index => $drug_id){
            $qty = intval($quantities[$index]);
            if($qty <= 0) continue;

            $drugRes = mysqli_query($con,"SELECT * FROM drugs WHERE id=$drug_id");
            $drug = mysqli_fetch_assoc($drugRes);

            if($drug['quantity'] < $qty){
                $error = "Insufficient quantity for {$drug['name']} (Available: {$drug['quantity']})";
                break;
            }

            // Update stock
            mysqli_query($con, "UPDATE drugs SET quantity=quantity-$qty WHERE id=$drug_id");

            // Insert into dispense_history
            mysqli_query($con, "INSERT INTO dispense_history 
                (prescription_id, drug_id, quantity, unit, user_id) 
                VALUES ($prescription_id, $drug_id, $qty, '".mysqli_real_escape_string($con,$drug['unit'])."', ".$_SESSION['user_id'].")");

            // Insert into pharmacy_payment
            mysqli_query($con, "INSERT INTO pharmacy_payment 
                (patient_id, ehr_no, patient_name, item_id, item_name, price, status, created_at) 
                VALUES (
                    ".$prescription['patient_id'].",
                    '".mysqli_real_escape_string($con,$prescription['ehr_no'])."',
                    '".mysqli_real_escape_string($con,$prescription['patient_first'].' '.$prescription['patient_last'])."',
                    $drug_id,
                    '".mysqli_real_escape_string($con,$drug['name'])."',
                    ".$drug['price'] * $qty.",
                    'pending',
                    NOW()
                )");

            $total_items += $qty;
            $total_price += $drug['price'] * $qty;
        }

        if(!isset($error)){
            // Mark prescription as completed
            mysqli_query($con, "UPDATE prescriptions SET status='completed' WHERE id=$prescription_id");

            $patient_name = $prescription['patient_first'].' '.$prescription['patient_last'];
            $action = "Dispensed $total_items items (₦$total_price) for patient: $patient_name, Prescription ID: $prescription_id";
            mysqli_query($con, "INSERT INTO user_log 
                (user_id, user_role, action, reference_id, ip_address) 
                VALUES (".$_SESSION['user_id'].",'pharmacy','".mysqli_real_escape_string($con,$action)."',$prescription_id,'".$_SERVER['REMOTE_ADDR']."')");

            $success = "Drugs dispensed successfully! Total Price: ₦".number_format($total_price,2);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Dispense Prescription</title>
    <?php include 'include/css.php'; ?>
</head>
<body>
<div id="app">
<?php include('include/sidebar.php'); ?>
<div class="app-content">
<?php include('include/header.php'); ?>

<div class="main-content">
<div class="wrap-content container">

<section id="page-title">
    <div class="row">
        <div class="col-sm-8">
            <h2 class="mainTitle">Dispense Prescription - <?= htmlspecialchars($prescription['patient_first'].' '.$prescription['patient_last']) ?></h2>
        </div>
    </div>
</section>

<div class="container-fluid container-fullw bg-white">

<!-- Messages -->
<?php if(isset($error)): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
<?php if(isset($success)): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

<?php if(isset($success)): ?>
<a href="print-prescription.php?id=<?= $prescription_id ?>" class="btn btn-info mb-3" target="_blank">
    <i class="fa fa-print"></i> Print Prescription
</a>
<?php endif; ?>

<h4>Prescription Details</h4>
<p><strong>Doctor:</strong> <?= htmlspecialchars($prescription['doctor_first'].' '.$prescription['doctor_last']) ?></p>
<p><strong>Diagnosis:</strong> <?= htmlspecialchars($prescription['diagnosis']) ?></p>
<p><strong>Medication:</strong> <?= htmlspecialchars($prescription['medication']) ?></p>
<p><strong>Instructions:</strong> <?= htmlspecialchars($prescription['instructions']) ?></p>

<!-- Search Drugs -->
<form method="post" class="form-inline mb-3">
    <div class="form-group">
        <input type="text" name="search" class="form-control" placeholder="Search drug by name, brand, or batch" value="<?= htmlspecialchars($search) ?>">
    </div>
    <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Search</button>
</form>

<!-- Dispense Form -->
<form method="post">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Drug Name</th>
                <th>Brand</th>
                <th>Available Quantity</th>
                <th>Unit</th>
                <th>Quantity to Dispense</th>
                <th>Select</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $sn=1;
        if(mysqli_num_rows($drugsQuery) > 0){
            while($drug = mysqli_fetch_assoc($drugsQuery)){
        ?>
            <tr>
                <td><?= $sn++ ?></td>
                <td><?= htmlspecialchars($drug['name']) ?></td>
                <td><?= htmlspecialchars($drug['brand']) ?></td>
                <td><?= $drug['quantity'] ?></td>
                <td><?= htmlspecialchars($drug['unit']) ?></td>
                <td><input type="number" name="quantity[]" class="form-control qty-input" min="1" max="<?= $drug['quantity'] ?>" value="1"></td>
                <td><input type="checkbox" name="drug[]" class="drug-checkbox" value="<?= $drug['id'] ?>" data-price="<?= $drug['price'] ?>"></td>
            </tr>
        <?php
            }
        } else {
            echo '<tr><td colspan="7" class="text-center">No drugs found</td></tr>';
        }
        ?>
            <tr>
                <td colspan="7">
                    <div class="mb-3">
                        <h3><strong>Total Price: ₦<span id="total-price">0.00</span></strong></h3>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>

    <button type="submit" name="dispense" class="btn btn-success">Dispense Selected Drugs</button>
    <a href="prescriptions-pending.php" class="btn btn-secondary">Back</a>
</form>

</div>
</div>
</div>

<?php include('include/footer.php'); ?>
<?php include('include/setting.php'); ?>
</div>
<?php include('include/js.php'); ?>

<script>
// Dynamic total price calculation
const checkboxes = document.querySelectorAll('.drug-checkbox');
const qtyInputs = document.querySelectorAll('.qty-input');
const totalPriceEl = document.getElementById('total-price');

function updateTotal() {
    let total = 0;
    checkboxes.forEach((cb, index) => {
        if(cb.checked) {
            const price = parseFloat(cb.dataset.price);
            const qty = parseInt(qtyInputs[index].value) || 0;
            total += price * qty;
        }
    });
    totalPriceEl.textContent = total.toFixed(2);
}

// Update on checkbox change or quantity input
checkboxes.forEach(cb => cb.addEventListener('change', updateTotal));
qtyInputs.forEach(qty => qty.addEventListener('input', updateTotal));
</script>

</body>
</html>
