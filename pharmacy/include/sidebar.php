<div class="sidebar app-aside" id="sidebar">
    <div class="sidebar-container perfect-scrollbar">
        <nav>

            <!-- start: MAIN NAVIGATION MENU -->
            <div class="navbar-title">
                <span>Pharmacy Panel</span>
            </div>

            <ul class="main-navigation-menu">

                <!-- Dashboard -->
                <li>
                    <a href="index.php">
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
					<a href="javascript:void(0)">
						<div class="item-content">
							<div class="item-media">
								<i class="ti-medall"></i>
							</div>
							<div class="item-inner">
								<span class="title"> Manage Drugs </span><i class="icon-arrow"></i>
							</div>
						</div>
					</a>
					<ul class="sub-menu">
						<li>
							<a href="add-drug.php">
								<span class="title"> Add Drug </span>
							</a>
						</li>
						<li>
							<a href="manage-drugs.php">
								<span class="title"> Manage Drugs </span>
							</a>
						</li>
					</ul>
				</li>
				 

                <!-- Prescriptions -->
                <li>
                    <a href="javascript:void(0)">
                        <div class="item-content">
                            <div class="item-media">
                                <i class="ti-files"></i>
                            </div>
                            <div class="item-inner">
                                <span class="title"> Prescriptions </span>
                                <i class="icon-arrow"></i>
                            </div>
                        </div>
                    </a>
                    <ul class="sub-menu">
                        <li>
                            <a href="prescriptions-pending.php">
                                <span class="title"> Pending Prescriptions </span>
                            </a>
                        </li>
                        <li>
                            <a href="prescriptions-completed.php">
                                <span class="title"> Completed Prescriptions </span>
                            </a>
                        </li>
                        <li>
                            <a href="prescriptions-cancelled.php">
                                <span class="title"> Cancelled Prescriptions </span>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Dispense Drugs -->
                <li>
                    <a href="prescriptions-pending.php">
                        <div class="item-content">
                            <div class="item-media">
                                <i class="ti-medall"></i>
                            </div>
                            <div class="item-inner">
                                <span class="title"> Dispense Drugs </span>
                            </div>
                        </div>
                    </a>
                </li>

                <!-- Patients -->
                <li>
                    <a href="patients.php">
                        <div class="item-content">
                            <div class="item-media">
                                <i class="ti-id-badge"></i>
                            </div>
                            <div class="item-inner">
                                <span class="title"> Patients </span>
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
                        <li>
                            <a href="report-daily.php">
                                <span class="title"> Daily Report </span>
                            </a>
                        </li>
                        <li>
                            <a href="report-monthly.php">
                                <span class="title"> Monthly Report </span>
                            </a>
                        </li>
                    </ul>
                </li>
					<?php if ($data['role'] == 'super admn') { ?>
                <!-- Activity Logs -->
                <li>
                    <a href="activity-logs.php">
                        <div class="item-content">
                            <div class="item-media">
                                <i class="ti-notepad"></i>
                            </div>
                            <div class="item-inner">
                                <span class="title"> Activity Logs </span>
                            </div>
                        </div>
                    </a>
                </li>
				<?php } ?>
                <!-- Profile -->
                <li>
                    <a href="profile.php">
                        <div class="item-content">
                            <div class="item-media">
                                <i class="ti-user"></i>
                            </div>
                            <div class="item-inner">
                                <span class="title"> Profile Settings </span>
                            </div>
                        </div>
                    </a>
                </li>
				
                <!-- Logout -->
                <li>
                    <a href="../logout.php">
                        <div class="item-content">
                            <div class="item-media">
                                <i class="ti-power-off"></i>
                            </div>
                            <div class="item-inner">
                                <span class="title"> Logout </span>
                            </div>
                        </div>
                    </a>
                </li>
				 
            </ul>
            <!-- end: MAIN NAVIGATION MENU -->

        </nav>
    </div>
</div>
