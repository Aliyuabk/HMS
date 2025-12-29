<?php
session_start();
require_once('include/config.php');

/* =======================
   AUTH CHECK
======================= */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'lab') {
    header("Location: ../index.php");
    exit;
}

/* =======================
   FETCH CURRENT PRICE
======================= */
$price_row = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM lab_price LIMIT 1"));

/* =======================
   HANDLE PRICE UPDATE
======================= */
if (isset($_POST['update_price'])) {
    $new_price = mysqli_real_escape_string($con, $_POST['price']);

    if ($price_row) {
        // Update existing price
        mysqli_query($con, "UPDATE lab_price SET price='$new_price', updated_at=NOW() WHERE id='{$price_row['id']}'");
        $msg = "Lab price updated successfully!";
    } else {
        // Insert new price
        mysqli_query($con, "INSERT INTO lab_price (price) VALUES ('$new_price')");
        $msg = "Lab price added successfully!";
    }

    // Refresh
    header("Location: price.php?success=".urlencode($msg));
    exit;
}

$success = isset($_GET['success']) ? $_GET['success'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Lab | Manage Price</title>
    <?php include 'include/css.php'; ?>
</head>
<body>
<div id="app">
    <div class="sidebar app-aside" id="sidebar"><?php include 'include/sidebar.php'; ?></div>
    <div class="app-content">
        <?php include 'include/header.php'; ?>

        <div class="main-content">
            <div class="wrap-content container" id="container">

                <section id="page-title">
                    <div class="row">
                        <div class="col-sm-8">
                            <h2 class="mainTitle">Lab | Manage Price</h2>
                        </div>
                        <ol class="breadcrumb">
                            <li><span>Laboratory</span></li>
                            <li class="active"><span>Price</span></li>
                        </ol>
                    </div>
                </section>

                <div class="container-fluid container-fullw bg-white">
                    <div class="row">
                        <div class="col-md-6 col-md-offset-3">

                            <?php if($success) { ?>
                                <div class="alert alert-success"><?= htmlspecialchars($success); ?></div>
                            <?php } ?>

                            <div class="panel panel-white">
                                <div class="panel-heading">
                                    <h5 class="panel-title">Current Lab Price</h5>
                                </div>
                                <div class="panel-body">
                                    <form method="post">
                                        <div class="form-group">
                                            <label>Price (₦)</label>
                                            <input type="number" name="price" class="form-control" required 
                                                   value="<?= $price_row ? htmlspecialchars($price_row['price']) : ''; ?>" step="0.01" min="0">
                                        </div>
                                        <button type="submit" name="update_price" class="btn btn-primary">
                                            <?= $price_row ? 'Update Price' : 'Add Price'; ?>
                                        </button>
                                    </form>
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
