<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!-- Sidebar -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php">
        <div class="sidebar-brand-icon">
            <img src="http://localhost/car_rental/img/logo.png" alt="Car Rental Logo" style="width: 40px; height: 40px;">
        </div>
        <div class="sidebar-brand-text mx-3">Car Rental Admin</div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Dashboard -->
    <li class="nav-item <?= ($currentPage == 'index.php') ? 'active' : '' ?>">
        <a class="nav-link" href="index.php">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </li>

    <!-- User Management -->
    <li class="nav-item <?= ($currentPage == 'user.php') ? 'active' : '' ?>">
        <a class="nav-link" href="user.php">
            <i class="fas fa-fw fa-users-cog"></i>
            <span>User Management</span>
        </a>
    </li>

    <!-- Car Management -->
    <li class="nav-item <?= ($currentPage == 'car_list.php') ? 'active' : '' ?>">
        <a class="nav-link" href="car_list.php">
            <i class="fas fa-fw fa-car"></i>
            <span>Car List</span>
        </a>
    </li>


    <!-- Bookings -->
    <li class="nav-item <?= ($currentPage == 'booking_list.php') ? 'active' : '' ?>">
        <a class="nav-link" href="booking_list.php">
            <i class="fas fa-fw fa-calendar-check"></i>
            <span>Bookings</span>
        </a>
    </li>

    <!-- Customers -->
    <li class="nav-item <?= ($currentPage == 'customer_list.php') ? 'active' : '' ?>">
        <a class="nav-link" href="customer_list.php">
            <i class="fas fa-fw fa-users"></i>
            <span>Customers</span>
        </a>
    </li>

    <!-- Payments -->
    <li class="nav-item <?= ($currentPage == 'payments.php') ? 'active' : '' ?>">
        <a class="nav-link" href="payments.php">
            <i class="fas fa-fw fa-credit-card"></i>
            <span>Payments</span>
        </a>
    </li>

    <!-- Reports -->
    <li class="nav-item <?= ($currentPage == 'reports.php') ? 'active' : '' ?>">
        <a class="nav-link" href="reports.php">
            <i class="fas fa-fw fa-chart-line"></i>
            <span>Reports</span>
        </a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider d-none d-md-block">

</ul>
<!-- End of Sidebar -->
