<?php
session_start();
require_once '../config/db_connection.php';
require_once 'functions/customer_functions.php';

// Check if user is logged in as customer
if (!isset($_SESSION['customer_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit();
}

// Get customer profile
$customer_profile = getCustomerProfile($conn, $_SESSION['customer_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Checkout - JC Hotel</title>

    <!-- Favicons -->
    <link href="../img/logo.png" rel="icon">
    <link href="../img/logo.png" rel="apple-touch-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">

    <!-- Main CSS File -->
    <link href="assets/css/main.css" rel="stylesheet">

    <style>
        .checkout-container {
            max-width: 1200px;
            margin: 120px auto 40px;
            padding: 20px;
        }

        .booking-item {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .booking-item h5 {
            color: #1b5e20;
            margin-bottom: 15px;
        }

        .date-inputs {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }

        .guest-input {
            max-width: 100px;
        }

        .price-summary {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-top: 30px;
        }

        .price-summary h4 {
            color: #1b5e20;
            margin-bottom: 20px;
        }

        .price-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .total-price {
            font-size: 1.2rem;
            font-weight: bold;
            color: #1b5e20;
            border-top: 2px solid #dee2e6;
            padding-top: 15px;
            margin-top: 15px;
        }

        .payment-methods {
            margin-top: 30px;
        }

        .payment-methods .form-check {
            margin-bottom: 15px;
        }

        .btn-confirm {
            background: #1b5e20;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            margin-top: 20px;
            transition: all 0.3s ease;
        }

        .btn-confirm:hover {
            background: #2e7d32;
        }

        .special-requests {
            margin-top: 15px;
        }

        @media (max-width: 768px) {
            .date-inputs {
                flex-direction: column;
                gap: 10px;
            }
            
            .guest-input {
                max-width: 100%;
            }
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

        .gap-3 {
            gap: 1rem !important;
        }
    </style>
</head>

<body>
    <header id="header" class="header d-flex align-items-center fixed-top">
        <div class="container-fluid container-xl d-flex align-items-center justify-content-between">
            <a href="customer_dashboard.php" class="logo d-flex align-items-center">
                <h1 class="sitename">JC HOTEL</h1>
            </a>
            <div class="d-flex align-items-center gap-3">
                <a href="customer_dashboard.php" class="btn btn-outline-light">
                    <i class="bi bi-arrow-left"></i> Back to Rooms
                </a>
                <a href="#" class="cart-icon position-relative">
                    <i class="bi bi-cart3 fs-4 text-white"></i>
                    <span class="cart-badge position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        0
                    </span>
                </a>
            </div>
        </div>
    </header>

    <main>
        <div class="checkout-container">
            <h2 class="mb-4">Checkout</h2>
            <form id="checkoutForm" action="process_multiple_booking.php" method="POST">
                <div id="bookingItems">
                    <!-- Booking items will be dynamically added here -->
                </div>

                <div class="price-summary">
                    <h4>Price Summary</h4>
                    <div id="priceBreakdown">
                        <!-- Price breakdown will be dynamically added here -->
                    </div>
                    <div class="total-price price-item">
                        <span>Total Amount</span>
                        <span id="totalAmount">₱0.00</span>
                    </div>
                </div>

                <div class="payment-methods">
                    <h4>Payment Method</h4>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="payment_method" id="cash" value="cash" checked>
                        <label class="form-check-label" for="cash">
                            Cash at Front Desk
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="payment_method" id="gcash" value="gcash">
                        <label class="form-check-label" for="gcash">
                            GCash
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-confirm">Confirm Booking</button>
            </form>
        </div>
    </main>

    <!-- Vendor JS Files -->
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get cart items from localStorage
            const cart = JSON.parse(localStorage.getItem('bookingCart')) || [];
            const bookingItems = document.getElementById('bookingItems');
            const priceBreakdown = document.getElementById('priceBreakdown');
            const totalAmount = document.getElementById('totalAmount');

            // Get dates from session storage if available
            const selectedCheckIn = sessionStorage.getItem('selectedCheckIn') || '';
            const selectedCheckOut = sessionStorage.getItem('selectedCheckOut') || '';

            let total = 0;

            // Add booking items
            cart.forEach((item, index) => {
                const bookingItem = document.createElement('div');
                bookingItem.className = 'booking-item';
                bookingItem.innerHTML = `
                    <h5>Room ${item.number} - ${item.type.charAt(0).toUpperCase() + item.type.slice(1)} Room</h5>
                    <input type="hidden" name="room_ids[]" value="${item.id}">
                    <input type="hidden" name="room_prices[]" value="${item.price}">
                    
                    <div class="date-inputs">
                        <div class="form-group">
                            <label for="checkIn${index}">Check-in Date</label>
                            <input type="date" class="form-control" id="checkIn${index}" 
                                name="check_in_dates[]" value="${selectedCheckIn}" required>
                        </div>
                        <div class="form-group">
                            <label for="checkOut${index}">Check-out Date</label>
                            <input type="date" class="form-control" id="checkOut${index}" 
                                name="check_out_dates[]" value="${selectedCheckOut}" required>
                        </div>
                        <div class="form-group">
                            <label for="guests${index}">Guests</label>
                            <input type="number" class="form-control guest-input" id="guests${index}" 
                                name="guests[]" min="1" max="4" value="1" required>
                        </div>
                    </div>
                    
                    <div class="special-requests">
                        <label for="specialRequests${index}">Special Requests</label>
                        <textarea class="form-control" id="specialRequests${index}" 
                            name="special_requests[]" rows="2"></textarea>
                    </div>
                `;
                bookingItems.appendChild(bookingItem);

                // Add to price breakdown
                const priceItem = document.createElement('div');
                priceItem.className = 'price-item';
                priceItem.innerHTML = `
                    <span>Room ${item.number} (${item.type})</span>
                    <span>₱${item.price.toLocaleString()}</span>
                `;
                priceBreakdown.appendChild(priceItem);

                total += item.price;

                // Set minimum date for check-in to today
                const today = new Date().toISOString().split('T')[0];
                document.getElementById(`checkIn${index}`).min = today;

                // Update check-out minimum date when check-in changes
                document.getElementById(`checkIn${index}`).addEventListener('change', function() {
                    document.getElementById(`checkOut${index}`).min = this.value;
                });
            });

            // Update total amount
            totalAmount.textContent = `₱${total.toLocaleString()}`;

            // Handle form submission
            document.getElementById('checkoutForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Basic validation
                const checkInDates = document.querySelectorAll('input[name="check_in_dates[]"]');
                const checkOutDates = document.querySelectorAll('input[name="check_out_dates[]"]');
                
                let isValid = true;
                checkInDates.forEach((checkIn, index) => {
                    const checkOut = checkOutDates[index];
                    if (checkIn.value >= checkOut.value) {
                        alert('Check-out date must be after check-in date for all rooms.');
                        isValid = false;
                    }
                });

                if (isValid) {
                    // Clear cart after successful submission
                    localStorage.removeItem('bookingCart');
                    sessionStorage.removeItem('selectedCheckIn');
                    sessionStorage.removeItem('selectedCheckOut');
                    
                    // Submit the form
                    this.submit();
                }
            });
        });
    </script>
</body>
</html> 