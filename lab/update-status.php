<?php
session_start();
require_once('include/config.php');

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'lab'){
    header("Location: ../index.php");
    exit;
}

$request_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$status = isset($_GET['status']) ? $_GET['status'] : '';

if($request_id && in_array($status, ['pending','in_progress','completed','cancelled'])){
    mysqli_query($con, "
        UPDATE lab_requests 
        SET status='$status', updated_at=NOW() 
        WHERE id='$request_id'
    ");
}

header("Location: index.php");
exit;
?>
