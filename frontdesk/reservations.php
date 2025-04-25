<?php
session_start();
require_once '../config/db_connection.php';
require_once 'functions.php';

// Check if frontdesk is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'frontdesk') {
    header('Location: ../admin/login.php');
    exit();
}

// Get room categories for the form
$room_categories = getRoomCategories($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservations - JC Hotel</title>
    <link href="../dist/css/style.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: #343a40;
            color: white;
            padding-top: 20px;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,.8);
            padding: 10px 20px;
            margin: 5px 0;
        }
        .sidebar .nav-link:hover {
            color: white;
            background: rgba(255,255,255,.1);
        }
        .sidebar .nav-link.active {
            background: rgba(255,255,255,.1);
            color: white;
        }
        .main-content {
            padding: 20px;
        }
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 20px;
        }
        .room-card {
            transition: transform 0.2s;
        }
        .room-card:hover {
            transform: translateY(-5px);
        }
        .room-image {
            height: 200px;
            object-fit: cover;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include'includes/sidebar.php'?>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Reservations</h2>
                    <div>
                        <span class="me-3">Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                        <span class="badge bg-primary">Frontdesk</span>
                    </div>
                </div>

                <!-- Reservation Form -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">New Reservation</h5>
                    </div>
                    <div class="card-body">
                        <form id="reservationForm" method="POST" action="process_reservation.php">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Customer Name</label>
                                    <input type="text" name="customer_name" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Contact Number</label>
                                    <input type="tel" name="contact_number" class="form-control" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Room Type</label>
                                    <select class="form-select" name="room_type" id="roomTypeSelect" required>
                                        <option value="">Select Room Type</option>
                                        <?php while ($category = $room_categories->fetch_assoc()): ?>
                                            <option value="<?php echo $category['type']; ?>">
                                                <?php echo ucfirst($category['type']); ?> 
                                                (₱<?php echo number_format($category['min_price'], 2); ?> - ₱<?php echo number_format($category['max_price'], 2); ?>)
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Room</label>
                                    <select class="form-select" name="room_number" id="roomSelect" required>
                                        <option value="">Select Room</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Check-in Date & Time</label>
                                    <input type="datetime-local" name="check_in" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Check-out Date & Time</label>
                                    <input type="datetime-local" name="check_out" class="form-control" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Total Amount</label>
                                    <input type="number" class="form-control" name="total_amount" id="totalAmount" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Payment Status</label>
                                    <select class="form-select" name="payment_status" required>
                                        <option value="unpaid">Unpaid</option>
                                        <option value="paid">Paid</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="3"></textarea>
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Save Reservation
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="../assets/libs/popper.js/dist/umd/popper.min.js"></script>
    <script src="../assets/libs/bootstrap/dist/js/bootstrap.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const roomTypeSelect = document.getElementById('roomTypeSelect');
            const roomSelect = document.getElementById('roomSelect');
            const totalAmount = document.getElementById('totalAmount');
            const checkInInput = document.querySelector('input[name="check_in"]');
            const checkOutInput = document.querySelector('input[name="check_out"]');

            // Set minimum date for check-in to today
            const today = new Date().toISOString().slice(0, 16);
            checkInInput.min = today;

            // Update check-out minimum date when check-in changes
            checkInInput.addEventListener('change', function() {
                checkOutInput.min = this.value;
                if (checkOutInput.value && checkOutInput.value <= this.value) {
                    checkOutInput.value = '';
                }
                updateAvailableRooms();
            });

            // Update available rooms when check-out changes
            checkOutInput.addEventListener('change', updateAvailableRooms);

            // Load available rooms when room type is selected
            roomTypeSelect.addEventListener('change', updateAvailableRooms);

            function updateAvailableRooms() {
                const roomType = roomTypeSelect.value;
                const checkIn = checkInInput.value;
                const checkOut = checkOutInput.value;

                if (roomType && checkIn && checkOut) {
                    // Show loading state
                    roomSelect.innerHTML = '<option value="">Loading available rooms...</option>';
                    roomSelect.disabled = true;

                    // Fetch available rooms
                    fetch(`get_available_rooms.php?type=${roomType}&check_in=${checkIn}&check_out=${checkOut}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.error) {
                                roomSelect.innerHTML = `<option value="">Error: ${data.error}</option>`;
                                return;
                            }

                            roomSelect.innerHTML = '<option value="">Select Room</option>';
                            if (data.length === 0) {
                                roomSelect.innerHTML += '<option value="" disabled>No rooms available for selected dates</option>';
                                return;
                            }

                            data.forEach(room => {
                                const option = document.createElement('option');
                                option.value = room.room_number;
                                option.textContent = `Room ${room.room_number} - ₱${parseFloat(room.price).toLocaleString('en-US', {minimumFractionDigits: 2})}`;
                                option.dataset.price = room.price;
                                roomSelect.appendChild(option);
                            });
                        })
                        .catch(error => {
                            console.error('Error loading rooms:', error);
                            roomSelect.innerHTML = '<option value="">Error loading rooms</option>';
                        })
                        .finally(() => {
                            roomSelect.disabled = false;
                        });
                } else {
                    roomSelect.innerHTML = '<option value="">Select Room</option>';
                    totalAmount.value = '';
                }
            }

            // Update total amount when room is selected
            roomSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption.value) {
                    totalAmount.value = selectedOption.dataset.price;
                } else {
                    totalAmount.value = '';
                }
            });

            // Form validation
            const form = document.getElementById('reservationForm');
            form.addEventListener('submit', function(e) {
                const checkIn = new Date(checkInInput.value);
                const checkOut = new Date(checkOutInput.value);

                if (checkOut <= checkIn) {
                    e.preventDefault();
                    alert('Check-out date must be after check-in date');
                    return;
                }

                if (!roomSelect.value) {
                    e.preventDefault();
                    alert('Please select an available room');
                    return;
                }
            });
        });
    </script>
</body>
</html> 