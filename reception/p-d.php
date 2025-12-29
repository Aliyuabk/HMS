<?php
include('include/config.php');

if(isset($_POST['ehr_no'])){
    $ehr_no = mysqli_real_escape_string($con, $_POST['ehr_no']);
    $query = mysqli_query($con, "SELECT id FROM patients WHERE ehr_no='$ehr_no' LIMIT 1");

    if(mysqli_num_rows($query) > 0){
        $row = mysqli_fetch_assoc($query);
        echo $row['id']; // patient ID
    } else {
        echo '0'; // not found
    }
}
?>
