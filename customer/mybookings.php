<?php
session_start();
require_once '../config/db_connection.php';

if (!isset($_SESSION['customer_id'])) {
    header('Location: ../login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - JC Hotel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/booking-theme.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Navigation Styles */
        .navbar {
            background-color: var(--primary-color);
            padding: 1rem 0;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand img {
            height: 40px;
            transition: transform 0.3s ease;
        }

        .navbar-brand:hover img {
            transform: scale(1.05);
        }

        .navbar .nav-link {
            color: var(--surface-color) !important;
            font-weight: 500;
            padding: 0.5rem 1rem !important;
            border-radius: 50px;
            transition: all 0.3s ease;
            margin: 0 0.2rem;
        }

        .navbar .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }

        .navbar .nav-link.active {
            background: rgba(255, 255, 255, 0.2);
            color: var(--surface-color) !important;
        }

        .navbar .nav-link i {
            margin-right: 0.5rem;
            font-size: 1.1rem;
        }

        .navbar-toggler {
            border-color: rgba(255, 255, 255, 0.1);
            padding: 0.5rem;
        }

        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(255, 255, 255, 0.8)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }

        @media (max-width: 991.98px) {
            .navbar-collapse {
                background: var(--primary-color);
                padding: 1rem;
                border-radius: 0 0 var(--border-radius) var(--border-radius);
                margin-top: 0.5rem;
            }

            .navbar .nav-link {
                padding: 0.75rem 1rem !important;
                border-radius: var(--border-radius);
            }
        }

        .booking-list {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            padding: 1.5rem 0;
            position: relative;
            min-height: 200px;
        }

        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.8);
            display: none;
            justify-content: center;
            align-items: center;
            border-radius: var(--border-radius);
        }

        .loading-overlay.active {
            display: flex;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        .booking-card {
            width: 100%;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.05);
            margin-bottom: 0.75rem;
            transform: translateY(20px);
            opacity: 0;
            animation: cardEnter 0.5s ease forwards;
            animation-delay: calc(var(--index) * 0.1s);
        }

        @keyframes cardEnter {
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @keyframes cardExit {
            from {
                transform: translateY(0);
                opacity: 1;
            }

            to {
                transform: translateY(-20px);
                opacity: 0;
            }
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .tab-pane {
            transition: opacity 0.3s ease;
        }

        .tab-pane:not(.show) {
            opacity: 0;
            pointer-events: none;
        }

        .tab-pane.show {
            opacity: 1;
            pointer-events: auto;
        }

        .booking-card:not(.mb-3):hover {
            transform: translateY(-2px);
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.08);
            border-color: rgba(0, 0, 0, 0.08);
        }

        .booking-card .row>div {
            padding: 1rem;
            font-size: 0.9rem;
            color: #495057;
            font-weight: 500;
        }

        .booking-card .row>div:not(:last-child) {
            border-right: 1px solid rgba(0, 0, 0, 0.05);
        }

        .booking-card.mb-3 {
            background: #f8f9fa;
            border: none;
            box-shadow: none;
            margin-bottom: 1rem;
        }

        .booking-card.mb-3 .row>div {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6c757d;
            padding: 0.75rem 1rem;
        }

        .status-badge {
            padding: 0.4rem 0.8rem;
            border-radius: 30px;
            font-size: 0.8rem;
            font-weight: 600;
            letter-spacing: 0.3px;
            display: inline-block;
            text-align: center;
            min-width: 90px;
        }

        .form-check-input {
            cursor: pointer;
            width: 1.1rem;
            height: 1.1rem;
            margin-top: 0.2rem;
            border-color: #dee2e6;
        }

        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        @media (max-width: 767.98px) {
            .booking-card {
                margin-bottom: 1rem;
            }

            .booking-card .row>div {
                border-right: none;
                border-bottom: 1px solid rgba(0, 0, 0, 0.05);
                padding: 0.75rem 1rem;
            }

            .booking-card .row>div:last-child {
                border-bottom: none;
            }

            .status-badge {
                margin-top: 0.25rem;
                width: 100%;
            }
        }

        .sticky-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.05);
            padding: 1.5rem 0;
            transform: translateY(100%);
            transition: transform 0.3s ease;
            z-index: 1000;
            border-top: 1px solid #dee2e6;
        }

        .sticky-footer.show {
            transform: translateY(0);
        }

        .sticky-footer .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .sticky-footer .subtotal-text {
            font-size: 1rem;
            color: #6c757d;
        }

        .sticky-footer .subtotal-amount {
            font-size: 1.25rem;
            font-weight: bold;
            color: #198754;
        }

        .body-padding {
            padding-bottom: 100px !important;
        }
    </style>
