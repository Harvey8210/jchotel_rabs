<!-- Sidebar -->
<div class="col-md-3 col-lg-2 px-0 position-fixed sidebar">
    <div class="p-3">
    </div>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'admin.php' ? 'active' : ''; ?>" href="admin.php" data-title="Dashboard">
                <i class="fas fa-tachometer-alt me-2"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>" href="users.php" data-title="Users">
                <i class="fas fa-users me-2"></i>
                <span>Users</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'rooms.php' ? 'active' : ''; ?>" href="rooms.php" data-title="Rooms">
                <i class="fas fa-bed me-2"></i>
                <span>Rooms</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'reservations.php' ? 'active' : ''; ?>" href="reservations.php" data-title="Reservations">
                <i class="fas fa-calendar-check me-2"></i>
                <span>Reservations</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'billing.php' ? 'active' : ''; ?>" href="billing.php" data-title="Billing">
                <i class="fas fa-file-invoice-dollar me-2"></i>
                <span>Billing</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>" href="profile.php" data-title="Profile">
                <i class="fas fa-user-cog me-2"></i>
                <span>Profile</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="logout.php" data-title="Logout">
                <i class="fas fa-sign-out-alt me-2"></i>
                <span>Logout</span>
            </a>
        </li>
    </ul>
</div> 