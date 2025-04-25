<?php
require_once "includes/config.php";
require_once "includes/functions.php";
require_login();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            $username = sanitize_input($_POST['username']);
            $password = $_POST['password'];
            $full_name = sanitize_input($_POST['full_name']);
            $role = sanitize_input($_POST['role']);
            
            $result = add_user($conn, $username, $password, $full_name, $role);
            if ($result === true) {
                $success_message = "User added successfully!";
            } else if ($result === false) {
                $error_message = "Username already exists. Please choose a different username.";
            } else {
                $error_message = "Error adding user.";
            }
        } elseif ($_POST['action'] == 'delete' && isset($_POST['user_id'])) {
            if (delete_user($conn, $_POST['user_id'])) {
                $success_message = "User deleted successfully!";
            } else {
                $error_message = "Error deleting user.";
            }
        }
    }
}

// Get all users
$users = get_all_users($conn);

// Set page title
$page_title = "User Management";

// Include header
include "includes/header.php";

// Include sidebar
include "includes/sidebar.php";
?>

<!-- Main Content -->
<div class="col-md-9 col-lg-10 ms-auto main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>User Management</h2>
        <div>
            <a href="add_user.php" class="btn btn-primary me-2">
                <i class="fas fa-plus me-2"></i>Add New User
            </a>
            <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="fas fa-user-plus me-2"></i>Quick Add
            </button>
        </div>
    </div>

    <?php if (isset($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Users Table -->
    <div class="card">
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
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($user = mysqli_fetch_assoc($users)): ?>
                            <tr>
                                <td><?php echo $user['user_id']; ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                <td><?php echo ucfirst($user['role']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-warning" onclick="editUser(<?php echo $user['user_id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
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

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="admin">Admin</option>
                            <option value="frontdesk">Front Desk</option>
                            <option value="staff">Staff</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Define page-specific scripts
$page_scripts = <<<EOT
<script>
    function editUser(userId) {
        // Implement edit user functionality
        alert('Edit user functionality will be implemented here');
    }
</script>
EOT;

// Include footer
include "includes/footer.php";
?> 