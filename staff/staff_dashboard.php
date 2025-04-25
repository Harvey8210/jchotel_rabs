<?php
require_once "../admin/includes/config.php";
require_once "../admin/includes/functions.php";
require_login();

// Check if user has staff role
if ($_SESSION["role"] !== "staff") {
    header("location: ../admin/admin.php");
    exit;
}

// Set page title
$page_title = "Staff Dashboard";

// Include header
include "../admin/includes/header.php";

// Include staff sidebar
include "../admin/includes/staff_sidebar.php";
?>

<!-- Main Content -->
<div class="col-md-9 col-lg-10 ms-auto main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Staff Dashboard</h2>
        <div>
            <span class="badge bg-primary">Staff</span>
        </div>
    </div>

    <!-- Dashboard Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Today's Reservations</h5>
                    <?php
                    $today = date('Y-m-d');
                    $sql = "SELECT COUNT(*) as count FROM reservations WHERE DATE(check_in) = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("s", $today);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();
                    echo "<h2 class='card-text'>" . $row['count'] . "</h2>";
                    $stmt->close();
                    ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Available Rooms</h5>
                    <?php
                    $sql = "SELECT COUNT(*) as count FROM rooms WHERE status = 'available'";
                    $result = $conn->query($sql);
                    $row = $result->fetch_assoc();
                    echo "<h2 class='card-text'>" . $row['count'] . "</h2>";
                    ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Users</h5>
                    <?php
                    $sql = "SELECT COUNT(*) as count FROM users";
                    $result = $conn->query($sql);
                    $row = $result->fetch_assoc();
                    echo "<h2 class='card-text'>" . $row['count'] . "</h2>";
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Add User Form -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Add New User</h5>
        </div>
        <div class="card-body">
            <form action="add_user.php" method="post">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="full_name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="staff">Staff</option>
                            <option value="frontdesk">Front Desk</option>
                        </select>
                    </div>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Add User</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Recent Users -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Recent Users</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Full Name</th>
                            <th>Role</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM users ORDER BY created_at DESC LIMIT 5";
                        $result = $conn->query($sql);
                        while ($user = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $user['user_id'] . "</td>";
                            echo "<td>" . htmlspecialchars($user['username']) . "</td>";
                            echo "<td>" . htmlspecialchars($user['full_name']) . "</td>";
                            echo "<td>" . ucfirst($user['role']) . "</td>";
                            echo "<td>" . date('M d, Y', strtotime($user['created_at'])) . "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include "../admin/includes/footer.php";
?> 