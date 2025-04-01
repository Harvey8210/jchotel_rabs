<?php
session_start();
require_once '../config/db_connection.php';

// Check if staff is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header('Location: ../admin/login.php');
    exit();
}

// Function to confirm reservation
function confirmReservation($reservation_id, $staff_id) {
    global $conn;
    
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Update reservation status
        $stmt = $conn->prepare("UPDATE reservations SET status = 'confirmed' WHERE reservation_id = ?");
        $stmt->bind_param("i", $reservation_id);
        $stmt->execute();
        
        // Record in audit trail
        $action = "Reservation #" . $reservation_id . " confirmed by staff";
        $stmt = $conn->prepare("INSERT INTO audit_trails (user_id, action_taken) VALUES (?, ?)");
        $stmt->bind_param("is", $staff_id, $action);
        $stmt->execute();
        
        // Commit transaction
        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        return false;
    }
}

// Function to check payment status
function checkPaymentStatus($reservation_id) {
    global $conn;
    
    try {
        // Get billing information
        $stmt = $conn->prepare("
            SELECT b.*, p.payment_id, p.amount_paid, p.payment_method, p.payment_date 
            FROM billing b 
            LEFT JOIN payments p ON b.billing_id = p.billing_id 
            WHERE b.reservation_id = ?
        ");
        $stmt->bind_param("i", $reservation_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $billing = $result->fetch_assoc();
            return [
                'status' => $billing['status'],
                'total_amount' => $billing['total_amount'],
                'final_amount' => $billing['final_amount'],
                'amount_paid' => $billing['amount_paid'] ?? 0,
                'payment_method' => $billing['payment_method'] ?? null,
                'payment_date' => $billing['payment_date'] ?? null
            ];
        }
        return null;
    } catch (Exception $e) {
        return null;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reservation_id = $_POST['reservation_id'] ?? null;
    $action = $_POST['action'] ?? '';
    
    if ($reservation_id) {
        if ($action === 'confirm') {
            $success = confirmReservation($reservation_id, $_SESSION['user_id']);
            if ($success) {
                $_SESSION['success_message'] = "Reservation confirmed successfully!";
            } else {
                $_SESSION['error_message'] = "Failed to confirm reservation.";
            }
        }
    }
    
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// Get all pending reservations
$stmt = $conn->prepare("
    SELECT r.*, c.full_name as customer_name, rm.room_number, rm.type as room_type
    FROM reservations r
    JOIN customers c ON r.customer_id = c.customer_id
    JOIN rooms rm ON r.room_id = rm.room_id
    WHERE r.status = 'pending'
    ORDER BY r.created_at DESC
");
$stmt->execute();
$pending_reservations = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Panel - JC Hotel</title>
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
        }
        .badge {
            padding: 8px 12px;
        }
        .table th {
            background: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0 sidebar">
                <div class="text-center mb-4">
                    <img src="../img/logo.png" alt="JC Hotel Logo" style="width: 80px;">
                    <h5 class="mt-2">Staff Panel</h5>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="confirm_reservation.php">
                            <i class="fas fa-check-circle me-2"></i>Confirm Reservations
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="view_reservations.php">
                            <i class="fas fa-list me-2"></i>View Reservations
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="view_payments.php">
                            <i class="fas fa-money-bill me-2"></i>View Payments
                        </a>
                    </li>
                    <li class="nav-item mt-4">
                        <a class="nav-link text-danger" href="../admin/logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Confirm Reservations</h2>
                    <div>
                        <span class="me-3">Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                        <span class="badge bg-primary">Staff</span>
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

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Reservation ID</th>
                                        <th>Customer</th>
                                        <th>Room</th>
                                        <th>Check In</th>
                                        <th>Check Out</th>
                                        <th>Payment Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($reservation = $pending_reservations->fetch_assoc()): ?>
                                        <?php 
                                        $payment_status = checkPaymentStatus($reservation['reservation_id']);
                                        ?>
                                        <tr>
                                            <td>#<?php echo $reservation['reservation_id']; ?></td>
                                            <td><?php echo htmlspecialchars($reservation['customer_name']); ?></td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <?php echo htmlspecialchars($reservation['room_number']); ?>
                                                </span>
                                                <small class="text-muted">
                                                    <?php echo ucfirst($reservation['room_type']); ?>
                                                </small>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($reservation['check_in'])); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($reservation['check_out'])); ?></td>
                                            <td>
                                                <?php if ($payment_status): ?>
                                                    <span class="badge bg-<?php echo $payment_status['status'] === 'paid' ? 'success' : 'warning'; ?>">
                                                        <?php echo ucfirst($payment_status['status']); ?>
                                                    </span>
                                                    <?php if ($payment_status['status'] === 'paid'): ?>
                                                        <div class="mt-1">
                                                            <small class="text-muted">
                                                                Amount: ₱<?php echo number_format($payment_status['amount_paid'], 2); ?><br>
                                                                Method: <?php echo ucfirst($payment_status['payment_method']); ?><br>
                                                                Date: <?php echo date('M d, Y H:i', strtotime($payment_status['payment_date'])); ?>
                                                            </small>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">No billing record</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="reservation_id" value="<?php echo $reservation['reservation_id']; ?>">
                                                    <input type="hidden" name="action" value="confirm">
                                                    <button type="submit" class="btn btn-success btn-sm" 
                                                            <?php echo ($payment_status && $payment_status['status'] === 'paid') ? '' : 'disabled'; ?>>
                                                        <i class="fas fa-check me-1"></i>Confirm
                                                    </button>
                                                </form>
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
</body>
</html> 