<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . " - JC Hotel" : "JC Hotel"; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 250px;
            --sidebar-collapsed-width: 70px;
            --header-height: 60px;
            --transition-speed: 0.3s;
        }

        body {
            overflow-x: hidden;
        }

        /* Header Styles */
        .navbar {
            height: var(--header-height);
            padding: 0 1rem;
            background: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            z-index: 1030;
            position: fixed;
            top: 0;
            right: 0;
            left: 0;
            transition: left var(--transition-speed) ease;
        }

        .navbar-brand {
            font-weight: 600;
            font-size: 1.25rem;
        }

        .navbar-toggler {
            border: none;
            padding: 0.5rem;
            margin-right: 1rem;
        }

        .navbar-toggler:focus {
            box-shadow: none;
        }

        .notification-badge {
            position: relative;
            top: -8px;
            right: 5px;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: var(--header-height);
            left: 0;
            bottom: 0;
            width: var(--sidebar-width);
            background: #343a40;
            color: white;
            transition: all var(--transition-speed) ease;
            z-index: 1020;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        .sidebar .nav-link {
            color: rgba(255,255,255,.75);
            padding: 0.8rem 1rem;
            display: flex;
            align-items: center;
            transition: all var(--transition-speed) ease;
            white-space: nowrap;
            position: relative;
        }

        .sidebar .nav-link i {
            width: 20px;
            text-align: center;
            margin-right: 10px;
            transition: all var(--transition-speed) ease;
            font-size: 1.2rem;
        }

        .sidebar .nav-link span {
            transition: all var(--transition-speed) ease;
            opacity: 1;
            display: inline-block;
        }

        /* Collapsed Sidebar Styles */
        .sidebar.collapsed .nav-link {
            padding: 0.8rem;
            justify-content: center;
        }

        .sidebar.collapsed .nav-link i {
            margin-right: 0;
            font-size: 1.4rem;
        }

        .sidebar.collapsed .nav-link span {
            display: none;
        }

        /* Tooltip for collapsed sidebar */
        .sidebar.collapsed .nav-link::after {
            content: attr(data-title);
            position: absolute;
            left: 100%;
            top: 50%;
            transform: translateY(-50%);
            background: #343a40;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            font-size: 0.875rem;
            white-space: nowrap;
            opacity: 0;
            visibility: hidden;
            transition: all var(--transition-speed) ease;
            margin-left: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .sidebar.collapsed .nav-link:hover::after {
            opacity: 1;
            visibility: visible;
        }

        .sidebar .nav-link:hover {
            color: rgba(255,255,255,1);
            background: rgba(255,255,255,0.1);
        }

        .sidebar .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.2);
        }

        /* Main Content Styles */
        .main-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--header-height);
            padding: 20px;
            transition: all var(--transition-speed) ease;
            min-height: calc(100vh - var(--header-height));
        }

        .main-content.expanded {
            margin-left: var(--sidebar-collapsed-width);
        }

        /* Card Styles */
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            transition: transform var(--transition-speed) ease;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        /* Table Styles */
        .table {
            margin-bottom: 0;
        }

        .table th {
            border-top: none;
            background: #f8f9fa;
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .main-content.expanded {
                margin-left: 0;
            }

            .navbar {
                left: 0;
            }

            .navbar.expanded {
                left: var(--sidebar-width);
            }
        }

        /* Animation Classes */
        .fade-in {
            animation: fadeIn var(--transition-speed) ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* Custom Scrollbar */
        .sidebar::-webkit-scrollbar {
            width: 5px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(255,255,255,0.1);
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.2);
            border-radius: 5px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(255,255,255,0.3);
        }
        
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
        <button class="navbar-toggler" type="button" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        <a class="navbar-brand" href="admin.php">JC Hotel Admin</a>
        <div class="ms-auto d-flex align-items-center">
            <div class="dropdown me-3">
                <button class="btn btn-link position-relative text-dark text-decoration-none" type="button" id="notificationDropdown" data-bs-toggle="dropdown">
                    <i class="fas fa-bell fa-lg"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notification-badge">
                        3
                    </span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationDropdown">
                    <li><a class="dropdown-item" href="#">New reservation received</a></li>
                    <li><a class="dropdown-item" href="#">Room status updated</a></li>
                    <li><a class="dropdown-item" href="#">System maintenance</a></li>
                </ul>
            </div>
            <div class="dropdown">
                <button class="btn btn-link dropdown-toggle text-dark text-decoration-none" type="button" id="userDropdown" data-bs-toggle="dropdown">
                    <i class="fas fa-user-circle me-2"></i>
                    <?php echo htmlspecialchars($_SESSION["full_name"]); ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                    <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user-cog me-2"></i>Profile</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container-fluid">
        <div class="row"> 