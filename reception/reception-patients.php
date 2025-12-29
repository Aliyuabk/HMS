<?php
session_start();
include('include/config.php');


$limit = 10; // patients per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

/* =========================
   SEARCH & FILTER LOGIC
========================= */
$where = [];
$params = [];
$types = "";

// Search by EHR or name
if (!empty($_POST['search'])) {
    $where[] = "(ehr_no LIKE ? OR first_name LIKE ? OR last_name LIKE ?)";
    $search = "%" . $_POST['search'] . "%";
    $params = array_merge($params, [$search, $search, $search]);
    $types .= "sss";
}

// Gender filter
if (!empty($_POST['gender'])) {
    $where[] = "gender = ?";
    $params[] = $_POST['gender'];
    $types .= "s";
}

// Date range filter
if (!empty($_POST['date_from']) && !empty($_POST['date_to'])) {
    $where[] = "DATE(created_at) BETWEEN ? AND ?";
    $params[] = $_POST['date_from'];
    $params[] = $_POST['date_to'];
    $types .= "ss";
}
$countSql = "SELECT COUNT(*) total FROM patients";
if ($where) {
    $countSql .= " WHERE " . implode(" AND ", $where);
}

$countStmt = $con->prepare($countSql);
if ($params) {
    $countStmt->bind_param(substr($types, 0, strlen($types) - 2), ...array_slice($params, 0, -2));
}
$countStmt->execute();
$totalRows = $countStmt->get_result()->fetch_assoc()['total'];

$totalPages = ceil($totalRows / $limit);


// Fetch patients
$sql = "SELECT * FROM patients";
if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= "ii";


$stmt = $con->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

