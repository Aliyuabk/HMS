<?php
include('include/config.php');

if(isset($_POST['room_id'])){
    $room_id = (int)$_POST['room_id']; // cast to int for safety

    $query = mysqli_query($con, "
        SELECT id, bed_number
        FROM beds
        WHERE room_id='$room_id'
        AND status='available'
    ");

    echo '<option value="">Select Bed</option>';

    while($row = mysqli_fetch_assoc($query)){
        echo '<option value="'.$row['id'].'">Bed '.$row['bed_number'].'</option>';
    }
}
?>
