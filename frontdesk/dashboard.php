<?php
session_start();
require_once '../config/db_connection.php';
require_once 'functions.php';

// Check if frontdesk is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'frontdesk') {
    header('Location: ../admin/login.php');
    exit();
}

// Function to create walk-in reservation
function createWalkInReservation($conn, $customer_name, $phone, $room_id, $check_in, $check_out, $notes = '') {
    try {
        $conn->begin_transaction();
        
        // First create customer record
        $stmt = $conn->prepare("INSERT INTO customers (full_name, phone, created_at) VALUES (?, ?, NOW())");
        $stmt->bind_param("ss", $customer_name, $phone);
        $stmt->execute();
        $customer_id = $conn->insert_id;
        
        // Create reservation
        $stmt = $conn->prepare("INSERT INTO reservations (customer_id, room_id, check_in, check_out, status, notes, created_at) VALUES (?, ?, ?, ?, 'confirmed', ?, NOW())");
        $stmt->bind_param("iisss", $customer_id, $room_id, $check_in, $check_out, $notes);
        $stmt->execute();
        $reservation_id = $conn->insert_id;
        
        // Update room status
        $stmt = $conn->prepare("UPDATE rooms SET status = 'occupied' WHERE room_id = ?");
        $stmt->bind_param("i", $room_id);
        $stmt->execute();
        
        // Record in audit trail
        $action = "Walk-in reservation #" . $reservation_id . " created by frontdesk";
        $stmt = $conn->prepare("INSERT INTO audit_trails (user_id, action_taken) VALUES (?, ?)");
        $stmt->bind_param("is", $_SESSION['user_id'], $action);
        $stmt->execute();
        
        $conn->commit();
        return $reservation_id;
    } catch (Exception $e) {
        $conn->rollback();
        return false;
    }
}

