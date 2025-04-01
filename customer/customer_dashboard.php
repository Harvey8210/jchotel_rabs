<?php
session_start();
require_once '../config/db_connection.php';
require_once 'functions/customer_functions.php';
require_once 'functions/room_functions.php';

// Check if user is logged in as customer
if (!isset($_SESSION['customer_id']) || $_SESSION['role'] !== 'customer') {
  header("Location: login.php");
  exit();
}

// Get available rooms
$result = getAvailableRooms($conn);

// Get customer profile
$customer_profile = getCustomerProfile($conn, $_SESSION['customer_id']);

// Get customer's active reservations
$active_reservations = getCustomerActiveReservations($conn, $_SESSION['customer_id']);

// Get customer's loyalty points
$loyalty_points = getCustomerLoyaltyPoints($conn, $_SESSION['customer_id']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>JC Hotel</title>
  <meta name="description" content="">
  <meta name="keywords" content="">

  <!-- Favicons -->
  <link href="../img/logo.png" rel="icon">
  <link href="../img/logo.png" rel="apple-touch-icon">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="assets/css/main.css" rel="stylesheet">

  <!-- =======================================================
  * Template Name: Squadfree
  * Template URL: https://bootstrapmade.com/squadfree-free-bootstrap-template-creative/
  * Updated: Aug 07 2024 with Bootstrap v5.3.3
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->
</head>

<body class="index-page">

  <header id="header" class="header d-flex align-items-center fixed-top">
    <div class="container-fluid container-xl position-relative d-flex align-items-center justify-content-between">

      <a href="index.html" class="logo d-flex align-items-center">
        <h1 class="sitename">JC HOTEL</h1>
      </a>

      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="#hero" class="active">Home</a></li>
          <li><a href="#Rooms">Rooms</a></li>
          <li><a href="#about">About</a></li>
          <li><a href="#teams">Teams</a></li>
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-person-circle "></i> <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'User'); ?>
            </a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="profile.php">Profile</a></li>
              <li><a class="dropdown-item" href="my_bookings.php">My Bookings</a></li>
              <li>
                <hr class="dropdown-divider">
              </li>
              <li><a class="dropdown-item" href="logout.php">Logout</a></li>
            </ul>
          </li>
          <li>
            <a href="#" class="cart-icon position-relative">
              <i class="bi bi-cart3"></i>
              <span class="cart-badge position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                0
              </span>
            </a>
          </li>
        </ul>
      </nav>

      <i class="mobile-nav-toggle d-xl-none bi bi-list" data-bs-toggle="offcanvas" data-bs-target="#mobileNav">
      </i>

      <!-- Mobile Navigation -->
      <div class="offcanvas offcanvas-end" tabindex="-1" id="mobileNav">
        <div class="offcanvas-header">
          <div class="user-info">
            <i class="bi bi-person-circle"></i>
            <span><?php echo htmlspecialchars($_SESSION['full_name'] ?? 'User'); ?></span>
          </div>
        </div>
        <div class="offcanvas-body">
          <nav class="mobile-nav">
            <ul class="mobile-nav-list">
              <li><a href="customer_dashboard.php">Home</a></li>
              <li><a href="customer_dashboard.php#Rooms">Rooms</a></li>
              <li><a href="customer_dashboard.php#about">About</a></li>
              <li><a href="customer_dashboard.php#teams">Teams</a></li>
              <li><a href="profile.php"><i class="bi bi-person"></i> Profile</a></li>
              <li><a href="my_bookings.php"><i class="bi bi-calendar-check"></i> My Bookings</a></li>
              <li class="logout-item"><a href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
            </ul>
          </nav>
        </div>
      </div>

    </div>
  </header>

  <style>
    /* Mobile Navigation Styles */
    .mobile-nav-toggle {
      font-size: 28px;
      cursor: pointer;
      display: none;
      line-height: 0;
      transition: 0.5s;
      position: fixed;
      right: 15px;
      top: 20px;
      z-index: 9999;
      color: #fff;
      background: transparent;
      border: none;
      padding: 5px;
    }

    /* Enhanced Offcanvas Styles */
    .offcanvas {
      background: linear-gradient(135deg, #1b5e20 0%, #2e7d32 100%);
      backdrop-filter: blur(10px);
      border-left: 1px solid rgba(255, 255, 255, 0.1);
      z-index: 9998;
    }

    .offcanvas-header {
      background: rgba(0, 0, 0, 0.2);
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      padding: 1.5rem;
    }

    .user-info {
      display: flex;
      align-items: center;
      gap: 10px;
      color: #fff;
      margin-top: 10px;
      padding: 10px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 8px;
    }

    .user-info i {
      font-size: 1.5rem;
      color: #ffc107;
    }

    .user-info span {
      font-size: 1rem;
      font-weight: 500;
    }

    .offcanvas-title {
      color: #fff;
      font-size: 1.5rem;
      font-weight: 600;
      margin: 0;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    .offcanvas-body {
      padding: 2rem 1.5rem;
      background: transparent;
    }

    .mobile-nav {
      padding: 0;
    }

    .mobile-nav-list {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    .mobile-nav-list li {
      margin: 0;
      opacity: 0;
      transform: translateX(20px);
      animation: slideIn 0.3s ease forwards;
    }

    .mobile-nav-list li:nth-child(1) {
      animation-delay: 0.1s;
    }

    .mobile-nav-list li:nth-child(2) {
      animation-delay: 0.2s;
    }

    .mobile-nav-list li:nth-child(3) {
      animation-delay: 0.3s;
    }

    .mobile-nav-list li:nth-child(4) {
      animation-delay: 0.4s;
    }

    .mobile-nav-list li:nth-child(5) {
      animation-delay: 0.5s;
    }

    .mobile-nav-list li:nth-child(6) {
      animation-delay: 0.6s;
    }

    .mobile-nav-list li:nth-child(7) {
      animation-delay: 0.7s;
    }

    @keyframes slideIn {
      to {
        opacity: 1;
        transform: translateX(0);
      }
    }

    .mobile-nav-list a {
      color: #fff;
      text-decoration: none;
      font-size: 1.1rem;
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 1rem 1.5rem;
      margin: 0.5rem 0;
      border-radius: 10px;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }

    .mobile-nav-list a i {
      font-size: 1.2rem;
      color: #ffc107;
    }

    .mobile-nav-list a::before {
      content: '';
      position: absolute;
      left: 0;
      top: 0;
      height: 100%;
      width: 0;
      background: rgba(255, 255, 255, 0.1);
      transition: width 0.3s ease;
    }

    .mobile-nav-list a:hover::before {
      width: 100%;
    }

    .mobile-nav-list a:hover {
      color: #ffc107;
      transform: translateX(10px);
    }

    .mobile-nav-list a.active {
      background: rgba(255, 255, 255, 0.1);
      color: #ffc107;
      font-weight: 600;
    }

    .logout-item {
      margin-top: 20px;
      border-top: 1px solid rgba(255, 255, 255, 0.1);
      padding-top: 20px;
    }

    .logout-item a {
      color: #ff4444;
    }

    .logout-item a:hover {
      color: #ff0000;
    }

    .logout-item a i {
      color: #ff4444;
    }

    .mobile-nav-toggle:hover {
      color: #ffc107;
    }

    @media (max-width: 991px) {
      .mobile-nav-toggle {
        display: block;
      }

      .navmenu {
        display: none;
      }
    }

    /* Enhanced Dropdown Styles */
    .dropdown-toggle {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 8px 15px;
      border-radius: 25px;
      background: rgba(255, 255, 255, 0.1);
      transition: all 0.3s ease;
    }

    .dropdown-toggle:hover {
      background: rgba(255, 255, 255, 0.2);
    }

    .dropdown-toggle i {
      font-size: 1.2rem;
      color: #ffc107;
    }

    .dropdown-menu {
      background: rgba(0, 0, 0, 0.9);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 10px;
      padding: 8px 0;
      min-width: 200px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    }

    .dropdown-item {
      color: #fff;
      padding: 10px 20px;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .dropdown-item:hover {
      background: rgba(255, 255, 255, 0.1);
      color: #ffc107;
    }

    .dropdown-item i {
      font-size: 1.1rem;
    }

    .dropdown-divider {
      border-color: rgba(255, 255, 255, 0.1);
      margin: 8px 0;
    }

    /* Room Image Styles */
    .portfolio-item {
      position: relative;
      overflow: hidden;
      border-radius: 8px;
      box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
    }

    .portfolio-item img {
      width: 100%;
      height: 300px;
      /* Fixed height for all images */
      object-fit: cover;
      /* This ensures the image covers the area without distortion */
      transition: transform 0.3s ease;
    }

    .portfolio-item:hover img {
      transform: scale(1.05);
      /* Slight zoom effect on hover */
    }

    .portfolio-info {
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      background: rgba(0, 0, 0, 0.7);
      padding: 20px;
      color: white;
      transform: translateY(100%);
      transition: transform 0.3s ease;
    }

    .portfolio-item:hover .portfolio-info {
      transform: translateY(0);
    }

    .portfolio-info h4 {
      margin: 0 0 10px 0;
      font-size: 1.2rem;
    }

    .portfolio-info p {
      margin: 0;
      font-size: 0.9rem;
    }

    .portfolio-info .price {
      font-size: 1.1rem;
      font-weight: bold;
      color: #ffc107;
      margin: 10px 0;
    }

    .portfolio-info a {
      color: white;
      margin-right: 15px;
      font-size: 1.2rem;
      transition: color 0.3s ease;
    }

    .portfolio-info a:hover {
      color: #ffc107;
    }

    /* Isotope Layout Adjustments */
    .isotope-container {
      margin-top: 30px;
    }

    /* Quick Booking Widget Styles */
    .quick-booking {
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
      border-bottom: 1px solid rgba(0,0,0,0.1);
    }

    .booking-widget {
      transition: all 0.3s ease;
    }

    .booking-widget:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }

    .booking-widget h3 {
      color: #1b5e20;
      font-size: 1.5rem;
      font-weight: 600;
    }

    .booking-widget .form-label {
      color: #495057;
      font-weight: 500;
    }

    .booking-widget .form-control {
      border: 2px solid #dee2e6;
      padding: 0.75rem;
      border-radius: 8px;
      transition: all 0.3s ease;
    }

    .booking-widget .form-control:focus {
      border-color: #1b5e20;
      box-shadow: 0 0 0 0.2rem rgba(27, 94, 32, 0.25);
    }

    .booking-widget .btn-primary {
      background-color: #1b5e20;
      border-color: #1b5e20;
      padding: 0.75rem;
      border-radius: 8px;
      font-weight: 500;
      transition: all 0.3s ease;
    }

    .booking-widget .btn-primary:hover {
      background-color: #2e7d32;
      border-color: #2e7d32;
      transform: translateY(-2px);
    }

    @media (max-width: 768px) {
      .booking-widget {
        margin: 0 15px;
      }
      
      .booking-widget .col-md-2 {
        margin-top: 1rem;
      }
    }

    /* Enhanced Room Status Dropdown */
    .form-select-sm {
      padding: 8px 30px 8px 12px;
      font-size: 0.875rem;
      border-radius: 20px;
      border: 2px solid #dee2e6;
      background-color: #fff;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .form-select-sm:focus {
      border-color: #ffc107;
      box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
    }

    .form-select-sm option {
      padding: 8px;
    }

    /* Status-specific colors */
    .form-select-sm option[value="available"] {
      color: #28a745;
      font-weight: 500;
    }

    .form-select-sm option[value="occupied"] {
      color: #dc3545;
      font-weight: 500;
    }

    .form-select-sm option[value="reserved"] {
      color: #ffc107;
      font-weight: 500;
    }

    .form-select-sm option[value="maintenance"] {
      color: #6c757d;
      font-weight: 500;
    }

    /* Active state for status dropdown */
    .form-select-sm:focus option:checked {
      background: linear-gradient(0deg, #ffc107 0%, #ffc107 100%);
      color: #fff;
    }

    /* Cart Preview Styles */
    .cart-preview {
      position: fixed;
      right: -350px; /* Start off-screen */
      top: 80px;
      width: 350px;
      background: white;
      border-radius: 10px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
      z-index: 1000;
      transition: right 0.3s ease;
    }

    .cart-preview.show {
      right: 20px;
    }

    .cart-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px;
      border-bottom: 1px solid #eee;
    }

    .cart-header h5 {
      margin: 0;
      color: #1b5e20;
    }

    .cart-count {
      background: #1b5e20;
      color: white;
      padding: 2px 8px;
      border-radius: 50%;
      font-size: 0.8rem;
    }

    .cart-items {
      max-height: 300px;
      overflow-y: auto;
      padding: 15px;
    }

    .cart-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 10px 0;
      border-bottom: 1px solid #eee;
    }

    .cart-item-info h6 {
      margin: 0;
      color: #333;
    }

    .cart-item-info p {
      margin: 5px 0 0;
      font-size: 0.9rem;
      color: #666;
    }

    .cart-item-actions {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .cart-item-actions button {
      background: none;
      border: none;
      color: #dc3545;
      cursor: pointer;
      padding: 5px;
      transition: all 0.3s ease;
    }

    .cart-item-actions button:hover {
      color: #c82333;
    }

    .cart-footer {
      padding: 15px;
      border-top: 1px solid #eee;
    }

    .cart-total {
      font-weight: bold;
      margin-bottom: 10px;
      color: #1b5e20;
    }

    #proceedToCheckout {
      width: 100%;
      background: #1b5e20;
      border: none;
    }

    #proceedToCheckout:hover {
      background: #2e7d32;
    }

    .cart-icon {
      text-decoration: none;
      padding: 5px;
      transition: all 0.3s ease;
    }

    .cart-icon:hover {
      transform: scale(1.1);
    }

    .cart-badge {
      font-size: 0.7rem;
      transform: translate(-50%, -50%) !important;
    }

    @media (max-width: 768px) {
      .cart-preview {
        width: 100%;
        right: -100%;
        top: 70px;
        border-radius: 0;
      }

      .cart-preview.show {
        right: 0;
      }
    }

    /* Enhanced unified styles for portfolio links */
    .portfolio-info .portfolio-links {
      position: absolute;
      right: 15px;
      top: 15px;
      display: flex;
      gap: 12px;
      z-index: 2;
      flex-direction: column; /* Stack buttons vertically */
    }

    .portfolio-info .portfolio-links a {
      width: 45px;
      height: 45px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: rgba(0, 0, 0, 0.6);
      border-radius: 50%;
      color: rgba(255, 255, 255, 0.9);
      font-size: 1.25rem;
      text-decoration: none;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      border: 2px solid rgba(255, 255, 255, 0.2);
      backdrop-filter: blur(4px);
      -webkit-backdrop-filter: blur(4px);
      overflow: hidden;
      position: relative;
    }

    /* Add specific style for Book Now button */
    .portfolio-info .portfolio-links a.add-to-cart-btn {
      margin-top: 10px; /* Add space between buttons */
    }

    .portfolio-info .portfolio-links a::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(255, 255, 255, 0.1);
      transform: translateY(100%);
      transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      z-index: -1;
    }

    .portfolio-info .portfolio-links a:hover {
      color: #ffc107;
      transform: translateY(-5px);
      border-color: rgba(255, 255, 255, 0.4);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    }

    .portfolio-info .portfolio-links a:hover::before {
      transform: translateY(0);
    }

    .portfolio-info .portfolio-links a:active {
      transform: translateY(-2px);
    }

    .portfolio-info .portfolio-links a i {
      font-size: 1.25rem;
      transition: transform 0.3s ease;
    }

    .portfolio-info .portfolio-links a:hover i {
      transform: scale(1.1);
    }

    /* Add tooltip styles */
    .portfolio-info .portfolio-links a::after {
      content: attr(title);
      position: absolute;
      bottom: -35px;
      left: 50%;
      transform: translateX(-50%);
      background: rgba(0, 0, 0, 0.8);
      color: white;
      padding: 5px 10px;
      border-radius: 4px;
      font-size: 0.75rem;
      white-space: nowrap;
      opacity: 0;
      visibility: hidden;
      transition: all 0.3s ease;
    }

    .portfolio-info .portfolio-links a:hover::after {
      opacity: 1;
      visibility: visible;
      bottom: -40px;
    }

    @media (max-width: 768px) {
      .portfolio-info .portfolio-links {
        right: 10px;
        top: 10px;
        gap: 10px;
      }

      .portfolio-info .portfolio-links a {
        width: 40px;
        height: 40px;
      }

      .portfolio-info .portfolio-links a i {
        font-size: 1.1rem;
      }
    }
  </style>

  <main class="main">

    <!-- Hero Section -->
    <section id="hero" class="hero section accent-background">

      <img src="../img/dashboard.jpg" alt="" data-aos="fade-in">

      <div class="container text-center" data-aos="fade-up" data-aos-delay="100">
        <h2>Welcome to JC Hotel</h2>
        <p>Your stay with us is what we cherish the most.</p>
        <a href="#Rooms" class="btn-scroll" title="Scroll Down"><i class="bi bi-chevron-down"></i></a>
      </div>

    </section><!-- /Hero Section -->

    <!-- Quick Booking Section -->
    <section class="quick-booking py-4 bg-light">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-lg-8">
            <div class="booking-widget p-4 bg-white rounded shadow-sm">
              <h3 class="text-center mb-4">Quick Room Availability Check</h3>
              <form class="row g-3">
                <div class="col-md-5">
                  <label for="quickCheckIn" class="form-label">Check-in Date</label>
                  <input type="date" class="form-control" id="quickCheckIn" required>
                </div>
                <div class="col-md-5">
                  <label for="quickCheckOut" class="form-label">Check-out Date</label>
                  <input type="date" class="form-control" id="quickCheckOut" required>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                  <button type="submit" class="btn btn-primary w-100">Check Availability</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Services Section -->


    <!-- Portfolio Section -->
    <section id="Rooms" class="portfolio section">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h2>Rooms</h2>
        <p>JC Hotel your home away from home.</p>
      </div><!-- End Section Title -->

      <div class="container">

        <div class="isotope-layout" data-default-filter="*" data-layout="masonry" data-sort="original-order">

          <ul class="portfolio-filters isotope-filters" data-aos="fade-up" data-aos-delay="100">
            <li data-filter="*" class="filter-active">All</li>
            <li data-filter=".filter-standard">Standard</li>
            <li data-filter=".filter-deluxe">Deluxe</li>
            <li data-filter=".filter-superior">Superior</li>
            <li data-filter=".filter-suite">Suite</li>
          </ul><!-- End Portfolio Filters -->

          <div class="row gy-4 isotope-container" data-aos="fade-up" data-aos-delay="200">
            <?php
            if ($result->num_rows > 0) {
              while ($room = $result->fetch_assoc()) {
                $roomInfo = getRoomTypeInfo($room['type']);
                $imagePath = getRoomImagePath($room['image']);
            ?>
                <div class="col-lg-4 col-md-6 portfolio-item isotope-item <?php echo $roomInfo['filter_class']; ?>">
                  <img src="<?php echo htmlspecialchars($imagePath); ?>" class="img-fluid" alt="<?php echo htmlspecialchars($roomInfo['display_name']); ?> Room">
                  <div class="portfolio-info">
                    <h4>Room <?php echo htmlspecialchars($room['room_number']); ?> - <?php echo htmlspecialchars($roomInfo['display_name']); ?></h4>
                    <p><?php echo htmlspecialchars($room['description']); ?></p>
                    <p class="price">₱<?php echo number_format($room['price'], 2); ?> / night</p>
                    <div class="portfolio-links">
                      <a href="<?php echo htmlspecialchars($imagePath); ?>"
                        title="<?php echo htmlspecialchars($roomInfo['display_name']); ?> Room"
                        data-gallery="portfolio-gallery-room"
                        class="glightbox preview-link">
                        <i class="bi bi-zoom-in"></i>
                      </a>
                      <a href="#" 
                        class="add-to-cart-btn"
                        data-room-id="<?php echo htmlspecialchars($room['room_id']); ?>"
                        data-room-number="<?php echo htmlspecialchars($room['room_number']); ?>"
                        data-room-type="<?php echo htmlspecialchars($room['type']); ?>"
                        data-room-price="<?php echo htmlspecialchars($room['price']); ?>"
                        title="Book Now">
                        <i class="bi bi-calendar-check"></i>
                      </a>
                    </div>
                  </div>
                </div><!-- End Portfolio Item -->
            <?php
              }
            } else {
              echo '<div class="col-12 text-center"><p>No rooms available at the moment.</p></div>';
            }
            ?>
          </div><!-- End Portfolio Container -->

        </div>

      </div>

    </section><!-- /Portfolio Section -->

    <!-- About Section -->
    <section id="about" class="about section">

      <div class="container">

        <div class="row gy-5">

          <div class="content col-xl-5 d-flex flex-column" data-aos="fade-up" data-aos-delay="100">
            <h3>Welcome to JC Hotel's Reservation and Billing System</h3>
            <p>
              Experience seamless room reservations and hassle-free billing with our advanced hotel management system. We've designed everything to make your stay as comfortable as possible, from booking to checkout.
            </p>
            <a href="#Rooms" class="about-btn align-self-center align-self-xl-start"><span>Book Now</span> <i class="bi bi-chevron-right"></i></a>
          </div>

          <div class="col-xl-7" data-aos="fade-up" data-aos-delay="200">
            <div class="row gy-4">

              <div class="col-md-6 icon-box position-relative">
                <i class="bi bi-calendar-check"></i>
                <h4><a href="" class="stretched-link">Easy Room Reservation</a></h4>
                <p>Book your preferred room type instantly with our user-friendly reservation system. Choose from Standard, Deluxe, Superior, or Suite rooms.</p>
              </div><!-- Icon-Box -->

              <div class="col-md-6 icon-box position-relative">
                <i class="bi bi-credit-card"></i>
                <h4><a href="" class="stretched-link">Secure Payment System</a></h4>
                <p>Our secure payment system accepts cash payments at the front desk and GCash. All transactions are processed through our integrated billing system with real-time payment tracking.</p>
              </div><!-- Icon-Box -->

              <div class="col-md-6 icon-box position-relative">
                <i class="bi bi-gift"></i>
                <h4><a href="" class="stretched-link">Loyalty Rewards Program</a></h4>
                <p>Earn points with every stay and redeem them for discounts on future bookings. Join our loyalty program for exclusive benefits.</p>
              </div><!-- Icon-Box -->

              <div class="col-md-6 icon-box position-relative">
                <i class="bi bi-receipt"></i>
                <h4><a href="" class="stretched-link">Digital Billing & Statements</a></h4>
                <p>Receive instant digital billing statements and track your payment history. All transactions are securely recorded and easily accessible.</p>
              </div><!-- Icon-Box -->

            </div>
          </div>

        </div>

      </div>

    </section><!-- /About Section -->



    <!-- Team Section -->
    <section id="teams" class="team teams">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h2>Team</h2>
        <p>Ca-ay and Friends</p>
      </div><!-- End Section Title -->

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row gy-5">

          <div class="col-xl-4 col-md-6 d-flex" data-aos="zoom-in" data-aos-delay="200">
            <div class="team-member">
              <div class="member-img">
                <img src="../img/teams/izagani.jpg" class="img-fluid" alt="">
              </div>
              <div class="member-info">
                <div class="social">
                  <a href=""><i class="bi bi-twitter-x"></i></a>
                  <a href="https://www.facebook.com/zan0514"><i class="bi bi-facebook"></i></a>
                  <a href=""><i class="bi bi-instagram"></i></a>
                  <a href=""><i class="bi bi-linkedin"></i></a>
                </div>
                <h4>Izagani R. Perez</h4>
                <span>Business Analyst</span>
              </div>
            </div>
          </div><!-- End Team Member -->

          <div class="col-xl-4 col-md-6 d-flex" data-aos="zoom-in" data-aos-delay="400">
            <div class="team-member">
              <div class="member-img">
                <img src="../img/teams/harvey.jpg" class="img-fluid" alt="">
              </div>
              <div class="member-info">
                <div class="social">
                  <a href=""><i class="bi bi-twitter-x"></i></a>
                  <a href="https://www.facebook.com/harvey.pajayao"><i class="bi bi-facebook"></i></a>
                  <a href=""><i class="bi bi-instagram"></i></a>
                  <a href=""><i class="bi bi-linkedin"></i></a>
                </div>
                <h4>Harvey A. Pajayao</h4>
                <span>Developer/Designer</span>
              </div>
            </div>
          </div><!-- End Team Member -->

          <div class="col-xl-4 col-md-6 d-flex" data-aos="zoom-in" data-aos-delay="600">
            <div class="team-member">
              <div class="member-img">
                <img src="../img/teams/marktitus.jpg" class="img-fluid" alt="">
              </div>
              <div class="member-info">
                <div class="social">
                  <a href=""><i class="bi bi-twitter-x"></i></a>
                  <a href="https://www.facebook.com/martitusr.montales.01"><i class="bi bi-facebook"></i></a>
                  <a href=""><i class="bi bi-instagram"></i></a>
                  <a href=""><i class="bi bi-linkedin"></i></a>
                </div>
                <h4>Marktitus R. Montales</h4>
                <span>Project Manager</span>
              </div>
            </div>
          </div><!-- End Team Member -->
          <div class="col-xl-4 col-md-6 d-flex" data-aos="zoom-in" data-aos-delay="600">
            <div class="team-member">
              <div class="member-img">
                <img src="../img/teams/patrick.jpg" class="img-fluid" alt="">
              </div>
              <div class="member-info">
                <div class="social">
                  <a href=""><i class="bi bi-twitter-x"></i></a>
                  <a href="https://www.facebook.com/martitusr.montales.01"><i class="bi bi-facebook"></i></a>
                  <a href=""><i class="bi bi-instagram"></i></a>
                  <a href=""><i class="bi bi-linkedin"></i></a>
                </div>
                <h4>Cymon Patrick J. Ca-ay</h4>
                <span>Tester</span>
              </div>
            </div>
          </div><!-- End Team Member -->
          <div class="col-xl-4 col-md-6 d-flex" data-aos="zoom-in" data-aos-delay="600">
            <div class="team-member">
              <div class="member-img">
                <img src="../img/teams/zoren.jpg" class="img-fluid" alt="">
              </div>
              <div class="member-info">
                <div class="social">
                  <a href=""><i class="bi bi-twitter-x"></i></a>
                  <a href="https://www.facebook.com/martitusr.montales.01"><i class="bi bi-facebook"></i></a>
                  <a href=""><i class="bi bi-instagram"></i></a>
                  <a href=""><i class="bi bi-linkedin"></i></a>
                </div>
                <h4>Zoren K. Empal</h4>
                <span>Maintenance & Support Technician</span>
              </div>
            </div>
          </div><!-- End Team Member -->
        </div>

      </div>

    </section><!-- /Team Section -->




    x

  </main>

  <footer id="footer" class="footer dark-background">

    <div class="container footer-top">
      <div class="row gy-4">
        <div class="col-lg-4 col-md-6 footer-about">
          <a href="index.html" class="logo d-flex align-items-center">
            <span class="sitename">About</span>
          </a>
          <div class="footer-contact pt-3">
            <p>National Highway, Poblacion, Tupi, Philippines</p>
            <p>Philippines, Ph 9504</p>
            <p class="mt-3"><strong>Phone:</strong> <span>
                09123456789</span></p>
            <p><strong>Email:</strong> <span>
                jchotel@gmail.com</span></p>
          </div>
          <div class="social-links d-flex mt-4">
            <a href=""><i class="bi bi-twitter-x"></i></a>
            <a href="https://www.facebook.com/jcjc.hotel"><i class="bi bi-facebook"></i></a>
            <a href=""><i class="bi bi-instagram"></i></a>
            <a href=""><i class="bi bi-linkedin"></i></a>
          </div>
        </div>

        <div class="col-lg-2 col-md-3 footer-links">
          <h4>Useful Links</h4>
          <ul>
            <li><a href="#">Home</a></li>
            <li><a href="#">About us</a></li>
            <li><a href="#">Services</a></li>
            <li><a href="#">Terms of service</a></li>
            <li><a href="#">Privacy policy</a></li>
          </ul>
        </div>

        <div class="col-lg-2 col-md-3 footer-links">
          <h4>Accept booking for</h4>
          <p>Birthday</p>
          <p>Wedding</p>
          <p>Baptismal</p>
          <p>Corporate meeting</p>
          <p>Small gathering</p>
        </div>



      </div>
    </div>



  </footer>

  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Preloader -->
  <div id="preloader"></div>

  <!-- Remove the Booking Modal section and replace with Cart Preview -->
  <div class="cart-preview" id="cartPreview">
    <div class="cart-header">
      <h5>Booking Cart</h5>
      <span class="cart-count">0</span>
    </div>
    <div class="cart-items">
      <!-- Cart items will be dynamically added here -->
    </div>
    <div class="cart-footer">
      <div class="cart-total">Total: ₱<span id="cartTotal">0.00</span></div>
      <a href="checkout.php" class="btn btn-primary" id="proceedToCheckout">Proceed to Checkout</a>
    </div>
  </div>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>
  <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
  <script src="assets/vendor/imagesloaded/imagesloaded.pkgd.min.js"></script>
  <script src="assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>
  <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>

  <!-- Main JS File -->
  <script src="assets/js/main.js"></script>

  <script>
    // Mobile Navigation Toggle
    document.addEventListener('DOMContentLoaded', function() {
      const mobileNavToggle = document.querySelector('.mobile-nav-toggle');
      const mobileNav = document.querySelector('.mobile-nav');
      const currentPath = window.location.pathname;
      const currentHash = window.location.hash;

      // Handle active state for navigation links
      const navLinks = document.querySelectorAll('.mobile-nav-list a');
      navLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href === currentPath || (href.includes(currentHash) && currentHash)) {
          link.classList.add('active');
        }
      });

      // Toggle mobile navigation
      mobileNavToggle.addEventListener('click', function() {
        mobileNav.classList.toggle('show');
        this.classList.toggle('bi-list');
        this.classList.toggle('bi-x');
      });

      // Close mobile navigation when clicking outside
      document.addEventListener('click', function(e) {
        if (!mobileNav.contains(e.target) && !mobileNavToggle.contains(e.target)) {
          mobileNav.classList.remove('show');
          mobileNavToggle.classList.add('bi-list');
          mobileNavToggle.classList.remove('bi-x');
        }
      });

      // Booking Modal Functionality
      const bookingModal = new bootstrap.Modal(document.getElementById('bookingModal'));
      const bookingForm = document.getElementById('bookingForm');
      const roomTypeDisplay = document.getElementById('roomTypeDisplay');
      const roomTypeInput = document.getElementById('roomType');
      const roomNumberInput = document.getElementById('roomNumber');
      const roomIdInput = document.getElementById('roomId');
      const checkInInput = document.getElementById('checkIn');
      const checkOutInput = document.getElementById('checkOut');
      const roomRateSpan = document.getElementById('roomRate');
      const numberOfNightsSpan = document.getElementById('numberOfNights');
      const totalPriceSpan = document.getElementById('totalPrice');
      const confirmBookingBtn = document.getElementById('confirmBooking');

      // Room rates and types mapping
      const roomInfo = {
        standard: { rate: 1500, display: 'Standard Room' },
        deluxe: { rate: 2500, display: 'Deluxe Room' },
        superior: { rate: 3500, display: 'Superior Room' },
        suite: { rate: 5000, display: 'Suite Room' }
      };

      // Set minimum date for check-in to today
      const today = new Date().toISOString().split('T')[0];
      checkInInput.min = today;

      // Update check-out minimum date when check-in changes
      checkInInput.addEventListener('change', function() {
        checkOutInput.min = this.value;
        if (checkOutInput.value && checkOutInput.value < this.value) {
          checkOutInput.value = this.value;
        }
        calculateTotal();
      });

      // Update total when dates change
      checkOutInput.addEventListener('change', calculateTotal);

      function calculateTotal() {
        if (checkInInput.value && checkOutInput.value && roomTypeInput.value) {
          const checkIn = new Date(checkInInput.value);
          const checkOut = new Date(checkOutInput.value);
          const nights = Math.ceil((checkOut - checkIn) / (1000 * 60 * 60 * 24));
          const rate = roomInfo[roomTypeInput.value].rate;

          numberOfNightsSpan.textContent = nights;
          roomRateSpan.textContent = `₱${rate.toLocaleString()}`;
          totalPriceSpan.textContent = `₱${(nights * rate).toLocaleString()}`;
        }
      }

      // Handle booking confirmation
      confirmBookingBtn.addEventListener('click', function() {
        if (bookingForm.checkValidity()) {
          // Show loading state
          confirmBookingBtn.disabled = true;
          confirmBookingBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';

          // Submit the form
          bookingForm.submit();
        } else {
          bookingForm.reportValidity();
        }
      });

      // Add click event to all "Book Now" buttons
      document.querySelectorAll('.details-link').forEach(button => {
        button.addEventListener('click', function(e) {
          e.preventDefault();
          const href = this.getAttribute('href');
          const roomId = href.split('id=')[1];
          
          // Get room details from data attributes
          const roomNumber = this.getAttribute('data-room-number');
          const roomType = this.getAttribute('data-room-type');
          
          // Populate the form
          roomIdInput.value = roomId;
          roomNumberInput.value = roomNumber;
          roomTypeInput.value = roomType;
          roomTypeDisplay.value = roomInfo[roomType].display;
          
          // Show the modal
          bookingModal.show();
        });
      });

      // Add this to your existing DOMContentLoaded event listener
      document.addEventListener('DOMContentLoaded', function() {
        // Set minimum date for quick check-in to today
        const quickCheckIn = document.getElementById('quickCheckIn');
        const quickCheckOut = document.getElementById('quickCheckOut');
        const today = new Date().toISOString().split('T')[0];
        
        quickCheckIn.min = today;
        
        // Update check-out minimum date when check-in changes
        quickCheckIn.addEventListener('change', function() {
          quickCheckOut.min = this.value;
          if (quickCheckOut.value && quickCheckOut.value < this.value) {
            quickCheckOut.value = this.value;
          }
        });

        // Handle quick availability check form submission
        const quickBookingForm = document.querySelector('.booking-widget form');
        quickBookingForm.addEventListener('submit', function(e) {
          e.preventDefault();
          const checkIn = quickCheckIn.value;
          const checkOut = quickCheckOut.value;
          
          // Scroll to rooms section
          document.getElementById('Rooms').scrollIntoView({ behavior: 'smooth' });
          
          // You can add additional logic here to filter rooms based on dates
          // For example, highlighting available rooms or showing a message
        });
      });
    });

    document.addEventListener('DOMContentLoaded', function() {
      // Cart functionality
      const cartIcon = document.querySelector('.cart-icon');
      const cartPreview = document.getElementById('cartPreview');
      const cartBadge = document.querySelector('.cart-badge');
      const cartCount = document.querySelector('.cart-count');
      const cartItems = document.querySelector('.cart-items');
      const cartTotal = document.getElementById('cartTotal');
      const checkoutBtn = document.getElementById('proceedToCheckout');

      // Initialize cart from localStorage
      let cart = JSON.parse(localStorage.getItem('bookingCart')) || [];
      updateCartDisplay();

      // Toggle cart preview
      cartIcon.addEventListener('click', function(e) {
        e.preventDefault();
        cartPreview.classList.toggle('show');
      });

      // Close cart preview when clicking outside
      document.addEventListener('click', function(e) {
        if (!cartPreview.contains(e.target) && !cartIcon.contains(e.target)) {
          cartPreview.classList.remove('show');
        }
      });

      // Add to Cart functionality
      document.querySelectorAll('.add-to-cart-btn').forEach(button => {
        button.addEventListener('click', function(e) {
          e.preventDefault();
          
          const roomData = {
            id: this.getAttribute('data-room-id'),
            number: this.getAttribute('data-room-number'),
            type: this.getAttribute('data-room-type'),
            price: parseFloat(this.getAttribute('data-room-price'))
          };

          // Add to cart
          cart.push(roomData);
          localStorage.setItem('bookingCart', JSON.stringify(cart));
          
          // Update cart display
          updateCartDisplay();
          
          // Show cart preview
          cartPreview.classList.add('show');
          
          // Show success message
          alert('Room added to cart successfully!');
        });
      });

      function updateCartDisplay() {
        // Update badges
        cartBadge.textContent = cart.length;
        cartCount.textContent = cart.length;
        
        // Clear current cart items
        cartItems.innerHTML = '';
        
        // Calculate total
        let total = 0;
        
        // Add each item to cart
        cart.forEach((item, index) => {
          total += item.price;
          
          const itemElement = document.createElement('div');
          itemElement.className = 'cart-item';
          itemElement.innerHTML = `
            <div class="cart-item-info">
              <h6>Room ${item.number}</h6>
              <p>${item.type.charAt(0).toUpperCase() + item.type.slice(1)} Room - ₱${item.price.toLocaleString()}</p>
            </div>
            <div class="cart-item-actions">
              <button onclick="removeFromCart(${index})">
                <i class="bi bi-trash"></i>
              </button>
            </div>
          `;
          
          cartItems.appendChild(itemElement);
        });
        
        // Update total
        cartTotal.textContent = total.toLocaleString();
        
        // Show/hide checkout button
        checkoutBtn.style.display = cart.length > 0 ? 'block' : 'none';
      }

      // Function to remove item from cart
      window.removeFromCart = function(index) {
        cart.splice(index, 1);
        localStorage.setItem('bookingCart', JSON.stringify(cart));
        updateCartDisplay();
      };

      // Quick booking form functionality
      const quickBookingForm = document.querySelector('.booking-widget form');
      quickBookingForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const checkIn = document.getElementById('quickCheckIn').value;
        const checkOut = document.getElementById('quickCheckOut').value;
        
        // Store dates in session storage for use during checkout
        sessionStorage.setItem('selectedCheckIn', checkIn);
        sessionStorage.setItem('selectedCheckOut', checkOut);
        
        // Scroll to rooms section
        document.getElementById('Rooms').scrollIntoView({ behavior: 'smooth' });
      });
    });
  </script>

</body>

</html>