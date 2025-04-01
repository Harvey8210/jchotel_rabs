<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top">
    <div class="container-fluid">
        <!-- Toggle Sidebar Button -->
        <button class="btn btn-link" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Brand -->
        <a class="navbar-brand ms-2" href="admin.php">JC Hotel Admin</a>

        <!-- Right Side Items -->
        <div class="ms-auto d-flex align-items-center">
            <!-- Notifications -->
            <div class="dropdown me-3">
                <a class="nav-link position-relative" href="#" role="button" data-bs-toggle="dropdown">
                    <i class="fas fa-bell"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        3
                    </span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="#">New reservation</a></li>
                    <li><a class="dropdown-item" href="#">Room status update</a></li>
                    <li><a class="dropdown-item" href="#">System notification</a></li>
                </ul>
            </div>

            <!-- User Dropdown -->
            <div class="dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                    <i class="fas fa-user-circle me-2"></i>
                    <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Admin'); ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="profile.php">
                            <i class="fas fa-user me-2"></i> Profile
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#">
                            <i class="fas fa-cog me-2"></i> Settings
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item text-danger" href="logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<style>
    /* Navbar Styles */
    .navbar {
        height: 60px;
        z-index: 1030;
    }

    .navbar-brand {
        font-weight: 600;
        color: #343a40;
    }

    #sidebarToggle {
        color: #343a40;
        padding: 0.5rem;
        font-size: 1.25rem;
    }

    #sidebarToggle:hover {
        color: #007bff;
    }

    .nav-link {
        color: #343a40;
        padding: 0.5rem 1rem;
    }

    .nav-link:hover {
        color: #007bff;
    }

    .dropdown-menu {
        border: none;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        border-radius: 0.5rem;
        padding: 0.5rem 0;
    }

    .dropdown-item {
        padding: 0.5rem 1.5rem;
        color: #343a40;
    }

    .dropdown-item:hover {
        background-color: #f8f9fa;
        color: #007bff;
    }

    .dropdown-item i {
        width: 20px;
        text-align: center;
    }

    .dropdown-divider {
        margin: 0.5rem 0;
    }

    .badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
</style>

<script>
    // Toggle Sidebar
    document.getElementById('sidebarToggle').addEventListener('click', function() {
        document.querySelector('.sidebar').classList.toggle('d-none');
    });
</script> 