<?php
session_start();
include('include/config.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pharmacy') {
    header("Location: ../index.php");
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) die("Invalid prescription ID");

$id = (int)$_GET['id'];

$check = mysqli_query($con, "SELECT * FROM prescriptions WHERE id=$id AND status='pending'");
if (mysqli_num_rows($check) == 0) die("Prescription already processed or not found");

/* Cancel prescription */
$cancel = mysqli_query($con, "UPDATE prescriptions SET status='cancelled' WHERE id=$id");
if (!$cancel) die("Failed to cancel prescription: " . mysqli_error($con));

/* Log action */
$ip = $_SERVER['REMOTE_ADDR'];
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];
$action = "Cancelled prescription ID $id";

mysqli_query($con, "
    INSERT INTO user_log(user_id,user_role,action,reference_id,ip_address)
    VALUES ('$user_id','$user_role','$action','$id','$ip')
");

header("Location: prescriptions-pending.php?success=cancelled");
exit;
