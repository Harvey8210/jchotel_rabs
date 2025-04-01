<?php
require_once "includes/config.php";
require_once "includes/functions.php";
require_login();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            $room_number = sanitize_input($_POST['room_number']);
            $type = sanitize_input($_POST['type']);
            $price = floatval($_POST['price']);
            $description = sanitize_input($_POST['description']);
            
            // Handle image upload
            $image_name = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $file_name = $_FILES['image']['name'];
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $image_name = $file_name;
            }
            
            if (add_room($conn, $room_number, $type, $price, $description, $image_name)) {
                $success_message = "Room added successfully!";
            } else {
                $error_message = "Error: Room number '$room_number' already exists. Please use a different room number.";
            }
        } elseif ($_POST['action'] == 'delete' && isset($_POST['room_id'])) {
            if (delete_room($conn, $_POST['room_id'])) {
                $success_message = "Room deleted successfully!";
            } else {
                $error_message = "Error deleting room.";
            }
        } elseif ($_POST['action'] == 'update_status' && isset($_POST['room_id']) && isset($_POST['status'])) {
            if (update_room_status($conn, $_POST['room_id'], $_POST['status'])) {
                $success_message = "Room status updated successfully!";
            } else {
                $error_message = "Error updating room status.";
            }
        }
    }
}

// Get all rooms
$rooms = get_all_rooms($conn);

// Set page title
$page_title = "Room Management";

// Include header
include "includes/header.php";

// Include sidebar
include "includes/sidebar.php";
?>

<!-- Main Content -->
<div class="col-md-9 col-lg-10 ms-auto main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Room Management</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoomModal">
            <i class="fas fa-plus me-2"></i>Add New Room
        </button>
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

    <!-- Rooms Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Room Number</th>
                            <th>Type</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($room = mysqli_fetch_assoc($rooms)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($room['room_number']); ?></td>
                                <td><?php echo ucfirst($room['type']); ?></td>
                                <td>₱<?php echo number_format($room['price'], 2); ?></td>
                                <td>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="room_id" value="<?php echo $room['room_id']; ?>">
                                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                            <option value="available" <?php echo $room['status'] == 'available' ? 'selected' : ''; ?>>Available</option>
                                            <option value="occupied" <?php echo $room['status'] == 'occupied' ? 'selected' : ''; ?>>Occupied</option>
                                            <option value="reserved" <?php echo $room['status'] == 'reserved' ? 'selected' : ''; ?>>Reserved</option>
                                            <option value="maintenance" <?php echo $room['status'] == 'maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                                        </select>
                                    </form>
                                </td>
                                <td><?php echo htmlspecialchars($room['description']); ?></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-warning" onclick="editRoom(<?php echo $room['room_id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this room?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="room_id" value="<?php echo $room['room_id']; ?>">
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

<!-- Add Room Modal -->
<div class="modal fade" id="addRoomModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Room</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label for="room_number" class="form-label">Room Number</label>
                        <input type="number" 
                               class="form-control" 
                               id="room_number" 
                               name="room_number" 
                               min="1" 
                               max="999" 
                               pattern="[0-9]*" 
                               inputmode="numeric"
                               onkeypress="return event.charCode >= 48 && event.charCode <= 57"
                               required>
                    </div>
                    <div class="mb-3">
                        <label for="type" class="form-label">Room Type</label>
                        <select class="form-select" id="type" name="type" required>
                            <option value="">Select Room Type</option>
                            <option value="standard">Standard</option>
                            <option value="superior">Superior</option>
                            <option value="suite">Suite</option>
                            <option value="deluxe">Deluxe</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="price" class="form-label">Price</label>
                        <input type="number" class="form-control" id="price" name="price" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="image" class="form-label">Room Image</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        <div class="form-text">Upload an image of the room (optional)</div>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Room</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
// Define page-specific scripts
$page_scripts = <<<EOT
<script>
    document.getElementById('room_number').addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
        
        let value = parseInt(this.value);
        if (value < 1) this.value = 1;
        if (value > 999) this.value = 999;
    });

    function editRoom(roomId) {
        alert('Edit room functionality will be implemented here');
    }

    // Image preview functionality
    document.getElementById('image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.createElement('img');
                preview.src = e.target.result;
                preview.className = 'image-preview';
                preview.style.display = 'block';
                
                // Remove existing preview if any
                const existingPreview = document.querySelector('.image-preview');
                if (existingPreview) {
                    existingPreview.remove();
                }
                
                // Add new preview after the file input
                document.getElementById('image').parentNode.appendChild(preview);
            }
            reader.readAsDataURL(file);
        }
    });
</script>
EOT;

// Include footer
include "includes/footer.php";
?> 