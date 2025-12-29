<?php
session_start();
include('include/config.php');

// Restrict access to admin only
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../index.php");
    exit;
}

// Get department ID
if(isset($_GET['delete']) && !empty($_GET['delete'])){
    $id = intval($_GET['delete']);

    // Delete department
    $stmt = $con->prepare("DELETE FROM department WHERE id = ?");
    $stmt->bind_param("i", $id);

    if($stmt->execute()){
        $_SESSION['success'] = "Department deleted successfully!";
    } else {
        $_SESSION['error'] = "Error deleting department.";
    }
    $stmt->close();
}

// Redirect back to manage page
header("Location: manage-department.php");
exit;
