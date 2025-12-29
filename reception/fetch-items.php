<?php
include('include/config.php');

if(isset($_POST['dept_id'])){
    $dept_id = (int)$_POST['dept_id'];

    $items = mysqli_query($con, "SELECT * FROM ehr_fees WHERE dept='$dept_id' ORDER BY item_name ASC");

    echo '<option value="">Select Item</option>';
    while($row = mysqli_fetch_assoc($items)){
        echo '<option value="'.$row['id'].'">'.htmlentities($row['item_name']).' - ₦'.$row['price'].'</option>';
    }
}
