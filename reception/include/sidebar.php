<div class="sidebar app-aside" id="sidebar">
    <div class="sidebar-container perfect-scrollbar">
        <nav>
            <!-- start: MAIN NAVIGATION MENU -->
            <div class="navbar-title">
                <span>Main Navigation</span>
            </div>
            <ul class="main-navigation-menu">

                <!-- Dashboard -->
                <li>
                    <a href="reception-dashboard.php">
                        <div class="item-content">
                            <div class="item-media">
                                <i class="ti-home"></i>
                            </div>
                            <div class="item-inner">
                                <span class="title"> Dashboard </span>
                            </div>
                        </div>
                    </a>
                </li>
				<li>
					<a href="javascript:void(0)" data-toggle="modal" data-target="#doctorSigninModal">
						<div class="item-content">
							<div class="item-media">
								<i class="ti-user"></i>
							</div>
							<div class="item-inner">
								<span class="title"> New Payment </span>
								<i class="icon-arrow"></i>
							</div>
						</div>
					</a>
					<ul class="sub-menu">
						<li><a href="javascript:void(0)" data-toggle="modal" data-target="#doctorSigninModal"><span class="title"> Doctor Signin </span></a></li>
						<li><a href="reception-add-patient.php"><span class="title"> New IGR Payment </span></a></li>
					</ul>
				</li>

				<!-- Patients -->
                <li>
                    <a href="javascript:void(0)">
                        <div class="item-content">
                            <div class="item-media">
                                <i class="ti-user"></i>
                            </div>
                            <div class="item-inner">
                                <span class="title"> Patients </span>
                                <i class="icon-arrow"></i>
                            </div>
                        </div>
                    </a>
                    <ul class="sub-menu">
                        <li><a href="reception-patients.php"><span class="title"> All Patients </span></a></li>
                        <li><a href="reception-add-patient.php"><span class="title"> Add Patient </span></a></li>
                    </ul>
                </li>

                <!-- Appointments -->
                <li>
                    <a href="javascript:void(0)">
                        <div class="item-content">
                            <div class="item-media">
                                <i class="ti-calendar"></i>
                            </div>
                            <div class="item-inner">
                                <span class="title"> Appointments </span>
                                <i class="icon-arrow"></i>
                            </div>
                        </div>
                    </a>
                    <ul class="sub-menu">
                        <li><a href="reception-add-appointment.php"><span class="title"> Add Appointment </span></a></li>
                        <li><a href="reception-today-appointments.php"><span class="title"> Today's Appointments </span></a></li>
                        <li><a href="reception-all-appointments.php"><span class="title"> All Appointments </span></a></li>
                    </ul>
                </li>

                <!-- Patients -->


                <!-- Doctors -->
                <li>
                    <a href="javascript:void(0)">
                        <div class="item-content">
                            <div class="item-media">
                                <i class="ti-id-badge"></i>
                            </div>
                            <div class="item-inner">
                                <span class="title"> Doctors </span>
                                <i class="icon-arrow"></i>
                            </div>
                        </div>
                    </a>
                    <ul class="sub-menu">
                        <li><a href="reception-doctors.php"><span class="title"> All Doctors </span></a></li>
                        <li><a href="reception-doctor-schedule.php"><span class="title"> Doctor Schedule </span></a></li>
                    </ul>
                </li>

                <!-- Room Management -->
                <li>
                    <a href="reception-room-management.php">
                        <div class="item-content">
                            <div class="item-media">
                                <i class="ti-layout-grid2"></i>
                            </div>
                            <div class="item-inner">
                                <span class="title"> Room Management </span>
                            </div>
                        </div>
                    </a>
                </li>

                <!-- Reports -->
                <li>
                    <a href="javascript:void(0)">
                        <div class="item-content">
                            <div class="item-media">
                                <i class="ti-bar-chart"></i>
                            </div>
                            <div class="item-inner">
                                <span class="title"> Reports </span>
                                <i class="icon-arrow"></i>
                            </div>
                        </div>
                    </a>
                    <ul class="sub-menu">
                        <li><a href="reception-appointment-report.php"><span class="title"> Appointment Report </span></a></li>
                        <li><a href="reception-patient-report.php"><span class="title"> Patient Report </span></a></li>
                        <li><a href="reception-billing-report.php"><span class="title"> Billing Report </span></a></li>
                    </ul>
                </li>
                 <li>
                    <a href="doctors.schedule.php">
                        <div class="item-content">
                            <div class="item-media">
                                <i class="ti-settings"></i>
                            </div>
                            <div class="item-inner">
                                <span class="title"> Doctor Schedule </span>
                            </div>
                        </div>
                    </a>
                </li>

                <!-- Profile Settings -->
                <li>
                    <a href="reception-profile.php">
                        <div class="item-content">
                            <div class="item-media">
                                <i class="ti-settings"></i>
                            </div>
                            <div class="item-inner">
                                <span class="title"> Profile Settings </span>
                            </div>
                        </div>
                    </a>
                </li>

            </ul>
            <!-- end: MAIN NAVIGATION MENU -->
        </nav>
    </div>
</div>
<!-- Doctor Signin Modal -->
<div class="modal fade" id="doctorSigninModal" tabindex="-1" role="dialog" aria-labelledby="doctorSigninLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Doctor Signin - Search Patient</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <form id="doctorSigninForm">
          <div class="form-group">
            <label for="ehr_no">Enter Patient EHR No</label>
            <input type="text" id="ehr_no" name="ehr_no" class="form-control" maxlength="6" required>
          </div>
          <div id="signinError" class="text-danger mb-2" style="display:none;"></div>
          <button type="submit" class="btn btn-primary">Search</button>
        </form>
      </div>
    </div>
  </div>
</div>


