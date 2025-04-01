<?php
require_once "includes/config.php";
require_once "includes/functions.php";
require_login();

// Fetch user details if not in session
if (!isset($_SESSION["full_name"])) {
    $sql = "SELECT full_name FROM users WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $_SESSION["user_id"]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    $_SESSION["full_name"] = $user["full_name"];
}

// Set page title
$page_title = "Admin Dashboard";

// Include header
include "includes/header.php";

// Include sidebar
include "includes/sidebar.php";
?>

<!-- Main Content -->
<div class="col-md-9 col-lg-10 ms-auto main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Dashboard Overview</h2>
    </div>

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Rooms</h5>
                    <?php
                    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM rooms");
                    $row = mysqli_fetch_assoc($result);
                    echo "<h2 class='card-text'>" . $row['total'] . "</h2>";
                    ?>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Available Rooms</h5>
                    <?php
                    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM rooms WHERE status = 'available'");
                    $row = mysqli_fetch_assoc($result);
                    echo "<h2 class='card-text'>" . $row['total'] . "</h2>";
                    ?>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Users</h5>
                    <?php
                    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM users");
                    $row = mysqli_fetch_assoc($result);
                    echo "<h2 class='card-text'>" . $row['total'] . "</h2>";
                    ?>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Today's Reservations</h5>
                    <?php
                    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM reservations WHERE DATE(created_at) = CURDATE()");
                    $row = mysqli_fetch_assoc($result);
                    echo "<h2 class='card-text'>" . $row['total'] . "</h2>";
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recent Reservations</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Customer</th>
                                    <th>Room</th>
                                    <th>Check-in</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT r.*, c.full_name, rm.room_number 
                                       FROM reservations r 
                                       JOIN customers c ON r.customer_id = c.customer_id 
                                       JOIN rooms rm ON r.room_id = rm.room_id 
                                       ORDER BY r.created_at DESC LIMIT 5";
                                $result = mysqli_query($conn, $sql);
                                while($row = mysqli_fetch_assoc($result)) {
                                    echo "<tr>";
                                    echo "<td>" . $row['reservation_id'] . "</td>";
                                    echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['room_number']) . "</td>";
                                    echo "<td>" . date('M d, Y', strtotime($row['check_in'])) . "</td>";
                                    echo "<td><span class='badge bg-" . 
                                        ($row['status'] == 'confirmed' ? 'success' : 
                                        ($row['status'] == 'pending' ? 'warning' : 
                                        ($row['status'] == 'checked-in' ? 'info' : 
                                        ($row['status'] == 'checked-out' ? 'secondary' : 'danger')))) . 
                                        "'>" . ucfirst($row['status']) . "</span></td>";
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recent Users</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Full Name</th>
                                    <th>Role</th>
                                    <th>Joined</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT * FROM users ORDER BY created_at DESC LIMIT 5";
                                $result = mysqli_query($conn, $sql);
                                while($row = mysqli_fetch_assoc($result)) {
                                    echo "<tr>";
                                    echo "<td>" . $row['user_id'] . "</td>";
                                    echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
                                    echo "<td>" . ucfirst($row['role']) . "</td>";
                                    echo "<td>" . date('M d, Y', strtotime($row['created_at'])) . "</td>";
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include "includes/footer.php";
?> 