// Function to process walk-in payment
function processWalkInPayment($conn, $reservation_id, $amount_paid, $payment_method) {
    try {
        $conn->begin_transaction();
        
        // Get room price
        $stmt = $conn->prepare("
            SELECT r.price, rm.type 
            FROM reservations r 
            JOIN rooms rm ON r.room_id = rm.room_id 
            WHERE r.reservation_id = ?
        ");
        $stmt->bind_param("i", $reservation_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $room = $result->fetch_assoc();
        
        // Create billing record
        $stmt = $conn->prepare("INSERT INTO billing (reservation_id, total_amount, status, created_at) VALUES (?, ?, 'paid', NOW())");
        $stmt->bind_param("id", $reservation_id, $room['price']);
        $stmt->execute();
        $billing_id = $conn->insert_id;
        
        // Record payment
        $stmt = $conn->prepare("INSERT INTO payments (billing_id, amount_paid, payment_method, payment_date) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("ids", $billing_id, $amount_paid, $payment_method);
        $stmt->execute();
        
        // Record in audit trail
        $action = "Walk-in payment processed for reservation #" . $reservation_id;
        $stmt = $conn->prepare("INSERT INTO audit_trails (user_id, action_taken) VALUES (?, ?)");
        $stmt->bind_param("is", $_SESSION['user_id'], $action);
        $stmt->execute();
        
        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        return false;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'create_reservation') {
            $customer_name = $_POST['customer_name'];
            $phone = $_POST['phone'];
            $room_id = $_POST['room_id'];
            $check_in = $_POST['check_in'];
            $check_out = $_POST['check_out'];
            $notes = $_POST['notes'] ?? '';
            
            $reservation_id = createWalkInReservation($conn, $customer_name, $phone, $room_id, $check_in, $check_out, $notes);
            if ($reservation_id) {
                $_SESSION['success_message'] = "Walk-in reservation created successfully!";
            } else {
                $_SESSION['error_message'] = "Failed to create walk-in reservation.";
            }
        } elseif ($_POST['action'] === 'process_payment') {
            $reservation_id = $_POST['reservation_id'];
            $amount_paid = $_POST['amount_paid'];
            $payment_method = $_POST['payment_method'];
            
            if (processWalkInPayment($conn, $reservation_id, $amount_paid, $payment_method)) {
                $_SESSION['success_message'] = "Payment processed successfully!";
            } else {
                $_SESSION['error_message'] = "Failed to process payment.";
            }
        }
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// Get room statistics

// Get room categories
$room_categories = getRoomCategories($conn);

// Get today's check-ins
$today_checkins = getTodaysCheckIns($conn);

// Get today's check-outs
$today_checkouts = getTodaysCheckOuts($conn);

// Get available rooms for dropdown


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Frontdesk Dashboard - JC Hotel</title>
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
        .stats-card {
            border-radius: 10px;
            color: white;
        }
        .stats-card i {
            font-size: 2rem;
            opacity: 0.8;
        }
        .table th {
            background: #f8f9fa;
        }
        .badge {
            padding: 8px 12px;
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
                    <h2>Dashboard</h2>
                    <div>
                        <span class="me-3">Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                        <span class="badge bg-primary">Frontdesk</span>
                    </div>
                </div>

                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php 
                        echo $_SESSION['success_message'];
                        unset($_SESSION['success_message']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php 
                        echo $_SESSION['error_message'];
                        unset($_SESSION['error_message']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Room Categories -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Room Categories</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php while ($category = $room_categories->fetch_assoc()): ?>
                                <div class="col-md-3 mb-4">
                                    <div class="card room-card h-100" style="cursor: pointer;" 
                                         onclick="showAvailableRooms('<?php echo $category['type']; ?>')">
                                        <?php
                                        $imagePath = '../img/rooms/' . strtolower($category['type']) . '.jpg';
                                        if (file_exists($imagePath)): ?>
                                            <img src="<?php echo htmlspecialchars($imagePath); ?>" class="card-img-top room-image" alt="<?php echo ucfirst($category['type']); ?> Room">
                                        <?php else: ?>
                                            <img src="../img/default-room.jpg" class="card-img-top room-image" alt="Default Room">
                                        <?php endif; ?>
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo ucfirst($category['type']); ?></h5>
                                            <p class="card-text">
                                                <span class="badge bg-success text-white"><?php echo $category['count']; ?> Rooms</span>
                                                <br>
                                                <small class="text-muted">₱<?php echo number_format($category['min_price'], 2); ?> - ₱<?php echo number_format($category['max_price'], 2); ?> per night</small>
                                            </p>
                                            <p class="card-text">
                                                <i class="fas fa-bed me-1"></i> Comfortable beds<br>
                                                <i class="fas fa-wifi me-1"></i> Free WiFi<br>
                                                <i class="fas fa-tv me-1"></i> Smart TV<br>
                                                <i class="fas fa-shower me-1"></i> Private bathroom
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Available Rooms Modal -->
                    <div class="modal fade" id="availableRoomsModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Available Rooms - <span id="categoryTitle"></span></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label for="roomSelectModal" class="form-label">Select Room</label>
                                        <select class="form-select" id="roomSelectModal">
                                            <option value="">Choose a room...</option>
                                        </select>
                                    </div>
                                    <div id="roomDetails" class="mt-3" style="display: none;">
                                        <div class="card">
                                            <div class="card-body">
                                                <h6 class="card-subtitle mb-2 text-muted">Room Details</h6>
                                                <p class="card-text mb-1"><strong>Room Number:</strong> <span id="roomNumber"></span></p>
                                                <p class="card-text mb-1"><strong>Type:</strong> <span id="roomType"></span></p>
                                                <p class="card-text mb-1"><strong>Price:</strong> ₱<span id="roomPrice"></span></p>
                                                <p class="card-text"><strong>Description:</strong> <span id="roomDescription"></span></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="button" class="btn btn-primary" id="bookNowBtn" onclick="bookSelectedRoomFromModal()" style="display: none;">
                                        <i class="fas fa-book me-1"></i>Book Now
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Reservation Form Modal -->
                    <div class="modal fade" id="reservationModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">New Reservation</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <form id="reservationForm" method="POST">
                                <input type="hidden" name="action" value="create_reservation">
                                        <input type="hidden" name="room_id" id="selectedRoomId">
                                
                                        <div class="mb-3">
                                    <label class="form-label">Customer Name</label>
                                    <input type="text" name="customer_name" class="form-control" required>
                                </div>
                                
                                        <div class="mb-3">
                                    <label class="form-label">Phone Number</label>
                                    <input type="tel" name="phone" class="form-control" required>
                            </div>
                            
                                    <div class="mb-3">
                                        <label class="form-label">Check-in Date & Time</label>
                                <input type="datetime-local" name="check_in" class="form-control" required>
                            </div>
                            
                                    <div class="mb-3">
                                        <label class="form-label">Check-out Date & Time</label>
                                <input type="datetime-local" name="check_out" class="form-control" required>
                            </div>
                            
                                    <div class="mb-3">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="2"></textarea>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" form="reservationForm" class="btn btn-primary">Create Reservation</button>
                            </div>
                        </div>
                    </div>
                            </div>
                            
                <!-- Available Rooms Dropdown -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Available Rooms</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="roomSelect" class="form-label">Select Available Room</label>
                                    <select class="form-select" id="roomSelect">
                                        <option value="">Choose a room...</option>
                                        <?php while ($room = $available_rooms_dropdown->fetch_assoc()): ?>
                                            <option value="<?php echo $room['room_id']; ?>">
                                                Room <?php echo htmlspecialchars($room['room_number']); ?> - 
                                                <?php echo ucfirst($room['type']); ?> 
                                                (₱<?php echo number_format($room['price'], 2); ?>)
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <button type="button" class="btn btn-primary" onclick="bookSelectedRoom()">
                                    <i class="fas fa-book me-1"></i>Book Selected Room
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Today's Check-ins -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Today's Check-ins</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Reservation ID</th>
                                        <th>Customer</th>
                                        <th>Room</th>
                                        <th>Check-in Time</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($checkin = $today_checkins->fetch_assoc()): ?>
                                        <tr>
                                            <td>#<?php echo $checkin['reservation_id']; ?></td>
                                            <td>
                                                <?php echo htmlspecialchars($checkin['full_name']); ?><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($checkin['phone']); ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <?php echo htmlspecialchars($checkin['room_number']); ?>
                                                </span>
                                                <small class="text-muted">
                                                    <?php echo ucfirst($checkin['type']); ?>
                                                </small>
                                            </td>
                                            <td><?php echo date('h:i A', strtotime($checkin['check_in'])); ?></td>
                                            <td>
                                                <a href="check_in.php?reservation_id=<?php echo $checkin['reservation_id']; ?>" 
                                                   class="btn btn-success btn-sm">
                                                    <i class="fas fa-sign-in-alt me-1"></i>Check-in
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                                                        </div>
                                                    </div>
                                                </div>

                <!-- Today's Check-outs -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Today's Check-outs</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Reservation ID</th>
                                        <th>Customer</th>
                                        <th>Room</th>
                                        <th>Check-out Time</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($checkout = $today_checkouts->fetch_assoc()): ?>
                                        <tr>
                                            <td>#<?php echo $checkout['reservation_id']; ?></td>
                                            <td>
                                                <?php echo htmlspecialchars($checkout['full_name']); ?><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($checkout['phone']); ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <?php echo htmlspecialchars($checkout['room_number']); ?>
                                                </span>
                                                <small class="text-muted">
                                                    <?php echo ucfirst($checkout['type']); ?>
                                                </small>
                                            </td>
                                            <td><?php echo date('h:i A', strtotime($checkout['check_out'])); ?></td>
                                            <td>
                                                <a href="check_out.php?reservation_id=<?php echo $checkout['reservation_id']; ?>" 
                                                   class="btn btn-warning btn-sm">
                                                    <i class="fas fa-sign-out-alt me-1"></i>Check-out
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="../assets/libs/popper.js/dist/umd/popper.min.js"></script>
    <script src="../assets/libs/bootstrap/dist/js/bootstrap.min.js"></script>
    <script>
        let availableRoomsModal;
        let reservationModal;
        let selectedRoomData = null;

        document.addEventListener('DOMContentLoaded', function() {
            availableRoomsModal = new bootstrap.Modal(document.getElementById('availableRoomsModal'));
            reservationModal = new bootstrap.Modal(document.getElementById('reservationModal'));
            
            // Add event listener for room selection in modal
            document.getElementById('roomSelectModal').addEventListener('change', function() {
                const selectedRoom = this.value;
                const roomDetails = document.getElementById('roomDetails');
                const bookNowBtn = document.getElementById('bookNowBtn');
                
                if (selectedRoom) {
                    const room = JSON.parse(selectedRoom);
                    selectedRoomData = room;
                    
                    document.getElementById('roomNumber').textContent = room.room_number;
                    document.getElementById('roomType').textContent = room.type.charAt(0).toUpperCase() + room.type.slice(1);
                    document.getElementById('roomPrice').textContent = parseFloat(room.price).toLocaleString('en-US', {minimumFractionDigits: 2});
                    document.getElementById('roomDescription').textContent = room.description;
                    
                    roomDetails.style.display = 'block';
                    bookNowBtn.style.display = 'block';
                } else {
                    roomDetails.style.display = 'none';
                    bookNowBtn.style.display = 'none';
                    selectedRoomData = null;
                }
            });
        });

        function showAvailableRooms(category) {
            document.getElementById('categoryTitle').textContent = category.charAt(0).toUpperCase() + category.slice(1);
            const roomSelect = document.getElementById('roomSelectModal');
            roomSelect.innerHTML = '<option value="">Choose a room...</option>';
            document.getElementById('roomDetails').style.display = 'none';
            document.getElementById('bookNowBtn').style.display = 'none';
            
            availableRoomsModal.show();

            // Fetch available rooms for the selected category
            fetch(`get_available_rooms.php?category=${category}`)
                .then(response => response.json())
                .then(rooms => {
                    rooms.forEach(room => {
                        const option = document.createElement('option');
                        option.value = JSON.stringify(room);
                        option.textContent = `Room ${room.room_number} - ₱${parseFloat(room.price).toLocaleString('en-US', {minimumFractionDigits: 2})}`;
                        roomSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    roomSelect.innerHTML = '<option value="">Error loading rooms</option>';
                });
        }

        function bookSelectedRoomFromModal() {
            if (selectedRoomData) {
                document.getElementById('selectedRoomId').value = selectedRoomData.room_id;
                availableRoomsModal.hide();
                reservationModal.show();
            }
        }

        function showReservationForm(roomId) {
            document.getElementById('selectedRoomId').value = roomId;
            availableRoomsModal.hide();
            reservationModal.show();
        }

        function bookSelectedRoom() {
            const roomSelect = document.getElementById('roomSelect');
            const selectedRoomId = roomSelect.value;
            if (selectedRoomId) {
                showReservationForm(selectedRoomId);
            } else {
                alert('Please select a room first.');
            }
        }
    </script>

    <!-- Walk-in Reservation Modal -->
    <div class="modal fade" id="walkInReservationModal" tabindex="-1" aria-labelledby="walkInReservationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="walkInReservationModalLabel">
                        <i class="bi bi-person-plus me-2"></i>Walk-in Reservation
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="walkInReservationForm">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Customer Name</label>
                                <input type="text" class="form-control" name="customer_name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Contact Number</label>
                                <input type="tel" class="form-control" name="contact_number" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Room</label>
                                <select class="form-select" name="room_id" id="walkInRoomSelect" required>
                                    <option value="">Select Room</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Room Type</label>
                                <input type="text" class="form-control" id="walkInRoomType" readonly>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Check-in Date & Time</label>
                                <input type="datetime-local" class="form-control" name="check_in" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Check-out Date & Time</label>
                                <input type="datetime-local" class="form-control" name="check_out" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" rows="3"></textarea>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Total Amount</label>
                                <input type="number" class="form-control" name="total_amount" id="walkInTotalAmount" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Payment Status</label>
                                <select class="form-select" name="payment_status" required>
                                    <option value="unpaid">Unpaid</option>
                                    <option value="paid">Paid</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveWalkInReservation">
                        <i class="bi bi-save me-2"></i>Save Reservation
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Add this to your existing JavaScript
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize walk-in reservation modal
        const walkInModal = new bootstrap.Modal(document.getElementById('walkInReservationModal'));
        
        // Load available rooms for walk-in reservation
        function loadAvailableRooms() {
            fetch('get_available_rooms.php')
                .then(response => response.json())
                .then(data => {
                    const roomSelect = document.getElementById('walkInRoomSelect');
                    roomSelect.innerHTML = '<option value="">Select Room</option>';
                    
                    data.forEach(room => {
                        const option = document.createElement('option');
                        option.value = room.room_id;
                        option.textContent = `Room ${room.room_number} - ${room.room_type}`;
                        option.dataset.price = room.price;
                        option.dataset.type = room.room_type;
                        roomSelect.appendChild(option);
                    });
                })
                .catch(error => console.error('Error loading rooms:', error));
        }

        // Update room type and price when room is selected
        document.getElementById('walkInRoomSelect').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value) {
                document.getElementById('walkInRoomType').value = selectedOption.dataset.type;
                document.getElementById('walkInTotalAmount').value = selectedOption.dataset.price;
            } else {
                document.getElementById('walkInRoomType').value = '';
                document.getElementById('walkInTotalAmount').value = '';
            }
        });

        // Handle walk-in reservation form submission
        document.getElementById('saveWalkInReservation').addEventListener('click', function() {
            const form = document.getElementById('walkInReservationForm');
            const formData = new FormData(form);
            
            // Convert form data to JSON
            const data = {
                customer_name: formData.get('customer_name'),
                contact_number: formData.get('contact_number'),
                room_id: formData.get('room_id'),
                check_in: formData.get('check_in'),
                check_out: formData.get('check_out'),
                notes: formData.get('notes'),
                total_amount: formData.get('total_amount'),
                payment_status: formData.get('payment_status')
            };

            // Send data to server
            fetch('process_walk_in.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert('Walk-in reservation saved successfully!');
                    walkInModal.hide();
                    form.reset();
                    // Refresh your reservations table or list here
                    loadReservations(); // Assuming you have this function
                } else {
                    alert('Error saving reservation: ' + result.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while saving the reservation');
            });
        });

        // Load available rooms when modal is shown
        document.getElementById('walkInReservationModal').addEventListener('show.bs.modal', loadAvailableRooms);
    });
    </script>
</body>
</html> 