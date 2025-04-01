<?php
include '../config/db_connection.php';

/**
 * Get all available rooms
 * @param mysqli $conn Database connection
 * @return mysqli_result Result set of available rooms
 */
function getAvailableRooms($conn) {
    $query = "SELECT * FROM rooms WHERE status = 'available'";
    return $conn->query($query);
}

/**
 * Get room type display name and filter class
 * @param string $type Room type
 * @return array Array containing display name and filter class
 */
function getRoomTypeInfo($type) {
    $type = strtolower($type);
    $filterClass = '';
    $displayName = ucfirst($type);
    
    switch ($type) {
        case 'standard':
            $filterClass = 'filter-standard';
            break;
        case 'deluxe':
            $filterClass = 'filter-deluxe';
            break;
        case 'suite':
            $filterClass = 'filter-suite';
            break;
        case 'superior':
            $filterClass = 'filter-superior';
            break;
    }
    
    return [
        'display_name' => $displayName,
        'filter_class' => $filterClass
    ];
}

/**
 * Get room image path
 * @param string $image Image filename from database
 * @return string Full image path
 */
function getRoomImagePath($image) {
    return '../img/rooms/' . $image;
}

/**
 * Get room details by ID
 * @param mysqli $conn Database connection
 * @param int $room_id Room ID
 * @return array|false Room details or false if not found
 */
function getRoomDetails($conn, $room_id) {
    $stmt = $conn->prepare("
        SELECT r.*, 
               (SELECT COUNT(*) FROM reservations res 
                WHERE res.room_id = r.room_id 
                AND res.status IN ('confirmed', 'checked-in')) as active_reservations
        FROM rooms r 
        WHERE r.room_id = ?
    ");
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

/**
 * Check if room is available for dates
 * @param mysqli $conn Database connection
 * @param int $room_id Room ID
 * @param string $check_in Check-in date
 * @param string $check_out Check-out date
 * @return bool True if room is available, false otherwise
 */
function isRoomAvailableForDates($conn, $room_id, $check_in, $check_out) {
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count
        FROM reservations
        WHERE room_id = ? 
        AND status IN ('confirmed', 'checked-in')
        AND (
            (check_in BETWEEN ? AND ?) OR
            (check_out BETWEEN ? AND ?) OR
            (check_in <= ? AND check_out >= ?)
        )
    ");
    $stmt->bind_param("isssss", $room_id, $check_in, $check_out, $check_in, $check_out, $check_in, $check_out);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['count'] == 0;
} 