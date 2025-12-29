<?php
session_start();
include('include/config.php');

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../index.php");
    exit;
}

// Search handling
$search = "";
if(isset($_GET['search'])){
    $search = mysqli_real_escape_string($con, $_GET['search']);
}

// Fetch rooms
$query = "SELECT * FROM rooms";
if($search != ""){
    $query .= " WHERE room_name LIKE '%$search%' OR room_gender LIKE '%$search%'";
}
$result = mysqli_query($con, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin | Manage Room</title>
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
                <h1 class="mainTitle">Admin | Manage Rooms</h1>
            </div>
            <ol class="breadcrumb">
                <li><span>Admin</span></li>
                <li class="active"><span>Manage Rooms</span></li>
            </ol>
        </div>
    </section>


    <!-- ROOMS TABLE -->
    <div class="container-fluid container-fullw bg-white">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-white">
                    <div class="panel-heading">
                        <h5 class="panel-title">Room List</h5>
                    </div>
                        <!-- SEARCH BAR -->

                    <div class="panel-body">
                    <form method="GET" class="form-inline margin-bottom-15">
                        <input type="text" name="search" class="form-control" placeholder="Search by Room Name or Type" value="<?php echo htmlentities($search); ?>">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Search</button>
                        <a href="manage-room.php" class="btn btn-default">Reset</a>
                    </form>
                        <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Room Name</th>
                                        <th>Type</th>
                                        <th>Available Beds</th>
                                        <th>Occupied Beds</th>
                                        <th>Total Beds</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $count = 1;
                                while($row = mysqli_fetch_assoc($result)){
                                    $room_id = $row['id'];
                                    $total_beds = mysqli_num_rows(mysqli_query($con, "SELECT * FROM beds WHERE room_id='$room_id'"));
                                    $occupied_beds = mysqli_num_rows(mysqli_query($con, "SELECT * FROM beds WHERE room_id='$room_id' AND status='occupied'"));
                                    $available_beds = $total_beds - $occupied_beds;
                                    echo "<tr>
                                        <td>{$count}</td>
                                        <td>".htmlentities($row['room_name'])."</td>
                                        <td>".htmlentities($row['room_gender'])."</td>
                                        <td>{$available_beds}</td>
                                        <td>{$occupied_beds}</td>
                                        <td>{$total_beds}</td>
                                        <td>
                                            <a href='edit-room.php?id={$room_id}' class='btn btn-warning btn-sm'><i class='fa fa-edit'></i> </a>                                            
                                            <a href='manage-beds.php?room_id={$room_id}' class='btn btn-info btn-sm'><i class='fa fa-bed'></i> </a>
                                            <a href='manage-room.php?delete={$room_id}' class='btn btn-danger btn-sm' onclick=\"return confirm('Are you sure you want to delete this room?');\"><i class='fa fa-trash'></i> </a>
                                        </td>
                                    </tr>";
                                    $count++;
                                }

                                if(mysqli_num_rows($result) == 0){
                                    echo "<tr><td colspan='7' class='text-center'>No rooms found.</td></tr>";
                                }
                                ?>
                                </tbody>
                            </table>
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

  <?php include 'include/js.php';?>
</body>
</html>