</head>

<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg mb-4">
        <div class="container">
            <a class="navbar-brand" href="customer_dashboard.php">
                <h2 style="color: white;"><strong>JC hotel</strong></h2>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="customer_dashboard.php">
                            <i class="fas fa-home me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="mybookings.php">
                            <i class="fas fa-bookmark me-1"></i>My Bookings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt me-1"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-success mb-0">My Bookings</h2>
            <div class="btn-group">

                <a href="customer_dashboard.php" class="btn btn-outline-primary">
                    <i class="fas fa-plus me-2"></i>New Booking
                </a>
            </div>
        </div>

        <form id="bookingForm">
            <!-- Status Tabs -->
            <ul class="nav nav-tabs" id="bookingTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab">
                        <i class="fas fa-clock me-2"></i>Pending
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="confirmed-tab" data-bs-toggle="tab" data-bs-target="#checking" type="button" role="tab">
                        <i class="fas fa-check-circle me-2"></i>Checking
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="payment-tab" data-bs-toggle="tab" data-bs-target="#payment" type="button" role="tab">
                        <i class="fas fa-history me-2"></i>Payment
                    </button>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content" id="bookingTabContent">
                <!-- Label Card - Only for Pending Tab -->
                <div class="tab-pane fade show active" id="pending" role="tabpanel">
                    <!-- Label Card -->
                    <div class="booking-card mb-3">
                        <div class="row g-0">
                            <div class="col-1 text-center"><strong>Select</strong></div>
                            <div class="col-1"><strong>Room</strong></div>
                            <div class="col-2"><strong>Type</strong></div>
                            <div class="col-2"><strong>Check In</strong></div>
                            <div class="col-2"><strong>Check Out</strong></div>
                            <div class="col-1 text-center"><strong>Nights</strong></div>
                            <div class="col-2"><strong>Total Price</strong></div>
                            <div class="col-1 text-center"><strong>Action</strong></div>
                        </div>
                    </div>
                    <div class="booking-list">
                        <div class="loading-overlay" id="pendingLoading">
                            <div class="spinner"></div>
                        </div>
                        <?php
                        $customer_id = $_SESSION['customer_id'];
                        $status = 'pending';
                        $query = "SELECT r.*, rm.room_number, rm.type as room_type, rm.price, rm.image,
                                    DATEDIFF(r.check_out, r.check_in) as nights
                             FROM reservations r 
                             JOIN rooms rm ON r.room_id = rm.room_id 
                             WHERE r.customer_id = ? AND r.status = ?
                             ORDER BY r.created_at DESC";

                        $stmt = $conn->prepare($query);
                        $stmt->bind_param("is", $customer_id, $status);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows === 0) {
                            echo '<div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>No pending bookings found.
                                <a href="customer_dashboard.php" class="alert-link ms-2">Make a new booking</a>
                              </div>';
                        }

                        while ($booking = $result->fetch_assoc()) {
                            echo '<div class="booking-card" data-reservation-id="' . $booking['reservation_id'] . '">
                            <div class="row g-0 align-items-center">
                                <div class="col-1 text-center">
                                    <div class="form-check d-inline-block">
                                        <input type="checkbox" class="form-check-input booking-checkbox" value="' . $booking['reservation_id'] . '">
                                    </div>
                                </div>
                                <div class="col-1">' . htmlspecialchars($booking['room_number']) . '</div>
                                <div class="col-2">' . ucfirst(htmlspecialchars($booking['room_type'])) . '</div>
                                <div class="col-2">' . date('M d, Y', strtotime($booking['check_in'])) . '</div>
                                <div class="col-2">' . date('M d, Y', strtotime($booking['check_out'])) . '</div>
                                <div class="col-1 text-center">' . $booking['nights'] . '</div>
                                <div class="col-2">₱' . number_format($booking['price'] * $booking['nights'], 2) . '</div>
                                <div class="col-1 text-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteBooking(' . $booking['reservation_id'] . ')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>';
                        }
                        ?>
                    </div>
                    <!-- Booking Footer -->
                    <div class="booking-footer d-flex justify-content-between align-items-center mt-4">
                        <div class="text-muted">Subtotal:</div>
                        <div class="fw-bold text-success">₱<span id="totalPrice">0.00</span></div>
                        <button id="proceedToPayment" class="btn btn-primary">Proceed to Payment</button>
                    </div>
                </div>

                <!-- Pending Tab -->


                <!-- Checking Tab -->
                <div class="tab-pane fade" id="checking" role="tabpanel">
                    <!-- Label Card -->
                    <div class="booking-card mb-3">
                        <div class="row g-0">
                            <div class="col-3"><strong>Room</strong></div>
                            <div class="col-2 text-center"><strong>Nights</strong></div>
                            <div class="col-3"><strong>Total Price</strong></div>
                            <div class="col-2"><strong>Payment Status</strong></div>
                            <div class="col-2"><strong>Action</strong></div>
                        </div>
                    </div>
                    
                    <div class="booking-list">
                        <div class="loading-overlay" id="checkingLoading">
                            <div class="spinner"></div>
                        </div>
                        <?php
                        $status = 'checking';
                        $query = "SELECT r.*, rm.room_number, rm.type as room_type, rm.price, rm.image,
                                 b.billing_id, b.total_amount, b.status as payment_status, b.group_number,
                                 DATEDIFF(r.check_out, r.check_in) as nights
                                 FROM reservations r 
                                 JOIN rooms rm ON r.room_id = rm.room_id 
                                 LEFT JOIN billing b ON r.reservation_id = b.reservation_id
                                 WHERE r.customer_id = ? AND b.status = ?
                                 ORDER BY b.group_number, r.created_at DESC";

                        $stmt = $conn->prepare($query);
                        $stmt->bind_param("is", $customer_id, $status);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        $groupedBookings = [];
                        while ($booking = $result->fetch_assoc()) {
                            $groupedBookings[$booking['group_number']][] = $booking;
                        }

                        if (empty($groupedBookings)) {
                            echo '<div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>No checking bookings found.
                              </div>';
                        }

                        foreach ($groupedBookings as $groupNumber => $bookings) {
                            $groupTotal = 0;
                            $roomCount = count($bookings);

                            echo '<div class="booking-card">';
                            echo '<div class="row g-0 align-items-center mb-2">';
                            echo '<div class="col-12"><strong>Group Number: ' . htmlspecialchars($groupNumber) . '</strong></div>';
                            echo '</div>';

                            foreach ($bookings as $booking) {
                                $paymentStatus = $booking['payment_status'] ?? 'Pending';
                                $paymentBadgeClass = $paymentStatus === 'Paid' ? 'bg-success' : 'bg-warning';
                                $totalAmount = $booking['total_amount'] ?? ($booking['price'] * $booking['nights']);
                                $groupTotal += $totalAmount;

                                echo '<div class="row g-0 align-items-center mb-2">';
                                echo '<div class="col-3">' . htmlspecialchars($booking['room_number']) . ' - ' . ucfirst(htmlspecialchars($booking['room_type'])) . '</div>';
                                echo '<div class="col-2 text-center">' . $booking['nights'] . ' Night(s)</div>';
                                echo '<div class="col-3">₱' . number_format($totalAmount, 2) . '</div>';
                                echo '<div class="col-2"><span class="badge ' . $paymentBadgeClass . ' status-badge">' . $paymentStatus . '</span></div>';
                                echo '<div class="col-2"><button type="button" class="btn btn-sm btn-outline-info view-billing" data-billing-id="' . ($booking['billing_id'] ?? '') . '"><i class="fas fa-eye"></i></button></div>';
                                echo '</div>';
                            }

                            echo '<div class="row g-0 align-items-center mt-3">';
                            echo '<div class="col-12 text-end">';
                            echo '<strong>Total Rooms:</strong> ' . $roomCount . ' | ';
                            echo '<strong>Subtotal:</strong> ₱' . number_format($groupTotal, 2);
                            echo '</div>';
                            echo '</div>';
                            echo '</div>';
                        }
                        ?>
                    </div>
                </div>

                <!-- Payment Tab -->
                <div class="tab-pane fade" id="payment" role="tabpanel">
                    <!-- Label Card -->
                    <div class="booking-card mb-3">
                        <div class="row g-0">
                            <div class="col-3"><strong>Room</strong></div>
                            <div class="col-2 text-center"><strong>Nights</strong></div>
                            <div class="col-3"><strong>Total Price</strong></div>
                            <div class="col-2"><strong>Payment Status</strong></div>
                            <div class="col-2"><strong>Action</strong></div>
                        </div>
                    </div>
                    
                    <div class="booking-list">
                        <div class="loading-overlay" id="checkingLoading">
                            <div class="spinner"></div>
                        </div>
                        <?php
                        // Fetch available loyalty points
                        $loyaltyQuery = "SELECT SUM(points) AS available_points FROM loyalty_transactions WHERE customer_id = ? AND status = 'earned'";
                        $loyaltyStmt = $conn->prepare($loyaltyQuery);
                        $loyaltyStmt->bind_param("i", $customer_id);
                        $loyaltyStmt->execute();
                        $loyaltyResult = $loyaltyStmt->get_result();
                        $loyaltyData = $loyaltyResult->fetch_assoc();
                        $availablePoints = $loyaltyData['available_points'] ?? 0;

                        $status = 'payment';
                        $query = "SELECT r.*, rm.room_number, rm.type as room_type, rm.price, rm.image,
                                 b.billing_id, b.total_amount, b.status as payment_status, b.group_number, b.add_req_payment,
                                 DATEDIFF(r.check_out, r.check_in) as nights
                                 FROM reservations r 
                                 JOIN rooms rm ON r.room_id = rm.room_id 
                                 LEFT JOIN billing b ON r.reservation_id = b.reservation_id
                                 WHERE r.customer_id = ? AND b.status = ?
                                 ORDER BY b.group_number, r.created_at DESC";

                        $stmt = $conn->prepare($query);
                        $stmt->bind_param("is", $customer_id, $status);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        $groupedBookings = [];
                        while ($booking = $result->fetch_assoc()) {
                            $groupedBookings[$booking['group_number']][] = $booking;
                        }

                        if (empty($groupedBookings)) {
                            echo '<div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>No checking bookings found.
                              </div>';
                        }

                        foreach ($groupedBookings as $groupNumber => $bookings) {
                            $groupTotal = 0;
                            $additionalPaymentTotal = 0;
                            $roomCount = count($bookings);

                            echo '<div class="booking-card">';
                            echo '<div class="row g-0 align-items-center mb-2">';
                            echo '<div class="col-12"><strong>Group Number: ' . htmlspecialchars($groupNumber) . '</strong></div>';
                            echo '</div>';

                            foreach ($bookings as $booking) {
                                $paymentStatus = $booking['payment_status'] ?? 'Pending';
                                $paymentBadgeClass = $paymentStatus === 'Paid' ? 'bg-success' : 'bg-warning';
                                $totalAmount = $booking['total_amount'] ?? ($booking['price'] * $booking['nights']);
                                $groupTotal += $totalAmount;
                                $additionalPaymentTotal += $booking['add_req_payment'] ?? 0;

                                echo '<div class="row g-0 align-items-center mb-2">';
                                echo '<div class="col-3">' . htmlspecialchars($booking['room_number']) . ' - ' . ucfirst(htmlspecialchars($booking['room_type'])) . '</div>';
                                echo '<div class="col-2 text-center">' . $booking['nights'] . ' Night(s)</div>';
                                echo '<div class="col-3">₱' . number_format($totalAmount, 2) . '</div>';
                                echo '<div class="col-2"><span class="badge ' . $paymentBadgeClass . ' status-badge">' . $paymentStatus . '</span></div>';
                                echo '<div class="col-2"><button type="button" class="btn btn-sm btn-outline-info view-billing" data-billing-id="' . ($booking['billing_id'] ?? '') . '"><i class="fas fa-eye"></i></button></div>';
                                echo '</div>';
                            }

                            echo '<div class="row g-0 align-items-center mt-3 border-top pt-2">';
                            echo '<div class="col-12">';
                            echo '<div class="d-flex justify-content-end">';
                            echo '<div class="text-end">';
                            echo '<div class="mb-1 row"><span class="col-auto text-muted text-start">Total Rooms:</span> <strong class="col text-end">' . $roomCount . '</strong></div>';
                            echo '<div class="mb-1 row"><span class="col-auto text-muted text-start">Available Points:</span> <strong class="col text-end">' . number_format($availablePoints) . '</strong></div>';
                            echo '<div class="mb-1 row"><span class="col-auto text-muted text-start">Loyalty Points:</span> 
                                <div class="col text-end d-flex align-items-center">
                                    <input type="number" class="form-control loyalty-points" value="" min="0" max="' . $availablePoints . '" style="display: inline-block; width: 80px; appearance: none;" data-group-total="' . $groupTotal . '" data-additional-payment="' . $additionalPaymentTotal . '">
                                    <button type="button" class="btn btn-sm btn-outline-secondary ms-2 max-points-btn">Max</button>
                                </div>
                            </div>';
                            echo '<div class="mb-1 row"><span class="col-auto text-muted text-start">Total Additional Payment:</span> <strong class="col text-end text-success">₱' . number_format($additionalPaymentTotal, 2) . '</strong></div>';
                            echo '<div class="mb-1 row"><span class="col-auto text-muted text-start">Subtotal:</span> <strong class="col text-end">₱' . number_format($groupTotal, 2) . '</strong></div>';

                            echo '<div class="mt-2 pt-2 border-top row"><span class="col-auto text-muted text-start h5">Grand Total:</span> <strong class="col text-end text-success h5 grand-total">₱' . 
                                number_format($groupTotal + $additionalPaymentTotal,  2) . 
                                '</strong></div>';
                            echo '<div class="mt-3 text-end"><button type="button" class="btn btn-primary">Pay Now</button></div>';
                            echo '</div>';
                            echo '</div>';
                            echo '</div>';
                            echo '</div>';
                        }
                        ?>
                    </div>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        document.querySelectorAll('.loyalty-points').forEach(input => {
                            input.addEventListener('input', function() {
                                const availablePoints = parseFloat(this.getAttribute('max'));
                                const groupTotal = parseFloat(this.getAttribute('data-group-total'));
                                const additionalPayment = parseFloat(this.getAttribute('data-additional-payment'));
                                let loyaltyPoints = parseFloat(this.value) || 0;

                                if (loyaltyPoints > availablePoints) {
                                    loyaltyPoints = availablePoints;
                                    this.value = availablePoints;
                                }

                                const grandTotal = Math.max(0, groupTotal + additionalPayment - loyaltyPoints);
                                this.closest('.booking-card').querySelector('.grand-total').textContent = '₱' + grandTotal.toLocaleString('en-US', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                });
                            });
                        });

                        document.querySelectorAll('.max-points-btn').forEach(button => {
                            button.addEventListener('click', function() {
                                const input = this.previousElementSibling;
                                const maxPoints = parseFloat(input.getAttribute('max'));
                                input.value = maxPoints;
                                input.dispatchEvent(new Event('input'));
                            });
                        });
                    });
                </script>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        document.querySelectorAll('.loyalty-points').forEach(input => {
                            input.addEventListener('input', function() {
                                const groupTotal = parseFloat(this.getAttribute('data-group-total'));
                                const additionalPayment = parseFloat(this.getAttribute('data-additional-payment'));
                                const loyaltyPoints = parseFloat(this.value) || 0;
                                const grandTotal = Math.max(0, groupTotal + additionalPayment - loyaltyPoints);
                                this.closest('.booking-card').querySelector('.grand-total').textContent = '₱' + grandTotal.toLocaleString('en-US', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                });
                            });
                        });
                    });
                </script></script>

                <!-- Billing Details Modal -->
                <div class="modal fade" id="billingDetailsModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Billing Details</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div id="billingDetails">
                                    <!-- Content will be loaded dynamically -->
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        // Handle billing details view
                        const billingModal = new bootstrap.Modal(document.getElementById('billingDetailsModal'));
                        
                        document.querySelectorAll('.view-billing').forEach(button => {
                            button.addEventListener('click', async function() {
                                const billingId = this.getAttribute('data-billing-id');
                                if (!billingId) {
                                    Swal.fire({
                                        icon: 'info',
                                        title: 'No Billing Information',
                                        text: 'Billing information is not available for this booking.'
                                    });
                                    return;
                                }

                                try {
                                    const response = await fetch(`functions/get_billing_details.php?billing_id=${billingId}`);
                                    if (!response.ok) throw new Error('Failed to fetch billing details');
                                    
                                    const data = await response.json();
                                    const billingDetails = document.getElementById('billingDetails');
                                    
                                    billingDetails.innerHTML = `
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <tr>
                                                    <th>Billing ID</th>
                                                    <td>${data.billing_id}</td>
                                                </tr>
                                                <tr>
                                                    <th>Room Details</th>
                                                    <td>${data.room_number} - ${data.room_type}</td>
                                                </tr>
                                                <tr>
                                                    <th>Check In</th>
                                                    <td>${new Date(data.check_in).toLocaleDateString()}</td>
                                                </tr>
                                                <tr>
                                                    <th>Check Out</th>
                                                    <td>${new Date(data.check_out).toLocaleDateString()}</td>
                                                </tr>
                                                <tr>
                                                    <th>Number of Nights</th>
                                                    <td>${data.nights}</td>
                                                </tr>
                                                <tr>
                                                    <th>Room Rate per Night</th>
                                                    <td>₱${parseFloat(data.room_rate).toLocaleString()}</td>
                                                </tr>
                                                <tr>
                                                    <th>Total Amount</th>
                                                    <td>₱${parseFloat(data.total_amount).toLocaleString()}</td>
                                                </tr>
                                                <tr>
                                                    <th>Payment Status</th>
                                                    <td><span class="badge ${data.status === 'paid' ? 'bg-success' : 'bg-warning'}">${data.status}</span></td>
                                                </tr>
                                                <tr>
                                                    <th>Payment Date</th>
                                                    <td>${data.payment_date ? new Date(data.payment_date).toLocaleString() : 'Not paid yet'}</td>
                                                </tr>
                                                <tr>
                                                    <th>Payment Method</th>
                                                    <td>${data.payment_method || 'Not specified'}</td>
                                                </tr>
                                            </table>
                                        </div>
                                    `;
                                    
                                    billingModal.show();
                                } catch (error) {
                                    console.error('Error fetching billing details:', error);
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: 'Failed to load billing details. Please try again.'
                                    });
                                }
                            });
                        });
                    });
                </script>

            </div>
        </form>


    </div>


    <!-- Delete Modal -->
    <div class="modal fade" id="deleteBookingModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Booking</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Are you sure you want to delete this booking? This action cannot be undone.</p>
                    <button type="button" class="btn btn-danger" id="confirmDelete">
                        <i class="fas fa-trash me-2"></i>Delete Booking
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize variables
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.booking-checkbox');
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteBookingModal'));
            const stickyFooter = document.querySelector('.sticky-footer');
            const subtotalElement = document.getElementById('subtotal');
            const selectedCountElement = document.getElementById('selectedCount');
            const proceedToPaymentBtn = document.getElementById('proceedToPayment');
            let selectedReservationId = null;

            // Update totals when checkboxes change
            function updateTotals() {
                let total = 0;
                let count = 0;
                const pendingTab = document.getElementById('pending');
                pendingTab.querySelectorAll('.booking-checkbox:checked').forEach(checkbox => {
                    count++;
                    const bookingCard = checkbox.closest('.booking-card');
                    const priceText = bookingCard.querySelector('.col-2:nth-last-child(2)').textContent;
                    const price = parseFloat(priceText.replace('₱', '').replace(/,/g, ''));
                    if (!isNaN(price)) {
                        total += price;
                    }
                });

                document.getElementById('totalPrice').textContent = total.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });

                if (count > 0) {
                    proceedToPaymentBtn.removeAttribute('disabled');
                } else {
                    proceedToPaymentBtn.setAttribute('disabled', 'disabled');
                }
            }

            // Add event listeners for checkboxes
            document.querySelectorAll('.booking-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', updateTotals);
            });

            // Handle payment processing
            proceedToPaymentBtn.addEventListener('click', async function() {
                const selectedReservations = [];
                const pendingTab = document.getElementById('pending');

                pendingTab.querySelectorAll('.booking-checkbox:checked').forEach(checkbox => {
                    selectedReservations.push(checkbox.value);
                });

                if (selectedReservations.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No Reservations Selected',
                        text: 'Please select at least one pending reservation to proceed with payment.'
                    });
                    return;
                }

                try {
                    const loadingOverlay = pendingTab.querySelector('.loading-overlay');
                    loadingOverlay.classList.add('active');
                    proceedToPaymentBtn.setAttribute('disabled', 'disabled');

                    const response = await fetch('./functions/process_payment.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            reservations: selectedReservations
                        })
                    });

                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }

                    const data = await response.json();

                    if (data.success) {
                        await Swal.fire({
                            icon: 'success',
                            title: 'Payment Processed',
                            text: 'Your reservations have been confirmed successfully.',
                            confirmButtonText: 'OK'
                        });
                        window.location.reload();
                    } else {
                        throw new Error(data.message || 'Failed to process payment');
                    }
                } catch (error) {
                    console.error('Error during payment:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message || 'An error occurred while processing your payment. Please try again.'
                    });
                } finally {
                    const loadingOverlay = pendingTab.querySelector('.loading-overlay');
                    loadingOverlay.classList.remove('active');
                    proceedToPaymentBtn.removeAttribute('disabled');
                }
            });

            // Initialize page state
            updateTotals();
        });
    </script>
</body>

</html>