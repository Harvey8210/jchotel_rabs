<!-- Staff Sidebar -->
<div class="col-md-3 col-lg-2 px-0 position-fixed sidebar">
    <div class="p-3">
        <div class="d-flex align-items-center mb-3">
            <i class="fas fa-hotel me-2 fs-4"></i>
            <h5 class="mb-0">JC Hotel</h5>
        </div>
        <div class="mb-3">
            <small class="text-muted">Welcome, <?php echo htmlspecialchars($_SESSION["full_name"]); ?></small>
        </div>
    </div>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'staff_dashboard.php' ? 'active' : ''; ?>" href="staff_dashboard.php" data-title="Dashboard">
                <i class="fas fa-tachometer-alt me-2"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'add_user.php' ? 'active' : ''; ?>" href="add_user.php" data-title="Users">
                <i class="fas fa-users me-2"></i>
                <span>Users</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'staff_reservations.php' ? 'active' : ''; ?>" href="staff_reservations.php" data-title="Reservations">
                <i class="fas fa-calendar-check me-2"></i>
                <span>Reservations</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'staff_profile.php' ? 'active' : ''; ?>" href="staff_profile.php" data-title="Profile">
                <i class="fas fa-user-cog me-2"></i>
                <span>Profile</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="../admin/logout.php" data-title="Logout">
                <i class="fas fa-sign-out-alt me-2"></i>
                <span>Logout</span>
            </a>
        </li>
    </ul>
</div> 