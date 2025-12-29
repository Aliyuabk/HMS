<?php
session_start();
include('include/config.php');

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../index.php");
    exit;
}

$success = "";
$error = "";

if(isset($_POST['submit'])){
    $room_name = mysqli_real_escape_string($con, $_POST['room_name']);
    $room_gender = mysqli_real_escape_string($con, $_POST['role']);
    $max_bed = intval($_POST['max_bed']);

    if($room_name && $room_gender && $max_bed > 0){

        // Insert into rooms table
        $insert_room = mysqli_query($con, "INSERT INTO rooms (room_name, room_gender, max_bed) VALUES ('$room_name', '$room_gender', '$max_bed')");

        if($insert_room){
            $room_id = mysqli_insert_id($con);

            // Create bed rows automatically
            for($i = 1; $i <= $max_bed; $i++){
                mysqli_query($con, "INSERT INTO beds (room_id, bed_number) VALUES ('$room_id', '$i')");
            }

            $success = "Room added successfully with $max_bed beds.";
        } else {
            $error = "Error adding room. Please try again.";
        }
    } else {
        $error = "Please fill all fields correctly.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin | Add Room</title>
    <?php include 'include/css.php';?>
</head>
<body>
<div id="app">
<?php include('include/sidebar.php');?>
<div class="app-content">
<?php include('include/header.php');?>

<div class="main-content">
<div class="wrap-content container" id="container">

    <!-- PAGE TITLE -->
    <section id="page-title">
        <div class="row">
            <div class="col-sm-8">
                <h1 class="mainTitle">Admin | Add Room</h1>
            </div>
            <ol class="breadcrumb">
                <li><span>Admin</span></li>
                <li class="active"><span>Add Rooms</span></li>
            </ol>
        </div>
    </section>

    <!-- ALERTS -->
    <?php if($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <!-- ADD ROOM FORM -->
    <div class="container-fluid container-fullw bg-white">
        <div class="row">
            <div class="col-md-8 col-lg-8">
                <div class="panel panel-white">
                    <div class="panel-heading">
                        <h5 class="panel-title">Add Room</h5>
                    </div>
                    <div class="panel-body">
                        <form method="post">
                            <div class="form-group">
                                <label>Room Name</label>
                                <input type="text" name="room_name" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Room Gender</label>
                                <select name="role" class="form-control" required>
                                    <option value="">Select Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Beds Number</label>
                                <input type="number" name="max_bed" class="form-control" min="1" required>
                            </div>
                            <button type="submit" name="submit" class="btn btn-primary">Add Room</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
</div>
</div>

<?php include('include/footer.php');?>
<?php include('include/setting.php');?>
<?php include('include/js.php');?>
</body>
</html>
