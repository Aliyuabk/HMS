<?php
session_start();
include('include/config.php');

if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
    die("Invalid appointment ID");
}

$appt_id = (int)$_GET['id'];

// Delete appointment
$stmt = $con->prepare("DELETE FROM appointments WHERE id=?");
$stmt->bind_param("i", $appt_id);

if($stmt->execute()){
    echo "<script>alert('Appointment deleted successfully');window.location='reception-all-appointments.php';</script>";
} else {
    die("Error deleting appointment: " . $stmt->error);
}
$stmt->close();
?>
