<?php
session_start();
include('include/config.php');

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../index.php");
    exit;
}

$id = intval($_GET['id']);

mysqli_query($con, "DELETE FROM ehr_fees WHERE id=$id");

$_SESSION['success'] = "Fees deleted successfully";
header("Location: manage-fees.php");
exit;
