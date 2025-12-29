<?php
session_start();
include('include/config.php');

$data = json_decode(file_get_contents('php://input'), true);

$patient_id   = intval($data['patient_id']);
$table        = $data['table'] ?? '';
$created_date = $data['created_date'] ?? '';

// Validate table
if(!in_array($table, ['payment_request','pharmacy_payment','lab_payment_request'])){
    echo json_encode(['success'=>false, 'message'=>'Invalid table']);
    exit;
}

// Sanitize created_date
$created_date_safe = mysqli_real_escape_string($con, $created_date);

// Update the table to mark as paid
$sql = "UPDATE $table SET status='paid' WHERE patient_id=$patient_id AND DATE(created_at)='$created_date_safe'";
$res = mysqli_query($con, $sql);

if(!$res){
    echo json_encode(['success'=>false, 'message'=>mysqli_error($con)]);
    exit;
}

echo json_encode(['success'=>true]);
?>
