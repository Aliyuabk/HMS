<?php
session_start();
require_once('include/config.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pharmacy') {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$sql = mysqli_query($con, "SELECT * FROM pharmacy WHERE id='$user_id'");
$user_data = mysqli_fetch_array($sql);

/* =========================
   UPDATE PROFILE INFO
========================= */
if (isset($_POST['update_profile'])) {
    $first_name = mysqli_real_escape_string($con, $_POST['first_name']);
    $last_name  = mysqli_real_escape_string($con, $_POST['last_name']);
    $email      = mysqli_real_escape_string($con, $_POST['email']);
    $phone      = mysqli_real_escape_string($con, $_POST['phone']);

    $update = mysqli_query($con, "
        UPDATE pharmacy 
        SET first_name='$first_name', last_name='$last_name', email='$email', phone='$phone' 
        WHERE id='$user_id'
    ");

    if ($update) {
        echo "<script>alert('Profile updated successfully'); window.location='profile.php';</script>";
    } else {
        echo "<script>alert('Error updating profile');</script>";
    }
}

/* =========================
   CHANGE PASSWORD
========================= */
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password     = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (password_verify($current_password, $user_data['password'])) {
        if ($new_password === $confirm_password) {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            mysqli_query($con, "UPDATE pharmacy SET password='$hashed' WHERE id='$user_id'");
            echo "<script>alert('Password changed successfully'); window.location='profile.php';</script>";
        } else {
            echo "<script>alert('Passwords do not match');</script>";
        }
    } else {
        echo "<script>alert('Current password is incorrect');</script>";
    }
}

/* =========================
   UPLOAD PROFILE PHOTO
========================= */
if (isset($_POST['upload_photo'])) {

    if (!empty($_FILES['photo']['name'])) {

        $allowed = ['jpg','jpeg','png'];
        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            echo "<script>alert('Only JPG, JPEG and PNG allowed');</script>";
        } else {

            if (!is_dir('uploads/pharmacy')) {
                mkdir('uploads/pharmacy', 0777, true);
            }

            $new_name = "pharmacy_" . $user_id . "." . $ext;
            $path = "uploads/pharmacy/" . $new_name;

            if (move_uploaded_file($_FILES['photo']['tmp_name'], $path)) {
                mysqli_query($con, "UPDATE pharmacy SET photo='$path' WHERE id='$user_id'");
                echo "<script>alert('Profile photo updated'); window.location='profile.php';</script>";
            } else {
                echo "<script>alert('Photo upload failed');</script>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin | Profile Settings</title>
    <?php include 'include/css.php'; ?>
</head>
<body>
<div id="app">

<?php include 'include/sidebar.php'; ?>

<div class="app-content">
<?php include 'include/header.php'; ?>

<div class="main-content">
<div class="wrap-content container">

<section id="page-title">
<div class="row">
<div class="col-sm-8">
<h2 class="mainTitle">Admin | Profile Settings</h2>
</div>
</div>
</section>

<div class="container-fluid container-fullw bg-white">
<div class="row">

<!-- PROFILE PHOTO -->
<div class="col-md-4">
<div class="panel panel-white">
<div class="panel-heading">
<h5 class="panel-title">Profile Photo</h5>
</div>
<div class="panel-body text-center">

<img src="<?php echo !empty($user_data['photo']) ? $user_data['photo'] : 'assets/images/default-avatar.png'; ?>"
     class="img-circle"
     style="width:150px;height:150px;object-fit:cover;border:3px solid #ddd;">

<form method="post" enctype="multipart/form-data" style="margin-top:15px;">
<input type="file" name="photo" class="form-control" required>
<br>
<button type="submit" name="upload_photo" class="btn btn-primary">
Upload Photo
</button>
</form>

</div>
</div>
</div>

<!-- PROFILE UPDATE -->
<div class="col-md-4">
<div class="panel panel-white">
<div class="panel-heading">
<h5 class="panel-title">Update Profile</h5>
</div>
<div class="panel-body">

<form method="post">
<div class="form-group">
<label>First Name</label>
<input type="text" name="first_name" class="form-control" value="<?php echo $user_data['first_name']; ?>" required>
</div>

<div class="form-group">
<label>Last Name</label>
<input type="text" name="last_name" class="form-control" value="<?php echo $user_data['last_name']; ?>">
</div>

<div class="form-group">
<label>Email</label>
<input type="email" name="email" class="form-control" value="<?php echo $user_data['email']; ?>" required>
</div>

<div class="form-group">
<label>Phone</label>
<input type="text" name="phone" class="form-control" value="<?php echo $user_data['phone']; ?>">
</div>

<button type="submit" name="update_profile" class="btn btn-primary">
Update Profile
</button>
</form>

</div>
</div>
</div>

<!-- PASSWORD -->
<div class="col-md-4">
<div class="panel panel-white">
<div class="panel-heading">
<h5 class="panel-title">Change Password</h5>
</div>
<div class="panel-body">

<form method="post">
<div class="form-group">
<label>Current Password</label>
<input type="password" name="current_password" class="form-control" required>
</div>

<div class="form-group">
<label>New Password</label>
<input type="password" name="new_password" class="form-control" required>
</div>

<div class="form-group">
<label>Confirm Password</label>
<input type="password" name="confirm_password" class="form-control" required>
</div>

<button type="submit" name="change_password" class="btn btn-primary">
Change Password
</button>
</form>

</div>
</div>
</div>

</div>
</div>

</div>
</div>

<?php include 'include/footer.php'; ?>
<?php include 'include/setting.php'; ?>

</div>

<?php include 'include/js.php'; ?>
</body>
</html>
