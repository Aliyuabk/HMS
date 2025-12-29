<?php
session_start();
require_once "config/database.php";

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {

    $phone    = trim($_POST['phone']);
    $password = $_POST['password'];

    if ($phone === '' || $password === '') {
        $error = "All fields are required.";
    } else {

        // Role => Table mapping
        $roles = [
            'admin'     => 'admin',
            'doctor'    => 'doctor',
            'reception' => 'reception',
            'billing'   => 'billing',
            'lab'       => 'lab',
            'radiology' => 'radiology',
            'pharmacy'  => 'pharmacy'
        ];

        foreach ($roles as $role => $table) {

            $sql = "SELECT id, phone, password 
                    FROM `$table` 
                    WHERE phone = ? AND status = 'Active' 
                    LIMIT 1";

            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                continue; // skip broken table safely
            }

            $stmt->bind_param("s", $phone);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows === 1) {

                $user = $result->fetch_assoc();

                if (password_verify($password, $user['password'])) {

                    session_regenerate_id(true);

                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['phone']   = $user['phone'];
                    $_SESSION['role']    = $role;

                    // Redirect by role
                    switch ($role) {
                        case 'admin':
                            header("Location: admin/");
                            break;
                        case 'doctor':
                            header("Location: doctor/dashboard.php");
                            break;
                        case 'reception':
                            header("Location: reception/reception-dashboard.php");
                            break;
                        case 'billing':
                            header("Location: billing/index.php");
                            break;
                        case 'lab':
                            header("Location: lab/");
                            break;
                        case 'pharmacy':
                            header("Location: pharmacy/index.php");
                            break;
                         case 'radiology':
                            header("Location: radiology/index.php");
                            break;
                    }
                    exit;
                }
            }

            $stmt->close();
        }

        $error = "Invalid phone number or password.";
    }
}
?>
<!doctype html>
<html lang="en" dir="ltr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<title>MediCore | Sign In</title>

<!-- Favicon -->
<link rel="shortcut icon" href="favicon.ico">

<!-- Library / Plugin Css Build -->
<link rel="stylesheet" href="assets/css/core/libs.min.css">

<!-- Hope Ui Design System Css -->
<link rel="stylesheet" href="assets/css/hope-ui.min.css?v=4.0.0">

<!-- Custom Css -->
<link rel="stylesheet" href="assets/css/custom.min.css?v=4.0.0">

<!-- Dark Css -->
<link rel="stylesheet" href="assets/css/dark.min.css">

<!-- Customizer Css -->
<link rel="stylesheet" href="assets/css/customizer.min.css">

<!-- RTL Css -->
 <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
<link rel="stylesheet" href="assets/css/rtl.min.css">

</head>
<body class=" " data-bs-spy="scroll" data-bs-target="#elements-section" data-bs-offset="0" tabindex="0">

<div class="wrapper">
  <section class="login-content">
    <div class="row m-0 align-items-center bg-white vh-100">

      <div class="col-md-6 d-md-block d-none bg-success p-0 mt-n1 vh-100 overflow-hidden">
        <img src="assets/images/login.jpg" class="img-fluid gradient-main animated-scaleX" alt="images">
      </div>
      
      <!-- Form Section -->
      <div class="col-md-6">
        <div class="row justify-content-center">
          <div class="col-md-10">
            <div class="card card-transparent shadow-none d-flex justify-content-center mb-0 auth-card">
              <div class="card-body">
                <a href="#" class="navbar-brand d-flex align-items-center mb-3">   
                 <!-- <center><h2 class="logo-title ms-3">🏥 MediCore</h2></center>  -->
                </a>
                <h2 class="mb-2 text-center">MediCore</h2>
                <p class="text-center">Login to stay connected.</p>
                   <?php if(!empty($error)) : ?>
                    <div class="alert alert-danger mt-4"> 🏥 <?php echo htmlspecialchars($error); ?></div>
                  <?php endif; ?>
                <form method="POST" autocomplete="off">
                  <div class="row">
                    <div class="col-lg-12">
                      <div class="form-group">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="text" class="form-control" id="phone" name="phone" placeholder="Enter your phone number" required>
                      </div>
                    </div>
                    <div class="col-lg-12">
                      <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                      </div>
                    </div>
                    <div class="col-lg-12 d-flex justify-content-between">
                      <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="customCheck1" name="remember">
                        <label class="form-check-label" for="customCheck1">Remember Me</label>
                      </div>
                      <a href="recoverpw.html">Forgot Password?</a>
                    </div>
                  </div>

                 

                  <div class="d-flex justify-content-center">
                    <button type="submit" class="btn btn-primary" name="login">Sign In</button>
                  </div>
                </form>

              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Image Section -->

    </div>
  </section>
</div>

<!-- Library Bundle Script -->
<script src="assets/js/core/libs.min.js"></script>
<script src="assets/js/core/external.min.js"></script>
<script src="assets/js/charts/widgetcharts.js"></script>
<script src="assets/js/charts/vectore-chart.js"></script>
<script src="assets/js/charts/dashboard.js"></script>
<script src="assets/js/plugins/fslightbox.js"></script>
<script src="assets/js/plugins/setting.js"></script>
<script src="assets/js/plugins/slider-tabs.js"></script>
<script src="assets/js/plugins/form-wizard.js"></script>
<script src="assets/js/hope-ui.js" defer></script>

</body>
</html>