/* =========================
   DASHBOARD COUNTS
========================= */
$totalPatients = $con->query("SELECT COUNT(*) total FROM patients")->fetch_assoc()['total'];
$malePatients = $con->query("SELECT COUNT(*) total FROM patients WHERE gender='Male'")->fetch_assoc()['total'];
$femalePatients = $con->query("SELECT COUNT(*) total FROM patients WHERE gender='Female'")->fetch_assoc()['total'];
$newThisMonth = $con->query("
    SELECT COUNT(*) total FROM patients 
    WHERE MONTH(created_at)=MONTH(CURRENT_DATE()) 
    AND YEAR(created_at)=YEAR(CURRENT_DATE())
")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Reception | All Patients</title>
		
		<?php include('include/css.php'); ?>
	</head>
	<body>
		<div id="app">		
			<!-- SIDEBAR -->
			<?php include 'include/sidebar.php'; ?>
			<!-- SIDEBAR -->
			
			<!-- MAIN CONTENT -->
			<div class="app-content">
				<!-- start: TOP NAVBAR -->
				<?php include 'include/header.php';?>
						
				<!-- end: TOP NAVBAR -->
				<div class="main-content" >
					<div class="wrap-content container" id="container">
						<!-- start: PAGE TITLE -->
						<section id="page-title">
							<div class="row">
								<div class="col-sm-8">
									<h2 class="mainTitle">Reception | All Patients</h2>
								</div>
								<ol class="breadcrumb">
									<li>
										<span>Reception</span>
									</li>
									<li class="active">
										<span>All Patients</span>
									</li>
								</ol>
							</div>
						</section>
						<!-- end: PAGE TITLE -->
						
						<!-- start: PATIENTS LIST -->
						<div class="container-fluid container-fullw bg-white">
							<div class="row">
								<div class="col-md-12">
									<div class="panel panel-white">
										<div class="panel-heading">
											<h4 class="panel-title">Patient List</h4>
											<div class="panel-tools">
												<a href="reception-add-patient.php" class="btn btn-primary">
													<i class="fa fa-plus"></i> Add New Patient
												</a>
											</div>
										</div>
										<div class="panel-body">
											<div class="row">
												<div class="col-md-12">
													<form method="post" action="">
														<div class="row">
															<div class="col-md-3">
																<div class="form-group">
																	<input type="text" name="search" class="form-control" placeholder="Search by name or MRN">
																</div>
															</div>
															<div class="col-md-2">
																<div class="form-group">
																	<select name="gender" class="form-control">
																		<option value="">All Gender</option>
																		<option value="Male">Male</option>
																		<option value="Female">Female</option>
																	</select>
																</div>
															</div>
															<div class="col-md-3">
																<div class="form-group">
																	<input type="date" name="date_from" class="form-control" placeholder="Registered From">
																</div>
															</div>
															<div class="col-md-3">
																<div class="form-group">
																	<input type="date" name="date_to" class="form-control" placeholder="Registered To">
																</div>
															</div>
															<div class="col-md-1">
																<button type="submit" class="btn btn-primary">
																	<i class="fa fa-search"></i>
																</button>
															</div>
														</div>
													</form>
												</div>
											</div>
											
											<div class="table-responsive">
												<table class="table table-bordered table-hover">
													<thead>
														<tr>
															<th>#</th>
															<th>MRN</th>
															<th>Patient Name</th>
															<th>Gender</th>
															<th>Date of Birth</th>
															<th>Phone</th>
															<th>Email</th>
															<th>Registered Date</th>
															<th>Action</th>
														</tr>
													</thead>
												<tbody>
													<?php
													$i = 1;
													while ($row = $result->fetch_assoc()) {
													?>
													<tr>
														<td><?= $i++; ?></td>
														<td><?= htmlspecialchars($row['ehr_no']); ?></td>
														<td><?= htmlspecialchars($row['first_name'].' '.$row['last_name']); ?></td>
														<td><?= htmlspecialchars($row['gender']); ?></td>
														<td><?= htmlspecialchars($row['dob']); ?></td>
														<td><?= htmlspecialchars($row['phone']); ?></td>
														<td><?= htmlspecialchars($row['email']); ?></td>
														<td><?= date("Y-m-d", strtotime($row['created_at'])); ?></td>
														<td>
															<a href="reception-view-patient.php?id=<?= $row['id']; ?>" class="btn btn-xs btn-info">
																<i class="fa fa-eye"></i>
															</a>
															<a href="reception-edit-patient.php?id=<?= $row['id']; ?>" class="btn btn-xs btn-warning">
																<i class="fa fa-edit"></i>
															</a>
															<a href="reception-add-appointment.php?pid=<?= $row['id']; ?>" class="btn btn-xs btn-success">
																<i class="fa fa-calendar"></i>
															</a>
														</td>
													</tr>
													<?php } ?>
													</tbody>

												</table>
											</div>
											
											<div class="row">
													<div class="col-md-12">
														<div class="text-center">
															<ul class="pagination">

																<!-- Previous -->
																<li class="<?= ($page <= 1) ? 'disabled' : '' ?>">
																	<a href="?page=<?= $page - 1 ?>">«</a>
																</li>

																<!-- Page Numbers -->
																<?php for ($i = 1; $i <= $totalPages; $i++) { ?>
																	<li class="<?= ($page == $i) ? 'active' : '' ?>">
																		<a href="?page=<?= $i ?>"><?= $i ?></a>
																	</li>
																<?php } ?>

																<!-- Next -->
																<li class="<?= ($page >= $totalPages) ? 'disabled' : '' ?>">
																	<a href="?page=<?= $page + 1 ?>">»</a>
																</li>

															</ul>
														</div>
													</div>
												</div>

											</div>
										</div>
									</div>
								</div>
							</div>
							
							<div class="row">
								<div class="col-md-3 col-sm-6">
									<div class="panel panel-white no-radius text-center">
										<div class="panel-body">
											<span class="fa-stack fa-2x"> 
												<i class="fa fa-square fa-stack-2x text-primary"></i> 
												<i class="fa fa-users fa-stack-1x fa-inverse"></i> 
											</span>
											<h2 class="StepTitle">Total Patients</h2>
											<p class="text-large"><?= $totalPatients ?></p>
										</div>
									</div>
								</div>
								
								<div class="col-md-3 col-sm-6">
									<div class="panel panel-white no-radius text-center">
										<div class="panel-body">
											<span class="fa-stack fa-2x"> 
												<i class="fa fa-square fa-stack-2x text-success"></i> 
												<i class="fa fa-male fa-stack-1x fa-inverse"></i> 
											</span>
											<h2 class="StepTitle">Male Patients</h2>
											<p class="text-large"><?= $malePatients ?></p>
										</div>
									</div>
								</div>
								
								<div class="col-md-3 col-sm-6">
									<div class="panel panel-white no-radius text-center">
										<div class="panel-body">
											<span class="fa-stack fa-2x"> 
												<i class="fa fa-square fa-stack-2x text-info"></i> 
												<i class="fa fa-female fa-stack-1x fa-inverse"></i> 
											</span>
											<h2 class="StepTitle">Female Patients</h2>
											<p class="text-large"><?= $femalePatients ?></p>
										</div>
									</div>
								</div>
								
								<div class="col-md-3 col-sm-6">
									<div class="panel panel-white no-radius text-center">
										<div class="panel-body">
											<span class="fa-stack fa-2x"> 
												<i class="fa fa-square fa-stack-2x text-warning"></i> 
												<i class="fa fa-user-plus fa-stack-1x fa-inverse"></i> 
											</span>
											<h2 class="StepTitle">New This Month</h2>
											<p class="text-large"><?= $newThisMonth ?></p>
										</div>
									</div>
								</div>
							</div>
						</div>
						<!-- end: PATIENTS LIST -->
					</div>
				</div>
			</div>
			
			<!-- start: FOOTER -->
			<?php include 'include/footer.php';?>
			<!-- end: FOOTER -->
		
			<!-- start: SETTINGS -->
			<?php include 'include/setting.php';?>		
			<!-- end: SETTINGS -->
		</div>
		
		<?php include('include/js.php'); ?>
	</body>
</html>