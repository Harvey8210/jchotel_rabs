<?php
require_once "../admin/includes/config.php";
require_once "../admin/includes/functions.php";
require_login();

// Check if user has staff role
if ($_SESSION["role"] !== "staff") {
    header("location: ../admin/admin.php");
    exit;
}

$username = $password = $full_name = $role = "";
$username_err = $password_err = $full_name_err = $role_err = "";
$success_message = $error_message = "";

// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate username
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter a username.";
    } else {
        $username = trim($_POST["username"]);
    }
    
    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = "Password must have at least 6 characters.";
    } else {
        $password = trim($_POST["password"]);
    }
    
    // Validate full name
    if (empty(trim($_POST["full_name"]))) {
        $full_name_err = "Please enter a full name.";
    } else {
        $full_name = trim($_POST["full_name"]);
    }
    
    // Validate role
    if (empty(trim($_POST["role"]))) {
        $role_err = "Please select a role.";
    } else {
        $role = trim($_POST["role"]);
    }
    
    // Check input errors before inserting in database
    if (empty($username_err) && empty($password_err) && empty($full_name_err) && empty($role_err)) {
        // Use the staff_add_user function
        $result = staff_add_user($conn, $username, $password, $full_name, $role);
        
        if ($result["success"]) {
            $success_message = $result["message"];
            // Redirect to staff dashboard after successful addition
            header("location: staff_dashboard.php");
            exit;
        } else {
            $error_message = $result["message"];
        }
    }
    
    $conn->close();
}

// Set page title
$page_title = "Add New User";

// Include header
include "../admin/includes/header.php";

// Include staff sidebar
include "../admin/includes/staff_sidebar.php";
?>

<!-- Main Content -->
<div class="col-md-9 col-lg-10 ms-auto main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Add New User</h2>
        <a href="staff_dashboard.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
        </a>
    </div>
    
    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-body">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>" id="username">
                    <span class="invalid-feedback"><?php echo $username_err; ?></span>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" id="password">
                    <span class="invalid-feedback"><?php echo $password_err; ?></span>
                </div>
                
                <div class="mb-3">
                    <label for="full_name" class="form-label">Full Name</label>
                    <input type="text" name="full_name" class="form-control <?php echo (!empty($full_name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $full_name; ?>" id="full_name">
                    <span class="invalid-feedback"><?php echo $full_name_err; ?></span>
                </div>
                
                <div class="mb-3">
                    <label for="role" class="form-label">Role</label>
                    <select name="role" class="form-select <?php echo (!empty($role_err)) ? 'is-invalid' : ''; ?>" id="role">
                        <option value="">Select Role</option>
                        <option value="staff" <?php echo ($role === "staff") ? "selected" : ""; ?>>Staff</option>
                        <option value="frontdesk" <?php echo ($role === "frontdesk") ? "selected" : ""; ?>>Front Desk</option>
                    </select>
                    <span class="invalid-feedback"><?php echo $role_err; ?></span>
                </div>
                
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Add User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Include footer
include "../admin/includes/footer.php";
?> 