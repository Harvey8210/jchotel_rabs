<?php
session_start();
require_once '../config/db_connection.php';
require_once 'functions/customer_functions.php';
require_once 'functions/room_functions.php';

// Check if user is logged in as customer
if (!isset($_SESSION['customer_id']) || $_SESSION['role'] !== 'customer') {
  header("Location: ../login.php");
  exit();
}

// Updated function to check room availability and insert reservation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['check_in'], $_POST['check_out'])) {
    $checkIn = $_POST['check_in'];
    $checkOut = $_POST['check_out'];

    $query = "SELECT r.room_id, r.room_number, r.type, r.price, r.image, r.description
              FROM rooms r
              WHERE NOT EXISTS (
                  SELECT 1 FROM reservations res
                  WHERE res.room_id = r.room_id
                  AND (
                      (res.check_in <= ? AND res.check_out >= ?)
                      OR (res.check_in <= ? AND res.check_out >= ?)
                      OR (res.check_in >= ? AND res.check_out <= ?)
                  )
                  AND res.status IN ('confirmed', 'checked-in')
              )";

    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("ssssss", $checkIn, $checkIn, $checkOut, $checkOut, $checkIn, $checkOut);
        $stmt->execute();
        $result = $stmt->get_result();

        $availableRooms = [];
        while ($row = $result->fetch_assoc()) {
            $availableRooms[] = $row;
        }

        echo json_encode([
            'success' => true,
            'availableRooms' => $availableRooms
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Database query error.'
        ]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['room_id'], $_POST['check_in'], $_POST['check_out'])) {
    $roomId = $_POST['room_id'];
    $checkIn = $_POST['check_in'];
    $checkOut = $_POST['check_out'];
    $customerId = $_SESSION['customer_id']; // Assuming customer is logged in

    $query = "INSERT INTO reservations (room_id, customer_id, check_in, check_out, status) VALUES (?, ?, ?, ?, 'pending')";
    $stmt = $conn->prepare($query);

    if ($stmt) {
        $stmt->bind_param("iiss", $roomId, $customerId, $checkIn, $checkOut);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Reservation successfully created.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create reservation.']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error.']);
    }
    exit;
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
          <li><a href="#availability-checker">Rooms</a></li>
          <li><a href="#about">About</a></li>
          <li><a href="#teams">Teams</a></li>
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-person-circle "></i> <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'User'); ?>
            </a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="profile.php">Profile</a></li>
              <li><a class="dropdown-item" href="mybookings.php">My Bookings</a></li>
              <li>
                <hr class="dropdown-divider">
              </li>
              <li><a class="dropdown-item" href="logout.php">Logout</a></li>
            </ul>
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

    <!-- Availability Checker Form -->
    <style>
        #availability-checker {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 3rem 1.5rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        #availability-checker h5 {
            font-size: 1.8rem;
            font-weight: bold;
            color: #1b5e20;
            text-align: center;
            margin-bottom: 2rem;
        }

        #availability-checker .form-label {
            font-weight: 600;
            color: #2e7d32;
            text-transform: uppercase;
            font-size: 0.9rem;
        }

        #availability-checker .form-control {
            border: 2px solid #e0e0e0;
            padding: 1rem;
            border-radius: 12px;
            transition: all 0.3s ease;
            font-size: 1rem;
            background: rgba(255, 255, 255, 0.9);
        }

        #availability-checker .form-control:hover {
            border-color: #2e7d32;
        }

        #availability-checker .form-control:focus {
            border-color: #2e7d32;
            box-shadow: 0 0 0 0.25rem rgba(46, 125, 50, 0.15);
            background: #fff;
        }

        #availability-checker .btn-primary {
            background: linear-gradient(135deg, #2e7d32 0%, #1b5e20 100%);
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            font-weight: bold;
            color: #fff;
            text-transform: uppercase;
            transition: all 0.3s ease;
        }

        #availability-checker .btn-primary:hover {
            background: linear-gradient(135deg, #1b5e20 0%, #2e7d32 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(46, 125, 50, 0.3);
        }

       
    </style>

    <section id="availability-checker" class="availability-checker">
        <div class="container">
            <div class="card mt-4">
                <div class="card-header text-black">
                    <h5>Check Room Availability</h5>
                </div>
                <div class="card-body">
                    <form id="availabilityForm" method="POST">
                        <div class="row align-items-end">
                            <div class="col-md-4">
                                <label for="checkIn" class="form-label">Check-In Date</label>
                                <input type="date" id="checkIn" name="check_in" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label for="checkOut" class="form-label">Check-Out Date</label>
                                <input type="date" id="checkOut" name="check_out" class="form-control" required>
                            </div>
                            <div class="col-md-4 text-center">
                                <button type="submit" class="btn btn-primary w-100">Check Availability</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div id="availabilityResults" class="mt-4"></div>
        </div>
    </section>

    <!-- Modal -->
    <div class="modal fade" id="bookNowModal" tabindex="-1" aria-labelledby="bookNowModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bookNowModalLabel">Room Booking Options</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Room Number:</strong> <span id="modalRoomNumber"></span></p>
                    <p><strong>Room Type:</strong> <span id="modalRoomType"></span></p>
                    <p><strong>Description:</strong> <span id="modalRoomDescription"></span></p>
                    <p><strong>Price per Night:</strong> ₱<span id="modalRoomPrice"></span></p>
                    <div class="form-group mt-3">
                        <label for="optionalNotes" class="form-label"><strong>Notes</strong>(Optional)</label>
                        <textarea id="optionalNotes" class="form-control" rows="3" placeholder="Add any special requests or notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="addBookButton">Add Book</button>
                    <button type="button" class="btn btn-primary" id="proceedButton">Proceed</button>
                </div>
            </div>
        </div>
    </div>

    <style>
    .rooms-section {
        padding: 4rem 0;
        background: #f8f9fa;
    }

    .section-title {
        text-align: center;
        margin-bottom: 2rem;
        font-size: 2rem;
        font-weight: bold;
        color: #1b5e20;
    }

    .room-card {
        background: #fff;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
    }

    .room-card:hover {
        transform: translateY(-5px);
    }

    .room-img {
        position: relative;
        height: 200px;
        overflow: hidden;
    }

    .room-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .room-card:hover .room-img img {
        transform: scale(1.1);
    }

    .room-info {
        padding: 20px;
    }

    .room-info h4 {
        margin-bottom: 10px;
        color: #333;
    }

    .room-description {
        color: #666;
        font-size: 0.9rem;
        margin: 0.5rem 0;
    }

    .room-features {
        display: flex;
        gap: 1rem;
        margin: 1rem 0;
        font-size: 0.9rem;
        color: #555;
    }

    .room-features span {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .room-features i {
        color: #1b5e20;
    }

    .btn-primary {
        background: linear-gradient(135deg, #2e7d32 0%, #1b5e20 100%);
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: bold;
        color: #fff;
        text-transform: uppercase;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #1b5e20 0%, #2e7d32 100%);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(46, 125, 50, 0.3);
    }
    </style>

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
    document.getElementById('availabilityForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch('', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            const resultsDiv = document.getElementById('availabilityResults');
            resultsDiv.innerHTML = '';

            if (data.success && data.availableRooms.length > 0) {
                let rowDiv = document.createElement('div');
                rowDiv.classList.add('row', 'gy-4');

                data.availableRooms.forEach((room, index) => {
                    const colDiv = document.createElement('div');
                    colDiv.classList.add('col-md-4');

                    colDiv.innerHTML = `
                        <div class="room-card">
                            <div class="room-img">
                                <img src="../img/rooms/${room.image}" alt="Room Image">
                            </div>
                            <div class="room-info">
                                <h4>Room #${room.room_number}</h4>
                                <p>${room.description}</p>
                                <p>Type: ${room.type}</p>
                                <p>Price: ₱${room.price}</p>
                                <div class="d-flex justify-content-center mt-2">
                                    <button class="btn btn-primary book-now" data-room-id="${room.room_id}">Book Now</button>
                                </div>
                            </div>
                        </div>
                    `;

                    rowDiv.appendChild(colDiv);

                    // Append a new row after every 3 columns
                    if ((index + 1) % 3 === 0) {
                        resultsDiv.appendChild(rowDiv);
                        rowDiv = document.createElement('div');
                        rowDiv.classList.add('row', 'gy-4');
                    }
                });

                // Append any remaining columns
                if (rowDiv.children.length > 0) {
                    resultsDiv.appendChild(rowDiv);
                }

                document.querySelectorAll('.book-now').forEach(button => {
                    button.addEventListener('click', function () {
                        const roomId = this.getAttribute('data-room-id');
                        const roomNumber = this.closest('.room-info').querySelector('h4').textContent;
                        const roomType = this.closest('.room-info').querySelector('p:nth-of-type(2)').textContent.split(': ')[1];
                        const roomDescription = this.closest('.room-info').querySelector('p:nth-of-type(1)').textContent;
                        const roomPrice = parseFloat(this.closest('.room-info').querySelector('p:nth-of-type(3)').textContent.split('₱')[1]);

                        document.getElementById('modalRoomNumber').textContent = roomNumber;
                        document.getElementById('modalRoomType').textContent = roomType;
                        document.getElementById('modalRoomDescription').textContent = roomDescription;
                        document.getElementById('modalRoomPrice').textContent = roomPrice;
                        document.getElementById('modalRoomNumber').setAttribute('data-room-id', roomId);

                        const modal = new bootstrap.Modal(document.getElementById('bookNowModal'));
                        modal.show();
                    });
                });
            } else {
                resultsDiv.innerHTML = '<p>No rooms available for the selected dates.</p>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    });

    document.getElementById('addBookButton').addEventListener('click', function () {
        const roomId = document.getElementById('modalRoomNumber').getAttribute('data-room-id');
        const checkIn = document.getElementById('checkIn').value;
        const checkOut = document.getElementById('checkOut').value;
        const notes = document.getElementById('optionalNotes').value;

        if (!roomId || !checkIn || !checkOut) {
            Swal.fire({
                icon: 'error',
                title: 'Missing Information',
                text: 'Please ensure all fields are filled out.',
            });
            return;
        }

        fetch('book_room.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                room_id: roomId,
                check_in: checkIn,
                check_out: checkOut,
                notes: notes
            }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('bookNowModal'));
                modal.hide();

                Swal.fire({
                    icon: 'success',
                    title: 'Booking Successful',
                    timer: 1500,
                    showConfirmButton: false
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Failed to create reservation.',
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An unexpected error occurred.',
            });
        });
    });

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.book-now').forEach(button => {
            button.addEventListener('click', function () {
                const roomId = this.getAttribute('data-room-id');
                const roomNumber = this.closest('.room-info').querySelector('h4').textContent;
                const roomType = this.closest('.room-info').querySelector('p:nth-of-type(2)').textContent.split(': ')[1];
                const roomDescription = this.closest('.room-info').querySelector('p:nth-of-type(1)').textContent;
                const roomPrice = parseFloat(this.closest('.room-info').querySelector('p:nth-of-type(3)').textContent.split('₱')[1]);

                document.getElementById('modalRoomNumber').textContent = roomNumber;
                document.getElementById('modalRoomType').textContent = roomType;
                document.getElementById('modalRoomDescription').textContent = roomDescription;
                document.getElementById('modalRoomPrice').textContent = roomPrice;

                const modal = new bootstrap.Modal(document.getElementById('bookNowModal'));
                modal.show();
            });
        });

      

        document.getElementById('proceedButton').addEventListener('click', function () {
            alert('Proceed functionality to be implemented.');
        });
    });
  </script>

</body>
</html>