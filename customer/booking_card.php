<?php
// Ensure $booking array exists and has required fields
if (!isset($booking)) {
    return;
}

$statusClass = [
    'pending' => 'warning',
    'confirmed' => 'success',
    'completed' => 'info',
    'cancelled' => 'danger'
][$booking['status']] ?? 'secondary';

$totalPrice = $booking['price'] * $booking['nights'];
?>

<div class="booking-card position-relative">
    <div class="booking-header d-flex justify-content-between align-items-center">
        <h5>Room #<?php echo htmlspecialchars($booking['room_number']); ?></h5>
        <div class="form-check">
            <input class="form-check-input room-checkbox" type="checkbox" 
                   value="<?php echo $booking['room_id']; ?>" 
                   id="room<?php echo $booking['room_id']; ?>"
                   name="selected_rooms[]"
                   data-price="<?php echo $totalPrice; ?>">
            <label class="form-check-label" for="room<?php echo $booking['room_id']; ?>">
                Select Room
            </label>
        </div>
    </div>
    <div class="room-details">
        <div class="row">
            <div class="col-md-8">
                <p><strong>Room Type:</strong> <?php echo ucfirst($booking['room_type']); ?></p>
                <p><strong>Check-in:</strong> <?php echo date('M d, Y', strtotime($booking['check_in'])); ?></p>
                <p><strong>Check-out:</strong> <?php echo date('M d, Y', strtotime($booking['check_out'])); ?></p>
                <p><strong>Duration:</strong> <?php echo $booking['nights']; ?> night(s)</p>
                <p class="price"><strong>Total Price:</strong> ₱<?php echo number_format($booking['price'] * $booking['nights'], 2); ?></p>
            </div>
            <div class="col-md-4 text-end">
                <span class="status-badge bg-<?php echo $statusClass; ?>">
                    <?php echo ucfirst($booking['status']); ?>
                </span>
                <?php if ($booking['status'] === 'pending'): ?>
                <button type="button" class="btn btn-danger mt-3 cancel-booking" 
                        data-booking-id="<?php echo $booking['reservation_id']; ?>"
                        data-bs-toggle="modal" 
                        data-bs-target="#cancelBookingModal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <?php endif; ?>
            </div>
        </div>  
    </div>
</div>

