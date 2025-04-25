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

// Fetch reservations for the logged-in customer
function getCustomerReservations($conn, $customer_id) {
    $query = "SELECT r.reservation_id, r.check_in, r.check_out, rm.room_number, rm.type, rm.price 
              FROM reservations r
              JOIN rooms rm ON r.room_id = rm.room_id
              WHERE r.customer_id = ? AND r.status = 'pending'";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param("i", $customer_id);
    if (!$stmt->execute()) {
        return false;
    }
    return $stmt->get_result();
}

$customer_reservations = getCustomerReservations($conn, $_SESSION['customer_id']);
if ($customer_reservations === false) {
    $customer_reservations = new stdClass();
    $customer_reservations->num_rows = 0;
}
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
  <link href="assets/css/customer.css" rel="stylesheet">
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
            <a href="#" class="book-icon position-relative">
              <i class="bi bi-book"></i>
              <span class="book-badge position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
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

  <main class="main">

    <section id="hero" class="hero section accent-background">
      <img src="../img/dashboard.jpg" alt="" data-aos="fade-in">
      <div class="container text-center" data-aos="fade-up" data-aos-delay="100">
        <h2>Welcome to JC Hotel</h2>
        <p>Your stay with us is what we cherish the most.</p>
        <a href="#Rooms" class="btn-scroll" title="Scroll Down"><i class="bi bi-chevron-down"></i></a>
      </div>
    </section><!-- /Hero Section -->

    <section id="booking-widget" class="booking-widget section">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-lg-8">
            <h3 class="text-center mb-4">Check Room Availability</h3>
            <div class="booking-form-wrapper" data-aos="fade-up">
              <form id="availabilityForm" class="availability-form">
                <div class="row g-3">
                  <div class="col-md-5">
                    <label for="checkInDate" class="form-label">Check In</label>
                    <input type="date" class="form-control" id="checkInDate" name="checkIn" required>
                  </div>
                  <div class="col-md-5">
                    <label for="checkOutDate" class="form-label">Check Out</label>
                    <input type="date" class="form-control" id="checkOutDate" name="checkOut" required>
                  </div>
                  <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Check</button>
                  </div>
                </div>
                <div id="availabilityResults" class="mt-4"></div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </section><!-- /Booking Widget Section -->

    <!-- Rooms Section -->
    <section id="Rooms" class="rooms section">
      <div class="container">
        <div class="section-title" data-aos="fade-up">
          <h2>Rooms</h2>
          <p>Choose from our selection of comfortable rooms</p>
        </div>

        <div class="room-filters mb-5" data-aos="fade-up">
          <div class="d-flex justify-content-center gap-2">
            <button class="btn btn-outline-primary active" data-filter="all">All Rooms</button>
            <button class="btn btn-outline-primary" data-filter="standard">Standard</button>
            <button class="btn btn-outline-primary" data-filter="deluxe">Deluxe</button>
            <button class="btn btn-outline-primary" data-filter="superior">Superior</button>
            <button class="btn btn-outline-primary" data-filter="suite">Suite</button>
          </div>
        </div>

        <div class="row gy-4" id="roomsGrid">
          <?php
          $checkIn = $_GET['checkIn'] ?? date('Y-m-d');
          $checkOut = $_GET['checkOut'] ?? date('Y-m-d', strtotime('+1 day'));
          $rooms = getAvailableRooms($conn, $checkIn, $checkOut);
          
          if ($rooms && $rooms->num_rows > 0):
            while ($room = $rooms->fetch_assoc()):
              $isAvailable = $room['is_available'];
          ?>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-room-type="<?php echo htmlspecialchars($room['type']); ?>">
              <div class="card room-card <?php echo $isAvailable ? 'available' : 'booked'; ?>">
                <div class="room-img">
                  <img src="../img/rooms/<?php echo htmlspecialchars($room['type']); ?>.jpg" class="card-img-top" alt="Room Image">
                  <?php if ($isAvailable): ?>
                    <div class="status-badge available">Available</div>
                  <?php else: ?>
                    <div class="status-badge booked">Booked</div>
                  <?php endif; ?>
                </div>
                <div class="card-body">
                  <h5 class="card-title"><?php echo ucfirst(htmlspecialchars($room['type'])); ?> Room</h5>
                  <p><strong>Room #:</strong> <?php echo htmlspecialchars($room['room_number']); ?></p>
                  
                  <div class="price mb-3"><strong>₱<?php echo number_format($room['price'], 2); ?> / night</strong></div>
                  <div class="d-flex justify-content-between">
                    <?php if ($isAvailable): ?>
                      <button class="btn btn-primary book-now" 
                              data-room-id="<?php echo $room['room_id']; ?>"
                              data-room-type="<?php echo htmlspecialchars($room['type']); ?>"
                              data-room-price="<?php echo $room['price']; ?>">
                        <i class="bi bi-calendar-check me-2"></i>Book Now
                      </button>
                    <?php else: ?>
                      <button class="btn btn-secondary" disabled>
                        <i class="bi bi-x-circle me-2"></i>Not Available
                      </button>
                    <?php endif; ?>
                    
                  </div>
                </div>
              </div>
            </div>
          <?php 
            endwhile;
          else:
          ?>
            <div class="col-12 text-center">
              <p>No rooms found for the selected dates.</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </section><!-- /Rooms Section -->

    

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

    <section id="teams" class="team teams">
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
            <p class="mt-3"><strong>Phone:</strong> <span>09123456789</span></p>
            <p><strong>Email:</strong> <span>jchotel@gmail.com</span></p>
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

  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
  <div id="preloader"></div>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>
  <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
  <script src="assets/vendor/imagesloaded/imagesloaded.pkgd.min.js"></script>
  <script src="assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>
  <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>

  <!-- Include SweetAlert -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- Main JS File -->
  <script src="assets/js/main.js"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Mobile Navigation
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

      // Availability checker functionality
      const availabilityForm = document.getElementById('availabilityForm');
      const checkInDate = document.getElementById('checkInDate');
      const checkOutDate = document.getElementById('checkOutDate');
      const resultsDiv = document.getElementById('availabilityResults');

      // Set minimum date to today
      const today = new Date().toISOString().split('T')[0];
      checkInDate.min = today;

      // Update checkout min date when checkin changes
      checkInDate.addEventListener('change', function() {
        checkOutDate.min = this.value;
        if (checkOutDate.value && checkOutDate.value < this.value) {
          checkOutDate.value = this.value;
        }
      });

      // Handle availability check submission
      availabilityForm.addEventListener('submit', handleAvailabilityCheck);

      function handleAvailabilityCheck(e) {
        e.preventDefault();
        resultsDiv.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"></div></div>';

        fetch('check_availability.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: `checkIn=${checkInDate.value}&checkOut=${checkOutDate.value}`
        })
        .then(response => response.json())
        .then(data => {
          if (data.available) {
            resultsDiv.innerHTML = `
              <div class="alert alert-success">
                <h5>Rooms Available!</h5>
                <p>${data.message}</p>
                <a href="#Rooms" class="btn btn-primary btn-sm">View Available Rooms</a>
              </div>`;
          } else {
            resultsDiv.innerHTML = `
              <div class="alert alert-warning">
                <h5>Limited Availability</h5>
                <p>${data.message}</p>
              </div>`;
          }
        })
        .catch(error => {
          resultsDiv.innerHTML = `
            <div class="alert alert-danger">
              <p>Error checking availability. Please try again.</p>
            </div>`;
        });
      }

      // Room filtering functionality
      const filterButtons = document.querySelectorAll('.room-filters button');
      const roomsGrid = document.getElementById('roomsGrid');
      const rooms = roomsGrid.querySelectorAll('.col-lg-4');

      filterButtons.forEach(button => {
        button.addEventListener('click', function() {
          const filterValue = this.getAttribute('data-filter');
          
          // Update active button
          filterButtons.forEach(btn => btn.classList.remove('active'));
          this.classList.add('active');

          // Filter rooms with animation
          rooms.forEach(room => {
            const roomType = room.getAttribute('data-room-type');
            if (filterValue === 'all' || filterValue === roomType) {
              room.style.display = 'block';
              setTimeout(() => {
                room.classList.add('show');
                room.classList.remove('hide');
              }, 50);
            } else {
              room.classList.add('hide');
              room.classList.remove('show');
              setTimeout(() => {
                room.style.display = 'none';
              }, 300);
            }
          });
        });
      });

      // Room Details Modal
      const roomDetailsModal = document.getElementById('roomDetailsModal');
      const modalRoomImage = document.getElementById('modalRoomImage');
      const modalRoomType = document.getElementById('modalRoomType');
      const modalRoomDescription = document.getElementById('modalRoomDescription');
      const modalRoomNumber = document.getElementById('modalRoomNumber');
      const modalRoomPrice = document.getElementById('modalRoomPrice');

      document.querySelectorAll('.view-details').forEach(button => {
        button.addEventListener('click', function() {
          const roomType = this.getAttribute('data-room-type');
          const roomNumber = this.getAttribute('data-room-number');
          const roomPrice = this.getAttribute('data-room-price');

          modalRoomImage.src = `../img/rooms/${roomType}.jpg`;
          modalRoomType.textContent = `${roomType.charAt(0).toUpperCase() + roomType.slice(1)} Room`;
          modalRoomNumber.textContent = roomNumber;
          modalRoomPrice.textContent = parseFloat(roomPrice).toLocaleString();
        });
      });

      // Handle "Book Now" button click
      document.querySelectorAll('.book-now').forEach(button => {
        button.addEventListener('click', function() {
          const roomId = this.getAttribute('data-room-id');
          const roomType = this.getAttribute('data-room-type');
          const roomPrice = this.getAttribute('data-room-price');
          const checkInDate = document.getElementById('checkInDate').value;
          const checkOutDate = document.getElementById('checkOutDate').value;

          if (!checkInDate || !checkOutDate) {
            Swal.fire({
              icon: 'warning',
              title: 'Missing Dates',
              text: 'Please select check-in and check-out dates before booking.',
            });
            return;
          }

          // Show confirmation dialog
          Swal.fire({
            title: 'Confirm Booking',
            text: `Are you sure you want to book a ${roomType} room from ${checkInDate} to ${checkOutDate}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Book Now',
            cancelButtonText: 'Cancel',
          }).then((result) => {
            if (result.isConfirmed) {
              // Show loading state
              button.disabled = true;
              button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Booking...';

              // Send booking data to the server
              fetch('book_room.php', {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `roomId=${roomId}&checkIn=${checkInDate}&checkOut=${checkOutDate}&roomType=${roomType}&roomPrice=${roomPrice}`
              })
              .then(response => response.json())
              .then(data => {
                if (data.success) {
                  Swal.fire({
                    icon: 'success',
                    title: 'Booking Confirmed',
                    text: 'Your room has been successfully booked!',
                  }).then(() => {
                    location.reload(); // Reload the page to update the UI
                  });
                } else {
                  Swal.fire({
                    icon: 'error',
                    title: 'Booking Failed',
                    text: data.message || 'Failed to book the room. Please try again.',
                  });
                }
              })
              .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                  icon: 'error',
                  title: 'Error',
                  text: 'An error occurred while booking the room. Please try again.',
                });
              })
              .finally(() => {
                button.disabled = false;
                button.innerHTML = '<i class="bi bi-calendar-check me-2"></i>Book Now';
              });
            }
          });
        });
      });
    });

    // Helper function for room descriptions
    
  </script>

</body>
</html>