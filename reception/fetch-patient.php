<?php
include('include/config.php');

if(isset($_POST['ehr_no'])){
    $ehr = mysqli_real_escape_string($con, $_POST['ehr_no']);

    $query = mysqli_query($con, "
        SELECT first_name, last_name
        FROM patients
        WHERE ehr_no='$ehr'
        LIMIT 1
    ");

    if(mysqli_num_rows($query) > 0){
        $row = mysqli_fetch_assoc($query);
        echo htmlentities($row['first_name'].' '.$row['last_name']);
    } else {
        echo '';
    }
}
?>
