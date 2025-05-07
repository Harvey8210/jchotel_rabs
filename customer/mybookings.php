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
                    <button class="nav-link" id="completed-tab" data-bs-toggle="tab" data-bs-target="#completed" type="button" role="tab">
                        <i class="fas fa-history me-2"></i>Completed
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
                    
                    <div class="booking-list">
                        <div class="loading-overlay" id="checkingLoading">
                            <div class="spinner"></div>
                        </div>
                        <?php
                        $status = 'chceking';
                        $query = 

                        $stmt = $conn->prepare($query);
                        $stmt->bind_param("is", $customer_id, $status);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows === 0) {
                            echo '<div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>No confirmed bookings found.
                              </div>';
                        }

                        while ($booking = $result->fetch_assoc()) {
                            echo '<div class="booking-card">
                            
                        </div>';
                        }
                        ?>
                    </div>
                </div>

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
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
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